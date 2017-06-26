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
    		$quote['logi_quote_status'] = 'ONGOING';
			$quote['biz_quote_status'] = 'ONGOING';
			$quote['quote_status'] = 'ONGOING';
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
     * @desc 商务技术修改报价接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function updateQuoteApiAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['quote_no'])) {
    		
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
			
			$res = $this->quoteModel->save($quote);
    		
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
    public function getQuoteItemApiAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['quote_no'])) {
    		$res = $this->quoteItemModel->getItemList($condition);
			$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
     * @desc 商务技术添加报价SKU接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function addQuoteItemApiAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['quote_no'])) {
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
    	
    	if (isset($condition['id'])) {
    		$quote = $this->quoteModel->getDetail($condition);
    		
    		$quoteItem['quote_no'] = $condition['quote_no'];
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
    	
    	if (isset($condition['id'])) {
    		$res = $this->quoteItemModel->where(array('id' => $condition['id']))->save(array('status' => 'DELETED'));
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
	/**
     * @desc 获取SKU历史报价接口
 	 * @author liujf 2017-06-26
     * @return json
     */
    public function getQuoteItemHistoryApiAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['sku'])) {
    		$sql = "SELECT IF(`quote_sku` <> '', `quote_sku`, `inquiry_sku`) AS `sku` FROM `t_final_quote_item` WHERE `sku` = " . mysql_escape_string($condition['sku']);
    		$res = $this->finalQuoteItemModel->query($sql);
			$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
    /**
     * @desc 物流报价修改接口
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function updateQuoteLogiApiAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['inquiry_no'])) {
    		
    		$quote = $this->quoteModel->getDetail($condition);
    		
    		$logi['inspection_fee'] = $condition['inspection_fee'];
    		$logi['land_freight'] = $condition['land_freight'];
    		$logi['port_surcharge'] = $condition['port_surcharge'];
    		$logi['cargo_insu_rate'] = $condition['cargo_insu_rate'];
    		$logi['inter_shipping'] = $condition['inter_shipping'];
    		$logi['dest_tariff_rate'] = $condition['dest_tariff_rate'];
    		$logi['dest_clearance_fee'] = $condition['dest_clearance_fee'];
    		$logi['dest_delivery_charge'] = $condition['dest_delivery_charge'];
    		$logi['dest_va_tax_rate'] = $condition['dest_va_tax_rate'];
    		$logi['premium_rate'] = $condition['premium_rate'];
    		$logi['est_transport_cycle'] = $condition['est_transport_cycle'];
    		$logi['logi_code'] = $condition['logi_code'];
    		$logi['logi_agent'] = $condition['logi_agent']; //获取当前用户信息
    		$logi['logi_agent_email'] = $condition['logi_agent_email']; //获取当前用户信息
    		$logi['logi_submit_at'] = time();
    		$logi['logi_notes'] = $condition['logi_notes'];
    		$logi['overland_insu_rate'] = round($quote['total_exw_price'] * 0.0002, 8);
    		$logi['inland_marine_insu']	= inlandMarineInsurance(array('total_exw_price' => $quote['total_exw_price'], $logi['overland_insu_rate']));
    		$data = array('trade_terms' => $quote['trade_terms'],
    					  'total_exw_price' => $quote['total_exw_price'],
				    	  'inspection_fee' => $condition['inspection_fee'],
				    	  'premium_rate' => $condition['premium_rate'],
				    	  'payment_received_days' => $quote['payment_received_days'],
				    	  'bank_interest' => $quote['bank_interest'],
				    	  'fund_occupation_rate' => $quote['fund_occupation_rate'],
				    	  'land_freight' => $condition['land_freight'],
				    	  'overland_insu_rate' => $logi['overland_insu_rate'],
				    	  'dest_delivery_charge' => $condition['dest_delivery_charge'],
    					  'dest_tariff_rate' => $condition['dest_tariff_rate'],
				    	  'dest_va_tax_rate' => $condition['dest_va_tax_rate'],
    					  'dest_clearance_fee' => $condition['dest_clearance_fee'],
    		);
    		$logiData = logistics($data);
    		$logi['freightage_insu'] = $logiData['freightage_insu'];
    		$logi['dest_tariff'] = $logiData['dest_tariff'];
    		$logi['dest_va_tax'] = $logiData['dest_va_tax'];
    		$logi['total_insu_fee'] = $logiData['total_insu_fee'];
    		$logi['total_logi_fee'] = $logiData['total_logi_fee'];
    		$logi['total_quote_price'] = $logiData['total_quote_price'];
    		$logi['total_bank_fee'] = $logiData['total_bank_fee'];
    		
    		$res = $this->quoteModel->save($logi);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    }
    
    /**
     * @desc 处理报价相关审核
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function examineAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['inquiry_no'])) {
    		$data = $this->getExamine($condition);
    		
    		$res = $this->quoteModel->save($data);
    		
    		$this->jsonReturn($res);
    	}
    }
    
	
	/**
     * @desc 修改最终报价单
 	 * @author liujf 2017-06-21
     * @return json
     */
    public function updateFinalQuoteAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['inquiry_no'])) {
    		
    		$inquiry = $this->inquiryModel->where(array('inquiry_no' => $condition['inquiry_no']))->find();
    		
			$finalQuote['quoter'] = $inquiry['agent'];
			$finalQuote['quoter_email'] = $inquiry['agent_email'];
			$finalQuote['quote_at'] = time();
			$finalQuote['quote_notes'] = $condition['quote_notes'];
			
			$res = $this->finalQuoteModel->save($finalQuote);
    		
    		$this->jsonReturn($res);
    	}
    	
    }
    
    
    
    
	/**
     * @desc 修改最终报价单项目
 	 * @author liujf 2017-06-21
     * @return json
     */
    public function uptateFinalQuoteItemAction() {
    	
    	
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
     * @desc 获取审核数据
 	 * @author liujf 2017-06-21
 	 * @param array $condition 条件参数
     * @return array
     */
    private function getExamine($condition) {
    	$data = array();
    	
    	switch ($condition['examine_type']) { // 审核类型： logi(物流) 、biz(商务) 、quote(报价)
    		case 'logi' : $data['logi_quote_status'] = $condition['status'];
    					  break;
    		case 'biz' : $data['biz_quote_status'] = $condition['status'];
    					  break;
    		case 'quote' : $data['quote_status'] = $condition['status'];
    					   $data['checker'] = $condition['checker'];
						   $data['checker_email'] = $condition['checker_email'];
						   $data['check_at'] = time();
						   $data['check_notes'] = $condition['check_notes'];
						   
						   $this->afterExamine($condition);
    	}
    	
    	return $data;
    	
    }
    
	/**
     * @desc 报价单审核通过后的操作
 	 * @author liujf 2017-06-21
 	 * @param array $condition 条件参数
     * @return array
     */
    private function afterExamine($condition) {
    	
    	if ($condition['status'] == 'APPROVED') { // 审核通过
    		$quote = $this->quoteModel->getDetail($condition);
	    	$this->finalQuoteModel->add($quote);
	    	
	    	$quoteItemList = $this->quoteItemModel->getItemList($condition);
	    	$this->finalQuoteItemModel->addAll($quoteItemList);
	    	
	    	$quoteAttachList = $this->quoteAttachModel->getAttachList($condition);
	    	$this->finalQuoteAttachModel->addAll($quoteAttachList);
	    	
	    	$quoteItemAttachList = $this->quoteItemAttachModel->getAttachList($condition);
	    	$this->finalQuoteItemModel->addAll($quoteItemAttachList);
    	}
    	
    }
    
    /**
     * @desc 获取当前用户信息
 	 * @author liujf 2017-06-26
 	 * @return array
     */
    public function getUserInfo() {
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