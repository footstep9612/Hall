<?php

class ProAttrModel extends PublicModel
{
    //protected $dbName = 'erui_goods'; //测试数据库名称
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称
    protected $tableName = 'product_attr'; //数据表表名

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据条件获取产品属性值
     * @param null $where string 条件
     * @return mixed
     */
    public function wherelistp($where)
    {
        return $this->field('spu, attr_group, attr_no, attr_name, attr_value_type, attr_value, goods_flag, logistics_flag, hs_flag, required_flag, search_flag, sort_order, status, created_by, created_at')
                     ->where($where)
                     ->select();
    }
}