<?php
/**
 * @desc 物流报价控制器
 * @author liujf 2017-06-29
 */
class LogisticsController extends PublicController {

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
     * @desc 物流获取报价列表接口
 	 * @author liujf 2017-06-29
     * @return json
     */
    public function getQuoteLogiListApiAction() {
    	$condition = $this->put_data;
    	
    	$data = $this->quoteModel->getJoinList($condition);
    	
    	if ($data) {
    		$res['code'] = 1;
    		$res['message'] = '成功!';
    		$res['data'] = $data;
    		$res['count'] = $this->quoteModel->getJoinCount($condition);
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    }
    
	/**
     * @desc 物流获取报价详情接口
 	 * @author liujf 2017-06-28
     * @return json
     */
    public function getQuoteLogiDetailApiAction() {
    	$this->getQuoteDetailApiAction();
    }
    
    /**
     * @desc 物流报价修改接口
 	 * @author liujf 2017-06-20
     * @return json
     */
    public function updateQuoteLogiApiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['quote_no'])) {
    		
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
    		
    		$res = $this->quoteModel->where(array('quote_no' => $condition['quote_no']))->save($logi);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
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