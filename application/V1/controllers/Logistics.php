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
        $this->approveLogModel = new ApproveLogModel();
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
			$user = $this->getUserInfo();

			$condition['logi_agent'] = $user['name'];
			$condition['logi_agent_email'] = $user['email'];
    		$condition['logi_submit_at'] = date('Y-m-d H:i:s');

    		$condition['overland_insu_rate'] = $quote['total_exw_price'] > 8 ? round($quote['total_exw_price'] * 0.0002, 8) : $quote['total_exw_price'] ;
    		$inlandMarineInsurance = inlandMarineInsurance(array('total_exw_price' => $quote['total_exw_price'], 'overland_insu_rate' => $quote['overland_insu_rate']));
    		$condition['inland_marine_insu'] = isset($inlandMarineInsurance['code']) ? 0 : $inlandMarineInsurance;
    		$data = array('trade_terms' => $quote['trade_terms'],
    		              'port_surcharge' => $quote['port_surcharge'],
    					  'total_exw_price' => $quote['total_exw_price'],
				    	  'inspection_fee' => $quote['inspection_fee'],
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
    		              'cargo_insurance_rate' => $condition['cargo_insu_rate'],
    		);
    		$logiData = logistics($data);
    		$condition['freightage_insu'] = $logiData['freightage_insu'];
    		$condition['dest_tariff'] = $logiData['dest_tariff'];
    		$condition['dest_va_tax'] = $logiData['dest_va_tax'];
    		$condition['total_logi_fee'] = $logiData['total_logi_fee'];
    		$condition['total_quote_price'] = $logiData['total_quote_price'];
    		$condition['total_bank_fee'] = $logiData['total_bank_fee'];

			$condition['export_credit_insu'] = round($logiData['total_quote_price']*$condition['premium_rate'], 8);

    		
    		$where['quote_no'] = $condition['quote_no'];
    		$res = $this->quoteModel->updateQuote($where, $condition);
    		
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
	public function updateQuoteItemLogiAction() {
		$condition = $this->put_data;
    	
    	if (!empty($condition['item_id'])) {
    		$where['id'] = $condition['item_id'];
    		unset($condition['item_id']);
    		
    		$res = $this->quoteItemModel->updateItem($where, $condition);
    		
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
	public function logiQuoteApproveAction() {
		$condition = $this->put_data;
		

		if (!empty($condition['quote_no']) && !empty($condition['status'])) {
			$where['quote_no'] = $condition['quote_no'];
			
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
			
			if ($condition['status'] == 'Y') {
				if ($quote['biz_quote_status'] == 'APPROVED') $logiCheck['quote_status'] = $status;
			} else {
				$logiCheck['quote_status'] = $status;
			}
			
			$approveLog = array (
				'inquiry_no' => $quote['inquiry_no'],
				'type' => '物流报价审核',
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
	 * @desc 物流报价审核列表接口
	 * @author liujf 2017-07-02
	 * @return json
	 */
// 	public function logiQuoteApproveListAction() {
// 		$condition = $this->put_data;
    	
// 		$condition['belong'] = 'LOGISTICS';
		
//     	$res = $this->approveLogModel->getList($condition);
    		
// 		$this->jsonReturn($res);
// 	}
    
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