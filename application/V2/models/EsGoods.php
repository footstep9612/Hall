<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Esgoods
 *
 * @author zhongyg
 */
class EsGoodsModel extends Model {

    //put your code here
    protected $tableName = 'goods';
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
     * @desc   ES 商品
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
                $field = [$name];
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
     * @author  zhongyg
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @param array $array // 匹配范围
     * @param array $default 默认值
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    private function _getStatus(&$condition, &$body, $qurey_type = ESClient::MATCH_PHRASE, $name = '', $field = '', $array = [], $default = 'VALID') {
        if (!$field) {
            $field = [$name];
        }
        if (isset($condition[$name]) && $condition[$name]) {
            $status = $condition[$name];
            if ($status == 'ALL') {
                $body['query']['bool']['must_not'][] = ['bool' => [ESClient::SHOULD => [
                            [ESClient::MATCH_PHRASE => [$field => self::STATUS_DELETED]],
                            [ESClient::MATCH_PHRASE => [$field => 'CLOSED']]]
                ]];
            } elseif (in_array($status, $array)) {

                $body['query']['bool']['must'][] = [$qurey_type => [$field => $status]];
            } else {
                $body['query']['bool']['must'][] = [$qurey_type => [$field => $default]];
            }
        } else {
            $body['query']['bool']['must'][] = [$qurey_type => [$field => $default]];
        }
    }

    /*
     * 判断搜索状态是否存在
     * 存在 则组合查询
     * @author  zhongyg
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
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
     * @author  zhongyg
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @param array $default 默认值
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    private function _getQureyByBool(&$condition, &$body, $qurey_type = ESClient::MATCH_PHRASE, $name = '', $field = '', $default = 'N') {
        if (!$field) {
            $field = $name;
        }
        if (isset($condition[$name]) && $condition[$name]) {
            $recommend_flag = $condition[$name] == 'Y' ? 'Y' : $default;
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $recommend_flag]];
        }
    }

    /* 条件组合
     * @author  zhongyg
     * @param mix $condition // 搜索条件
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    private function getCondition($condition, $lang = 'en') {
        $body = [];
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'sku');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'spu');
        $this->_getQureyByArr($condition, $body, ESClient::MATCH_PHRASE, 'skus', 'sku');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'show_cat_no', 'show_cats.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'market_area_bn', 'show_cats.all');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'country_bn', 'show_cats.all');


        $mcat_no1 = $this->_getValue($condition, 'mcat_no1');
        $mcat_no2 = $this->_getValue($condition, 'mcat_no2');
        $mcat_no3 = $this->_getValue($condition, 'mcat_no3');

        if ($mcat_no1) {
            $body['query']['bool']['must'][] = [ESClient::WILDCARD => ['material_cat_no' => $mcat_no1 . '*']];
        }
        if ($mcat_no2) {
            $body['query']['bool']['must'][] = [ESClient::WILDCARD => ['material_cat_no' => $mcat_no1 . '*']];
        }
        if ($mcat_no3) {
            $body['query']['bool']['must'][] = [ESClient::WILDCARD => ['material_cat_no' => $mcat_no1 . '*']];
        }

        $this->_getQurey($condition, $body, ESClient::RANGE, 'created_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'checked_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'updated_at');
        $this->_getQurey($condition, $body, ESClient::RANGE, 'onshelf_at');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'name', 'name.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'show_name', 'show_name.ik');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'real_name', 'name.all');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'supplier_name', 'suppliers.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'brand', 'brand.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'source');
        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'cat_name', 'show_cats.all');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'checked_desc');
        $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED',
            'DELETED', 'DRAFT', 'INVALID']);
        $this->_getQureyByBool($condition, $body, ESClient::MATCH_PHRASE, 'recommend_flag', 'recommend_flag', 'N');
        // $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'shelves_status', 'shelves_status', ['VALID', 'INVALID']);
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'model', 'model');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'attrs', 'attrs.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH, 'specs', 'specs.ik');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'created_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'updated_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'checked_by');
        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'onshelf_by');
        $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        if (isset($condition['onshelf_flag']) && $condition['onshelf_flag']) {
            $onshelf_flag = $condition['onshelf_flag'] == 'N' ? 'N' : 'Y';
            if ($condition['onshelf_flag'] === 'A') {

            } elseif ($onshelf_flag === 'N') {
                $body['query']['bool']['must'][] = [ESClient::TERM => ['onshelf_flag' => 'N']];
            } else {
                $body['query']['bool']['must'][] = [ESClient::TERM => ['onshelf_flag' => 'Y']];
            }
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['onshelf_flag' => 'Y']];
        }
        $employee_model = new EmployeeModel();
        if (isset($condition['created_by_name']) && $condition['created_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['created_by_name']);

            foreach ($userids as $created_by) {
                $created_by_bool[] = [ESClient::MATCH_PHRASE => ['created_by' => $created_by]];
            }
            if ($userids) {
                $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $created_by_bool]];
            }
        }
        if (isset($condition['updated_by_name']) && $condition['updated_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['updated_by_name']);
            foreach ($userids as $updated_by) {
                $updated_by_bool[] = [ESClient::MATCH_PHRASE => ['updated_by' => $updated_by]];
            }
            if ($userids) {
                $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $updated_by_bool]];
            }
        }
        if (isset($condition['checked_by_name']) && $condition['checked_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['checked_by_name']);
            foreach ($userids as $checked_by) {
                $checked_by_bool[] = [ESClient::MATCH_PHRASE => ['checked_by' => $checked_by]];
            }

            if ($userids) {
                $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $checked_by_bool]];
            }
        }
        if (isset($condition['keyword']) && $condition['keyword']) {
            $show_name = $condition['keyword'];
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::MATCH => ['name.ik' => $show_name]],
                        [ESClient::TERM => ['sku' => $show_name]],
                        [ESClient::TERM => ['spu' => $show_name]],
                        [ESClient::WILDCARD => ['specs.all' => '*' . $show_name . '*']],
                        [ESClient::WILDCARD => ['brand.all' => '*' . $show_name . '*']],
                        [ESClient::WILDCARD => ['name.all' => '*' . $show_name . '*']],
            ]]];
        }

        return $body;
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getgoods($condition, $_source = null, $lang = 'en') {
        try {
            $body = $this->getCondition($condition, $lang);

            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }
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
            echo json_encode($body, 256);
            return [$es->setbody($body)
                        ->setsort('_score', 'desc')
                        ->setsort('created_at', 'desc')
                        ->setsort('sku', 'desc')
                        ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];
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

    public function getonshelf_flag($skus, $lang = 'en') {
        try {
            $onshelf_flags = $this->table('erui2_goods.show_cat_goods')
                            ->field('sku,max(created_by) as max_created_by'
                                    . ',max(created_at) as max_created_at'
                                    . ',max(updated_by) as min_updated_by'
                                    . ',max(updated_at) as max_updated_at'
                                    . ',max(checked_by) as min_checked_by'
                                    . ',max(checked_by) as min_checked_at')
                            ->where(['sku' => ['in', $skus], 'lang' => $lang, 'onshelf_flag' => 'Y'])
                            ->group('sku')->select();
            $ret = [];
            if ($onshelf_flags) {
                foreach ($onshelf_flags as $onshelf_flag) {
                    $sku = $onshelf_flag['sku'];

                    $ret[$sku] = $onshelf_flag;
                } return $ret;
            }
            return [];
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过SKU获取数据商品产品属性分类等信息列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getproductattrsbyspus($skus, $lang = 'en') {
        try {
            $goodss = $this->where(['sku' => ['in', $skus], 'lang' => $lang])
                    ->select();
            $spus = $skus = [];
            foreach ($goodss as $item) {
                $skus[] = $item['sku'];
                $spus[] = $item['spu'];
            }
            $spus = array_unique($spus);
            $skus = array_unique($skus);
            $espoducmodel = new EsProductModel();
            $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);
            $goods_attr_model = new GoodsAttrModel();
            $goods_attrs = $goods_attr_model->getgoods_attrbyskus($skus, $lang);

            $ret = [];
            foreach ($goodss as $item) {
                $id = $item['id'];
                $body = $item;
                $spu = $item['spu'];
                $sku = $item['sku'];
                $body['meterial_cat'] = $productattrs[$spu]['meterial_cat'];
                $goods_attr = $goods_attrs[$sku];
                $body['attrs'] = json_encode($goods_attr, JSON_UNESCAPED_UNICODE);
                if ($goods_attr[0]['spec_attrs']) {
                    $body['specs'] = $goods_attr[0]['spec_attrs'];
                } else {
                    $body['specs'] = json_encode([]);
                }


                $ret[$id] = $body;
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
     * @param array $item // 语言 zh en ru es
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    private function _findnulltoempty(&$item) {
        foreach ($item as $key => $val) {

            $item[$key] = strval($val);
        }
    }

    /* 通过批量导入商品信息到ES
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function importgoodss($lang = 'en') {
        try {
            ob_clean();
            $count = $this->where(['lang' => $lang])->count('id');


            for ($i = 0; $i < $count; $i += 100) {
                if ($i > $count) {
                    $i = $count;
                }

                echo $i, PHP_EOL, '<BR>';
                ob_flush();
                flush();
                $goods = $this->where(['lang' => $lang])
                                ->limit($i, 100)->select();

                $spus = $skus = [];

                if ($goods) {
                    foreach ($goods as $item) {
                        $skus[] = $item['sku'];
                        $spus[] = $item['spu'];
                    }
                } else {
                    return false;
                }

                $spus = array_unique($spus);
                $skus = array_unique($skus);
                $espoducmodel = new EsProductModel();
                $es = new ESClient();
                $goodsmodel = new GoodsModel();
                if ($lang == 'zh') {
                    $name_locs = $goodsmodel->getNamesBySkus($skus, 'en');
                } else {
                    $name_locs = $goodsmodel->getNamesBySkus($skus, 'zh');
                }
                $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);

                $goods_attach_model = new GoodsAttachModel();
                $attachs = $goods_attach_model->getgoods_attachsbyskus($skus, $lang);

                $goods_attr_model = new GoodsAttrModel();
                $goods_attrs = $goods_attr_model->getgoods_attrbyskus($skus, $lang);

                $goods_supplier_model = new GoodsSupplierModel();
                $suppliers = $goods_supplier_model->getsuppliersbyskus($skus);
                $show_cat_goods_model = new ShowCatGoodsModel();
                $scats = $show_cat_goods_model->getshow_catsbyskus($skus, $lang);

                $onshelf_flags = $this->getonshelf_flag($skus, $lang);
                echo '<pre>';
                foreach ($goods as $item) {
                    $flag = $this->_adddoc($item, $lang, $attachs, $scats, $productattrs, $goods_attrs, $suppliers, $onshelf_flags, $es, $name_locs);

                    print_r($flag);
                    ob_flush();
                    flush();
                }
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    private function _adddoc(&$item, &$lang, &$attachs, &$scats, &$productattrs, &$goods_attrs, &$suppliers, &$onshelf_flags, &$es, &$name_locs) {

        $sku = $id = $item['sku'];
        $spu = $item['spu'];

        $body = $item;
        $product_attr = $productattrs[$spu];

        $body['material_cat'] = $this->_getValue($product_attr, 'material_cat', [], 'string');
        if (!$body['material_cat']) {
            $body['material_cat'] = '{}';
        }

        $body['material_cat_zh'] = $this->_getValue($product_attr, 'material_cat_zh', [], 'string');
        if (!$body['material_cat_zh']) {
            $body['material_cat_zh'] = '{}';
        }
        $body['brand'] = $this->_getValue($product_attr, 'brand', [], 'string');

        $body['brand'] = str_replace("\r", '', $body['brand']);
        $body['brand'] = str_replace("\n", '', $body['brand']);
        $body['brand'] = str_replace("\t", '', $body['brand']);
        if (json_decode($body['brand'], true)) {
            $body['brand'] = json_encode(json_decode($body['brand'], true), 256);
        } elseif ($body['brand']) {
            $body['brand'] = '{"lang": "' . $lang . '", "name": "' . $body['brand'] . '", "logo": "", "manufacturer": ""}';
        } else {
            $body['brand'] = '{"lang": "' . $lang . '", "name": "", "logo": "", "manufacturer": ""}';
        }
        if (json_decode($body['brand'], true)) {
            $body['brand'] = json_encode(json_decode($body['brand'], true), 256);
        }
        if (isset($name_locs[$sku]) && $name_locs[$sku]) {
            $body['name_loc'] = $name_locs[$sku];
        } else {
            $body['name_loc'] = '';
        }
        $body['attachs'] = $this->_getValue($attachs, $sku, [], 'json');
        if (isset($goods_attrs[$sku]) && $goods_attrs[$sku]) {
            $body['attrs'] = json_encode($goods_attrs[$sku], JSON_UNESCAPED_UNICODE);
            if ($goods_attrs[$sku][0]['spec_attrs']) {
                $body['specs'] = $goods_attrs[$item['sku']][0]['spec_attrs'];
            } else {
                $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
            }
        } else {
            $body['attrs'] = json_encode([], JSON_UNESCAPED_UNICODE);
            $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
        }

        if (isset($suppliers[$sku]) && $suppliers[$sku]) {
            $body['suppliers'] = json_encode($suppliers[$sku], 256);
            $body['sppplier_count'] = count($suppliers[$sku]);
        } else {
            $body['suppliers'] = json_encode([], 256);
            $body['sppplier_count'] = 0;
        }



        if ($body['source'] == 'ERUI') {
            $body['sort_order'] = 100;
        } else {
            $body['sort_order'] = 1;
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
        $body['show_cats'] = $this->_getValue($scats, $sku, [], 'json');
        $body['material_cat_no'] = $productattrs[$spu]['material_cat_no'];
        $this->_findnulltoempty($body);
        $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
        if (!isset($flag['create'])) {
            LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);
        }
        return $flag;
    }

    /* 通过批量导入商品信息到ES
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function updategoodss($lang = 'en', $time = '1970-01-01 8:00:00') {
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
                $skus = $this->getSkusByTime($time);
                if ($skus) {
                    $where['_complex']['sku'] = ['in', $skus];
                }
            } else {
                $where = [
                    'lang' => $lang,
                ];
            }
            $count = $this->where($where)->count('id');


            for ($i = 0; $i < $count; $i += 100) {
                if ($i > $count) {
                    $i = $count;
                }
                echo $i, PHP_EOL;

                ob_flush();
                flush();
                $goods = $this->where($where)
                                ->limit($i, 100)->select();

                $spus = $skus = [];

                if ($goods) {
                    foreach ($goods as $item) {
                        $skus[] = $item['sku'];
                        $spus[] = $item['spu'];
                    }
                } else {
                    return false;
                }

                $spus = array_unique($spus);
                $skus = array_unique($skus);
                $espoducmodel = new EsProductModel();
                $es = new ESClient();
                $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);

                $goods_attach_model = new GoodsAttachModel();
                $attachs = $goods_attach_model->getgoods_attachsbyskus($skus, $lang);

                $goods_attr_model = new GoodsAttrModel();
                $goods_attrs = $goods_attr_model->getgoods_attrbyskus($skus, $lang);

                $goods_supplier_model = new GoodsSupplierModel();
                $suppliers = $goods_supplier_model->getsuppliersbyskus($skus);
                $show_cat_goods_model = new ShowCatGoodsModel();
                $scats = $show_cat_goods_model->getshow_catsbyskus($skus, $lang);

                foreach ($goods as $item) {

                    $sku = $id = $item['sku'];
                    $spu = $item['spu'];
                    $this->_findnulltoempty($item);
                    $body = $item;
                    $product_attr = $productattrs[$spu];

                    $body['material_cat'] = $this->_getValue($product_attr, 'material_cat', [], 'string');
                    if (!$body['material_cat']) {
                        $body['material_cat'] = '{}';
                    }
                    $body['attachs'] = $this->_getValue($attachs, $sku, [], 'json');
                    if (isset($goods_attrs[$sku]) && $goods_attrs[$sku]) {
                        $body['attrs'] = json_encode($goods_attrs[$sku], JSON_UNESCAPED_UNICODE);
                        if ($goods_attrs[$sku][0]['spec_attrs']) {
                            $body['specs'] = $goods_attrs[$item['sku']][0]['spec_attrs'];
                        } else {
                            $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        $body['attrs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                        $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                    }

                    $body['suppliers'] = $this->_getValue($suppliers, $sku, [], 'json');
                    if ($body['source'] == 'ERUI') {
                        $body['sort_order'] = 100;
                    } else {
                        $body['sort_order'] = 1;
                    }
                    $body['show_cats'] = $this->_getValue($scats, $sku, [], 'json');
                    $body['material_cat_no'] = $productattrs[$spu]['material_cat_no'];
                    $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
                    if (!isset($flag['create'])) {
                        LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);
                    }
                    print_r($flag);
                    ob_flush();
                    flush();
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

    public function getSkusByTime($time = '1970-01-01 8:00:00') {
        if ($time) {
            $where = [
                //'lang' => $lang,
                '_complex' => [
                    '_logic' => 'or',
                    'created_at' => ['egt' => $time],
                    'updated_at' => ['egt' => $time],
                    'checked_at' => ['egt' => $time],
                ],
            ];
            $show_cat_goods_model = new ShowCatGoodsModel();
            $show_cat_goods = $show_cat_goods_model->where($where)->group('sku')->select();
            $skus = [];
            foreach ($show_cat_goods as $show_cat_good) {
                $skus[] = $show_cat_good['sku'];
            }
            return $skus;
        } else {
            return [];
        }
    }

    /* 条件判
     * @param array $condition  条件
     * @param string $name需要判断的键值
     * @param string $default 默认值
     * @param string $type 判断的类型
     * @param array $arr 状态判断时状态数组
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    private function _getValue($condition, $name, $default = null, $type = 'string', $arr = ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']) {
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
        $material_cat_no = null;
        if (isset($condition['spu'])) {
            $spu = $data['spu'] = $condition['spu'];

            $es_product_model = new EsProductModel();
            $product_info = $es_product_model->getproductattrsbyspus([$spu], $lang);
            $data['material_cat_no'] = $product_info[$spu]['material_cat_no'];
            $data['material_cat'] = $product_info[$spu]['material_cat'];
            $data['brand'] = $product_info[$spu]['brand'];
        } else {
            $data['spu'] = '';
            $data['brand'] = '';
            $data['material_cat_no'] = '';
            $data['material_cat'] = '{}';
        }
        $sku = $data['sku'] = $this->_getValue($condition, 'sku');

        $show_cat_goods_model = new ShowCatGoodsModel();
        $scats = $show_cat_goods_model->getshow_catsbyskus([$sku], $lang);
        $data['show_cats'] = $this->_getValue($scats, $sku, [], 'json');

        $goods_supplier_model = new GoodsSupplierModel();
        $suppliers = $goods_supplier_model->getsuppliersbyskus([$sku]);

        $data['suppliers'] = $this->_getValue($suppliers, $sku, [], 'json');
        $data['qrcode'] = $this->_getValue($condition, 'qrcode');
        $data['name'] = $this->_getValue($condition, 'name');
        $data['show_name'] = $this->_getValue($condition, 'show_name');
        $data['model'] = $this->_getValue($condition, 'model');
        $data['description'] = $this->_getValue($condition, 'description');
        $data['exw_day'] = $this->_getValue($condition, 'exw_day');
        $data['min_pack_naked_qty'] = $this->_getValue($condition, 'min_pack_naked_qty');
        $data['nude_cargo_unit'] = $this->_getValue($condition, 'nude_cargo_unit');
        $data['min_pack_unit'] = $this->_getValue($condition, 'min_pack_unit');
        $data['min_order_qty'] = $this->_getValue($condition, 'min_order_qty');
        $data['purchase_price'] = $this->_getValue($condition, 'purchase_price');
        $data['purchase_price_cur_bn'] = $this->_getValue($condition, 'purchase_price_cur_bn');
        $data['nude_cargo_l_mm'] = $this->_getValue($condition, 'nude_cargo_l_mm');
        $data['nude_cargo_w_mm'] = $this->_getValue($condition, 'nude_cargo_w_mm');
        $data['nude_cargo_h_mm'] = $this->_getValue($condition, 'nude_cargo_h_mm');
        $data['min_pack_l_mm'] = $this->_getValue($condition, 'min_pack_l_mm');
        $data['min_pack_w_mm'] = $this->_getValue($condition, 'min_pack_w_mm');
        $data['min_pack_h_mm'] = $this->_getValue($condition, 'min_pack_h_mm');
        $data['net_weight_kg'] = $this->_getValue($condition, 'net_weight_kg');
        $data['gross_weight_kg'] = $this->_getValue($condition, 'gross_weight_kg');
        $data['compose_require_pack'] = $this->_getValue($condition, 'compose_require_pack');
        $data['pack_type'] = $this->_getValue($condition, 'pack_type');
        $data['name_customs'] = $this->_getValue($condition, 'name_customs');
        $data['hs_code'] = $this->_getValue($condition, 'hs_code');
        $data['tx_unit'] = $this->_getValue($condition, 'tx_unit');
        $data['tax_rebates_pct'] = $this->_getValue($condition, 'tax_rebates_pct');
        $data['regulatory_conds'] = $this->_getValue($condition, 'regulatory_conds');
        $data['commodity_ori_place'] = $this->_getValue($condition, 'commodity_ori_place');
        $data['source'] = $this->_getValue($condition, 'source');
        $data['source_detail'] = $this->_getValue($condition, 'source_detail');
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
     * 添加商品到Es
     * @param array $data 需要更新的数据
     * @param string $lang // 语言 zh en ru es
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function create_data($sku, $lang = 'en') {
        try {
            $es = new ESClient();
            if (is_array($sku)) {
                $goods_model = new GoodsModel();
                $goods = $goods_model->where(['sku' => ['in', $sku], 'lang' => $lang])->select();
            } elseif ($sku) {

                $goods_model = new GoodsModel();
                $goods = $goods_model->where(['sku' => $sku, 'lang' => $lang])->select();
            } else {
                return false;
            }
            $spus = $skus = [];
            if ($goods) {
                foreach ($goods as $item) {
                    $skus[] = $item['sku'];
                    $spus[] = $item['spu'];
                }
            } else {
                return false;
            }

            $spus = array_unique($spus);
            $skus = array_unique($skus);

            $goodsmodel = new GoodsModel();
            if ($lang == 'zh') {
                $name_locs = $goodsmodel->getNamesBySkus($spus, 'en');
            } else {
                $name_locs = $goodsmodel->getNamesBySkus($spus, 'zh');
            }
            $espoducmodel = new EsProductModel();
            $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);

            $goods_attach_model = new GoodsAttachModel();
            $attachs = $goods_attach_model->getgoods_attachsbyskus($skus, $lang);

            $goods_attr_model = new GoodsAttrModel();
            $goods_attrs = $goods_attr_model->getgoods_attrbyskus($skus, $lang);

            $goods_supplier_model = new GoodsSupplierModel();
            $suppliers = $goods_supplier_model->getsuppliersbyskus($skus);
            $show_cat_goods_model = new ShowCatGoodsModel();
            $scats = $show_cat_goods_model->getshow_catsbyskus($skus, $lang);

            $onshelf_flags = $this->getonshelf_flag($skus, $lang);
            foreach ($goods as $item) {
                $this->_adddoc($item, $lang, $attachs, $scats, $productattrs, $goods_attrs, $suppliers, $onshelf_flags, $es, $name_locs);
            }
            $es->refresh($this->dbName);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 更新sku
     * @param array $data 需要更新的数据
     * @param string $sku  SKU
     * @param string $lang // 语言 zh en ru es
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function update_data($data, $sku, $lang = 'en') {
        try {
            $es = new ESClient();
            if (empty($sku)) {
                return false;
            } else {
                $data['sku'] = $sku;
            }

            $body = $this->getInsertCodition($data);
            $id = $sku;
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
     * @param array $sku SKU
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function changestatus($sku, $status = 'VALID', $lang = 'en') {
        try {
            $es = new ESClient();
            if (empty($sku)) {
                return false;
            }
            if (in_array(strtoupper($status), ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {
                $data['status'] = strtoupper($status);
            } else {
                $data['status'] = 'CHECKING';
            }
            $id = $sku;
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

    /* 新增ES
     * @param array $data 需要更新的数据
     * @param string $spu  spu
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getshowcats($sku = null, $lang = 'en') {

        if (empty($sku)) {
            return false;
        }
        $show_cat_goods_model = new ShowCatGoodsModel();
        $scats = $show_cat_goods_model->getshow_catsbyskus($sku, $lang);

        $show_cats = json_encode($scats[$sku], 256);
        return $show_cats;
    }

    /* 新增ES
     * @param string $old_cat_no 需要更新的分类编码
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function update_showcats($old_cat_no, $lang = 'en') {
        if (empty($old_cat_no)) {
            return false;
        }
        $index = $this->dbName;
        $type_goods = 'goods_' . $lang;
        $count_goods = $this->setbody(['query' => [
                        ESClient::MATCH_PHRASE => [
                            "show_cats" => $old_cat_no
                        ]
            ]])->count($index, $type_goods);

        for ($i = 0; $i < $count_goods['count']; $i += 100) {
            $ret = $this->setbody(['query' => [
                            ESClient::MATCH_PHRASE => [
                                "show_cats" => $old_cat_no
                            ]
                ]])->search($index, $type_goods, $i, 100);
            $updateParams = array();
            $updateParams['index'] = $this->dbName;
            $updateParams['type'] = $type_goods;
            if ($ret) {
                foreach ($ret['hits']['hits'] as $item) {
                    $spu = $item['_source']['spu'];
                    $updateParams['body'][] = ['update' => ['_id' => $item['_id']]];
                    $updateParams['body'][] = ['doc' => $this->getshowcats($spu, $lang)];
                }
                $this->bulk($updateParams);
            }
        }

        return true;
    }

    /* 更新属性规格
     * @param string $sku SKU
     * @param string $lang 语言
     * @param array $product_attrs 属性
     * @param array $product_specs 规格
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function Update_Attrs($sku, $lang = 'en', $product_attrs = [], $product_specs = []) {
        $es = new ESClient();
        if (empty($sku)) {
            return false;
        }

        $goods_attr_model = new GoodsAttrModel();
        $goods_attrs = $goods_attr_model->getgoods_attrbyskus([$sku], $lang);


        if (isset($goods_attrs[$sku][0]['spec_attrs'])) {
            $body['specs'] = $goods_attrs[$sku][0]['spec_attrs'];
        } else {
            $body['specs'] = '[]';
        }

        $data['attrs'] = json_encode($goods_attrs[$sku], JSON_UNESCAPED_UNICODE);
        $id = $sku;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);
        return true;
    }

    /* 更新SKU
     * @param string $sku SKU
     * @param string $lang 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function Update_Attachs($sku, $lang = 'en') {
        $es = new ESClient();
        if (empty($sku)) {
            return false;
        }
        $goods_attach_model = new GoodsAttachModel();
        $attachs = $goods_attach_model->getgoods_attachsbyskus([$sku], $lang);

        if (isset($attachs[$sku])) {
            $data['attachs'] = json_encode($attachs[$sku], 256);
        } else {
            $data['attachs'] = '[]';
        }
        $id = $sku;
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

    public function delete_data($sku, $lang = 'en') {
        $es = new ESClient();
        if (empty($sku)) {
            return false;
        }
        $data['status'] = self::STATUS_DELETED;
        $data['deleted_flag'] = 'Y';
        $id = $sku;
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
        $type_goods = 'goods_' . $lang;
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

    public function BatchUpdate_Attachs($skus, $lang = 'en') {
        $es = new ESClient();
        if (empty($skus)) {
            return false;
        }
        if (is_string($skus)) {
            $skus = [$skus];
        }
        $goods_attach_model = new GoodsAttachModel();
        $attachs = $goods_attach_model->getgoods_attachsbyskus($skus, $lang);
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
            foreach ($skus as $sku) {
                if (isset($attachs[$sku])) {
                    $sku_attachs = json_encode($attachs[$sku], 256);
                } else {
                    $sku_attachs = '[]';
                }
                $updateParams['body'][] = ['update' => ['_id' => $sku]];
                $updateParams['body'][] = ['doc' => ['attachs' => $sku_attachs]];
            }
            $es->bulk($updateParams);
        }
        return true;
    }

}
