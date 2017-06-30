<?php
/**
 * 货币.
 * User: linkai
 * Date: 2017/6/30
 * Time: 21:10
 */
class CurrencyModel extends PublicModel{
    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'currency'; //数据表表名

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
    public function getCurrency(){
        if(redisHashExist('Currency','currency')){
            return json_decode(redisHashGet('Currency','currency'),true);
        }
        try{
            $field = 'bn,dollar_symbol,name';
            $result = $this->field($field)->order('bn')->select();
            if($result){
                redisHashSet('Currency','currency',json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
    }
}