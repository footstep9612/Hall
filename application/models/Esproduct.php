<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EsProduct
 *
 * @author zhongyg
 */
 class EsproductModel extends PublicModel {

//put your code here
    protected $tableName = 'product';
    protected $dbName = 'erui_goods'; //数据库名称

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /* 条件组合
     * @param mix $condition // 搜索条件
     */

    private function getCondition($condition) {
        $body = [];
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        if (isset($condition['sku'])) {
            $sku = $condition['sku'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['skus' => $sku]];
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
            $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' => ['gte' => $created_at_start, 'gle' => $created_at_end,]]];
        } elseif (isset($condition['created_at_start'])) {
            $created_at_start = $condition['created_at_start'];

            $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' => ['gte' => $created_at_start,]]];
        } elseif (isset($condition['created_at_end'])) {
            $created_at_end = $condition['created_at_end'];

            $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' => ['gle' => $created_at_end,]]];
        }
        if (isset($condition['checked_at_start']) && isset($condition['checked_at_end'])) {
            $checked_at_start = $condition['checked_at_start'];
            $checked_at_end = $condition['checked_at_end'];
            $body['query']['bool']['must'][] = [ESClient::RANGE => ['checked_at' => ['gte' => $checked_at_start, 'gle' => $checked_at_end,]]];
        } elseif (isset($condition['checked_at_start'])) {
            $checked_at_start = $condition['checked_at_start'];

            $body['query']['bool']['must'][] = [ESClient::RANGE => ['checked_at' => ['gte' => $checked_at_start,]]];
        } elseif (isset($condition['created_at_end'])) {
            $checked_at_end = $condition['checked_at_end'];

            $body['query']['bool']['must'][] = [ESClient::RANGE => ['checked_at' => ['gle' => $checked_at_end,]]];
        }

        if (isset($condition['updated_at_start']) && isset($condition['updated_at_end'])) {
            $updated_at_start = $condition['updated_at_start'];
            $updated_at_end = $condition['updated_at_end'];
            $body['query']['bool']['must'][] = [ESClient::RANGE => ['updated_at' => ['gte' => $updated_at_start,
                        'gle' => $updated_at_end,]]];
        } elseif (isset($condition['updated_at_start'])) {
            $updated_at_start = $condition['updated_at_start'];

            $body['query']['bool']['must'][] = [ESClient::RANGE => ['updated_at' => ['gte' => $updated_at_start,]]];
        } elseif (isset($condition['updated_at_end'])) {
            $updated_at_end = $condition['updated_at_end'];
            $body['query']['bool']['must'][] = [ESClient::RANGE => ['updated_at' => ['gle' => $updated_at_end,]]];
        }
        if (isset($condition['status'])) {
            $status = $condition['status'];
            if (!in_array($updated_at_end, ['NORMAL', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {
                $status = 'NORMAL';
            }
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['status' => $status]];
        } else {
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['status' => 'VALID']];
        }

        if (isset($condition['recommend_flag'])) {
            $recommend_flag = $condition['recommend_flag'] == 'Y' ? 'Y' : 'N';
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['status' => $recommend_flag]];
        }
        if (isset($condition['brand'])) {
            $brand = $condition['brand'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['brand' => $brand]];
        }
        if (isset($condition['source'])) {
            $source = $condition['source'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['source' => $source]];
        }
        if (isset($condition['exe_standard'])) {
            $exe_standard = $condition['exe_standard'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['exe_standard' => $exe_standard]];
        }
        if (isset($condition['app_scope'])) {
            $app_scope = $condition['app_scope'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['app_scope' => $app_scope]];
        }
        if (isset($condition['advantages'])) {
            $advantages = $condition['advantages'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['advantages' => $advantages]];
        }
        if (isset($condition['tech_paras'])) {
            $tech_paras = $condition['tech_paras'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['tech_paras' => $tech_paras]];
        }
        if (isset($condition['source_detail'])) {
            $source_detail = $condition['source_detail'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['source_detail' => $source_detail]];
        }

        if (isset($condition['keywords'])) {
            $keywords = $condition['keywords'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['keywords' => $keywords]];
        }
        if (isset($condition['supplier_id'])) {
            $supplier_id = $condition['supplier_id'];

            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['supplier_id' => $supplier_id]];
        }

        if (isset($condition['supplier_name'])) {
            $supplier_id = $condition['supplier_name'];

            $body['query']['bool']['must'][] = [ESClient::MATCH => ['supplier_name' => $supplier_name]];
        }

        if (isset($condition['created_by'])) {
            $created_by = $condition['created_by'];

            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['created_by' => $created_by]];
        }

        if (isset($condition['updated_by'])) {
            $updated_by = $condition['updated_by'];

            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['updated_by' => $updated_by]];
        }
        if (isset($condition['checked_by'])) {
            $checked_by = $condition['checked_by'];

            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['checked_by' => $checked_by]];
        }

        if (isset($condition['show_name'])) {
            $show_name = $condition['show_name'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['show_name' => $show_name]];
        }
        if (isset($condition['attrs'])) {
            $attrs = $condition['attrs'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['attrs' => $attrs]];
        } if (isset($condition['specs'])) {
            $specs = $condition['specs'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['specs' => $specs]];
        }
        if (isset($condition['keyword'])) {
            $show_name = $condition['keyword'];
            $body['query'] = ['multi_match' => [
                    "query" => $show_name,
                    "type" => "most_fields",
                    "fields" => ["show_name", "attrs", 'specs']
            ]];
        }
        return $body;
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @param mix  $_source //要搜索的字段
     * @return mix  
     */

    public function getproducts($condition, $_source, $lang = 'en') {

        try {
            if (!$_source) {
                $_source = ['skus', 'meterial_cat_no', 'spu', 'name', 'show_name', 'attrs', 'specs'
                    , 'profile', 'supplier_name', 'supplier_id', 'attachs', 'brand', 'recommend_flag'];
            }
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

            $allcount = $es->setbody($body)->count($this->dbName, $this->tableName . '_' . $lang);
            return [$es->setbody($body)
                        ->setfields($_source)
                        ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $from, $pagesize, $allcount['count']];
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

    public function getproductsbysku($sku, $lang = 'en') {
        try {
            $es = new ESClient();
            $es->setmust(['skus' => $sku], ESClient::MATCH);
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

    public function getproductsbyspu($spu, $lang = 'en') {
        try {
            $es = new ESClient();
            $es->setmust(['spu' => $spu], ESClient::MATCH_PHRASE);
            return $es->search($this->dbName, $this->tableName . '_' . $lang);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 和上级分类信息 顶级分类信息
     * @param mix $cat_nos // 物料分类编码数组3f
     * @param string $lang // 语言 zh en ru es 
     * @return mix  物料分类及上级和顶级信息
     */

    public function getmaterial_cat($cat_no, $lang = 'en') {
        try {
            $cat3 = $this->table('erui_goods.t_material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => $cat_no, 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            $cat2 = $this->table('erui_goods.t_material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            $cat1 = $this->table('erui_goods.t_material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            return [$cat1['cat_no'], $cat1['name'], $cat2['cat_no'], $cat2['name'], $cat3['cat_no'], $cat3['name']];
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 及上级分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  物料分类及上级和顶级信息
     */

    public function getmaterial_cats($cat_nos, $lang = 'en') {
        if (!$cat_nos) {
            return[];
        }
        try {
            $cat3s = $this->table('erui_goods.t_material_cat')
                    ->field('id,cat_no,name,parent_cat_no')
                    ->where(['cat_no' => ['in', $cat_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            if (!$cat3s) {

                return [];
            }

            $cat1_nos = $cat2_nos = [];
            foreach ($cat3s as $cat) {
                $cat2_nos[] = $cat['parent_cat_no'];
            }
            $cat2s = $this->table('erui_goods.t_material_cat')
                    ->field('id,cat_no,name,parent_cat_no')
                    ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();

            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }

            $cat1s = $this->table('erui_goods.t_material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $newcat1s = [];
            foreach ($cat1s as $val) {
                $newcat1s[$val['cat_no']] = $val;
            }
            $newcat2s = [];
            foreach ($cat2s as $val) {
                $newcat2s[$val['cat_no']] = $val;
            }
            foreach ($cat3s as $val) {
                $newcat3s[$val['cat_no']] = ['cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['cat_no'],
                    'cat_name1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['name'],
                    'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                    'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    'cat_no3' => $val['cat_no'],
                    'cat_name3' => $val['name']];
            }

            return $newcat3s;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据SPUS 获取商品规格信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  规格信息
     */

    public function getgoods_specsbyspus($spus, $lang = 'en') {
        try {
            $product_attrs = $this->table('erui_goods.t_goods_attr')
                    ->field('spu,attr_name,attr_value,attr_no')
                    ->where(['spu' => ['in', $spus],
                        'lang' => $lang,
                        'spec_flag' => 'Y',
                        'status' => 'VALID'
                    ])
                    ->select();
            $ret = [];
            foreach ($product_attrs as $item) {
                $spu = $item['spu'];
                unset($item['spu']);
                $ret[$spu][] = $item;
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
     * @return mix  属性信息
     */

    public function getproduct_attrbyspus($spus, $lang = 'en') {
        try {
            $product_attrs = $this->table('erui_goods.t_product_attr')
                    ->field('*')
                    ->where(['spu' => ['in', $spus], 'lang' => $lang,
                        'status' => 'NORMAL'])
                    ->select();
            $ret = [];
            if ($product_attrs) {
                foreach ($product_attrs as $item) {

                    $ret[$item['spu']][] = $item;
                }
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

    public function getskusbyspus($spus, $lang = 'en') {
        try {
            $specs = $this->table('erui_goods.t_goods')
                    ->field('sku,spu,`name`,`model`,`show_name`')
                    ->where(['spu' => ['in', $spus], 'status' => 'VALID'])
                    ->select();
            $ret = [];
            if ($specs) {
                foreach ($specs as $spec) {
                    $spu = $spec['spu'];
                    $sku = $spec['sku'];
                    unset($spec['spu']);
                    unset($spec['sku']);
                    $ret[$spu][$sku] = $spec;
                }
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据SPUS 获取产品展示分类信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  展示分类信息列表
     */

    public function getshow_catsbyspus($spus, $lang = 'en') {
        try {

            $show_cat_products = $this->table('erui_goods.t_show_cat_product')
                    ->field('cat_no,spu')
                    ->where(['spu' => ['in', $spus], 'status' => 'VALID'])
                    ->select();
            $ret = [];


            foreach ($show_cat_products as $item) {

                $ret[$item['spu']] = $item['cat_no'];
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据SKUS 获取商品规格信息
     * @param mix $sKus // 产品SKU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  规格信息
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
            if ($product_attrs) {
                foreach ($product_attrs as $item) {
                    $sku = $item['sku'];
                    unset($item['sku']);
                    $ret[$sku][] = $item;
                }
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据分类编码数组获取物料分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  规格信息
     */

    public function getshow_material_cats($cat_nos, $lang = 'en') {

        try {
            $show_material_cats = $this->table('erui_goods.t_show_material_cat')
                    ->field('show_cat_no,material_cat_no')
                    ->where(['material_cat_no' => ['in', $cat_nos], 'status' => 'VALID'])
                    ->select();

            $ret = [];
            if ($show_material_cats) {
                foreach ($show_material_cats as $item) {

                    $ret[$item['material_cat_no']][$item['show_cat_no']] = $item['show_cat_no'];
                }
            }

            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据展示分类编码数组获取展示分类信息
     * @param mix $show_cat_nos // 展示分类编码数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     */

    public function getshow_cats($show_cat_nos, $lang = 'en') {

        try {
            $cat3s = $this->table('erui_goods.t_show_cat')
                    ->field('parent_cat_no,cat_no,name')
                    ->where(['cat_no' => ['in', $show_cat_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $cat1_nos = $cat2_nos = [];


            if (!$cat3s) {
                return [];
            }

            foreach ($cat3s as $cat) {
                $cat2_nos[] = $cat['parent_cat_no'];
            }

            $cat2s = $this->table('erui_goods.t_show_cat')
                            ->field('id,cat_no,name,parent_cat_no')
                            ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }
            $cat1s = $this->table('erui_goods.t_show_cat')->field('id,cat_no,name')
                            ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            $newcat1s = [];
            foreach ($cat1s as $val) {
                $newcat1s[$val['cat_no']] = $val;
            }
            $newcat2s = [];
            foreach ($cat2s as $val) {
                $newcat2s[$val['cat_no']] = $val;
            }
            foreach ($cat3s as $val) {
                $newcat3s[$val['cat_no']] = [
                    'cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['cat_no'],
                    'cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['name'],
                    'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                    'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    'cat_no3' => $val['cat_no'],
                    'cat_name3' => $val['name']];
            }

            return $newcat3s;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过SKU获取数据商品文件列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix  
     */

    public function getproduct_attachsbyspus($spus, $lang = 'en') {

        try {
            $product_attrs = $this->table('erui_goods.t_product_attach')
                    ->field('id,attach_url,attach_name,attach_url,spu')
                    ->where(['sku' => ['in', $spus],
                        'attach_type' => ['in', ['BIG_IMAGE', 'MIDDLE_IMAGE', 'SMALL_IMAGE', 'DOC']],
                        'status' => 'VALID'])
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

    /*
     * 根据SPU数组获取展示属性信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     */

    public function getproductattrsbyspus($spus, $lang = 'en') {
        try {
            $products = $this->where(['spu' => ['in', $spus], 'lang' => $lang])
                    ->field('spu,meterial_cat_no')
                    ->select();


            $spus = $mcat_nos = [];
            foreach ($products as $item) {
                $mcat_nos[] = $item['meterial_cat_no'];
                $spus[] = $item['spu'];
            }
            $spus = array_unique($spus);
            $mcat_nos = array_unique($mcat_nos);
            $mcats = $this->getmaterial_cats($mcat_nos, $lang);
            $scats_no_spu = $this->getshow_catsbyspus($spus, $lang);
            $scats_no_mcatsno = $this->getshow_material_cats($mcat_nos, $lang);
            $product_attrs = $this->getproduct_attrbyspus($spus, $lang);
            $show_cat_nos = [];
            foreach ($scats_no_spu as $show_cat_no) {
                $show_cat_nos[] = $show_cat_no;
            }foreach ($scats_no_mcatsno as $showcatnos) {
                foreach ($showcatnos as $show_cat_no) {
                    $show_cat_nos[] = $show_cat_no;
                }
            }
            $show_cat_nos = array_unique($show_cat_nos);
            $scats = $this->getshow_cats($show_cat_nos, $lang);

            $ret = [];
            foreach ($products as $item) {
                $show_cat[$scats_no_spu[$item['spu']]] = $scats[$scats_no_spu[$item['spu']]];
                foreach ($scats_no_mcatsno[$item['meterial_cat_no']] as $show_cat_no) {
                    $show_cat[$show_cat_no] = $scats[$show_cat_no];
                }
                $body['meterial_cat'] = json_encode($mcats[$item['meterial_cat_no']], JSON_UNESCAPED_UNICODE);
                $body['show_cats'] = json_encode($show_cat, JSON_UNESCAPED_UNICODE); // $mcats[$item['meterial_cat_no']];
                $body['attrs'] = json_encode($product_attrs[$item['spu']], JSON_UNESCAPED_UNICODE);
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
     * 批量导入产品数据到ES

     * @param string $lang // 语言 zh en ru es 
     * @return mix  
     */

    public function importproducts($lang = 'en') {
        try {
            $products = $this->where(['lang' => $lang])
                    ->select();
            ob_end_clean();
            echo count($products), '<BR>';
            flush();
            ob_flush();
            $spus = $mcat_nos = [];
            if ($products) {
                foreach ($products as $item) {
                    $mcat_nos[] = $item['meterial_cat_no'];
                    $spus[] = $item['spu'];
                }
                $spus = array_unique($spus);
                $mcat_nos = array_unique($mcat_nos);
                $mcats = $this->getmaterial_cats($mcat_nos, $lang);
                $scats_no_spu = $this->getshow_catsbyspus($spus, $lang);
                $scats_no_mcatsno = $this->getshow_material_cats($mcat_nos, $lang);
                $product_attrs = $this->getproduct_attrbyspus($spus, $lang);
                $attachs = $this->getproduct_attachsbyspus($spus, $lang);
                $show_cat_nos = [];
                foreach ($scats_no_spu as $show_cat_no) {
                    $show_cat_nos[] = $show_cat_no;
                }
                foreach ($scats_no_mcatsno as $showcatnos) {
                    foreach ($showcatnos as $show_cat_no) {
                        $show_cat_nos[] = $show_cat_no;
                    }
                }
                $show_cat_nos = array_unique($show_cat_nos);


                $scats = $this->getshow_cats($show_cat_nos, $lang);
                $skus = $this->getskusbyspus($spus, $lang);
                $specs = $this->getgoods_specsbyskus($spus, $lang);
                $es = new ESClient();

                foreach ($products as $key => $item) {
                    $id = $item['spu'];
                    $body = $item;
                    if (isset($skus[$item['spu']])) {


                        $body['skus'] = json_encode($skus[$item['spu']], JSON_UNESCAPED_UNICODE);
                    } else {
                        $body['skus'] = '[]';
                    }

                    if (isset($specs[$item['spu']])) {


                        $body['specs'] = json_encode($specs[$item['spu']], JSON_UNESCAPED_UNICODE);
                    } else {
                        $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                        ;
                    }

                    if (isset($attachs[$item['spu']])) {
                        $body['attachs'] = json_encode($attachs[$item['spu']], 256);
                    } else {
                        $body['attachs'] = '[]';
                    }
                    $show_cat = [];
                    if (isset($scats_no_spu[$item['spu']]) && isset($scats[$scats_no_spu[$item['spu']]])) {
                        $show_cat[$scats_no_spu[$item['spu']]] = $scats[$scats_no_spu[$item['spu']]];
                    }
                    if (isset($mcats[$item['meterial_cat_no']])) {
                        $body['meterial_cat'] = json_encode($mcats[$item['meterial_cat_no']], JSON_UNESCAPED_UNICODE);
                    } else {
                        $body['meterial_cat'] = json_encode(new \stdClass(), JSON_UNESCAPED_UNICODE);
                    }
                    $body['show_cats'] = json_encode($show_cat, JSON_UNESCAPED_UNICODE); // $mcats[$item['meterial_cat_no']];
                    if (isset($product_attrs[$item['spu']])) {
                        $body['attrs'] = json_encode($product_attrs[$item['spu']], JSON_UNESCAPED_UNICODE);
                    } else {
                        $body['attrs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                    }
                    $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
                    echo $key, '<BR>';
                    flush();
                    ob_flush();

                    //return $flag;
                }
            } else {
                return false;
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
