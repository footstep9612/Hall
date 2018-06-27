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
class OrderaddressController extends PublicController {

    public function init() {
        parent::init();
    }

    /* 订单收货人信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   交收信息
     */

    public function LastAdressAction() {

        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }

        $order_address_model = new OrderAddressModel();
        $order_address = $order_address_model->info($order_id);


        if ($order_address) {

            $this->jsonReturn($order_address);
        } elseif ($order_address === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

}
