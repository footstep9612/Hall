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
        //  parent::init();
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
