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

    /*
     * 条件id,lang,bn,name,time_zone,region,pinyin
     */

    private function _getCondition(&$condition) {
        $data = ['deleted_flag' => 'N'];
        getValue($data, $condition, 'lang', 'string', 'lang');
        if (isset($condition['bn']) && $condition['bn']) {
            $data['bn'] = $condition['bn'];
        }
        return $data;
    }

    /**
     * 获取列表
     * @param data $condition;
     * @return array
     * @author jhw
     */
    public function getList($condition, $order = 'id desc') {
        try {
            $where = $this->_getCondition($condition);


            unset($condition);
            $redis_key = md5(json_encode($where) . $order);
            if (redisHashExist('Country', $redis_key)) {
                return json_decode(redisHashGet('Country', $redis_key), true);
            }
            $result = $this
                    ->field('bn,name')
                    ->where($where)
                    ->order($order)
                    ->select();

            redisHashSet('Country', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

    /**
     * 获取列表
     * @param array ;
     * @return array
     * @author jhw
     */
    public function getCount($condition) {
        try {
            $where = $this->_getCondition($condition);
            unset($condition);

            $count = $this
                    ->where($where)
                    ->count();
            return $count;
        } catch (Exception $ex) {
            print_r($ex);
            return [];
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

    public function setCountry(&$arr) {
        if ($arr) {

            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }
            $countrynames = $this->getNamesBybns($country_bns, $this->lang);
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
