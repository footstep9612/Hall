<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/28
 * Time: 14:34
 */
class DestDeliveryLogiModel extends Model{
    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 't_dest_delivery_logi';

    const STATUS_VALID = 'VALID';    //有效的

    /**
     * 根据落地国家跟语言获取信息
     * @param string $country
     * @param string $lang
     * @return array|mixed|string
     */
    public function getList($country='',$lang=''){
        if(empty($country) || empty($lang))
            return array();

        if(redisHashExist('DDL',md5($country.'_'.$lang))){
            return json_decode(redisHashGet('DDL',md5($country.'_'.$lang)),true);
        }
        try{
            $condition = array(
                'country' =>$country,
                'lang'=>$lang,
                'status'=>self::STATUS_VALID
            );
            $field = 'lang,logi_no,trans_mode,country,from_loc,to_loc,period_min,period_max,logi_notes,description';
            $result = $this->field($field)->where($condition)->select();
            if($result){
                redisHashSet('DDL',md5($country.'_'.$lang),json_encode($result));
            }
            return $result;
        }catch (Exception $e){
            return '';
        }

    }
}