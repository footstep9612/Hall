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

    private function getCondition($condition, $lang = 'en') {
        $body = [];
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
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'brand', 'brand.ik');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'real_name', 'name.all');
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


        $this->_getQurey($condition, $body, ESClient::MATCH, 'show_name', 'show_name.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'name', 'name.ik');
        if (isset($condition['attrs']) && $condition['attrs']) {
            $attrs = trim($condition['attrs']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::WILDCARD => ['attrs.spec_attrs.value.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_goods_attrs.value.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_hs_attrs.value.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.other_attrs.value.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.spec_attrs.name.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_goods_attrs.name.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.ex_hs_attrs.name.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.other_attrs.name.ik' => '*' . $attrs . '*']],
            ]]];
        }
        if (isset($condition['attrs']) && $condition['attrs']) {
            $attrs = trim($condition['attrs']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::WILDCARD => ['attrs.spec_attrs.value.ik' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.spec_attrs.name.ik' => '*' . $attrs . '*']],
            ]]];
        }
        $this->_getQurey($condition, $body, ESClient::MATCH, 'warranty', 'warranty.ik');

        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = $condition['keyword'];
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::MATCH => ['name.ik' => $keyword]],
                        [ESClient::MATCH => ['show_name.ik' => $keyword]],
                        [ESClient::TERM => ['spu' => $keyword]],
                        [ESClient::WILDCARD => ['attr.spec_attrs.name.all' => '*' . $show_name . '*']],
                        [ESClient::MATCH => ['keywords.ik' => '*' . $keyword . '*']],
                        [ESClient::WILDCARD => ['brand.name.all' => '*' . $keyword . '*']],
                        [ESClient::WILDCARD => ['source.all' => '*' . $keyword . '*']],
                        [ESClient::WILDCARD => ['name.all' => '*' . $keyword . '*']],
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
            $es->setbody($body)->setsort('sku_count', 'desc')->setsort('id', 'desc');

            if (isset($condition['sku_count']) && $condition['sku_count'] == 'Y') {
                $es->setaggs('sku_count', 'sku_count', 'sum');
                $es->setaggs('show_cats.cat_no3', 'show_cat_no3', 'terms', 30);
//                $es->setaggs('show_cats.cat_no2', 'show_cat_no2');
//                $es->setaggs('show_cats.cat_no1', 'show_cat_no1');
            } else {
                $es->setaggs('show_cats.cat_no3', 'show_cat_no3', 'terms', 30);
//                $es->setaggs('show_cats.cat_no2', 'show_cat_no2');
//                $es->setaggs('show_cats.cat_no1', 'show_cat_no1');
            }
            $es->sethighlight(['show_name.ik' => new stdClass()]);

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

}
