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
    protected $update_dbName = 'erui_goods'; //数据库名称

    const STATUS_DELETED = 'DELETED';

    public function __construct() {
        parent::__construct();
        $model = new EsVersionModel();
        $version = $model->getVersion();

        if ($version) {
            $this->update_dbName = $this->dbName . '_' . $version['update_version'];
        }
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
                $body['query']['bool']['must'][] = [ESClient::RANGE => [$name => ['gte' => $created_at_start, 'lt' => $created_at_end,]]];
            } elseif (isset($condition[$name . '_start']) && $condition[$name . '_start']) {
                $created_at_start = trim($condition[$name . '_start']);

                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['gte' => $created_at_start,]]];
            } elseif (isset($condition[$name . '_end']) && $condition[$name . '_end']) {
                $created_at_end = trim($condition[$name . '_end']);
                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['lt' => $created_at_end,]]];
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
                            [ESClient::TERM => [$field => self::STATUS_DELETED]],
                            [ESClient::TERM => [$field => 'CLOSED']]]
                ]];
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

    private function _getQureyByArr(&$condition, &$body, $qurey_type = ESClient::TERMS, $names = '', $field = '') {
        if (!$field) {
            $field = [$names];
        }
        if (isset($condition[$names]) && $condition[$names]) {
            $name_arr = $condition[$names];
            $bool = [];
            $arr = [];
            foreach ($name_arr as $name) {
                if (!empty($name)) {
                    $arr = trim($name);
                }
            }
            if (!empty($bool)) {
                $body['query']['bool']['must'][] = [$qurey_type => [$field => $arr]];
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

    private function _getQureyByBool(&$condition, &$body, $qurey_type = ESClient::TERM, $name = '', $field = '', $default = 'N') {
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
        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'spu');

        if (isset($condition['spus']) && $condition['spus']) {
            $name_arr = $condition['spus'];
            $body['query']['bool']['must'][] = [ESClient::TERMS => ['spu' => $name_arr]];
        }
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
        if (isset($condition['image_count']) && $condition['image_count'] === '0') {
            $value = trim($condition['image_count']);
            $body['query']['bool']['must'][] = [ESClient::TERM => ['image_count' => $value]];
        }

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
        $this->_getQurey($condition, $body, ESClient::MATCH, 'exe_standard', 'exe_standard.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'app_scope', 'app_scope.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'advantages', 'advantages.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'tech_paras', 'tech_paras.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'source_detail', 'source_detail.' . $analyzer);
        $this->_getQurey($condition, $body, ESClient::MATCH, 'keywords', 'keywords.' . $analyzer);
        $this->_getQureyByBool($condition, $body, ESClient::TERM, 'relation_flag', 'relation_flag');
        $this->_getQureyByBool($condition, $body, ESClient::TERM, 'recommend_flag', 'recommend_flag');

        $this->_getQurey($condition, $body, ESClient::TERM, 'created_by');
        $this->_getQurey($condition, $body, ESClient::TERM, 'updated_by');
        $this->_getQurey($condition, $body, ESClient::TERM, 'checked_by');
        if (empty($condition['deleted_flag'])) {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => $condition['deleted_flag'] === 'Y' ? 'Y' : 'N']];
        }
        $employee_model = new EmployeeModel();
        if (isset($condition['created_by_name']) && $condition['created_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['created_by_name']));

            foreach ($userids as $created_by) {
                $created_by_bool[] = [ESClient::TERM => ['created_by' => $created_by]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $created_by_bool]];
        }
        if (isset($condition['updated_by_name']) && $condition['updated_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['updated_by_name']));
            foreach ($userids as $updated_by) {
                $updated_by_bool[] = [ESClient::TERM => ['updated_by' => $updated_by]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $updated_by_bool]];
        }
        if (isset($condition['checked_by_name']) && $condition['checked_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['checked_by_name']));

            foreach ($userids as $checked_by) {
                $checked_by_bool[] = [ESClient::TERM => ['checked_by' => $checked_by]];
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
            $body['query']['bool']['must'][] = [ESClient::TERM => ['onshelf_flag' => 'Y']];
        }


        if (isset($condition['show_name']) && $condition['show_name']) {
            $show_name = trim($condition['show_name']);
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['show_name.' . $analyzer => ['query' => $name, 'boost' => 1, 'operator' => 'and']]];
        }

        if (isset($condition['name']) && $condition['name']) {
            $name = trim($condition['name']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::MATCH => ['name.' . $analyzer => ['query' => $name, 'boost' => 99, 'minimum_should_match' => '75%', 'operator' => 'or']]],
                        [ESClient::TERM => ['spu' => $name]],
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
        if (isset($condition['spec_attrs']) && $condition['spec_attrs']) {
            $spec_attrs = trim($condition['spec_attrs']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::WILDCARD => ['attrs.spec_attrs.value.all' => '*' . $spec_attrs . '*']],
                        [ESClient::WILDCARD => ['attrs.spec_attrs.name.all' => '*' . $spec_attrs . '*']],
            ]]];
        }

        $this->_getQurey($condition, $body, ESClient::MATCH, 'warranty', 'warranty.' . $analyzer);
        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = trim($condition['keyword']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::MATCH => ['name.' . $analyzer => ['query' => $keyword, 'boost' => 99, 'minimum_should_match' => '75%', 'operator' => 'or']]],
                        [ESClient::MATCH => ['show_name.' . $analyzer => ['query' => $keyword, 'boost' => 99, 'minimum_should_match' => '75%', 'operator' => 'or']]],
                        [ESClient::MATCH => ['attr.spec_attrs.name.' . $analyzer => ['query' => $keyword, 'boost' => 1, 'operator' => 'and']]],
                        [ESClient::MATCH => ['attr.spec_attrs.value.' . $analyzer => ['query' => $keyword, 'boost' => 1, 'operator' => 'and']]],
                        [ESClient::TERM => ['spu' => $keyword]],
                        [ESClient::MATCH => ['keywords.' . $analyzer => ['query' => $keyword, 'boost' => 2]]],
                        [ESClient::MATCH_PHRASE => ['brand.name.all' => ['query' => $keyword, 'boost' => 39]]],
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
            $es->setbody($body)->setsort('created_at', 'desc')
                    ->setsort('_id', 'desc');



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
     * 获取产品图片总数量
     */

    public function getImageCountByCondition($condition, $lang) {
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);

        $es->setaggs('image_count', 'image_count', 'sum');
        $es->setfields(['image_count']);
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);

        $image_count = 0;
        if (isset($ret['aggregations']['image_count']['value'])) {
            $image_count = $ret['aggregations']['image_count']['value'];
        }
        $ret1 = $ret = $es = null;
        unset($ret1, $ret, $es);

        return $image_count;
    }

    /*
     * 获取产品图片总数量
     */

    public function getSupplierCountByCondition($condition, $lang) {
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);

        $es->setaggs('suppliers.supplier_id', 'supplier_id', 'terms', 0);
        $es->setfields(['spu']);
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);


        $count = 0;
        if (isset($ret['aggregations']['supplier_id']['buckets'])) {
            $count = count($ret['aggregations']['supplier_id']['buckets']);
        }
        //$ret = $es = null;
        //unset($ret, $es);

        return $count;
    }

    /*
     * 获取供应商及对应供应商spu数量总数
     * @param array $condition //搜索条件
     * @param string $lang // 语言
     * @author  zhongyg
     * @date    2018-6-11 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getSupplieridsAndSpuCountByCondition($condition, $lang) {
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);
        $es->setaggs('suppliers.supplier_id', 'supplier_id', 'terms', 0);
        $es->setfields(['spu']);
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);
        $result = [];
        $supplier_ids = [];
        if (isset($ret['aggregations']['supplier_id']['buckets'])) {

            foreach ($ret['aggregations']['supplier_id']['buckets'] as $SupplieridSpuCount) {

                $result[$SupplieridSpuCount['key']] = $SupplieridSpuCount['doc_count'];
                $supplier_ids[] = $SupplieridSpuCount['key'];
            }
        }


        return [$result, $supplier_ids];
    }

    /*
     * 获取商品总数量
     */

    public function getSkuCountByCondition($condition, $lang) {
        $body = $this->getCondition($condition);
//        $redis_key = 'spu_' . md5(json_encode($body)) . '_' . $lang;
//        if (redisExist($redis_key)) {
//            return redisGet($redis_key);
//        }
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
        // redisSet($redis_key, $sku_count, 180);
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

    public function getList($condition, $_source, $lang = 'en', $from = 0, $pagesize = 1000, &$total = 0) {

        try {
            $body = $this->getCondition($condition);
            $es = new ESClient();
            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }
            $es->setbody($body)->setfields($_source)->setsort('id', 'desc');
            $data = $es->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize);

            $total = isset($data['hits']['total']) ? $data['hits']['total'] : 0;
            if (isset($data['hits']['hits'])) {
                $ret = [];
                foreach ($data['hits']['hits'] as $item) {

                    $ret[] = $item['_source'];
                }
                return $ret;
            } else {
                return [];
            }
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

            unset($es);
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
            $key = md5(json_encode($spus)) . '_' . $lang;
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

                    $attr_spus[] = $spu;
                    $brands[$spu] = $item['brand'];



                    if (!empty($item['material_cat_no'])) {
                        $mcat_nos[] = $item['material_cat_no'];
                    }


                    if (!empty($item['bizline_id'])) {
                        $bizline_ids[] = $item['bizline_id'];
                    }
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
                                    . ' ,max(updated_by) as max_updated_by'
                                    . ',max(updated_at) as max_updated_at'
                                    . ' ,max(checked_by) as max_checked_by'
                                    . ' ,max(checked_by) as max_checked_at')
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

    public function importproducts($lang = 'en', $product_spus = [], $deleted_flag = null) {
        try {
            $max_id = 0;
            $where_count = ['lang' => $lang, 'id' => ['gt', 0]];
            if ($product_spus) {
                $where_count['spu'] = ['in', $product_spus];
            }
            if ($deleted_flag) {
                $where_count['deleted_flag'] = $deleted_flag == 'Y' ? 'Y' : 'N';
            }

            $count = $this->where($where_count)->count('id');


            echo '共有', $count, '条记录需要导入!', PHP_EOL;
// die;
            ob_flush();

            flush();
            $k = 1;
            for ($i = 0; $i < $count; $i += 100) {
                $time1 = microtime(true);
                if ($i > $count) {
                    $i = $count;
                }

                $where = ['lang' => $lang,];
                if ($max_id === 0) {
                    $where['id'] = ['gt', 0];
                } else {
                    $where['id'] = ['gt', $max_id];
                }
                if ($product_spus) {
                    $where['spu'] = ['in', $product_spus];
                }
                if ($deleted_flag) {
                    $where['deleted_flag'] = $deleted_flag == 'Y' ? 'Y' : 'N';
                }
                $products = $this->where($where)->limit(0, 100)
                                ->order('id ASC')->select();
                $bizline_ids = $spus = $mcat_nos = [];
                $material_cat_model = new MaterialCatModel();
                $productmodel = new ProductModel();
                $goods_supplier_model = new GoodsSupplierModel();
                $show_cat_product_model = new ShowCatProductModel();
                $product_attach_model = new ProductAttachModel();
                $product_attr_model = new ProductAttrModel();
                $es = new ESClient();
                $bizline_model = new BizlineModel();
                $special_goods_model = new SpecialGoodsModel();

                if ($products) {
                    foreach ($products as $item) {
                        !empty($item['material_cat_no']) ? $mcat_nos[] = $item['material_cat_no'] : '';
                        !empty($item['material_cat_no']) ? $spus[] = $item['spu'] : '';
                        !empty($item['bizline_id']) ? $bizline_ids[] = $item['bizline_id'] : '';
                    }
                    $spus = array_unique($spus);
                    $mcat_nos = array_unique($mcat_nos);

                    if ($lang == 'zh') {
                        $name_locs = $productmodel->getNamesBySpus($spus, 'en');
                    } else {
                        $name_locs = $productmodel->getNamesBySpus($spus, 'zh');
                    }
                    $mcats = $material_cat_model->getmaterial_cats($mcat_nos, $lang); //获取物料分类
                    $mcats_zh = $material_cat_model->getmaterial_cats($mcat_nos, 'zh');
                    $scats = $show_cat_product_model->getshow_catsbyspus($spus, $lang); //根据spus获取展示分类编码
                    $suppliers = $goods_supplier_model->getsuppliersbyspus($spus, $lang);
                    $product_attrs = $product_attr_model->getproduct_attrbyspus($spus, $lang); //根据spus获取产品属性
                    $attachs = $product_attach_model->getproduct_attachsbyspus($spus, $lang); //根据SPUS获取产品附件
                    $minimumorderouantitys = $this->getMinimumOrderQuantity($spus, $lang);
                    $bizline_arr = $bizline_model->getNameByIds($bizline_ids);
                    $onshelf_flags = $this->getonshelf_flag($spus, $lang);
                    $special_goods = $special_goods_model->getSpecialsBySpu($spus, $lang);

                    echo '<pre>';
                    $updateParams = [];
                    $updateParams['index'] = $this->update_dbName;
                    $updateParams['type'] = $this->tableName . '_' . $lang;
                    foreach ($products as $key => $item) {
                        $flag = $this->_adddoc($item, $attachs, $scats, $mcats, $product_attrs, $minimumorderouantitys, $onshelf_flags, $lang, $es, $k, $mcats_zh, $name_locs, $suppliers, $bizline_arr, $special_goods);
                        if ($key === 99) {
                            $max_id = $item['id'];
                        }
                        var_dump($flag);
                    }



                    echo microtime(true) - $time1, "\r\n";
                } else {
                    $this->_delcache();
                    return false;
                }
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    private function _adddoc(&$item, &$attachs, &$scats, &$mcats, &$product_attrs, &$minimumorderouantitys, &$onshelf_flags, &$lang, &$es, &$k, &$mcats_zh, &$name_locs, &$suppliers, &$bizline_arr, $special_goods) {

        $spu = $id = $item['spu'];
        $es_product = $es->exists($this->update_dbName, $this->tableName . '_' . $lang, $id) ? $es->get($this->update_dbName, $this->tableName . '_' . $lang, $id, 'brand,material_cat_no') : null;
        $body = $item;
        $body['name'] = htmlspecialchars_decode($item['name']);
        $body['tech_paras'] = htmlspecialchars_decode($item['tech_paras']);
        $body['exe_standard'] = htmlspecialchars_decode($item['exe_standard']);
        $body['show_name'] = htmlspecialchars_decode($item['show_name']);
        $item['brand'] = str_replace("\t", '', str_replace("\n", '', str_replace("\r", '', $item['brand'])));
        $body['brand'] = !empty(json_decode($item['brand'], true)) ? json_decode($item['brand'], true) : ($item['brand'] ? ['lang' => $lang, 'name' => trim($item['brand']), 'logo' => '', 'manufacturer' => ''] : ['lang' => $lang, 'name' => '', 'logo' => '', 'manufacturer' => '']);
        $body['sort_order'] = $body['source'] == 'ERUI' ? '100' : '1';
        if (isset($attachs[$spu])) {
            $body['attachs'] = json_encode($attachs[$spu], 256);
            $body['image_count'] = isset($attachs[$spu]['BIG_IMAGE']) ? count($attachs[$spu]['BIG_IMAGE']) : '0';
        } else {
            $body['attachs'] = '[]';
            $body['image_count'] = '0';
        }
        $body['image_count'] = strval($body['image_count']);
        $body['sku_count'] = isset($body['sku_count']) && intval($body['sku_count']) > 0 ? intval($body['sku_count']) : 0;
        $material_cat_no = trim($item['material_cat_no']);
        $body['material_cat'] = isset($mcats[$material_cat_no]) ? $mcats[$item['material_cat_no']] : new stdClass();
        $body['bizline'] = isset($bizline_arr[trim($item['bizline_id'])]) ? $bizline_arr[$item['bizline_id']] : new stdClass();
        $body['material_cat_zh'] = isset($mcats_zh[$material_cat_no]) ? $mcats_zh[$material_cat_no] : new stdClass();
        $body['specials'] = isset($special_goods[$spu]) ? $special_goods[$spu] : [];
        if (isset($scats[$spu])) {
            $show_cats = $scats[$spu];
            rsort($show_cats);
            $body['show_cats'] = $show_cats;
        } else {
            $body['show_cats'] = [];
        }
        $body['specials'] = isset($special_goods[$spu]) ? $special_goods[$spu] : [];
        $body['show_cats_nested'] = $body['show_cats'];
        if ($es_product && ($es_product['_source']['brand'] != $body['brand'] || $es_product['_source']['material_cat_no'] !== $item['material_cat_no'])) {
            $this->BatchSKU($spu, $lang, $body['brand'], $body['brand_childs'], $item['material_cat_no'], $body['material_cat'], $body['material_cat_zh']);
        }

        $body['name_loc'] = isset($name_locs[$spu]) && $name_locs[$spu] ? htmlspecialchars_decode($name_locs[$spu]) : '';

        if (isset($product_attrs[$spu])) {
            $attrs = $this->_setattrs($product_attrs[$spu]);
            $body['attrs'] = new stdClass(); // $attrs;
            $body['spec_attrs'] = !empty($attrs['spec_attrs']) ? $attrs['spec_attrs'] : [];
        } else {
            $body['spec_attrs'] = [];
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
            if ($onshelf_flags[$id]['max_checked_at']) {
                $body['onshelf_by'] = $onshelf_flags[$id]['max_checked_at'];
                $body['onshelf_at'] = $onshelf_flags[$id]['max_checked_at'];
            } elseif ($onshelf_flags[$id]['max_updated_at']) {
                $body['onshelf_by'] = $onshelf_flags[$id]['max_updated_by'];
                $body['onshelf_at'] = $onshelf_flags[$id]['max_updated_at'];
            } elseif ($onshelf_flags[$id]['max_created_at']) {
                $body['onshelf_by'] = $onshelf_flags[$id]['max_created_by'];
                $body['onshelf_at'] = $onshelf_flags[$id]['max_created_at'];
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
            $body['supplier_count'] = count($suppliers[$id]);
        } else {
            $body['suppliers'] = [];
            $body['supplier_count'] = '0';
        }
        $body['supplier_count'] = strval($body['supplier_count']);
        $this->_findnulltoempty($body);
        if ($es_product) {
            $flag = $es->update_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);
        } else {
            $flag = $es->add_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);
        }
        if (!isset($flag['_version'])) {
            LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);
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
//        if (!empty($attrs['ex_goods_attrs'])) {
//            $ret['ex_goods_attrs'] = $this->_formatattr($attrs['ex_goods_attrs']);
//        } else {
//            $ret['ex_goods_attrs'] = [];
//        }
//        if (!empty($attrs['ex_hs_attrs'])) {
//            $ret['ex_hs_attrs'] = $this->_formatattr($attrs['ex_hs_attrs']);
//        } else {
//            $ret['ex_hs_attrs'] = [];
//        }
//        if (!empty($attrs['other_attrs'])) {
//            $ret['other_attrs'] = $this->_formatattr($attrs['other_attrs']);
//        } else {
//            $ret['other_attrs'] = [];
//        }
        return $ret;
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
            foreach ($attrs_arr as $key => $items) {
                if (is_array($items)) {
                    foreach ($items as $name => $value) {
                        !in_array(['name' => $this->_filter($name),
                                    'value' => $this->_filter($value)], $ret) && !empty($name) && !empty($value) && $value != '/' ? $ret[] = ['name' => $this->_filter($name),
                                    'value' => $this->_filter($value)] : '';
                    }
                } elseif (is_string($items) && !empty($key) && !empty($items)) {
                    $ret[] = ['name' => $this->_filter($key),
                        'value' => $this->_filter($items)];
                }
            }
        }
        return $ret;
    }

    private function _filter($htmlval) {

        $val = htmlspecialchars_decode($htmlval);
        $rval = str_replace("\r", '', $val);
        $nval = str_replace("\n", '', $rval);
        $tval = str_replace("\t", '', $nval);
        return strtolower(trim($tval));
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
            $updateParams['index'] = $this->update_dbName;
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
                            $body['sort_order'] = '100';
                        } else {
                            $body['sort_order'] = '1';
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

                        $flag = $es->add_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);


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
                $products = $product_model->where(['spu' => ['in', $spu], 'deleted_flag' => 'N', 'lang' => $lang])->order('id asc')->select();
            } elseif ($spu) {
                $product_model = new ProductModel();
                $products = $product_model->where(['spu' => $spu, 'deleted_flag' => 'N', 'lang' => $lang])->order('id asc')->select();
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

                $attachs = (new ProductAttachModel())->getproduct_attachsbyspus($spus, $lang); //根据SPUS获取产品附件

                $bizline_arr = (new BizlineModel())->getNameByIds($bizline_ids);
                $minimumorderouantitys = $this->getMinimumOrderQuantity($spus, $lang);
                $special_goods = (new SpecialGoodsModel())->getSpecialsBySpu($spus, $lang);
                $productmodel = new ProductModel();
                if ($lang == 'zh') {
                    $name_locs = $productmodel->getNamesBySpus($spus, 'en');
                } else {
                    $name_locs = $productmodel->getNamesBySpus($spus, 'zh');
                }
                $onshelf_flags = $this->getonshelf_flag($spus, $lang);
                $k = 0;
                foreach ($products as $item) {
                    $this->_adddoc($item, $attachs, $scats, $mcats, $product_attrs, $minimumorderouantitys, $onshelf_flags, $lang, $es, $k, $mcats_zh, $name_locs, $suppliers, $bizline_arr, $special_goods);
                }
            }
            $es->refresh($this->update_dbName);
            $this->_delcache();
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
            $flag = $es->update_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);
            if ($flag['_shards']['successful'] !== 1) {
                LOG::write("FAIL:" . $id . var_export($flag, true), LOG::ERR);
                $this->_delcache();
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
            $es->update_document($this->update_dbName, $this->tableName . '_' . $lang, $data, $id);
            $es->refresh($this->update_dbName);
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
            return [];
        }
        $show_cat_product_model = new ShowCatProductModel();
        $scats = $show_cat_product_model->getshow_catsbyspus([$spu], $lang);
        $show_cats = isset($scats[$spu]) ? $scats[$spu] : [];

        rsort($show_cats);
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
        $es = new ESClient();
        if (empty($old_cat_no)) {
            return false;
        }
        $index = $this->update_dbName;
        $type = 'product_' . $lang;
        $count = $es->setbody(["query" => ['bool' => [ESClient::SHOULD => [
                                [ESClient::TERM => ["show_cats.cat_no3" => $old_cat_no]],
                                [ESClient::TERM => ["show_cats.cat_no2" => $old_cat_no]],
                                [ESClient::TERM => ["show_cats.cat_no1" => $old_cat_no]]
                    ]]]])->count($index, $type);


        for ($i = 0; $i < $count['count']; $i += 100) {
            $ret = $es->setbody(["query" => ['bool' => [ESClient::SHOULD => [
                                    [ESClient::TERM => ["show_cats.cat_no3" => $old_cat_no]],
                                    [ESClient::TERM => ["show_cats.cat_no2" => $old_cat_no]],
                                    [ESClient::TERM => ["show_cats.cat_no1" => $old_cat_no]]
                        ]]]])->search($index, $type, $i, 100);
            $updateParams = array();
            $updateParams['index'] = $this->update_dbName;
            $updateParams['type'] = 'product_' . $lang;


            if ($ret) {
                foreach ($ret['hits']['hits'] as $item) {
                    $updateParams['body'][] = ['update' => ['_id' => $item['_id']]];
                    $updateParams['body'][] = ['doc' => ['show_cats' => $this->getshowcats($item['_source']['spu'], $lang),
                            'show_cats_nested' => $this->getshowcats($item['_source']['spu'], $lang)
                    ]];
                }

                $es = new ESClient();
                $es->bulk($updateParams);
            }
        }
        $esgoods = new EsGoodsModel();
        $esgoods->update_showcats($old_cat_no, $lang);
        $es->refresh($this->update_dbName);
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
        $data['material_cat'] = $mcatmodel->getinfo($new_cat_no, 'zh');
        $data['material_cat_zh'] = $mcatmodel->getinfo($new_cat_no, 'zh');
        $data['material_cat_no'] = $new_cat_no;
        if ($spu) {
            $id = $spu;
            $es->update_document($this->update_dbName, $type, $data, $id);
        } else {
            $es_product_data = [
                "doc" => [
                    "material_cat" => $data['material_cat'],
                    "material_cat_zh" => $data['material_cat_zh'],
                    'material_cat_no' => $new_cat_no,
                ],
                "query" => ['bool' => [ESClient::SHOULD => [[ESClient::TERM => ["material_cat_zh.cat_no3" => $material_cat_no]],
                            [ESClient::TERM => ["material_cat_zh.cat_no2" => $material_cat_no]],
                            [ESClient::TERM => ["material_cat_zh.cat_no1" => $material_cat_no]]
            ]]]];
            $es->UpdateByQuery($this->update_dbName, 'product_' . $lang, $es_product_data);
        }
        if ($spu) {
            $esgoodsdata = [
                "doc" => [
                    "material_cat" => $data['material_cat'],
                    "material_cat_zh" => $data['material_cat_zh'],
                    'material_cat_no' => $new_cat_no,
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
                    "material_cat_zh" => $data['material_cat_zh'],
                    'material_cat_no' => $new_cat_no,
                ],
                "query" => ['bool' => [ESClient::SHOULD => [
                            [ESClient::TERM => ["material_cat_zh.cat_no3" => $material_cat_no]],
                            [ESClient::TERM => ["material_cat_zh.cat_no2" => $material_cat_no]],
                            [ESClient::TERM => ["material_cat_zh.cat_no1" => $material_cat_no]]
            ]]]];
        }
        $es->UpdateByQuery($this->update_dbName, 'goods_' . $lang, $esgoodsdata);
        $es->refresh($this->update_dbName);
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

    public function Update_Attrs($spus, $lang = 'en') {
        $es = new ESClient();
        if (empty($spus)) {
            return false;
        }
        if (is_string($spus)) {
            $product_attr_model = new ProductAttrModel();
            $product_attrs = $product_attr_model->getproduct_attrbyspus([$spus], $lang);

            $id = $spus;
            if (isset($product_attrs[$spus])) {
                $attrs = $product_attrs[$spus];
                $attrs = $this->_setattrs($attrs);
                $data['attrs'] = new stdClass(); // $attrs;
                if ($attrs['spec_attrs']) {
                    $data['spec_attrs'] = $attrs['spec_attrs'];
                } else {
                    $data['spec_attrs'] = [];
                }
            } else {
                $data['spec_attrs'] = [];

                $data['attrs'] = new stdClass();
            }
            $type = $this->tableName . '_' . $lang;
            $es->update_document($this->update_dbName, $type, $data, $id);

            return true;
        } elseif (is_array($spus)) {
            $updateParams = [];
            $updateParams['index'] = $this->update_dbName;
            $updateParams['type'] = 'product_' . $lang;
            $product_attr_model = new ProductAttrModel();
            $product_attrs = $product_attr_model->getproduct_attrbyspus($spus, $lang);
            foreach ($spus as $spu) {
                $data = [];
                if (isset($product_attrs[$spus])) {
                    $attrs = $product_attrs[$spus];
                    $attrs = $this->_setattrs($attrs);
                    $data['attrs'] = new stdClass(); // $attrs;
                    if ($attrs['spec_attrs']) {
                        $data['spec_attrs'] = $attrs['spec_attrs'];
                    } else {
                        $data['spec_attrs'] = [];
                    }
                } else {
                    $data['spec_attrs'] = [];

                    $data['attrs'] = new stdClass();
                }
                $updateParams['body'][] = ['update' => ['_id' => $spu]];
                $updateParams['body'][] = ['doc' => $data];
            }

            if (!empty($updateParams['body'])) {
                $es->bulk($updateParams);
            }

            return true;
        }
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
        $es->update_document($this->update_dbName, $type, $data, $id);


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
        $es->update_document($this->update_dbName, $type, $data, $id);
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
        $es->UpdateByQuery($this->update_dbName, 'goods_' . $lang, $esgoodsdata);
        $es->refresh($this->update_dbName);

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
        $es->update_document($this->update_dbName, $type, $data, $id);
        $this->_delcache();
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

    public function delete_data($spus, $lang = 'en') {
        $es = new ESClient();
        if (empty($spus)) {
            return false;
        }

        $type = 'product_' . $lang;
        if (is_string($spus)) {
            $spu = $spus;

            $data = [];
            $data['onshelf_flag'] = 'N';
            $data['deleted_flag'] = 'Y';
            $data['show_cats'] = [];
            $data['show_cats_nested'] = [];

            $data['status'] = self::STATUS_DELETED;

            $type = $this->tableName . '_' . $lang;
            $es->update_document($this->update_dbName, $type, $data, $spu);
            unset($data['show_cats_nested']);
            $esgoodsdata = [
                "doc" => $data,
                "query" => ['bool' => [ESClient::MUST => [
                            [ESClient::TERM => ["spu" => $spu]],
            ]]]];
            $es->UpdateByQuery($this->update_dbName, 'goods_' . $lang, $esgoodsdata);
        } elseif (is_array($spus)) {

            $updateParams = [];
            $updateParams['index'] = $this->update_dbName;
            $updateParams['type'] = 'product_' . $lang;
            foreach ($spus as $spu) {
                $data = [];

                $data['onshelf_flag'] = 'N';
                $data['deleted_flag'] = 'Y';
                $data['show_cats'] = [];
                $data['show_cats_nested'] = [];
                $data['status'] = self::STATUS_DELETED;
                $updateParams['body'][] = ['update' => ['_id' => $spu]];
                $updateParams['body'][] = ['doc' => $data];
                unset($data['show_cats_nested']);
                $esgoodsdata = [
                    "doc" => $data,
                    "query" => ['bool' => [ESClient::MUST => [
                                [ESClient::TERM => ["spu" => $spu]],
                ]]]];

                $es->UpdateByQuery($this->update_dbName, 'goods_' . $lang, $esgoodsdata);
            }

            $es->bulk($updateParams);
        }
        $es->refresh($this->update_dbName);
        $this->_delcache();
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
        $updateParams['index'] = $this->update_dbName;
        $updateParams['type'] = $type_goods;
        foreach ($skus as $sku) {

            $updateParams['body'][] = ['update' => ['_id' => $sku]];
            $updateParams['body'][] = ['doc' => [
                    'status' => self::STATUS_DELETED,
                    'deleted_flag' => 'Y']];
        }
        $es->bulk($updateParams);
        $this->_delcache();
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
        $updateParams['index'] = $this->update_dbName;
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
        $this->_delcache();
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

    public function Updateshelf($spus, $lang, $onshelf_flag = 'Y', $onshelf_by = 0) {
        $es = new ESClient();
        if (empty($spus)) {
            return false;
        }

        $type = 'product_' . $lang;
        if (is_string($spus)) {
            $spu = $spus;
            $es_product = $es->get($this->update_dbName, $this->tableName . '_' . $lang, $spu, 'show_cats');
            $data = [];
            $data['onshelf_flag'] = $onshelf_flag;
            $data['onshelf_by'] = $onshelf_by;
            $data['show_cats'] = $this->getshowcats($spu, $lang);
            $data['show_cats_nested'] = $data['show_cats'];
            $data['onshelf_at'] = date('Y-m-d H:i:s');
            $type = $this->tableName . '_' . $lang;
            $es->update_document($this->update_dbName, $type, $data, $spu);
            unset($data['show_cats_nested']);
            $esgoodsdata = [
                "doc" => $data,
                "query" => ['bool' => [ESClient::MUST => [
                            [ESClient::TERM => ["spu" => $spu]],
            ]]]];
            $es->UpdateByQuery($this->update_dbName, 'goods_' . $lang, $esgoodsdata);
            if ($data['show_cats']) {
                $show_cat = new ShowCatModel();
                foreach ($data['show_cats'] as $showcat) {
                    $show_cat->UpdateSpuCountByShowCatNo($showcat['cat_no3'], $lang);
                }
            } elseif (!empty($es_product['_source']['show_cats'])) {
                $show_cat = new ShowCatModel();
                foreach ($es_product['_source']['show_cats'] as $showcat) {
                    $show_cat->UpdateSpuCountByShowCatNo($showcat['cat_no3'], $lang);
                }
            }
        } elseif (is_array($spus)) {
            $show_cat_product_model = new ShowCatProductModel();
            $scats = $show_cat_product_model->getshow_catsbyspus($spus, $lang);
            $updateParams = [];
            $updateParams['index'] = $this->update_dbName;
            $updateParams['type'] = 'product_' . $lang;
            foreach ($spus as $spu) {
                $data = [];
                $data['onshelf_flag'] = $onshelf_flag;
                $data['onshelf_by'] = $onshelf_by;
                if (isset($scats[$spu])) {
                    $data['show_cats'] = $scats[$spu];
                } else {
                    $data['show_cats'] = [];
                }
                $es_product = $es->get($this->update_dbName, $this->tableName . '_' . $lang, $spu, 'show_cats');

                rsort($data['show_cats']);
                $data['show_cats_nested'] = $data['show_cats'];
                $data['onshelf_at'] = date('Y-m-d H:i:s');
                $updateParams['body'][] = ['update' => ['_id' => $spu]];
                $updateParams['body'][] = ['doc' => $data];
                unset($data['show_cats_nested']);
                $esgoodsdata = [
                    "doc" => $data,
                    "query" => ['bool' => [ESClient::MUST => [
                                [ESClient::TERM => ["spu" => $spu]],
                ]]]];
                $es->UpdateByQuery($this->update_dbName, 'goods_' . $lang, $esgoodsdata);
                if ($data['show_cats']) {
                    $show_cat = new ShowCatModel();
                    foreach ($data['show_cats'] as $showcat) {
                        $show_cat->UpdateSpuCountByShowCatNo($showcat['cat_no3'], $lang);
                    }
                } elseif (!empty($es_product['_source']['show_cats'])) {
                    $show_cat = new ShowCatModel();
                    foreach ($es_product['_source']['show_cats'] as $showcat) {
                        $show_cat->UpdateSpuCountByShowCatNo($showcat['cat_no3'], $lang);
                    }
                }
            }
            $ret = $es->bulk($updateParams);
        }

        $es->refresh($this->update_dbName);
        $this->_delcache();
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

    public function UpdateSkuCount($spus, $lang) {
        $es = new ESClient();
        if (empty($spus)) {
            return false;
        }

        $type = 'product_' . $lang;
        $gModel = new GoodsModel();
        if (is_string($spus)) {
            $spu = $spus;
            $data = [];
            $skucount = $gModel->where(['spu' => $spu, 'lang' => $lang, 'deleted_flag' => 'N'])->count('id');
            $sku_count = intval($skucount) ? intval($skucount) : '0';
            $data['sku_count'] = strval($sku_count);
            $type = $this->tableName . '_' . $lang;
            $es->update_document($this->update_dbName, $type, $data, $spu);
        } elseif (is_array($spus)) {

            $updateParams = [];
            $updateParams['index'] = $this->update_dbName;
            $updateParams['type'] = 'product_' . $lang;
            foreach ($spus as $spu) {
                $data = [];
                $skucount = $gModel->where(['spu' => $spu, 'lang' => $lang, 'deleted_flag' => 'N'])->count('id');
                $sku_count = intval($skucount) ? intval($skucount) : 0;
                $data['sku_count'] = strval($sku_count);
                $updateParams[] = $data;
            }
            $ret = $es->bulk($updateParams);
        }
        $this->_delcache();
        $es->refresh($this->update_dbName);
        return true;
    }

    /*
     * 对应表
     *
     */

    private function _getKeys() {
        return ['D' => 'spu',
            'E' => 'material_cat_no',
            'F' => 'name',
            'G' => 'show_name',
            'H' => 'bizline',
            'I' => 'brand',
            'J' => 'description',
            'K' => 'tech_paras',
            'L' => 'exe_standard',
            'M' => 'warranty',
            'N' => 'keywords',
            'O' => 'material_cat',
            'P' => 'show_cat',
            'Q' => 'created_at',
            'R' => 'checked_at',
            'S' => 'onshelf_at'
        ];
    }

    private static $xlsSize = 5000;

    /**
     * 产品导出
     * @return string
     */
    public function export($condition = [], $process = '', $lang = '') {
        /** 返回导出进度start */
        $progress_key = 'processed_' . md5(json_encode($condition));
        if (!empty($process)) {
            if (redisExist($progress_key)) {
                $progress_redis = json_decode(redisGet($progress_key), true);
                return $progress_redis['processed'] < $progress_redis['total'] ?
                        ceil($progress_redis['processed'] / $progress_redis['total'] * 100) : 100;
            } else {
                return 100;
            }
        }
        $progress_redis = array('start_time' => time());    //用来记录导入进度信息
        /** 导入进度end */
        set_time_limit(0);  # 设置执行时间最大值


        $count = $this->getCount($condition, $lang);

        $progress_redis['total'] = $count;
        if ($count <= 0) {
            jsonReturn('', ErrorMsg::FAILED, '无数据可导出');
        }
        $date = date('YmdHi', time());
        //单excel显示条数
        //excel输出的起始行
        try {

            for ($p = 0; $p < $count / self::$xlsSize; $p++) {
                $this->Createxls($condition, $lang, $p, $date);
                $progress_redis['processed'] = $p;    //记录导入进度信息
                redisSet($progress_key, json_encode($progress_redis));
            }
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Export failed:' . $e, Log::ERR);
            return false;
        }
        $tmpDir = MYPATH . DS . 'public' . DS . 'tmp' . DS;
        rmdir($tmpDir);
        $dirName = $tmpDir . $date;
        ZipHelper::zipDir($dirName, $dirName . '.zip');
        ZipHelper::removeDir($dirName);    //清除目录
        if (file_exists($dirName . '.zip')) {
            //把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $dirName . '.zip';
            $data['type'] = 'application/zip';
            $data['name'] = pathinfo($dirName . '.zip', PATHINFO_BASENAME);
            $fileId = postfile($data, $url);
            if ($fileId) {
                unlink($dirName . '.zip');
                return array('url' => $fastDFSServer . $fileId['url'] . '?filename=spu导出-' . $fileId['name'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $dirName . '.zip 上传到FastDFS失败', Log::ERR);
            return false;
        }
    }

    /*
     * 删除缓存
     */

    private function _delcache() {

        $redis = new phpredis();
        $treekeys = $redis->getKeys('spu_*');
        $redis->delete($treekeys);
        unset($redis);
        $config = Yaf_Registry::get("config");
        $rconfig = $config->redis->config->toArray();
        $rconfig['dbname'] = 3;
        $redis3 = new phpredis($rconfig);
        $keys = $redis3->getKeys('spu_*');
        $redis3->delete($keys);
        unset($redis3);
    }

    /*
     * 生成excel
     */

    public function Createxls($condition = [], $lang = '', $xlsNum = 1, $date = null) {
        //存储目录

        $tmpDir = MYPATH . DS . 'public' . DS . 'tmp' . DS;
        rmdir($tmpDir);

        $dirName = $tmpDir . $date;
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }

        $localFile = MYPATH . "/public/file/spuTemplate.xls";    //模板
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
        $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
        $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
        $objPHPExcel = $objReader->load($localFile);    //加载文件

        $objSheet = $objPHPExcel->setActiveSheetIndex(0);    //当前sheet
        $objSheet->getColumnDimension('C')->setWidth(25);
        $objSheet->setTitle('SPU导出_' . ($xlsNum + 1) . '_' . $lang);
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);

        $objSheet->setCellValue("O1", '物料分类');
        $objSheet->setCellValue("P1", '展示分类');
        $objSheet->setCellValue("Q1", '创建时间');
        $objSheet->setCellValue("R1", '审核时间');
        $objSheet->setCellValue("S1", '上架时间');
        $objSheet->setCellValue("T1", '审核状态');
        $objSheet->getStyle("T1")->getFont()->setBold(true);    //粗体
        $keys = $this->_getKeys();
        $result = $this->getList($condition, ['spu', 'material_cat_no', 'name', 'show_name', 'brand',
            'keywords', 'exe_standard', 'tech_paras', 'description', 'warranty',
            'status', 'bizline', 'created_at', 'material_cat', 'show_cats', 'checked_at', 'onshelf_at'], $lang, $xlsNum * self::$xlsSize, self::$xlsSize);

        foreach ($result as $j => $item) {
            foreach ($keys as $letter => $key) {



                if ($key === 'brand' && isset($item['brand']['name']) && $item['brand']['name']) {

                    $objSheet->setCellValue($letter . ($j + 2), $item['brand']['name']);
                } elseif ($key === 'material_cat' && $item['material_cat']) {

                    $material_cat = (isset($item['material_cat']['cat_name1']) ? $item['material_cat']['cat_name1'] : '') . '/'
                            . (isset($item['material_cat']['cat_name2']) ? $item['material_cat']['cat_name2'] : '') . '/'
                            . (isset($item['material_cat']['cat_name3']) ? $item['material_cat']['cat_name3'] : '') . '-'
                            . $item['material_cat_no'];
                    $objSheet->setCellValue($letter . ($j + 2), $material_cat);
                } elseif ($key === 'show_cat' && $item['show_cats']) {
                    $show_cats = null;
                    foreach ($item['show_cats'] as $show_cat) {
                        $show_cats .= (isset($show_cat['cat_name1']) ? $show_cat['cat_name1'] : '') . '/'
                                . (isset($item['material_cat']['cat_name2']) ? $show_cat['cat_name2'] : '') . '/'
                                . (isset($show_cat['cat_name3']) ? $show_cat['cat_name3'] : '') . '-'
                                . $show_cat['cat_no3'] . PHP_EOL;
                    }


                    $objSheet->setCellValue($letter . ($j + 2), $show_cats);
                } elseif ($key === 'bizline' && isset($item['bizline']['name']) && $item['bizline']['name']) {

                    $objSheet->setCellValue($letter . ($j + 2), $item['bizline']['name']);
                } elseif (isset($item[$key]) && $item[$key]) {
                    if ($letter == 'C') {
                        $objSheet->setCellValue($letter . ($j + 2), ' ' . $item[$key], PHPExcel_Cell_DataType::TYPE_STRING);
                    } elseif ($letter == 'D') {
                        $objSheet->setCellValue($letter . ($j + 2), $item[$key], PHPExcel_Cell_DataType::TYPE_STRING);
                    } else {
                        $objSheet->setCellValue($letter . ($j + 2), $item[$key]);
                    }
                } else {
                    $objSheet->setCellValue($letter . ($j + 2), ' ');
                }
            }
            $status = '';
            switch ($item['status']) {
                case 'VALID':
                    $status = '通过';
                    break;
                case 'INVALID':
                    $status = '驳回';
                    break;
                case 'CHECKING':
                    $status = '待审核';
                    break;
                case 'DRAFT':
                    $status = '草稿';
                    break;
                default:
                    $status = $item['status'];
                    break;
            }
            $objSheet->setCellValue("T" . ($j + 2), ' ' . $status);
        }
        $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '00000000'),],],];
        $objSheet->getStyle('A1:S' . ($j + 2))->applyFromArray($styleArray);

        $objSheet->freezePaneByColumnAndRow(3, 2);
//
//        for ($i = 65; $i <= 78; $i++) {
//            $objSheet->freezePane(chr($i) . '4');
//        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $objWriter->save($dirName . '/' . $date . '_' . ($xlsNum + 1) . '_' . $lang . '.xls');
        unset($objPHPExcel, $objSheet);

        return true;
    }

}
