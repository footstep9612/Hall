<?php
/**
 * @desc 物流报价控制器
 * @author liujf 2017-06-29
 */
class LogisticsController extends PublicController {

	public function init() {
		parent::init();
        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
	}
    
	/**
     * @desc 物流获取报价列表接口
 	 * @author liujf 2017-06-29
     * @return json
     */
    public function getQuoteLogiListAction() {
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
 	 * @author liujf 2017-06-29
     * @return json
     */
    public function getQuoteLogiDetailAction() {
    	$condition = $this->put_data;
    	
    	$res = $this->quoteModel->getJoinDetail($condition);
    	
    	$this->jsonReturn($res);
    }
    
    /**
     * @desc 物流报价修改接口
 	 * @author liujf 2017-06-29
     * @return json
     */
    public function updateQuoteLogiAction() {
    	$condition = $this->put_data;
    	
    	if (!empty($condition['quote_no'])) {
    		
    		$quote = $this->quoteModel->getDetail($condition);
    		
    		$condition['logi_submit_at'] = time();
    		$condition['overland_insu_rate'] = round($quote['total_exw_price'] * 0.0002, 8);
    		$condition['inland_marine_insu'] = inlandMarineInsurance(array('total_exw_price' => $quote['total_exw_price'], $condition['overland_insu_rate']));
    		$data = array('trade_terms' => $quote['trade_terms'],
    					  'total_exw_price' => $quote['total_exw_price'],
				    	  'inspection_fee' => $condition['inspection_fee'],
				    	  'premium_rate' => $condition['premium_rate'],
				    	  'payment_received_days' => $quote['payment_received_days'],
				    	  'bank_interest' => $quote['bank_interest'],
				    	  'fund_occupation_rate' => $quote['fund_occupation_rate'],
				    	  'land_freight' => $condition['land_freight'],
				    	  'overland_insu_rate' => $condition['overland_insu_rate'],
				    	  'dest_delivery_charge' => $condition['dest_delivery_charge'],
    					  'dest_tariff_rate' => $condition['dest_tariff_rate'],
				    	  'dest_va_tax_rate' => $condition['dest_va_tax_rate'],
    					  'dest_clearance_fee' => $condition['dest_clearance_fee'],
    		);
    		$logiData = logistics($data);
    		$condition['freightage_insu'] = $logiData['freightage_insu'];
    		$condition['dest_tariff'] = $logiData['dest_tariff'];
    		$condition['dest_va_tax'] = $logiData['dest_va_tax'];
    		$condition['total_insu_fee'] = $logiData['total_insu_fee'];
    		$condition['total_logi_fee'] = $logiData['total_logi_fee'];
    		$condition['total_quote_price'] = $logiData['total_quote_price'];
    		$condition['total_bank_fee'] = $logiData['total_bank_fee'];
    		
    		$res = $this->quoteModel->updateQuote($condition['quote_no'], $condition);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}
    	
    } 
    
	/**
	 * @desc 物流获取报价SKU列表接口
	 * @author liujf 2017-06-30
	 * @return json
	 */
	public function getQuoteItemLogiListAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {
			$data = $this->quoteItemModel->getJoinList($condition);

			if ($data) {
				$res['code'] = 1;
				$res['message'] = '成功!';
				$res['data'] = $data;
				$res['count'] = $this->quoteItemModel->getJoinCount($condition);
				$this->jsonReturn($res);
			} else {
				$this->jsonReturn(false);
			}
		} else {
			$this->jsonReturn(false);
		}

	}
	
	/**
	 * @desc 物流修改报价SKU接口
	 * @author liujf 2017-06-26
	 * @return json
	 */
	public function uptateQuoteItemLogiAction() {
		$condition = $this->put_data;
    	
    	if (!empty($condition['id'])) {
    		
    		$res = $this->quoteItemModel->save($condition);
    		
    		$this->jsonReturn($res);
    	} else {
    		$this->jsonReturn(false);
    	}

	}
	
	/**
	 * @desc 物流报价审核接口
	 * @author liujf 2017-06-30
	 * @return json
	 */
	public function logiQuoteExamineAction() {
		$condition = $this->put_data;
		

		if (!empty($condition['quote_no'])) {
			$where['quote_no'] = array('in', explode(',', $condition['quote_no']));
			
			$user = $this->getUserInfo();
			
			$time = date('Y-m-d H:i:s');
			
			$quote = $this->quoteModel->getDetail($condition['quote_no']);
			
			$status = $condition['status'] == 'Y' ? 'APPROVED' : 'NOT_APPROVED';
			
			$logiCheck = array(
				'logi_quote_status' => $status,
				'logi_checker' => $user['name'],
				'logi_checker_email' => $user['email'],
				'logi_check_at' => $time,
				'logi_check_notes' => $condition['notes']
			);
			
			$approveLog = array (
				'inquiry_no' => $quote['inquiry_no'],
				'type' => $condition['type'],
				'approver_id' => $user['id'],
				'approver' => $user['name'],
				'status' => $condition['status'],
				'notes' => $condition['notes']
			);
			
			$this->quoteModel->where($where)->save($logiCheck);
			
			$res = $this->addApproveLog($approveLog);
			
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