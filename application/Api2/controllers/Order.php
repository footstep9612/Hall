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
            $info['show_status_text'] = $oder_moder->getShowStatus($info['show_status']);
            $info['pay_status_text'] = $oder_moder->getPayStatus($info['pay_status']);
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
        echo $order_attach_model->_sql();
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
        echo $order_attach_model->_sql();
        if ($order_attachs) {
            $info['others'] = $order_attachs;
        } else {
            $info['others'] = null;
        }
    }

}
