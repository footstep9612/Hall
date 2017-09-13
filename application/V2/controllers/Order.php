<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 * @author  zhongyg
 * @date    2017-9-12 13:08:12
 * @version V2.0
 * @desc
 */
class OrderController extends PublicController {

    public function init() {
        parent::init();
    }
	/* 创建新订单
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function createAction(){
		$data = $this->getPut(); 
		$data = file_get_contents('php://input');
		$data = @json_decode($data,true);		
		$send = $this->saveOrder($data);
        $this->jsonReturn($send);
	}
	/* 修改订单信息
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function updateAction(){
		$data = file_get_contents('php://input');
		$data = @json_decode($data,true);
		if(!isset($data['id']) || empty($data['id'])){
			$this->jsonReturn(['code'=>-101,'message'=>'参数订单ID为空']);
		}
		
		if(!isset($data['po_no']) || empty($data['po_no'])){
			$this->jsonReturn(['code'=>-101,'message'=>'PO号不能为空']);
		}
		
		if(!isset($data['execute_no']) || empty($data['execute_no'])){
			$this->jsonReturn(['code'=>-101,'message'=>'执行单号不能为空']);
		}
		
		$send['code'] = 1;
        $send['message'] = 'success';
        $this->jsonReturn($send);
	}
	
	/* 获取订单详情基本信息
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function detailAction(){
		$data = file_get_contents('php://input');
		$data = @json_decode($data,true);
		if($data['id'] != 1){
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
		$send['code'] = 1;
        $send['message'] = 'success';
		$send['data'] = [
			'id'=>1,                                 //订单ID 
			'order_no'=>'201701010001',              //订单编号 
			'po_no'=>'ST156452/123456',              //po编号
			'execute_no'=>'DY170912-0101',           //执行编号
			'contract_date'=>'2017-12-11',           //签约日期
			'buyer_id'=>'11',                        //采购商ID
			'buyer'=> 'Kuwait Drilling Company ',    //客户名称
			'agent_id'=>'12',                        //市场经办人ID
			'agent' =>'王XX',                        //市场经办人
			'order_contact_id'=>  122,               //采购商ID
			'order_contact_company'=>'采购商名称',   //采购商
			'order_contact_name'=>'张女士',                  //采购商联系人
			'order_contact_phone'=>'18888888888',               //采购商电话
			'order_contact_email'=>'18888888888@126.com',               //采购商Email
			'buyer_contact_id'=> 200,                  //供应商ID
			'buyer_contact_company'=>'供应商名称',             //供应商
			'buyer_contact_name'    =>'李先生',              //供应商联系人
			'buyer_contact_phone'=>'16666666666',               //供应商电话
			'buyer_contact_email'=>'16666666666@126.com',               //供应商Email
			'amount'=>'150000000',                   //订单金额
			'currency'=>'USD',                       //币种
			'trade_terms'=>'EXW',                       //贸易条款简码
			'trans_mode'=>'Ocean',                        //运输方式简码
			'from_country'=>'China',                      //起运国
			'from_port'=>'Qingdao',                         //起运港口
			'to_country'=>'India',                        //目的国
			'to_port'=>'Chennai',                           //目的港口
			'address'=>'Ahmadi City ， Block 8， 349th Street'                           //地址    
		];
        $this->jsonReturn($send);
	}
	
	/* 获取订单附件信息
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function attachmentsAction(){
		$data = file_get_contents('php://input');
		$data = @json_decode($data,true);
		if($data['id'] != 1){
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
		$send['code'] = 1;
        $send['message'] = 'success';
		$send['data'] = [
			[
				'id'=>'123',
				'attach_group'=>'PO',
				'attach_name'=>'PO单',
				'attach_url'=>'group1/M00/AB/CD/EF/GH/zdAfAkajxaDegAKlsRs.doc'
			],
			[
				'id'=>'124',
				'attach_group'=>'OTHERS',
				'attach_name'=>'报价单',
				'attach_url'=>'group1/M00/AB/CD/EF/GH/zdAfAkajxaDegAKlsRs2.doc'
			],
			[
				'id'=>'125',
				'attach_group'=>'OTHERS',
				'attach_name'=>'采购说明书',
				'attach_url'=>'group1/M00/AB/CD/EF/GH/zdAfAkajxaDegAKlsRs3.doc'
			]
		];
        $this->jsonReturn($send);
	}
	
	/* 获取订单收货信息
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function deliveryAction(){
		$data = file_get_contents('php://input');
		$data = @json_decode($data,true);
		if($data['id'] != 1){
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
		$send['code'] = 1;
        $send['message'] = 'success';
		$send['data'] = [
			[
				'id'=>'123',
				'describe'=>'第一批货',
				'delivery_at'=>'2017-05-06 12:20:10'
			],
			[
				'id'=>'124',
				'describe'=>'第二批',
				'delivery_at'=>'2017-05-16 12:20:10'
			]
		];
        $this->jsonReturn($send);
	}
	
	/* 获取订单收货人信息
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function consigneeAction(){
		$data = file_get_contents('php://input');
		$data = @json_decode($data,true);
		if($data['id'] != 1){
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
		$send['code'] = 1;
        $send['message'] = 'success';
		$send['data'] = [
			[
				'id'=>'123',
				'name'=>'Kuwait Drilling Company K.S.C.C.',  //联系人姓名
				'tel_number'=>'00965 23981598',  //电话
				'country'=>'Kuwait',  //国家
				'country'=>'Kuwait',  //国家
				'zipcode'=>'60111',  //邮编
				'city'=>'Ahmadi City',  //城市
				'fax'=>'00965 23981598',  //传真
				'address'=>'Ahmadi City ， Block 8， 349th Street',  //办公地址
				'email'=>'Ahmadi@Ahmadi.com',  //email
			]
		];
        $this->jsonReturn($send);
	}
	
	/* 获取订单结算信息
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function settlementAction(){
		$data = file_get_contents('php://input');
		$data = @json_decode($data,true);
		if($data['id'] != 1){
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
		$send['code'] = 1;
        $send['message'] = 'success';
		$send['data'] = [
			[
				'id'=>'123',
				'name'=>'第1笔款',
				'amount'=>'1000.00',
				'payment_mode'=>'在线支付',
				'payment_at'=>'2017-05-06'
			],
			[
				'id'=>'124',
				'name'=>'第2笔款',
				'amount'=>'1000.00',
				'payment_mode'=>'在线支付',
				'payment_at'=>'2017-05-16'
			],
			[
				'id'=>'125',
				'name'=>'第3笔款',
				'amount'=>'1000.00',
				'payment_mode'=>'在线支付',
				'payment_at'=>'2017-05-26'
			]
			
		];
        $this->jsonReturn($send);
	}
	
	/* 保存订单信息
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	private function saveOrder($data){		
		if(!isset($data['po_no']) || empty($data['po_no'])){
			return ['code'=>-101,'message'=>'PO号不能为空'];
		}
		if(!isset($data['execute_no']) || empty($data['execute_no'])){
			return ['code'=>-101,'message'=>'执行单号不能为空'];
		}
		$order['po_no'] = $this->safeString($data['po_no']);
		$order['execute_no'] = $this->safeString($data['execute_no']);
		$contract_date = strtotime($data['contract_date']);
		if($contract_date > 0){
			$order['contract_date'] = date('Y-m-d',$contract_date);
		}
		//采购商ID
		if(is_numeric($data['buyer_id']) && $data['buyer_id'] > 0){
			$order['buyer_id'] = intval($data['buyer_id']);
		}
		//市场经办人ID
		if(is_numeric($data['agent_id']) && $data['agent_id'] > 0){
			$order['agent_id'] = intval($data['agent_id']);
		}
		$order['amount']          = doubleval($data['amount']);//订单金额
		$order['currency_bn']     = $this->safeString($data['currency_bn']);//币种
		$order['trade_terms_bn']  = $this->safeString($data['trade_terms_bn']);	//贸易条款简码
		$order['trans_mode_bn']   = $this->safeString($data['trans_mode_bn']);	//运输方式简码
		$order['from_country_bn'] = $this->safeString($data['from_country_bn']);//起运国
		$order['from_port_bn']    = $this->safeString($data['from_port_bn']);	//起运港口
		$order['to_country_bn']   = $this->safeString($data['to_country_bn']);	//目的国
		$order['to_port_bn']      = $this->safeString($data['to_port_bn']);	//目的港口
		$order['address']         = $this->safeString($data['address']);//地址	
		$order['order_contact_id']= intval($data['order_contact_id']);
		$order['buyer_contact_id']= intval($data['buyer_contact_id']);
		
		$orderModel = new OrderModel();
		
		//开始执行存储事务
		
		//$orderModel->startTrans();
        try{
			$id = intval($data['id']);
			$order_no = trim($data['order_no']);
			if($id > 0 ){
				$info = $orderModel->where(['id'=>$id])->find();
				if(empty($info) || $order_no != $info['order_no']){
					//$orderModel->rollback();
					return ['code'=>-105,'参数传递错误'];
				}				
				$ret = $orderModel->update($order,['id'=>$id]);
				if($ret === false){
					//$orderModel->rollback();
					return ['code'=>-106,'更新订单信息失败'];
				}
			}else{
				$order['created_at'] = date('Y-m-d H:i:s');
				$order['created_by'] = intval($this->user['id']);
				$order['order_no'] = $this->generateOrderId();
				$id = $orderModel->add($order);
				if(!$id){
					//$orderModel->rollback();
					return ['code'=>-106,'创建订单失败'];
				}				
			}
			$order['id'] = $id;
			
			//保存采购商信息
			$orderContactRet = $this->saveBuyerContact($data,$order['id'],$refId);
			if($orderContactRet['code'] != 1){
				//$orderModel->rollback();
				return $orderContactRet;
			}
			if($refId != $order['order_contact_id']){
				$orderModel->where(['id'=>$id])
				           ->setField(['order_contact_id'=>$refId]);
			}
			//保存供应商信息
			$buyerContactRet = $this->saveOrderContact($data,$order['id'],$refId);
			if($buyerContactRet['code'] != 1){
				//$orderModel->rollback();
				return $buyerContactRet;
			}
			if($refId != $order['buyer_contact_id']){
				$orderModel->where(['id'=>$id])
				           ->setField(['buyer_contact_id'=>$refId]);
			}
			
			$this->savePOFile($data,$order['id']);
			$this->saveOtherFiles($data,$order['id']);
			$this->saveDelivery($data,$order['id']);
			$this->saveConsignee($data,$order['id']);
			$this->saveSettlement($data,$order['id']);
			//$orderModel->commit();
			return ['code'=>1,'Success'];
			
		}catch(Exception $e){
			//$orderModel->rollback();
			echo $e->getMessage().PHP_EOL;
			echo $e->getFile().PHP_EOL;
			echo $e->getLine().PHP_EOL;
			echo $e->getTraceAsString();die();
			return ['code'=>-106,'message'=>'更新订单失败'.$e->getMessage()];
		}		
	}
	//保存采购商信息
	private function saveOrderContact($data,$order_id,&$refId){
	    $contact['company'] = $this->safeString($data['order_contact_company']);
		$contact['name']    = $this->safeString($data['order_contact_name']);
		$contact['phone']   = $this->safeString($data['order_contact_phone']);
		$contact['email']   = $this->safeString($data['order_contact_email']);
		$contact['created_at'] = date('Y-m-d H:i:s');
		$contact['created_by'] =  intval($this->user['id']);
		$contact['order_id']   = $order_id;
		$orderContact = new OrderContactModel();
		$ret = $orderContact->saveData($contact,$refId);
		return $ret;		
	}
	//保存供应商信息
	private function saveBuyerContact($data,$order_id,&$refId){
		$contact['company'] = $this->safeString($data['buyer_contact_company']);
		$contact['name']    = $this->safeString($data['buyer_contact_name']);
		$contact['phone']   = $this->safeString($data['buyer_contact_phone']);
		$contact['email']   = $this->safeString($data['buyer_contact_email']);
		$contact['created_at'] = date('Y-m-d H:i:s');
		$contact['created_by'] = intval($this->user['id']);
		$contact['order_id']   = $order_id;
		$buyerContact = new OrderBuyerContactModel();
		return $buyerContact->saveData($contact,$refId);		
	}
	//PO文件处理
	private function savePOFile($data,$order_id){
		$attach = new OrderAttachModel();
		$attachCondition = [
		    'order_id'     => $order_id,
			'attach_group' => 'PO',
			'deleted_flag' => 'N'
		];
		$attach->where($attachCondition)->setField(['deleted_flag'=>'Y']);
		if(isset($data['po_file']) && !empty($data['po_file'])){
			$po = $attach->where($attachCondition)->find();
			if(empty($po)){
				$attachCondition['attach_name'] = 'PO';
				$attachCondition['attach_url']  = $this->safeString($data['po_file']);
				$poRet = $attach->addData($attachCondition);
				return $poRet >0;
			}else{
				$attach_url  = $this->safeString($data['po_file']);
				$poRet = $attach->where(['id'=>intval($po['id'])])
				                ->setField(['attach_url'=>$attach_url,'deleted_flag'=>'N']);
				return $poRet['code'] == 1;
			}
		}
	}
	//处理其他附件
	private function saveOtherFiles($data,$order_id){
		$attach = new OrderAttachModel();
        $num = 0;	
		$condition = [
		    'order_id'     => $order_id,
			'attach_group' => 'OTHERS',
			'deleted_flag' => 'N'
		];
		$attach->where($condition)->setField(['deleted_flag'=>'Y']);
		
		if(isset($data['other_files']) && is_array($data['other_files'])){			
			$others = $attach->where($condition)->getField('id');	
			$userId = intval($this->user['id']);
			$now = date('Y-m-d H:i:s');			
			foreach($data['other_files'] as $file){
				if(in_array($file['id'],$others)){
					$attach->save(
					    [
						    'attach_name' => $file['name'],
							'attach_url'  => $file['url'],
							'deleted_flag'=> 'N'
						],
						[
						    'id'=>intval($file['id'])
						]
					);
					$used[] = intval($file['id']);
				}else{
					$attach->add(
					    [
							'order_id'     => $order_id,
							'attach_group' => 'OTHERS',
							'deleted_flag' => 'N',
						    'attach_name' => $file['name'],
							'attach_url'  => $file['url'],
							'created_by'=>$userId,
							'created_at'=>$now
						]
					);
				}
				$num++;
			}			
		}
		return $num;
	}
	//处理收货人信息
	private function saveConsignee($data,$order_id){
		$orderAddress = new OrderAddressModel();
		$orderAddress->where(['order_id'=>$order_id])->setField(['deleted_flag'=>'Y']);
		
		if(!isset($data['consignee_id']) ){
			return false;
		}
		$consignees = explode(',',$data['consignee_id']);
		$consignees = array_map('intval',$consignees);
		$consignees = array_filter($consignees,function($item){
			return $item >0;
		});
		if(empty($consignees)){
			return;
		}
		$buyercontact = new BuyercontactModel();
		$contacts = $buyercontact->where(['id'=>['in',$consignees]])->select();
		if(empty($contacts)){
			return false;
		}		
		$addresses = [];
		$userId = intval($this->user['id']);
		$now = date('Y-m-d H:i:s');
		foreach($contacts as $item){
			$address = [
			    'order_id'=>$order_id,			    
			    'address'    => $item['address'],
			    'zipcode'    => $item['zipcode'],
			    'tel_number' => $item['phone'],
			    'name'       => $item['first_name'].' '.$item['last_name'],
			    'country'    => $item['country_bn'],
			    'city'       => $item['city'],
			    'email'      => $item['email'],
			    'fax'        => $item['fax'],
				'created_at' => $now,
				'created_by' => $userId
			];
			$orderAddress->add($address);
		}
	}
	
	
	private function saveDelivery($data,$order_id){
		$orderDelivery = new OrderDeliveryModel();
		$orderDelivery->where(['order_id'=>$order_id])->delete();
		if(is_array($data['delivery'])){
			$userId = intval($this->user['id']);
			$now = date('Y-m-d H:i:s');
			foreach($data['delivery'] as $delivery){
				if(empty($delivery['describe']) && empty($delivery['delivery_at'])){
					continue;
				}
				unset($delivery['id']);
				$delivery['order_id'] = $order_id;
				$delivery['created_by'] = $userId;
				$delivery['created_at'] = $now;
				$orderDelivery->add($delivery);
			}
		}
	}
	
	private function saveSettlement($data,$order_id){
		$orderPayment = new OrderPaymentModel();
		$orderPayment->where(['order_id'=>$order_id])->delete();
		if(is_array($data['settlement'])){
			$userId = intval($this->user['id']);
			$now = date('Y-m-d H:i:s');
			foreach($data['settlement'] as $settlement){
				if(empty($settlement['name']) && empty($settlement['amount'])
					&& empty($settlement['payment_mode'])
					&& empty($settlement['payment_at'])
				){
					continue;
				}
				unset($settlement['id']);
				$settlement['order_id'] = $order_id;
				$settlement['created_by'] = $userId;
				$settlement['created_at'] = $now;
				$orderPayment->add($settlement);
			}
		}
	}
	
	private function safeString($str,$type='bn'){
		return $str;
	}
	
	private function generateOrderId(){
		$today = date('Ymd');
		$order = new OrderModel();
		$order_no = $order->where(['order_no' => ['like',$today.'%']])->order('id desc')->getField('order_no');
		if(empty($order_no)){
			return $today.'0001';
		}
		$no = substr($order_no,8);
		$no = intval($no)+1;
		return $today.str_pad($no,4,'0',STR_PAD_LEFT);
	}

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function info($order_id) {

        return $this->where(['id' => $order_id])->find();
    }

    /* 获取订单列表
     *
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    //put your code here
    public function listAction() {

        $condition = $this->getPut(); //查询条件

        $oder_moder = new OrderModel();
        $data = $oder_moder->getList($condition);
        $count = $oder_moder->getCount($condition);
        if ($data) {
            $buyerids = [];
            foreach ($data as $order) {
                $buyerids[] = $order['buyer_id'];
            }
            $buyer_model = new BuyerModel();
            $buyernames = $buyer_model->getBuyerNamesByBuyerids($buyerids);
            foreach ($data as $key => $val) {
                if ($val['buyer_id'] && isset($buyernames[$val['buyer_id']])) {
                    $val['buyer_id_name'] = $buyernames[$val['buyer_id']];
                } else {
                    $val['buyer_id_name'] = '';
                }
                $val['show_status_text'] = $oder_moder->getShowStatus($val['show_status']);
                $val['pay_status_text'] = $oder_moder->getPayStatus($val['pay_status']);
                $data[$key] = $val;
            }
            $this->setvalue('count', intval($count));
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setvalue('count', 0);
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

}
