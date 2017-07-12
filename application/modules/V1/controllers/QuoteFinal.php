<?php
/**
 * name: QuoteFinal.php
 * desc: 市场报价单控制器
 * User: zhangyuliang
 * Date: 2017/6/30
 * Time: 14:35
 */
class QuoteFinalController extends PublicController {

    public function init() {
        parent::init();
        $this->inquiryModel = new InquiryModel();
        $this->inquiryItemModel = new InquiryItemModel();
        $this->inquiryAttachModel = new InquiryAttachModel();
        $this->inquiryItemAttachModel = new InquiryItemAttachModel();
        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->quoteAttachModel = new QuoteAttachModel();
        $this->quoteItemAttachModel = new QuoteItemAttachModel();
        $this->finalQuoteModel = new FinalQuoteModel();
        $this->finalQuoteItemModel = new FinalQuoteItemModel();
        $this->finalQuoteAttachModel = new FinalQuoteAttachModel();
        $this->finalQuoteItemAttachModel = new FinalQuoteItemAttachModel();
        $this->exchangeRateModel = new ExchangeRateModel();
        $this->goodsPriceHisModel = new GoodsPriceHisModel();
    }

    /**
     * @desc 创建市场报价单
     * @author liujf 2017-07-04
     * @return mix
     */
    public function createFinalQuoteAction() {
        $condition = $this->put_data;

        $quotel_no_arr = explode(',', $condition['quote_no']);

        $where = array('quote_no' => array('in', $quotel_no_arr));

        $quoteList = $this->quoteModel->where($where)->select();

       $quoteFinal = $quoteFinalList = $inquiry_no_arr = array();

        $time = date('Y-m-d H:i:s');

        foreach ($quoteList as $quote) {
            $quoteFinal = $quote;
            unset($quoteFinal['id']);
            unset($quoteFinal['logi_quote_status']);
            unset($quoteFinal['biz_quote_status']);
            unset($quoteFinal['logi_agent']);
            unset($quoteFinal['logi_agent_email']);
            unset($quoteFinal['logi_submit_at']);
            unset($quoteFinal['logi_notes']);
            unset($quoteFinal['logi_checker']);
            unset($quoteFinal['logi_checker_email']);
            unset($quoteFinal['logi_check_at']);
            unset($quoteFinal['logi_check_notes']);
            unset($quoteFinal['checker2']);
            unset($quoteFinal['checker2_email']);
            unset($quoteFinal['check2_at']);
            unset($quoteFinal['check2_notes']);
            $quoteFinal['created_at'] = $time;
            $quoteFinalList[] = $quoteFinal;

            $inquiry_no_arr[] = $quote['inquiry_no'];
        }
        if ($this->finalQuoteModel->addAll($quoteFinalList)) {

            $approveLog = array(
                'inquiry_no' =>implode(',', $inquiry_no_arr),
                'type' => '创建市场报价单'
            );
            
            $this->addApproveLog($approveLog); //记录创建市场报价单日志
            
            $this->createFinalQuoteItem($where);
            $this->createFinalQuoteAttach($where);
            $this->createFinalQuoteItemAttach($where);

            $this->jsonReturn(true);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 创建市场报价单项目
     * @author liujf 2017-06-24
     * @param array $where 报价单号查询条件
     * @return json
     */
    private function createFinalQuoteItem($where) {

        $finalItemList = $finalItem = array();

        $quoteItemList = $this->quoteItemModel->where($where)->select();

        foreach ($quoteItemList as $quoteItem) {
            $finalItem = $quoteItem;
            unset($finalItem['id']);
            $finalItem['exw_unit_price'] = '';
            $finalItem['quote_unit_price'] = '';
            $finalItem['created_at'] = date('Y-m-d H:i:s');
            $finalItemList[] = $finalItem;
        }

        return $this->finalQuoteItemModel->addAll($finalItemList);
    }

    /**
     * @desc 创建市场报价单附件
     * @author liujf 2017-06-24
     */
    private function createFinalQuoteAttach($where) {

        $finalAttachList = $finalAttach = array();

        $quoteAttachList = $this->quoteAttachModel->where($where)->select();

        foreach ($quoteAttachList as $quoteAttach) {
            $finalAttach = $quoteAttach;
            unset($finalAttach['id']);
            $finalAttachList[] = $finalAttach;
        }

        return $this->finalQuoteAttachModel->addAll($finalAttachList);
    }

    /**
     * @desc 创建市场报价单项目附件
     * @author liujf 2017-06-24
     */
    private function createFinalQuoteItemAttach($where) {

        $finalItemAttachList = $finalItemAttach = array();

        $quoteItemAttachList = $this->quoteItemAttachModel->where($where)->select();

        foreach ($quoteItemAttachList as $quoteItemAttach) {
            $finalItemAttach = $quoteItemAttach;
            unset($finalItemAttach['id']);
            $finalItemAttachList[] = $finalItemAttach;
        }

        return $this->finalQuoteItemAttachModel->addAll($finalItemAttachList);
    }

    /**
     * @desc 市场获取可修改报价列表接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function getFinalQuoteListAction() {

        $condition = $this->put_data;
        $user = new UserModel();
        $area = new MarketAreaModel();
        $country = new MarketAreaCountryModel();

        $data = $this->finalQuoteModel->getList($condition);

        if($data) {
            foreach ($data as $key => $val) {
                if (!empty($val['agent'])) {
                    $userId = json_decode($val['agent']);
                    $userInfo = $user->where('id=' . $userId['1'])->find();
                    $data[$key]['agent'] = $userInfo['name'];
                }
                if (!empty($val['inquiry_region'])) {
                    $areaInfo = $area->where('id=' . $val['inquiry_region'])->find();
                    $data[$key]['inquiry_region'] = $areaInfo['bn'];
                }
                if (!empty($val['inquiry_country'])) {
                    $areaInfo = $country->where('id=' . $val['inquiry_country'])->find();
                    $data[$key]['inquiry_country'] = $areaInfo['country_bn'];
                }
            }
        }
        if ($data) {
            $res['code'] = 1;
            $res['message'] = '成功!';
            $res['data'] = $data;
            $res['count'] = $this->finalQuoteModel->getCount($condition);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 市场获取报价详情接口
     * @author liujf 2017-06-28
     * @return json
     */
    public function getFinalQuoteDetailAction() {
        $condition = $this->put_data;

        $res = $this->finalQuoteModel->getDetail($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 市场修改报价接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function updateFinalQuoteAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {
            $where['quote_no'] = $condition['quote_no'];

            $finalQuote['payment_received_days'] = $condition['payment_received_days'];
            $finalQuote['exw_delivery_period'] = $condition['exw_delivery_period'];
            $finalQuote['fund_occupation_rate'] = $condition['fund_occupation_rate'];

            $res = $this->finalQuoteModel->update($where,$finalQuote);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场获取报价附件列表接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function getFinalQuoteAttachListAction() {
        $condition = $this->put_data;

        $res = $this->finalQuoteAttachModel->getAttachList($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 市场添加报价附件接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function addFinalQuoteAttachApiAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {

            $res = $this->finalQuoteAttachModel->addAttach($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场删除报价附件接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function delFinalQuoteAttachAction() {
        $condition = $this->put_data;

       if (!empty($condition['attach_id'])) {
			$condition['id'] = $condition['attach_id'];
    		unset($condition['attach_id']);

            $res = $this->finalQuoteAttachModel->where($condition)->delete();

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 市场获取报价SKU列表接口
     * @author liujf 2017-06-26
     * @return json
     */
    public function getFinalQuoteItemListAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {
            if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
                $data = $this->finalQuoteItemModel
                    ->alias('a')
                    ->join("quote_item b ON a.id = b.id", 'LEFT')
                    ->field("a.*,b.exw_unit_price AS quote_exw_unit_price,b.exw_unit_price AS q_exw_unit_price,b.quote_unit_price AS q_quote_unit_price")
                    ->where(array('a.quote_no' => $condition['quote_no']))->page($condition['currentPage'], $condition['pageSize'])->select();
            } else {
                $data = $this->finalQuoteItemModel
                    ->alias('a')
                    ->join("quote_item b ON a.id = b.id", 'LEFT')
                    ->field("a.*,b.exw_unit_price AS quote_exw_unit_price,b.exw_unit_price AS q_exw_unit_price,b.quote_unit_price AS q_quote_unit_price")
                    ->where(array('a.quote_no' => $condition['quote_no']))->select();
            }

            if ($data) {
                $res['code'] = 1;
                $res['message'] = '成功!';
                $res['data'] = $data;
                $res['count'] = $this->finalQuoteItemModel->getCount($condition);
                $this->jsonReturn($res);
            } else {
                $this->jsonReturn(false);
            }
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场获取报价SKU详情接口
     * @author liujf 2017-06-28
     * @return json
     */
    public function getFinalQuoteItemDetailAction() {
        $condition = $this->put_data;

        if (!empty($condition['item_id'])) {
			$condition['id'] = $condition['item_id'];
    		unset($condition['item_id']);

            $res = $this->finalQuoteItemModel
                ->alias('a')
                ->join("quote_item b ON a.id = b.id", 'LEFT')
                ->field("a.*,b.exw_unit_price AS quote_exw_unit_price,b.exw_unit_price AS q_exw_unit_price,b.quote_unit_price AS q_quote_unit_price")
                ->where(array('a.id' => $condition['id']))->find();

            $this->jsonReturn($res);
        }
    }

    /**
     * @desc 市场修改报价SKU接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function uptateFinalQuoteItemAction() {
        $finalQuoteItem = $condition = $this->put_data;

        if (!empty($condition['item_id'])) {
			$condition['id'] = $condition['item_id'];
    		unset($condition['item_id']);
            $finalQuote = $this->finalQuoteModel->getDetail($condition);
            $where['id'] = $condition['id'];
            $where['quote_no'] = $condition['quote_no'];
            $finalQuoteItem['exw_unit_price'] = $condition['exw_unit_price'];
            $finalQuoteItem['quote_unit_price'] = $finalQuote['total_quote_price']*($finalQuoteItem['exw_unit_price']/$finalQuote['total_exw_price']);

            $res = $this->finalQuoteItemModel->updateItem($where, $finalQuoteItem);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 市场获取报价SKU附件列表接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function getFinalQuoteItemAttachListAction() {
        $condition = $this->put_data;

        $res = $this->finalQuoteItemAttachModel->getAttachList($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 市场添加报价SKU附件接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function addFinalQuoteItemAttachAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {

            $res = $this->finalQuoteItemAttachModel->addAttach($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场删除报价SKU附件接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function delFinalQuoteItemAttachAction() {
        $condition = $this->put_data;

        if (!empty($condition['attach_id'])) {
			$condition['id'] = $condition['attach_id'];
    		unset($condition['attach_id']);

            $res = $this->finalQuoteItemAttachModel->delAttach();

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取报价计算后的数据
     * @author liujf 2017-06-20
     * @param array $condition 条件参数
     * @return array
     */
    private function getCalculateQuoteInfo($condition) {
        $quoteItemList = $this->quoteItemModel->where(array('quote_no' => $condition['quote_no']))->select();

        $exchangeRate = $this->getRateUSD($condition['purchase_cur']);

        $totalWeight = 0;
        $totalPurchasePrice	= 0;
        foreach ($quoteItemList as $quoteItem) {
            $totalWeight += $quoteItem['unit_weight'];
            $itemRate = $this->exchangeRateModel->where(array('currency1' => $quoteItem['purchase_cur'], 'currency2' => $condition['purchase_cur']))->field('rate')->find();

            $exwData[] = array('busyer_unit_price' => $quoteItem['purchase_price'] * $exchangeRate, 'num' => $quoteItem['quote_quantity']);
            $totalPurchasePrice += $quoteItem['total_purchase_price'] * $itemRate['rate'];
        }

        return array('totalWeight' => $totalWeight, 'totalPurchasePrice' => $totalPurchasePrice, 'exchangeRate' => $exchangeRate, 'exwData' => $exwData);
    }

    /**
     * @desc 获取币种兑美元汇率
     * @author liujf 2017-06-20
     * @return float
     */
    private function getRateUSD($cur) {
        $exchangeRate = $this->exchangeRateModel->where(array('currency1' => $cur, 'currency2' => 'USD'))->field('rate')->find();

        return $exchangeRate['rate'];
    }
    
    /**
     * @desc 创建SKU历史报价
     * @author liujf 2017-07-04
     * @return json
     */
    private function createGoodsPriceHis() {
        $condition = $this->put_data;
        
        $where['quote_no'] = $condition['quote_no'];
         
        $finalQuote = $this->finalQuoteModel->where($where)->find();
         
        $finalQuoteItemList = $this->finalQuoteItemModel->where($where)->select();
         
        $goodsPriceHisList = $goodsPriceHis = array();
         
        $time = date('Y-m-d H:i:s');
         
        foreach ($finalQuoteItemList as $finalQuoteItem) {
            $goodsPriceHis['quoter'] = $finalQuote['quoter'];
            $goodsPriceHis['quoter_email'] = $finalQuote['quoter_email'];
            $goodsPriceHis['inquiry_no'] = $finalQuote['inquiry_no'];
            $goodsPriceHis['spu'] = '';
            $goodsPriceHis['sku'] = empty($finalQuoteItem['quote_sku']) ? $finalQuoteItem['inquiry_sku'] : $finalQuoteItem['quote_sku'];
            $goodsPriceHis['name_en'] = $finalQuoteItem['name_en'];
            $goodsPriceHis['name_zh'] = $finalQuoteItem['name_zh'];
            $goodsPriceHis['model'] = $finalQuoteItem['quote_model'];
            $goodsPriceHis['spec'] = $finalQuoteItem['quote_spec'];
            $goodsPriceHis['brand'] = $finalQuoteItem['quote_brand'];
            $goodsPriceHis['quantity'] = $finalQuoteItem['quote_quantity'];
            $goodsPriceHis['unit'] = $finalQuoteItem['quote_unit'];
            $goodsPriceHis['inquiry_desc'] = $finalQuoteItem['inquiry_desc'];
            $goodsPriceHis['quote_desc'] = $finalQuoteItem['quote_desc'];
            $goodsPriceHis['supplier_id'] = $finalQuoteItem['supplier_id'];
            $goodsPriceHis['supplier_contact'] = $finalQuoteItem['supplier_contact'];
            $goodsPriceHis['supplier_contact_email'] = $finalQuoteItem['supplier_contact_email'];
            $goodsPriceHis['supplier_contact_phone'] = $finalQuoteItem['supplier_contact_phone'];
            $goodsPriceHis['purchase_unit_price'] = $finalQuoteItem['purchase_price'];
            $goodsPriceHis['purchase_cur'] = $finalQuoteItem['purchase_cur'];
            $goodsPriceHis['exw_unit_price'] = $finalQuoteItem['exw_unit_price'];
            $goodsPriceHis['exw_cur'] = $finalQuoteItem['exw_cur'];
            $goodsPriceHis['quote_unit_price'] = $finalQuoteItem['quote_unit_price'];
            $goodsPriceHis['quote_cur'] = $finalQuoteItem['quote_cur'];
            $goodsPriceHis['unit_weight'] = $finalQuoteItem['unit_weight'];
            $goodsPriceHis['weight_unit'] = $finalQuoteItem['weight_unit'];
            $goodsPriceHis['package_size'] = $finalQuoteItem['package_size'];
            $goodsPriceHis['size_unit'] = $finalQuoteItem['size_unit'];
            $goodsPriceHis['delivery_period'] = $finalQuoteItem['delivery_period'];
            $goodsPriceHis['period_of_validity'] = $finalQuoteItem['period_of_validity'];
            $goodsPriceHis['rebate_rate'] = $finalQuoteItem['rebate_rate'];
            $goodsPriceHis['quote_notes'] = $finalQuoteItem['quote_notes'];
            $goodsPriceHis['reason_for_no_quote'] = $finalQuoteItem['reason_for_no_quote'];
            $goodsPriceHis['goods_from'] = $finalQuoteItem['goods_from'];
            $goodsPriceHis['status'] = $finalQuoteItem['status'];
            $goodsPriceHis['created_at'] = $time;
    
            $goodsPriceHisList[] = $goodsPriceHis;
        }
    
        return $this->goodsPriceHisModel->addAll($goodsPriceHisList);
         
    }
    
	/**
     * @desc 获取SKU历史报价接口
 	 * @author liujf 2017-07-02
     * @return json
     */
    public function getGoodsPriceHisAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['sku'])) {
    		
    		$res = $this->goodsPriceHisModel->getList($condition);
    		
			$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
	 * @desc 重写jsonReturn方法
	 * @author liujf 2017-06-24
	 */
	public function jsonReturn($data = array(), $type = 'JSON') {
		if ($data) {
			$this->setCode('1');
			$this->setMessage('成功!');
			parent::jsonReturn($data, $type);
		} else {
			$this->setCode('-101');
			$this->setMessage('失败!');
			parent::jsonReturn();
		}
	}
}