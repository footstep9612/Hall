<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/28
 * Time: 11:32
 */
class CityModel extends Model{
    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 't_city';
    /**
     * 根据简称获取城市名称
     * @param string $bn 简称
     * @param string $lang 语言
     * @return string
     */
    public function getCityByBn($bn='',$lang=''){
        if(empty($bn) || empty($lang))
            return '';

        if(redisHashExist('City',$bn.'_'.$lang)){
            return redisHashGet('City',$bn.'_'.$lang);
        }
        try{
            $condition = array(
                'bn' =>$bn,
                'lang'=>$lang,
                //'status'=>self::STATUS_VALID
            );
            $field = 'name';
            $result = $this->field($field)->where($condition)->find();
            if($result){
                redisHashSet('City',$bn.'_'.$lang,$result['name']);
            }
            return $result['name'];
        }catch (Exception $e){
            return '';
        }
    }
}