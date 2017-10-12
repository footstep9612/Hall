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
class OrderLogModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_log';
    protected $dbName = 'erui2_order'; //数据库名称

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
    }

    public function getGroup($group) {

        switch ($group) {
            case 'OUTBOUND':
                return '出库';
            case 'LOGISTICS':
                return '物流';
            case 'DELIVERY':
                return '交收';
            case 'COLLECTION':
                return '收款';
            case 'CREDIT':
                return '授信';
            default : return null;
        }
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getlist($order_id) {

        return $this->field('content,log_at,log_group,out_no,waybill_no,amount'
                                . ',type,order_address_id,log_id,order_id')
                        ->where(['order_id' => $order_id, 'deleted_flag' => 'N'])
                        ->order('log_at ASC,id ASC')->select();
    }

    /**
     * 获取列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function CerditList($userInfo, $start_no, $pagesize) {

        if (empty($userInfo['buyer_id'])) {
            jsonReturn('', -1001, '用户ID缺失!');
        }
        $where['od.buyer_id'] = $userInfo['buyer_id'];
        $where['od.deleted_flag'] = 'N';
        $where['ol.log_group'] = 'CREDIT';    //工作分组--授信
        $where['ol.deleted_flag'] = 'N'; //删除状态

        $orderModel = new OrderModel();
        try {
            $orders = $orderModel->alias('od')
                    ->join('erui2_order.order_log ol on ol.order_id=od.id', 'left')
                    ->where($where)
                    ->limit($start_no, $pagesize)
                    ->order('ol.log_at desc')
                    ->select();
            echo $orderModel->_sql();
            $count = $orderModel->alias('od')
                    ->join('erui2_order.order_log ol on ol.order_id=od.id', 'left')
                    ->where($where)
                    ->count('od.id');
            if ($orders) {
                return [$orders, $count];
            } else {
                return false;
            }
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return false;
        }
    }

}
