<?php
/**
 * 物料分类与展示分类关系
 * @author link
 */
class ShowMaterialCatModel extends PublicModel {
    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'show_material_cat';

        parent::__construct();
    }

    /**
     * 根据条件查询
     * @author link 2017-08-04
     * @param array $condition
     * @param string $field
     * @return array|bool
     */
    public function findByCondition($condition=[], $field=''){
        if(empty($condition) || !is_array($condition)) {
            return false;
        }

        if(is_array($field)) {
            $field = implode(',' , $field);
        }elseif(empty($field)){
            $field = 'show_cat_no,material_cat_no,status,created_by,created_at,updated_by,updated_at,checked_by,checked_at';
        }

        /**
         * 取缓存
         */
        if(redisHashExist('show_material_cat',md5(serialize($condition).serialize($field)))){
            return json_decode(redisHashGet('show_material_cat',md5(serialize($condition).serialize($field))),true);
        }

        try{
            $result = $this->field($field)->where($condition)->select();
            if($result){
                redisHashSet('show_material_cat',md5(serialize($condition).serialize($field)),json_encode($result));
                return $result;
            }
        }catch (Exception $e) {
            return false;
        }
        return array();
    }

}
