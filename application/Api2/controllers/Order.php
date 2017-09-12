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

        $order_id = $this->getPut('id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $oder_moder = new OrderModel();
        $info = $oder_moder->info($order_id);
        if ($info) {
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
