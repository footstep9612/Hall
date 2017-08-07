<?php
/*
 * @desc 物流报价控制器
 * 
 * @author liujf 
 * @time 2017-08-02
 */
class LogisticsController extends PublicController {

	public function init() {
		parent::init();
		
		$this->quoteModel = new QuoteModel();
		$this->quoteLogiFeeModel = new QuoteLogiFeeModel();
		$this->quoteItemLogiModel = new QuoteItemLogiModel();
		$this->exchangeRateModel = new ExchangeRateModel();
		$this->userModel = new UserModel();

        $this->time = date('Y-m-d H:i:s');
	}
	
	/**
	 * @desc 获取报价单项物流报价列表接口
	 *
	 * @author liujf
	 * @time 2017-08-02
	 */
	public function getQuoteItemLogiListAction() {
	    $condition = $this->put_data;
	
	    $data = $this->quoteItemLogiModel->getJoinList($condition);
	
	    $this->_handleList($this->quoteItemLogiModel, $data, $condition, true);
	}
	
	/**
	 * @desc 获取报价单项物流报价接口
	 *
	 * @author liujf
	 * @time 2017-08-02
	 */
	public function getQuoteItemLogiDetailAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['r_id'])) {
	        $condition['id'] = $condition['r_id'];
	        unset($condition['r_id']);
    	    $res = $this->quoteItemLogiModel->getJoinDetail($condition);
    	
    	    $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 修改报价单项物流报价信息接口
	 *
	 * @author liujf
	 * @time 2017-08-02
	 */
	public function updateQuoteItemLogiInfoAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['r_id'])) {
	        $where['id'] = $condition['r_id'];
	        unset($condition['r_id']);
	        $res = $this->quoteItemLogiModel->updateInfo($where, $condition);
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 获取报价单列表接口
	 *
	 * @author liujf
	 * @time 2017-08-07
	 */
	public function getQuoteLogiListAction() {
	    $condition = $this->put_data;
	    
	    if (!empty($condition['agent_name'])) {
	         $agent = $this->userModel->where(['name' => $condition['agent_name']])->find();
	         $condition['agent_id'] = $agent['id'];
	    }
	    
	    if (!empty($condition['pm_name'])) {
	        $pm = $this->userModel->where(['name' => $condition['pm_name']])->find();
	        $condition['pm_id'] = $pm['id'];
	    }
	
	    $quoteLogiFeeList= $this->quoteLogiFeeModel->getJoinList($condition);
	    
	    foreach ($quoteLogiFeeList as &$quoteLogiFee) {
            $userAgent = $this->userModel->info($quoteLogiFee['agent_id']);
            $userPm = $this->userModel->info($quoteLogiFee['pm_id']);
	        $quoteLogiFee['agent_name'] = $userAgent['name'];
	        $quoteLogiFee['pm_name'] = $userPm['name'];
	    }
	    
	    if ($quoteLogiFeeList) {
	        $res['code'] = 1;
	        $res['message'] = '成功!';
	        $res['data'] = $quoteLogiFeeList;
	        $res['count'] = $this->quoteLogiFeeModel->getListCount($condition);
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 获取报价单物流费用详情接口
	 *
	 * @author liujf
	 * @time 2017-08-03
	 */
	public function getQuoteLogiFeeDetailAction() {
	    $condition = $this->put_data;
	
	    $res = $this->quoteLogiFeeModel->getJoinDetail($condition);
	
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 修改报价单物流费用信息接口
	 *
	 * @author liujf
	 * @time 2017-08-03
	 */
	public function updateQuoteLogiFeeInfoAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id'])) {
	        
	        $data = $condition;
	        
	        unset($data['from_port']);
	        unset($data['to_port']);
	        unset($data['trans_mode_bn']);
	        unset($data['box_type_bn']);
	        unset($data['quote_remarks']);
	        
	        $data['inspection_fee'] = 0;
	        $data['land_freight'] = 0;
	        $data['port_surcharge'] = 0;
	        $data['inter_shipping'] = 0;
	        $data['dest_delivery_fee'] = 0;
	        $data['dest_clearance_fee'] = 0;
	        $data['overland_insu_rate'] = 0;
	        $data['shipping_insu_rate'] = 0;
	        $data['dest_tariff_rate'] = 0;
	        $data['dest_va_tax_rate'] = 0;
	        
	        $quoteLogiFee = $this->quoteLogiFeeModel->getJoinDetail($condition);
	        $quote = $this->quoteModel->getDetail(['id' =>$quoteLogiFee['quote_id']]);
	        
	        if ($quoteLogiFee['logi_agent_id'] == '') {
	            $data['logi_agent_id'] = $this->user['id'];
	        }
	        
	        $data['updated_by'] = $this->user['id'];
	        $data['updated_at'] = $this->time;
	        
	        $data['inspection_fee'] = $condition['inspection_fee'];
	        
	        switch ($quoteLogiFee['trade_terms_bn']) {
	            case 'EXW' :
	                
	                break;
	            case 'FCA' || 'FAS' :
	                $data['land_freight'] = $condition['land_freight'];
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'];
	                break;
	            case 'FOB' :
	                $data['land_freight'] = $condition['land_freight'];
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'];
	                $data['port_surcharge'] = $condition['port_surcharge'];
	                break;
	            case 'CPT' || 'CFR' :
	                $data['land_freight'] = $condition['land_freight'];
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'];
	                $data['port_surcharge'] = $condition['port_surcharge'];
	                $data['inter_shipping'] = $condition['inter_shipping'];
	                break;
	            case 'CIF' || 'CIP' :
	                $data['land_freight'] = $condition['land_freight'];
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'];
	                $data['port_surcharge'] = $condition['port_surcharge'];
	                $data['inter_shipping'] = $condition['inter_shipping'];
	                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'];
	                break;
	            case 'DAP' || 'DAT' :
	                $data['land_freight'] = $condition['land_freight'];
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'];
	                $data['port_surcharge'] = $condition['port_surcharge'];
	                $data['inter_shipping'] = $condition['inter_shipping'];
	                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'];
	                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'];
	                break;
	            case 'DDP' || '快递' :
	                $data['land_freight'] = $condition['land_freight'];
	                $data['overland_insu_rate'] = $condition['overland_insu_rate'];
	                $data['port_surcharge'] = $condition['port_surcharge'];
	                $data['inter_shipping'] = $condition['inter_shipping'];
	                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'];
	                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'];
	                $data['dest_clearance_fee'] = $condition['dest_clearance_fee'];
	                $data['dest_tariff_rate'] = $condition['dest_tariff_rate'];
	                $data['dest_va_tax_rate'] = $condition['dest_va_tax_rate'];
	        }
	        
	        $inspectionFeeUSD = $data['inspection_fee'] * $this->_getRateUSD($data['inspection_fee_cur']);
	        $landFreightUSD = $data['land_freight'] * $this->_getRateUSD($data['land_freight_cur']);
	        $overlandInsuUSD = $quote['total_exw_price'] * 1.1 * $data['overland_insu_rate'];
	        $portSurchargeUSD = $data['port_surcharge'] * $this->_getRateUSD($data['port_surcharge_cur']);
	        $interShippingUSD = $data['inter_shipping'] * $this->_getRateUSD($data['inter_shipping_cur']);
	        $shippingInsuUSD = $quote['total_quote_price'] * 1.1 * $data['shipping_insu_rate'];
	        $destDeliveryFeeUSD = $data['dest_delivery_fee'] * $this->_getRateUSD($data['dest_delivery_fee_cur']);
	        $destClearanceFeeUSD = $data['dest_clearance_fee'] * $this->_getRateUSD($data['dest_clearance_fee_cur']);
	        $sumUSD = $quote['total_exw_price'] + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $inspectionFeeUSD + $interShippingUSD;
	        $destTariffUSD = $sumUSD * $data['dest_tariff_rate'];
	        $destVaTaxUSD = $sumUSD * (1 + $data['dest_tariff_rate']) * $data['dest_va_tax_rate'];
	        
	        // 物流费用合计
	        $totalFeeUSD = $inspectionFeeUSD +  $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $shippingInsuUSD + $destDeliveryFeeUSD + $destClearanceFeeUSD + $destTariffUSD + $destVaTaxUSD;
	        $data['shipping_charge_cny'] = round($totalFeeUSD * $this->_getRateCNY('USD'), 3);
	        $data['shipping_charge_ncny'] = round($totalFeeUSD, 3);
	        
	        $res1 = $this->quoteLogiFeeModel->updateInfo($where, $data);
	        
	        $quoteData = [
	            'from_port' => $condition['from_port'],
	            'to_port' => $condition['to_port'],
	            'trans_mode_bn' => $condition['trans_mode_bn'],
	            'box_type_bn' => $condition['box_type_bn'],
	            'quote_remarks' => $condition['quote_remarks']
	        ];
	        
	        $res2 = $this->quoteModel->updateQuote(['quote_no' => $quote['quote_no']], $quoteData);
	
	        $this->jsonReturn($res1 && $res2);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 分配物流报价人接口
	 *
	 * @author liujf
	 * @time 2017-08-03
	 */
	public function assignLogiAgentAction() {
	    $condition = $this->put_data;
	    
	    if (!empty($condition['quote_id'])) {
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $res = $this->quoteLogiFeeModel->updateInfo($where, ['logi_agent_id' => $condition['logi_agent_id']]);
	        
	        $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
	}
	
	/**
	 * @desc 获取币种兑换人民币汇率
	 *
	 * @param string $cur 币种
	 * @return float
	 * @author liujf
	 * @time 2017-08-03
	 */
	private function _getRateCNY($cur) {
	
	    return $this->_getRate($cur, 'CNY');
	}
	
	/**
	 * @desc 获取币种兑换美元汇率
	 *
	 * @param string $cur 币种
	 * @return float
	 * @author liujf
	 * @time 2017-08-03
	 */
	private function _getRateUSD($cur) {
	
	    return $this->_getRate($cur, 'USD');
	}
	
	/**
	 * @desc 获取币种兑换汇率
	 *
	 * @param string $cur 币种
	 * @param string $exchangeCur 兑换币种
	 * @return float
	 * @author liujf
	 * @time 2017-08-03
	 */
	private function _getRate($cur, $exchangeCur = 'CNY') {
	    
	    if (!epmty($cur)) {
	        $exchangeRate = $this->exchangeRateModel->where(['cur_bn1' => $cur, 'cur_bn2' => $exchangeCur])->field('rate')->find();
	        
	        return $exchangeRate['rate'];
	    } else {
	        return false;
	    }
	    
	}
    
	/**
	 * @desc 对获取列表数据的处理
	 * 
     * @author liujf 
     * @time 2017-08-02
	 */
	private function _handleList($model, $data = [], $condition = [], $join = false) {
	   if ($data) {
    		$res['code'] = 1;
    		$res['message'] = '成功!';
    		$res['data'] = $data;
    		$res['count'] = $join ? $model->getJoinCount($condition) : $model->getCount($condition);
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
	}
    
	/**
     * @desc 重写jsonReturn方法
     * 
     * @author liujf 
     * @time 2017-08-02
     */
    public function jsonReturn($data = [], $type = 'JSON') {
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