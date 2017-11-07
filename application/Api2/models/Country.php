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
class CountryModel extends PublicModel {

    const STATUS_VALID = 'VALID';    //有效的

    //put your code here

    protected $dbName = 'erui_dict';
    protected $tableName = 'country';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /*
     * 条件id,lang,bn,name,time_zone,region,pinyin
     */

    private function _getCondition(&$condition) {
        $data = [];
        getValue($data, $condition, 'lang', 'string', 'c.lang');
        if (isset($condition['bn']) && $condition['bn']) {
            $data['c.bn'] = $condition['bn'];
        }
        getValue($data, $condition, 'name', 'like', 'c.name');
        getValue($data, $condition, 'time_zone', 'string', 'c.time_zone');
        getValue($data, $condition, 'region_bn', 'like', 'c.region_bn');
        if (isset($condition['status']) && $condition['status'] == 'ALL') {

        } elseif (isset($condition['status']) && in_array($condition['status'], ['VALID', 'INVALID'])) {
            $data['c.status'] = $condition['status'];
        } else {
            $data['c.status'] = 'VALID';
        }
        getValue($data, $condition, 'market_area_bn', 'like', 'mac.market_area_bn');

        return $data;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'id desc') {

        if (!empty($limit)) {
            return $this->field('id,lang,bn,name,time_zone,region_bn,pinyin,int_tel_code')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field('id,lang,bn,name,time_zone,region_bn,int_tel_code')
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 获取列表
     * @param string $name 国家名称;
     * @param string $lang 语言
     * @return array
     * @author jhw
     */
    public function getCountrybynameandlang($name, $lang = 'en') {

        try {
            $data = ['country.name' => $name,
                'country.lang' => 'zh',
                'c.status' => 'VALID',
                'country.status' => 'VALID',
                'c.lang' => $lang
            ];

            $row = $this->alias('country')
                    ->join($this->getTableName() . ' as  c on country.bn=c.bn')
                    ->field('c.name')
                    ->where($data)
                    ->find();
            if ($row) {
                return $row['name'];
            } else {
                return 'China';
            }
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return 'China';
        }
    }

    /**
     * 获取国家对应营销区域
     * @author klp
     */
    public function getMarketArea($country, $lang) {
        $where = array(
            'name' => $country,
            'lang' => $lang
        );
        $country_bn = $this->field('bn')->where($where)->find();

        $MarketAreaCountry = new MarketAreaCountryModel(); //对应表的营销区域简写bn
        $market_area_bn = $MarketAreaCountry->field('market_area_bn')->where(array('country_bn' => $country_bn['bn']))->find();
        $MarketArea = new MarketAreaModel();
        $market_area = $MarketArea->field('name,bn')->where(array('bn' => $market_area_bn['market_area_bn'], 'lang' => $lang))->find();
        if ($market_area) {
            $market_area['country_bn'] = $country_bn;
            return $market_area;
        } else {
            return false;
        }
    }

    /**
     * 国家地区列表,按首字母分组排序
     * @param  $lang
     * @return array|[]
     * @author klp
     */
    public function getInfoSort($lang) {
        $condition = array(
            'lang' => $lang,
            'status' => 'VALID'
        );
        $result = $this->field('name,bn,region_bn,time_zone')->where($condition)->select();
        if ($result) {
            $data = array();
            foreach ($result as $val) {
                $sname = $val['name'];
                $firstChar = $this->getFirstCharter($sname); //取出第一个汉字或者单词的首字母
                $data[$firstChar][] = $val; //以这个首字母作为key
            }
            ksort($data); //对数据进行ksort排序，以key的值以升序对关联数组进行排序
            return $data;
        } else {
            return array();
        }
    }

    /**
     * 取汉字的第一个字的首字母
     * @param type $str
     * @return string|null
     * @author klp
     */
    public function getFirstCharter($str) {
        if (empty($str)) {
            return '';
        }

        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) {
            return strtoupper($str{0});
        }
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $ascs = [-20319, -20283, -19775, -19218, -18710, -18526, -18239, -17922, - 17417, -16474, -16212, -15640, -15165, -14922, -14914, -14630, -14149, -14090, - 13318, -12838, -12556, -11847, -11055, -10247];
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        for ($i = 0; $i < 26; $i++) {
            if ($asc >= $ascs[$i] && $asc < $ascs[$i + 1]) {

                if ($i > 18) {
                    return chr($i + 68);
                } elseif ($i > 7 && $i <= 18) {
                    return chr($i + 66);
                } else {
                    return chr($i + 65);
                }
            }
        }
        return null;
    }

    /**
     * 根据简称与语言获取国家名称
     * @param string $bn 简称
     * @param string $lang 语言
     * @param string
     */
    public function getCountryByBn($bn = '', $lang = '') {
        if (empty($bn) || empty($lang))
            return '';

        if (redisHashExist('Country', $bn . '_' . $lang)) {
            return redisHashGet('Country', $bn . '_' . $lang);
        }
        try {
            $condition = array(
                'bn' => $bn,
                'lang' => $lang,
                    // 'status'=>self::STATUS_VALID
            );
            $field = 'name';
            $result = $this->field($field)->where($condition)->find();
            if ($result) {
                redisHashSet('Country', $bn . '_' . $lang, $result['name']);
            }
            return $result['name'];
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * 根据简称与语言获取国家名称
     * @param array $bn 简称
     * @param string $lang 语言
     * @param string
     */
    public function getCountryByBns($bns = [], $lang = '') {
        if (empty($bns) || empty($lang))
            return '';

        if (redisHashExist('Country', implode('_', $bns) . '_' . $lang)) {
            return json_decode(redisHashGet('Country', implode('_', $bns) . '_' . $lang), true);
        }
        try {
            $condition = array(
                'bn' => ['in', $bns],
                'lang' => $lang,
                    // 'status'=>self::STATUS_VALID
            );
            $field = 'bn,name';
            $data = $this->field($field)->where($condition)->select();
            $result = [];
            if ($data) {
                foreach ($data as $item) {
                    $result[$item['bn']] = $item['name'];
                }
            }
            if ($result) {
                redisHashSet('Country', implode('_', $bns) . '_' . $lang, json_encode($result));
            }
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

}
