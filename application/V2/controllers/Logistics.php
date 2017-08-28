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
		
		$this->inquiryModel = new InquiryModel();
		$this->quoteModel = new QuoteModel();
		$this->quoteItemModel = new QuoteItemModel();
		$this->quoteLogiFeeModel = new QuoteLogiFeeModel();
		$this->quoteItemLogiModel = new QuoteItemLogiModel();
		$this->exchangeRateModel = new ExchangeRateModel();
		$this->userModel = new UserModel();
		$this->inquiryCheckLogModel = new InquiryCheckLogModel();
		$this->quoteLogiQwvModel = new QuoteLogiQwvModel();

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
	
	    if (empty($condition['quote_id'])) $this->jsonReturn(false);
	    
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
	 * @time 2017-08-08
	 */
	public function updateQuoteItemLogiInfoAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['items'])) {
	        
	        $flag = true;
	        $data = [];
	        
	        //$this->quoteItemLogiModel->startTrans();
	        
	        foreach ($condition['items'] as $item) {
	            $where['id'] = $item['id'];
	            unset($item['id']);
	            
	            $item['updated_by'] = $this->user['id'];
	            $item['updated_at'] = $this->time;
	            
	            $res = $this->quoteItemLogiModel->updateInfo($where, $item);
	            
	            /*if (!$res) {
	                $this->quoteItemLogiModel->rollback();
	                $flag = false;
	                break;
	            }*/
	            
	            if (!$res) {
	               $data[] = $where['id'];
	               $flag = false;
	            }
	        }
	        
	       // if ($flag) $this->quoteItemLogiModel->commit();
	
	        if ($flag) {
	            $this->jsonReturn($flag);
	        } else {
	            $this->setCode('-101');
	            $this->setMessage('失败!');
	            parent::jsonReturn($data);
	        }
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
	    
	    $condition['logi_agent_id'] = $this->user['id'];
	
	    $quoteLogiFeeList= $this->quoteLogiFeeModel->getJoinList($condition);
	    
	    foreach ($quoteLogiFeeList as &$quoteLogiFee) {
            $userAgent = $this->userModel->info($quoteLogiFee['agent_id']);
            $userPm = $this->userModel->info($quoteLogiFee['pm_id']);
	        $quoteLogiFee['agent_name'] = $userAgent['name'];
	        $quoteLogiFee['pm_name'] = $userPm['name'];
	    }
	    	    
	    $this->_handleList($this->quoteLogiFeeModel, $quoteLogiFeeList, $condition, true);
	}
	
	/**
	 * @desc 获取报价单物流费用详情接口
	 *
	 * @author liujf
	 * @time 2017-08-03
	 */
	public function getQuoteLogiFeeDetailAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id']) || !empty($condition['inquiry_id']) ) {
	        
    	    $quoteLogiFee = $this->quoteLogiFeeModel->getJoinDetail($condition);
    	    
	        $quoteLogiFee['overland_insu'] = $quoteLogiFee['total_exw_price'] * 1.1 * $quoteLogiFee['overland_insu_rate'];
	        $quoteLogiFee['shipping_insu'] = $quoteLogiFee['total_quote_price'] * 1.1 * $quoteLogiFee['shipping_insu_rate'];
	        $tmpTotalFee = $quoteLogiFee['total_exw_price'] + $quoteLogiFee['land_freight'] * $this->_getRateUSD($quoteLogiFee['land_freight_cur']) + $quoteLogiFee['overland_insu'] + $quoteLogiFee['port_surcharge'] * $this->_getRateUSD($quoteLogiFee['port_surcharge_cur']) + $quoteLogiFee['inspection_fee'] * $this->_getRateUSD($quoteLogiFee['inspection_fee_cur']) + $quoteLogiFee['inter_shipping'] * $this->_getRateUSD($quoteLogiFee['inter_shipping_cur']);
	        $quoteLogiFee['dest_tariff_fee'] = $tmpTotalFee * $quoteLogiFee['dest_tariff_rate'];
	        $quoteLogiFee['dest_va_tax_fee'] = $tmpTotalFee * (1 + $quoteLogiFee['dest_tariff_rate']) * $quoteLogiFee['dest_va_tax_rate'];
	        $user = $this->getUserInfo();
	        $quoteLogiFee['current_name'] = $user['name'];
    	
    	    $this->jsonReturn($quoteLogiFee);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 修改报价单物流费用信息接口
	 *
	 * @author liujf
	 * @time 2017-08-10
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
	        
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $quoteLogiFee = $this->quoteLogiFeeModel->getDetail($where);
	        $data['premium_rate'] = $quoteLogiFee['premium_rate'];
	        
	        $quote = $this->quoteModel->getDetail(['id' =>$condition['quote_id']]);
	        $data['trade_terms_bn'] = $quote['trade_terms_bn'];
	        $data['payment_period'] = $quote['payment_period'];
	        $data['fund_occupation_rate'] = $quote['fund_occupation_rate'];
	        $data['bank_interest'] = $quote['bank_interest'];
	        $data['total_exw_price'] = $quote['total_exw_price'];
	        
	        $data = $this->calcuTotalLogiFee($data);
	        
	        //if ($quoteLogiFee['logi_agent_id'] == '') {
	            $data['logi_agent_id'] = $this->user['id'];
	        //}
	        
	        if ($quoteLogiFee['logi_from_port'] != $condition['logi_from_port']) $data['logi_from_port'] = $condition['logi_from_port'];
	        if ($quoteLogiFee['logi_to_port'] != $condition['logi_to_port']) $data['logi_to_port'] = $condition['logi_to_port'];
	        if ($quoteLogiFee['logi_trans_mode_bn'] != $condition['logi_trans_mode_bn']) $data['logi_trans_mode_bn'] = $condition['logi_trans_mode_bn'];
	        if ($quoteLogiFee['logi_box_type_bn'] != $condition['logi_box_type_bn']) $data['logi_box_type_bn'] = $condition['logi_box_type_bn'];
	        
	        $data['updated_by'] = $this->user['id'];
	        $data['updated_at'] = $this->time;
	        
	        $this->quoteLogiFeeModel->startTrans();
	        $res1 = $this->quoteLogiFeeModel->updateInfo($where, $data);
	        
	        $quoteData = [];
	       
	        if ($quote['quote_remarks'] != $condition['quote_remarks']) $quoteData['quote_remarks'] = $condition['quote_remarks'];
	        
	        if ($data['total_quote_price'] != $quote['total_quote_price']) $quoteData['total_quote_price'] = $data['total_quote_price'];
	        if ($data['total_bank_fee'] != $quote['total_bank_fee']) $quoteData['total_bank_fee'] = $data['total_bank_fee'];
	        if ($data['total_insu_fee'] != $quote['total_insu_fee']) $quoteData['total_insu_fee'] = $data['total_insu_fee'];
	        
	        if ($quoteData) {
	            $this->quoteModel->startTrans();
	            $res2 = $this->quoteModel->updateQuote(['quote_no' => $quote['quote_no']], $quoteData);
	        }
	        
	        $quoteItemList = $this->quoteItemModel->getItemList($where);
	        
	        $res3 = true;
	        $this->quoteItemModel->startTrans();
	        foreach ($quoteItemList as $quoteItem) {
	            $quoteUnitPrice = round($data['total_quote_price'] * $quoteItem['exw_unit_price'] / $data['total_exw_price'], 4);
	            $quoteUnitPrice = $quoteUnitPrice > 0 ? $quoteUnitPrice : 0;
	            if ($quoteItem['quote_unit_price'] != $quoteUnitPrice) {
	                $tmpRes = $this->quoteItemModel->updateItem(['id' => $quoteItem['id']], ['quote_unit_price' => $quoteUnitPrice]);
	                if (!$tmpRes) {
	                    $res3 = false;
	                    break;
	                }
	            }
	        }
	        
	        if (isset($res2)) {
	            if ($res1 && $res2 && $res3) {
	                $this->quoteLogiFeeModel->commit();
	                $this->quoteModel->commit();
	                $this->quoteItemModel->commit();
	                $res = true;
	            } else {
	                $this->quoteLogiFeeModel->rollback();
	                $this->quoteModel->rollback();
	                $this->quoteItemModel->rollback();
	                $res = false;
	            }
	        } else {
	            if ($res1 && $res3) {
	                $this->quoteLogiFeeModel->commit();
	                $this->quoteItemModel->commit();
	                $res = true;
	            } else {
	                $this->quoteLogiFeeModel->rollback();
	                $this->quoteItemModel->rollback();
	                $res = false;
	            }
	        }
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 获取物流报价件重尺列表接口
	 * 
	 * @author liujf
	 * @time 2017-08-17
	 */
	public function getQuoteLogiQwvListAction() {
	    $condition = $this->put_data;
	     
	    if (empty($condition['quote_id'])) $this->jsonReturn(false);
	     
	    $data = $this->quoteLogiQwvModel->getList($condition);
	    
	    $this->_handleList($this->quoteLogiQwvModel, $data, $condition);
	}
	
	/**
	 * @desc 新增物流报价件重尺记录接口
	 * 
	 * @author liujf
	 * @time 2017-08-17
	 */
	public function addQuoteLogiQwvRecordAction() {
	    $condition = $this->put_data;
	    
	    if (empty($condition['quote_id'])) $this->jsonReturn(false);
	
	    $condition['created_by'] = $this->user['id'];
	    $condition['created_at'] = $this->time;
	    $condition['updated_by'] = $this->user['id'];
	    $condition['updated_at'] = $this->time;
	     
	    $res = $this->quoteLogiQwvModel->addRecord($condition);
	     
	    $this->jsonReturn($res);
	}
	
	/**
	 * @desc 修改物流报价件重尺信息接口
	 * 
     * @author liujf
	 * @time 2017-08-17
	 */
	public function updateQuoteLogiQwvInfoAction() {
	    $condition = $this->put_data;
	     
	    if (!empty($condition['r_id'])) {
	        $where['id'] = $condition['r_id'];
	        unset($condition['r_id']);
	        
	        $volumn = $condition['length'] * $condition['width'] * $condition['height'];
	        $condition['volumn'] = $volumn > 0 ? $volumn : 0;
	        
	        $condition['updated_by'] = $this->user['id'];
	        $condition['updated_at'] = $this->time;
	        
	        $res = $this->quoteLogiQwvModel->updateInfo($where, $condition);
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 批量修改物流报价件重尺信息接口
	 *
	 * @author liujf
	 * @time 2017-08-21
	 */
	public function batchUpdateQuoteLogiQwvInfoAction() {
	    $condition = $this->put_data;
	    
	    if (!empty($condition['items'])) {
	         
	        $flag = true;
	        $data = [];
	         
	        foreach ($condition['items'] as $item) {
	            $where['id'] = $item['id'];
	            unset($item['id']);
	            
	            $volumn = $item['length'] * $item['width'] * $item['height'];
	            $item['volumn'] = $volumn > 0 ? $volumn : 0;
	            
	            $item['updated_by'] = $this->user['id'];
	            $item['updated_at'] = $this->time;
	             
	            $res = $this->quoteLogiQwvModel->updateInfo($where, $item);	             
	             
	            if (!$res) {
	                $data[] = $where['id'];
	                $flag = false;
	            }
	        }
	    
	        if ($flag) {
	            $this->jsonReturn($flag);
	        } else {
	            $this->setCode('-101');
	            $this->setMessage('失败!');
	            parent::jsonReturn($data);
	        }
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 删除物流报价件重尺记录接口
	 * 
	 * @author liujf
	 * @time 2017-08-17
	 */
	public function delQuoteLogiQwvRecordAction() {
	    $condition = $this->put_data;
	     
	    if (!empty($condition['r_id'])) {
	        $res = $this->quoteLogiQwvModel->delRecord($condition);
	
	        $this->jsonReturn($res);
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
	        
	        $data = [
	            'logi_agent_id' => $condition['logi_agent_id'],
	            'updated_at' => $this->time
	        ];
	        
	        $res = $this->quoteLogiFeeModel->updateInfo($where, $data);
	        
	        $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
	}
	
	/**
	 * @desc 提交物流审核接口
	 *
	 * @author liujf
	 * @time 2017-08-08
	 */
	public function submitLogiCheckAction() {
	    $condition = $this->put_data;
	     
	    if (!empty($condition['quote_id']) && !empty($condition['checked_by'])) {
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $data = [
	            'status' => 'QUOTED',
	            'checked_by' => $condition['checked_by'],
	            'updated_at' => $this->time
	        ];
	         
	        $res = $this->quoteLogiFeeModel->updateInfo($where, $data);
	         
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 提交项目经理审核接口
	 *
	 * @author liujf
	 * @time 2017-08-08
	 */
	public function submitProjectCheckAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id'])) {
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $quoteLogiFee = $this->quoteLogiFeeModel->where($where)->find();
	        
	        $this->quoteLogiFeeModel->startTrans();
	        $this->quoteModel->startTrans();
	        $this->inquiryModel->startTrans();
	        $this->inquiryCheckLogModel->startTrans();
	        
	        $quoteLogiFeeData = [
	            'status' => 'APPROVED',
	            'updated_at' => $this->time,
	            'checked_at' => $this->time
	        ];
	
	        $res1 = $this->quoteLogiFeeModel->updateInfo($where, $quoteLogiFeeData);
	        
	        $res2 = $this->quoteModel->where(['id' => $condition['quote_id']])->save(['status' => 'QUOTED_BY_LOGI']);
	        
	        $res3 = $this->inquiryModel->updateStatus(['id' => $quoteLogiFee['inquiry_id'], 'status' => 'QUOTED_BY_LOGI']);
	         
	        $checkLog= [
	            'inquiry_id' => $quoteLogiFee['inquiry_id'],
	            'quote_id' => $condition['quote_id'],
	            'category' => 'LOGI',
	            'action' => 'APPROVING',
	            'op_result' => 'APPROVED'
	        ];
	         
	        $res4 = $this->addCheckLog($checkLog, $this->inquiryCheckLogModel);
	        
	        if ($res1 && $res2 && $res3 && $res4) {
	            $this->quoteLogiFeeModel->commit();
	            $this->quoteModel->commit();
	            $this->inquiryModel->commit();
	            $this->inquiryCheckLogModel->commit();
	            $res = true;
	        } else {
	            $this->quoteLogiFeeModel->rollback();
	            $this->quoteModel->rollback();
	            $this->inquiryModel->rollback();
	            $this->inquiryCheckLogModel->rollback();
	            $res = false;
	        }
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 物流报价驳回接口
	 *
	 * @author liujf
	 * @time 2017-08-08
	 */
	public function rejectLogiAction() {
	    $condition = $this->put_data;
	
	    if (!empty($condition['quote_id'])) {
	        $where['quote_id'] = $condition['quote_id'];
	        
	        $quoteLogiFee = $this->quoteLogiFeeModel->where($where)->find();
	        
	        $this->quoteLogiFeeModel->startTrans();
	        $this->inquiryCheckLogModel->startTrans();
	        
	        $quoteLogiFeeData = [
	            'status' => 'REJECTED',
	            'updated_at' => $this->time,
	            'checked_at' => $this->time
	        ];
	        
	        $res1 = $this->quoteLogiFeeModel->updateInfo($where, $quoteLogiFeeData);
	        
	        $checkLog= [
	            'inquiry_id' => $quoteLogiFee['inquiry_id'],
	            'quote_id' => $condition['quote_id'],
	            'category' => 'LOGI',
	            'action' => 'APPROVING',
	            'op_note' => $condition['op_note'],
	            'op_result' => 'REJECTED'
	        ];
	        
	        $res2 = $this->addCheckLog($checkLog, $this->inquiryCheckLogModel);
	        
	        if ($res1 && $res2) {
	            $this->quoteLogiFeeModel->commit();
	            $this->inquiryCheckLogModel->commit();
	            $res = true;
	        } else {
	            $this->quoteLogiFeeModel->rollback();
	            $this->inquiryCheckLogModel->rollback();
	            $res = false;
	        }
	
	        $this->jsonReturn($res);
	    } else {
	        $this->jsonReturn(false);
	    }
	}
	
	/**
	 * @desc 计算物流合计
	 *
	 * @param array $condition
	 * --------------------------------------------------------------
	 *     trade_terms_bn 贸易术语简称
	 *     total_exw_price 报出EXW合计
	 *     premium_rate 保险税率
	 *     payment_period 回款周期(天)
	 *     bank_interest 银行利息
	 *     fund_occupation_rate 资金占用比例
	 *     inspection_fee 商检费
	 *     inspection_fee_cur 商检费币种
	 *     land_freight 陆运费
	 *     land_freight_cur 陆运费币种
	 *     port_surcharge 港杂费
	 *     port_surcharge_cur 港杂费币种
	 *     inter_shipping 国际运费
	 *     inter_shipping_cur 国际运费币种
	 *     dest_delivery_fee 目的地配送费
	 *     dest_delivery_fee_cur 目的地配送费币种
	 *     dest_clearance_fee 目的地清关费
	 *     dest_clearance_fee_cur 目的地清关费币种
	 *     overland_insu_rate 陆运险率
	 *     shipping_insu_rate 国际运输险率
	 *     dest_tariff_rate 目的地关税税率
	 *     dest_va_tax_rate 目的地增值税率
	 * --------------------------------------------------------------
	 * @return mixed
	 * @author liujf
	 * @time 2017-08-18
	 */
	public function calcuTotalLogiFee($condition = []) {
	    
	    if (empty($condition['trade_terms_bn'])) return false;
	   
	    $data = $condition;
	     
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
	     
	    $data['inspection_fee'] = $condition['inspection_fee'] > 0 ? $condition['inspection_fee'] : 0;
	     
	    switch ($data['trade_terms_bn']) {
	        case 'EXW' :
	             
	            break;
	        case 'FCA' || 'FAS' :
	            $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	            $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	            break;
	        case 'FOB' :
	            $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	            $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	            $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	            break;
	        case 'CPT' || 'CFR' :
	            $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	            $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	            $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	            $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	            break;
	        case 'CIF' || 'CIP' :
	            $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	            $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	            $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	            $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	            $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
	            break;
	        case 'DAP' || 'DAT' :
	            $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	            $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	            $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	            $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	            $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
	            $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
	            break;
	        case 'DDP' || '快递' :
	            $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
	            $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
	            $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
	            $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
	            $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
	            $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
	            $data['dest_clearance_fee'] = $condition['dest_clearance_fee'] > 0 ? $condition['dest_clearance_fee'] : 0;
	            $data['dest_tariff_rate'] = $condition['dest_tariff_rate'] > 0 ? $condition['dest_tariff_rate'] : 0;
	            $data['dest_va_tax_rate'] = $condition['dest_va_tax_rate'] > 0 ? $condition['dest_va_tax_rate'] : 0;
	    }
	     
	    $inspectionFeeUSD = $data['inspection_fee'] * $this->_getRateUSD($data['inspection_fee_cur']);
	    $landFreightUSD = $data['land_freight'] * $this->_getRateUSD($data['land_freight_cur']);
	    $overlandInsuUSD = $data['total_exw_price'] * 1.1 * $data['overland_insu_rate'];
	    $portSurchargeUSD = $data['port_surcharge'] * $this->_getRateUSD($data['port_surcharge_cur']);
	    $interShippingUSD = $data['inter_shipping'] * $this->_getRateUSD($data['inter_shipping_cur']);
	    $destDeliveryFeeUSD = $data['dest_delivery_fee'] * $this->_getRateUSD($data['dest_delivery_fee_cur']);
	    $destClearanceFeeUSD = $data['dest_clearance_fee'] * $this->_getRateUSD($data['dest_clearance_fee_cur']);
	    $sumUSD = $data['total_exw_price'] + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $inspectionFeeUSD + $interShippingUSD;
	    $destTariffUSD = $sumUSD * $data['dest_tariff_rate'];
	    $destVaTaxUSD = $sumUSD * (1 + $data['dest_tariff_rate']) * $data['dest_va_tax_rate'];
	     
	    $tmpRate1 = 1 - $data['premium_rate'] - round($data['payment_period'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365, 8);
	    $tmpRate2 = $tmpRate1 - 1.1 * $data['shipping_insu_rate'];
	     
	    switch ($data['trade_terms_bn']) {
	        case 'EXW' :
	            $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD) / $tmpRate1, 8);
	            break;
	        case 'FCA' || 'FAS' :
	            $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD) / $tmpRate1, 8);
	            break;
	        case 'FOB' :
	            $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD) / $tmpRate1, 8);
	            break;
	        case 'CPT' || 'CFR' :
	            $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) / $tmpRate1, 8);
	            break;
	        case 'CIF' || 'CIP' :
	            $tmpCaFee = $data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD;
	            $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
	            break;
	        case 'DAP' || 'DAT' :
	            $tmpCaFee = $data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $destDeliveryFeeUSD;
	            $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
	            break;
	        case 'DDP' || '快递' :
	            $tmpCaFee = ($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) * (1 + $data['dest_tariff_rate']) * (1 + $data['dest_va_tax_rate']) + $destDeliveryFeeUSD + $destClearanceFeeUSD;
	            $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
	    }
	     
	    $shippingInsuUSD = $totalQuotePrice * 1.1 * $data['shipping_insu_rate'];
	    $totalBankFeeUSD = round($totalQuotePrice * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_period']  / 365, 8);
	    $totalInsuFeeUSD =$totalQuotePrice * $data['premium_rate'];
	    
	    $data['overland_insu'] = $overlandInsuUSD;
	    $data['shipping_insu'] = $shippingInsuUSD;
	    $data['dest_tariff_fee'] = $destTariffUSD;
	    $data['dest_va_tax_fee'] = $destVaTaxUSD;
	     
	    // 物流费用合计
	    $totalFeeUSD = $inspectionFeeUSD +  $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $shippingInsuUSD + $destDeliveryFeeUSD + $destClearanceFeeUSD + $destTariffUSD + $destVaTaxUSD;
	    $data['shipping_charge_cny'] = round($totalFeeUSD * $this->_getRateCNY('USD'), 4);
	    $data['shipping_charge_ncny'] = round($totalFeeUSD, 4);
	    
	    $data['total_quote_price'] = round($totalQuotePrice, 4);
	    $data['total_bank_fee'] = round($totalBankFeeUSD, 4);
	    $data['total_insu_fee'] = round($totalInsuFeeUSD, 4);
	     
	    return $data;
	}
	
	/**
	 * @desc 获取报出价格合计
	 *
	 * @param float $calcuFee, $shippingInsuRate, $calcuRate
	 * @return float
	 * @author liujf
	 * @time 2017-08-10
	 */
	private function _getTotalQuotePrice($calcuFee, $shippingInsuRate, $calcuRate) {
	
	    $tmpIfFee = round($calcuFee * 1.1 * $shippingInsuRate / $calcuRate, 8);
	    
	    if ($tmpIfFee >= 8 || $tmpIfFee == 0) {
	        $totalQuotePrice = round($calcuFee / $calcuRate, 8);
	    } else {
	        $totalQuotePrice = round(($calcuFee + 8) / $calcuRate, 8);
	    }
	    
	    return $totalQuotePrice;
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
	    
	    if (!empty($cur)) {
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