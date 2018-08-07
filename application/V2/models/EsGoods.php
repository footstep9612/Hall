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
    protected $dbName = 'erui_goods'; //数据库名称
    protected $update_dbName = 'erui_goods'; //数据库名称

    const STATUS_DELETED = 'DELETED';

    public function __construct($str = '') {
        parent::__construct($str = '');
        $model = new EsVersionModel();
        $version = $model->getVersion();

        if ($version) {
            $this->update_dbName = $this->dbName . '_' . $version['update_version'];
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
        if ($lang == 'zh') {
            $analyzer = 'ik';
            $analyzer_loc = 'en';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
            $analyzer_loc = 'ik';
        } else {
            $analyzer = 'ik';
            $analyzer_loc = 'en';
        }

        if (!empty($condition['product_name'])) {
            $product_name = trim($condition['product_name']);
            $product_model = new ProductModel();
            $products = $product_model
                    ->field(['spu'])
                    ->where(['name' => ['like', '%' . $product_name . '%']])
                    ->select();
            $spus = [];
            if ($products) {
                foreach ($products as $product) {
                    $spus[] = $product['spu'];
                }
            }
            if ($spus) {
                $condition['spus'] = $spus;
            } else {
                $condition['spus'] = ['null'];
            }
        }

        //sku&&name合并搜索
        if (!empty($condition['name'])) {
            if(preg_match("/^\d*$/",$condition['name']) && mb_strlen($condition['name']) == 16) {
                $condition['sku'] = $condition['name'];
                unset($condition['name']);
            }else {
                $condition['name'] = $condition['name'];
            }
        }

        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        ESClient::getQurey($condition, $body, ESClient::TERM, 'sku');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'spu');
        if (isset($condition['skus']) && $condition['skus']) {
            $name_arr = $condition['skus'];
            $body['query']['bool']['must'][] = [ESClient::TERMS => ['sku' => $name_arr]];
        }
        if (isset($condition['spus']) && $condition['spus']) {
            $name_arr = $condition['spus'];
            $body['query']['bool']['must'][] = [ESClient::TERMS => ['spu' => $name_arr]];
        }
//        if (isset($condition['show_cat_no']) && $condition['show_cat_no']) {
//            $show_cat_no = trim($condition['show_cat_no']);
//            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
//                        [ESClient::TERM => ['show_cats.cat_no1' => $show_cat_no]],
//                        [ESClient::TERM => ['show_cats.cat_no2' => $show_cat_no]],
//                        [ESClient::TERM => ['show_cats.cat_no3' => $show_cat_no]],
//            ]]];
//        }
        ESClient::getQurey($condition, $body, ESClient::TERM, 'market_area_bn', 'show_cats.market_area_bn');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'country_bn', 'show_cats.country_bn');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'scat_no1', 'show_cats.cat_no1');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'scat_no2', 'show_cats.cat_no2');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'scat_no3', 'show_cats.cat_no3');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'mcat_no1', 'material_cat.cat_no1');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'mcat_no2', 'material_cat.cat_no2');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'mcat_no3', 'material_cat.cat_no3');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'bizline_id', 'bizline_id');




        ESClient::getQurey($condition, $body, ESClient::TERM, 'image_count', 'image_count');
        ESClient::getQurey($condition, $body, ESClient::RANGE, 'created_at');
        ESClient::getQurey($condition, $body, ESClient::RANGE, 'checked_at');
        ESClient::getQurey($condition, $body, ESClient::RANGE, 'updated_at');
        ESClient::getQurey($condition, $body, ESClient::RANGE, 'onshelf_at');
        if (isset($condition['price_validity']) && $condition['price_validity'] === 'Y') {
            $condition['pricevalidity_start'] = '2017-01-01';
            $condition['pricevalidity_end'] = date('Y-m-d', strtotime('+30 days'));
            ESClient::getQurey($condition, $body, ESClient::RANGE, 'pricevalidity', 'costprices.price_validity');
            unset($condition['pricevalidity_end'], $condition['pricevalidity_start']);
        }
        ESClient::getQurey($condition, $body, ESClient::RANGE, 'price_validity', 'costprices.price_validity');
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'name', 'name.all');
        ESClient::getQurey($condition, $body, ESClient::MATCH, 'show_name', 'show_name.' . $analyzer);
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'real_name', 'name.all');
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'supplier_name', 'suppliers.supplier_name.all');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'supplier_id', 'suppliers.supplier_id');
        // ESClient::getQurey($condition, $body, ESClient::MATCH, 'brand', 'brand.'.$analyzer);
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'brand', 'brand.name.all');
        ESClient::getQurey($condition, $body, ESClient::MATCH_PHRASE, 'source');
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'cat_name', 'show_cats.all');
        ESClient::getQurey($condition, $body, ESClient::MATCH, 'checked_desc');
        ESClient::getStatus($condition, $body, ESClient::TERM, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED',
            'DELETED', 'DRAFT', 'INVALID']);
        ESClient::getQureyByBool($condition, $body, ESClient::TERM, 'recommend_flag', 'recommend_flag', 'N');
        //ESClient::getStatus($condition, $body, ESClient::MATCH_PHRASE, 'shelves_status', 'shelves_status', ['VALID', 'INVALID']);
        ESClient::getQurey($condition, $body, ESClient::MATCH_PHRASE, 'model', 'model');
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
        ESClient::getQurey($condition, $body, ESClient::TERM, 'created_by');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'updated_by');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'checked_by');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'onshelf_by');

        if (empty($condition['deleted_flag'])) {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => trim($condition['deleted_flag']) === 'Y' ? 'Y' : 'N']];
        }

        if (isset($condition['onshelf_flag']) && $condition['onshelf_flag']) {
            $onshelf_flag = trim($condition['onshelf_flag']) == 'N' ? 'N' : 'Y';
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
            $userids = $employee_model->getUseridsByUserName(trim($condition['created_by_name']));

            foreach ($userids as $created_by) {
                $created_by_bool[] = [ESClient::TERM => ['created_by' => $created_by]];
            }
            if ($userids) {
                $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $created_by_bool]];
            }
        }
        if (isset($condition['updated_by_name']) && $condition['updated_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['updated_by_name']));
            foreach ($userids as $updated_by) {
                $updated_by_bool[] = [ESClient::TERM => ['updated_by' => $updated_by]];
            }
            if ($userids) {
                $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $updated_by_bool]];
            }
        }
        if (isset($condition['checked_by_name']) && $condition['checked_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['checked_by_name']));
            foreach ($userids as $checked_by) {
                $checked_by_bool[] = [ESClient::TERM => ['checked_by' => $checked_by]];
            }

            if ($userids) {
                $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $checked_by_bool]];
            }
        }
        if (isset($condition['keyword']) && $condition['keyword']) {
            $show_name = trim($condition['keyword']);
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        //  [ESClient::MATCH => ['name.' . $analyzer => ['query' => $show_name, 'boost' => 7]]],
                        //[ESClient::MATCH => ['show_name.' . $analyzer => ['query' => $show_name, 'boost' => 7]]],
                        [ESClient::TERM => ['sku' => ['value' => $show_name, 'boost' => 100]]],
                        //  [ESClient::MATCH => ['model.' . $analyzer => ['query' => $show_name, 'boost' => 1, 'operator' => 'and']]],
                        [ESClient::TERM => ['spu' => ['value' => $show_name, 'boost' => 90]]],
                        [ESClient::TERM => ['attr.spec_attrs.value.lower' => ['value' => strtolower($show_name), 'boost' => 1,]]],
                        [ESClient::TERM => ['attr.spec_attrs.name.lower' => ['value' => strtolower($show_name), 'boost' => 1,]]],
                        [ESClient::TERM => ['brand.name.lower' => ['value' => strtolower($show_name), 'boost' => 5,]]],
                        [ESClient::TERM => ['model.lower' => ['value' => strtolower($show_name), 'boost' => 9]]],
                        [ESClient::MATCH => ['name.' . $analyzer => ['query' => $show_name, 'boost' => 9, 'operator' => 'and']]],
                        [ESClient::MATCH => ['name_loc.' . $analyzer_loc => ['query' => $show_name, 'boost' => 7, 'operator' => 'and']]],
                        [ESClient::MATCH => ['show_name_loc.' . $analyzer_loc => ['query' => $show_name, 'boost' => 7, 'operator' => 'and']]],
            ]]];
        }

        return $body;
    }


    public function getStatics($condition)
    {
        $en = $this->getgoods($condition, null)[0]['hits']['total'];
        $zh = $this->getgoods($condition, null, 'zh')[0]['hits']['total'];
        $ru = $this->getgoods($condition, null, 'ru')[0]['hits']['total'];
        $es = $this->getgoods($condition, null, 'es')[0]['hits']['total'];

        return [$en, $zh, $ru, $es];
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

            $es->setbody($body);
            if ($_source) {
                $es->setfields($_source);
            }
            if (isset($condition['keyword']) && $condition['keyword']) {
                $es->setsort('_score', 'desc')->setsort('created_at', 'desc')->setsort('sku', 'desc');
                $es->setpreference('_primary_first');
            } else {
                $es->setsort('created_at', 'desc')
                        ->setsort('sku', 'desc');
            }

            return [$es->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];
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
     * @desc   ES 商品
     */

    public function getskucountBySpus($spus, $lang = 'en') {
        try {
            $condition['spus'] = $spus;
            $body = $this->getCondition($condition, $lang);
            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }

            $es = new ESClient();
            $es->setaggs('brand.name.all', 'spu', 'terms', 0);
            return $es->setbody($body)->search($this->dbName, $this->tableName . '_' . $lang, 0, 0);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 获取SKU总数
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
     * 获取供应商及对应供应商SKU数量总数
     * @param array $condition //搜索条件
     * @param string $lang // 语言
     * @author  zhongyg
     * @date    2018-6-11 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getSupplieridsAndSkuCountByCondition($condition, $lang) {
        $body = $this->getCondition($condition);
        $es = new ESClient();
        $es->setbody($body);

        $es->setaggs('suppliers.supplier_id', 'supplier_id', 'terms', 0);
        $es->setfields(['spu']);
        $ret = $es->search($this->dbName, $this->tableName . '_' . $lang, 0, 1);
        $result = [];
        $supplier_ids = [];
        if (isset($ret['aggregations']['supplier_id']['buckets'])) {

            foreach ($ret['aggregations']['supplier_id']['buckets'] as $SupplieridSkuCount) {

                $result[$SupplieridSkuCount['key']] = $SupplieridSkuCount['doc_count'];
                $supplier_ids[] = $SupplieridSkuCount['key'];
            }
        }
        return [$result, $supplier_ids];
    }

    /*
     * 获取SKU总数
     * @param array $condition //搜索条件
     * @param string $lang // 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getSkuCountBySpu($spu, $lang = 'en') {

        try {

            $where = ['spu' => $spu,
                'lang' => $lang,
                'deleted_flag' => 'N',
                'status' => ['in', ['DRAFT', 'CHECKING', 'INVALID']]
            ];

            return $this->where($where)->count();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    /*
     * 获取SKU总数
     * @param array $spus //搜索条件
     * @param string $lang // 语言
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getStatusSkuCountBySpu($spus, $lang = 'en') {

        try {

            $where = ['spu' => ['in', $spus],
                'lang' => $lang,
                'deleted_flag' => 'N',
                'status' => ['in', ['DRAFT', 'CHECKING', 'INVALID', 'VALID']]
            ];
            $data = $this->field('spu,sum(if (`status`=\'DRAFT\',1,0)) as draft_count,'
                            . 'sum(if (`status`=\'CHECKING\',1,0)) as checking_count,'
                            . 'sum(if (`status`=\'INVALID\',1,0)) as invalid_count,'
                            . 'sum(if (`status`=\'VALID\',1,0)) as valid_count')
                    ->where($where)
                    ->group('spu')
                    ->select();
            $ret = [];
            foreach ($data as $item) {
                $ret[$item['spu']] = $item;
            }
            return $ret;
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
            $onshelf_flags = $this->table('erui_goods.show_cat_goods')
                            ->field('sku,max(created_by) as max_created_by'
                                    . ',max(created_at) as max_created_at'
                                    . ',max(updated_by) as max_updated_by'
                                    . ',max(updated_at) as max_updated_at'
                                    . ',max(checked_by) as max_checked_by'
                                    . ',max(checked_by) as max_checked_at')
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
            if ($val === null) {
                $item[$key] = '';
            } else {
                $item[$key] = $val;
            }
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

    public function importgoodss($lang = 'en', $goods_skus = [], $deleted_flag = null) {
        try {
            ob_clean();
            $max_id = 0;
            $where_count = ['lang' => $lang, 'deleted_flag' => 'N', 'id' => ['gt', 0]];
            if ($goods_skus) {
                $where_count['sku'] = ['in', $goods_skus];
            }
            if ($deleted_flag) {
                $where_count['deleted_flag'] = $deleted_flag == 'Y' ? 'Y' : 'N';
            }
            $count = $this->where($where_count)->count('id');
            $espoducmodel = new EsProductModel();
            $es = new ESClient();
            $goodsmodel = new GoodsModel();
            $product_model = new ProductModel();
            $goods_attach_model = new GoodsAttachModel();
            $goods_cost_price_model = new GoodsCostPriceModel();
            $goods_attr_model = new GoodsAttrModel();
            $goods_supplier_model = new GoodsSupplierModel();
            $show_cat_goods_model = new ShowCatGoodsModel();
            echo '共有', $count, '条记录需要导入!', PHP_EOL;
            for ($i = 0; $i < $count; $i += 100) {
                if ($i > $count) {
                    $i = $count;
                }

                echo $i, PHP_EOL, '<BR>';
                //  usleep(300);
                ob_flush();
                flush();

                $time1 = microtime(true);
                $where = ['lang' => $lang];

                if ($max_id === 0) {
                    $where['id'] = ['gt', 0];
                } else {
                    $where['id'] = ['gt', $max_id];
                }
                if ($goods_skus) {
                    $where['sku'] = ['in', $goods_skus];
                }
                if ($deleted_flag) {
                    $where['deleted_flag'] = $deleted_flag == 'Y' ? 'Y' : 'N';
                }
                $goods = $this->where($where)->limit(0, 100)->order('id ASC')->select();
                $nonamespus = $spus = $skus = [];

                if ($goods) {
                    foreach ($goods as $item) {
                        $skus[] = $item['sku'];
                        $spus[] = $item['spu'];
                        if (empty($item['name']) || empty($item['show_name'])) {
                            $nonamespus[] = $item['spu'];
                        }
                    }
                } else {
                    return false;
                }

                $spus = array_unique($spus);
                $skus = array_unique($skus);

                if ($lang == 'zh') {
                    $name_locs = $goodsmodel->getNamesBySkus($skus, 'en');
                } else {
                    $name_locs = $goodsmodel->getNamesBySkus($skus, 'zh');
                }
                $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);


                $product_names = $product_model->getProductNames($nonamespus, $lang);

                $attachs = $goods_attach_model->getgoods_attachsbyskus($skus, $lang);


                $costprices = $goods_cost_price_model->getCostPricesBySkus($skus);


                $goods_attrs = $goods_attr_model->getgoods_attrbyskus($skus, $lang);


                $suppliers = $goods_supplier_model->getsuppliersbyskus($skus);

                $scats = $show_cat_goods_model->getshow_catsbyskus($skus, $lang);

                $onshelf_flags = $this->getonshelf_flag($skus, $lang);
                echo '<pre>';

                $updateParams = [];
                $updateParams['index'] = $this->update_dbName;
                $updateParams['type'] = $this->tableName . '_' . $lang;
                foreach ($goods as $key => $item) {
//                    $time2 = microtime(true);
                    $flag = $this->_adddoc($item, $lang, $attachs, $scats, $productattrs, $goods_attrs, $suppliers, $onshelf_flags, $es, $name_locs, $costprices, $product_names, false);
                    if ($key === 99) {
                        $max_id = $item['id'];
                    }
//                    echo microtime(true) - $time2, "\r\n";
                    print_r($flag);
                    ob_flush();
                    flush();
                }

                echo microtime(true) - $time1, "\r\n";
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    private function _adddoc(&$item, &$lang, &$attachs, &$scats, &$productattrs, &$goods_attrs, &$suppliers, &$onshelf_flags, &$es, &$name_locs, &$costprices = [], &$product_names = [], $is_body = false) {

        $sku = $id = $item['sku'];
        $spu = $item['spu'];

        $body = $item;
        $body['name'] = htmlspecialchars_decode($item['name']);
        $body['show_name'] = htmlspecialchars_decode($item['show_name']);
        $product_attr = $productattrs[$spu];
        $es_goods = null;

        if ($es->exists($this->update_dbName, $this->tableName . '_' . $lang, $id)) {

            $es_goods = $es->get($this->update_dbName, $this->tableName . '_' . $lang, $id, 'suppliers,min_order_qty,exw_days,min_pack_unit');
        }
        if (isset($product_attr['material_cat']) && $product_attr['material_cat']) {
            $body['material_cat'] = $product_attr['material_cat'];
        } else {
            $body['material_cat'] = new stdClass();
        }

        if (isset($product_attr['material_cat_zh']) && $product_attr['material_cat_zh']) {
            $body['material_cat_zh'] = $product_attr['material_cat_zh'];
        } else {
            $body['material_cat_zh'] = new stdClass();
        }
        if (isset($product_attr['bizline_id']) && $product_attr['bizline_id']) {
            $body['bizline_id'] = $product_attr['bizline_id'];
        } else {
            $body['bizline_id'] = new stdClass();
        }

        if (isset($product_attr['bizline']) && $product_attr['bizline']) {
            $body['bizline'] = $product_attr['bizline'];
        } else {
            $body['bizline'] = new stdClass();
        }

        if (isset($scats[$sku])) {

            $show_cats = $scats[$sku];
            rsort($show_cats);
            $body['show_cats'] = $show_cats;
        } else {
            $body['show_cats'] = [];
        }
        if (isset($costprices[$sku])) {

            $cost_prices = $costprices[$sku];
            rsort($cost_prices);
            $body['costprices'] = $cost_prices;
        } else {
            $body['costprices'] = [];
        }

        $body['brand'] = $this->_getValue($product_attr, 'brand', [], 'string');

        $body['brand'] = str_replace("\t", '', str_replace("\n", '', str_replace("\r", '', $body['brand'])));

        if (json_decode($body['brand'], true)) {

            $body['brand'] = json_decode($body['brand'], true);
        } elseif ($body['brand']) {
            $body['brand'] = ['lang' => $lang, 'name' => trim($body['brand']), 'logo' => '', 'manufacturer' => ''];
        } else {
            $body['brand'] = ['lang' => $lang, 'name' => '', 'logo' => '', 'manufacturer' => ''];
        }
        if (isset($name_locs[$sku]) && $name_locs[$sku]) {
            $body['name_loc'] = htmlspecialchars_decode($name_locs[$sku]);
        } else {
            $body['name_loc'] = '';
        }
        if (empty($body['show_name'])) {
            if (isset($product_names[$spu]['show_name']) && $product_names[$spu]['show_name']) {
                $body['show_name'] = $product_names[$spu]['show_name'];
            }
        }
        if (empty($body['name'])) {
            if (isset($product_names[$spu]['name']) && $product_names[$spu]['name']) {
                $body['name'] = $product_names[$spu]['name'];
            }
        }

        if (isset($attachs[$sku])) {
            $body['attachs'] = json_encode($attachs[$sku], 256);
            $body['image_count'] = isset($attachs[$sku]['BIG_IMAGE']) ? count($attachs[$sku]['BIG_IMAGE']) : 0;
        } else {
            $body['attachs'] = '[]';
            $body['image_count'] = 0;
        }
        $body['image_count'] = strval($body['image_count']);
        if (isset($goods_attrs[$sku]) && $goods_attrs[$sku]) {
            $attrs = $goods_attrs[$sku];
            $attrs = $this->_setattrs($attrs);
            $body['attrs'] = $attrs;
            if ($attrs['spec_attrs']) {
                $body['spec_attrs'] = $attrs['spec_attrs'];
            } else {
                $body['spec_attrs'] = [];
            }
        } else {
            $body['attrs'] = new stdClass();
            $body['spec_attrs'] = [];
            //json_encode([], JSON_UNESCAPED_UNICODE);
        }

        if (isset($suppliers[$id]) && $suppliers[$id]) {
            $body['suppliers'] = $suppliers[$id];

            $body['supplier_count'] = count($suppliers[$id]);
        } else {
            $body['suppliers'] = [];

            $body['supplier_count'] = 0;
        }
        $body['supplier_count'] = strval($body['supplier_count']);
        if ($es_goods && ($es_goods['_source']['suppliers'] != $body['suppliers'] || $es_goods['_source']['min_order_qty'] != $body['min_order_qty'] || $es_goods['_source']['exw_days'] != $body['exw_days'] || $es_goods['_source']['min_pack_unit'] != $body['min_pack_unit'] )) {
            $this->UpdateSPU($spu, $lang);
        }

        if ($body['source'] == 'ERUI') {
            $body['sort_order'] = 100;
        } else {
            $body['sort_order'] = 1;
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

        $body['material_cat_no'] = $productattrs[$spu]['material_cat_no'];
        $this->_findnulltoempty($body);

        if ($es_goods && $is_body) {

            return ['update', $body];
        } elseif (!$es_goods && $is_body) {
            return ['create', $body];
        } elseif ($es_goods) {
            $flag = $es->update_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);
        } else {
            $flag = $es->add_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);
        }
        if (!isset($flag['_version'])) {
            LOG::write("FAIL:" . $item['id'] . "\r\n" . var_export($flag, true), LOG::ERR);
            LOG::write("FAIL:" . $item['id'] . "\r\n" . json_encode($body, 256), LOG::ERR);
        }

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
            foreach ($attrs_arr as $name => $value) {
                $ret[] = ['name' => $this->_filter($name),
                    'value' => $this->_filter($value)];
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

    public function UpdateSPU($spu, $lang) {
        try {

            $es_product_model = new EsProductModel();
            $es_product_model->create_data($spu, $lang);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
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
                    $flag = $es->add_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);
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
            if (is_array($sku) && !empty($sku)) {
                $goods_model = new GoodsModel();
                $goods = $goods_model->where(['sku' => ['in', $sku], 'deleted_flag' => 'N', 'lang' => $lang])
                        ->order('id asc')
                        ->select();
            } elseif ($sku) {

                $goods_model = new GoodsModel();
                $goods = $goods_model->where(['sku' => $sku, 'deleted_flag' => 'N', 'lang' => $lang])
                        ->order('id asc')
                        ->select();
            } else {
                return false;
            }
            $spus = $skus = [];
            if ($goods) {
                foreach ($goods as $item) {
                    $skus[] = $item['sku'];
                    $spus[] = $item['spu'];
                    if (empty($item['name']) || empty($item['show_name'])) {
                        $nonamespus[] = $item['spu'];
                    }
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
            $product_model = new ProductModel();
            $product_names = $product_model->getProductNames($nonamespus, $lang);
            $goods_attach_model = new GoodsAttachModel();
            $attachs = $goods_attach_model->getgoods_attachsbyskus($skus, $lang);

            $goods_attr_model = new GoodsAttrModel();
            $goods_attrs = $goods_attr_model->getgoods_attrbyskus($skus, $lang);

            $goods_supplier_model = new GoodsSupplierModel();
            $suppliers = $goods_supplier_model->getsuppliersbyskus($skus);
            $show_cat_goods_model = new ShowCatGoodsModel();
            $scats = $show_cat_goods_model->getshow_catsbyskus($skus, $lang);
            $goods_cost_price_model = new GoodsCostPriceModel();
            $costprices = $goods_cost_price_model->getCostPricesBySkus($skus);
            $onshelf_flags = $this->getonshelf_flag($skus, $lang);

            foreach ($goods as $item) {
                $this->_adddoc($item, $lang, $attachs, $scats, $productattrs, $goods_attrs, $suppliers, $onshelf_flags, $es, $name_locs, $costprices, $product_names);
            }

            $es->refresh($this->update_dbName);
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
            $flag = $es->update_document($this->update_dbName, $this->tableName . '_' . $lang, $body, $id);
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

    public function changestatus($skus, $status = 'VALID', $lang = 'en', $checked_by = '') {
        $es = new ESClient();
        if (empty($skus)) {
            return false;
        }

        try {
            $type = 'goods_' . $lang;
            $spus = [];
            if (is_string($skus)) {
                $goods_model = new GoodsModel();
                $goods_info = $goods_model->field('deleted_flag,checked_by,checked_at,updated_by,updated_at,status,sku,spu')
                                ->where(['sku' => $skus, 'lang' => $lang])->find();
                $sku = $skus;
                $data = [];
                $data['deleted_flag'] = $goods_info['deleted_flag'];
                $data['checked_by'] = $goods_info['checked_by'];
                $data['checked_at'] = $goods_info['checked_at'];
                $data['updated_by'] = $goods_info['updated_by'];
                $data['updated_at'] = $goods_info['updated_at'];
                $data['status'] = $goods_info['status'];
                $type = $this->tableName . '_' . $lang;
                $spus[] = $goods_info['spu'];
                $es->update_document($this->update_dbName, $type, $data, $sku);
            } elseif (is_array($skus)) {


                $updateParams = [];
                $updateParams['index'] = $this->update_dbName;
                $updateParams['type'] = 'goods_' . $lang;
                $goods_model = new GoodsModel();
                $goods_list = $goods_model->field('deleted_flag,checked_by,checked_at,updated_by,updated_at,status,sku,spu')
                                ->where(['sku' => ['in', $skus], 'lang' => $lang])->select();

                foreach ($goods_list as $goods_info) {
                    $data = [];
                    $data['deleted_flag'] = strval($goods_info['deleted_flag']);
                    $data['checked_by'] = intval($goods_info['checked_by']);
                    $data['checked_at'] = strval($goods_info['checked_at']);
                    $data['updated_by'] = intval($goods_info['updated_by']);
                    $data['updated_at'] = strval($goods_info['updated_at']);
                    $data['status'] = strval($goods_info['status']);
                    $spus[] = $goods_info['spu'];
                    $updateParams['body'][] = ['update' => ['_id' => $goods_info['sku']]];
                    $updateParams['body'][] = ['doc' => $data];
                }

                if (!empty($updateParams['body'])) {
                    $es->bulk($updateParams);
                }
            }

            if ($spus && $status = 'VALID') {
                $esproduct_model = new EsProductModel();
                $esproduct_model->Update_Attrs($spus, $lang);
            }
            $es->refresh($this->update_dbName);
            return true;
        } catch (Exception $ex) {

            Log::write($ex->getMessage());
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
        $scats = $show_cat_goods_model->getshow_catsbyskus([$sku], $lang);

        $show_cats = $scats[$sku];
        rsort($show_cats);
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
        $es = new ESClient();
        $index = $this->update_dbName;
        $type_goods = 'goods_' . $lang;
        $count_goods = $es->setbody(["query" => ['bool' => [ESClient::SHOULD => [
                                [ESClient::TERM => ["show_cats.cat_no3" => $old_cat_no]],
                                [ESClient::TERM => ["show_cats.cat_no2" => $old_cat_no]],
                                [ESClient::TERM => ["show_cats.cat_no1" => $old_cat_no]]
                    ]]]])->count($index, $type_goods);

        for ($i = 0; $i < $count_goods['count']; $i += 100) {
            $ret = $es->setbody(["query" => ['bool' => [ESClient::SHOULD => [
                                    [ESClient::TERM => ["show_cats.cat_no3" => $old_cat_no]],
                                    [ESClient::TERM => ["show_cats.cat_no2" => $old_cat_no]],
                                    [ESClient::TERM => ["show_cats.cat_no1" => $old_cat_no]]
                        ]]]])->search($index, $type_goods, $i, 100);
            $updateParams = array();
            $updateParams['index'] = $this->update_dbName;
            $updateParams['type'] = $type_goods;
            if ($ret) {
                foreach ($ret['hits']['hits'] as $item) {
                    $sku = $item['_source']['sku'];
                    $updateParams['body'][] = ['update' => ['_id' => $item['_id']]];
                    $updateParams['body'][] = ['doc' => ['show_cats' => $this->getshowcats($sku, $lang)]];
                }
                $es->bulk($updateParams);
            }
        }
        $es->refresh($this->update_dbName);
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
        $es->update_document($this->update_dbName, $type, $data, $id);
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
            $es->update_document($this->update_dbName, $type, $data, $id);
        } else {
            $type = $this->tableName . '_en';
            $es->update_document($this->update_dbName, $type, $data, $id);
            $type = $this->tableName . '_es';
            $es->update_document($this->update_dbName, $type, $data, $id);
            $type = $this->tableName . '_ru';
            $es->update_document($this->update_dbName, $type, $data, $id);
            $type = $this->tableName . '_es';
            $es->update_document($this->update_dbName, $type, $data, $id);
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

    public function delete_data($skus, $lang = 'en') {
        $es = new ESClient();
        if (empty($skus)) {
            return false;
        }
        $type = 'goods_' . $lang;

        if (is_string($skus)) {

            $goods_supplier_model = new GoodsSupplierModel();
            $suppliers = $goods_supplier_model->getsuppliersbyskus([$skus]);
            $goods = $this->field('spu')->where(['sku' => $skus, 'lang' => $lang])->find();
            $sku = $skus;
            $data = [];
            $data['onshelf_flag'] = 'N';
            $data['deleted_flag'] = 'Y';
            if ($suppliers[$sku]) {
                $data['suppliers'] = $suppliers;
            }
            $data['status'] = self::STATUS_DELETED;
            $type = $this->tableName . '_' . $lang;
            $es->update_document($this->update_dbName, $type, $data, $sku);
            if (isset($goods['spu']) && $goods['spu']) {
                $product_model = new ProductModel();
                $productr_supplier_model = new ProductSupplierModel();
                $suppliers = $productr_supplier_model->getsupplieridsbyspu($goods['spu']);
                $product = $product_model->field('sku_count')->where(['spu' => $goods['spu'], 'lang' => $lang])->find();
                if (isset($product['sku_count']) && intval($product['sku_count']) > 0) {
                    $sku_count = intval($product['sku_count']);
                } else {
                    $sku_count = 0;
                }

                $es->update_document($this->update_dbName, 'product_' . $lang, ['sku_count' => strval($sku_count), 'suppliers' => $suppliers], $goods['spu']);
            }
        } elseif (is_array($skus)) {
            $product_updateParams = $updateParams = [];
            $product_updateParams['index'] = $updateParams['index'] = $this->update_dbName;
            $updateParams['type'] = 'goods_' . $lang;
            $product_updateParams['type'] = 'product_' . $lang;
            $goodses = $this->field('spu')->where(['sku' => ['in', $skus], 'lang' => $lang])->group('spu')->select();

            $spus = [];
            if ($goodses) {
                foreach ($goodses as $goods) {
                    $spus[] = $goods['spu'];
                }
                $product_model = new ProductModel();
                $products = $product_model->field('spu,sku_count')->where(['spu' => ['in', $spus], 'lang' => $lang])->select();
            }
            foreach ($skus as $sku) {
                $data = [];
                $data['onshelf_flag'] = 'N';
                $data['deleted_flag'] = 'Y';

                $data['status'] = self::STATUS_DELETED;
                $updateParams['body'][] = ['update' => ['_id' => $sku]];
                $updateParams['body'][] = ['doc' => $data];
            }
            $es->bulk($updateParams);
            $productr_supplier_model = new ProductSupplierModel();
            $suppliers = $productr_supplier_model->getsuppliersbyspus($spus);
            foreach ($products as $product) {
                $data = [];
                if (isset($product['sku_count']) && intval($product['sku_count']) > 0) {
                    $sku_count = intval($product['sku_count']);
                } else {
                    $sku_count = 0;
                }
                $data['suppliers'] = isset($suppliers[$product['spu']]) ? $suppliers[$product['spu']] : [];
                $data['sku_count'] = strval($sku_count);
                $product_updateParams['body'][] = ['update' => ['_id' => $product['spu']]];
                $product_updateParams['body'][] = ['doc' => $data];
            }
            $es->bulk($product_updateParams);
        }
        $es->refresh($this->update_dbName);
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
        $updateParams['index'] = $this->update_dbName;
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
        $updateParams['index'] = $this->update_dbName;
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
