<?php

/**
 * 产品附件.
 * User: linkai
 * Date: 2017/6/20
 * Time: 14:19
 */
class ProductAttachModel extends PublicModel {

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_INVALID = 'INVALID';  //无效
    const STATUS_DELETED = 'DELETED';  //无效
    const DELETED_Y = 'Y'; //删除
    const DELETED_N = 'N'; //未删除

    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'product_attach';

        parent::__construct();
    }

    public function getAttachBySpu($spu){
        if(is_array($spu)){
            $condition_attach = [
                'spu' => ['in' ,$spu],
                'attach_type' => 'BIG_IMAGE',
                'status'=> self::STATUS_VALID,
                'deleted_flag' => self::DELETED_N,
            ];
        }else {
            $condition_attach = [
                'spu' => $spu ,
                'attach_type' => 'BIG_IMAGE' ,
                'status' => self::STATUS_VALID ,
                'deleted_flag' => self::DELETED_N ,
            ];
        }

        try{
            $attachs = $this->field('spu,attach_type,attach_name,attach_url,default_flag')->where($condition_attach)->order('sort_order')->select();
            return $attachs ? $attachs : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ProductAttach】getAttachBySpu:' . $e , Log::ERR);
            return false;
        }
    }

}
