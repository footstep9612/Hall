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
     * @param data $condition;
     * @return array
     * @author jhw
     */
    public function getlistBycodition($condition, $order = 'c.id desc', $type = true) {
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
                    ->join('erui_operation.market_area_country mac on c.bn=mac.country_bn', 'left')
                    ->join('erui_operation.market_area ma on ma.bn=mac.market_area_bn and ma.lang=c.lang', 'left')
                    ->join('erui_dict.region r on r.bn=c.region_bn and r.lang=c.lang', 'left')
                    ->field('c.id,c.lang,c.bn,c.name,c.time_zone,c.region_bn,r.name as region_name,'
                            . 'ma.name as market_area_name ,mac.market_area_bn,c.int_tel_code')
                    ->where($where);
            if ($type) {
                $this->limit($from . ',' . $pagesize);
            }
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
            $data = $this->alias('c')
                    ->join('erui_operation.market_area_country mac on c.bn=mac.country_bn', 'left')
                    ->join('erui_operation.market_area ma on ma.bn=mac.market_area_bn and ma.lang=c.lang', 'left')
                    ->_getCondition($condition);
            return $this->where($data)->count();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'id desc') {

        if (!empty($limit)) {
            return $this->field('id,lang,bn,name,time_zone,region,pinyin')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field('id,lang,bn,name,time_zone,region,pinyin,int_tel_code')
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            $row = $this->where($where)
                    ->field('id,lang,bn,name,time_zone,region,pinyin,int_tel_code')
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            return $this->where($where)
                            ->save(['status' => 'DELETED']);
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  array $update id
     * @return bool
     * @author jhw
     */
    public function update_data($update) {

        $data = $this->create($update);
        $where['bn'] = $data['bn'];
        $arr['status'] = $data['status'] == 'VALID' ? 'VALID' : 'INVALID';
        $flag = $this->where($where)->save($arr);
        if ($flag && $update['market_area_bn'] && $where['bn']) {
            $update = ['market_area_bn' => $update['market_area_bn'],
                'country_bn' => $arr['bn']];
            if ($this->getmarket_area_countryexit($update)) {
                $this->table('erui_operation.market_area_country')
                        ->add($update, [], true);
            }

            return $flag;
        } else {
            return false;
        }
    }

    /**
     * 批量更新状态
     * @param  array $data
     * @return bool
     * @author zyg
     */
    public function updatestatus($data) {
        if (!is_array($data['countrys'])) {
            return false;
        }
        $this->startTrans();
        foreach ($data['countrys'] as $item) {
            $flag = $this->where(['bn' => $item['bn']])->save(['status' => $item['status']]);
            if (!$flag) {
                $this->rollback();
                return FALSE;
            }
        }
        $this->commit();
        return true;
    }

    public function getmarket_area_countryexit($where) {

        return $this->table('erui_operation.market_area_country')->where($where)->find();
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['lang'])) {
            $arr['lang'] = $create['lang'];
        }
        if (isset($create['bn'])) {
            $arr['bn'] = $create['bn'];
        }
        if (isset($create['name'])) {
            $arr['name'] = $create['name'];
            $arr['pinyin'] = Pinyin($create['name']);
        }
        if (isset($create['time_zone'])) {
            $arr['time_zone'] = $create['time_zone'];
        }
        if (isset($create['region'])) {
            $arr['region'] = $create['region'];
        }
        $data = $this->create($arr);
        if ($data && $create['market_area_bn']) {
            $update = ['market_area_bn' => $create['market_area_bn'],
                'country_bn' => $arr['bn']];
            $this->table('erui_operation.market_area_country')
                    ->create($update);
        }
        $flag = $this->add($data);
        if ($flag && $create['market_area_bn']) {
            $update = ['market_area_bn' => $create['market_area_bn'],
                'country_bn' => $arr['bn']];
            if ($this->getmarket_area_countryexit($update)) {
                $this->table('erui_operation.market_area_country')
                        ->create($update);
            }
        }
        return $flag;
    }

    /**
     * 判断是否存在
     * @param  mix $where 搜索条件
     * @return mix
     * @date 2017-08-01
     * @author zyg
     */
    public function exist($where) {

        return $this->_exist($where);
    }

    /**
     * 国家地区列表,按首字母分组排序
     * @param  $lang
     * @return array|[]
     * @author klp
     */
    public function getInfoSort($lang) {
        $condition = array(
            'lang' => $lang
        );

        if (redisExist(md5(json_encode($condition)))) {
            $result = json_decode(redisGet(md5(json_encode($condition))), true);
            return $result ? $result : array();
        }
        $result = $this->field('name,bn,region,time_zone')->where($condition)->select();
        if ($result) {
            $data = array();
            foreach ($result as $val) {
                $sname = $val['name'];
                $firstChar = $this->getFirstCharter($sname); //取出第一个汉字或者单词的首字母
                $data[$firstChar][] = $val; //以这个首字母作为key
            }
            ksort($data); //对数据进行ksort排序，以key的值以升序对关联数组进行排序
            redisSet(md5(json_encode($condition)), $data);
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

    /**
     * 获取IP地址
     * @author klp
     */
    public function getRealIp() {
        if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif ($_SERVER["HTTP_CLIENT_IP"]) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif ($_SERVER["REMOTE_ADDR"]) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = "Unknown";
        }
        return $ip;
    }

    //新浪通过IP地址获取当前地理位置（省份,城市等）的接口   klp
    public function getIpAddress($ip) {
        if ($ip == "127.0.0.1")
            jsonReturn('', '-1003', '当前为本机地址');
        $ipContent = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip");
        $arr = json_decode($ipContent, true); //解析json
        $country = $arr['country']; //取得国家
        return $country;
    }

    //获取IP地址对应英文国家名称  klp
    public function getName($country) {
        $where = array(
            'name' => $country
        );
        $bn = $this->field('bn')->where($where)->find();
        $condition = array(
            'bn' => $bn['bn'],
            'lang' => 'en'
        );
        $nameEn = $this->field('name')->where($condition)->find();
        if ($nameEn) {
            return $nameEn;
        } else {
            return false;
        }
    }

    /**
     * 获取国家对应营销区域
     * @author klp
     */
    public function getMarketArea($country, $lang) {
        $where = array(
            'name' => $country
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
     * @param string $bn 简称
     * @param string $lang 语言
     * @param string
     */
    public function getBnByName($name = '') {
        if (empty($name)) {
            return '';
        }
        if (redisHashExist('Country', $name)) {
            return json_decode(redisHashGet('Country', $name), true);
        }
        try {

            $condition = array(
                'name' => ['like', '%' . $name . '%'],
                'status' => self::STATUS_VALID
            );

            $field = 'bn';
            $result = $this->field($field)->where($condition)->select();

            $bns = [];
            if ($result) {
                foreach ($result as $bn) {
                    $bns[] = $bn['bn'];
                }
            } else {
                return [];
            }

            if ($result) {
                redisHashSet('Country', $name, json_encode($bns));
            }
            return $bns;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 根据简称与语言获取国家名称
     * @param string $bn 简称
     * @param string $lang 语言
     * @param string
     */
    public function import($lang = 'en') {
        if (empty($lang))
            return '';
        try {
            $condition = array(
                'lang' => $lang,
            );
            $result = $this->where($condition)->select();
            if (!$result) {
                return false;
            }
            $updateParams = array();
            $updateParams['index'] = 'erui_dict';
            $updateParams['type'] = 'country_' . $lang;
            $city_model = new Model('erui_dict.city', 't_');
            $port_model = new Model('erui_dict.port', 't_');
            $market_area_country_model = new Model('erui_dict.market_area_country', 't_');
            $es = new ESClient();
            foreach ($result as $item) {
                $updateParams['body'][] = ['create' => ['_id' => $item['bn']]];
                $item['citys'] = json_encode($city_model
                                ->field('id,bn,name')
                                ->where(['country_bn' => $item['bn'], 'lang' => $lang])
                                ->select(), 256);
                $item['ports'] = json_encode($port_model
                                ->field('id,bn,name,port_type,trans_mode')
                                ->where(['country_bn' => $item['bn'], 'lang' => $lang])
                                ->select(), 256);
                $item['letter'] = strtoupper(mb_substr($item['pinyin'], 0, 1));
                $market_area_country = $market_area_country_model->field('market_area_bn')->where(['country_bn' => $item['bn']])->find();
                $item['market_area_bn'] = $market_area_country['market_area_bn'];
                $es->add_document('erui_dict', 'country_' . $lang, $item, $item['bn']);
            }
        } catch (Exception $ex) {
            var_dump($ex);

            return '';
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

    public function getNamesBybns($bns) {

        try {
            $where = [];

            if (is_string($bns)) {
                $where['bn'] = $bns;
            } elseif (is_array($bns)) {
                $where['bn'] = ['in', $bns];
            } else {
                return false;
            }
            $where['lang'] = 'zh';
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

    /**
     * 根据bn获取国家新，并通过语言分组
     * @author link 2017-10-31
     * @param string $bn
     * @return array|bool
     */
    public function getInforByBn($bn = '') {
        if (empty($bn)) {
            return false;
        }

        try {
            $where = ['bn' => $bn, 'deleted_flag' => 'N', 'status' => 'VALID'];
            $result = $this->field('lang,bn,name')->where($where)->select();
            if ($result) {
                $country = [];
                foreach ($result as $item) {
                    $country[$item['lang']] = $item;
                }
                return $country;
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

}
