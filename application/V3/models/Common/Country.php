<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Country
 *
 * @author jhw
 */
class Common_CountryModel extends PublicModel {

    const STATUS_VALID = 'VALID';    //有效的

    //put your code here

    protected $dbName = 'erui_dict';
    protected $tableName = 'country';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition(&$condition) {
        $data = ['c.deleted_flag' => 'N'];
        getValue($data, $condition, 'lang', 'string', 'c.lang');
        if (isset($condition['bn']) && $condition['bn']) {
            $data['c.bn'] = $condition['bn'];
        }


        getValue($data, $condition, 'name', 'like', 'c.name');
        getValue($data, $condition, 'time_zone', 'string', 'c.time_zone');
        getValue($data, $condition, 'region_bn', 'string', 'c.region_bn');
        if (isset($condition['status']) && $condition['status'] == 'ALL') {

        } elseif (isset($condition['status']) && in_array($condition['status'], ['VALID', 'INVALID'])) {
            $data['c.status'] = $condition['status'];
        } else {
            $data['c.status'] = 'VALID';
        }
        getValue($data, $condition, 'market_area_bn', 'string', 'mac.market_area_bn');

        return $data;
    }

    /**
     * 获取列表
     * @param data $condition;
     * @return array
     * @author jhw
     */
    public function getList($condition, $order = 'c.id desc', $type = true) {
        try {
            $where = $this->_getCondition($condition);
            if ($type) {
                list($from, $pagesize) = $this->_getPage($condition);
            }
            unset($condition);

            $redis_key = md5(json_encode($where) . $order . $from . $pagesize . $type);
            if (redisHashExist('Country', $redis_key)) {
                return json_decode(redisHashGet('Country', $redis_key), true);
            }
            $this->alias('c')
                    ->join((new Common_MarketAreaCountryModel())->getTableName() . ' mac on c.bn=mac.country_bn', 'left')
                    ->join((new Common_MarketAreaModel())->getTableName() . ' ma on ma.bn=mac.market_area_bn and ma.lang=c.lang and ma.deleted_flag=\'N\'', 'left')
                    ->field('c.bn,c.name')
                    ->where($where);

            $result = $this->order($order)
                    ->select();

            redisHashSet('Country', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

    /*
     * 获取数据
     */

    public function getCount($condition) {
        try {
            $where = $this->_getCondition($condition);
            return $this->alias('c')
                            ->join((new Common_MarketAreaCountryModel())->getTableName() . ' mac on c.bn=mac.country_bn', 'left')
                            ->join((new Common_MarketAreaModel())->getTableName() . ' ma on ma.bn=mac.market_area_bn and ma.lang=c.lang and ma.deleted_flag=\'N\'', 'left')
                            ->where($where)
                            ->count('DISTINCT c.bn');
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    /*
     * 根据用户ID 获取用户姓名
     * @param array $user_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getNamesBybns($bns, $lang = null) {

        try {
            $where = [];

            if (is_string($bns)) {
                $where['bn'] = $bns;
            } elseif (is_array($bns)) {
                $where['bn'] = ['in', $bns];
            } else {
                return false;
            }
            if ($lang) {
                $where['lang'] = $lang;
            } else {
                $where['lang'] = LANG_SET;
            }
            $areas = $this->where($where)->field('bn,name')->select();
            $area_names = [];
            foreach ($areas as $area) {
                $area_names[$area['bn']] = $area['name'];
            }

            return $area_names;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setCountry(&$arr, $lang) {
        if ($arr) {

            $country_bns = [];
            foreach ($arr as $key => $val) {
                if (isset($val['country_bn']) && $val['country_bn']) {
                    $country_bns[] = trim($val['country_bn']);
                }
            }
            if ($country_bns) {
                $countrynames = $this->getNamesBybns($country_bns, $lang);
                foreach ($arr as $key => $val) {
                    if (trim($val['country_bn']) && isset($countrynames[trim($val['country_bn'])])) {
                        $val['country_name'] = $countrynames[trim($val['country_bn'])];
                    } else {
                        $val['country_name'] = '';
                    }
                    $arr[$key] = $val;
                }
            }
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setCountrys(&$arr, $lang, $fileds = ['country_name' => 'country_bn']) {
        if ($arr) {

            $country_bns = [];
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if (isset($val[$filed]) && $val[$filed]) {
                        $country_bns[] = $val[$filed];
                    }
                }
            }
            if ($country_bns) {
                $countrynames = $this->getNamesBybns($country_bns, $lang);
                foreach ($arr as $key => $val) {
                    foreach ($fileds as $filed_key => $filed) {
                        if ($val[$filed] && isset($countrynames[$val[$filed]])) {
                            $val[$filed_key] = $countrynames[$val[$filed]];
                        } else {
                            $val[$filed_key] = '';
                        }
                    }
                    $arr[$key] = $val;
                }
            }
        }
    }

}
