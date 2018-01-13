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

    /**
     * 验证用户权限
     * Author:张玉良
     * @return string
     */
    public function checkAuthAction() {
        $groupid = $this->user['group_id'];

        if (isset($groupid)) {
            $maketareateam = new MarketAreaTeamModel();
            $users = [];
            $agent =0;
            if($groupid){
                if (is_array($groupid)) {
                    //查询是否是市场人员
                    $agent = $maketareateam->where('market_org_id in(' . implode(',', $groupid) . ')')->count('id');
                } else {
                    //查询是否是市场人员
                    if(is_numeric($groupid)){
                        $agent = $maketareateam->where('market_org_id=' . $groupid)->count('id');
                    }
                }
            }
            if ($agent > 0) {
                $results['code'] = '2';
                $results['message'] = '市场人员！';
            } else {
                $results['code'] = '3';
                $results['message'] = '其他人员！';
            }
        } else {
            $results['code'] = '-101';
            $results['message'] = '用户没有权限此操作！';
        }
        return $results;
    }

    /* 创建新订单
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */

    public function createAction() {
        $data = $this->getPut();
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        $ret = $this->checkOrderData($data);
        if ($ret === true) {
            $send = $this->saveOrder($data);
            $this->jsonReturn($send);
        } else {
            $this->jsonReturn($ret);
        }
    }

    /* 修改订单信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */

    public function updateAction() {
        $data = $this->getPut();
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        $ret = $this->checkOrderData($data, true);
        if ($ret === true) {
            $send = $this->saveOrder($data);
            $this->jsonReturn($send);
        } else {
            $this->jsonReturn($ret);
        }
    }

    /* 订单全部收款完成
     * @author  zhengkq
     * @date    2017-9-15 17:26:09
     * @param int $order_id // 订单ID
     * @return array
     */

    public function doneAction() {
        $data = $this->getPut();
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        if (isset($data['id']) && $data['id'] > 0) {
            $id = intval($data['id']);
            $orderModel = new OrderModel();
            $ret = $orderModel->where(['id' => $id])->setField(['show_status' => 'COMPLETED', 'pay_status' => 'PAY']);
            $this->jsonReturn(['code' => 1, 'message' => '处理完成']);
        } else {
            $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
        }
        $this->jsonReturn($send);
    }

    /* 获取订单详情基本信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */

    public function detailAction() {
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        if (isset($data['id']) && $data['id'] > 0) {
            $id = intval($data['id']);
            $lang = trim($data['lang']);
            if (!preg_match("/^[a-z]{1,2}(-[a-z]{1,2}?)$/i", $lang)) {
                $lang = 'zh';
            }
            $orderModel = new OrderModel();
            $field = '`id`,`order_no`,`po_no`,`execute_no`,`contract_date`,' .
                    '`execute_date`,`order_agent`,' .
                    '`buyer_id`,`agent_id`,`order_contact_id`,`buyer_contact_id`,' .
                    '`amount`,`currency_bn`,`trade_terms_bn`,`trans_mode_bn`,' .
                    '`from_country_bn`,`from_port_bn`,`to_country_bn`,`to_port_bn`,' .
                    '`address`,`status`,`show_status`,`pay_status`,`created_at`,`expected_receipt_date`,`remark`';
            $info = $orderModel->where(['id' => $id])->field($field)->find();
            if (empty($info)) {
                $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
            }
            //获取客户名称
            $buyerModel = new BuyerModel();
            $buyerInfo = $buyerModel->field('name,buyer_no')->where(['id' => $info['buyer_id']])->find();
            $info['buyer'] = isset($buyerInfo['name']) ? $buyerInfo['name'] : '';
            $info['buyer_no'] = isset($buyerInfo['buyer_no']) ? $buyerInfo['buyer_no'] : '';
            //获取市场经办人姓名
            $employeeModel = new EmployeeModel();
            $employee = $employeeModel->where(['id' => $info['agent_id']])->getField('name');
            $info['agent'] = $employee;
            //读取采购商信息
            $buyerContact = new OrderBuyerContactModel();
            $buyer = $buyerContact->where(['id' => $info['buyer_contact_id']])->find();
            $info['buyer_contact_company'] = $buyer['company'];
            $info['buyer_contact_name'] = $buyer['name'];
            $info['buyer_contact_phone'] = $buyer['phone'];
            $info['buyer_contact_email'] = $buyer['email'];
            //读取供货商信息
            $orderContact = new OrderContactModel();
            $contact = $orderContact->where(['id' => $info['order_contact_id']])->find();
            $info['order_contact_company'] = $contact['company'];
            $info['order_contact_name'] = $contact['name'];
            $info['order_contact_phone'] = $contact['phone'];
            $info['order_contact_email'] = $contact['email'];

            $this->jsonReturn(['code' => 1, 'message' => 'success', 'data' => $info]);
        } else {
            $this->jsonReturn(['code' => -101, 'message' => '参数传递错误']);
        }
    }

    /* 获取订单附件信息
     * @author  zhengkq
     * @date    2017-09-13 17:50:09
     * @param int $order_id // 订单ID
     * @return array
     */

    public function attachmentsAction() {
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);

        if (isset($data['id']) && $data['id'] > 0) {
            $orderAttach = new OrderAttachModel();
            $condition = [
                'order_id' => intval($data['id']),
                'deleted_flag' => 'N'
            ];
            if (intval($data['all']) != 1) {
                $condition['attach_group'] = ['in', ['PO', 'OTHERS']];
            }
            $data = $orderAttach->where($condition)->field('id,attach_group,attach_name,attach_url')->select();
        } else {
            $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
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

    public function deliveryAction() {
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        if (isset($data['id']) && $data['id'] > 0) {
            $orderDelivery = new OrderDeliveryModel();
            $condition = [
                'order_id' => intval($data['id'])
            ];
            $data = $orderDelivery->where($condition)->field('id,describe,delivery_at')->select();
        } else {
            $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
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

    public function consigneeAction() {
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        if (isset($data['id']) && $data['id'] > 0) {
            $orderAddress = new OrderAddressModel();
            $condition = [
                'order_id' => intval($data['id']),
                'deleted_flag' => 'N'
            ];
            $data = $orderAddress->where($condition)->field('id,name,tel_number,country,zipcode,city,fax,address,email')->select();
        } else {
            $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
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

    public function settlementAction() {
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        if (isset($data['id']) && $data['id'] > 0) {
            $orderPayment = new OrderPaymentModel();
            $condition = [
                'order_id' => intval($data['id'])
            ];
            $data = $orderPayment->where($condition)->field('id,name,amount,payment_mode,payment_at')->select();
        } else {
            $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
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

    private function saveOrder($data) {
        $data = $this->_trim($data);
        $order['po_no'] = $this->safeString($data['po_no']);
        $order['execute_no'] = $this->safeString($data['execute_no']);
        $contract_date = strtotime($data['contract_date']);
        if ($contract_date > 0) {
            $order['contract_date'] = date('Y-m-d', $contract_date);
        }
        $execute_date = strtotime($data['execute_date']);
        if ($contract_date > 0) {
            $order['execute_date'] = date('Y-m-d', $execute_date);
        }
        $expected_receipt_date = strtotime($data['expected_receipt_date']);
        if ($expected_receipt_date > 0) {
            $order['expected_receipt_date'] = date('Y-m-d', $expected_receipt_date);
        }
        if (!empty($data['order_agent'])) {
            $order['order_agent'] = $this->safeString($data['order_agent']);
        }
        //采购商ID
        if (is_numeric($data['buyer_id']) && $data['buyer_id'] > 0) {
            $order['buyer_id'] = intval($data['buyer_id']);
        }
        //市场经办人ID
        if (is_numeric($data['agent_id']) && $data['agent_id'] > 0) {
            $order['agent_id'] = intval($data['agent_id']);
        }
        $data['amount'] = str_replace(',', '', $data['amount']);
        if (is_numeric($data['amount'])) {
            if (doubleval($data['amount']) > 0) {
                $order['amount'] = doubleval($data['amount']); //订单金额
            } else {
                $this->jsonReturn(['code' => -101, 'message' => '订单金额必须大于0']);
            }
        }
        $order['currency_bn'] = $this->safeString($data['currency_bn']); //币种
        $order['trade_terms_bn'] = $this->safeString($data['trade_terms_bn']);    //贸易条款简码
        $order['trans_mode_bn'] = $this->safeString($data['trans_mode_bn']);    //运输方式简码
        $order['from_country_bn'] = $this->safeString($data['from_country_bn']); //起运国
        $order['from_port_bn'] = $this->safeString($data['from_port_bn']);    //起运港口
        $order['to_country_bn'] = $this->safeString($data['to_country_bn']);    //目的国
        $order['to_port_bn'] = $this->safeString($data['to_port_bn']);    //目的港口
        $order['address'] = $this->safeString($data['address']); //地址
        $order['remark'] = $this->safeString($data['remark']); //备注
        $order['order_contact_id'] = intval($data['order_contact_id']);
        $order['buyer_contact_id'] = intval($data['buyer_contact_id']);

        $orderModel = new OrderModel();

        //开始执行保存
        try {
            //保存订单基本信息
            if (isset($data['order_no']) && !empty($data['order_no'])) {
                $order_no = $data['order_no'];
                $info = $orderModel->where(['order_no' => $order_no, 'deleted_flag' => 'N'])->find();
                if (empty($info)) {
                    return ['code' => -105, '参数传递错误'];
                } elseif ($info['show_status'] == 'COMPLETED') {
                    $this->jsonReturn(['code' => -101, 'message' => '订单已完成，禁止修改']);
                }
                $ret = $orderModel->where(['id' => $info['id']])->save($order);
                if ($ret === false) {
                    return ['code' => -106, '更新订单信息失败' . $ret . $orderModel->getError()];
                }
                $order['id'] = $info['id'];
            } else {
                $order['created_at'] = date('Y-m-d H:i:s');
                $order['created_by'] = intval($this->user['id']);
                $order['order_no'] = $order_no = $this->generateOrderId();
                $order['show_status'] = 'GOING';
                $order['pay_status'] = 'UNPAY';
                $order['deleted_flag'] = 'N';
                $id = $orderModel->add($order);
                if (!$id) {
                    return ['code' => -106, '创建订单失败'];
                }
                $order['id'] = $id;
            }

            //保存采购商信息
            $orderContactRet = $this->saveBuyerContact($data, $order['id'], $refId);
            if ($orderContactRet['code'] != 1) {
                return $orderContactRet;
            }
            if ($refId > 0) {
                $orderModel->where(['id' => $order['id']])
                        ->setField(['buyer_contact_id' => $refId]);
            }
            //保存供应商信息
            $buyerContactRet = $this->saveOrderContact($data, $order['id'], $refId);
            if ($buyerContactRet['code'] != 1) {
                return $buyerContactRet;
            }
            if ($refId > 0) {
                $orderModel->where(['id' => $order['id']])
                        ->setField(['order_contact_id' => $refId]);
            }
            //保存商品信息
            $this->_saveOrderGoods($data, $order_no);

            $this->savePOFile($data, $order['id']);
            $this->saveOtherFiles($data, $order['id']);
            $this->saveDelivery($data, $order['id']);
            $this->saveConsignee($data, $order['id']);
            $this->saveSettlement($data, $order['id']);
            return ['code' => 1, 'message' => 'Success'];
        } catch (Exception $e) {
            return ['code' => -106, 'message' => '更新订单失败'];
        }
    }
    
    /**
     * @desc 保存订单商品信息
     *
     * @param array $data
     * @param string $orderNo
     * @author liujf
     * @time 2018-01-10
     */
    private function _saveOrderGoods($data, $orderNo) {
        $orderNo = $this->_trim($orderNo);
        if ($orderNo == '') $this->jsonReturn(['code'=>-105,'message'=>'订单ID不能为空']);
        $orderGoodsModel = new OrderGoodsModel();
        $time = date('Y-m-d H:i:s');
        foreach ($data['order_goods'] as $orderGoodsData) {
            $orderGoodsData['order_no'] = $orderNo;
            $orderGoodsData['lang'] = $orderGoodsData['lang'] == '' ? 'zh' : $orderGoodsData['lang'];
            $orderGoodsData['buy_number'] = intval($orderGoodsData['buy_number']) ? : null;
            $where = ['id' => intval($orderGoodsData['id'])];
            $hasGoods = $orderGoodsModel->where($where)->getField('id');
            if ($hasGoods) {
                $orderGoodsData['updated_by'] = $this->user['id'];
                $orderGoodsData['updated_at'] = $time;
                $orderGoodsModel->updateInfo($where, $orderGoodsData);
            } else {
                unset($orderGoodsData['price']);
                $orderGoodsData['created_by'] = $this->user['id'];
                $orderGoodsData['created_at'] = $time;
                $orderGoodsModel->addRecord($orderGoodsData);
            }
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

    private function saveOrderContact($data, $order_id, &$refId) {
        $contact['company'] = $this->safeString($data['order_contact_company']);
        $contact['name'] = $this->safeString($data['order_contact_name']);
        $contact['phone'] = $this->safeString($data['order_contact_phone']);
        $contact['email'] = $this->safeString($data['order_contact_email']);
        $contact['created_at'] = date('Y-m-d H:i:s');
        $contact['created_by'] = intval($this->user['id']);
        $contact['order_id'] = $order_id;
        $orderContact = new OrderContactModel();
        $ret = $orderContact->saveData($contact, $refId);
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

    private function saveBuyerContact($data, $order_id, &$refId) {
        $contact['company'] = $this->safeString($data['buyer_contact_company']);
        $contact['name'] = $this->safeString($data['buyer_contact_name']);
        $contact['phone'] = $this->safeString($data['buyer_contact_phone']);
        $contact['email'] = $this->safeString($data['buyer_contact_email']);
        $contact['created_at'] = date('Y-m-d H:i:s');
        $contact['created_by'] = intval($this->user['id']);
        $contact['order_id'] = $order_id;
        $buyerContact = new OrderBuyerContactModel();
        return $buyerContact->saveData($contact, $refId);
    }

    /* PO文件处理
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
     * @param array $data  提交的数据数组
     * @param int $order_id  订单ID
     * @return array
     */

    private function savePOFile($data, $order_id) {
        $attach = new OrderAttachModel();
        $attachCondition = [
            'order_id' => $order_id,
            'attach_group' => 'PO',
            'deleted_flag' => 'N'
        ];
        $attach->where($attachCondition)->setField(['deleted_flag' => 'Y']);
        if (isset($data['po_file']) && !empty($data['po_file'])) {
            $po = $attach->where($attachCondition)->find();
            if (empty($po)) {
                $attachCondition['attach_name'] = 'PO';
                $attachCondition['attach_url'] = $this->safeString($data['po_file']);
                $poRet = $attach->addData($attachCondition);
                return $poRet > 0;
            } else {
                $attach_url = $this->safeString($data['po_file']);
                $poRet = $attach->where(['id' => intval($po['id'])])
                        ->setField(['attach_url' => $attach_url, 'deleted_flag' => 'N']);
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

    private function saveOtherFiles($data, $order_id) {
        $attach = new OrderAttachModel();
        $num = 0;
        $condition = [
            'order_id' => $order_id,
            'attach_group' => 'OTHERS',
            'deleted_flag' => 'N'
        ];
        $attach->where($condition)->setField(['deleted_flag' => 'Y']);

        if (isset($data['other_files']) && is_array($data['other_files'])) {
            $others = $attach->where($condition)->getField('id');
            $userId = intval($this->user['id']);
            $now = date('Y-m-d H:i:s');
            foreach ($data['other_files'] as $file) {
                if (in_array($file['id'], $others)) {
                    $attach->save(
                            [
                        'attach_name' => $file['attach_name'],
                        'attach_url' => $file['attach_url'],
                        'deleted_flag' => 'N'
                            ], [
                        'id' => intval($file['id'])
                            ]
                    );
                    $used[] = intval($file['id']);
                } else {
                    $attach->add(
                            [
                                'order_id' => $order_id,
                                'attach_group' => 'OTHERS',
                                'deleted_flag' => 'N',
                                'attach_name' => $file['attach_name'],
                                'attach_url' => $file['attach_url'],
                                'created_by' => $userId,
                                'created_at' => $now
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

    private function saveConsignee($data, $order_id) {
        $orderAddress = new OrderAddressModel();
        $orderAddress->where(['order_id' => $order_id])->setField(['deleted_flag' => 'Y']);

        if (!isset($data['consignee_id'])) {
            return;
        }
        $consignees = explode(',', $data['consignee_id']);
        $consignees = array_map('intval', $consignees);
        $consignees = array_filter($consignees, function($item) {
            return $item > 0;
        });
        if (empty($consignees)) {
            return;
        }
        $buyercontact = new BuyercontactModel();
        $contacts = $buyercontact->where(['id' => ['in', $consignees]])->select();
        if (empty($contacts)) {
            return false;
        }
        $addresses = [];
        $userId = intval($this->user['id']);
        $now = date('Y-m-d H:i:s');
        foreach ($contacts as $item) {
            $address = [
                'order_id' => $order_id,
                'address' => $item['address'],
                'zipcode' => $item['zipcode'],
                'tel_number' => $item['phone'],
                'consignee_id' => $item['id'],
                'name' => $item['name'],
                'country' => $item['country_bn'],
                'city' => $item['city'],
                'email' => $item['email'],
                'fax' => $item['fax'],
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

    private function saveDelivery($data, $order_id) {
        $orderDelivery = new OrderDeliveryModel();
        $orderDelivery->where(['order_id' => $order_id])->delete();
        if (is_array($data['delivery'])) {
            $userId = intval($this->user['id']);
            $now = date('Y-m-d H:i:s');
            foreach ($data['delivery'] as $delivery) {
                $delivery['describe'] = trim($delivery['describe']);
                if (empty($delivery['describe'])) {
                    unset($delivery['describe']);
                }
                if (strlen($delivery['delivery_at']) > 10) {
                    $delivery['delivery_at'] = substr($delivery['delivery_at'], 0, 10);
                }
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/i", $delivery['delivery_at'])) {
                    unset($delivery['delivery_at']);
                }

                if (isset($delivery['describe']) || isset($delivery['delivery_at'])) {
                    unset($delivery['id']);
                    $delivery['order_id'] = $order_id;
                    $delivery['created_by'] = $userId;
                    $delivery['created_at'] = $now;
                    $orderDelivery->add($delivery);
                }
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

    private function saveSettlement($data, $order_id) {
        $orderPayment = new OrderPaymentModel();
        $orderPayment->where(['order_id' => $order_id])->delete();
        if (is_array($data['settlement'])) {
            $userId = intval($this->user['id']);
            $now = date('Y-m-d H:i:s');
            foreach ($data['settlement'] as $settlement) {
                $settlement['name'] = trim($settlement['name']);
                if (empty($settlement['name'])) {
                    unset($settlement['name']);
                }

                if (doubleval($settlement['amount']) > 0) {
                    $settlement['amount'] = doubleval($settlement['amount']);
                } else {
                    unset($settlement['amount']);
                }
                if (strlen($settlement['payment_at']) > 10) {
                    $settlement['payment_at'] = substr($settlement['payment_at'], 0, 10);
                }
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/i", $settlement['payment_at'])) {
                    unset($settlement['payment_at']);
                } else {
                    $settlement['payment_at'] = date('Y-m-d', strtotime($settlement['payment_at']));
                }
                $settlement['payment_mode'] = trim($settlement['payment_mode']);
                if (empty($settlement['payment_mode'])) {
                    unset($settlement['payment_mode']);
                }
                if (isset($settlement['name']) || isset($settlement['amount']) || isset($settlement['payment_mode']) || isset($settlement['payment_at'])
                ) {
                    unset($settlement['id']);
                    $settlement['order_id'] = $order_id;
                    $settlement['created_by'] = $userId;
                    $settlement['created_at'] = $now;
                    $orderPayment->add($settlement);
                }
            }
        }
    }

    /**
     * 检查订单数据
     *
     * */
    private function checkOrderData($data, $isUpdate = false) {
        if ($isUpdate) {
            if (empty($data['order_no']) || !is_numeric($data['order_no'])) {
                return ['code' => -101, 'message' => '订单编号不能为空'];
            }
            $order_no = trim($data['order_no']);
            $orderModel = new OrderModel();
            $info = $orderModel->where(['order_no' => $order_no, 'deleted_flag' => 'N'])->find();
            if (empty($info)) {
                return ['code' => -105, '参数传递错误'];
            } elseif ($info['show_status'] == 'COMPLETED') {
                return ['code' => -101, 'message' => '订单已完成，禁止修改'];
            }
        }
        if (!isset($data['po_no']) || empty($data['po_no']) || trim($data['po_no']) == '') {
            //modify at 2017-11-07 14:53 by zhengkq
            //return ['code' => -101, 'message' => 'PO号不能为空'];
        }
        if (!isset($data['execute_no']) || empty($data['execute_no']) || trim($data['execute_no']) == '') {
            return ['code' => -101, 'message' => '执行单号不能为空'];
        }
        if (isset($data['amount']) && !empty($data['amount']) && !is_numeric($data['amount'])) {
            return ['code' => -101, 'message' => '订单金额不是一个有效的数字'];
        }
        if (doubleval($data['amount']) > 100000000000) {
            return ['code' => -101, 'message' => '订单金额不能大于1000亿'];
        }
        if (isset($data['settlement']) && is_array($data['settlement'])) {
            foreach ($data['settlement'] as $item) {
                if (isset($item['amount']) && !empty($item['amount']) && !is_numeric($item['amount'])) {
                    return ['code' => -101, 'message' => '结算方式-金额不是一个有效的数字'];
                }
                if (doubleval($item['amount']) > 100000000000) {
                    return ['code' => -101, 'message' => '结算金额不能大于1000亿'];
                }
            }
        }
        return true;
    }

    private function safeString($str, $type = 'bn') {
        $badstr = "`!#\$%^&*{}\'\"?";
        for ($i = 0; $i < strlen($badstr); $i++) {
            $str = str_replace($badstr[$i], '', $str);
        }
        return $str;
    }

    /* 处理收货人信息
     * @author  zhengkq
     * @date    2017-09-14 13:00:09
     * @return string 返回最新订单编号
     */

    private function generateOrderId() {
        $today = date('Ymd');
        $order = new OrderModel();
        $order_no = $order->where(['order_no' => ['like', $today . '%']])->order('id desc')->getField('order_no');
        if (empty($order_no)) {
            return $today . '0001';
        }
        $no = substr($order_no, 8);
        $no = intval($no) + 1;
        return $today . str_pad($no, 4, '0', STR_PAD_LEFT);
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
        //$auth = $this->checkAuthAction();
        $condition = $this->getPut(); //查询条件
//        if ($auth['code'] == '2') {
//            $condition['agent_id'] = $this->user['id'];
//        }
        $oder_moder = new OrderModel();
        $data = $oder_moder->getList($condition);
        $count = $oder_moder->getCount($condition);
        if ($data) {
            $order_ids = [];
            foreach ($data as $key => $val) {
                $val['show_status_text'] = $oder_moder->getShowStatus($val['show_status']);
                $val['pay_status_text'] = $oder_moder->getPayStatus($val['pay_status']);  
                $val['can_delete'] = 'Y';
                $order_ids[$val['id']] = $key;                 
                $data[$key] = $val;
            }
            if(sizeof($order_ids) >0){
                $OrderLog = new OrderLogModel();
                $logCond = [
                    'log_group'=>'OUTBOUND',
                    'order_id' => ['in',array_keys($order_ids)],
                    'deleted_flag'=>'N'
                ];
                $logs = $OrderLog->field('distinct(order_id) as order_id')->where($logCond)->select();
                foreach($logs as $item){
                    $data_key = $order_ids[$item['order_id']] ;  
                    $data[$data_key]['can_delete'] = 'N';
                }
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
    /** 删除订单
     *
     * @author  Zhengkq
     * @date    2017-11-20 14:50
     * @version V2.0
     * @desc   订单未出库时可删除
     **/
    public function deleteAction() {
        //$auth = $this->checkAuthAction();
        $data = file_get_contents('php://input');
        $data = @json_decode($data, true);
        
        if (!isset($data['id']) || $data['id'] < 1) {
            $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
        }
        $order_id = intval($data['id']);
        $cond = ['id'=>$order_id,'deleted_flag'=>'N'];
        $order = new OrderModel();
        $hasOrder = $order->where($cond)->count();
        if($hasOrder != 1){
            $this->jsonReturn(['code' => -101, 'message' => '订单不存在']);
        }
        $OrderLog = new OrderLogModel();
        $logCond = [
            'log_group'=>'OUTBOUND',
            'order_id' => $order_id ,
            'deleted_flag'=>'N',
        ];
        $logs = $OrderLog->where($logCond)->count();
        if($logs >0){
            $this->jsonReturn(['code' => -101, 'message' => '订单已出库，删除失败']);
        }else{
            $hasOrder = $order->where($cond)->limit(1)->save(['deleted_flag'=>'Y']);
            $this->jsonReturn(['code' => 1, 'message' => '删除成功']);
        }
    }
    
    /**
     * @desc 获取订单商品列表接口
     *
     * @author liujf
     * @time 2018-01-10
     */
    public function getOrderGoodsListAction() {
        $condition = $this->_trim($this->put_data);
        if ($condition['order_no'] == '') $this->jsonReturn(['code' => -101, 'message' => '缺少订单编号参数']);
        $orderGoodsModel = new OrderGoodsModel();
        $field = 'id, sku, name, name_zh, brand, model, price, buy_number, nude_cargo_unit';
        $data = $orderGoodsModel->getList($condition, $field);
        if ($data) {
            $this->jsonReturn($data);
        } else {
            $this->jsonReturn(['code' => -101, 'message' => '数据为空']);
        }
    }
    
    /**
     * @desc 去掉数据两侧的空格
     *
     * @param mixed $data
     * @return mixed
     * @author liujf
     * @time 2018-01-11
     */
    private function _trim($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) $data[$k] = $this->_trim($v);
            return $data;
        } else if (is_object($data)) {
            foreach ($data as $k => $v) $data->$k = $this->_trim($v);
            return $data;
        } else if (is_string($data)) {
            return trim($data);
        } else {
            return $data;
        }
    }
}
