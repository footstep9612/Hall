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
            $result = $this->field($field)->where(['deleted_flag' => 'N'])->select();
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
            $result = $this->field($field)->where(['deleted_flag' => 'N'])->order('bn')->select();
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
    public function getlist($status) {

        try {
            $where = [];
            if ($status == 'VALID') {
                $where['status'] = $status;
                $where['deleted_flag'] = 'N';
            } elseif ($status == 'DELETED') {
                $where['status'] = $status;
                $where['deleted_flag'] = 'Y';
            }
            $field = 'bn,symbol,name';
            $result = $this->field($field)
                            ->where($where)
                            ->order('bn')->select();
            if ($result) {

//                foreach ($result as $key => $item) {
//                    $result[$key]['name'] = $item['bn'] . '_' . $item['name'];
//                }
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
                $where = ['bn' => ['in', $bns], 'deleted_flag' => 'N'];
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

    /**
     * 获取根据bn币种名称

     * @return array|mixed
     * @author  zhongyg
     */
    public function getSymbolByBns($bn) {

        try {
            if ($bn) {
                $where = ['bn' => $bn, 'deleted_flag' => 'N'];
                $price_symbol = $this->where($where)->getField('symbol');
                return $price_symbol ? $price_symbol : '';
            } else {
                return '';
            }
        } catch (Exception $e) {
            return '';
        }
    }

}
