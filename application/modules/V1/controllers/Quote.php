<?php
/**
 * @desc 报价单控制器
 * @author liujf 2017-06-17
 */
class QuoteController extends PublicController {

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
        $this->userModel = new UserModel();
        $this->goodsPriceHisModel = new GoodsPriceHisModel();
	}
	
	/**
     * @desc 报价总体信息接口
     * @author liujf 2017-06-17
     * @return json
     */
    public function QuoteDetailAction() {
    	$condition = $this->put_data;
        $res = $this->quoteModel->getDetail($condition);
        
    	$this->jsonReturn($res);
    }
    
	/**
     * @desc SKU报价信息接口
     * @author liujf 2017-06-17
     * @return json
     */
    public function QuoteSkuDetailAction() {
    	$condition = $this->put_data;
        $res = $this->quoteItemModel->getDetail($condition['inquiry_no']);
        
    	$this->jsonReturn($res);
    }
    
    /**
     * @desc 开始报价接口
     * @author liujf 2017-06-23
     * @return json
     */
    public function startQuoteApiAction() {
    	//var_dump($this->user);exit;
    	//$data = $this->inquiryModel->getLastSql();
    	//var_dump($data);exit;
    	//$this->jsonReturn($res);
    	
    	$this->createQuote();

    }
    
	/**
     * @desc 创建报价单
 	 * @author liujf 2017-06-24
     * @return mix
     */
    private function createQuote() {
    	
    	$condition = $this->put_data;
    	
    	$serial_no_arr = explode(',', $condition['serial_no']);
    	
    	$whereQuote = $where = array('serial_no' => array('in', $serial_no_arr));
    	
    	$whereQuote['quote_status'] = 'NOT_QUOTED';
    	
    	$inquiryList = $this->inquiryModel->where($where)->select();
    	
    	$quoteList = $correspond = array();
    	
    	$user = $this->getUserInfo();
    	
    	$time = time();
    	
    	foreach ($inquiryList as $inquiry) {
    		$quote['serial_no'] = $this->getQuoteSerialNo(); // 报价单流水号
    		$quote['quote_no'] = $this->getQuoteNo(); // 报价单号
    		$quote['inquiry_no'] = $inquiry['inquiry_no'];
    		$quote['quote_lang'] = 'zh';
    		$quote['trade_terms'] = $inquiry['trade_terms'];
    		$quote['payment_received_days'] = '';
    		$quote['exw_delivery_period'] = '';
    		$quote['period_of_validity'] = '';
    		$quote['logi_quote_status'] = 'NOT_QUOTED';
			$quote['biz_quote_status'] = 'NOT_QUOTED';
			$quote['quote_status'] = 'NOT_QUOTED';
			$quote['quoter'] = $user['name']; //获取当前用户信息
			$quote['quoter_email'] = $user['email']; //获取当前用户信息
			$quote['quote_at'] = $time;
			$quote['created_by'] = $user['name'];
			$quote['created_at'] = $time;
			
			$correspond[$inquiry['serial_no']] = $quote['quote_no']; //询单流水号和报价单号的对应
			
			$quoteList[] = $quote;
    	}
		
		if ($this->quoteModel->addAll($quoteList)) {
			$this->createQuoteItem($where, $correspond);
			$this->createQuoteAttach($where, $correspond);
			$this->createQuoteItemAttach($where, $correspond);
			
			$this->jsonReturn(true);
		} else {
			$this->jsonReturn(false);
		}
    
    }
    
	/**
     * @desc 创建报价单项目
 	 * @author liujf 2017-06-24
 	 * @param array $where 报价单号查询条件
 	 * @param array $correspond 询单号和报价单号的对应
     * @return json
     */
    private function createQuoteItem($where, $correspond) {
    	
    	$quoteItemList = $quoteItem = array();
    	
    	$inquiryItemList = $this->inquiryItemModel->where($where)->select();
    	
    	foreach ($inquiryItemList as $inquiryItem) {
    		$quoteItem['quote_no'] = $correspond[$inquiryItem['serial_no']];
    		$quoteItem['inquiry_sku'] = $inquiryItem['sku'];
    		$quoteItem['inquiry_item_id'] = $inquiryItem['id'];
    		$quoteItem['buyer_sku'] = '';
    		$quoteItem['quote_sku'] = '';
    		$quoteItem['name_en'] = $inquiryItem['name_en'];
    		$quoteItem['name_cn'] = $inquiryItem['name_cn'];
    		$quoteItem['quote_model'] = $inquiryItem['model'];
    		$quoteItem['quote_spec'] = $inquiryItem['spec'];
    		$quoteItem['quote_brand'] = $inquiryItem['brand'];
    		$quoteItem['quote_quantity'] = $inquiryItem['quantity'];
    		$quoteItem['quote_unit'] = $inquiryItem['unit'];
    		$quoteItem['inquiry_desc'] = $inquiryItem['description'];
    		$quoteItem['status'] = 'ONGOING';
    		$quoteItem['created_at'] = time();
			
			$quoteItemList[] = $quoteItem;
    	
    	}
    	
    	return $this->quoteItemModel->addAll($quoteItemList);
    	
    }
    
	/**
     * @desc 创建报价单附件
 	 * @author liujf 2017-06-24
     */
    private function createQuoteAttach($where, $correspond) {
    	
    	$quoteAttachList = $quoteAttach = array();
    	
    	$inquiryAttachList = $this->inquiryAttachModel->where($where)->select();
    	
    	foreach ($inquiryAttachList as $inquiryAttach) {
    		$quoteAttach['quote_no'] = $correspond[$inquiryAttach['serial_no']];
    		$quoteAttach['attach_group'] = $inquiryAttach['attach_group'];
    		$quoteAttach['attach_type'] = $inquiryAttach['attach_type'];
    		$quoteAttach['attach_name'] = $inquiryAttach['attach_name'];
    		$quoteAttach['attach_url'] = $inquiryAttach['attach_url'];
    		
    		$quoteAttachList[] = $quoteAttach;
    	}
    	
    	return $this->quoteAttachModel->addAll($quoteAttachList);
    }
    
	/**
     * @desc 创建报价单项目附件
 	 * @author liujf 2017-06-24
     */
    private function createQuoteItemAttach($where, $correspond) {
    	
    	$quoteItemAttachList = $quoteItemAttach = array();
    	
    	$inquiryItemAttachList = $this->inquiryItemAttachModel->where($where)->select();
    	
    	foreach ($inquiryItemAttachList as $inquiryItemAttach) {
    		$quoteItemAttach['quote_no'] = $correspond[$inquiryItemAttach['serial_no']];
    		$quoteItemAttach['quote_sku'] = $inquiryItemAttach['sku'];
    		$quoteItemAttach['attach_type'] = $inquiryItemAttach['attach_type'];
    		$quoteItemAttach['attach_name'] = $inquiryItemAttach['attach_name'];
    		$quoteItemAttach['attach_url'] = $inquiryItemAttach['attach_url'];
    		
    		$quoteItemAttachList[] = $quoteItemAttach;
    	}
    	
    	return $this->quoteItemAttachModel->addAll($quoteItemAttachList);
    }
    
    /**
     * @desc 商务技术获取报价列表接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function getQuoteListApiAction() {
    	
    	$condition['biz_quote_status'] = 'ONGOING';
    	
    	$data = $this->quoteModel->getList($condition);
    	
    	if ($data) {
    		$res['code'] = 1;
    		$res['message'] = '成功!';
    		$res['data'] = $data;
    		$res['count'] = $this->quoteModel->getCount($condition);
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    }
    
	/**
     * @desc 商务技术获取报价详情接口
 	 * @author liujf 2017-06-28
     * @return json
     */
    public function getQuoteDetailApiAction() {
    	$condition = $this->put_data;
    	
    	$res = $this->quoteModel->getDetail($condition);
        
    	$this->jsonReturn($res);
    }
    
    /**
     * @desc 商务技术修改报价接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function updateQuoteApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['quote_no'])) {
    		
    		$user = $this->getUserInfo();
    		
    		$calculateQuoteInfo = $this->getCalculateQuoteInfo($condition);
    		
    		$quote['package_volumn'] = $condition['package_volumn'];
    		$quote['size_unit'] = 'm^3';
    		$quote['package_mode'] = $condition['package_mode'];
    		$quote['origin_place'] = $condition['origin_place'];
    		$quote['destination'] = $condition['destination'];
    		$quote['gross_profit_rate'] = $condition['gross_profit_rate'];
    		$quote['payment_received_days'] = strtotime($condition['payment_received_days']);
    		$quote['exw_delivery_period'] = strtotime($condition['exw_delivery_period']);
    		$quote['period_of_validity'] = strtotime($condition['period_of_validity']);
    		$quote['purchase_cur'] = $condition['purchase_cur'];
    		$quote['bank_interest'] = $condition['bank_interest'];
    		$quote['fund_occupation_rate'] = $condition['fund_occupation_rate'];
    		$quote['payment_mode'] = $condition['payment_mode'];
    		$quote['total_weight'] = $calculateQuoteInfo['$totalWeight'];
    		$quote['weight_unit'] = 'kg';
    		$quote['exchange_rate'] = $calculateQuoteInfo['exchangeRate'];
			$quote['total_purchase_price'] = $calculateQuoteInfo['totalPurchasePrice'];
			$exw = exw($calculateQuoteInfo['exwData'], $condition['gross_profit_rate']);
			$quote['total_exw_price'] = $exw['total'];
			$quote['total_exw_cur'] = 'USD';
			$quote['total_quote_cur'] = 'USD';
			$quote['total_logi_fee_cur'] = 'USD';
			$quote['total_bank_fee_cur'] = 'USD';
			$quote['total_insu_fee_cur'] = 'USD';
			$quote['quoter'] = $user['name'];
			$quote['quoter_email'] = $user['email'];
			$quote['quote_at'] = time();
			$quote['quote_notes'] = $condition['quote_notes'];
			
			$res = $this->quoteModel->where(array('quote_no' => $condition['quote_no']))->save($quote);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
    /**
     * @desc 商务技术获取报价SKU列表接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function getQuoteItemListApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['quote_no'])) {
    		if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    			$data = $this->quoteItemModel
	    					->alias('a')
	    					->join("final_quote_item b ON a.id = b.id", 'LEFT')
	    					->field("a.*,b.quote_unit_price AS final_quote_unit_price")
	    					->where(array('a.quote_no' => $condition['quote_no']))->page($condition['currentPage'], $condition['pageSize'])->select();
    		} else {
    			$data = $this->quoteItemModel
	    					->alias('a')
	    					->join("final_quote_item b ON a.id = b.id", 'LEFT')
	    					->field("a.*,b.quote_unit_price AS final_quote_unit_price")
	    					->where(array('a.quote_no' => $condition['quote_no']))->select();
    		}
    		
    		if ($data) {
	    		$res['code'] = 1;
	    		$res['message'] = '成功!';
	    		$res['data'] = $data;
	    		$res['count'] = $this->quoteItemModel->getCount($condition);
	    		$this->jsonReturn($res);
	    	} else {
	    		$this->jsonReturn(false);
	    	}
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
     * @desc 商务技术获取报价SKU详情接口
 	 * @author liujf 2017-06-28
     * @return json
     */
    public function getQuoteItemDetailApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['id'])) {
    		$res = $this->quoteItemModel
	    				->alias('a')
	    				->join("final_quote_item b ON a.id = b.id", 'LEFT')
	    				->field("a.*,b.quote_unit_price AS final_quote_unit_price")
	    				->where(array('a.quote_no' => $condition['id']))->find();
    				    
    		$this->jsonReturn($res);
    	}
    }
    
	/**
     * @desc 商务技术添加报价SKU接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function addQuoteItemApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['quote_no'])) {
    		$quote = $this->quoteModel->getDetail($condition);
    		
    		$quoteItem['quote_no'] = $condition['quote_no'];
    		$quoteItem['inquiry_sku'] = '';
    		$quoteItem['buyer_sku'] = '';
    		$quoteItem['quote_sku'] = $condition['sku'];
    		$quoteItem['name_en'] = $condition['name_en'];
    		$quoteItem['name_cn'] = $condition['name_cn'];
    		$quoteItem['quote_model'] = $condition['quote_model'];
    		$quoteItem['quote_spec'] = $condition['quote_spec'];
    		$quoteItem['quote_brand'] = $condition['quote_brand'];
    		$quoteItem['quote_quantity'] = $condition['quote_quantity'];
    		$quoteItem['quote_unit'] = $condition['quote_unit'];
    		$quoteItem['inquiry_desc'] = $condition['description'];
    		$quoteItem['status'] = 'ONGOING';
    		$quoteItem['created_at'] = time();
    		
    		$quoteItem['goods_from'] = $condition['goods_from'];
    		$quoteItem['supplier_id'] = $condition['supplier_id'];
    		$quoteItem['supplier_contact'] = $condition['supplier_contact'];
    		$quoteItem['supplier_contact_email'] = $condition['supplier_contact_email'];
    		$quoteItem['supplier_contact_phone'] = $condition['supplier_contact_phone'];
    		$quoteItem['purchase_price'] = $condition['purchase_price'];
    		$quoteItem['total_purchase_price'] = round($condition['purchase_price'] * $quoteItem['quote_quantity'], 8);
    		$quoteItem['purchase_cur'] = $condition['purchase_cur'];
    		
    		$exchangeRate = $this->getRateUSD($condition['purchase_cur']);
    		
    		if ($quote['gross_profit_rate'] != '') {
    			$quoteItem['exw_unit_price'] = round($condition['purchase_price'] * $quote['gross_profit_rate'] / $exchangeRate, 8); 
    			$quoteItem['total_exw_price'] = $quoteItem['exw_unit_price'] * $quoteItem['quote_quantity'];
    		}
    		
    		$quoteItem['exw_cur'] = 'USD';
    		
    		if ($quote['total_quote_price'] != '') {
    			$data = array('total_quote_price' => $quote['total_quote_price'],
			    		      'total_exw_price' => $quote['total_exw_price'],
			    		      'exw_unit_price' => $quoteItem['exw_unit_price']
    			);
    			$quoteArr = quoteUnitPrice($data);
    			$quoteItem['quote_unit_price'] = $quoteArr['quote_unit_price'];
    			$quoteItem['total_quote_price'] = $quoteArr['quote_unit_price'] * $quoteItem['quote_quantity']; 
    		}
    		
    		$quoteItem['quote_cur'] = 'USD';
    		$quoteItem['unit_weight'] = $condition['unit_weight'];
    		$quoteItem['weight_unit'] = 'kg';
    		$quoteItem['package_size'] = $condition['package_size'];
    		$quoteItem['size_unit'] = 'm^3';
    		$quoteItem['delivery_period'] = $condition['delivery_period'];
    		$quoteItem['reason_for_no_quote'] = $condition['reason_for_no_quote'];
    		$quoteItem['rebate_rate'] = $condition['rebate_rate'];
    		$quoteItem['quote_notes'] = $condition['quote_notes'];
    		$quoteItem['period_of_validity'] = $condition['period_of_validity'];
    		
    		$res = $this->quoteItemModel->where($where)->add($quoteItem);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
     * @desc 商务技术修改报价SKU接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function uptateQuoteItemApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['id'])) {
    		$quote = $this->quoteModel->getDetail($condition);
    		
    		$quoteItem['buyer_sku'] = '';
    		$quoteItem['quote_sku'] = $condition['sku'];
    		$quoteItem['name_en'] = $condition['name_en'];
    		$quoteItem['name_cn'] = $condition['name_cn'];
    		$quoteItem['quote_model'] = $condition['quote_model'];
    		$quoteItem['quote_spec'] = $condition['quote_spec'];
    		$quoteItem['quote_brand'] = $condition['quote_brand'];
    		$quoteItem['quote_quantity'] = $condition['quote_quantity'];
    		$quoteItem['quote_unit'] = $condition['quote_unit'];
    		$quoteItem['inquiry_desc'] = $condition['description'];
    		
    		$quoteItem['quote_sku'] = $condition['quote_sku'];
    		$quoteItem['goods_from'] = $condition['goods_from'];
    		$quoteItem['supplier_id'] = $condition['supplier_id'];
    		$quoteItem['supplier_contact'] = $condition['supplier_contact'];
    		$quoteItem['supplier_contact_email'] = $condition['supplier_contact_email'];
    		$quoteItem['supplier_contact_phone'] = $condition['supplier_contact_phone'];
    		$quoteItem['purchase_price'] = $condition['purchase_price'];
    		$quoteItem['total_purchase_price'] = round($condition['purchase_price'] * $quoteItem['quote_quantity'], 8);
    		$quoteItem['purchase_cur'] = $condition['purchase_cur'];
    		
    		$exchangeRate = $this->getRateUSD($condition['purchase_cur']);
    		
    		if ($quote['gross_profit_rate'] != '') {
    			$quoteItem['exw_unit_price'] = round($condition['purchase_price'] * $quote['gross_profit_rate'] / $exchangeRate, 8); 
    			$quoteItem['total_exw_price'] = $quoteItem['exw_unit_price'] * $quoteItem['quote_quantity'];
    		}
    		
    		$quoteItem['exw_cur'] = 'USD';
    		
    		if ($quote['total_quote_price'] != '') {
    			$data = array('total_quote_price' => $quote['total_quote_price'],
			    		      'total_exw_price' => $quote['total_exw_price'],
			    		      'exw_unit_price' => $quoteItem['exw_unit_price']
    			);
    			$quoteArr = quoteUnitPrice($data);
    			$quoteItem['quote_unit_price'] = $quoteArr['quote_unit_price'];
    			$quoteItem['total_quote_price'] = $quoteArr['quote_unit_price'] * $quoteItem['quote_quantity']; 
    		}
    		
    		$quoteItem['quote_cur'] = 'USD';
    		$quoteItem['unit_weight'] = $condition['unit_weight'];
    		$quoteItem['weight_unit'] = 'kg';
    		$quoteItem['package_size'] = $condition['package_size'];
    		$quoteItem['size_unit'] = 'm^3';
    		$quoteItem['delivery_period'] = $condition['delivery_period'];
    		$quoteItem['reason_for_no_quote'] = $condition['reason_for_no_quote'];
    		$quoteItem['rebate_rate'] = $condition['rebate_rate'];
    		$quoteItem['quote_notes'] = $condition['quote_notes'];
    		$quoteItem['period_of_validity'] = $condition['period_of_validity'];
    		
    		$res = $this->quoteItemModel->where(array('id' => $condition['id']))->save($quoteItem);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
    /**
     * @desc 商务技术删除报价SKU接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function deleteQuoteItemApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['id'])) {
    		$res = $this->quoteItemModel->where(array('id' => $condition['id']))->save(array('status' => 'DELETED'));
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
     * @desc 市场获取可修改报价列表接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function getFinalQuoteListApiAction() {
    	
    	$condition['quote_status'] = 'APPROVING';
    	
    	$data = $this->finalQuoteModel->getList($condition);
    	
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
    public function getFinalQuoteDetailApiAction() {
    	$condition = $this->put_data;
    	
    	$res = $this->finalQuoteModel->getDetail($condition);
        
    	$this->jsonReturn($res);
    }
    
	/**
     * @desc 市场获取报价SKU列表接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function getFinalQuoteItemListApiAction() {
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
    public function getFinalQuoteItemDetailApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['id'])) {
    		
    		$res = $this->finalQuoteItemModel
    			        ->alias('a')
    				    ->join("quote_item b ON a.id = b.id", 'LEFT')
    				    ->field("a.*,b.exw_unit_price AS quote_exw_unit_price,b.exw_unit_price AS q_exw_unit_price,b.quote_unit_price AS q_quote_unit_price")
    				    ->where(array('a.id' => $condition['id']))->find();
    				    
    		$this->jsonReturn($res);
    	}
    }
	
	/**
     * @desc 市场修改报价接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function updateFinalQuoteApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['quote_no'])) {
    		
    		$user = $this->getUserInfo();
    		
    		$quote = $this->quoteModel->where(array('quote_no' => $condition['quote_no']))->find();
    		
    		$inquiry = $this->inquiryModel->where(array('inquiry_no' => $quote['inquiry_no']))->find();
    		
    		$calculateQuoteInfo = $this->getCalculateQuoteInfo($condition);
    		
    		$finalQuote['package_volumn'] = $condition['package_volumn'];
    		$finalQuote['size_unit'] = 'm^3';
    		$finalQuote['package_mode'] = $condition['package_mode'];
    		$finalQuote['origin_place'] = $condition['origin_place'];
    		$finalQuote['destination'] = $condition['destination'];
    		$finalQuote['gross_profit_rate'] = $condition['gross_profit_rate'];
    		$finalQuote['payment_received_days'] = strtotime($condition['payment_received_days']);
    		$finalQuote['exw_delivery_period'] = strtotime($condition['exw_delivery_period']);
    		$finalQuote['period_of_validity'] = strtotime($condition['period_of_validity']);
    		$finalQuote['purchase_cur'] = $condition['purchase_cur'];
    		$finalQuote['bank_interest'] = $condition['bank_interest'];
    		$finalQuote['fund_occupation_rate'] = $condition['fund_occupation_rate'];
    		$finalQuote['payment_mode'] = $condition['payment_mode'];
    		$finalQuote['total_weight'] = $calculateQuoteInfo['$totalWeight'];
    		$finalQuote['weight_unit'] = 'kg';
    		$finalQuote['exchange_rate'] = $calculateQuoteInfo['exchangeRate'];
			$finalQuote['total_purchase_price'] = $calculateQuoteInfo['totalPurchasePrice'];
			$exw = exw($calculateQuoteInfo['exwData'], $condition['gross_profit_rate']);
			$finalQuote['total_exw_price'] = $exw['total'];
			$finalQuote['total_exw_cur'] = 'USD';
			$finalQuote['total_quote_cur'] = 'USD';
			$finalQuote['total_logi_fee_cur'] = 'USD';
			$finalQuote['total_bank_fee_cur'] = 'USD';
			$finalQuote['total_insu_fee_cur'] = 'USD';
			$finalQuote['quoter'] = $inquiry['agent'];
			$finalQuote['quoter_email'] = $inquiry['agent_email'];
			$finalQuote['quote_at'] = time();
			$finalQuote['quote_notes'] = $condition['quote_notes'];
			
			$res = $this->finalQuoteModel->where(array('quote_no' => $condition['quote_no']))->save($finalQuote);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
     * @desc 市场修改报价SKU接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function uptateFinalQuoteItemApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['id'])) {
    		$finalQuote = $this->finalQuoteModel->getDetail($condition);
    		
    		$finalQuoteItem['quote_no'] = $condition['quote_no'];
    		$finalQuoteItem['buyer_sku'] = '';
    		$finalQuoteItem['quote_sku'] = $condition['sku'];
    		$finalQuoteItem['name_en'] = $condition['name_en'];
    		$finalQuoteItem['name_cn'] = $condition['name_cn'];
    		$finalQuoteItem['quote_model'] = $condition['quote_model'];
    		$finalQuoteItem['quote_spec'] = $condition['quote_spec'];
    		$finalQuoteItem['quote_brand'] = $condition['quote_brand'];
    		$finalQuoteItem['quote_quantity'] = $condition['quote_quantity'];
    		$finalQuoteItem['quote_unit'] = $condition['quote_unit'];
    		$finalQuoteItem['inquiry_desc'] = $condition['description'];
    		
    		$finalQuoteItem['quote_sku'] = $condition['quote_sku'];
    		$finalQuoteItem['goods_from'] = $condition['goods_from'];
    		$finalQuoteItem['supplier_id'] = $condition['supplier_id'];
    		$finalQuoteItem['supplier_contact'] = $condition['supplier_contact'];
    		$finalQuoteItem['supplier_contact_email'] = $condition['supplier_contact_email'];
    		$finalQuoteItem['supplier_contact_phone'] = $condition['supplier_contact_phone'];
    		$finalQuoteItem['purchase_price'] = $condition['purchase_price'];
    		$finalQuoteItem['total_purchase_price'] = round($condition['purchase_price'] * $finalQuoteItem['quote_quantity'], 8);
    		$finalQuoteItem['purchase_cur'] = $condition['purchase_cur'];
    		
    		$exchangeRate = $this->getRateUSD($condition['purchase_cur']);
    		
    		if ($finalQuote['gross_profit_rate'] != '') {
    			$finalQuoteItem['exw_unit_price'] = round($condition['purchase_price'] * $finalQuote['gross_profit_rate'] / $exchangeRate, 8); 
    			$finalQuoteItem['total_exw_price'] = $finalQuoteItem['exw_unit_price'] * $finalQuoteItem['quote_quantity'];
    		}
    		
    		$finalQuoteItem['exw_cur'] = 'USD';
    		
    		if ($finalQuote['total_quote_price'] != '') {
    			$data = array('total_quote_price' => $finalQuote['total_quote_price'],
			    		      'total_exw_price' => $finalQuote['total_exw_price'],
			    		      'exw_unit_price' => $finalQuoteItem['exw_unit_price']
    			);
    			$quoteArr = quoteUnitPrice($data);
    			$finalQuoteItem['quote_unit_price'] = $quoteArr['quote_unit_price'];
    			$finalQuoteItem['total_quote_price'] = $quoteArr['quote_unit_price'] * $finalQuoteItem['quote_quantity']; 
    		}
    		
    		$finalQuoteItem['quote_cur'] = 'USD';
    		$finalQuoteItem['unit_weight'] = $condition['unit_weight'];
    		$finalQuoteItem['weight_unit'] = 'kg';
    		$finalQuoteItem['package_size'] = $condition['package_size'];
    		$finalQuoteItem['size_unit'] = 'm^3';
    		$finalQuoteItem['delivery_period'] = $condition['delivery_period'];
    		$finalQuoteItem['reason_for_no_quote'] = $condition['reason_for_no_quote'];
    		$finalQuoteItem['rebate_rate'] = $condition['rebate_rate'];
    		$finalQuoteItem['quote_notes'] = $condition['quote_notes'];
    		$finalQuoteItem['period_of_validity'] = $condition['period_of_validity'];
    		
    		$res = $this->finalQuoteItemModel->where(array('id' => $condition['id']))->save($finalQuoteItem);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    }
    
	/**
     * @desc 商务技术获取报价附件列表接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function getQuoteAttachListApiAction() {
        $condition = $this->put_data;

        $res = $this->quoteAttachModel->getAttachList($condition);
        
        $this->jsonReturn($res);
    }
    
	/**
     * @desc 商务技术添加报价附件接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function addQuoteAttachApiAction() {
        $condition = $this->put_data;
        
        if (!empty($condition['quote_no'])) {
        	$data['quote_no'] = $condition['quote_no'];
        	$data['attach_group'] = $condition['attach_group'];
        	$data['attach_type'] = $condition['attach_type'];
        	$data['attach_name'] = $condition['attach_name'];
        	$data['attach_url'] = $condition['attach_url'];
        	
        	$res = $this->quoteAttachModel->add($data);
        	
        	$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
         
    }

    /**
     * @desc 商务技术删除报价附件接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function deleteQuoteAttachApiAction() {
    	$condition = $this->put_data;
        
        if (!empty($condition['id'])) {
        	
        	$res = $this->quoteAttachModel->where(array('id' => $condition['id']))->delete();
        	
        	$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    }
    
	/**
     * @desc 商务技术获取报价SKU附件列表接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function getQuoteItemAttachListApiAction() {
        $condition = $this->put_data;

        $res = $this->quoteItemAttachModel->getAttachList($condition);
        
        $this->jsonReturn($res);
    }
    
	/**
     * @desc 商务技术添加报价SKU附件接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function addQuoteItemAttachApiAction() {
        $condition = $this->put_data;
        
        if (!empty($condition['quote_no'])) {
        	$data['quote_no'] = $condition['quote_no'];
        	$data['attach_group'] = $condition['attach_group'];
        	$data['attach_type'] = $condition['attach_type'];
        	$data['attach_name'] = $condition['attach_name'];
        	$data['attach_url'] = $condition['attach_url'];
        	
        	$res = $this->quoteItemAttachModel->add($data);
        	
        	$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
         
    }

    /**
     * @desc 商务技术删除报价SKU附件接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function deleteQuoteItemAttachApiAction() {
    	$condition = $this->put_data;
        
        if (!empty($condition['id'])) {
        	
        	$res = $this->quoteItemAttachModel->where(array('id' => $condition['id']))->delete();
        	
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
    public function getFinalQuoteAttachListApiAction() {
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
        	$data['quote_no'] = $condition['quote_no'];
        	$data['attach_group'] = $condition['attach_group'];
        	$data['attach_type'] = $condition['attach_type'];
        	$data['attach_name'] = $condition['attach_name'];
        	$data['attach_url'] = $condition['attach_url'];
        	
        	$res = $this->finalQuoteAttachModel->add($data);
        	
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
    public function deleteFinalQuoteAttachApiAction() {
    	$condition = $this->put_data;
        
        if (!empty($condition['id'])) {
        	
        	$res = $this->finalQuoteAttachModel->where(array('id' => $condition['id']))->delete();
        	
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
    public function getFinalQuoteItemAttachListApiAction() {
        $condition = $this->put_data;

        $res = $this->finalQuoteItemAttachModel->getAttachList($condition);
        
        $this->jsonReturn($res);
    }
    
	/**
     * @desc 市场添加报价SKU附件接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function addFinalQuoteItemAttachApiAction() {
        $condition = $this->put_data;
        
        if (!empty($condition['quote_no'])) {
        	$data['quote_no'] = $condition['quote_no'];
        	$data['attach_group'] = $condition['attach_group'];
        	$data['attach_type'] = $condition['attach_type'];
        	$data['attach_name'] = $condition['attach_name'];
        	$data['attach_url'] = $condition['attach_url'];
        	
        	$res = $this->finalQuoteItemAttachModel->add($data);
        	
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
    public function deleteFinalQuoteItemAttachApiAction() {
    	$condition = $this->put_data;
        
        if (!empty($condition['id'])) {
        	
        	$res = $this->finalQuoteItemAttachModel->where(array('id' => $condition['id']))->delete();
        	
        	$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    }
    
	/**
     * @desc 获取SKU历史报价接口
 	 * @author liujf 2017-06-27
     * @return json
     */
    public function getGoodsPriceHisApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['sku'])) {
    		
    		$res = $this->goodsPriceHisModel->getList($condition);
    		
			$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
     * @desc 创建SKU历史报价
 	 * @author liujf 2017-06-27
     * @return json
     */
    private function createGoodsPriceHis() {
    	$condition = $this->put_data;
    	
    	
    	$finalQuote = $this->finalQuoteModel->where(array('quote_no' => $condition['quote_no']))->find();
    	
    	$finalQuoteItemList = $this->finalQuoteItemModel->where(array('quote_no' => $condition['quote_no']))->select();
    	
    	$goodsPriceHisList = $goodsPriceHis = array();
    	
    	$time = time();
    	
    	foreach ($finalQuoteItemList as $finalQuoteItem) {
    		$goodsPriceHis['quoter'] = $finalQuote['quoter'];
	    	$goodsPriceHis['quoter_email'] = $finalQuote['quoter'];
	    	$goodsPriceHis['inquiry_no'] = $finalQuote['quoter'];
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
     * @desc 处理报价相关审核接口
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function examineApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['quote_no'])) {
    		$data = $this->getExamine($condition);
    		
    		$res = $this->quoteModel->where(array('quote_no' => $condition['quote_no']))->save($data);
    		
    		if ($condition['examine_type'] == 'quote') $this->afterExamine($condition);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    }
    
	/**
     * @desc 获取审核数据
 	 * @author liujf 2017-06-21
 	 * @param array $condition 条件参数
     * @return array
     */
    private function getExamine($condition) {
    	$data = array();
    	
    	switch ($condition['examine_type']) { // 审核类型： logi(物流) 、biz(商务) 、quote(报价)
    		case 'logi'  : $data['logi_quote_status'] = $condition['status'];
    					   break;
    		case 'biz'   : $data['biz_quote_status'] = $condition['status'];
    					   break;
    		case 'quote' : $user = $this->getUserInfo();
    					   $data['quote_status'] = $condition['status'];
    					   $data['checker'] = $user['name'];
						   $data['checker_email'] = $user['email'];
						   $data['check_at'] = time();
						   $data['check_notes'] = $condition['check_notes'];
    	}
    	
    	return $data;
    	
    }
    
	/**
     * @desc 审核通过后的操作
 	 * @author liujf 2017-06-21
 	 * @param array $condition 条件参数
     * @return array
     */
    private function afterExamine($condition) {
    	
    	if ($condition['status'] == 'APPROVED') { // 报价完成
    		$quote = $this->quoteModel->getDetail($condition);
	    	$this->finalQuoteModel->add($quote);
	    	
	    	$quoteItemList = $this->quoteItemModel->getItemList($condition);
	    	$this->finalQuoteItemModel->addAll($quoteItemList);
	    	
	    	$quoteAttachList = $this->quoteAttachModel->getAttachList($condition);
	    	$this->finalQuoteAttachModel->addAll($quoteAttachList);
	    	
	    	$quoteItemAttachList = $this->quoteItemAttachModel->getAttachList($condition);
	    	$this->finalQuoteItemModel->addAll($quoteItemAttachList);
	    	
	    	$this->createGoodsPriceHis($condition);
    	}
    	
    }
    
    /**
     * @desc 获取当前用户信息
 	 * @author liujf 2017-06-26
 	 * @return array
     */
    private function getUserInfo() {
    	return $this->userModel->where(array('id' => $this->user['id']))->field('name,email')->find();
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