<?php

/**
 * @desc   QuoteModel
 * @Author 买买提
 */
class QuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取综合报价信息
     * @param array $condition    条件
     * @param       $field    筛选字段
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
            return true;
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
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
