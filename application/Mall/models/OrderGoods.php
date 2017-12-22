<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/21
 * Time: 18:04
 */
class OrderGoodsModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_goods';
    protected $dbName = 'erui_order'; //数据库名称

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
    }

    /* 获取商品详情
     * @param int $order_id // 订单ID
     */

    public function getList($order_no) {
        $where = $this->_getCondition($order_no);
        $field = 'id,order_no,sku,lang,name,model,spec_attrs,price,buy_number';
        $field .= ',min_pack_naked_qty,nude_cargo_unit,min_pack_unit,thumb,buyer_id';
        return $this->field($field)
                     ->where($where)
                     ->order('id desc')
                     ->select();
    }

    private function _getCondition($order_no) {
        $where = [];
        $this->_getValue($where, $order_no, 'order_no');
        $where['deleted_flag'] = "N";
        return $where;
    }

}