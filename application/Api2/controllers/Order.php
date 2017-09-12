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

    //put your code here
    public function infoAction() {

        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $oder_moder = new OrderModel();
        $info = $oder_moder->info($order_id);



        $delivery_model = new DeliveryModel();
        $deliverys = $delivery_model->getlist($order_id);

        $order_attach_model = new OrderAttachModel();
        $order_attachs = $order_attach_model->getlist($order_id);

        if ($order_attachs) {
            $info['order_attach'] = $order_attachs;
        } else {
            $info['order_attach'] = null;
        }
        $payment_model = new PaymentModel();
        $payments = $payment_model->getlist($order_id);

        $workflow_model = new WorkflowModel();
        $workflows = $workflow_model->getlist($order_id);

        $order_contact_model = new OrderContactModel();
        $order_contact = $order_contact_model->info($order_id);

        if ($order_contact) {
            $info['order_contact'] = $order_contact;
        } else {
            $info['order_contact'] = null;
        }
        $order_buyer_contact_model = new OrderBuyerContactModel();
        $order_buyer_contact = $order_buyer_contact_model->info($order_id);

        if ($order_buyer_contact) {
            $info['order_buyer_contact'] = $order_buyer_contact;
        } else {
            $info['order_buyer_contact'] = null;
        }
        $order_address_model = new OrderAddressModel();
        $order_address = $order_address_model->info($order_id);

        if ($order_address) {
            $info['order_address'] = $order_address;
        } else {
            $info['order_address'] = null;
        }
        if ($info) {
            $info['show_status_text'] = $oder_moder->getShowStatus($info['show_status']);
            $info['pay_status_text'] = $oder_moder->getPayStatus($info['pay_status']);
            $this->setvalue('workflows', $workflows);
            $this->setvalue('payments', $payments);
            $this->setvalue('deliverys', $deliverys);
            $this->jsonReturn($info);
        } elseif ($info === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

}
