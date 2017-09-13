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
        //parent::init();
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
	
	/* 获取订单执行日志
	 * @author  zhengkq
     * @date    2017-8-1 17:50:09
     * @param int $order_id // 订单ID
	 * @return array
     */
	public function operationsAction(){
		
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
