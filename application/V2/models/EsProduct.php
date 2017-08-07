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
            if (!$field) {
                $field = $name;
            }
            if (isset($condition[$name . '_start']) && isset($condition[$name . '_end']) && $condition[$name . '_end'] && $condition[$name . '_start']) {
                $created_at_start = $condition[$name . '_start'];
                $created_at_end = $condition[$name . '_end'];
                $body['query']['bool']['must'][] = [ESClient::RANGE => [$name => ['gte' => $created_at_start, 'lte' => $created_at_end,]]];
            } elseif (isset($condition[$name . '_start']) && $condition[$name . '_start']) {
                $created_at_start = $condition[$name . '_start'];

                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['gte' => $created_at_start,]]];
            } elseif (isset($condition[$name . '_end']) && $condition[$name . '_end']) {
                $created_at_end = $condition[$name . '_end'];
                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['lte' => $created_at_end,]]];
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



        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no1', 'material_cat.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no2', 'material_cat.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no3', 'material_cat.all');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'created_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'checked_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'updated_at');

        $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']);
        // $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'shelves_status', 'shelves_status', ['VALID', 'INVALID']);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'brand.ik');
        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'real_name', 'name.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'source');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'exe_standard', 'exe_standard.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'app_scope', 'app_scope.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'advantages', 'advantages.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'tech_paras', 'tech_paras.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'source_detail', 'source_detail.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'keywords', 'keywords.ik');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_id', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_name', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'created_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'updated_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'checked_by');
        $employee_model = new EmployeeModel();
        if (isset($condition['created_by_name']) && $condition['created_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['created_by_name']);
            foreach ($userids as $created_by) {
                $created_by_bool[] = [ESClient::MATCH_PHRASE => ['created_by' => $created_by]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $created_by_bool]];
        }
        if (isset($condition['updated_by_name']) && $condition['updated_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['updated_by_name']);
            foreach ($userids as $updated_by) {
                $updated_by_bool[] = [ESClient::MATCH_PHRASE => ['updated_by' => $updated_by]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $updated_by_bool]];
        }
        if (isset($condition['checked_by_name']) && $condition['checked_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['checked_by_name']);
            foreach ($userids as $checked_by) {
                $checked_by_bool[] = [ESClient::MATCH_PHRASE => ['checked_by' => $checked_by]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $checked_by_bool]];
        }
        if (isset($condition['onshelf_flag']) && $condition['onshelf_flag']) {
            $onshelf_flag = $condition['onshelf_flag'] == 'N' ?: 'Y';
            $body['query']['bool']['must'][] = ['bool' => [ESClient::WILDCARD => ['show_cats.all' => '"onshelf_flag":"' . $onshelf_flag . '"']]];
        }

        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'onshelf_flag', 'show_cats.all');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'show_name', 'show_name.ik');
        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'name', 'name.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'attrs', 'attrs.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'specs', 'specs.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'warranty', 'warranty.ik');
        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'keyword', ['show_name.ik', 'attrs.ik', 'specs.ik', 'spu', 'source.ik', 'brand.ik']);
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
            $body = $this->getCondition($condition);

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
            if ($body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }
            $es->setbody($body)->setsort('sort_order', 'desc')->setsort('_id', 'desc');

            if (isset($condition['sku_count']) && $condition['sku_count'] == 'Y') {
                $es->setaggs('sku_count', 'sku_count', 'sum');
            } else {
                $es->setaggs('material_cat_no', 'material_cat_no');
            }
            $data = [$es->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];
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
                            ->setaggs('sku_count', 'sku_count', 'sum')
                            ->search($this->dbName, $this->tableName . '_' . $lang);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
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
                $spu = $item['spu'];
                $mcat_nos[] = $item['material_cat_no'];
                $attr_spus[] = $spu;
                $brands[$spu] = $item['brand'];
                $material_cat_nos[$spu] = $item['material_cat_no'];
            }

            $mcat_nos = array_unique($mcat_nos);

            $material_cat_model = new MaterialCatModel();
            $mcats = $material_cat_model->getmaterial_cats($mcat_nos, $lang);


            $ret = [];
            foreach ($products as $item) {

                if (isset($mcats[$item['material_cat_no']])) {
                    $body['material_cat'] = json_encode($mcats[$item['material_cat_no']], JSON_UNESCAPED_UNICODE);
                } else {
                    $body['material_cat'] = json_encode(new stdClass(), JSON_UNESCAPED_UNICODE);
                }
                $spu = $item['spu'];
                $body['brand'] = $brands[$spu];
                $body['material_cat_no'] = $material_cat_nos[$spu];
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

                $products = $this->where(['lang' => $lang, 'id' => ['gt', $max_id]])->limit(0, 100)->order('id asc')->select();

                $spus = $mcat_nos = [];
                if ($products) {
                    foreach ($products as $item) {
                        $mcat_nos[] = $item['material_cat_no'];
                        $spus[] = $item['spu'];
                    }
                    $spus = array_unique($spus);
                    $mcat_nos = array_unique($mcat_nos);

                    $material_cat_model = new MaterialCatModel();
                    $mcats = $material_cat_model->getmaterial_cats($mcat_nos, $lang); //获取物料分类                  


                    $show_cat_product_model = new ShowCatProductModel();
                    $scats = $show_cat_product_model->getshow_catsbyspus($spus, $lang); //根据spus获取展示分类编码

                    $product_attr_model = new ProductAttrModel();
                    $product_attrs = $product_attr_model->getproduct_attrbyspus($spus, $lang); //根据spus获取产品属性

                    $product_attach_model = new ProductAttachModel();
                    $attachs = $product_attach_model->getproduct_attachsbyspus($spus, $lang); //根据SPUS获取产品附件
                    $es = new ESClient();

                    foreach ($products as $key => $item) {
                        $spu = $id = $item['spu'];
                        $this->_findnulltoempty($item);
                        $body = $item;

                        if ($body['source'] == 'ERUI') {
                            $body['sort_order'] = 100;
                        } else {
                            $body['sort_order'] = 1;
                        }

                        if (isset($attachs[$spu])) {
                            $body['attachs'] = json_encode($attachs[$spu], 256);
                        } else {
                            $body['attachs'] = '[]';
                        }
                        $show_cat = [];
                        if (isset($scats_no_spu[$spu]) && isset($scats[$scats_no_spu[$spu]])) {
                            $show_cat[$scats_no_spu[$spu]] = $scats[$scats_no_spu[$spu]];
                        }
                        $material_cat_no = $item['material_cat_no'];



                        if (isset($mcats[$material_cat_no])) {
                            $body['material_cat'] = json_encode($mcats[$material_cat_no], JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['material_cat'] = json_encode(new \stdClass(), JSON_UNESCAPED_UNICODE);
                        }
                        $body['show_cats'] = $this->_getValue($scats, $spu, [], 'json');

                        if (isset($product_attrs[$spu])) {
                            $body['attrs'] = json_encode($product_attrs[$spu], JSON_UNESCAPED_UNICODE);
                            if ($product_attrs[$item['spu']][0]['spec_attrs']) {
                                $body['specs'] = $product_attrs[$spu][0]['spec_attrs'];
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
                        // print_r($flag);
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

    /*
     * 批量更新产品数据到ES
     * @author zyg 2017-07-31
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function updateproducts($lang = 'en', $time = '1970-01-01 8:00:00') {
        try {
            if ($time) {
                $where = [
                    'lang' => $lang,
                    '_complex' => [
                        '_logic' => 'or',
                        'created_at' => ['egt' => $time],
                        'updated_at' => ['egt' => $time],
                        'checked_at' => ['egt' => $time],
                    ],
                ];
            } else {
                $where = [
                    'lang' => $lang,
                ];
            }
            $count = $this->where($where)->count('id');
            $max_id = 0;
            echo '共有', $count, '条记录需要导入!', PHP_EOL;
            $k = 1;
            for ($i = 0; $i < $count; $i += 100) {
                if ($i > $count) {
                    $i = $count;
                }
                   $where['id'] = ['gt',$max_id];
                $products = $this->where($where)->limit(0, 100)->order('id asc')->select();
                $spus = $mcat_nos = [];
                if ($products) {
                    foreach ($products as $item) {
                        $mcat_nos[] = $item['material_cat_no'];
                        $spus[] = $item['spu'];
                    }
                    $spus = array_unique($spus);
                    $mcat_nos = array_unique($mcat_nos);

                    $material_cat_model = new MaterialCatModel();
                    $mcats = $material_cat_model->getmaterial_cats($mcat_nos, $lang); //获取物料分类                  


                    $show_cat_product_model = new ShowCatProductModel();
                    $scats = $show_cat_product_model->getshow_catsbyspus($spus, $lang); //根据spus获取展示分类编码

                    $product_attr_model = new ProductAttrModel();
                    $product_attrs = $product_attr_model->getproduct_attrbyspus($spus, $lang); //根据spus获取产品属性

                    $product_attach_model = new ProductAttachModel();
                    $attachs = $product_attach_model->getproduct_attachsbyspus($spus, $lang); //根据SPUS获取产品附件
                    $es = new ESClient();

                    foreach ($products as $key => $item) {
                        $spu = $id = $item['spu'];
                        $this->_findnulltoempty($item);
                        $body = $item;

                        if ($body['source'] == 'ERUI') {
                            $body['sort_order'] = 100;
                        } else {
                            $body['sort_order'] = 1;
                        }

                        if (isset($attachs[$spu])) {
                            $body['attachs'] = json_encode($attachs[$spu], 256);
                        } else {
                            $body['attachs'] = '[]';
                        }
                        $show_cat = [];
                        if (isset($scats_no_spu[$spu]) && isset($scats[$scats_no_spu[$spu]])) {
                            $show_cat[$scats_no_spu[$spu]] = $scats[$scats_no_spu[$spu]];
                        }
                        $material_cat_no = $item['material_cat_no'];



                        if (isset($mcats[$material_cat_no])) {
                            $body['material_cat'] = json_encode($mcats[$material_cat_no], JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['material_cat'] = json_encode(new \stdClass(), JSON_UNESCAPED_UNICODE);
                        }

                        $body['show_cats'] = $this->_getValue($scats, $spu, [], 'json');

                        if (isset($product_attrs[$spu])) {
                            $body['attrs'] = json_encode($product_attrs[$spu], JSON_UNESCAPED_UNICODE);
                            if ($product_attrs[$item['spu']][0]['spec_attrs']) {
                                $body['specs'] = $product_attrs[$spu][0]['spec_attrs'];
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
                return $return;
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
                return $return;
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
            $data['material_cat'] = json_encode($mcatmodel->getinfo($material_cat_no, $lang), 256); //通过物料分类编码获取物料分类详情
        } else {
            $data['material_cat_no'] = '';
            $data['material_cat'] = json_encode(new \stdClass());
        }
        if (isset($condition['spu'])) {
            $spu = $data['spu'] = $condition['spu'];

            $product_attr_model = new ProductAttrModel();
            $product_attrs = $product_attr_model->getproduct_attrbyspus([$spu], $lang); //通过SPU获取产品属性

            $product_attach_model = new ProductAttachModel();
            $product_attachs = $product_attach_model->getproduct_attachsbyspus([$spu], $lang); //通过SPU获取产品附件
            $data['attrs'] = $this->_getValue($product_attrs, $spu, [], 'json');

            if ($data['attrs'][0]['spec_attrs']) {
                $data['specs'] = $data['attrs'][0]['spec_attrs'];
            } else {
                $data['specs'] = json_encode([], 256);
            }
            $data['attachs'] = $this->_getValue($product_attachs, $spu, [], 'json');
        } else {
            $data['spu'] = '';
            $data['attrs'] = json_encode([], 256);
            $data['specs'] = json_encode([], 256);
            $data['attachs'] = json_encode([], 256);
        }
        $show_cat_product_model = new ShowCatProductModel();
        $scats = $show_cat_product_model->getShowCatnosBySpu([$spu], $lang); //通过展示分类获取展示分类信息
        $data['show_cats'] = $this->_getValue($scats, $spu, [], 'json');
        $data['qrcode'] = $this->_getValue($condition, 'qrcode');
        $data['name'] = $this->_getValue($condition, 'name');
        $data['show_name'] = $this->_getValue($condition, 'show_name');
        $data['brand'] = $this->_getValue($condition, 'brand');
        $data['keywords'] = $this->_getValue($condition, 'keywords');
        $data['exe_standard'] = $this->_getValue($condition, 'exe_standard');
        $data['tech_paras'] = $this->_getValue($condition, 'tech_paras');
        $data['advantages'] = $this->_getValue($condition, 'advantages');
        $data['description'] = $this->_getValue($condition, 'description');
        $data['profile'] = $this->_getValue($condition, 'profile');
        $data['principle'] = $this->_getValue($condition, 'principle');
        $data['app_scope'] = $this->_getValue($condition, 'app_scope');
        $data['properties'] = $this->_getValue($condition, 'properties');
        $data['warranty'] = $this->_getValue($condition, 'warranty');
        $data['customizability'] = $this->_getValue($condition, 'customizability');
        $data['availability'] = $this->_getValue($condition, 'availability');
        $data['availability_ratings'] = $this->_getValue($condition, 'availability_ratings');
        $data['resp_time'] = $this->_getValue($condition, 'resp_time');
        $data['resp_rate'] = $this->_getValue($condition, 'resp_rate');
        $data['delivery_cycle'] = $this->_getValue($condition, 'delivery_cycle');
        $data['target_market'] = $this->_getValue($condition, 'target_market');
        $data['supply_ability'] = $this->_getValue($condition, 'supply_ability');
        $data['source'] = $this->_getValue($condition, 'source');
        $data['source_detail'] = $this->_getValue($condition, 'source_detail');
        $data['sku_count'] = $this->_getValue($condition, 'sku_count');
        $data['recommend_flag'] = $this->_getValue($condition, 'recommend_flag', 'N', 'bool');
        $data['status'] = $this->_getValue($condition, 'status', 'CHECKING', 'in_array');
        $data['created_by'] = $this->_getValue($condition, 'created_by');
        $data['created_at'] = $this->_getValue($condition, 'created_at');
        $data['updated_by'] = $this->_getValue($condition, 'updated_by');
        $data['updated_at'] = $this->_getValue($condition, 'updated_at');
        $data['checked_by'] = $this->_getValue($condition, 'checked_by');
        $data['checked_at'] = $this->_getValue($condition, 'checked_at');

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

    public function create_data($spu, $lang = 'en') {
        try {
            $es = new ESClient();
            if ($spu) {
                $product_model = new ProductModel();
                $data = $product_model->where(['sku' => $sku, 'lang' => $lang])->find();
            }

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
        $scats = $showcatproduct_model->getShowCatnosBySpu($spu, $lang);
        $show_cats = $this->_getValue($scats, $spu, [], 'json');
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
        $esgoods = new EsGoodsModel();
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
        $data['material_cat'] = json_encode($mcatmodel->getinfo($new_cat_no, $lang), 256);
        $data['material_cat_no'] = $new_cat_no;
        if ($spu) {
            $id = $spu;
            $es->update_document($this->dbName, $type, $data, $id);
        } else {
            $es_product_data = [
                "doc" => [
                    "material_cat" => $data['material_cat'],
                    'material_cat_no' => $new_cat_no,
                ],
                "query" => [
                    ESClient::WILDCARD => [
                        "material_cat" => '*' . $material_cat_no . '*'
                    ]
                ]
            ];
            $es->UpdateByQuery($this->dbName, 'product_' . $lang, $es_product_data);
        }
        if ($spu) {
            $esgoodsdata = [
                "doc" => [
                    "material_cat" => $data['material_cat'],
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
                    "material_cat" => $data['material_cat'],
                ],
                "query" => [
                    ESClient::WILDCARD => [
                        "material_cat" => '*' . $material_cat_no . '*'
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
        $product_attr_model = new ProductAttrModel();
        $product_attrs = $product_attr_model->getproduct_attrbyspus([$spu], $lang);

        $id = $spu;
        $data['attrs'] = $this->_getValue($product_attrs, $spu, [], 'json');
        if ($data['attrs'][0]['spec_attrs']) {
            $data['specs'] = $data['attrs'][0]['spec_attrs'];
        } else {
            $data['specs'] = json_encode([]);
        }
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);
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
        $product_attach_model = new ProductAttachModel();
        $attachs = $product_attach_model->getproduct_attachsbyspus([$spu], $lang);
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
