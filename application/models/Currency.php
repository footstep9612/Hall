<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/6/28
 * Time: 11:32
 */
class CurrencyModel extends PublicModel
{
    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'currency';
    /**
     * 根据简称获取城市名称
     * @param string $bn 简称
     * @param string $lang 语言
     * @return string
     */
    public function getPayMethod(){

        $key_redis = md5(json_encode('payMethod'));
        if(redisExist($key_redis)){
            $result = redisGet($key_redis);
            return $result ? json_decode($result) : array();
        }
        try{
            $field = 'bn,name,dollar_symbol';
            $result = $this->field($field)->select();
            if($result){
                redisSet($key_redis,$result);
            }
            return $result;
        }catch (Exception $e){
            return array();
        }
    }
}