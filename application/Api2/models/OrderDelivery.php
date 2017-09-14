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
class OrderDeliveryModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_delivery';
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

        return $this->field('delivery_at,describe')
                        ->where(['order_id' => $order_id])
                        ->order('created_at ASC')->select();
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getlistByOrderids($order_ids) {

        $data = $this->field('min(delivery_at) as delivery_at,order_id')
                ->where(['order_id' => ['in', $order_ids], 'delivery_at' => ['gt', date('Y-m-d H:i:s')]])
                ->order('created_at ASC')
                ->group('order_id')
                ->select();
        $deliverys = [];
        if ($data) {
            foreach ($data as $item) {
                $deliverys[$item['order_id']] = $item['delivery_at'];
            }
        }

        return $deliverys;
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getlastdelivery_at($order_id) {

        $data = $this->field(' delivery_at,order_id')
                ->where(['order_id' => $order_id, 'delivery_at' => ['gt', date('Y-m-d H:i:s')]])
                ->order('created_at ASC')
                ->group('order_id')
                ->find();

        if ($data) {
            return $data['delivery_at'];
        } else {
            return null;
        }
    }

}
