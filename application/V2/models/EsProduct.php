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

    public function __construct() {
        parent::__construct();
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
                $value = trim($condition[$name]);
                if (!$field) {
                    $field = $name;
                }
                $body['query']['bool']['must'][] = [$qurey_type => [$field => $value]];
            }
        } elseif ($qurey_type == ESClient::WILDCARD) {

            if (isset($condition[$name]) && $condition[$name]) {

                $value = trim($condition[$name]);
                if (!$field) {
                    $field = $name;
                }
                $body['query']['bool']['must'][] = [$qurey_type => [$field => '*' . $value . '*']];
            }
        } elseif ($qurey_type == ESClient::MULTI_MATCH) {
            if (isset($condition[$name]) && $condition[$name]) {
                $value = trim($condition[$name]);
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
                $created_at_start = trim($condition[$name . '_start']);
                $created_at_end = trim($condition[$name . '_end']);
                $body['query']['bool']['must'][] = [ESClient::RANGE => [$name => ['gte' => $created_at_start, 'lte' => $created_at_end,]]];
            } elseif (isset($condition[$name . '_start']) && $condition[$name . '_start']) {
                $created_at_start = trim($condition[$name . '_start']);

                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['gte' => $created_at_start,]]];
            } elseif (isset($condition[$name . '_end']) && $condition[$name . '_end']) {
                $created_at_end = trim($condition[$name . '_end']);
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
            $status = trim($condition[$name]);
            if ($status == 'ALL') {

                $body['query']['bool']['must_not'][] = ['bool' => [ESClient::SHOULD => [
                            [ESClient::MATCH_PHRASE => [$field => self::STATUS_DELETED]],
                            [ESClient::MATCH_PHRASE => [$field => 'CLOSED']]]
                ]];
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
                $bool[] = [$qurey_type => [$field => trim($name)]];
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
            $recommend_flag = trim($condition[$name]) == 'Y' ? 'Y' : $default;
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
        //   $this->_getQurey($condition, $body, ESClient::WILDCARD, 'show_cat_no', 'show_cats.all');
        if (isset($condition['show_cat_no']) && $condition['show_cat_no']) {
            $show_cat_no = trim($condition['show_cat_no']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::TERM => ['show_cats.cat_no1' => $show_cat_no]],
                        [ESClient::TERM => ['show_cats.cat_no2' => $show_cat_no]],
                        [ESClient::TERM => ['show_cats.cat_no3' => $show_cat_no]],
            ]]];
        }
        $this->_getQurey($condition, $body, ESClient::TERM, 'market_area_bn', 'show_cats.market_area_bn');
        $this->_getQurey($condition, $body, ESClient::TERM, 'country_bn', 'show_cats.country_bn');
        $this->_getQurey($condition, $body, ESClient::TERM, 'scat_no1', 'show_cats.cat_no1');
        $this->_getQurey($condition, $body, ESClient::TERM, 'scat_no2', 'show_cats.cat_no2');
        $this->_getQurey($condition, $body, ESClient::TERM, 'scat_no3', 'show_cats.cat_no3');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no1', 'material_cat.cat_no1');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no2', 'material_cat.cat_no2');
        $this->_getQurey($condition, $body, ESClient::TERM, 'mcat_no3', 'material_cat.cat_no3');
        $this->_getQurey($condition, $body, ESClient::TERM, 'bizline_id', 'bizline_id');
        $this->_getQurey($condition, $body, ESClient::TERM, 'image_count', 'image_count');


        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'supplier_name', 'suppliers.supplier_name.all');
        $this->_getQurey($condition, $body, ESClient::TERM, 'supplier_id', 'suppliers.supplier_id');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'created_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'checked_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'updated_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'onshelf_at');
        $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED',
            'DELETED', 'DRAFT', 'INVALID'], 'VALID');

        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'brand', 'brand.name.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'real_name', 'name.all');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'source');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'exe_standard', 'exe_standard.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'app_scope', 'app_scope.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'advantages', 'advantages.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'tech_paras', 'tech_paras.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'source_detail', 'source_detail.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'keywords', 'keywords.ik');



        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'created_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'updated_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'checked_by');
        if (empty($condition['deleted_flag'])) {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => $condition['deleted_flag'] === 'Y' ? 'Y' : 'N']];
        }
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
            $onshelf_flag = trim($condition['onshelf_flag']) == 'N' ? 'N' : 'Y';
            if (trim($condition['onshelf_flag']) === 'A') {

            } elseif ($onshelf_flag === 'N') {
                $body['query']['bool']['must'][] = [ESClient::TERM => ['onshelf_flag' => 'N']];
            } else {
                $body['query']['bool']['must'][] = [ESClient::TERM => ['onshelf_flag' => 'Y']];
            }
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['show_cats.onshelf_flag' => 'Y']];
        }
        if (isset($condition['show_name']) && $condition['show_name']) {
            $show_name = trim($condition['show_name']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::MATCH => ['show_name.ik' => $show_name]],
                        [ESClient::WILDCARD => ['show_name.all' => '*' . $show_name . '*']],
            ]]];
        }

        if (isset($condition['name']) && $condition['name']) {
            $name = trim($condition['name']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        // [ESClient::MATCH => ['name.ik' => $name]],
                        [ESClient::WILDCARD => ['name.all' => '*' . $name . '*']],
            ]]];
        }
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
        if (isset($condition['attrs']) && $condition['attrs']) {
            $attrs = trim($condition['attrs']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::WILDCARD => ['attrs.spec_attrs.value.all' => '*' . $attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.spec_attrs.name.all' => '*' . $attrs . '*']],
            ]]];
        }

        $this->_getQurey($condition, $body, ESClient::MATCH, 'warranty', 'warranty.ik');
        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = trim($condition['keyword']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::MATCH => ['name.ik' => $keyword]],
                        [ESClient::MATCH => ['show_name.ik' => $keyword]],
                        [ESClient::TERM => ['spu' => $keyword]],
                        [ESClient::MATCH => ['keywords.ik' => $keyword]],
                        [ESClient::WILDCARD => ['attr.spec_attrs.value.all' => '*' . $keyword . '*']],
                        [ESClient::WILDCARD => ['attr.spec_attrs.name.all' => '*' . $keyword . '*']],
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
            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }
            $es->setbody($body)->setsort('created_at', 'desc')->setsort('sku_count', 'desc')
                    ->setsort('_id', 'desc');

            if (isset($condition['sku_count']) && $condition['sku_count'] == 'Y') {
                $es->setaggs('sku_count', 'sku_count', 'sum');
            }
            $es->setaggs('image_count', 'image_count', 'sum');
//            else {
//                $es->setaggs('show_cats.cat_no3', 'show_cat_no3');
//                $es->setaggs('show_cats.cat_no2', 'show_cat_no2');
//                $es->setaggs('show_cats.cat_no1', 'show_cat_no1');
//            }
            $es->setaggs('brand.name.all', 'brands', 'terms', 0);
            $es->setaggs('suppliers.supplier_id', 'suppliers', 'terms', 0);

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
     * 获取产品总数
     * @param array $condition //搜索条件
     * @param string $lang // 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getCounts($conditions, $lang = 'en') {

        try {
            $updateParams = [];
            foreach ($conditions as $condition) {
                $body = $this->getCondition($condition);
                $updateParams['body'][] = ['count' => [$this->dbName, $this->tableName . '_' . $lang]];
                $updateParams['body'][] = [$body];
            }

            $es = new ESClient();
            $ret = $es->bulk($updateParams);

//            $ret = $es->setbody($body)
//                    ->count($this->dbName, $this->tableName . '_' . $lang, '');
//            if (isset($ret['count'])) {
//                return $ret['count'];
//            } else {
//                return 0;
//            }
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
            rsort($spus);
            $key = json_encode($spus) . '_' . $lang;
            $data = redisGet($key);
            if ($data && json_decode($data)) {
                return json_decode($data, true);
            } else {
                $products = $this->where(['spu' => ['in', $spus], 'lang' => $lang])
                        ->field('spu,material_cat_no,brand,bizline_id')
                        ->select();
                $brands = [];
                $bizline_ids = [];
                $material_cat_nos = [];
                $attr_spus = $mcat_nos = [];

                foreach ($products as $item) {
                    $this->_findnulltoempty($item);
                    $spu = $item['spu'];
                    $mcat_nos[] = $item['material_cat_no'];
                    $attr_spus[] = $spu;
                    $brands[$spu] = $item['brand'];
                    $brands[$spu] = $item['brand'];
                    $bizline_ids[] = $item['bizline_id'];
                    $material_cat_nos[$spu] = $item['material_cat_no'];
                }

                $bizline_model = new BizlineModel();
                $bizline_arr = $bizline_model->getNameByIds($bizline_ids);
                $mcat_nos = array_unique($mcat_nos);

                $material_cat_model = new MaterialCatModel();
                $mcats = $material_cat_model->getmaterial_cats($mcat_nos, $lang);
                $mcats_zh = $material_cat_model->getmaterial_cats($mcat_nos, 'zh');

                $ret = [];
                foreach ($products as $item) {

                    if (isset($mcats[$item['material_cat_no']])) {
                        $body['material_cat'] = $mcats[$item['material_cat_no']];
                    } else {
                        $body['material_cat'] = new stdClass();
                    }
                    if (isset($mcats_zh[$item['material_cat_no']])) {
                        $body['material_cat_zh'] = $mcats_zh[$item['material_cat_no']]; //json_encode($mcats_zh[$item['material_cat_no']], JSON_UNESCAPED_UNICODE);
                    } else {
                        $body['material_cat_zh'] = new stdClass(); // json_encode(new stdClass(), JSON_UNESCAPED_UNICODE);
                    }
                    $spu = $item['spu'];
                    $body['brand'] = $brands[$spu];
                    $body['bizline_id'] = $item['bizline_id'];
                    $body['bizline'] = isset($bizline_arr[$item['bizline_id']]) ? $bizline_arr[$item['bizline_id']] : new stdClass();
                    $body['material_cat_no'] = $material_cat_nos[$spu];
                    $ret[$item['spu']] = $body;
                }
                redisSet($key, json_encode($ret), 60);
                return $ret;
            }
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
     * @return mix  sku数组信息列表
     */

    public function getMinimumOrderQuantity($spus, $lang = 'en') {
        try {
            $minimumorderquantutys = $this->table('erui_goods.goods')
                            ->field('spu,min(min_order_qty) as value ,min(exw_days) as min_exw_day,min_pack_unit,'
                                    . 'max(exw_days) as max_exw_day')
                            ->where(['spu' => ['in', $spus], 'lang' => $lang])
                            ->group('spu')->select();
            $ret = [];
            if ($minimumorderquantutys) {
                foreach ($minimumorderquantutys as $minimumorderquantuty) {
                    $spu = $minimumorderquantuty['spu'];

                    $ret[$spu] = $minimumorderquantuty;
                } return $ret;
            }
            return [];
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
     * @return mix  sku数组信息列表
     */

    public function getonshelf_flag($spus, $lang = 'en') {
        try {
            $onshelf_flags = $this->table('erui_goods.show_cat_product')
                            ->field('spu,max(created_by) as max_created_by'
                                    . ',max(created_at) as max_created_at'
                                    . ' ,max(updated_by) as min_updated_by'
                                    . ',max(updated_at) as max_updated_at'
                                    . ' ,max(checked_by) as min_checked_by'
                                    . ' ,max(checked_by) as min_checked_at')
                            ->where(['spu' => ['in', $spus], 'lang' => $lang, 'onshelf_flag' => 'Y'])
                            ->group('spu')->select();
            $ret = [];
            if ($onshelf_flags) {
                foreach ($onshelf_flags as $onshelf_flag) {
                    $spu = $onshelf_flag['spu'];

                    $ret[$spu] = $onshelf_flag;
                } return $ret;
            }
            return [];
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

            if ($val === null) {
                $item[$key] = '';
            } else {
                $item[$key] = $val;
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
            $min_id = 0;
            $count = $this->where(['lang' => $lang, 'id' => ['gt', $min_id]
                    ])->count('id');


            echo '共有', $count, '条记录需要导入!', PHP_EOL;
            // die;
            ob_flush();

            flush();
            $k = 1;
            for ($i = 0; $i < $count; $i += 100) {
                if ($i > $count) {
                    $i = $count;
                }


                if ($min_id === 0) {
                    $products = $this->where(['lang' => $lang, 'id' => ['gt', 0]
                                    ])->limit(0, 100)
                                    ->order('id desc')->select();
                } else {
                    $products = $this->where(['lang' => $lang, 'id' => ['lt', $min_id]
                                    ])->limit(0, 100)
                                    ->order('id desc')->select();
                }
                $bizline_ids = $spus = $mcat_nos = [];
                if ($products) {
                    foreach ($products as $item) {
                        $mcat_nos[] = $item['material_cat_no'];
                        $spus[] = $item['spu'];
                        $bizline_ids[] = $item['bizline_id'];
                    }
                    $spus = array_unique($spus);
                    $mcat_nos = array_unique($mcat_nos);
                    $productmodel = new ProductModel();
                    if ($lang == 'zh') {
                        $name_locs = $productmodel->getNamesBySpus($spus, 'en');
                    } else {
                        $name_locs = $productmodel->getNamesBySpus($spus, 'zh');
                    }
                    $material_cat_model = new MaterialCatModel();
                    $mcats = $material_cat_model->getmaterial_cats($mcat_nos, $lang); //获取物料分类
                    $mcats_zh = $material_cat_model->getmaterial_cats($mcat_nos, 'zh');

                    $show_cat_product_model = new ShowCatProductModel();
                    $scats = $show_cat_product_model->getshow_catsbyspus($spus, $lang); //根据spus获取展示分类编码

                    $goods_supplier_model = new GoodsSupplierModel();
                    $suppliers = $goods_supplier_model->getsuppliersbyspus($spus, $lang);
                    $product_attr_model = new ProductAttrModel();
                    $product_attrs = $product_attr_model->getproduct_attrbyspus($spus, $lang); //根据spus获取产品属性

                    $product_attach_model = new ProductAttachModel();
                    $attachs = $product_attach_model->getproduct_attachsbyspus($spus, $lang); //根据SPUS获取产品附件
                    $es = new ESClient();
                    $minimumorderouantitys = $this->getMinimumOrderQuantity($spus, $lang);

                    $bizline_model = new BizlineModel();
                    $bizline_arr = $bizline_model->getNameByIds($bizline_ids);
                    $onshelf_flags = $this->getonshelf_flag($spus, $lang);
                    echo '<pre>';
                    foreach ($products as $key => $item) {
                        $flag = $this->_adddoc($item, $attachs, $scats, $mcats, $product_attrs, $minimumorderouantitys, $onshelf_flags, $lang, $max_id, $es, $k, $mcats_zh, $name_locs, $suppliers, $bizline_arr);
                        if ($key === 99) {
                            $min_id = $item['id'];
                        }
                        print_r($flag);
                        ob_flush();
                        flush();
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

    private function _adddoc(&$item, &$attachs, &$scats, &$mcats, &$product_attrs, &$minimumorderouantitys, &$onshelf_flags, &$lang, &$max_id, &$es, &$k, &$mcats_zh, &$name_locs, &$suppliers, &$bizline_arr) {

        $spu = $id = $item['spu'];
        $es_product = $es->get($this->dbName, $this->tableName . '_' . $lang, $id, 'brand,material_cat_no');

        $body = $item;
        $item['brand'] = str_replace("\t", '', str_replace("\n", '', str_replace("\r", '', $item['brand'])));
        if (json_decode($item['brand'], true)) {
            $body['brand'] = json_decode($item['brand'], true);
        } elseif ($item['brand']) {
            $body['brand'] = ['lang' => $lang, 'name' => $item['brand'], 'logo' => '', 'manufacturer' => ''];
        } else {
            $body['brand'] = ['lang' => $lang, 'name' => '', 'logo' => '', 'manufacturer' => ''];
        }

        $body['sort_order'] = $body['source'] == 'ERUI' ? 100 : 1;
        if (isset($attachs[$spu])) {
            $body['attachs'] = json_encode($attachs[$spu], 256);
            $body['image_count'] = isset($attachs[$spu]['BIG_IMAGE']) ? count($attachs[$spu]['BIG_IMAGE']) : 0;
        } else {
            $body['attachs'] = '[]';
            $body['image_count'] = 0;
        }

        $material_cat_no = $item['material_cat_no'];
        if (isset($mcats[$material_cat_no])) {
            $body['material_cat'] = $mcats[$item['material_cat_no']];
        } else {
            $body['material_cat'] = new stdClass();
        }
        if (isset($bizline_arr[$item['bizline_id']])) {
            $body['bizline'] = $bizline_arr[$item['bizline_id']];
        } else {
            $body['bizline'] = new stdClass();
        }


        if (isset($mcats_zh[$item['material_cat_no']])) {
            $body['material_cat_zh'] = $mcats_zh[$item['material_cat_no']];
        } else {
            $body['material_cat_zh'] = new stdClass();
        }
        if (isset($scats[$spu])) {

            $show_cats = $scats[$spu];
            rsort($show_cats);
            $body['show_cats'] = $show_cats;
        } else {
            $body['show_cats'] = [];
        }

        if ($es_product && ($es_product['brand'] !== $body['brand'] || $es_product['material_cat_no'] !== $item['material_cat_no'])) {
            $this->BatchSKU($spu, $lang, $body['brand'], $body['brand_childs'], $item['material_cat_no'], $body['material_cat'], $body['material_cat_zh']);
        }
        if (isset($name_locs[$spu]) && $name_locs[$spu]) {
            $body['name_loc'] = $name_locs[$spu];
        } else {
            $body['name_loc'] = '';
        }
        if (isset($product_attrs[$spu])) {
            $attrs = $product_attrs[$spu];
            $attrs = $this->_setattrs($attrs);
            $body['attrs'] = $attrs;
        } else {
            $body['attrs'] = new stdClass();
        }
        if (isset($minimumorderouantitys[$id])) {

            $body['minimumorderouantity'] = strval($minimumorderouantitys[$id]['value']);
            $body['max_exw_day'] = strval($minimumorderouantitys[$id]['max_exw_day']);
            $body['min_exw_day'] = strval($minimumorderouantitys[$id]['min_exw_day']);
            $body['min_pack_unit'] = strval($minimumorderouantitys[$id]['min_pack_unit']);
        } else {
            $body['minimumorderouantity'] = 0;
            $body['max_exw_day'] = '';
            $body['min_exw_day'] = '';
            $body['min_pack_unit'] = '';
        }
        if (isset($onshelf_flags[$id])) {
            $body['onshelf_flag'] = 'Y';
            if ($onshelf_flags[$id]['checked_at']) {
                $body['onshelf_by'] = $onshelf_flags[$id]['checked_at'];
                $body['onshelf_at'] = $onshelf_flags[$id]['checked_by'];
            } elseif ($onshelf_flags[$id]['updated_at']) {
                $body['onshelf_by'] = $onshelf_flags[$id]['updated_at'];
                $body['onshelf_at'] = $onshelf_flags[$id]['updated_by'];
            } elseif ($onshelf_flags[$id]['created_at']) {
                $body['onshelf_by'] = $onshelf_flags[$id]['created_at'];
                $body['onshelf_at'] = $onshelf_flags[$id]['created_by'];
            } else {
                $body['onshelf_by'] = '';
                $body['onshelf_at'] = '';
            }
        } else {
            $body['onshelf_flag'] = 'N';
            $body['onshelf_by'] = '';
            $body['onshelf_at'] = '';
        }

        if (isset($suppliers[$id]) && $suppliers[$id]) {
            $body['suppliers'] = $suppliers[$id];
            //  $body['suppliers'] = json_encode($suppliers[$id], 256);
            $body['supplier_count'] = count($suppliers[$id]);
        } else {
            $body['suppliers'] = [];
            //  $body['suppliers'] = json_encode([], 256);
            $body['supplier_count'] = 0;
        }
        $this->_findnulltoempty($body);
        if ($es_product) {
            $flag = $es->update_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);



            if (!isset($flag['_version'])) {
                LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);
            }
        } else {
            $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
            if (!isset($flag['created'])) {
                LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);
            }
        }

        $k++;
        return $flag;
    }

    /* 设置属性
     * @author zyg 2017-10-12
     * @param string $spu // SPU
     * @return mix
     * @author  zhongyg
     * @version V2.0
     * @desc   ES 产品
     */

    private function _setattrs($attrs) {
        $ret = [];
        if (!empty($attrs['spec_attrs'])) {
            $ret['spec_attrs'] = $this->_formatattr($attrs['spec_attrs']);
        } else {
            $ret['spec_attrs'] = [];
        }
        if (!empty($attrs['ex_goods_attrs'])) {
            $ret['ex_goods_attrs'] = $this->_formatattr($attrs['ex_goods_attrs']);
        } else {
            $ret['ex_goods_attrs'] = [];
        }
        if (!empty($attrs['ex_hs_attrs'])) {
            $ret['ex_hs_attrs'] = $this->_formatattr($attrs['ex_hs_attrs']);
        } else {
            $ret['ex_hs_attrs'] = [];
        }
        if (!empty($attrs['other_attrs'])) {
            $ret['other_attrs'] = $this->_formatattr($attrs['other_attrs']);
        } else {
            $ret['other_attrs'] = [];
        }
    }

    /* 属性格式化
     * @author zyg 2017-10-12
     * @param string $spu // SPU
     * @return mix
     * @author  zhongyg
     * @version V2.0
     * @desc   ES 产品
     */

    private function _formatattr($attrs_json) {
        $attrs_arr = json_decode($attrs_json, true);
        $ret = [];
        if ($attrs_arr) {
            foreach ($attrs_arr as $name => $value) {
                $ret[] = ['name' => $name,
                    'value' => $value];
            }
        }
        return $ret;
    }

    /*
     * 批量更新商品的品牌或物理分类编码
     * @author zyg 2017-07-31
     * @param string $spu // SPU
     * @param string $lang // 语言 zh en ru es
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function BatchSKU($spu, $lang, $brand, $brand_childs, $material_cat_no, $material_cat, $material_cat_zh) {
        try {
            $goods_model = new GoodsModel();
            $goods = $goods_model->where(['spu' => $spu, 'lang' => $lang])->field('sku')->select();
            $updateParams = [];
            $updateParams['index'] = $this->dbName;
            $updateParams['type'] = 'goods_' . $lang;
            if ($goods) {
                foreach ($goods as $good) {
                    $updateParams['body'][] = ['update' => ['_id' => $good['sku']]];
                    $updateParams['body'][] = ['doc' =>
                        ['brand' => $brand,
                            'brand' => $brand,
                            'brand_childs' => $brand_childs,
                            'material_cat_no' => $material_cat_no,
                            'material_cat' => $material_cat,
                            'material_cat_zh' => $material_cat_zh,
                        ]
                    ];
                }
                $es = new ESClient();
                $es->bulk($updateParams);
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
                $where['id'] = ['gt', $max_id];
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
                return trim($value);
            } else {
                $condition = null;
                unset($condition);
                return trim($default);
            }
        } elseif ($type === 'bool') {
            if (isset($condition[$name]) && $condition[$name]) {
                $flag = trim($condition[$name]) == 'Y' ? 'Y' : 'N';
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
                $return = strtoupper(trim($condition[$name]));
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
            if (is_array($spu)) {

                $product_model = new ProductModel();
                $products = $product_model->where(['spu' => ['in', $spu], 'lang' => $lang])->select();
            } elseif ($spu) {
                $product_model = new ProductModel();
                $products = $product_model->where(['spu' => $spu, 'lang' => $lang])->select();
            } else {
                return false;
            }
            if (!$products) {
                return false;
            }

            if ($products) {
                foreach ($products as $item) {
                    $mcat_nos[] = $item['material_cat_no'];
                    $spus[] = $item['spu'];
                    $bizline_ids[] = $item['bizline_id'];
                }
                $mcat_nos = array_unique($mcat_nos);
                $material_cat_model = new MaterialCatModel();
                $mcats = $material_cat_model->getmaterial_cats($mcat_nos, $lang); //获取物料分类
                $mcats_zh = $material_cat_model->getmaterial_cats($mcat_nos, 'zh'); //获取物料分类
                $show_cat_product_model = new ShowCatProductModel();
                $scats = $show_cat_product_model->getshow_catsbyspus($spus, $lang); //根据spus获取展示分类编码

                $product_attr_model = new ProductAttrModel();
                $product_attrs = $product_attr_model->getproduct_attrbyspus($spus, $lang); //根据spus获取产品属性
                $goods_supplier_model = new GoodsSupplierModel();
                $suppliers = $goods_supplier_model->getsuppliersbyspus($spus, $lang);
                $product_attach_model = new ProductAttachModel();
                $attachs = $product_attach_model->getproduct_attachsbyspus($spus, $lang); //根据SPUS获取产品附件
                $bizline_model = new BizlineModel();
                $bizline_arr = $bizline_model->getNameByIds($bizline_ids);
                $minimumorderouantitys = $this->getMinimumOrderQuantity($spus, $lang);
                $productmodel = new ProductModel();
                if ($lang == 'zh') {
                    $name_locs = $productmodel->getNamesBySpus($spus, 'en');
                } else {
                    $name_locs = $productmodel->getNamesBySpus($spus, 'zh');
                }
                $onshelf_flags = $this->getonshelf_flag($spus, $lang);
                $k = 0;
                foreach ($products as $item) {

                    $flag = $this->_adddoc($item, $attachs, $scats, $mcats, $product_attrs, $minimumorderouantitys, $onshelf_flags, $lang, $k, $es, $k, $mcats_zh, $name_locs, $suppliers, $bizline_arr);
                }
            }
            $es->refresh($this->dbName);
            return true;
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
                        ESClient::MATCH => [
                            "show_cats.all" => $old_cat_no
                        ]
            ]])->count($index, $type);
        for ($i = 0; $i < $count['count']; $i += 100) {
            $ret = $this->setbody(['query' => [
                            ESClient::MATCH => [
                                "show_cats.ik" => $old_cat_no
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
        $data['deleted_flag'] = 'Y';
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

    /* 删除SKU
     * @param string $sku SKU
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function batchdelete($skus, $lang = 'en') {
        $es = new ESClient();
        if (empty($skus)) {
            return false;
        }
        if (is_string($skus)) {
            $skus = [$skus];
        }

        $type_goods = 'product_' . $lang;
        $updateParams = array();
        $updateParams['index'] = $this->dbName;
        $updateParams['type'] = $type_goods;
        foreach ($skus as $sku) {

            $updateParams['body'][] = ['update' => ['_id' => $sku]];
            $updateParams['body'][] = ['doc' => [
                    'status' => self::STATUS_DELETED,
                    'deleted_flag' => 'Y']];
        }
        $es->bulk($updateParams);
        return true;
    }

    public function BatchUpdate_Attachs($spus, $lang = 'en') {
        $es = new ESClient();
        if (empty($spus)) {
            return false;
        }
        if (is_string($spus)) {
            $spus = [$spus];
        }
        $product_attach_model = new ProductAttachModel();
        $attachs = $product_attach_model->getproduct_attachsbyspus($spus, $lang);


        $updateParams = array();
        $updateParams['index'] = $this->dbName;
        if ($lang) {
            $langs = [$lang];
        } else {
            $langs = ['en', 'zh', 'es', 'ru'];
        }
        foreach ($langs as $lang) {
            $type_goods = 'goods_' . $lang;
            $updateParams['type'] = $type_goods;
            foreach ($spus as $spu) {
                if (isset($attachs[$spu])) {
                    $spu_attachs = json_encode($attachs[$spu], 256);
                } else {
                    $spu_attachs = '[]';
                }
                $updateParams['body'][] = ['update' => ['_id' => $spu]];
                $updateParams['body'][] = ['doc' => ['attachs' => $spu_attachs]];
            }
            $es->bulk($updateParams);
        }
        return true;
    }

}
