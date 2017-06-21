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
        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->exchangeRateModel = new ExchangeRateModel();
	}
	
	/**
     * @desc 报价总体信息接口
     * @author liujf 2017-06-17
     * @return json
     */
    public function QuoteDetailAction() {
    	$condition = $this->put_data;
        $res = $this->quoteModel->getInfo($condition['inquiry_no']);
        
    	$this->jsonOutput($res);
    }
    
	/**
     * @desc SKU报价信息接口
     * @author liujf 2017-06-17
     * @return json
     */
    public function QuoteSkuDetailAction() {
    	$condition = $this->put_data;
        $res = $this->quoteItemModel->getDetail($condition['inquiry_no']);
        
    	$this->jsonOutput($res);
    }
    
	/**
     * @desc 创建报价单
 	 * @author liujf 2017-06-17
     * @return json
     */
    public function createQuoteAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['inquiry_no'])) {
    		$inquiry = $this->inquiryModel->where(array('inquiry_no' => $condition['inquiry_no']))->find();
    		
    		$calculateQuoteInfo = $this->getCalculateQuoteInfo($condition);

			$time = time();
    		
    		$quote['serial_no'] = $this->getQuoteSerialNo();
    		$quote['quote_no'] = '';
    		$quote['inquiry_no'] = $condition['inquiry_no'];
    		$quote['quote_lang'] = 'zh';
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
			$quote['trade_terms'] = $inquiry['trade_terms'];
			$quote['exchange_rate'] = $calculateQuoteInfo['exchangeRate'];
			$quote['total_purchase_price'] = $calculateQuoteInfo['totalPurchasePrice'];
			$quote['purchase_cur'] = $condition['purchase_cur'];
			$exw = exw($calculateQuoteInfo['exwData'], $condition['gross_profit_rate']);
			$quote['total_exw_price'] = $exw['total'];
			$quote['total_exw_cur'] = 'USD';
			$quote['total_quote_cur'] = 'USD';
			$quote['total_logi_fee_cur'] = 'USD';
			$quote['total_bank_fee_cur'] = 'USD';
			$quote['total_insu_fee_cur'] = 'USD';
			//$quote['logi_quote_status'] = 'ONGOING';
			$quote['biz_quote_status'] = 'ONGOING';
			$quote['quote_status'] = 'ONGOING';
			$quote['quoter'] = $condition['quoter']; //获取当前用户信息
			$quote['quoter_email'] = $condition['quoter_email']; //获取当前用户信息
			$quote['quote_at'] = $time;
			$quote['quote_notes'] = $condition['quote_notes'];
			$quote['created_by'] = $inquiry['created_by'];
			$quote['created_at'] = $time;
			
			$res = $this->quoteModel->add($quote);
    		
    		$this->jsonOutput($res);
    	}
    	
    }
    
    /**
     * @desc 修改报价单
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function updateQuoteAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['inquiry_no'])) {
    		
    		$calculateQuoteInfo = $this->getCalculateQuoteInfo($condition);
    		
    		$quote['package_volumn'] = $condition['package_volumn'];
    		$quote['package_mode'] = $condition['package_mode'];
    		$quote['origin_place'] = $condition['origin_place'];
    		$quote['destination'] = $condition['destination'];
    		$quote['gross_profit_rate'] = $condition['gross_profit_rate'];
    		$quote['payment_received_days'] = strtotime($condition['payment_received_days']);
    		$quote['exw_delivery_period'] = strtotime($condition['exw_delivery_period']);
    		$quote['purchase_cur'] = $condition['purchase_cur'];
    		$quote['fund_occupation_rate'] = $condition['fund_occupation_rate'];
    		$quote['payment_mode'] = $condition['payment_mode'];
    		$quote['total_weight'] = $calculateQuoteInfo['$totalWeight'];
    		$quote['exchange_rate'] = $calculateQuoteInfo['exchangeRate'];
			$quote['total_purchase_price'] = $calculateQuoteInfo['totalPurchasePrice'];
			$quote['purchase_cur'] = $condition['purchase_cur'];
			$exw = exw($calculateQuoteInfo['exwData'], $condition['gross_profit_rate']);
			$quote['total_exw_price'] = $exw['total'];
			$quote['quoter'] = $condition['quoter'];
			$quote['quoter_email'] = $condition['quoter_email'];
			$quote['quote_at'] = time();
			$quote['quote_notes'] = $condition['quote_notes'];
			
			$res = $this->quoteModel->save($quote);
    		
    		$this->jsonOutput($res);
    	}
    	
    }
    
    /**
     * @desc 修改报价单物流信息
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function updateQuoteLogiAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['inquiry_no'])) {
    		
    		$quote = $this->quoteModel->where(array('inquiry_no' => $condition['inquiry_no']))->find();
    		
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
    		$logi['overland_insu_rate'] = round($quote['total_exw_price'] * 0.0002, 2);
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
    		
    		$this->jsonOutput($res);
    	}
    }
    
	/**
     * @desc 创建报价单项目
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function createQuoteItemAction() {
    	$condition = $this->put_data;
    	
    	if (isset($condition['inquiry_no'])) {
    		$inquiryItemList = $this->inquiryItemModel->where(array('inquiry_no' => $condition['inquiry_no']))->select();
    		
    		$quoteItemList = $quoteItem = array();
    		foreach ($inquiryItemList as $inquiryItem) {
    			$quoteItem['quote_no'] = $inquiryItem['inquiry_no'];
    			$quoteItem['inquiry_sku'] = $inquiryItem['sku'];
    			$quoteItem['inquiry_sku'] = $inquiryItem['sku'];
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
    		
    		$res = $this->quoteItemModel->addAll($quoteItemList);
    		
    		$this->jsonOutput($res);
    	}
    	
    }
    
    /**
     * @desc 修改报价单项目
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function uptateQuoteItemAction() {
    	$condition = $this->put_data;
    	if (isset($condition['inquiry_no']) && isset($condition['inquiry_sku'])) {
    		$quoteItem = $this->quoteItemModel->where(array('inquiry_no' => $condition['inquiry_no'], 'inquiry_sku' => $condition['inquiry_sku']))->find();
    		
    		$quoteItem['goods_from'] = $condition['goods_from'];
    		$quoteItem['supplier_id'] = $condition['supplier_id'];
    		$quoteItem['supplier_contact'] = $condition['supplier_contact'];
    		$quoteItem['supplier_contact_email'] = $condition['supplier_contact_email'];
    		$quoteItem['supplier_contact_phone'] = $condition['supplier_contact_phone'];
    		$quoteItem['purchase_price'] = $condition['purchase_price'];
    		$quoteItem['total_purchase_price'] = round($condition['purchase_price'] * $quoteItem['quote_quantity'], 8);
    		$quoteItem['purchase_cur'] = $condition['purchase_cur'];
    		
    		$exchangeRate = $this->getRateUSD($condition['purchase_cur']);
    		
    		exw(array(array('busyer_unit_price' => $condition['purchase_price'], 'num' => $condition['purchase_price'])), $exchangeRate);
    		
    		//$quoteItem['exw_unit_price'] = ''; //调用公式
    		//$quoteItem['total_exw_price'] = ''; //调用公式
    		$quoteItem['exw_cur'] = 'USD';
    		//$quoteItem['quote_unit_price'] = ''; //调用公式
    		//$quoteItem['total_quote_price'] = ''; //调用公式
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
    		
    		$res = $this->quoteItemModel->where(array('inquiry_no' => $condition['inquiry_no'], 'inquiry_sku' => $condition['inquiry_sku']))->save($quoteItem);
    		
    		$this->jsonOutput($res);
    	}
    	
    }
    
	/**
     * @desc 获取报价计算后的数据
 	 * @author liujf 2017-06-20
 	 * @param array $condition 条件参数
     * @return array
     */
    private function getCalculateQuoteInfo($condition) {
    	$quoteItemList = $this->quoteItemModel->where(array('quote_no' => $condition['inquiry_no']))->select();
    		
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
    	}
    	
    	return $data;
    	
    }

}