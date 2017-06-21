<?php
/**
 * Description of ProAttrModel
 *
 * @author  klp
 */
class ProAttrModel extends PublicModel
{
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
    public function wherelist($where)
    {
        return $this->field('spu, attr_group, attr_no, attr_name, attr_value_type, attr_value, goods_flag, logistics_flag, hs_flag, required_flag, search_flag, sort_order, status, created_by, created_at')
                     ->where($where)
                     ->select();
    }

    /**
     * 根据商品条件查询产品属性
     * @param null $where 条件 spu lang语言 (必)
     * @return string json
     */
    public function ProInfo($where)
    {
        $result = $this->field('id, spu, attr_group, created_by, created_at')
                       ->where($where)
                       ->select();
        return $result;
    }


}