<?php

/* To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EsProduct
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   ES 产品
 */
class EsProductModel extends Model {

//put your code here
    protected $tableName = 'product';
    protected $dbName = 'erui2_goods'; //数据库名称

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /*
     * 判断搜索条件是否存在
     * 存在 则组合查询
     * @author  zhongyg
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称    
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getQurey(&$condition, &$body, $qurey_type = ESClient::MATCH, $name = '', $field = null) {
        if ($qurey_type == ESClient::MATCH || $qurey_type == ESClient::MATCH_PHRASE) {
            if (isset($condition[$name]) && $condition[$name]) {
                $value = $condition[$name];
                if (!$field) {
                    $field = $name;
                }
                $body['query']['bool']['must'][] = [$qurey_type => [$field => $value]];
            }
        } elseif ($qurey_type == ESClient::WILDCARD) {

            if (isset($condition[$name]) && $condition[$name]) {

                $value = $condition[$name];
                if (!$field) {
                    $field = $name;
                }
                $body['query']['bool']['must'][] = [$qurey_type => [$field => '*' . $value . '*']];
            }
        } elseif ($qurey_type == ESClient::MULTI_MATCH) {
            if (isset($condition[$name]) && $condition[$name]) {
                $value = $condition[$name];
                if (!$field) {
                    $field = [$name];
                }
                $body['query']['bool']['must'][] = [$qurey_type => [
                        'query' => $value,
                        'type' => 'most_fields',
                        'operator' => 'and',
                        'fields' => $field
                ]];
            }
        } elseif ($qurey_type == ESClient::RANGE) {
            if (isset($condition[$name . '_start']) && isset($condition[$name . '_end']) && $condition[$name . '_end'] && $condition[$name . '_start']) {
                $created_at_start = $condition[$name . '_start'];
                $created_at_end = $condition[$name . '_end'];
                $body['query']['bool']['must'][] = [ESClient::RANGE => [$name => ['gte' => $created_at_start, 'gle' => $created_at_end,]]];
            } elseif (isset($condition[$name . '_start']) && $condition[$field . '_start']) {
                $created_at_start = $condition[$name . '_start'];

                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['gte' => $created_at_start,]]];
            } elseif (isset($condition[$name . '_end']) && $condition[$name . '_end']) {
                $created_at_end = $condition[$name . '_end'];
                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['gle' => $created_at_end,]]];
            }
        }
    }

    /*
     * 判断搜索状态是否存在
     * 存在 则组合查询
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @param string $default // 默认值
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getStatus(&$condition, &$body, $qurey_type = ESClient::MATCH, $name = '', $field = '', $array = [], $default = 'VALID') {
        if (!$field) {
            $field = [$name];
        }
        if (isset($condition[$name]) && $condition[$name]) {
            $status = $condition[$name];
            if ($status == 'ALL') {
                
            } elseif (in_array($status, $array)) {

                $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $status]];
            } else {
                $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $default]];
            }
        } else {
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $default]];
        }
    }

    /*
     * 判断搜索状态是否存在
     * 存在 则组合查询
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getQureyByArr(&$condition, &$body, $qurey_type = ESClient::MATCH_PHRASE, $names = '', $field = '') {
        if (!$field) {
            $field = [$names];
        }
        if (isset($condition[$names]) && $condition[$names]) {
            $name_arr = $condition[$names];
            $bool = [];
            foreach ($name_arr as $name) {
                $bool[] = [$qurey_type => [$field => $name]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $bool]];
        }
    }

    /*
     * 判断搜索状态是否存在 
     * 存在 则组合查询
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @param string $default // 默认值
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getQureyByBool(&$condition, &$body, $qurey_type = ESClient::MATCH_PHRASE, $name = '', $field = '', $default = 'N') {
        if (!$field) {
            $field = $name;
        }
        if (isset($condition[$name]) && $condition[$name]) {
            $recommend_flag = $condition[$name] == 'Y' ? 'Y' : $default;
            $body['query']['bool']['must'][] = [$qurey_type => [$field => $recommend_flag]];
        }
    }

    /* 条件组合
     * @param mix $condition // 搜索条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function getCondition($condition) {
        $body = [];
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'spu');
        $this->_getQureyByArr($condition, $body, ESClient::MATCH_PHRASE, 'spus', 'spu');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'show_cat_no', 'show_cats.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'market_area_bn', 'show_cats.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'country_bn', 'show_cats.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no1', 'meterial_cat.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no2', 'meterial_cat.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no3', 'meterial_cat.all');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'created_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'checked_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'updated_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'shelves_at');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'shelves_by');
        $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']);
        $this->_getQureyByBool($condition, $body, ESClient::MATCH_PHRASE, 'recommend_flag', 'recommend_flag', 'N');
        // $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'shelves_status', 'shelves_status', ['VALID', 'INVALID']);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'brand');
        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'real_name', 'name');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'source');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'exe_standard');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'app_scope');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'advantages');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'tech_paras');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'source_detail');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'keywords');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_id', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_name', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'created_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'updated_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'checked_by');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'show_name');
        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'name', 'name');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'attrs');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'specs');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'warranty');
        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'keyword', ['show_name', 'attrs', 'specs', 'spu', 'source', 'brand']);
        return $body;
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @param mix  $_source //要搜索的字段
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getProducts($condition, $_source, $lang = 'en') {

        try {
//            if (!$_source) {
//                $_source = ['material_cat_no', 'spu', 'name', 'show_name', 'attrs', 'specs'
//                    , 'profile', 'suppliers', 'source', 'attachs', 'brand',
//                    'recommend_flag', 'supply_ability', 'tech_paras', 'meterial_cat',
//                    'brand', 'supplier_name', 'sku_count'];
//            }
            $body = $this->getCondition($condition);
            $redis_key = 'es_product2_' . md5(json_encode($body));
            //   $data = json_decode(redisGet($redis_key), true);
            if (!$data) {
                $pagesize = 10;
                $current_no = 1;
                if (isset($condition['current_no'])) {
                    $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
                }
                if (isset($condition['pagesize'])) {
                    $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
                }
                $from = ($current_no - 1) * $pagesize;
                $es = new ESClient();
                unset($condition['source']);
                $es->setbody($body)->setsort('sort_order', 'desc')->setsort('_id', 'desc');

                if (isset($condition['sku_count']) && $condition['sku_count'] == 'Y') {
                    $es->setaggs('sku_count', 'sku_count', 'sum');
                } else {
                    $es->setaggs('material_cat_no', 'material_cat_no');
                }
                $data = [$es->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];

//                if ($data) {
//                    redisSet($redis_key, json_encode($data), 3600);
//                }
                return $data;
            }
            return $data;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 获取产品总数
     * @param array $condition //搜索条件
     * @param string $lang // 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getCount($condition, $lang = 'en') {

        try {

            $body = $this->getCondition($condition);
            $es = new ESClient();
            $ret = $es->setbody($body)
                    ->count($this->dbName, $this->tableName . '_' . $lang, '');
            if (isset($ret['count'])) {
                return $ret['count'];
            } else {
                return 0;
            }
        } catch (Exception $ex) {

            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    /*
     * 获取商品数量
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getKsucount($condition, $lang = 'en') {

        try {
            $es = new ESClient();
            $body = $this->getCondition($condition);
            return $es->setbody($body)
                            ->setfields('spu')
                            ->setaggs('sku_num', 'sku_num', 'sum')
                            ->search($this->dbName, $this->tableName . '_' . $lang);
        } catch (Exception $ex) {

            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix 
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getmeterial_catlist($condition, $lang = 'en') {

        try {
            $body = $this->getCondition($condition);
            $pagesize = 50;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }
            $from = ($current_no - 1) * $pagesize;
            $es = new ESClient();
            return $es->setbody($body)
                            ->setaggs('material_cat_no', 'material_cat_no')
                            ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getshow_catlist($condition, $lang = 'en') {

        try {
            $data = $this->getmeterial_catlist($condition, $lang);
            $show_model = new ShowCatModel();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 根据spu获取sku数   (这里不包括删除的)
     * @author link
     * @param string $spus spu编码
     * @param string $lang 语言
     * @retrun int
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */
    public function getCountBySpus($spus = '', $lang = '') {
        $condition = array(
            'status' => array('neq', self::STATUS_DELETED)
        );
        if ($spus != '') {
            $condition['spu'] = ['in', $spus];
        }
        if ($lang != '') {
            $condition['lang'] = $lang;
        }
        try {
//redis 操作
            $redis_key = md5(json_encode($condition));
            if (redisExist($redis_key)) {
                return redisGet($redis_key);
            } else {
                $count = $this->field('count(id)')->where($condition)->group('spu')->select();
                redisSet($redis_key, $count);
                return $count ? $count : [];
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /* 通过ES 获取数据列表
     * @param string $spu // 商品名称 属性名称或属性值
     * @param string $lang // 展示分类编码
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getproductsbyspu($spu, $lang = 'en') {
        try {
            $es = new ESClient();
            $es->setmust(['spu' => $spu], ESClient::MATCH_PHRASE);
            return $es->search($this->dbName, $this->tableName . '_' . $lang);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 和上级分类信息 顶级分类信息
     * @param mix $cat_no // 物料分类编码数组3f
     * @param string $lang // 语言 zh en ru es 
     * @return mix  物料分类及上级和顶级信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getmaterial_cat($cat_no, $lang = 'en') {
        try {
            $cat3 = $this->table('erui2_goods.material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => $cat_no, 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            $cat2 = $this->table('erui2_goods.material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            $cat1 = $this->table('erui2_goods.material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            return [$cat1['cat_no'], $cat1['name'], $cat2['cat_no'], $cat2['name'], $cat3['cat_no'], $cat3['name']];
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 及上级分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  物料分类及上级和顶级信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getmaterial_cats($cat_nos, $lang = 'en') {
        if (!$cat_nos) {
            return[];
        }
        try {
            $cat3s = $this->table('erui2_goods.material_cat')
                            ->field('id,cat_no,name,parent_cat_no')
                            ->where(['cat_no' => ['in', $cat_nos], 'lang' => $lang, 'status' => 'VALID'])->select();

            if (!$cat3s) {
                return [];
            }
            $cat1_nos = $cat2_nos = [];
            foreach ($cat3s as $cat) {
                $cat2_nos[] = $cat['parent_cat_no'];
            }
            $cat2s = $this->table('erui2_goods.material_cat')
                    ->field('id,cat_no,name,parent_cat_no')
                    ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            if (!$cat2s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name']];
                }
                return $newcat3s;
            }
            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }

            $cat1s = $this->table('erui2_goods.material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $newcat1s = [];
            if (!$cat1s) {
                $newcat3s = [];
                $newcat2s = [];
                foreach ($cat2s as $val) {
                    $newcat2s[$val['cat_no']] = $val;
                }
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name'],
                        'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                        'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    ];
                }
                return $newcat3s;
            }
            foreach ($cat1s as $val) {
                $newcat1s[$val['cat_no']] = $val;
            }
            $newcat2s = [];
            foreach ($cat2s as $val) {
                $newcat2s[$val['cat_no']] = $val;
            }
            foreach ($cat3s as $val) {
                $newcat3s[$val['cat_no']] = ['cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['cat_no'],
                    'cat_name1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['name'],
                    'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                    'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    'cat_no3' => $val['cat_no'],
                    'cat_name3' => $val['name']];
            }

            return $newcat3s;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据SPUS 获取产品属性信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  属性信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getproduct_attrbyspus($spus, $lang = 'en') {
        if (!$spus || !is_array($spus)) {
            return [];
        }
        try {

            $product_attrs = $this->table('erui2_goods.product_attr')
                    ->field('*')
                    ->where(['spu' => ['in', $spus],
                        'lang' => $lang,
                        'status' => 'VALID'])
                    ->select();

            $ret = [];
            if ($product_attrs) {
                foreach ($product_attrs as $item) {

                    $ret[$item['spu']][] = $item;
                }
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据SPUS 获取产品展示分类信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  展示分类信息列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getshow_catsbyspus($spus, $lang = 'en') {
        try {
            if ($spus && is_array($spus)) {
                $show_cat_products = $this->table('erui2_goods.show_cat_product scp')
                        ->join('erui2_goods.show_cat sc on scp.cat_no=sc.cat_no', 'left')
                        ->field('scp.cat_no,scp.spu')
                        ->where(['scp.spu' => ['in', $spus],
                            'scp.status' => 'VALID',
                            'sc.status' => 'VALID',
                            'sc.lang' => $lang,
                            'sc.id>0',
                        ])
                        ->select();
            } else {
                return [];
            }
            $ret = [];
            foreach ($show_cat_products as $item) {

                $ret[$item['spu']] = $item['cat_no'];
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据分类编码数组获取物料分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  规格信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getshow_material_cats($cat_nos, $lang = 'en') {
        if (!$cat_nos || !is_array($cat_nos)) {
            return [];
        }
        try {

            $show_material_cats = $this->table('erui2_goods.show_material_cat smc')
                    ->join('erui2_goods.show_cat sc on smc.show_cat_no=sc.cat_no')
                    ->field('show_cat_no,material_cat_no')
                    ->where([
                        'smc.material_cat_no' => ['in', $cat_nos],
                        'sc.status' => 'VALID',
                        'sc.lang' => $lang,
                        'sc.id>0',
                        'smc.status' => 'VALID'])
                    ->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
        $ret = [];
        if ($show_material_cats) {
            foreach ($show_material_cats as $item) {

                $ret[$item['material_cat_no']][$item['show_cat_no']] = $item['show_cat_no'];
            }
        }

        return $ret;
    }

    /*
     * 根据展示分类编码数组获取展示分类信息
     * @param mix $show_cat_nos // 展示分类编码数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getshow_cats($show_cat_nos, $lang = 'en') {

        try {
            if ($show_cat_nos) {
                $cat3s = $this->table('erui2_goods.show_cat')
                        ->field('market_area_bn,country_bn,parent_cat_no,cat_no,name')
                        ->where(['cat_no' => ['in', $show_cat_nos], 'lang' => $lang, 'status' => 'VALID'])
                        ->select();
                $cat1_nos = $cat2_nos = [];
            } else {
                return [];
            }

            if (!$cat3s) {
                return [];
            }

            foreach ($cat3s as $cat) {
                $cat2_nos[] = $cat['parent_cat_no'];
            }
            if ($cat2_nos) {
                $cat2s = $this->table('erui2_goods.show_cat')
                                ->field('id,cat_no,name,parent_cat_no')
                                ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            }
            if (!$cat2s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name'],
                        'market_area_bn' => $val['market_area_bn'],
                        'country_bn' => $val['country_bn']
                    ];
                }
                return $newcat3s;
            }
            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }
            if ($cat1_nos) {
                $cat1s = $this->table('erui2_goods.show_cat')->field('id,cat_no,name')
                                ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            }

            $newcat2s = [];
            foreach ($cat2s as $val) {
                $newcat2s[$val['cat_no']] = $val;
            }
            if (!$cat1s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name'],
                        'market_area_bn' => $val['market_area_bn'],
                        'country_bn' => $val['country_bn'],
                        'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                        'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                        'market_area_bn' => $val['market_area_bn'],
                        'country_bn' => $val['country_bn'],
                    ];
                }
                return $newcat3s;
            }
            $newcat1s = [];
            foreach ($cat1s as $val) {
                $newcat1s[$val['cat_no']] = $val;
            }
            foreach ($cat3s as $val) {
                $newcat3s[$val['cat_no']] = [
                    'cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['cat_no'],
                    'cat_name1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['name'],
                    'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                    'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    'cat_no3' => $val['cat_no'],
                    'market_area_bn' => $val['market_area_bn'],
                    'country_bn' => $val['country_bn'],
                    'cat_name3' => $val['name']];
            }
            return $newcat3s;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过SKU获取数据商品文件列表
     * @param mix $spus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getproduct_attachsbyspus($spus, $lang = 'en') {

        try {
            $product_attachs = $this->table('erui2_goods.product_attach')
                    ->field('id,attach_type,attach_url,attach_name,attach_url,spu')
                    ->where(['spu' => ['in', $spus],
                        'attach_type' => ['in', ['BIG_IMAGE', 'MIDDLE_IMAGE', 'SMALL_IMAGE', 'DOC']],
                        'status' => 'VALID'])
                    ->select();
            $ret = [];
            if ($product_attachs) {
                foreach ($product_attachs as $item) {
                    $data['attach_name'] = $item['attach_name'];
                    $data['attach_url'] = $item['attach_url'];
                    $ret[$item['spu']][$item['attach_type']][] = $data;
                }
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据SPU数组获取展示属性信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getproductattrsbyspus($spus, $lang = 'en') {
        try {
            $products = $this->where(['spu' => ['in', $spus], 'lang' => $lang])
                    ->field('spu,material_cat_no,brand,source')
                    ->select();
            $brands = [];
            $sources = [];
            $material_cat_nos = [];
            $attr_spus = $mcat_nos = [];
            foreach ($products as $item) {
                $this->_findnulltoempty($item);
                $mcat_nos[] = $item['material_cat_no'];
                $attr_spus[] = $item['spu'];
                $brands[$item['spu']] = $item['brand'];
                $sources[$item['spu']] = $item['source'];
                $material_cat_nos[$item['spu']] = $item['material_cat_no'];
            }
            $unique_spus = array_unique($attr_spus);
            $mcat_nos = array_unique($mcat_nos);
            $mcats = $this->getmaterial_cats($mcat_nos, $lang);
            $scats_no_spu = $this->getshow_catsbyspus($unique_spus, $lang);
            $scats_no_mcatsno = $this->getshow_material_cats($mcat_nos, $lang);
            $product_attrs = $this->getproduct_attrbyspus($unique_spus, $lang);
            $show_cat_nos = [];
            foreach ($scats_no_spu as $show_cat_no) {
                $show_cat_nos[] = $show_cat_no;
            }foreach ($scats_no_mcatsno as $showcatnos) {
                foreach ($showcatnos as $show_cat_no) {
                    $show_cat_nos[] = $show_cat_no;
                }
            }
            $unit_show_cat_nos = array_unique($show_cat_nos);
            $scats = $this->getshow_cats($unit_show_cat_nos, $lang);

            $ret = [];
            foreach ($products as $item) {
                $show_cat = [];
                $show_cat[$scats_no_spu[$item['spu']]] = $scats[$scats_no_spu[$item['spu']]];
                if (isset($scats_no_mcatsno[$item['material_cat_no']])) {
                    foreach ($scats_no_mcatsno[$item['material_cat_no']] as $show_cat_no) {
                        $show_cat[$show_cat_no] = $scats[$show_cat_no];
                    }
                }
                if (isset($mcats[$item['material_cat_no']])) {
                    $body['meterial_cat'] = json_encode($mcats[$item['material_cat_no']], JSON_UNESCAPED_UNICODE);
                } else {
                    $body['meterial_cat'] = json_encode(new stdClass(), JSON_UNESCAPED_UNICODE);
                }
                if (!empty($show_cat)) {
                    rsort($show_cat);
                    $body['show_cats'] = json_encode($show_cat, JSON_UNESCAPED_UNICODE);
                } else {
                    $body['show_cats'] = json_encode([], JSON_UNESCAPED_UNICODE);
                }
                if (isset($product_attrs[$item['spu']])) {
                    $body['attrs'] = json_encode($product_attrs[$item['spu']], JSON_UNESCAPED_UNICODE);
                    if ($product_attrs[$item['spu']][0]['spec_attrs']) {
                        $body['specs'] = $product_attrs[$item['spu']][0]['spec_attrs'];
                    } else {
                        $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    $body['attrs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                    $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                }
                $body['brand'] = $brands[$item['spu']];
                $body['source'] = $sources[$item['spu']];
                $body['material_cat_no'] = $material_cat_nos[$item['spu']];
                $ret[$item['spu']] = $body;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 将数组中的null值转换为空值
     * @author zyg 2017-07-31
     * @param array $item // 语言 zh en ru es 
     * @return mix 
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    private function _findnulltoempty(&$item) {
        foreach ($item as $key => $val) {
            if (is_null($val)) {
                $item[$key] = '';
            }
        }
    }

    /*
     * 批量导入产品数据到ES
     * @author zyg 2017-07-31
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function importproducts($lang = 'en') {
        try {
            $count = $this->where(['lang' => $lang])->count('id');
            $max_id = 0;
            echo '共有', $count, '条记录需要导入!', PHP_EOL;
            $k = 1;
            for ($i = 0; $i < $count; $i += 100) {
                if ($i > $count) {
                    $i = $count;
                }

                $products = $this->where([
                                    'lang' => $lang,
                                    'id' => ['gt', $max_id]
                                ])
                                ->limit(0, 100)->order('id asc')->select();

                $spus = $mcat_nos = [];
                if ($products) {
                    foreach ($products as $item) {
                        $mcat_nos[] = $item['material_cat_no'];
                        $spus[] = $item['spu'];
                    }
                    $spus = array_unique($spus);
                    $mcat_nos = array_unique($mcat_nos);
                    $mcats = $this->getmaterial_cats($mcat_nos, $lang);
                    $scats_no_spu = $this->getshow_catsbyspus($spus, $lang);
                    $scats_no_mcatsno = $this->getshow_material_cats($mcat_nos, $lang);
                    $product_attrs = $this->getproduct_attrbyspus($spus, $lang);
                    $attachs = $this->getproduct_attachsbyspus($spus, $lang);
                    $show_cat_nos = [];
                    foreach ($scats_no_spu as $show_cat_no) {
                        $show_cat_nos[] = $show_cat_no;
                    }
                    foreach ($scats_no_mcatsno as $showcatnos) {
                        foreach ($showcatnos as $show_cat_no) {
                            $show_cat_nos[] = $show_cat_no;
                        }
                    }
                    $show_cat_nos = array_unique($show_cat_nos);
                    $scats = $this->getshow_cats($show_cat_nos, $lang);


                    $es = new ESClient();

                    foreach ($products as $key => $item) {
                        $id = $item['spu'];
                        $this->_findnulltoempty($item);
                        $body = $item;

                        if ($body['source'] == 'ERUI') {
                            $body['sort_order'] = 100;
                        } else {
                            $body['sort_order'] = 1;
                        }
                        if (in_array($body['brand'], ['KERUI', '科瑞'])) {
                            $body['sort_order'] += 20;
                        }

                        if (isset($attachs[$item['spu']])) {
                            $body['attachs'] = json_encode($attachs[$item['spu']], 256);
                        } else {
                            $body['attachs'] = '[]';
                        }
                        $show_cat = [];
                        if (isset($scats_no_spu[$item['spu']]) && isset($scats[$scats_no_spu[$item['spu']]])) {
                            $show_cat[$scats_no_spu[$item['spu']]] = $scats[$scats_no_spu[$item['spu']]];
                        }
                        if (isset($scats_no_mcatsno[$item['material_cat_no']])) {
                            foreach ($scats_no_mcatsno[$item['material_cat_no']] as $show_cat_no) {
                                $show_cat[$show_cat_no] = $scats[$show_cat_no];
                            }
                        }
                        if (isset($mcats[$item['material_cat_no']])) {
                            $body['meterial_cat'] = json_encode($mcats[$item['material_cat_no']], JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['meterial_cat'] = json_encode(new \stdClass(), JSON_UNESCAPED_UNICODE);
                        }
                        if ($show_cat) {
                            rsort($show_cat);
                            $body['show_cats'] = json_encode($show_cat, JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['show_cats'] = json_encode([], JSON_UNESCAPED_UNICODE);
                        }
                        if (isset($product_attrs[$item['spu']])) {
                            $body['attrs'] = json_encode($product_attrs[$item['spu']], JSON_UNESCAPED_UNICODE);
                            if ($product_attrs[$item['spu']][0]['spec_attrs']) {
                                $body['specs'] = $product_attrs[$item['spu']][0]['spec_attrs'];
                            } else {
                                $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                            }
                        } else {
                            $body['attrs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                            $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                        }

                        $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);


                        if ($flag['_shards']['successful'] !== 1) {
                            LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);
                        }
                        if ($key === 99) {
                            $max_id = $item['id'];
                        }
                        $k++;
                        print_r($flag);
                    }
                } else {
                    return false;
                }
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /* 条件判断
     * @param array $condition  条件
     * @param string $name需要判断的键值
     * @param string $default 默认值
     * @param string $type 判断的类型
     * @param array $arr 状态判断时状态数组
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    protected function _getValue($condition, $name, $default = null, $type = 'string', $arr = ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']) {
        if ($type === 'string') {
            if (isset($condition[$name]) && $condition[$name]) {
                $value = $condition[$name];
                $condition = null;
                unset($condition);
                return $value;
            } else {
                $condition = null;
                unset($condition);
                return $default;
            }
        } elseif ($type === 'bool') {
            if (isset($condition[$name]) && $condition[$name]) {
                $flag = $condition[$name] == 'Y' ? 'Y' : 'N';
                $condition = null;
                unset($condition);
                return $flag;
            } else {
                $condition = null;
                unset($condition);
                return 'N';
            }
        } elseif ($type === 'json') {
            if (isset($condition[$name]) && $condition[$name]) {
                $return = json_encode($condition[$name], 256);
                $condition = null;
                unset($condition);
                return $flag;
            } else {
                $condition = null;
                unset($condition);
                return json_encode($default, 256);
            }
        } elseif ($type === 'in_array') {
            if (isset($condition[$name]) && in_array($condition[$name], $arr)) {
                $return = strtoupper($condition[$name]);
                $condition = null;
                unset($condition);
                return $flag;
            } else {
                $condition = null;
                unset($condition);
                return $default;
            }
        }
    }

    /* 新增条件组合
     * @param array $condition  条件
     * @param string $lang 语言
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getInsertCodition($condition, $lang = 'en') {
        $data = [];
        if (isset($condition['id'])) {
            $data['id'] = $condition['id'];
        }
        $data['lang'] = $lang;
        if (isset($condition['material_cat_no'])) {
            $material_cat_no = $data['material_cat_no'] = $condition['material_cat_no'];
            $mcatmodel = new MaterialcatModel();
            $data['meterial_cat'] = json_encode($mcatmodel->getinfo($material_cat_no, $lang), 256);
            $smmodel = new ShowmaterialcatModel();
            $show_cat_nos = $smmodel->getshowcatnosBymatcatno($material_cat_no, $lang);
            $scats = $this->getshow_cats($show_cat_nos, $lang);
            $data['show_cats'] = $this->_getValue($scats, $material_cat_no, [], 'json');
        } else {
            $data['material_cat_no'] = '';
            $data['meterial_cat'] = json_encode(new \stdClass());
            $data['show_cats'] = json_encode([]);
        }
        if (isset($condition['spu'])) {
            $spu = $data['spu'] = $condition['spu'];
            $product_attrs = $this->getproduct_attrbyspus([$spu], $lang);
            $specs = $this->getproduct_specsbyspus([$spu], $lang);
            $attachs = $this->getproduct_attachsbyspus([$spu], $lang);
            $data['attrs'] = $this->_getValue($product_attrs, $spu, [], 'json');
            $data['specs'] = $this->_getValue($specs, $spu, [], 'json');
            $data['specs'] = $this->_getValue($attachs, $spu, [], 'json');
        } else {
            $data['spu'] = '';
            $data['attrs'] = json_encode([], 256);
            $data['specs'] = json_encode([], 256);
            $data['attachs'] = json_encode([], 256);
        }
        $data['qrcode'] = $this->_getValue($condition, 'qrcode');
        $data['name'] = $this->_getValue($condition, 'name');
        $data['show_name'] = $this->_getValue($condition, 'show_name');
        $data['keywords'] = $this->_getValue($condition, 'keywords');
        $data['exe_standard'] = $this->_getValue($condition, 'exe_standard');
        $data['app_scope'] = $this->_getValue($condition, 'app_scope');
        $data['tech_paras'] = $this->_getValue($condition, 'tech_paras');
        $data['profile'] = $this->_getValue($condition, 'profile');
        $data['description'] = $this->_getValue($condition, 'description');
        $data['brand'] = $this->_getValue($condition, 'brand');
        $data['warranty'] = $this->_getValue($condition, 'warranty');
        $data['customization_flag'] = $this->_getValue($condition, 'customization_flag');
        $data['customization_flag'] = $this->_getValue($condition, 'customization_flag');
        $data['customization_flag'] = $this->_getValue($condition, 'customization_flag', 'N', 'bool');
        $data['customizability'] = $this->_getValue($condition, 'customizability');
        $data['availability'] = $this->_getValue($condition, 'availability');
        $data['resp_time'] = $this->_getValue($condition, 'resp_time');
        $data['resp_rate'] = $this->_getValue($condition, 'resp_rate');
        $data['delivery_cycle'] = $this->_getValue($condition, 'delivery_cycle');
        $data['target_market'] = $this->_getValue($condition, 'target_market');
        $data['source'] = $this->_getValue($condition, 'source');
        $data['source_detail'] = $this->_getValue($condition, 'source_detail');
        $data['recommend_flag'] = $this->_getValue($condition, 'recommend_flag', 'N', 'bool');
        $data['status'] = $this->_getValue($condition, 'status', 'CHECKING', 'in_array');
        $data['created_by'] = $this->_getValue($condition, 'created_by');
        $data['created_at'] = $this->_getValue($condition, 'created_at');
        $data['updated_by'] = $this->_getValue($condition, 'updated_by');
        $data['updated_at'] = $this->_getValue($condition, 'updated_at');
        $data['checked_by'] = $this->_getValue($condition, 'checked_by');
        $data['checked_at'] = $this->_getValue($condition, 'checked_at');
        $data['shelves_status'] = $this->_getValue($condition, 'shelves_status', 'INVALID', 'in_array', ['INVALID', 'VALID']);
        return $data;
    }

    /*
     * 添加产品到Es
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function create_data($data, $lang = 'en') {
        try {
            $es = new ESClient();
            $body = $this->getInsertCodition($data);
            $id = $data['spu'];
            $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
            if (!isset($flag['created'])) {
                LOG::write("FAIL:" . $id . var_export($flag, true), LOG::ERR);
                return false;
            } else {
                return true;
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 添加产品到Es
     * @param array $data 需要更新的数据
     * @param string $spu  spu
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function update_data($data, $spu, $lang = 'en') {
        try {
            $es = new ESClient();
            $body = $this->getInsertCodition($data);
            if (empty($spu)) {
                return false;
            }
            $id = $spu;
            $flag = $es->update_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
            if ($flag['_shards']['successful'] !== 1) {
                LOG::write("FAIL:" . $id . var_export($flag, true), LOG::ERR);
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /* 上架
     * @param array $data 需要更新的数据
     * @param string $spu  spu
     * @param string $status 状态
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function changestatus($spu, $status = 'VALID', $lang = 'en') {
        try {
            $es = new ESClient();
            if (empty($spu)) {
                return false;
            }
            $data['status'] = $status;
            $id = $spu;
            $es->update_document($this->dbName, $this->tableName . '_' . $lang, $data, $id);
            return true;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /* 上下架
     * @param array $data 需要更新的数据
     * @param string $spu  spu
     * @param string $status 状态
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function changesShelvesstatus($spu, $status = 'VALID', $lang = 'en') {
        try {
            $es = new ESClient();
            if (empty($spu)) {
                return false;
            }
            $data['shelves_status'] = $status;
            $id = $spu;
            $es->update_document($this->dbName, $this->tableName . '_' . $lang, $data, $id);
            $esgoodsdata = [
                "doc" => [
                    "shelves_status" => $data['shelves_status'],
                ],
                "query" => [
                    ESClient::MATCH_PHRASE => [
                        "spu" => $spu
                    ],
                    ESClient::MATCH_PHRASE => [
                        "status" => 'VALID'
                    ]
                ]
            ];
            $es->UpdateByQuery($this->dbName, 'goods_' . $lang, $esgoodsdata);
            return true;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /* 新增ES
     * @param array $data 需要更新的数据
     * @param string $spu  spu
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品  
     */

    public function getshowcats($spu = null, $lang = 'en') {

        if (empty($spu)) {
            return false;
        }
        $showcatproduct_model = new ShowCatProductModel();
        $show_cat_nos = $showcatproduct_model->getShowCatnosBySpu($spu, $lang);
        $scats = $this->getshow_cats($show_cat_nos, $lang);
        $show_cats = json_encode($scats, 256);
        return $show_cats;
    }

    /* 新增ES
     * @param string $old_cat_no  需要更新的展示分类编码
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function update_showcats($old_cat_no, $lang = 'en') {
        if (empty($old_cat_no)) {
            return false;
        }
        $index = $this->dbName;
        $type = 'product_' . $lang;
        $count = $this->setbody(['query' => [
                        ESClient::MATCH_PHRASE => [
                            "show_cats" => $old_cat_no
                        ]
            ]])->count($index, $type);
        for ($i = 0; $i < $count['count']; $i += 100) {
            $ret = $this->setbody(['query' => [
                            ESClient::MATCH_PHRASE => [
                                "show_cats" => $old_cat_no
                            ]
                ]])->search($index, $type, $i, 100);
            $updateParams = array();
            $updateParams['index'] = $this->dbName;
            $updateParams['type'] = 'product_' . $lang;
            if ($ret) {
                foreach ($ret['hits']['hits'] as $item) {
                    $updateParams['body'][] = ['update' => ['_id' => $item['_id']]];
                    $updateParams['body'][] = ['doc' => $this->getshowcats($item['_source']['spu'], $lang)];
                }
                $es = new ESClient();
                $es->bulk($updateParams);
            }
        }
        $esgoods = new EsgoodsModel();
        $esgoods->update_showcats($old_cat_no, $lang);
        return true;
    }

    /* 更新物料分类
     * @param string $material_cat_no  物料分类
     * @param string $spu  SPU
     * @param string $lang 语言
     * @param string $new_cat_no  新的物料分类
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function Updatemeterialcatno($material_cat_no, $spu = null, $lang = 'en', $new_cat_no = '') {
        $es = new ESClient();
        if (empty($material_cat_no)) {
            return false;
        }
        if (!$new_cat_no) {
            $new_cat_no = $material_cat_no;
        }
        $type = $this->tableName . '_' . $lang;
        $mcatmodel = new MaterialcatModel();
        $data['meterial_cat'] = json_encode($mcatmodel->getinfo($new_cat_no, $lang), 256);
        $smmodel = new ShowmaterialcatModel();
        $show_cat_nos = $smmodel->getshowcatnosBymatcatno($new_cat_no, $lang);
        $scats = $this->getshow_cats($show_cat_nos, $lang);
        $data['show_cats'] = $this->_getValue($scats, $new_cat_no, [], 'json');
        $data['material_cat_no'] = $new_cat_no;
        if ($spu) {
            $id = $spu;
            $es->update_document($this->dbName, $type, $data, $id);
        } else {
            $es_product_data = [
                "doc" => [
                    "meterial_cat" => $data['meterial_cat'],
                    "show_cats" => $data['show_cats'],
                    'material_cat_no' => $new_cat_no,
                ],
                "query" => [
                    ESClient::MATCH_PHRASE => [
                        "material_cat_no" => $material_cat_no
                    ]
                ]
            ];
            $es->UpdateByQuery($this->dbName, 'product_' . $lang, $es_product_data);
        }
        if ($spu) {
            $esgoodsdata = [
                "doc" => [
                    "meterial_cat" => $data['meterial_cat'],
                    "show_cats" => $data['show_cats'],
                ],
                "query" => [
                    ESClient::MATCH_PHRASE => [
                        "spu" => $spu
                    ]
                ]
            ];
        } else {
            $esgoodsdata = [
                "doc" => [
                    "meterial_cat" => $data['meterial_cat'],
                    "show_cats" => $data['show_cats'],
                ],
                "query" => [
                    ESClient::MATCH_PHRASE => [
                        "material_cat_no" => $material_cat_no
                    ]
                ]
            ];
        }
        $es->UpdateByQuery($this->dbName, 'goods_' . $lang, $esgoodsdata);
        return true;
    }

    /* 更新属性
     * @param string $spu  SPU
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品  
     */

    public function Update_Attrs($spu, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        $product_attrs = $this->getproduct_attrbyspus([$spu], $lang);
        $specs = $this->getproduct_specsbyspus([$spu], $lang);
        $id = $spu;
        $data['attrs'] = $this->_getValue($product_attrs, $spu, [], 'json');
        $data['specs'] = $this->_getValue($specs, $spu, [], 'json');
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);
        $goodsmodel = new GoodsModel();
        $sku_infos = $goodsmodel->getskusbyspu($spu, $lang);
        $esgoodsmodel = new EsgoodsModel();
        foreach ($sku_infos as $sku) {
            $esgoodsmodel->Update_Attrs($sku['sku'], $lang, $product_attrs, $specs);
        }
        return true;
    }

    /* 更新附件
     * @param string $spu  SPU
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品  
     */

    public function Update_Attachs($spu, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        $attachs = $this->getproduct_attachsbyspus([$spu], $lang);
        $data['attachs'] = $this->_getValue($attachs, $spu, [], 'json');
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);


        return true;
    }

    /* 更新品牌
     * @param string $spu  SPU
     * @param string $brand 品牌
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function Update_brand($spu, $brand, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }

        if ($brand) {
            $data['brand'] = $brand;
        } else {
            $data['brand'] = '';
        }
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);

        $esgoodsdata = [
            "doc" => [
                "brand" => $brand,
            ],
            "query" => [
                ESClient::MATCH_PHRASE => [
                    "spu" => $spu
                ]
            ]
        ];
        $es->UpdateByQuery($this->dbName, 'goods_' . $lang, $esgoodsdata);
        return true;
    }

    /* 更新SPU名称
     * @param string $spu  SPU
     * @param string $spuname SPU名称
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function Update_spuname($spu, $spuname, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        if ($spuname) {
            $data['name'] = $spuname;
        } else {
            $data['name'] = '';
        }
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);
        return true;
    }

    /* 更新供应商
     * @param string $spu  SPU
     * @param string $supplier_name SPU名称
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function Update_supplier_name($spu, $supplier_name, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        if ($supplier_name) {
            $data['supplier_name'] = $supplier_name;
        } else {
            $data['supplier_name'] = '';
        }
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);

        $esgoodsdata = [
            "doc" => [
                "supplier_name" => $supplier_name,
            ],
            "query" => [
                ESClient::MATCH_PHRASE => [
                    "spu" => $spu
                ]
            ]
        ];
        $es->UpdateByQuery($this->dbName, 'goods_' . $lang, $esgoodsdata);
        return true;
    }

    /* 删除产品
     * @param string $spu  SPU
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function delete_data($spu, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        $data['status'] = self::STATUS_DELETED;
        $id = $spu;
        if ($lang) {
            $type = $this->tableName . '_' . $lang;
            $es->update_document($this->dbName, $type, $data, $id);
        } else {
            $type = $this->tableName . '_en';
            $es->update_document($this->dbName, $type, $data, $id);
            $type = $this->tableName . '_es';
            $es->update_document($this->dbName, $type, $data, $id);
            $type = $this->tableName . '_ru';
            $es->update_document($this->dbName, $type, $data, $id);
            $type = $this->tableName . '_es';
            $es->update_document($this->dbName, $type, $data, $id);
        }
        return true;
    }

}
