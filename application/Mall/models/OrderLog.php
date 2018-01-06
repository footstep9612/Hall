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
    protected $dbName = 'erui_order'; //数据库名称

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
                        ->group('log_at,waybill_no')
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
                    ->join('erui_order.order_log ol on ol.order_id=od.id', 'left')
                    ->where($where)
                    ->limit($start_no, $pagesize)
                    ->order('ol.log_at desc')
                    ->select();

            $count = $orderModel->alias('od')
                    ->join('erui_order.order_log ol on ol.order_id=od.id', 'left')
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

    /**
     * 获取列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getBuyerLogList($condition = [], $start_no, $pagesize) {
        if (!empty($condition['buyer_id'])) {
            $where['b.buyer_id'] = $condition['buyer_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有客户id!';
            return $results;
        }
        if (!empty($condition['order_id'])) {
            $where['a.order_id'] = $condition['order_id'];
        }
        if (!empty($condition['log_group'])) {
            $where['a.log_group'] = $condition['log_group'];
        }
        $where['a.deleted_flag'] = 'N';
        $where['b.deleted_flag'] = 'N';
        $field = 'a.id,a.order_id,a.log_group,a.content,a.out_no,a.waybill_no,a.log_at,a.amount,a.type,a.log_id,b.order_no,b.po_no,b.execute_no,b.buyer_id';

        try {
            $count = $this->alias('a')
                ->join('erui_order.order b ON a.order_id = b.id', 'LEFT')
                ->where($where)
                ->count('a.id');

            $list = $this->alias('a')
                ->join('erui_order.order b ON a.order_id = b.id', 'LEFT')
                ->field($field)
                ->where($where)
                ->limit($start_no, $pagesize)
                ->order('a.created_at asc')
                ->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = $count;
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

}
