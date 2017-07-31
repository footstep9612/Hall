<?php
/**
 * 属性
 * User: linkai
 * Date: 2017/6/22
 * Time: 16:01
 */
class AttrModel extends PublicModel{

    //状态
    const STATUS_VALID = 'VALID';    //有效的
    const STATUS_INVALID = 'INVALID';    //无效
    const STATUS_DELETED = 'DELETED';    //删除

    public function __construct()
    {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj=Yaf_Registry::get("config");
        $config_db=$config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'attr';

        parent::__construct();
    }
}