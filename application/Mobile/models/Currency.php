<?php

/**
 * Created by PhpStorm.
 * User: zyg
 * Date: 2017/6/28
 * Time: 11:32
 */
class CurrencyModel extends PublicModel {

    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'currency';

    /**
     * 获取币种
     * @param string $lang
     * @param string $country
     * @return array|mixed
     */
    public function getCurrency() {
        if (redisHashExist('Currency', 'currency')) {
            return json_decode(redisHashGet('Currency', 'currency'), true);
        }
        try {


            $field = 'bn,symbol,name,en_name';
            $result = $this->field($field)
                    ->order('bn')
                    ->where(['status' => 'VALID', 'deleted_flag' => 'N'])
                    ->group('bn')
                    ->select();
            if ($result) {
                redisHashSet('Currency', 'currency', json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
    }

}
