<?php
/**
* Description of GoodsAttrTplModel
*
 * @author  klp
*/
class GoodsAttrTplModel extends PublicModel
{
    //protected $dbName = 'erui_goods'; //测试数据库名称
    protected $dbName = 'erui_goods'; //数据库名称
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

    /**
     * 根据条件查询商品属性 sku数据查询
     * @param null $where 条件 sku lang语言(必) skuid  attr_group规格
     * @return string json
     */
    public function AttrInfo($where)
    {
        $result = $this->field('id, spu, sku, attr_group, attr_name, sort_order, created_by, created_at')
            ->where($where)
            ->select();
        return $result;
    }

    /**
     * 根据条件查询商品总数
     * @param null $where 条件  sku
     * @return string json
     */
    public function GetCount($where)
    {
        $result = $this->where($where)
                 /*->field('id, spu, sku, attr_group, sort_order, created_by, created_at')*/
                ->count('id');
        return $result;
    }
}