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
                'country.deleted_flag' => 'N',
                'c.deleted_flag' => 'N',
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
            'lang' => $lang,
            'deleted_flag' => 'N',
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
            'status' => 'VALID',
            'deleted_flag' => 'N'
        );
        $result = $this->where($condition)->select();
        if ($result) {
            $data = array();
            foreach ($result as $val) {
                $sname = $val['name'];
                $firstChar = $this->_getFirstCharter($sname, $lang); //取出第一个汉字或者单词的首字母
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
    public function _getFirstCharter($str, $lang) {
        if (empty($str)) {
            return '';
        }
        if ($lang == 'ru') {
            $fchar = mb_substr($str, 0, 1, 'gb2312');
            $asc = $this->_ruasc($fchar);
            if ($asc == -12144 || $asc == 4294955152) {
                return 'А';
            }
            if ($asc == -12143 || $asc == 4294955153) {
                return 'Б';
            }
            if ($asc == -12142 || $asc == 4294955154) {
                return 'В';
            }
            if ($asc == -12141 || $asc == 4294955155) {
                return 'Г';
            }
            if ($asc == -12140 || $asc == 4294955156) {
                return 'Д';
            }
            if ($asc == -12139 || $asc == 4294955157) {
                return 'Е';
            }
            if ($asc == -12137 || $asc == 4294955159) {
                return 'З';
            }
            if ($asc == -12136 || $asc == 4294955160) {
                return 'И';
            }
            if ($asc == -12135 || $asc == 4294955161) {
                return 'Й';
            }
            if ($asc == -12134 || $asc == 4294955162) {
                return 'К';
            }
            if ($asc == -12133 || $asc == 4294955163) {
                return 'Л';
            }
            if ($asc == -12132 || $asc == 4294955164) {
                return 'М';
            }
            if ($asc == -12131 || $asc == 4294955165) {
                return 'Н';
            }
            if ($asc == -12130 || $asc == 4294955166) {
                return 'О';
            }
            if ($asc == -12129 || $asc == 4294955167) {
                return 'П';
            }
            if ($asc == -12128 || $asc == 4294955168) {
                return 'Р';
            }
            if ($asc == -12127 || $asc == 4294955169) {
                return 'С';
            }
            if ($asc == -12126 || $asc == 4294955170) {
                return 'Т';
            }
            if ($asc == -12125 || $asc == 4294955171) {
                return 'У';
            }
            if ($asc == -12124 || $asc == 4294955172) {
                return 'Ф';
            }
            if ($asc == -12123 || $asc == 4294955173) {
                return 'Х';
            }
            if ($asc == -12122 || $asc == 4294955174) {
                return 'Ц';
            }
            if ($asc == -12121 || $asc == 4294955175) {
                return 'Ч';
            }
            if ($asc == -12120 || $asc == 4294955176) {
                return 'Ш';
            }
            if ($asc == -12115 || $asc == 4294955181) {
                return 'Э';
            }
            if ($asc == -12114 || $asc == 4294955182) {
                return 'Ю';
            }
            if ($asc == -12113 || $asc == 4294955183) {
                return 'Я';
            }
            return null;
        } else {
            $fchar = ord($str{0});
            if ($fchar >= ord('A') && $fchar <= ord('z'))
                return strtoupper($str{0});
            $s1 = iconv('UTF-8', 'gb2312', $str);
            $s2 = iconv('gb2312', 'UTF-8', $s1);
            $s = $s2 == $str ? $s1 : $str;
            $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
            if ($asc >= -20319 && $asc <= -20284)
                return 'A';
            if ($asc >= -20283 && $asc <= -19776)
                return 'B';
            if ($asc >= -19775 && $asc <= -19219)
                return 'C';
            if ($asc >= -19218 && $asc <= -18711)
                return 'D';
            if ($asc >= -18710 && $asc <= -18527)
                return 'E';
            if ($asc >= -18526 && $asc <= -18240)
                return 'F';
            if ($asc >= -18239 && $asc <= -17923)
                return 'G';
            if ($asc >= -17922 && $asc <= -17418)
                return 'H';
            if ($asc >= -17417 && $asc <= -16475)
                return 'J';
            if ($asc >= -16474 && $asc <= -16213)
                return 'K';
            if ($asc >= -16212 && $asc <= -15641)
                return 'L';
            if ($asc >= -15640 && $asc <= -15166)
                return 'M';
            if ($asc >= -15165 && $asc <= -14923)
                return 'N';
            if ($asc >= -14922 && $asc <= -14915)
                return 'O';
            if ($asc >= -14914 && $asc <= -14631)
                return 'P';
            if ($asc >= -14630 && $asc <= -14150)
                return 'Q';
            if ($asc >= -14149 && $asc <= -14091)
                return 'R';
            if ($asc >= -14090 && $asc <= -13319)
                return 'S';
            if ($asc >= -13318 && $asc <= -12839)
                return 'T';
            if ($asc >= -12838 && $asc <= -12557)
                return 'W';
            if ($asc >= -12556 && $asc <= -11848)
                return 'X';
            if ($asc >= -11847 && $asc <= -11056)
                return 'Y';
            if ($asc >= -11055 && $asc <= -10247)
                return 'Z';
            return null;
        }
    }

    private function _ruasc($s) {
        if (ord($s) < 128)
            return ord($s);
        return current(unpack('N', "\xff\xff$s"));
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
