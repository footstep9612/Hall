<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 * @author  zhongyg
 * @date    2017-9-12 13:09:26
 * @version V2.0
 * @desc
 */
class PaymentModel extends PublicModel {

    //put your code here
    protected $tableName = 'payment';
    protected $dbName = 'erui2_order'; //数据库名称

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getlist($order_id) {

        return $this->field('amount,payment_mode,payment_at')
                        ->where(['order_id' => $order_id, 'deleted_flag' => 'N'])
                        ->order('created_at ASC')
                        ->select();
    }

}
