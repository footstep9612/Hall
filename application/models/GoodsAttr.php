<?php

/**
 * Class GoodsAttrModel
 */
class GoodsAttrModel extends PublicModel
{
    //protected $dbName = 'erui_goods'; //数据库名称
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称
    protected $tableName = 'goods_attr'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取商品属性值
     * @param null $where string 条件
     * @return mixed
     */
    public function WhereList($where)
    {
        $result = $this->field('attr_group, attr_no, attr_name, attr_value, attr_value_type, goods_flag, logistics_flag, hs_flag, spec_flag, required_flag, search_flag, sort_order, status')
                       ->where($where)
                       ->select();
        return $result;
    }



    /**
     * order处理
     * @access protected
     * @param mixed $order
     * @return string
     */
    protected function dealOrder($order) {
        if(is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val){
                if(is_numeric($key)) {
                    $array[] =  $val;
                }else{
                    $array[] =  $key.' '.$val;
                }
            }
            $order   =  implode(',',$array);
        }
        return $order;
    }
}