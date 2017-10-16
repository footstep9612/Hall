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
class OrderPaymentModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_payment';
    protected $dbName = 'erui_order'; //数据库名称

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
        return $this->alias('op')
                        ->field('op.name,op.amount,op.payment_mode,op.payment_at,op.payment_mode as  payment_mode_bn')
                        ->where(['op.order_id' => $order_id, 'op.deleted_flag' => 'N'])
                        ->order('op.created_at ASC')
                        ->select();
//        return $this->alias('op')
//                        ->join('erui_dict.payment_mode dp on op.payment_mode_bn=dp.bn and dp.lang=\'en\'', 'left')
//                        ->field('op.amount,op.payment_mode_bn,op.payment_at,dp.name as payment_mode')
//                        ->where(['op.order_id' => $order_id, 'op.deleted_flag' => 'N'])
//                        ->order('op.created_at ASC')
//                        ->select();
    }

}
