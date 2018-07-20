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

    public function __construct() {
        parent::__construct();
    }

    /*
     * 条件id,lang,bn,name,time_zone,region,pinyin
     */

    private function _getCondition(&$condition) {
        $data = ['c.deleted_flag' => 'N'];
        getValue($data, $condition, 'lang', 'string', 'c.lang');
        if (isset($condition['bn']) && $condition['bn']) {
            if (is_string($condition['bn'])) {
                $data['c.bn'] = $condition['bn'];
            } elseif (is_array($condition['bn'])) {
                $data['c.bn'] = ['in', $condition['bn']];
            }
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
                    ->join('erui_operation.market_area ma on ma.bn=mac.market_area_bn and ma.lang=c.lang and ma.deleted_flag=\'N\'', 'left')
                    ->join('erui_dict.region r on r.bn=c.region_bn and r.lang=c.lang and r.deleted_flag=\'N\'', 'left')
                    ->field('c.id,c.lang,c.code,c.bn,c.name,c.int_tel_code,c.time_zone,c.region_bn,r.name as region_name,'
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
                    ->join('erui_operation.market_area ma on ma.bn=mac.market_area_bn and ma.lang=c.lang  and ma.deleted_flag=\'N\' ', 'left')
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
            return $this->field('id,lang,code,bn,name,time_zone,region_bn,int_tel_code')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field('id,lang,code,bn,name,time_zone,region_bn,int_tel_code')
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
                    ->field('id,lang,code,bn,name,time_zone,region,pinyin,int_tel_code')
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
                            ->save(['status' => 'DELETED', 'deleted_flag' => 'Y']);
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
        $arr['deleted_flag'] = 'N';
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
            $flag = $this->where(['bn' => $item['bn']])->save(['status' => $item['status'], 'deleted_flag' => 'N']);
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
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function checkArea($area_bn) {
        $cond = array('bn' => $area_bn, 'deleted_flag' => 'N');
        $info = $this->table('erui_operation.market_area')->field('bn as area_bn')->where($cond)->select();
        return $info;
    }

    public function checkCountryBn($country_bn) {
        $cond = array('bn' => $country_bn, 'deleted_flag' => 'N');
        $info = $this->field('id,lang,bn as country_bn')->where($cond)->select();
        return $info;
    }

    public function checkCountryName($str) {
        $cond = "deleted_flag='N' and name in ($str)";
        $info = $this->field('lang,name as country_name')
                ->where($cond)
                ->select();
        return $info;
    }

    public function updateCountryBn($country) {
        $country = $this->field('id,bn')->where(array('deleted_flag' => 'N', 'bn' => $country, 'lang' => 'zh'))->find();
        return $country;
    }

    public function updateCountryName($countryStr) {
        $country = $this->field('lang,bn,name')
                ->where("deleted_flag='N' and name in ($countryStr)")
                ->select();
        return $country;
    }

    public function nameByBn($bn) {
        $country = $this->field('lang,bn,name')
                ->where("deleted_flag='N' and bn='$bn'")
                ->select();
        return $country;
    }

    public function showCountry($data) {
        if (empty($data['id'])) {
            $info = new stdClass();
        } else {
            $info = $this->field('id,bn as country_bn,name as country_name_zh,name_en as country_name_en,name_ru as country_name_ru,name_es as country_name_es,int_tel_code as tel_code')
                    ->where(array('lang' => 'zh', 'deleted_flag' => 'N', 'id' => $data['id']))
                    ->find();
            $area = $this->table('erui_operation.market_area_country')->alias('country')
                            ->join("erui_operation.market_area area on country.market_area_bn=area.bn and area.lang='zh'", 'left')
                            ->field('area.bn')
                            ->where(array('country.country_bn' => $info['country_bn']))->find();
            $info['area_bn'] = $area['bn'];
        }

        return $info;
    }

    public function delCountry($data) {
        if (empty($data['id'])) {
            $info = 0;
        } else {
            $info = $this->field('id,bn as country_bn,name,name_en,name_ru,name_es')
                    ->where(array('lang' => 'zh', 'deleted_flag' => 'N', 'id' => $data['id']))
                    ->find();
            $this->where(array('bn' => $info['country_bn']))->save(array('deleted_flag' => 'Y', 'source' => $data['source']));

            $area = $this->table('erui_operation.market_area_country')
                    ->where(array('country_bn' => $info['country_bn']))
                    ->delete();
            $info = 1;
        }
        return $info;
    }

    public function insertCountry($data) {
        $arr = [];
        foreach ($data['country_name'] as $k => $v) {
            $arr[$k]['lang'] = $k;
            $arr[$k]['code'] = $data['code'];
            $arr[$k]['name'] = $data['country_name'][$k];
            $arr[$k]['name_en'] = $data['country_name']['en'];
            $arr[$k]['name_ru'] = $data['country_name']['ru'];
            $arr[$k]['name_es'] = $data['country_name']['es'];
            $arr[$k]['bn'] = $data['country_bn'];
            $arr[$k]['int_tel_code'] = $data['tel_code'];
            $arr[$k]['region_bn'] = $data['area_bn'];
            $arr[$k]['source'] = $data['source'];
            $arr[$k]['code'] = $data['country_name']['en'];
        }
        $info[] = $arr['zh'];
        $info[] = $arr['en'];
        $info[] = $arr['ru'];
        $info[] = $arr['es'];
        $res = $this->addAll($info);

        $areaInfo['market_area_bn'] = $data['area_bn'];
        $areaInfo['country_bn'] = $data['country_bn'];
        $areaInfo['created_at'] = date('Y-m-d H:i:s');
        $model = new MarketAreaCountryModel();
        $area = $model->add($areaInfo);
        return true;
    }

    public function updateCountry($data) {
        $arr = [];
        foreach ($data['country_name'] as $k => $v) {
            $arr[$k]['lang'] = $k;
//            $arr[$k]['code']=$data['code'];
            $arr[$k]['name'] = $data['country_name'][$k] ? $data['country_name'][$k] : null;
            $arr[$k]['name_en'] = $data['country_name']['en'] ? $data['country_name']['en'] : null;
            $arr[$k]['name_ru'] = $data['country_name']['ru'] ? $data['country_name']['ru'] : null;
            $arr[$k]['name_es'] = $data['country_name']['es'] ? $data['country_name']['es'] : null;
//            $arr[$k]['bn']=$data['country_bn'];
            $arr[$k]['int_tel_code'] = $data['tel_code'];
            $arr[$k]['region_bn'] = $data['area_bn'];
            $arr[$k]['source'] = $data['source'];
        }
        $hehe = $this->field('id,code,bn')->where(array('id' => $data['id']))->find();
        $info = $this->field('lang')->where(array('bn' => $hehe['bn']))->select();
        $a = array_keys($arr);
        $z = [];
        foreach ($info as $k => $v) {
            $z[] = $v['lang'];
        }
        $d = array_diff($a, $z);
        $i = array_intersect($a, $z);
        if (!empty($d)) {
            foreach ($d as $k => $v) {
                $arr[$v]['bn'] = $hehe['bn'];
                $arr[$v]['code'] = $hehe['code'];
                $res = $this->add($arr[$v]);
            }
        }
        if (!empty($i)) {
            foreach ($i as $k => $v) {
                $this->where(array('lang' => $v, 'bn' => $hehe['bn']))->save($arr[$v]);
            }
        }
//        $this->where(array('id'=>$data['id']))->save($arr['zh']);
//        $this->where("bn='$hehe[bn]' and id <> $data[id] ")->save(array('deleted_flag'=>'Y'));
//        $this->where("bn='$hehe[bn]'")->save(array('deleted_flag'=>'Y'));
//        $info[]=$arr['zh'];
//        $info[]=$arr['en'];
//        $info[]=$arr['ru'];
//        $info[]=$arr['es'];
//        foreach($info as $k => $v){
//            $this->where("bn='$hehe[bn]'")->save(array('deleted_flag'=>'Y'));
//        }
//        $res=$this->addAll($info);

        $areaInfo['market_area_bn'] = $data['area_bn'];
        $areaInfo['country_bn'] = $hehe['bn'];
        $areaInfo['created_at'] = date('Y-m-d H:i:s');
        $model = new MarketAreaCountryModel();
        $model->where(array('country_bn' => $hehe['bn']))->delete();
        $model->add($areaInfo);
        return true;
    }

    public function getCountryCond($data) {
        $cond = " country.lang='zh' and country.deleted_flag='N'";
        if (!empty($data['area_bn'])) {
            $cond .= " and area.bn='" . trim($data['area_bn']) . "'";
        }
        if (!empty($data['country_name'])) {
            $cond .= " and country.name like '%$data[country_name]%'";
        }
        return $cond;
    }

    public function countryAdmin($data = []) {
        $cond = $this->getCountryCond($data);
        $page = isset($data['current_page']) ? $data['current_page'] : 1;
        $lang = isset($data['lang']) ? $data['lang'] : 'zh';
        $offsize = ($page - 1) * 10;
        $count = $this->alias('country')
                ->join('erui_operation.market_area_country countryBn on country.bn=countryBn.country_bn', 'left')
                ->join("erui_operation.market_area area on countryBn.market_area_bn=area.bn and area.lang='zh'", 'left')
                ->field('country.id,country.bn as country_bn,country.name,country.name_en,country.name_ru,country.name_es,area.name as area_name')
                ->where($cond)


//            ->where(array('country.lang'=>'zh','country.deleted_flag'=>'N'))
                ->count();
        $field = 'country.id,country.bn as country_bn,country.name as country_name_zh,country.name_en as country_name_en,country.name_ru as country_name_ru,country.name_es as country_name_es,area.name as area_name';
        $field .= ",(select count(*) from erui_dict.port port where port.country_bn=country.bn and port.deleted_flag='N' and port.lang='zh') as port_count";
        $info = $this->alias('country')
                ->join('erui_operation.market_area_country countryBn on country.bn=countryBn.country_bn', 'left')
                ->join("erui_operation.market_area area on countryBn.market_area_bn=area.bn and area.lang='$lang'", 'left')
                ->field($field)
                ->where($cond)
                ->order('country.id desc')
                ->limit($offsize, 10)
                ->select();
        if (empty($info)) {
            $info = [];
        }
        $arr['current_page'] = $page;
        $arr['total_count'] = $count;
        $arr['info'] = $info;
        return $arr;
    }

    public function countryTest() {
        $bn = $this->field('lang,bn,name')->select();
        foreach ($bn as $k => $v) {
            $area = $this->table('erui_operation.market_area_country')->field('market_area_bn')->where(array('country_bn' => $v['bn']))->find();
            if ($v['lang'] == 'en') {
                $this->where(array('bn' => $v['bn'], 'lang' => 'zh'))
                        ->save(array('region_bn' => $area['market_area_bn'], 'name_en' => $v['name']));
            } elseif ($v['lang'] == 'ru') {
                $this->where(array('bn' => $v['bn'], 'lang' => 'zh'))
                        ->save(array('region_bn' => $area['market_area_bn'], 'name_ru' => $v['name']));
            } elseif ($v['lang'] == 'es') {
                $this->where(array('bn' => $v['bn'], 'lang' => 'zh'))
                        ->save(array('region_bn' => $area['market_area_bn'], 'name_es' => $v['name']));
            } elseif ($v['lang'] == 'zh') {
                $this->where(array('bn' => $v['bn'], 'lang' => 'zh'))
                        ->save(array('region_bn' => $area['market_area_bn']));
            }
        }
        return true;
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
        $market_area = $MarketArea->field('name,bn')->where(['bn' => $market_area_bn['market_area_bn'],
                    'lang' => $lang, 'deleted_flag' => 'N'])->find();
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
                'deleted_flag' => 'N'
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

    public function getCountryAndCodeByBn($bn = '', $lang = '') {
        if (empty($bn) || empty($lang)) {
            return '';
        }
        $condition = array(
            'bn' => $bn,
            'lang' => $lang,
            'deleted_flag' => 'N'
        );
        $field = 'name,code';
        $result = $this->field($field)->where($condition)->find();
        return $result;
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
                'status' => self::STATUS_VALID,
                'deleted_flag' => 'N'
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
     * 根据国家简称获取营销区域和国家名称
     * @param array $bns // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getNameAndAreasBybns($bns) {


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
            $areas = $this->where($where)
                            ->join($where)
                            ->field('bn,name')->select();
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

    /**
     * 通过集团CRM的国家名称获取country_bn和电话区号
     * 王帅
     * @param $country_name
     */
    public function getCountryBnCodeByName($country_name) {
        return $this->field('bn,int_tel_code')->where(array('name' => $country_name))->find();
    }

    //国家简称获取->国家名称,地区名称
    public function getCountryAreaByBn($country_bn, $lang = 'zh') {
        $cond = array(
            'bn' => $country_bn,
            'lang' => $lang,
            'deleted_flag' => 'N'
        );
        $country = $this->field('name as country_name')->where($cond)->find();
        $area = $this->table('erui_operation.market_area_country')->alias('country')
                ->join('erui_operation.market_area area on country.market_area_bn=area.bn and area.deleted_flag=\'N\'', 'left')
                ->field('area.name as area_name')
                ->where(array('country.country_bn' => $country_bn, 'area.lang' => $lang))
                ->find();
        $arr['area'] = $area['area_name'];
        $arr['country'] = $country['country_name'];
        return $arr;
    }

    /**
     * @desc 通过国家简称获取名称
     *
     * @param string $bn 国家简称
     * @param string $lang 语言
     * @return mixed
     * @author liujf
     * @time 2018-03-21
     */
    public function getCountryNameByBn($bn, $lang = 'zh') {
        return $this->where(['bn' => $bn, 'lang' => $lang, 'deleted_flag' => 'N'])->getField('name');
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

}
