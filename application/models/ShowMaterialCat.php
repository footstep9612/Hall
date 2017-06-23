<?php
/**
 * 展示分类与物料分类关系.
 * User: linkai
 * Date: 2017/6/23
 * Time: 10:40
 */
class ShowMaterialCatModel extends PublicModel{
    //状态
    const STATUS_DRAFT = 'DRAFT';    //草稿
    const STATUS_APPROVING = 'APPROVING';    //审核
    const STATUS_VALID = 'VALID';    //生效
    const STATUS_DELETED = 'DELETED';    //删除

    public function __construct()
    {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj=Yaf_Registry::get("config");
        $config_db=$config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'show_material_cat';

        parent::__construct();
    }

    /**
     * 未写完
     * @param array $condition 条件
     * return array
     */
   /* public function getShowcatByMcat($condition=''){
        if(empty($condition) || (!isset($condition['show_cat_no']) && !isset($condition['material_cat_no'])))
            return array();

        $where = array(
            'status' => self::STATUS_VALID
        );
        if(isset($condition['show_cat_no'])){
            $where['show_cat_no'] = $condition['show_cat_no'];
            $this->field('material_cat_no')->where($where)->;
        }elseif(isset($condition['material_cat_no'])){
            $where['material_cat_no'] = $condition['material_cat_no'];
        }
        $field = '';
    }*/

}