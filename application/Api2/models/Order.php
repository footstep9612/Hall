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
class OrderModel extends PublicModel {

    //put your code here
    protected $tableName = 'order';
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

    public function info($order_id, $lang = 'en') {
        $field = 'id,order_no,po_no,execute_no,contract_date,address,status,show_status,pay_status,amount,trade_terms_bn,currency_bn';
        $field .= ',trans_mode_bn,(select trans_mode from erui2_dict.trans_mode as t where t.bn=trans_mode_bn and t.lang=\'' . $lang . '\') as trans_mode';
        $field .= ',from_country_bn,(select name from erui2_dict.country as t where t.bn=from_country_bn and t.lang=\'' . $lang . '\') as from_country';
        $field .= ',to_country_bn,(select name from erui2_dict.country as t where t.bn=to_country_bn and t.lang=\'' . $lang . '\') as to_country';
        $field .= ',from_port_bn,(select name from erui2_dict.port as t where t.bn=from_port_bn and t.lang=\'' . $lang . '\') as from_port';
        $field .= ',to_port_bn,(select name from erui2_dict.port as t where t.bn=to_port_bn and t.lang=\'' . $lang . '\') as to_port';
        return $this->field($field)
                        ->where(['id' => $order_id])->find();
    }

    /* 查询条件
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _getCondition($condition) {

        $where = [];
        $where['deleted_flag'] = 'N';
        $this->_getValue($where, $condition, 'order_no'); //平台订单号
        $this->_getValue($where, $condition, 'po_no'); //po编号
        $this->_getValue($where, $condition, 'execute_no'); //执行编号
        if (isset($condition['status']) && $condition['status']) {
            switch ($condition['status']) {
                case 'to_be_confirmed':

                    $where['show_status'] = 'UNCONFIRM';
                    break;
                case 'proceeding':

                    $where['show_status'] = 'GOING';
                    break;
                case 'finished':
                    $where['show_status'] = 'COMPLETED';
                    break;
            }
        }
        if (isset($condition['pay_status']) && $condition['pay_status']) {
            switch ($condition['pay_status']) {
                case 'unpaid':
                    $where['pay_status'] = 'UNPAY';
                    break;
                case 'part_paid':
                    $where['pay_status'] = 'PARTPAY';
                    break;
                case 'payment_completed':
                    $where['pay_status'] = 'PAY';
                    break;
            }
        }

        $this->_getValue($where, $condition, 'contract_date', 'between'); //签约日期
        if (isset($condition['buyer_id']) && $condition['buyer_id']) {
            $where['buyer_id'] = $condition['buyer_id'];
        }
        if (isset($condition['buyername']) && $condition['buyername']) {

            $buyermodel = new BuyerModel();
            $buyerids = $buyermodel->getBuyeridsByBuyerName($condition['buyername']);
            if ($buyerids) {
                $where['buyer_id'] = ['in', $buyerids];
            }
        }
        return $where;
    }

    /* 获取订单列表
     * @param array $condition // 查询条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getList($condition, $lang = 'en') {

        $where = $this->_getCondition($condition);
        list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,order_no,po_no,execute_no,contract_date,buyer_id,address,status,show_status,pay_status,amount,trade_terms_bn,currency_bn';
        $field .= ',trans_mode_bn,(select trans_mode from erui2_dict.trans_mode as t where t.bn=trans_mode_bn and t.lang=\'' . $lang . '\') as trans_mode';
        $field .= ',from_country_bn,(select name from erui2_dict.country as t where t.bn=from_country_bn and t.lang=\'' . $lang . '\') as from_country';
        $field .= ',to_country_bn,(select name from erui2_dict.country as t where t.bn=to_country_bn and t.lang=\'' . $lang . '\') as to_country';
        $field .= ',from_port_bn,(select name from erui2_dict.port as t where t.bn=from_port_bn and t.lang=\'' . $lang . '\') as from_port';
        $field .= ',to_port_bn,(select name from erui2_dict.port as t where t.bn=to_port_bn and t.lang=\'' . $lang . '\') as to_port';
        return $this
                        ->field($field)
                        ->where($where)->limit($start_no, $pagesize)->order('id desc')->select();
    }

    /* 获取订单数量
     * @param array $condition // 查询条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
    }

    /* 获取订单状态
     * @param int $show_status // 订单展示状态CONFIRM待确认 GOING.进行中  COMPLETED.已完成
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getShowStatus($show_status) {
        switch ($show_status) {

            case 'UNCONFIRM':
                return 'To be confirmed';

            case 'GOING':
                return 'Proceeding';

            case 'COMPLETED':
                return 'Finished';

            default :return'To be confirmed';
        }
    }

    /* 获取订单付款状态
     * @param int $status // 状态 支付状态 UNPAY未付款 PARTPAY部分付款  PAY已付款
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getPayStatus($pay_status) {
        switch ($pay_status) {
            case 'UNPAY':
                return 'Unpaid';

            case 'PARTPAY':
                return 'Part paid';

            case 'PAY':
                return 'Payment completed';

            default :return'Unpaid';
        }
    }

}
