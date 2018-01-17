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
    protected $dbName = 'erui_goods'; //数据库名称

    const STATUS_DELETED = 'DELETED';

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
        if ($qurey_type == ESClient::MATCH || $qurey_type == ESClient::MATCH_PHRASE || $qurey_type == ESClient::TERM) {
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

                $body['query']['bool']['must'][] = [ESClient::TERM => [$field => $status]];
            } else {
                $body['query']['bool']['must'][] = [ESClient::TERM => [$field => $default]];
            }
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => [$field => $default]];
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

    private function getCondition($condition, $lang = 'en', &$country_bn = null, &$is_show_cat = false, &$show_cat_name = null, &$is_brand = false, &$brand_name = null) {
        $body = [];
        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        $this->_getQurey($condition, $body, ESClient::TERM, 'spu');
        $this->_getQureyByArr($condition, $body, ESClient::TERM, 'spus', 'spu');

        if (isset($condition['country_bn']) && $condition['country_bn'] && $condition['country_bn'] !== 'Argentina') {
            $show_cat_model = new ShowCatModel();
            $country_bn = $condition['country_bn'];
            $showcat = $show_cat_model->field('id')->where(['lang' => $lang,
                        'country_bn' => $country_bn,
                        'level_no' => 3,
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])->find();
            if ($showcat) {
                $condition['country_bn'] = $country_bn;
            } else {
                $condition['country_bn'] = 'Argentina';
            }
        } else {
            $country_bn = $condition['country_bn'] = 'Argentina';
        }

        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no1', 'material_cat.cat_no1');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no2', 'material_cat.cat_no2');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no3', 'material_cat.cat_no3');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'created_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'checked_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'updated_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'onshelf_at');
        $this->_getStatus($condition, $body, ESClient::TERM, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']);
        if (isset($condition['recommend_flag']) && $condition['recommend_flag']) {
            $recommend_flag = $condition['recommend_flag'] === 'Y' ? 'Y' : 'N';
            $body['query']['bool']['must'][] = [ESClient::TERM => ['recommend_flag' => $recommend_flag]];
        }
        if (!empty($condition['brand'])) {
            $brandmodel = new BrandModel();
            $brand = $brandmodel->getbrand(['name' => trim($condition['brand'])], $lang);
            $body['query']['bool']['must'][] = [ESClient::TERM => ['brand.name.all' => ['value' => trim($brand), 'boost' => 100]]];
            //$this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'brand', 'brand.name.all');
        }
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'real_name', 'name.all');
        $this->_getQurey($condition, $body, ESClient::TERM, 'source');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'exe_standard', 'exe_standard.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'app_scope', 'app_scope.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'advantages', 'advantages.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'tech_paras', 'tech_paras.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'warranty', 'warranty.all');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'source_detail', 'source_detail.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'keywords', 'keywords.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_id', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_name', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::TERM, 'created_by');
        $this->_getQurey($condition, $body, ESClient::TERM, 'updated_by');
        $this->_getQurey($condition, $body, ESClient::TERM, 'checked_by');
        $this->_getQurey($condition, $body, ESClient::TERM, 'customization_flag', 'customization_flag.all');
        $this->_getQurey($condition, $body, ESClient::TERM, 'sku_count');


        $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        $employee_model = new EmployeeModel();
        if (isset($condition['updated_by_name']) && $condition['updated_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['updated_by_name']);
            foreach ($userids as $updated_by) {
                $updated_by_bool[] = [ESClient::TERM => ['updated_by' => $updated_by]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $updated_by_bool]];
        }
        if (isset($condition['checked_by_name']) && $condition['checked_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['checked_by_name']);
            foreach ($userids as $checked_by) {
                $checked_by_bool[] = [ESClient::TERM => ['checked_by' => $checked_by]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $checked_by_bool]];
        }
        $onshelf_flag = '';
        if (isset($condition['onshelf_flag']) && $condition['onshelf_flag']) {
            $onshelf_flag = trim($condition['onshelf_flag']) == 'N' ? 'N' : 'Y';
            if (trim($condition['onshelf_flag']) === 'A') {
                $onshelf_flag = '';
            }
        } else {
            $onshelf_flag = 'Y';
        }
        if (isset($condition['show_cat_no']) && $condition['show_cat_no'] && $country_bn) {
            $show_cat_no = trim($condition['show_cat_no']);
            $show_cats_nested = [];
            if ($onshelf_flag) {
                $show_cats_nested[] = [ESClient::TERM => ['show_cats_nested.onshelf_flag' => $onshelf_flag]];
            }
            $show_cats_nested[] = [ESClient::TERM => ['show_cats_nested.country_bn' => $country_bn]];
            $show_cats_nested[] = ['bool' => [ESClient::SHOULD => [[ESClient::TERM => ['show_cats_nested.cat_no1' => $show_cat_no]],
                        [ESClient::TERM => ['show_cats_nested.cat_no2' => $show_cat_no]],
                        [ESClient::TERM => ['show_cats_nested.cat_no3' => $show_cat_no]]
            ]]];

            $body['query']['bool']['must'][] = [ESClient::NESTED =>
                [
                    'path' => "show_cats_nested",
                    'query' => ['bool' => [ESClient::MUST => $show_cats_nested]]
            ]];
        } elseif ($country_bn) {
            $show_cats_nested = [];
            if ($onshelf_flag) {
                $show_cats_nested[] = [ESClient::TERM => ['show_cats_nested.onshelf_flag' => $onshelf_flag]];
            }
            $show_cats_nested[] = [ESClient::TERM => ['show_cats_nested.country_bn' => $country_bn]];
            $body['query']['bool']['must'][] = [ESClient::NESTED =>
                [
                    'path' => "show_cats_nested",
                    'query' => ['bool' => [ESClient::MUST => $show_cats_nested]]
            ]];
        }
        if (!empty($condition['min_exw_day']) && intval($condition['min_exw_day']) > 0) {
            $body['query']['bool']['must'][] = [ESClient::RANGE => ['min_exw_day' => ['lte' => intval($condition['min_exw_day']),]]];
        }
        if (!empty($condition['minimumorderouantity']) && intval($condition['minimumorderouantity']) > 0) {
            $body['query']['bool']['must'][] = [ESClient::RANGE => ['minimumorderouantity' => ['lte' => intval($condition['minimumorderouantity']),]]];
        }
        $this->_getQurey($condition, $body, ESClient::MATCH, 'show_name', 'show_name.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'name', 'name.' . $analyzer);
        if (isset($condition['attrs']) && $condition['attrs']) {
            $attrs = trim($condition['attrs']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::WILDCARD => ['attrs.spec_attrs.value.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_goods_attrs.value.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_hs_attrs.value.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.other_attrs.value.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.spec_attrs.name.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_goods_attrs.name.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_hs_attrs.name.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.other_attrs.name.all' => '*' . $attrs . '*']],
            ]]];
        }
        if (isset($condition['spec_attrs']) && $condition['spec_attrs']) {
            $attrs = strtolower(trim($condition['attrs']));
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::TERM => ['attrs.spec_attrs.value.all' => ['value' => $attrs, 'boost' => 99]]],
                        [ESClient::TERM => ['attrs.spec_attrs.name.all' => ['value' => $attrs, 'boost' => 99]]],
            ]]];
        }

        if (isset($condition['spec_value']) && $condition['spec_value'] && isset($condition['spec_name']) && $condition['spec_name']) {
            $spec_value = strtolower(trim($condition['spec_value']));
            $spec_name = strtolower(trim($condition['spec_name']));
            $body['query']['bool']['must'][] = [ESClient::NESTED =>
                [
                    'path' => "spec_attrs",
                    'query' => ['bool' => [ESClient::MUST => [
                                [ESClient::TERM => ['spec_attrs.value.all' => ['value' => $spec_value, 'boost' => 99]]],
                                [ESClient::TERM => ['spec_attrs.name.all' => ['value' => $spec_name, 'boost' => 99]]],
                            ]]]
                ]
            ];
        } elseif (isset($condition['spec_name']) && $condition['spec_name']) {
            $spec_name = trim($condition['spec_name']);
            $body['query']['bool']['must'][] = [ESClient::NESTED =>
                [
                    'path' => "spec_attrs",
                    'query' => ['bool' => [ESClient::MUST => [
                                [ESClient::MATCH_PHRASE => ['spec_attrs.name.' . $analyzer => ['query' => $spec_name, 'boost' => 99]]],
                            ]]]
                ]
            ];
        } elseif (isset($condition['spec_value']) && $condition['spec_value']) {
            $spec_value = trim($condition['spec_value']);
            $body['query']['bool']['must'][] = [ESClient::NESTED =>
                [
                    'path' => "spec_attrs",
                    'query' => ['bool' => [ESClient::MUST => [
                                [ESClient::MATCH_PHRASE => ['spec_attrs.value.' . $analyzer => ['query' => $spec_value, 'boost' => 99]]],
                            ]]]
                ]
            ];
        }


        $this->_getQurey($condition, $body, ESClient::MATCH, 'warranty', 'warranty.' . $analyzer);

        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = trim($condition['keyword']);

            if (empty($show_cat_model)) {
                $show_cat_model = new ShowCatModel();
            }

            $showcats = $show_cat_model->field('cat_no')
                            ->where(['lang' => $lang,
                                'country_bn' => $condition['country_bn'],
                                'name' => $keyword,
                                'status' => 'VALID',
                                'deleted_flag' => 'N'
                            ])->select();
            if (empty($showcats)) {
                $brand_model = new BrandModel();
                $brands = $brand_model->getBrandByBrandName($keyword, $lang);

                if (empty($brands)) {
                    $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                                [ESClient::MATCH => ['name.' . $analyzer => ['query' => $keyword, 'boost' => 99, 'minimum_should_match' => '50%', 'operator' => 'or']]],
                                [ESClient::MATCH => ['show_name.' . $analyzer => ['query' => $keyword, 'boost' => 99, 'minimum_should_match' => '50%', 'operator' => 'or']]],
                                //  [ESClient::MATCH_PHRASE => ['brand.name.' . $analyzer => ['query' => $keyword, 'boost' => 39]]],
                                [ESClient::MATCH => ['tech_paras.' . $analyzer => ['query' => $keyword, 'boost' => 2, 'operator' => 'and']]],
                                [ESClient::MATCH => ['exe_standard.' . $analyzer => ['query' => $keyword, 'boost' => 1, 'operator' => 'and']]],
                                [ESClient::TERM => ['spu' => ['value' => $keyword, 'boost' => 100]]],
                    ]]];
                } else {
                    $brand_name = $keyword;
                    $is_brand = true;
                    $this->_getEsBrand($brands, $keyword, $body, $lang, $brand_name);
                }
            } else {
                $show_cat_name = $keyword;
                $is_show_cat = true;
                $this->_getEsShowCats($showcats, $keyword, $body);
            }
        }
        return $body;
    }

    /* 获取品牌组合
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @param mix  $_source //要搜索的字段
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getEsBrand($brands, $keyword, &$body, $lang = 'en', &$brand_name = null) {
        $brand_bool = [];
        foreach ($brands as $brand) {
            $brand_langs = json_decode($brand['brand'], true);


            foreach ($brand_langs as $brand_lang) {
                if ($brand_lang['lang'] === $lang && $brand_lang['name']) {
                    $brand_name = $brand_lang['name'];
                    $brand_bool[] = [ESClient::TERM => ['brand.name.all' => ['value' => $brand_lang['name'], 'boost' => 99]]];
                }
            }
        }
        if ($brand_bool) {
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $brand_bool]];
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['brand.name.all' => ['value' => $keyword, 'boost' => 99]]];
        }
    }

    /* 获取分类组合
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @param mix  $_source //要搜索的字段
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getEsShowCats($showcats, $keyword, &$body) {
        $show_cat_bool = [];
        foreach ($showcats as $showcat) {
            $show_cat_bool[] = [ESClient::TERM => ['show_cats.cat_no3' => ['value' => $showcat['cat_no'], 'boost' => 99]]];
            $show_cat_bool[] = [ESClient::TERM => ['show_cats.cat_no2' => ['value' => $showcat['cat_no'], 'boost' => 95]]];
            $show_cat_bool[] = [ESClient::TERM => ['show_cats.cat_no1' => ['value' => $showcat['cat_no'], 'boost' => 90]]];
        }
        if ($show_cat_bool) {
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $show_cat_bool]];
        } else {
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::TERM => ['show_cats.cat_name3' => ['value' => $keyword, 'boost' => 99]]],
                        [ESClient::TERM => ['show_cats.cat_name2' => ['value' => $keyword, 'boost' => 95]]],
                        [ESClient::TERM => ['show_cats.cat_name1' => ['value' => $keyword, 'boost' => 90]]],
            ]]];
        }
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

    public function getProducts($condition, $lang = 'en') {

        try {
            if ($lang == 'zh') {
                $analyzer = 'ik';
            } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
                $analyzer = $lang;
            } else {
                $analyzer = 'ik';
            }


            $body = $this->getCondition($condition, $lang);

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
            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }
            if (isset($condition['keyword']) && $condition['keyword']) {
                $es->setbody($body)->setsort('_score')
                        ->setsort('created_at', 'DESC')
                        ->setsort('id', 'DESC');
                $es->setpreference('_primary_first');
            } else {
                $es->setbody($body)->setsort('created_at', 'DESC')->setsort('id', 'DESC');
            }


            $es->setaggs('show_cats.cat_no3', 'show_cat_no3', 'terms', 30);
            $es->sethighlight(['show_name.' . $analyzer => new stdClass(), 'name.' . $analyzer => new stdClass()]);

            $data = [$es->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];
            $es->body = $body = $es = null;
            unset($es, $body);
            return $data;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
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

    public function getNewProducts($condition, $lang = 'en', &$country_bn = null, &$is_show_cat = false, &$show_cat_name = null, &$is_brand = false, &$brand_name = null) {

        try {
            if ($lang == 'zh') {
                $analyzer = 'ik';
            } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
                $analyzer = $lang;
            } else {
                $analyzer = 'ik';
            }

            $body = $this->getCondition($condition, $lang, $country_bn, $is_show_cat, $show_cat_name, $is_brand, $brand_name);

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
            $ret_count = $es->setbody($body)->count($this->dbName, $this->tableName . '_' . $lang, '');
            if (isset($ret_count['count']) && $ret_count['count'] <= $from) {

                $from = $ret_count['count'] % $pagesize === 0 ? $ret_count['count'] - $pagesize : $ret_count['count'] - $ret_count['count'] % $pagesize;
                $current_no = intval($ret_count['count'] / $pagesize);
            }

            unset($condition['source']);
            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }
            $es->setbody($body);
            if (isset($condition['keyword']) && $condition['keyword']) {
                $es->setsort('_score', 'desc')->setsort('created_at', 'desc');
            }
            $es->setpreference('_primary_first');
            $es->setfields(['spu', 'show_name', 'name', 'keywords', 'tech_paras', 'exe_standard', 'sku_count',
                'brand', 'customization_flag', 'warranty', 'attachs', 'minimumorderouantity', 'min_pack_unit']);
            $es->sethighlight(['show_name.' . $analyzer => new stdClass(), 'name.' . $analyzer => new stdClass()]);
            $data = [$es->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];
            $es->body = $body = $es = null;
            unset($es, $body);
            return $data;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    public function getCatList($condition, $lang) {
        $show_cat_model = new ShowCatModel();
        if (isset($condition['show_cat_no']) && $condition['show_cat_no']) {
            $show_cat = $show_cat_model->field('level_no')->where(['cat_no' => $condition['show_cat_no'], 'lang' => $lang])->find();
            $show_cat['level_no'] > 1 ? $condition['show_cat_no'] = null : '';
        }
        $country_bn = null;
        $body = $this->getCondition($condition, $lang, $country_bn);
        $es = new ESClient();
        $es->setbody($body);
        $es->setfields(['spu']);
        $es->body['aggs'] = ['show_cats_nested' => [
                'nested' => [
                    'path' => 'show_cats_nested'
                ],
                'aggs' => ['country_bn' => [
                        'terms' => ['field' => 'show_cats_nested.country_bn', 'size' => 10, 'order' => ['_count' => 'desc'],],
                        'aggs' => ['cat_no2' =>
                            ['terms' => ['field' => 'show_cats_nested.cat_no2', 'size' => 10, 'order' => ['_count' => 'desc']],
                                'aggs' => ['cat_no3' => [
                                        'terms' => ['field' => 'show_cats_nested.cat_no3', 'size' => 10, 'order' => ['_count' => 'desc']]
                                    ]]]
        ]]]]];
        $es->body['size'] = 0;
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 0);

        $show_cat_nos = [];
        $show_cats = [];
        if (isset($ret['aggregations']['show_cats_nested']['country_bn']['buckets'])) {
            foreach ($ret['aggregations']['show_cats_nested']['country_bn']['buckets'] as $countrys) {

                if (isset($countrys['cat_no2']['buckets'])) {
                    foreach ($countrys['cat_no2']['buckets'] as $cats) {
                        $show_cat_nos[] = $cats['key'];
                        $show_cats[$cats['key']] = [
                            'name' => '',
                            'cat_no' => $cats['key'],
                            'count' => $cats['doc_count'],
                            'childs' => []
                        ];
                        if (isset($cats['cat_no3']['buckets'])) {
                            $child_cats = [];
                            foreach ($cats['cat_no3']['buckets'] as $cat) {
                                $show_cat_nos[] = $cat['key'];
                                $child_cats[$cat['key']] = [
                                    'name' => '',
                                    'cat_no' => $cat['key'],
                                    'count' => $cat['doc_count'],
                                ];
                            }

                            $show_cats[$cats['key']]['childs'] = $child_cats;
                        }
                    }
                }
            }
        }

        if ($show_cat_nos) {
            $catno_key = 'ShowCats_' . md5(http_build_query($show_cat_nos)) . '_' . $country_bn . '_' . $lang;
            $newshowcats = json_decode(redisGet($catno_key), true);
            $newshow_cats = [];
            if (!$newshowcats) {
                $showcatmodel = new ShowCatModel();
                $showcats = $showcatmodel->getshowcatsByshowcatnos($show_cat_nos, $lang, false, $country_bn);
                foreach ($showcats as $showcat) {
                    $newshow_cats[$showcat['cat_no']] = $showcat['name'];
                }
            } else {
                return $newshowcats;
            }
            foreach ($show_cats as $key => $show_cat) {

                if (isset($newshow_cats[$show_cat['cat_no']])) {
                    $show_cat['name'] = $newshow_cats[$show_cat['cat_no']];
                } else {
                    continue;
                }

                foreach ($show_cat['childs'] as $K => $child_showcat) {
                    if (isset($newshow_cats[$child_showcat['cat_no']])) {
                        $child_showcat['name'] = $newshow_cats[$child_showcat['cat_no']];
                        $show_cat['childs'][$K] = $child_showcat;
                    } else {
                        unset($show_cat['childs'][$K]);
                    }
                }
//                rsort($show_cat['childs']);
                $newshowcats[] = $show_cat;
            }

            redisSet($catno_key, json_encode($newshowcats), 3600);
            return $newshowcats;
        } else {
            return [];
        }
    }

    public function getBrandsList($condition, $lang = 'en') {
        $brand = $condition['brand'];
        unset($condition['brand']);
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);
        $es->setfields(['spu']);
        $brand_terms = [
            'field' => 'brand.name.all',
            'size' => 10,
            'order' => ['_count' => 'desc']
        ];

        $es->body['aggs']['brand_name'] = [
            'terms' => $brand_terms
        ];



        $es->body['size'] = 0;
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 0);

        $brand_names = [];
        if ($brand) {
            $is_include = false;
        } else {
            $is_include = true;
        }
        if (isset($ret['aggregations']['brand_name']['buckets'])) {
            foreach ($ret['aggregations']['brand_name']['buckets'] as $brand_name) {
                if ($brand_name['key']) {
                    $brand_names[] = ['brand_name' => $brand_name['key'], 'count' => $brand_name['doc_count']];
                }
                if (!$is_include && strtolower($brand_name['key']) == strtolower($brand)) {
                    $is_include = true;
                }
            }
        }
        if ($is_include === false) {

            $brand_names[count($brand_names) - 1] = ['brand_name' => strtoupper($brand), 'count' => 0];
        }
        return $brand_names;
    }

    public function getSpecsList($condition, $lang = 'en') {

        $specname = $condition['spec_name'];
        $specvalue = $condition['spec_value'];
        unset($condition['spec_name'], $condition['spec_value']);
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);
        $es->setfields(['spu']);
        $spec_name_terms = ['field' => 'spec_attrs.name.all',
            'size' => 20,
            'order' => ['_count' => 'desc']];
        $spec_value_terms = ['field' => 'spec_attrs.value.all',
            'size' => 10,
            'order' => ['_count' => 'desc']];

        $es->body['aggs']['spec_attrs'] = ['nested' => ['path' => 'spec_attrs'],
            'aggs' => ['spec_name' => ['terms' => $spec_name_terms,
                    'aggs' => ['spec_value' => [
                            'terms' => $spec_value_terms
        ]]]]];
        if ($specname) {
            $is_spec_name_include = false;
        } else {
            $is_spec_name_include = true;
        }
        if ($specvalue) {
            $is_spec_value_include = false;
        } else {
            $is_spec_value_include = true;
        }


        $es->body['size'] = 0;
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 0);

        $spec_names = [];
        if (isset($ret['aggregations']['spec_attrs']['spec_name']['buckets'])) {
            foreach ($ret['aggregations']['spec_attrs']['spec_name']['buckets'] as $spec_name) {
                $spec_values = [];
                if ($spec_name['key']) {

                    foreach ($spec_name['spec_value']['buckets'] as $spec_value) {
                        if ($spec_value['key']) {
                            $spec_values[] = ['spec_value' => $spec_value['key'], 'count' => $spec_value['doc_count']];
                        }
                        if (!$is_spec_value_include && strtolower($spec_name['key']) == strtolower($specname) && strtolower($spec_value['key']) == strtolower($specvalue)) {
                            $is_spec_value_include = true;
                        }
                    }
                    if ($is_spec_value_include && strtolower($spec_name['key']) == strtolower($specname)) {

                        $is_spec_name_include = true;
                    } elseif (strtolower($spec_name['key']) == strtolower($specname)) {
                        $is_spec_name_include = true;
                        $spec_values[count($spec_values) - 1] = ['spec_value' => $specvalue, 'count' => 0];
                    }
                    $spec_names[] = ['spec_name' => $spec_name['key'], 'count' => $spec_name['doc_count'],
                        'spec_values' => $spec_values];
                }
            }
        }
        if ($is_spec_name_include === false) {

            $spec_names[count($spec_names) - 1] = ['spec_name' => strtolower($specname),
                'count' => 0,
                'spec_values' => [['spec_value' => strtolower($specvalue), 'count' => 0]]];
        }

        return $spec_names;
    }

    public function getSkuCountByCondition($condition, $lang) {
        $body = $this->getCondition($condition);
        $redis_key = 'spu_' . md5(json_encode($body)) . '_' . $lang;
        if (redisExist($redis_key)) {
            return redisGet($redis_key);
        }
        $es = new ESClient();
        $es->setbody($body);
        $es->setfields(['sku_count']);
        /*         * ******************sku_count 报错 可以注释这段************************** */
        $es->setaggs('sku_count', 'sku_count', 'sum');
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);
        $sku_count = 0;
        if (isset($ret['aggregations']['sku_count']['value'])) {

            $sku_count = $ret['aggregations']['sku_count']['value'];
        }

        $ret1 = $ret = $es = null;
        unset($ret1, $ret, $es);
        redisSet($redis_key, $sku_count, 180);
        return $sku_count;
        /*         * **************************sku_count 报错 可以恢复这段************************** */
        /* $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1000);
          $sku_count = 0;
          if (isset($ret['hits']['hits'])) {
          foreach ($ret['hits']['hits'] as $item) {
          $sku_count += $item['_source']['sku_count'];
          }
          }
          if (isset($ret['hits']['total']) && $ret['hits']['total'] > 1000) {
          for ($i = 1000; $i <= $ret['hits']['total']; $i += 1000) {
          $ret1 = $es->search($this->dbName, $this->tableName . '_' . $lang, $i, 1000);
          if (isset($ret1['hits']['hits'])) {
          foreach ($ret1['hits']['hits'] as $item) {
          $sku_count += $item['_source']['sku_count'];
          }
          }
          }
          }
          $ret1 = $ret = $es = null;
          unset($ret1, $ret, $es);
          redisSet($redis_key, $sku_count, 3600);
          return $sku_count; */
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

    public function UpdateViewCount($spu, $lang) {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }

        $type = 'product_' . $lang;
        $data = [];
        $pinfo = $this->field('view_count')->where(['spu' => $spu, 'lang' => $lang, 'deleted_flag' => 'N'])
                ->find();
        $view_count = intval($pinfo['view_count']) ? intval($pinfo['view_count']) : 1;
        $data['view_count'] = strval($view_count);
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $spu);

        $this->_delcache();
        $es->refresh($this->dbName);
        return true;
    }

}
