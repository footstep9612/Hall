<?php

/**
 * @desc   QuoteModel
 * @Author 买买提
 */
class QuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote';

    const INQUIRY_DRAFT = 'DRAFT'; //新建询单
    const INQUIRY_BIZ_DISPATCHING = 'BIZ_DISPATCHING'; //事业部分单员
    const INQUIRY_CC_DISPATCHING = 'CC_DISPATCHING'; //易瑞客户中心
    const INQUIRY_BIZ_QUOTING = 'BIZ_QUOTING'; //事业部报价
    const INQUIRY_LOGI_DISPATCHING = 'LOGI_DISPATCHING'; //物流分单员
    const INQUIRY_LOGI_QUOTING = 'LOGI_QUOTING'; //物流报价
    const INQUIRY_LOGI_APPROVING = 'LOGI_APPROVING'; //物流审核
    const INQUIRY_BIZ_APPROVING = 'BIZ_APPROVING'; //事业部核算
    const INQUIRY_MARKET_APPROVING = 'MARKET_APPROVING'; //事业部审核
    const INQUIRY_MARKET_CONFIRMING = 'MARKET_CONFIRMING'; //市场确认
    const INQUIRY_QUOTE_SENT = 'QUOTE_SENT'; //报价单已发出
    const INQUIRY_INQUIRY_CLOSED = 'INQUIRY_CLOSED'; //报价关闭

    const QUOTE_NOT_QUOTED = 'NOT_QUOTED'; //未报价
    const QUOTE_ONGOING = 'ONGOING'; //报价中
    const QUOTE_QUOTED = 'QUOTED'; //已报价
    const QUOTE_COMPLETED = 'COMPLETED'; //已完成


    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取综合报价信息
     * @param array $condition    条件
     * @param string $field    筛选字段
     * @return array
     */
    public function getGeneralInfo(array $condition,$field){
        return $this->where($condition)->field($field)->find();
    }

    /**
     * @param array $condition    条件
     * @param array $data    数据
     * @return array|bool
     */
    public function updateGeneralInfo(array $condition,$data){

        try{
            $this->where($condition)->save($this->create($data));
            //处理计算相关逻辑
            $this->calculate($condition);
            return true;

        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }

    }


    /**
     * 处理所有计算相关逻辑
     * @param $condition    条件
     * @return bool
     */
    private function calculate($condition){

        $quoteItemModel = new QuoteItemModel();
        $exchangeRateModel = new ExchangeRateModel();

        /*
        |--------------------------------------------------------------------------
        | 计算商务报出EXW单价         计算公式 : EXW单价=采购单价*毛利率/汇率
        |--------------------------------------------------------------------------
        */
        $quoteInfo = $this->where($condition)->field('id,gross_profit_rate,exchange_rate')->find();
        $gross_profit_rate = $quoteInfo['gross_profit_rate'];//毛利率

        $quoteItemIds = $quoteItemModel->where($condition)->field('id,purchase_unit_price,purchase_price_cur_bn,reason_for_no_quote')->select();
        if (!empty($quoteItemIds)) {
            foreach ($quoteItemIds as $key => $value) {
                if (empty($value['reason_for_no_quote']) && !empty($value['purchase_unit_price'])) {
                    $exchange_rate = $exchangeRateModel->where(['cur_bn2' => $value['purchase_price_cur_bn'], 'cur_bn1' => 'USD'])->order('created_at DESC')->getField('rate');
                    $exw_unit_price = $value['purchase_unit_price'] * $gross_profit_rate / $exchange_rate;
                    $exw_unit_price = sprintf("%.8f", $exw_unit_price);
                    $quoteItemModel->where(['id' => $value['id']])->save([
                        'exw_unit_price' => $exw_unit_price
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 计算商务报出EXW总价        计算公式 : EXW总价=EXW单价*条数*数量
        |--------------------------------------------------------------------------
        */
        $quoteItemExwUnitPrices = $quoteItemModel->where($condition)->field('exw_unit_price,quote_qty,gross_weight_kg')->select();

        $total_exw_price = [];
        foreach ($quoteItemExwUnitPrices as $price) {
            $total_exw_price[] = $price['exw_unit_price'] * $price['quote_qty'];
        }
        $total_exw_price = array_sum($total_exw_price);

        $total_gross_weight_kg = [];
        foreach ($quoteItemExwUnitPrices as $price) {
            $total_gross_weight_kg[] = $price['gross_weight_kg'];
        }
        $total_gross_weight_kg = array_sum($total_gross_weight_kg);

       $this->where($condition)->save([
            //总重
            'total_weight' => $total_gross_weight_kg,
            //exw合计
            'total_exw_price' => $total_exw_price
        ]);

        /*
        |--------------------------------------------------------------------------
        | 采购合计          计算公式 : 采购总价=采购单价*条数
        |--------------------------------------------------------------------------
        */
        $totalPurchase = [];
        $quoteItemsData = $quoteItemModel->where($condition)->field('purchase_unit_price,purchase_price_cur_bn,quote_qty')->select();
        foreach ($quoteItemsData as $quote => $item) {
            switch ($item['purchase_price_cur_bn']) {
                case 'EUR' :
                    $rate = $exchangeRateModel->where(['cur_bn2' => 'EUR', 'cur_bn1' => 'USD'])->order('created_at DESC')->getField('rate');
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'] / $rate;
                    break;
                case 'USD' :
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'];
                    break;
                case 'CNY' :
                    $rate = $exchangeRateModel->where(['cur_bn2' => 'CNY', 'cur_bn1' => 'USD'])->order('created_at DESC')->getField('rate');
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'] / $rate;
                    break;
            }
        }

        return $this->where($condition)->save(['total_purchase' => array_sum($totalPurchase)]);

    }

    /**
     * @param array $condition
     * @return array
     */
    public function rejectToBiz(array $condition){

        /*
        |--------------------------------------------------------------------------
        | 退回事业部分单员
        |--------------------------------------------------------------------------
        |
        | 询单(inquiry): ['status'=>BIZ_DISPATCHING,'quote_status'=>NOT_QUOTED]
        | 报价单(quote): ['status'=>'BIZ_DISPATCHING']
        |
        */
        $this->startTrans();
        $quoteResult = $this->where($condition)->save(['status'=>self::INQUIRY_BIZ_DISPATCHING]);

        $inquiry = new InquiryModel();
        $inquiry->startTrans();
        $inquiryResult = $inquiry->where([
            'id' => $condition['inquiry_id']
        ])->save([
            'status' => self::INQUIRY_BIZ_DISPATCHING,
            'quote_status' => self::QUOTE_NOT_QUOTED
        ]);

        if ($quoteResult && $inquiryResult){
            $this->commit();
            $inquiry->commit();
            return ['code'=>'1','message'=>'退回成功!'];
        }else{
            $this->rollback();
            $inquiry->rollback();
            return ['code'=>'1','message'=>'不能重复退回!'];
        }

    }


    /**
     * 提交物流分单员
     * @param $request 数据
     * @param $user 操作用户id
     * @return array
     */
    public function sendLogisticsHandler($request,$user){

        //更改询单(inqury->status)的状态
        $inquiry = new InquiryModel();
        $inquiry->startTrans();
        $inquiryResult = $inquiry->where(['id' => $request['inquiry_id']])->save(['status' => self::INQUIRY_LOGI_DISPATCHING]);

        //更改报价(quote->status)的状态
        $this->startTrans();
        $quoteResult = $this->where(['inquiry_id' => $request['inquiry_id']])->save(['status' => self::INQUIRY_LOGI_DISPATCHING]);


        if ($inquiryResult && $quoteResult) {

            //给物流表创建一条记录
            $quoteLogiFeeModel = new QuoteLogiFeeModel();

            $quoteInfo = $this->where(['inquiry_id' => $request['inquiry_id']])->field('id,premium_rate')->find();

           $quoteLogiFeeModel->add($quoteLogiFeeModel->create([
                'quote_id' => $quoteInfo['id'],
                'inquiry_id' => $request['inquiry_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $user,
                'premium_rate' => $quoteInfo['premium_rate']
            ]));

            //给物流报价单项形成记录
            $quoteItemModel = new QuoteItemModel();
            $quoteItemIds = $quoteItemModel->where(['quote_id' => $quoteInfo['id']])->getField('id', true);

            $quoteItemLogiModel = new QuoteItemLogiModel();
            foreach ($quoteItemIds as $quoteItemId) {
                $quoteItemLogiModel->add($quoteItemLogiModel->create([
                    'quote_id' => $quoteInfo['id'],
                    'quote_item_id' => $quoteItemId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $user
                ]));
            }

            $inquiry->commit();
            $this->commit();

            return ['code' => '1', 'message' => '提交成功!'];

        } else {

            $inquiry->rollback();
            $this->rollback();

            return ['code' => '-104', 'message' => '不能重复提交!'];
        }

    }

    /**
     * @desc 获取查询条件
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
     public function getWhere($condition) {
     	$where = array();

     	if(!empty($condition['id'])) {
     	    $where['id'] = $condition['id'];
     	}
     	
     	if(!empty($condition['quote_no'])) {
    		$where['quote_no'] = $condition['quote_no'];
    	}
    	
     	if(!empty($condition['biz_quote_status'])) {
    		$where['biz_quote_status'] = array('in', $condition['biz_quote_status']);
    	}
    	
    	return $where;
    	
     }
     
	/**
     * @desc 获取关联查询条件
 	 * @author liujf 2017-06-29
     * @param array $condition
     * @return array
     */
     public function getJoinWhere($condition) {
		 $where = array();

		 if(!empty($condition['quote_no'])) {
			 $where['a.quote_no'] = $condition['quote_no'];
    	 }
		 if(!empty($condition['biz_quote_status'])) {
			 $where['a.biz_quote_status'] = $condition['biz_quote_status'];
		 }
		 if(!empty($condition['logi_quote_status'])) {
			 $where['a.logi_quote_status'] = array('in', $condition['logi_quote_status']);
		 }
		 if (!empty($condition['serial_no'])) {
			 $where['b.serial_no'] = $condition['serial_no'];
		 }
		 if (!empty($condition['inquiry_no'])) {
			 $where['b.inquiry_no'] = $condition['inquiry_no'];
		 }
		 if (!empty($condition['quote_status'])) {
			 $where['a.quote_status'] = $condition['quote_status'];
		 }
		 if (!empty($condition['inquiry_region'])) {
			 $where['b.inquiry_region'] = $condition['inquiry_region'];
		 }
		 if (!empty($condition['inquiry_country'])) {
			 $where['b.inquiry_country'] = $condition['inquiry_country'];
		 }
		 if (!empty($condition['agent'])) {
			 $where['b.agent'] = $condition['agent'];
		 }
		 if (!empty($condition['customer_id'])) {
			 $where['b.customer_id'] = $condition['customer_id'];
		 }
		 if(!empty($condition['start_time']) && !empty($condition['end_time'])){
			 $where['created_at'] = array(
					 array('gt',date('Y-m-d H:i:s',$condition['start_time'])),
					 array('lt',date('Y-m-d H:i:s',$condition['end_time']))
			 );
		 }
    	
    	return $where;
    	
     }
     
	/**
     * @desc 获取记录总数
 	 * @author liujf 2017-06-27
     * @param array $condition 
     * @return int $count
     */
    public function getCount($condition) {
    	$where = $this->getWhere($condition);
    	
    	$count = $this->where($where)->count('id');
    	
    	return $count > 0 ? $count : 0;
    }
    
    /**
     * @desc 获取报价单列表
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getList($condition) {
    	
    	$where = $this->getWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		return $this->where($where)->page($condition['currentPage'], $condition['pageSize'])->order('id DESC')->select();
    	} else {
    		return $this->where($where)->page(1, 10)->order('id DESC')->select();
    	}
    }   
    
	/**
     * @desc 获取关联记录总数
 	 * @author liujf 2017-06-29
     * @param array $condition 
     * @return int $count
     */
    public function getJoinCount($condition) {
    	
    	$where = $this->getJoinWhere($condition);
    	
    	$count = $this->alias('a')
    				  ->join($this->joinInquiry, 'LEFT')
    				  ->field($this->fieldJoin)
    				  ->where($where)
    				  ->count('a.id');
    	
    	return $count > 0 ? $count : 0;
    }
    
	/**
     * @desc 获取关联询价单列表
 	 * @author liujf 2017-06-29
     * @param array $condition
     * @return array
     */
    public function getJoinList($condition) {
    	
    	$where = $this->getJoinWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		
    		return $this->alias('a')
	    				 ->join($this->joinInquiry, 'LEFT')
	    				 ->field($this->fieldJoin)
	    				 ->where($where)
	    				 ->page($condition['currentPage'], $condition['pageSize'])
	    				 ->order('a.id DESC')
	    				 ->select();
    	} else {
    		return $this->alias('a')
    					->join($this->joinInquiry, 'LEFT')
    					->field($this->fieldJoin)
    					->where($where)
    					->page(1, 10)
    					->order('a.id DESC')
    					->select();
    	}
    }
    
	/**
     * @desc 获取报价单详情
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getDetail($condition) {
    	
    	$where = $this->getWhere($condition);
    	
        return $this->where($where)->find();
    }
    
	/**
     * @desc 获取关联询价单详情
 	 * @author liujf 2017-06-29
     * @param array $condition
     * @return array
     */
    public function getJoinDetail($condition) {
    	
    	$where = $this->getJoinWhere($condition);
    	
    	if (empty($where)) return false;
    	
    	return $this->alias('a')
    				->join($this->joinInquiry, 'LEFT')
    				->field($this->fieldJoin)
    				->where($where)
    				->find();
    }

	/**
	 * @desc 修改报价单
	 * @author zhangyuliang 2017-06-29
	 * @param array $where , $condition
	 * @return array
	 */
	public function updateQuote($where = [], $condition = []) {

		if(empty($where['quote_no'])){
			return false;
		}

		$data = $this->create($condition);

		return $this->where($where)->save($data);
	}

	/**
	 * @desc 批量修改状态
	 * @author zhangyuliang 2017-06-30
	 * @param array $condition
	 * @return array
	 */
	public function updateQuoteStatus($condition = [], $data = []) {

		if(isset($condition['inquiry_id'])){
			$where['inquiry_id'] = array('in',explode(',',$condition['inquiry_id']));
		}else{
			$results['code'] = '-103';
			$results['message'] = '没有ID!';
			return $results;
		}
		if(!empty($condition['status'])){
			$data['status'] = $condition['status'];
		}

		try {
			$id = $this->where($where)->save($data);
			if($id){
				$results['code'] = '1';
				$results['message'] = '成功！';
			}else{
				$results['code'] = '-101';
				$results['message'] = '修改失败!';
			}
			return $results;
		} catch (Exception $e) {
			$results['code'] = $e->getCode();
			$results['message'] = $e->getMessage();
			return $results;
		}
	}

	/**
	 * @desc 删除报价单
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function delQuote($condition = []) {

		if(!empty($condition['quote_no'])) {
			$where['quote_no'] = $condition['quote_no'];
		}else{
			return false;
		}

		return $this->where($where)->save(['quote_status' => 'DELETED']);
	}
}
