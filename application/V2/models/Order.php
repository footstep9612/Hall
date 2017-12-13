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
    protected $dbName = 'erui_order'; //数据库名称

    const SHOW_STATUS_UNCONFIRM = 'UNCONFIRM'; // 订单展示状态CONFIRM待确认
    const SHOW_STATUS_GOING = 'GOING'; // 订单展示状态  GOING.进行中
    const SHOW_STATUS_COMPLETED = 'COMPLETED'; // 订单展示状态 COMPLETED.已完成
    const PAY_STATUS_UNCONFIRM = 'UNPAY'; //支付状态 UNPAY未付款
    const PAY_STATUS_GOING = 'PARTPAY'; //支付状态 PARTPAY部分付款
    const PAY_STATUS_COMPLETED = 'PAY'; //支付状态  PAY已付款

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
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
                return '待确认';

            case 'GOING':
                return '进行中';

            case 'COMPLETED':
                return '已完成';

            default :return'待确认';
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
                return '未收款';

            case 'PARTPAY':
                return '部分收款';

            case 'PAY':
                return '收款完成';

            default :return'未付款';
        }
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function info($order_id, $lang = 'zh') {
        $field = 'id,order_no,po_no,execute_no,contract_date,address,status,show_status,pay_status,amount,trade_terms_bn,currency_bn';
        $field .= ',trans_mode_bn,(select trans_mode from erui_dict.trans_mode as t where t.bn=trans_mode_bn and t.lang=\'' . $lang . '\') as trans_mode';
        $field .= ',from_country_bn,(select name from erui_dict.country as t where t.bn=from_country_bn and t.lang=\'' . $lang . '\') as from_country';
        $field .= ',to_country_bn,(select name from erui_dict.country as t where t.bn=to_country_bn and t.lang=\'' . $lang . '\') as to_country';
        $field .= ',from_port_bn,(select name from erui_dict.port as t where t.bn=from_port_bn and t.lang=\'' . $lang . '\') as from_port';
        $field .= ',to_port_bn,(select name from erui_dict.port as t where t.bn=to_port_bn and t.lang=\',buyer_id' . $lang . '\') as to_port,quality,distributed';
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
        $where['order.deleted_flag'] = 'N';
        $this->_getValue($where, $condition, 'order_no'); //平台订单号
        $this->_getValue($where, $condition, 'po_no'); //po编号
        $this->_getValue($where, $condition, 'execute_no'); //执行编号
        if (isset($condition['show_status']) && $condition['show_status']) {
            if (in_array($condition['show_status'], ['UNCONFIRM', 'GOING', 'COMPLETED'])) {
                $where['show_status'] = $condition['show_status'];
            }
        }
        if (isset($condition['pay_status']) && $condition['pay_status']) {
            if (in_array($condition['pay_status'], ['UNPAY', 'PARTPAY', 'PAY'])) {
                $where['pay_status'] = $condition['pay_status'];
            }
        }
        if (isset($condition['agent_id']) && $condition['agent_id']) {
            $where['agent_id'] = $condition['agent_id'];
        }


        $this->_getValue($where, $condition, 'contract_date', 'between'); //支付状态

        if (isset($condition['buyer_no']) && $condition['buyer_no']) {
            $where['buyer.buyer_no'] = $condition['buyer_no'];
        }
        if (isset($condition['name']) && $condition['name']) {
            $where['buyer.name'] = $condition['name'];
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

    public function getList($condition) {

        $where = $this->_getCondition($condition);
        list($start_no, $pagesize) = $this->_getPage($condition);
        return $this
                        ->field('order.id,is_reply,order_no,po_no,execute_no,contract_date, buyer_id,order.status,show_status,pay_status,buyer.name as buyer_id_name,buyer.buyer_no')
                        ->join('`erui_buyer`.`buyer`  on buyer.id=order.buyer_id', 'left')
                        ->where($where)->limit($start_no, $pagesize)->order('order.created_at desc')->select();
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

        return $this->join('`erui_buyer`.`buyer`  on buyer.id=order.buyer_id', 'left')->where($where)->count();
    }

    /**
     * @param $buyer_id
     * 获取订单数，和金额
     * wangs
     */
    public function statisOrder($buyer_id){
        $sql = "select count(id) as count,SUM(amount) as account from `erui_order`.`order` where buyer_id=$buyer_id GROUP BY amount
";
        $info = $this->query($sql);
        if(empty($info)){
            $data = array(
                'countaccount'=>array('count'=>0,'account'=>0),
                'range'=>array('max'=>0,'min'=>0)
            );
            return $data;
        }
        $sqlm = "select max(amount) as max,min(amount) as min from `erui_order`.`order` where buyer_id=$buyer_id";
        $arr = $this->query($sqlm);
        $data = array(
            'countaccount'=>$info[0],
            'range'=>$arr[0]
        );
        return $data;
    }
    /**
     * 客户首页订单，金额展示数据
     * wangs
     */
    public function getOrderStatis($ids){
        $arr = [];
        foreach($ids as $k => $v){
            $arr[$k]=$this->statisOrder($v);
        }
        return $arr;
    }

}
