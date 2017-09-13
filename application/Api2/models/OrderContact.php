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
class OrderContactModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_contact';
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

    public function info($order_id) {

        return $this->where(['order_id' => $order_id])->order('created_at desc')->find();
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getlistByOrderids($order_ids) {

        $data = $this->field('max(created_at), company ,order_id')
                ->where(['order_id' => ['in', $order_ids]])
                ->order('created_at desc')
                ->group('order_id')
                ->select();
        $deliverys = [];
        if ($data) {
            foreach ($data as $item) {
                $deliverys[$item['order_id']] = $item['company'];
            }
        }

        return $deliverys;
    }

}
