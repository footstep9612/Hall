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
class OrderdeliveryController extends PublicController {

    public function init() {
        parent::init();
    }

    /* 订单交收信息列表
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   交收信息
     */

    public function ListAction() {

        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $delivery_model = new OrderDeliveryModel();
        $deliverys = $delivery_model->getlist($order_id);
        if ($deliverys) {
            $this->jsonReturn($deliverys);
        } elseif ($deliverys === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

}
