<?php/* * To change this license header, choose License Headers in Project Properties. * To change this template file, choose Tools | Templates * and open the template in the editor. *//** * Description of Esgoods * * @author zhongyg */class EsGoodsModel extends PublicModel {    //put your code here    protected $tableName = 'goods';    protected $dbName = 'erui2_goods'; //数据库名称    public function __construct($str = '') {        parent::__construct($str = '');    }    /*     * 判断搜索条件是否存在     * 存在 则组合查询     */    private function _getQurey(&$condition, &$body, $qurey_type = ESClient::MATCH, $name = '', $field = null, $minimum_should_match = false) {        if ($qurey_type == ESClient::MATCH || $qurey_type == ESClient::MATCH_PHRASE) {            if (isset($condition[$name]) && $condition[$name]) {                $value = $condition[$name];                if (!$field) {                    $field = $name;                }                $body['query']['bool']['must'][] = [$qurey_type => [$field => $value]];            }        } elseif ($qurey_type == ESClient::WILDCARD) {            if (isset($condition[$name]) && $condition[$name]) {                $value = $condition[$name];                if (!$field) {                    $field = $name;                }                $body['query']['bool']['must'][] = [$qurey_type => [$field => '*' . $value . '*']];            }        } elseif ($qurey_type == ESClient::MULTI_MATCH) {            if (isset($condition[$name]) && $condition[$name]) {                $value = $condition[$name];                if (!$field) {                    $field = [$name];                }                $body['query']['bool']['must'][] = [$qurey_type => [                        'query' => $value,                        'type' => 'most_fields',                        'operator' => 'and',                        'fields' => $field                ]];            }        } elseif ($qurey_type == ESClient::RANGE) {            if (isset($condition[$name . '_start']) && isset($condition[$name . '_end']) && $condition[$name . '_end'] && $condition[$name . '_start']) {                $created_at_start = $condition[$name . '_start'];                $created_at_end = $condition[$name . '_end'];                $body['query']['bool']['must'][] = [ESClient::RANGE => [$name => ['gte' => $created_at_start, 'gle' => $created_at_end,]]];            } elseif (isset($condition[$name . '_start']) && $condition[$field . '_start']) {                $created_at_start = $condition[$name . '_start'];                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['gte' => $created_at_start,]]];            } elseif (isset($condition[$name . '_end']) && $condition[$name . '_end']) {                $created_at_end = $condition[$name . '_end'];                $body['query']['bool']['must'][] = [ESClient::RANGE => [$field => ['gle' => $created_at_end,]]];            }        }    }    /*     * 判断搜索状态是否存在     * 存在 则组合查询     */    private function _getStatus(&$condition, &$body, $qurey_type = ESClient::MATCH, $name = '', $field = '', $array = [], $default = 'VALID') {        if (!$field) {            $field = [$name];        }        if (isset($condition[$name]) && $condition[$name]) {            $status = $condition[$name];            if ($status == 'ALL') {                            } elseif (in_array($status, $array)) {                $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $status]];            } else {                $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $default]];            }        } else {            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $default]];        }    }    /*     * 判断搜索状态是否存在     * 存在 则组合查询     */    private function _getQureyByArr(&$condition, &$body, $qurey_type = ESClient::MATCH_PHRASE, $names = '', $field = '') {        if (!$field) {            $field = [$names];        }        if (isset($condition[$names]) && $condition[$names]) {            $name_arr = $condition[$names];            $bool = [];            foreach ($name_arr as $name) {                $bool[] = [$qurey_type => [$field => $name]];            }            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $bool]];        }    }    /*     * 判断搜索状态是否存在     * 存在 则组合查询     */    private function _getQureyByBool(&$condition, &$body, $qurey_type = ESClient::MATCH_PHRASE, $name = '', $field = '', $default = 'N') {        if (!$field) {            $field = $name;        }        if (isset($condition[$name]) && $condition[$name]) {            $recommend_flag = $condition[$name] == 'Y' ? 'Y' : $default;            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => [$field => $recommend_flag]];        }    }    /* 条件组合     * @param mix $condition // 搜索条件     */    private function getCondition($condition) {        $body = [];        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'sku');        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'spu');        $this->_getQureyByArr($condition, $body, ESClient::MATCH_PHRASE, 'skus', 'sku');        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'show_cat_no', 'show_cats.all');        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'market_area_bn', 'show_cats.all');        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'country_bn', 'show_cats.all');        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no1', 'meterial_cat.all');        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no2', 'meterial_cat.all');        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'mcat_no3', 'meterial_cat.all');        $this->_getQurey($condition, $body, ESClient::RANGE, 'created_at');        $this->_getQurey($condition, $body, ESClient::RANGE, 'checked_at');        $this->_getQurey($condition, $body, ESClient::RANGE, 'updated_at');        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'name', 'name');        $this->_getQurey($condition, $body, ESClient::MATCH, 'show_name');        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'real_name', 'name');        $this->_getQurey($condition, $body, ESClient::MATCH, 'supplier_name');        $this->_getQurey($condition, $body, ESClient::MATCH, 'brand');        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'source');        $this->_getQurey($condition, $body, ESClient::WILDCARD, 'cat_name', 'show_cats.all');        $this->_getQurey($condition, $body, ESClient::MATCH, 'checked_desc');        $this->_getQurey($condition, $body, ESClient::RANGE, 'shelves_at');        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'shelves_by');        $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']);        $this->_getQureyByBool($condition, $body, ESClient::MATCH_PHRASE, 'recommend_flag', 'recommend_flag', 'N');        $this->_getStatus($condition, $body, ESClient::MATCH_PHRASE, 'shelves_status', 'shelves_status', ['VALID', 'INVALID']);        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'model');        $this->_getQurey($condition, $body, ESClient::MATCH, 'attrs');        $this->_getQurey($condition, $body, ESClient::MATCH, 'specs');        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'created_by');        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'updated_by');        $this->_getQurey($condition, $body, ESClient::MATCH_PHRASE, 'checked_by');        $this->_getQurey($condition, $body, ESClient::MULTI_MATCH, 'keyword', ['show_name', 'attrs', 'specs', 'spu', 'source', 'brand', 'skus']);        return $body;    }    /* 通过搜索条件获取数据列表     * @param mix $condition // 搜索条件     * @param string $lang // 语言     * @return mix       */    public function getgoods($condition, $_source = null, $lang = 'en') {        try {            if (!$_source) {                $_source = ['sku', 'spu', 'name', 'show_name', 'model'                    , 'purchase_price1', 'purchase_price2', 'attachs', 'package_quantity', 'exw_day',                    'purchase_price_cur', 'purchase_unit', 'pricing_flag', 'show_cats',                    'meterial_cat', 'brand', 'supplier_name', 'warranty'];            }            $body = $this->getCondition($condition);            $pagesize = 10;            $current_no = 1;            if (isset($condition['current_no'])) {                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;            }            if (isset($condition['pagesize'])) {                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;            }            $from = ($current_no - 1) * $pagesize;            $es = new ESClient();            return [$es->setbody($body)                        ->setfields($_source)                        ->setsort('sort_order', 'desc')                        ->setsort('_id', 'desc')                        ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /* 通过搜索条件获取数据列表     * @param mix $condition // 搜索条件     * @param string $lang // 语言     * @return mix       */    public function getgoodscount($condition, $lang = 'en') {        try {            $body = $this->getCondition($condition);            $es = new ESClient();            $goodscount = $es->setbody($body)                    ->count($this->dbName, $this->tableName . '_' . $lang);            return $goodscount['count'];        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return 0;        }    }    /* 通过搜索条件获取数据列表     * @param mix $condition // 搜索条件     * @param string $lang // 语言     * @return mix       */    public function getshow_catlist($condition, $lang = 'en') {        try {            $body = $this->getCondition($condition);            $from = ($current_no - 1) * $pagesize;            $es = new ESClient();            return $es->setbody($body)                            ->setaggs('show_cats', 'chowcat', 'terms')                            ->search($this->dbName, $this->tableName . '_' . $lang, $from);        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /* 通过ES 获取数据列表     * @param string $name // 商品名称 属性名称或属性值     * @param string $show_cat_no // 展示分类编码     * @return mix       */    public function getGoodsbysku($sku, $lang = 'en') {        try {            $es = new ESClient();            $es->setmust(['sku' => $sku], ESClient::TERM);            return $es->search($this->dbName, $this->tableName . '_' . $lang);        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /* 通过ES 获取数据列表     * @param string $name // 商品名称 属性名称或属性值     * @param string $show_cat_no // 展示分类编码     * @return mix       */    public function getGoodsbyspu($sku, $lang = 'en') {        try {            $es = new ESClient();            $es->setmust(['sku' => $sku], ESClient::TERM);            return $es->search($this->dbName, $this->tableName . '_' . $lang);        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /* 通过SKU获取数据商品属性列表     * @param mix $skus // 商品SKU编码数组     * @param string $lang // 语言     * @return mix       */    public function getgoods_attrbyskus($skus, $lang = 'en') {        try {            $product_attrs = $this->table('erui2_goods.goods_attr')                    ->field('*')                    ->where(['sku' => ['in', $skus], 'spec_flag' => 'N', 'lang' => $lang, 'status' => 'VALID'])                    ->select();            foreach ($product_attrs as $item) {                $ret[$item['sku']][] = $item;            }            return $ret;        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /* 通过SKU获取数据商品文件列表     * @param mix $skus // 商品SKU编码数组     * @param string $lang // 语言     * @return mix       */    public function getgoods_attachsbyskus($skus, $lang = 'en') {        try {            $goods_attachs = $this->table('erui2_goods.goods_attach')                    ->field('id,attach_type,attach_url,attach_name,attach_url,sku')                    ->where(['sku' => ['in', $skus],                        'attach_type' => ['in', ['BIG_IMAGE', 'MIDDLE_IMAGE', 'SMALL_IMAGE', 'DOC']],                        'status' => 'VALID'])                    ->select();            $ret = [];            if ($goods_attachs) {                foreach ($goods_attachs as $item) {                    $data['attach_name'] = $item['attach_name'];                    $data['attach_url'] = $item['attach_url'];                    $ret[$item['sku']][$item['attach_type']][] = $data;                }            }            return $ret;        } catch (Exception $ex) {            print_r($ex->getMessage());            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /* 通过SKU获取数据商品规格列表     * @param mix $skus // 商品SKU编码数组     * @param string $lang // 语言     * @return mix       */    public function getgoods_specsbyskus($skus, $lang = 'en') {        try {            $product_attrs = $this->table('erui2_goods.goods_attr')                    ->field('sku,attr_name,attr_value,attr_no')                    ->where(['sku' => ['in', $skus],                        'lang' => $lang,                        'spec_flag' => 'Y',                        'status' => 'VALID'                    ])                    ->select();            $ret = [];            foreach ($product_attrs as $item) {                $sku = $item['sku'];                unset($item['sku']);                $ret[$sku][] = $item;            }            return $ret;        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /* 通过SKU获取数据商品产品属性分类等信息列表     * @param mix $skus // 商品SKU编码数组     * @param string $lang // 语言     * @return mix       */    public function getproductattrsbyspus($skus, $lang = 'en') {        try {            $goodss = $this->where(['sku' => ['in', $skus], 'lang' => $lang])                    ->select();            $spus = $skus = [];            foreach ($goodss as $item) {                $skus[] = $item['sku'];                $spus[] = $item['spu'];            }            $spus = array_unique($spus);            $skus = array_unique($skus);            $espoducmodel = new EsproductModel();            $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);            $goods_attrs = $this->getgoods_attrbyskus($spus, $lang);            $specs = $this->getgoods_specsbyskus($skus, $lang);            $ret = [];            foreach ($goodss as $item) {                $id = $item['id'];                $body = $item;                $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];                $body['show_cat'] = $productattrs[$item['spu']]['show_cats'];                $body['specs'] = $specs[$item['sku']];                $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);                foreach ($goods_attrs[$item['sku']] as $attr) {                    array_push($product_attrs, $attr);                }                $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);                // $body['specs'] = json_encode($specs, JSON_UNESCAPED_UNICODE);                $ret[$id] = $body;            }            return $ret;        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return [];        }    }    /*     * 将数组中的null值转换为空值     * @author zyg 2017-07-31     * @param array $item // 语言 zh en ru es      * @return mix      */    private function _findnulltoempty(&$item) {        foreach ($item as $key => $val) {            if (is_null($val)) {                $item[$key] = '';            }        }    }    /* 通过批量导入商品信息到ES     * @author zyg 2017-07-31     * @param string $lang // 语言     * @return mix       */    public function importgoodss($lang = 'en') {        try {            $count = $this->where(['lang' => $lang])->count('id');            for ($i = 0; $i < $count; $i += 100) {                if ($i > $count) {                    $i = $count;                }                echo $i, PHP_EOL;                ob_flush();                flush();                $goods = $this->where(['lang' => $lang])                                ->limit($i, 100)->select();                $spus = $skus = [];                if ($goods) {                    foreach ($goods as $item) {                        $skus[] = $item['sku'];                        $spus[] = $item['spu'];                    }                } else {                    return false;                }                $spus = array_unique($spus);                $skus = array_unique($skus);                $espoducmodel = new EsproductModel();                $es = new ESClient();                $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);                $attachs = $this->getgoods_attachsbyskus($skus, $lang);                $goods_attrs = $this->getgoods_attrbyskus($skus, $lang);                $specs = $this->getgoods_specsbyskus($skus, $lang);                foreach ($goods as $item) {                    $id = $item['sku'];                    $this->_findnulltoempty($item);                    $body = $item;                    $product_attr = $this->_getValue($productattrs, $item['spu'], [], 'json');                    $body['meterial_cat'] = $this->_getValue($product_attr, 'meterial_cat', [], 'json');                    $body['meterial_cat'] = $this->_getValue($product_attr, 'show_cats', [], 'json');                    $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);                    $body['attachs'] = $this->_getValue($specs, $item['sku'], [], 'json');                    $body['attachs'] = $this->_getValue($attachs, $item['sku'], [], 'json');                    if (isset($goods_attrs[$item['sku']]) && $product_attrs) {                        foreach ($goods_attrs[$item['sku']] as $attr) {                            array_push($product_attrs, $attr);                        }                    } elseif (isset($goods_attrs[$item['sku']])) {                        $product_attrs = $goods_attrs[$item['sku']];                    }                    $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);                    $body['supplier_name'] = $this->_getValue($productattrs, 'supplier_name');                    $body['brand'] = $this->_getValue($productattrs, 'brand');                    $body['source'] = $this->_getValue($productattrs, 'source');                    if ($body['source'] == 'ERUI') {                        $body['sort_order'] = 100;                    } else {                        $body['sort_order'] = 1;                    }                    if (in_array($body['brand'], ['KERUI', '科瑞'])) {                        $body['sort_order'] += 20;                    }                    $body['supplier_id'] = $productattrs[$item['spu']]['supplier_id'];                    $body['meterial_cat_no'] = $productattrs[$item['spu']]['meterial_cat_no'];                    $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);                    if (!isset($flag['create'])) {                        LOG::write("FAIL:" . $item['id'] . var_export($flag, true), LOG::ERR);                    }                    print_r($flag);                    ob_flush();                    flush();                }            }        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return false;        }    }    /* 条件判断     * @author zyg 2017-07-31     * @param array $condition  条件     * @param string $name需要判断的键值     * @param string $default 默认值     * @param string $type 判断的类型     * @param array $arr 状态判断时状态数组     * @return mix       */    private function _getValue($condition, $name, $default = null, $type = 'string', $arr = ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED']) {        if ($type === 'string') {            if (isset($condition[$name]) && $condition[$name]) {                $value = $condition[$name];                $condition = null;                unset($condition);                return $value;            } else {                $condition = null;                unset($condition);                return $default;            }        } elseif ($type === 'bool') {            if (isset($condition[$name]) && $condition[$name]) {                $flag = $condition[$name] == 'Y' ? 'Y' : 'N';                $condition = null;                unset($condition);                return $flag;            } else {                $condition = null;                unset($condition);                return 'N';            }        } elseif ($type === 'json') {            if (isset($condition[$name]) && $condition[$name]) {                $return = json_encode($condition[$name], 256);                $condition = null;                unset($condition);                return $flag;            } else {                $condition = null;                unset($condition);                return json_encode($default, 256);            }        } elseif ($type === 'in_array') {            if (isset($condition[$name]) && in_array($condition[$name], $arr)) {                $return = strtoupper($condition[$name]);                $condition = null;                unset($condition);                return $flag;            } else {                $condition = null;                unset($condition);                return $default;            }        }    }    public function getInsertCodition($condition, $lang = 'en') {        $data = [];        if (isset($condition['id'])) {            $data['id'] = $condition['id'];        }        $data['lang'] = $lang;        $meterial_cat_no = null;        if (isset($condition['spu'])) {            $data['spu'] = $condition['spu'];            $mpmodel = new Materialcatproduct();            $meterial_cat_noinfo = $mpmodel->getcatnobyspu($data['spu']);            $meterial_cat_no = $meterial_cat_noinfo['cat_no'];        } else {            $data['spu'] = '';        }        $data['sku'] = $this->_getValue($condition, 'sku');        if ($meterial_cat_no) {            $material_cat_no = $data['meterial_cat_no'] = $condition['meterial_cat_no'];            $mcatmodel = new MaterialcatModel();            $data['meterial_cat'] = json_encode($mcatmodel->getinfo($material_cat_no, $lang), 256);            $smmodel = new ShowmaterialcatModel();            $show_cat_nos = $smmodel->getshowcatnosBymatcatno($material_cat_no, $lang);            $es_product_model = new EsproductModel();            $scats = $es_product_model->getshow_cats($show_cat_nos, $lang);            $data['show_cats'] = $this->_getValue($scats, $material_cat_no, [], 'json');        } else {            $data['meterial_cat_no'] = '';            $data['meterial_cat'] = json_encode(new \stdClass());            $data['show_cats'] = json_encode([]);        }        $data['qrcode'] = $this->_getValue($condition, 'qrcode');        $data['name'] = $this->_getValue($condition, 'name');        $data['show_name'] = $this->_getValue($condition, 'show_name');        $data['model'] = $this->_getValue($condition, 'model');        $data['description'] = $this->_getValue($condition, 'description');        $data['package_quantity'] = $this->_getValue($condition, 'package_quantity');        $data['exw_day'] = $this->_getValue($condition, 'exw_day');        $data['purchase_price1'] = $this->_getValue($condition, 'purchase_price1');        $data['purchase_price2'] = $this->_getValue($condition, 'purchase_price2');        $data['purchase_price_cur'] = $this->_getValue($condition, 'purchase_price_cur');        $data['purchase_unit'] = $this->_getValue($condition, 'purchase_unit');        $data['pricing_flag'] = $this->_getValue($condition, 'pricing_flag', 'N', 'bool');        $data['status'] = $this->_getValue($condition, 'status', 'CHECKING', 'in_array');        $data['created_by'] = $this->_getValue($condition, 'created_by');        $data['created_at'] = $this->_getValue($condition, 'created_at');        $data['updated_by'] = $this->_getValue($condition, 'updated_by');        $data['updated_at'] = $this->_getValue($condition, 'updated_at');        $data['checked_by'] = $this->_getValue($condition, 'checked_by');        $data['checked_at'] = $this->_getValue($condition, 'checked_at');        $data['supplier_id'] = $this->_getValue($condition, 'supplier_id');        $data['supplier_name'] = $this->_getValue($condition, 'supplier_name');        $data['brand'] = $this->_getValue($condition, 'brand');        $data['source'] = $this->_getValue($condition, 'source');        $data['checked_desc'] = $this->_getValue($condition, 'checked_desc');        $data['shelves_status'] = $this->_getValue($condition, 'shelves_status', 'INVALID', 'in_array', ['INVALID', 'VALID']);        return $data;    }    /*     * 添加产品到Es     * @param string $lang // 语言 zh en ru es      * @return mix       */    public function create_data($data, $lang = 'en') {        try {            $es = new ESClient();            if (!isset($data['sku']) || empty($data['sku'])) {                return false;            }            $body = $this->getInsertCodition($data);            $id = $data['sku'];            $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);            if ($data['spu']) {                $es_product_model = new EsproductModel();                $es_product_model->Update_skus($data['spu'], '', $lang);            }            if ($flag['_shards']['successful'] !== 1) {                LOG::write("FAIL:" . $id . var_export($flag, true), LOG::ERR);                return true;            } else {                return false;            }        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return false;        }    }    /*     * 添加产品到Es     * @param string $lang // 语言 zh en ru es      * @return mix       */    public function update_data($data, $sku, $lang = 'en') {        try {            $es = new ESClient();            if (empty($sku)) {                return false;            } else {                $data['sku'] = $sku;            }            $body = $this->getInsertCodition($data);            $id = $sku;            $flag = $es->update_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);            if ($flag['_shards']['successful'] !== 1) {                LOG::write("FAIL:" . $id . var_export($flag, true), LOG::ERR);                return true;            } else {                return false;            }        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return false;        }    }    /* 上架     *      */    public function changestatus($sku, $lang = 'en') {        try {            $es = new ESClient();            if (empty($sku)) {                return false;            }            if (in_array(strtoupper($status), ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {                $data['status'] = strtoupper($status);            } else {                $data['status'] = 'CHECKING';            }            $id = $sku;            $flag = $es->update_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);            if ($flag['_shards']['successful'] !== 1) {                LOG::write("FAIL:" . $id . var_export($flag, true), LOG::ERR);                return true;            } else {                return false;            }        } catch (Exception $ex) {            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);            LOG::write($ex->getMessage(), LOG::ERR);            return false;        }    }    /* 新增ES     * $substr 替换前的内容, 需要替换的内容     * $replacement 替换后的内容     */    public function update_showcats($old_cat_no, $lang = 'en') {        if (empty($old_cat_no)) {            return false;        }        $index = $this->dbName;        $type_goods = 'goods_' . $lang;        $count_goods = $this->setbody(['query' => [                        ESClient::MATCH_PHRASE => [                            "show_cats" => $old_cat_no                        ]            ]])->count($index, $type_goods);        for ($i = 0; $i < $count_goods['count']; $i += 100) {            $ret = $this->setbody(['query' => [                            ESClient::MATCH_PHRASE => [                                "show_cats" => $old_cat_no                            ]                ]])->search($index, $type_goods, $i, 100);            $updateParams = array();            $updateParams['index'] = $this->dbName;            $updateParams['type'] = $type_goods;            if ($ret) {                foreach ($ret['hits']['hits'] as $item) {                    $updateParams['body'][] = ['update' => ['_id' => $item['_id']]];                    $updateParams['body'][] = ['doc' => $this->getshowcats($item['_source']['spu'], $lang)];                }                $this->bulk($updateParams);            }        }        return true;    }    /* 更新属性规格     *      */    public function Update_Attrs($sku, $lang = 'en', $product_attrs = [], $product_specs = []) {        $es = new ESClient();        if (empty($sku)) {            return false;        }        $goodsmodel = new GoodsModel();        $goodsinfo = $goodsmodel->getInfo($sku, $lang);        $goods_attrs = $this->getgoods_attrbyskus([$sku], $lang);        $specs = $this->getgoods_specsbyskus([$sku], $lang);        $EsproductModel = new EsproductModel();        if (empty($product_attrs)) {            $product_attrs = $EsproductModel->getgoods_specsbyspus([$goodsinfo['spu']], $lang);        }        if (empty($product_specs)) {            $product_specs = $EsproductModel->getproductattrsbyspus([$goodsinfo['spu']], $lang);        }        $goods_attrs = $goods_attrs[$sku];        $specs = $specs[$sku];        if (isset($specs[$item['sku']])) {            $body['specs'] = json_encode($specs[$item['sku']], 256);        } else {            $body['specs'] = '[]';        }        if (isset($product_attrs[$goodsinfo['spu']]) && $goods_attrs) {            foreach ($product_attrs[$goodsinfo['spu']] as $attr) {                array_push($goods_attrs, $attr);            }        } elseif (isset($product_attrs[$goodsinfo['spu']])) {            $goods_attrs = $product_attrs[$goodsinfo['spu']];        }        if (isset($product_specs[$goodsinfo['spu']]) && $specs) {            foreach ($product_specs[$goodsinfo['spu']] as $spec) {                array_push($specs, $spec);            }        } elseif (isset($product_specs[$goodsinfo['spu']])) {            $specs = $product_specs[$goodsinfo['spu']];        }        $data['attrs'] = json_encode($goods_attrs, JSON_UNESCAPED_UNICODE);        $id = $sku;        $data['specs'] = json_encode($specs, 256);        $type = $this->tableName . '_' . $lang;        $es->update_document($this->dbName, $type, $data, $id);        return true;    }    /* 新增ES     *      */    public function Update_Attachs($sku, $lang = 'en') {        $es = new ESClient();        if (empty($sku)) {            return false;        }        $attachs = $this->getgoods_attachsbyskus([$sku], $lang);        if (isset($attachs[$sku])) {            $data['attachs'] = json_encode($attachs[$sku], 256);        } else {            $data['attachs'] = '[]';        }        $id = $sku;        if ($lang) {            $type = $this->tableName . '_' . $lang;            $es->update_document($this->dbName, $type, $data, $id);        } else {            $type = $this->tableName . '_en';            $es->update_document($this->dbName, $type, $data, $id);            $type = $this->tableName . '_es';            $es->update_document($this->dbName, $type, $data, $id);            $type = $this->tableName . '_ru';            $es->update_document($this->dbName, $type, $data, $id);            $type = $this->tableName . '_es';            $es->update_document($this->dbName, $type, $data, $id);        }        return true;    }    public function delete_data($sku, $lang = 'en') {        $es = new ESClient();        if (empty($sku)) {            return false;        }        $data['status'] = self::STATUS_DELETED;        $id = $sku;        if ($lang) {            $type = $this->tableName . '_' . $lang;            $es->update_document($this->dbName, $type, $data, $id);        } else {            $type = $this->tableName . '_en';            $es->update_document($this->dbName, $type, $data, $id);            $type = $this->tableName . '_es';            $es->update_document($this->dbName, $type, $data, $id);            $type = $this->tableName . '_ru';            $es->update_document($this->dbName, $type, $data, $id);            $type = $this->tableName . '_es';            $es->update_document($this->dbName, $type, $data, $id);        }        return true;    }}