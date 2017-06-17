<?php

/**
 * Class GoodsAttrModel
 *  @author  klp
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
     * 删除数据
     * @param $where
     * @return	bool
     */

    public function Delete($where)
    {
        $sta = $this->where($where)->delete();
        return ($sta)? true : false;
    }

    /**
     * 添加数据
     * @param $data
     * @return mixed
     */

    public function CreateInfo($data){
        $sta = $this->add($data);
        if($sta){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 修改数据
     * @param $where
     * @param $data
     * @return bool
     */

    public function UpdatedInfo($where, $data)
    {
        $sta = $this->where($where)
                     ->save($data);
        if($sta){
            return true;
        }else{
            return false;
        }
    }


}