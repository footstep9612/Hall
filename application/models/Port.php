<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/30
 * Time: 19:46
 */
class PortModel extends PublicModel
{
    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'port'; //数据表表名

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取港口
     * @param string $lang
     * @param string $country
     * @return array|mixed
     */
    public function getPort($lang='',$country=''){
        $condition = array(
            'lang'=>$lang,
        );
        if(!empty($country)){
            $condition['country_bn'] =  $country;
        }

        if(redisHashExist('Port',md5(json_encode($condition)))){
            return json_decode(redisHashGet('Port',md5(json_encode($condition))),true);
        }
        try{
            $field = 'lang,country_bn,bn,name,port_type,trans_mode,description,address,longitude,latitude';
            $result = $this->field($field)->where($condition)->order('bn')->select();
            if($result){
                redisHashSet('Port',md5(json_encode($condition)),json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
    }
}