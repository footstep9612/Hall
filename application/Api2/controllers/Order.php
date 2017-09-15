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

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function infoAction() {
        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $oder_moder = new OrderModel();
        $info = $oder_moder->info($order_id);
        if ($info) {
            if ($info['buyer_id'] != $this->user['id']) {
                $this->setCode(MSG::MSG_FAILED);
                $this->setMessage('该订单不是你发布的!');
                $this->jsonReturn(null);
            }
            $info['show_status_text'] = $oder_moder->getShowStatus($info['show_status']);
            $info['pay_status_text'] = $oder_moder->getPayStatus($info['pay_status']);
            $delivery_model = new OrderDeliveryModel();
            $delivery_at = $delivery_model->getlastdelivery_at($order_id);

            $info['delivery_at'] = $delivery_at;
            if ($delivery_at) {
                $info['delivery_left'] = ceil((strtotime($delivery_at) - time()) / 86400);
            } else {
                $info['delivery_left'] = null;
            }

            $this->_setOrderAttachOther($info, $order_id); //获取附件
            $this->_setOrderAttachPo($info, $order_id); //获取附件
            $this->_setOrderBuyerContact($info, $order_id); //获取采购商信息
            $this->_setOrderContact($info, $order_id); //获取供应商信息
            $this->jsonReturn($info);
        } elseif ($info === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

    /* 获取采购商信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderBuyerContact(&$info, $order_id) {
        $order_buyer_contact_model = new OrderBuyerContactModel();
        $order_buyer_contact = $order_buyer_contact_model->info($order_id);

        if ($order_buyer_contact) {
            $info['order_buyer_contact'] = $order_buyer_contact;
        } else {
            $info['order_buyer_contact'] = null;
        }
    }

    /* 获取供应商信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderContact(&$info, $order_id) {
        $order_contact_model = new OrderContactModel();
        $order_contact = $order_contact_model->info($order_id);

        if ($order_contact) {
            $info['order_contact'] = $order_contact;
        } else {
            $info['order_contact'] = null;
        }
    }

    /* 获取附件列表信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderAttachPo(&$info, $order_id) {
        $order_attach_model = new OrderAttachModel();
        $order_attachs = $order_attach_model->getlist($order_id, 'PO');
        if ($order_attachs) {
            $info['po'] = $order_attachs[0];
        } else {
            $info['po'] = null;
        }
    }

    /* 获取附件列表信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderAttachOther(&$info, $order_id) {
        $order_attach_model = new OrderAttachModel();
        $order_attachs = $order_attach_model->getlist($order_id, 'OTHERS');

        if ($order_attachs) {
            $info['others'] = $order_attachs;
        } else {
            $info['others'] = null;
        }
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

        $order_moder = new OrderModel();
        $condition['buyer_id'] = $this->user['buyer_id'];
        $data = $order_moder->getList($condition);

        $count = $order_moder->getCount($condition);
        if ($data) {
            $orderids = $buyerids = [];
            foreach ($data as $order) {
                $buyerids[] = $order['buyer_id'];
                $orderids[] = $order['id'];
            }
            $delivery_model = new OrderDeliveryModel();
            $delivery_ats = $delivery_model->getlistByOrderids($orderids);

            $contact_model = new OrderContactModel();
            $contacts = $contact_model->getlistByOrderids($orderids);
            $buyer_model = new OrderBuyerContactModel();
            $buyers = $buyer_model->getlistByOrderids($orderids);

            foreach ($data as $key => $val) {
                if (isset($delivery_ats[$val['id']]) && $delivery_ats[$val['id']]) {
                    $val['delivery_at'] = $delivery_ats[$val['id']];
                } else {
                    $val['delivery_at'] = '';
                }

                if (isset($buyers[$val['id']]) && $buyers[$val['id']]) {
                    $val['buyer'] = $buyers[$val['id']];
                } else {
                    $val['buyer'] = '';
                }

                if (isset($contacts[$val['id']]) && $contacts[$val['id']]) {
                    $val['supplier'] = $contacts[$val['id']];
                } else {
                    $val['supplier'] = '';
                }
                $val['show_status_text'] = $order_moder->getShowStatus($val['show_status']);
                $val['pay_status_text'] = $order_moder->getPayStatus($val['pay_status']);
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
