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
class EsgoodsModel extends PublicModel {

    //put your code here
    protected $tableName = 'goods';
    protected $dbName = 'erui_goods'; //数据库名称

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /* 新增ES
     * 
     */

    public function add_data($insert = [], $lang = 'en') {
        $es = new ESClient();
        $type = $this->tableName . '_' . $lang;
        $id = $insert['sku'];
        $es->add_document($this->dbName, $type, $insert, $id);
    }

    /* 新增ES
     * 
     */

    public function Update_data($updatedata = [], $lang = 'en') {
        $es = new ESClient();
        $type = $this->tableName . '_' . $lang;
        $id = $insert['sku'];
        $es->add_document($this->dbName, $type, $updatedata, $id);
    }

    /* 新增ES
     * 
     */

    public function Updateshowcats($showcatsdata = [], $lang = 'en') {
        $es = new ESClient();
        $type = $this->tableName . '_' . $lang;
        $id = $insert['sku'];

        $es->add_document($this->dbName, $type, $body, $id);
    }

    /* 新增ES
     * 
     */

    public function UpdateAttrs($attrdata = [], $lang = 'en') {
        $es = new ESClient();
        $type = $this->tableName . '_' . $lang;
        $id = $insert['sku'];
        $data = $es->get_document($this->dbName, $type, $id);

        $es->add_document($this->dbName, $type, $body, $id);
    }

    /* 新增ES
     * 
     */

    public function delete_data($sku, $lang = 'en') {
        $es = new ESClient();
        $type = $this->tableName . '_' . $lang;
        $id = $insert['sku'];
        $es->delete_document($this->dbName, $type, $id);
    }

    /* 条件组合
     * @param mix $condition // 搜索条件
     */

    private function getCondition($condition) {
        $body = [];
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        if (isset($condition['sku'])) {
            $sku = $condition['sku'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['sku' => $sku]];
        }
        if (isset($condition['spu'])) {
            $spu = $condition['spu'];
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['spu' => $spu]];
        }
        if (isset($condition['show_cat_no'])) {
            $show_cat_no = $condition['show_cat_no'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['show_cats' => $show_cat_no]];
        }
        if (isset($condition['created_at_start']) && isset($condition['created_at_end'])) {
            $created_at_start = $condition['created_at_start'];
            $created_at_end = $condition['created_at_end'];
            $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' =>
                    ['gte' => $created_at_start,
                        'gle' => $created_at_end,
                    ]
                ]
            ];
        } elseif (isset($condition['created_at_start'])) {
            $created_at_start = $condition['created_at_start'];

            $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' =>
                    ['gte' => $created_at_start,
                    ]
                ]
            ];
        } elseif (isset($condition['created_at_end'])) {
            $created_at_end = $condition['created_at_end'];

            $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' =>
                    ['gle' => $created_at_end,
                    ]
                ]
            ];
        }

        if (isset($condition['status'])) {
            $status = $condition['status'];
            if (!in_array($updated_at_end, ['NORMAL', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {
                $status = 'NORMAL';
            }
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['status' => $status]];
        } else {
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['status' => 'NORMAL']];
        }

        if (isset($condition['model'])) {
            $model = $condition['model'];
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['model' => $model]];
        }
        if (isset($condition['pricing_flag'])) {
            $model = $condition['pricing_flag'] == 'N' ? 'N' : 'Y';
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['pricing_flag' => $model]];
        }

        if (isset($condition['created_by'])) {
            $created_by = $condition['created_by'];
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['created_by' => $created_by]];
        }
        if (isset($condition['keyword'])) {
            $show_name = $condition['keyword'];
            $body['query'] = ['multi_match' => [
                    "query" => $show_name,
                    "type" => "most_fields",
                    "fields" => ["show_name", "attrs", 'specs']
            ]];
        }
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @param mix  $_source //需要输出的字段
     * @return mix  
     */

    public function getgoods($condition, $_source = [], $lang = 'en') {

        if (!$_source) {
            $_source = ['sku', 'spu', 'name', 'show_name', 'attrs', 'specs', 'model'
                , 'purchase_price1', 'purchase_price2', 'purchase_price_cur', 'purchase_unit', 'pricing_flag'];
        }
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
            $newbody = $this->getCondition($condition);
            $allcount = 0;
            $allcount = $es->setbody($body)->count($this->dbName, $this->tableName . '_' . $lang);
            return [$es->setbody($body)
                        ->setfields($_source)
                        ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $from, $pagesize, $allcount];
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
     */

    public function getshow_catlist($condition, $lang = 'en') {

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


            return $es->setbody($body)
                            ->setaggs('show_cats', 'chowcat', 'terms')
                            ->search($this->dbName, $this->tableName . '_' . $lang, $from);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过ES 获取数据列表
     * @param string $name // 商品名称 属性名称或属性值
     * @param string $show_cat_no // 展示分类编码
     * @return mix  
     */

    public function getGoodsbysku($sku, $lang = 'en') {
        try {
            $es = new ESClient();
            $es->setmust(['sku' => $sku], ESClient::TERM);
            return $es->search($this->dbName, $this->tableName . '_' . $lang);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过ES 获取数据列表
     * @param string $name // 商品名称 属性名称或属性值
     * @param string $show_cat_no // 展示分类编码
     * @return mix  
     */

    public function getGoodsbyspu($sku, $lang = 'en') {
        try {
            $es = new ESClient();
            $es->setmust(['sku' => $sku], ESClient::TERM);
            return $es->search($this->dbName, $this->tableName . '_' . $lang);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过SKU获取数据商品属性列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix  
     */

    public function getgoods_attrbyskus($skus, $lang = 'en') {

        try {
            $product_attrs = $this->table('erui_goods.t_goods_attr')
                    ->field('*')
                    ->where(['sku' => ['in', $skus], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $ret = [];
            foreach ($product_attrs as $item) {

                $ret[$item['sku']][] = $item;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过SKU获取数据商品规格列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix  
     */

    public function getgoods_specsbyskus($skus, $lang = 'en') {
        try {
            $product_attrs = $this->table('erui_goods.t_goods_attr')
                    ->field('sku,attr_name,attr_value,attr_no')
                    ->where(['sku' => ['in', $skus],
                        'lang' => $lang,
                        'spec_flag' => 'Y',
                        'status' => 'VALID'
                    ])
                    ->select();
            $ret = [];
            foreach ($product_attrs as $item) {
                $sku = $item['sku'];
                unset($item['sku']);
                $ret[$sku][] = $item;
            }
            return $ret;
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
            $goods_attrs = $this->getgoods_attrbyskus($spus, $lang);
            $specs = $this->getgoods_specsbyskus($skus, $lang);
            $ret = [];
            foreach ($goodss as $item) {
                $id = $item['id'];
                $body = $item;

                $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];
                $body['show_cat'] = $productattrs[$item['spu']]['show_cats'];
                $body['specs'] = $specs[$item['sku']];
                $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);
                foreach ($goods_attrs[$item['sku']] as $attr) {

                    array_push($product_attrs, $attr);
                }
                $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);
                // $body['specs'] = json_encode($specs, JSON_UNESCAPED_UNICODE);
                $ret[$id] = $body;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过批量导入商品信息到ES

     * @param string $lang // 语言
     * @return mix  
     */

    public function importgoodss($lang = 'en') {
        try {
            $goodss = $this->where(['lang' => $lang])
                    ->select();
            $spus = $skus = [];
            if ($goodss) {
                foreach ($goodss as $item) {
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


            $goods_attrs = $this->getgoods_attrbyskus($spus, $lang);
            $specs = $this->getgoods_specsbyskus($skus, $lang);
            foreach ($goodss as $item) {
                $id = $item['sku'];
                $body = $item;
                $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];
                $body['show_cats'] = $productattrs[$item['spu']]['show_cats'];
                $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);
                if ($specs[$item['sku']]) {
                    $body['specs'] = $specs[$item['sku']];
                } else {
                    $body['specs'] = '[]';
                }
                if (isset($goods_attrs[$item['sku']])) {
                    foreach ($goods_attrs[$item['sku']] as $attr) {

                        array_push($product_attrs, $attr);
                    }
                }
                $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);
                $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
