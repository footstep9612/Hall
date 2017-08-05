<?php

/**
 * Created by PhpStorm.
 * User: zyg
 * Date: 2017/6/28
 * Time: 11:32
 */
class CurrencyModel extends PublicModel {

    protected $dbName = 'erui2_dict'; //数据库名称
    protected $tableName = 'currency';

    /**
     * 根据简称获取城市名称
     * @param string $bn 简称
     * @param string $lang 语言
     * @return string
     */
    public function getPayMethod() {

        $key_redis = md5(json_encode('payMethod'));
        if (redisExist($key_redis)) {
            $result = redisGet($key_redis);
            return $result ? json_decode($result) : array();
        }
        try {
            $field = 'bn,name,symbol';
            $result = $this->field($field)->select();
            if ($result) {
                redisSet($key_redis, $result);
            }
            return $result;
        } catch (Exception $e) {
            return array();
        }
    }

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
            $field = 'bn,symbol,name';
            $result = $this->field($field)->order('bn')->select();
            if ($result) {
                redisHashSet('Currency', 'currency', json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 获取所有币种
     * @param string $lang
     * @param string $country
     * @return array|mixed
     * @author  zhongyg
     */
    public function getlist() {

        try {
            $field = 'bn,symbol,name';
            $result = $this->field($field)->order('bn')->select();
            if ($result) {

                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 获取根据bn币种名称

     * @return array|mixed
     * @author  zhongyg
     */
    public function getNameByBns($bns) {

        try {
            if ($bns) {
                $where = ['bn' => ['in', $bns]];
                $field = 'bn,name';
                $result = $this->where($where)->field($field)->select();
                $curs = [];
                foreach ($result as $cur) {
                    $curs[$cur['bn']] = $cur['name'];
                }
                return $curs;
            } else {
                return [];
            }
        } catch (Exception $e) {
            return[];
        }
    }

}
