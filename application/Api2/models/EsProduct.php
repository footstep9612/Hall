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

    private function getCondition($condition, $lang = 'en') {
        $body = [];
        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'spu');
        $this->_getQureyByArr($condition, $body, ESClient::MATCH_PHRASE, 'spus', 'spu');
        if (isset($condition['show_cat_no']) && $condition['show_cat_no']) {
            $show_cat_no = trim($condition['show_cat_no']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::TERM => ['show_cats.cat_no1' => $show_cat_no]],
                        [ESClient::TERM => ['show_cats.cat_no2' => $show_cat_no]],
                        [ESClient::TERM => ['show_cats.cat_no3' => $show_cat_no]],
            ]]];
        }
        if (isset($condition['country_bn']) && $condition['country_bn'] && $condition['country_bn'] !== 'China') {
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
                $condition['country_bn'] = 'China';
            }
        } else {
            $condition['country_bn'] = 'China';
        }

        // $this->_getQurey($condition, $body, ESClient::TERM, 'market_area_bn', 'show_cats.market_area_bn');
        $this->_getQurey($condition, $body, ESClient::TERM, 'country_bn', 'show_cats.country_bn');
        $this->_getQurey($condition, $body, ESClient::TERM, 'scat_no1', 'show_cats.cat_no1');
        $this->_getQurey($condition, $body, ESClient::TERM, 'scat_no2', 'show_cats.cat_no2');
        $this->_getQurey($condition, $body, ESClient::TERM, 'scat_no3', 'show_cats.cat_no3');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no1', 'material_cat.cat_no1');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no2', 'material_cat.cat_no2');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no3', 'material_cat.cat_no3');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'created_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'checked_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'updated_at');
        $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']);
        if (isset($condition['recommend_flag']) && $condition['recommend_flag']) {
            $recommend_flag = $condition['recommend_flag'] === 'Y' ? 'Y' : 'N';
            $body['query']['bool']['must'][] = [ESClient::TERM => ['recommend_flag' => $recommend_flag]];
        }

// $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'shelves_status', 'shelves_status', ['VALID', 'INVALID']);
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'brand', 'brand.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'real_name', 'name.all');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'source');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'exe_standard', 'exe_standard.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'app_scope', 'app_scope.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'advantages', 'advantages.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'tech_paras', 'tech_paras.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'source_detail', 'source_detail.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'keywords', 'keywords.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_id', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_name', 'suppliers.all');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'created_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'updated_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'checked_by');

        $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        $employee_model = new EmployeeModel();
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
            $onshelf_flag = trim($condition['onshelf_flag']) == 'N' ? 'N' : 'Y';
            if (trim($condition['onshelf_flag']) === 'A') {

            } elseif ($onshelf_flag === 'N') {
                $body['query']['bool']['must'][] = [ESClient::TERM => ['show_cats.onshelf_flag' => 'N']];
            } else {
                $body['query']['bool']['must'][] = [ESClient::TERM => ['show_cats.onshelf_flag' => 'Y']];
            }
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['show_cats.onshelf_flag' => 'Y']];
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
            $attrs = trim($condition['attrs']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::WILDCARD => ['attrs.spec_attrs.value.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.spec_attrs.name.all' => '*' . $attrs . '*']],
            ]]];
        }
        $this->_getQurey($condition, $body, ESClient::MATCH, 'warranty', 'warranty.' . $analyzer);

        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = $condition['keyword'];
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::MATCH => ['name.' . $analyzer => ['query' => $keyword, 'boost' => 7]]],
                        [ESClient::MATCH => ['show_name.' . $analyzer => ['query' => $keyword, 'boost' => 7]]],
                        [ESClient::MATCH => ['keywords.' . $analyzer => ['query' => $keyword, 'boost' => 2]]],
                        [ESClient::WILDCARD => ['brand.name.all' => ['value' => '*' . $keyword . '*', 'boost' => 1]]],
                        [ESClient::WILDCARD => ['show_name.all' => ['value' => '*' . $keyword . '*', 'boost' => 9]]],
                        [ESClient::WILDCARD => ['name.all' => ['value' => '*' . $keyword . '*', 'boost' => 9]]],
                        [ESClient::WILDCARD => ['attr.spec_attrs.name.all' => ['value' => '*' . $keyword . '*', 'boost' => 1]]],
                        [ESClient::WILDCARD => ['attr.spec_attrs.value.all' => ['value' => '*' . $keyword . '*', 'boost' => 1]]],
                        [ESClient::TERM => ['spu' => $keyword]],
            ]]];
        }
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

    public function getProducts($condition, $_source = null, $lang = 'en') {

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
                $es->setbody($body)->setsort('_score')->setsort('id', 'DESC');
            } else {
                $es->setbody($body)->setsort('id', 'DESC');
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

    public function getNewProducts($condition, $_source = null, $lang = 'en') {

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
            $es->setbody($body);
            if (isset($condition['keyword']) && $condition['keyword']) {
                $es->setsort('_score');
            }
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
        unset($condition['show_cat_no']);
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);
        $es->setfields(['spu']);
        $es->body['aggs']['cat_no2'] = [
            'terms' => [
                'field' => 'show_cats.cat_no2',
                'size' => 10,
                'order' => ['_count' => 'desc']
            ],
            'aggs' => ['cat_no3' => [
                    'terms' => [
                        'field' => 'show_cats.cat_no3',
                        'size' => 10,
                        'order' => ['_count' => 'desc']
                    ]
                ]
            ]
        ];
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);
        $show_cat_nos = [];
        $show_cats = [];
        if (isset($ret['aggregations']['cat_no2']['buckets'])) {
            foreach ($ret['aggregations']['cat_no2']['buckets'] as $cats) {
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
        if ($show_cat_nos) {
            $catno_key = 'ShowCats_' . md5(http_build_query($show_cat_nos)) . '_' . $lang;
            $newshowcats = json_decode(redisGet($catno_key), true);
            $newshow_cats = [];
            if (!$newshowcats) {
                $showcatmodel = new ShowCatModel();
                $showcats = $showcatmodel->getshowcatsByshowcatnos($show_cat_nos, $lang, false);
                foreach ($showcats as $showcat) {
                    $newshow_cats[$showcat['cat_no']] = $showcat['name'];
                }
            } else {
                return $newshowcats;
            }
            foreach ($show_cats as $key => $show_cat) {

                if (isset($newshow_cats[$show_cat['cat_no']])) {
                    $show_cat['name'] = $newshow_cats[$show_cat['cat_no']];
                }
                foreach ($show_cat['childs'] as $key => $child_showcat) {
                    if (isset($newshow_cats[$child_showcat['cat_no']])) {
                        $child_showcat['name'] = $newshow_cats[$child_showcat['cat_no']];
                    }
                    $show_cat['childs'][$key] = $child_showcat;
                }
                rsort($show_cat['childs']);
                $newshowcats[] = $show_cat;
            }

            redisSet($catno_key, json_encode($newshowcats), 3600);
            return $newshowcats;
        } else {
            return [];
        }
    }

    public function getBrandsList($condition, $lang = 'en') {
        unset($condition['brand_name']);
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);
        $es->setfields(['spu']);
        $es->setaggs('brand.name.all', 'brand_name', 'terms', 10);
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);
        $brand_names = [];
        if (isset($ret['aggregations']['brand_name']['buckets'])) {
            foreach ($ret['aggregations']['brand_name']['buckets'] as $brand_name) {
                $brand_names[] = ['brand_name' => $brand_name['key'], 'count' => $brand_name['doc_count']];
            }
        }
        return $brand_names;
    }

    public function getSpecsList($condition, $lang = 'en') {
        unset($condition['show_cat_no']);
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);
        $es->setfields(['spu']);
        $es->setaggs('attrs.spec_attrs.name.all', 'spec_name', 'terms', 20);
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);
        $spec_names = [];
        if (isset($ret['aggregations']['spec_name']['buckets'])) {
            foreach ($ret['aggregations']['spec_name']['buckets'] as $spec_name) {
                $spec_names[] = ['spec_name' => $spec_name['key'], 'count' => $spec_name['doc_count']];
            }
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
