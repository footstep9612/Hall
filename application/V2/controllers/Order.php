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
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function createAction(){
        $data = $this->getPut(); 
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true);  
        $ret = $this->checkOrderData($data);
		if($ret === true){
			$send = $this->saveOrder($data);
			$this->jsonReturn($send);
		}else{
			$this->jsonReturn($ret);
		}
        
    }
    /* 修改订单信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function updateAction(){
        $data = $this->getPut(); 
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true); 
		$ret = $this->checkOrderData($data,true);
		if($ret === true){
			$send = $this->saveOrder($data);
			$this->jsonReturn($send);
		}else{
			$this->jsonReturn($ret);
		}
    }
	
	/* 订单全部收款完成
     * @author  zhengkq
     * @date    2017-9-15 17:26:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function doneAction(){
        $data = $this->getPut(); 
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true);        
        if(isset($data['id']) && $data['id'] >0){
			$id = intval($data['id']);
			$orderModel = new OrderModel();
			$ret = $orderModel->where(['id'=>$id])->setField(['show_status'=>'COMPLETED','pay_status'=>'PAY']);
			$this->jsonReturn(['code'=>1,'message'=>'处理完成']);
		}else{
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
        $this->jsonReturn($send);
    }
    
    /* 获取订单详情基本信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function detailAction(){
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true);
		if(isset($data['id']) && $data['id'] >0){
			$id = intval($data['id']);
			$lang = trim($data['lang']);
			if(!preg_match("/^[a-z]{1,2}(-[a-z]{1,2}?)$/i",$lang)){
				$lang = 'zh';
			}
			$orderModel = new OrderModel();
			$field = '`id`,`order_no`,`po_no`,`execute_no`,`contract_date`,'.
			         '`buyer_id`,`agent_id`,`order_contact_id`,`buyer_contact_id`,'.
					 '`amount`,`currency_bn`,`trade_terms_bn`,`trans_mode_bn`,'.
					 '`from_country_bn`,`from_port_bn`,`to_country_bn`,`to_port_bn`,'.
					 '`address`,`status`,`show_status`,`pay_status`,`created_at`';
			$info = $orderModel->where(['id'=>$id])->field($field)->find();
			if(empty($info)){
				$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
			}
			//获取客户名称
			$buyerModel = new BuyerModel();
			$buyerInfo = $buyerModel->where(['id'=>$info['buyer_id']])->getField('name');
			$info['buyer'] = $buyerInfo;
			//获取市场经办人姓名
			$employeeModel = new EmployeeModel();
			$employee = $employeeModel->where(['id'=>$info['agent_id']])->getField('name');
			$info['agent'] = $employee;
			//读取采购商信息
			$buyerContact = new OrderBuyerContactModel();
			$buyer = $buyerContact->where(['id'=>$info['buyer_contact_id']])->find();
			$info['buyer_contact_company'] = $buyer['company'];
			$info['buyer_contact_name'] = $buyer['name'];
			$info['buyer_contact_phone'] = $buyer['phone'];
			$info['buyer_contact_email'] = $buyer['email'];
			//读取供货商信息
			$orderContact = new OrderContactModel();
			$contact = $orderContact->where(['id'=>$info['order_contact_id']])->find();
			$info['order_contact_company'] = $contact['company'];
			$info['order_contact_name'] = $contact['name'];
			$info['order_contact_phone'] = $contact['phone'];
			$info['order_contact_email'] = $contact['email'];			
			
			$this->jsonReturn(['code'=>1,'message'=>'success','data'=>$info]);
		}else{
			 $this->jsonReturn(['code'=>-101,'message'=>'参数传递错误']);
		}		
    }
    
    /* 获取订单附件信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function attachmentsAction(){
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true);
        if(isset($data['id']) && $data['id'] > 0){
            $orderAttach = new OrderAttachModel();
			$condition = [
			    'order_id'=>intval($data['id']),
				'attach_group'=>['in',['PO','OTHERS']],
				'deleted_flag'=>'N'
			];
			$data = $orderAttach->where($condition)->field('id,attach_name,attach_url')->select();
        }else{
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
        $send['code'] = 1;
        $send['message'] = 'success';
        $send['data'] = $data;
        $this->jsonReturn($send);
    }
    
    /* 获取订单收货信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function deliveryAction(){
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true);
        if(isset($data['id']) && $data['id'] > 0){
			$orderDelivery = new OrderDeliveryModel();
			$condition = [
			    'order_id'=>intval($data['id'])
			];
			$data = $orderDelivery->where($condition)->field('id,describe,delivery_at')->select();
        }else{
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
        $send['code'] = 1;
        $send['message'] = 'success';
        $send['data'] = $data;
        $this->jsonReturn($send);
    }
    
    /* 获取订单收货人信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function consigneeAction(){
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true);
        if(isset($data['id']) && $data['id'] > 0){
			$orderAddress = new OrderAddressModel();
			$condition = [
			    'order_id'=>intval($data['id']),
				'deleted_flag'=>'N'
			];
			$data = $orderAddress->where($condition)->field('id,name,tel_number,country,zipcode,city,fax,address,email')->select();
        }else{
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
        $send['code'] = 1;
        $send['message'] = 'success';
        $send['data'] = $data;
        $this->jsonReturn($send);
    }
    
    /* 获取订单结算信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */
    public function settlementAction(){
        $data = file_get_contents('php://input');
        $data = @json_decode($data,true);
         if(isset($data['id']) && $data['id'] > 0){
			$orderPayment = new OrderPaymentModel();       
			$condition = [
			    'order_id'=>intval($data['id'])
			];
			$data = $orderPayment->where($condition)->field('id,name,amount,payment_mode,payment_at')->select();
        }else{
			$this->jsonReturn(['code'=>-101,'message'=>'订单不存在']);
		}
        $send['code'] = 1;
        $send['message'] = 'success';
        $send['data'] = $data;
        $this->jsonReturn($send);
    }
    
    /* 保存订单信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param array $data // 提交的数据数组
     * @return array
     */
    private function saveOrder($data){        
        
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
        $order['trade_terms_bn']  = $this->safeString($data['trade_terms_bn']);    //贸易条款简码
        $order['trans_mode_bn']   = $this->safeString($data['trans_mode_bn']);    //运输方式简码
        $order['from_country_bn'] = $this->safeString($data['from_country_bn']);//起运国
        $order['from_port_bn']    = $this->safeString($data['from_port_bn']);    //起运港口
        $order['to_country_bn']   = $this->safeString($data['to_country_bn']);    //目的国
        $order['to_port_bn']      = $this->safeString($data['to_port_bn']);    //目的港口
        $order['address']         = $this->safeString($data['address']);//地址    
        $order['order_contact_id']= intval($data['order_contact_id']);
        $order['buyer_contact_id']= intval($data['buyer_contact_id']);
		
        $orderModel = new OrderModel();
        
        //开始执行保存        
        try{
            //保存订单基本信息
            if(isset($data['order_no']) && !empty($data['order_no']) ){
                $order_no = trim($data['order_no']);
                $info = $orderModel->where(['order_no'=>$order_no,'deleted_flag'=>'N'])->find();
                if(empty($info)){
                    return ['code'=>-105,'参数传递错误'];
                }elseif($info['show_status'] == 'COMPLETED'){
					$this->jsonReturn(['code'=>-101,'message'=>'订单已完成，禁止修改']);
				}
                $ret = $orderModel->where(['id'=>$info['id']])->save($order);
                if($ret === false){
                    return ['code'=>-106,'更新订单信息失败'.$ret.$orderModel->getError()];
                }
                $order['id'] = $info['id'];
            }else{
                $order['created_at'] = date('Y-m-d H:i:s');
                $order['created_by'] = intval($this->user['id']);
                $order['order_no'] = $this->generateOrderId();
				$order['show_status']= 'GOING';	
				$order['pay_status']= 'UNPAY';							
		        $order['deleted_flag']= 'N';
                $id = $orderModel->add($order);
                if(!$id){
                    return ['code'=>-106,'创建订单失败'];
                }
				$order['id'] = $id;
            }
                        
            //保存采购商信息
            $orderContactRet = $this->saveBuyerContact($data,$order['id'],$refId);
            if($orderContactRet['code'] != 1){
                return $orderContactRet;
            }
            if($refId >0){
                $orderModel->where(['id'=>$order['id']])
                           ->setField(['buyer_contact_id'=>$refId]);
            }
            //保存供应商信息
            $buyerContactRet = $this->saveOrderContact($data,$order['id'],$refId);
            if($buyerContactRet['code'] != 1){
                return $buyerContactRet;
            }
            if($refId > 0){
                $orderModel->where(['id'=>$order['id']])
                           ->setField(['order_contact_id'=>$refId]);
            }
            
            $this->savePOFile($data,$order['id']);
            $this->saveOtherFiles($data,$order['id']);
            $this->saveDelivery($data,$order['id']);
            $this->saveConsignee($data,$order['id']);
            $this->saveSettlement($data,$order['id']);
            return ['code'=>1,'message'=>'Success'];
            
        }catch(Exception $e){
            return ['code'=>-106,'message'=>'更新订单失败'.$e->getMessage()];
        }        
    }
	/* 保存供应商信息
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
	 * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
	 * @param ptr string $refId 记录ID引用
     * @return array
     */
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
	/* 保存采购商信息
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
	 * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
	 * @param ptr string $refId 记录ID引用
     * @return array
     */
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
	/* PO文件处理
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
	 * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
     * @return array
     */
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
		return false;
    }
	/* 处理其他附件
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
	 * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
     * @return int 返回处理成功文件数
     */
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
                            'attach_url'  => $file['file'],
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
                            'attach_url'  => $file['file'],
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
	/* 处理收货人信息
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
	 * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
     * @return void 
     */
    private function saveConsignee($data,$order_id){
        $orderAddress = new OrderAddressModel();
        $orderAddress->where(['order_id'=>$order_id])->setField(['deleted_flag'=>'Y']);
        
        if(!isset($data['consignee_id']) ){
            return;
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
		return true;
    }
    
    /* 处理交收信息
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
	 * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
     * @return void 
     */
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
    /* 处理结算方式
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
	 * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
     * @return void 
     */
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
	/**
	* 检查订单数据
	*
	**/
    private function checkOrderData($data,$isUpdate =false){		
		if($isUpdate){
			if(empty($data['order_no']) || !is_numeric($data['order_no'])){
				return ['code'=>-101,'message'=>'订单编号不能为空'];
			}
			$order_no = trim($data['order_no']);
			$orderModel = new OrderModel();
			$info = $orderModel->where(['order_no'=>$order_no,'deleted_flag'=>'N'])->find();
			if(empty($info)){
				return ['code'=>-105,'参数传递错误'];
			}elseif($info['show_status'] == 'COMPLETED'){
				return ['code'=>-101,'message'=>'订单已完成，禁止修改'];
			}
		}
		if(!isset($data['po_no']) || empty($data['po_no']) || trim($data['po_no'])==''){
            return ['code'=>-101,'message'=>'PO号不能为空'];
        }
        if(!isset($data['execute_no']) || empty($data['execute_no']) || trim($data['execute_no'])==''){
            return ['code'=>-101,'message'=>'执行单号不能为空'];
        }
		if(isset($data['amount']) && !is_numeric($data['amount'])){
			return ['code'=>-101,'message'=>'订单金额不是一个有效的数字'];
		}
		if(isset($data['settlement']) && is_array($data['settlement'])){
			foreach($data['settlement'] as $item){
				if(isset($item['amount']) && !is_numeric($item['amount'])){
					return ['code'=>-101,'message'=>'结算方式-金额不是一个有效的数字'];
				}
			}
		}
		return true;
	}
    private function safeString($str,$type='bn'){
		$badstr = "`!@#\$%^&*{}\'\"?";
		for($i=0;$i<strlen($badstr);$i++){
			$str = str_replace($badstr[$i],'',$str);
		}
        return $str;
    }
    /* 处理收货人信息
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
     * @return string 返回最新订单编号 
     */
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
