<?php

class GoodsAttrTplModel extends PublicModel
{
    //protected $dbName = 'erui_goods'; //测试数据库名称
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称
    protected $tableName = 'goods_tpl_attr'; //数据表表名

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 根据条件获取商品模板属性值
     * @param null $where string 条件
     * @return mixed
     */
    public function WhereAttrlist($where)
    {
        $result = $this->field('input_type, value_type, value_unit, options, input_hint')
                       ->where($where)
                       ->select();
        return $result;
    }
}