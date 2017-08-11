<?php

/* To change this license header, choose License Headers in Project Properties.
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
        if (isset($condition['spus'])) {
            $spus = $condition['spus'];
            $spus_arr = [];
            foreach ($spus as $spu) {
                $spus_arr[] = [ESClient::MATCH_PHRASE => ['spu' => $spu]];
            }
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => $spus_arr]];
        }
        if (isset($condition['show_cat_no'])) {
            $show_cat_no = $condition['show_cat_no'];
            $body['query']['bool']['must'][] = [ESClient::MATCH =>
                ['show_cats' => $show_cat_no,]];
        }
        if (isset($condition['mcat_no1'])) {
            $mcat_no1 = $condition['mcat_no1'];
            $body['query']['bool']['must'][] = [ESClient::MATCH =>
                ['meterial_cat' => $mcat_no1]];
        }
        if (isset($condition['mcat_no2'])) {
            $mcat_no2 = $condition['mcat_no2'];
            $body['query']['bool']['must'][] = [ESClient::MATCH =>
                ['meterial_cat' => $mcat_no2]];
        }
        if (isset($condition['mcat_no3'])) {
            $mcat_no3 = $condition['mcat_no3'];
            $body['query']['bool']['must'][] = [ESClient::MATCH =>
                ['meterial_cat' => $mcat_no3]];
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
            if (!in_array($status, ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {
                $status = 'VALID';
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
            $body['query']['bool']['must'][] = [ESClient::MATCH_PHRASE => ['source' => $source]];
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
        if (isset($condition['name'])) {
            $name = $condition['name'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['name' => ['query' => $name,
                        'minimum_should_match' => '75%']]];
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
            $body['query']['bool']['must'][] = [ESClient::MULTI_MATCH => [
                    'query' => $show_name,
                    'type' => 'most_fields',
                    'fields' => ['show_name', 'attrs', 'specs', 'spu', 'source', 'brand', 'skus']
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
                    , 'profile', 'supplier_name', 'source', 'supplier_id', 'attachs', 'brand',
                    'recommend_flag', 'supply_capabilitys', 'tech_paras', 'meterial_cat',
                    'brand', 'supplier_name'];
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
            $allcount = $es->setbody($newbody)
                    ->count($this->dbName, $this->tableName . '_' . $lang);
            return [$es->setbody($body)
                        ->setfields($_source)
                        ->setsort('sort_order', 'desc')
                        ->setsort('_score', 'desc')
                        ->setaggs('meterial_cat_no', 'meterial_cat_no')
                        ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $from, $pagesize, $allcount['count']];
        } catch (Exception $ex) {

            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    public function getcount($condition, $lang = 'en') {

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

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix
     */

    public function getmeterial_catlist($condition, $lang = 'en') {

        try {
            $body = $this->getCondition($condition);
            $pagesize = 50;
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
                            ->setaggs('meterial_cat_no', 'meterial_cat_no')
                            ->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize);
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
            $data = $this->getmeterial_catlist($condition, $lang);
            $show_model = new ShowCatModel();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 根据spu获取sku数   (这里不包括删除的)
     * @author link
     * @param string $spus spu编码
     * @param string $lang 语言
     * @retrun int
     */
    public function getCountBySpus($spus = '', $lang = '') {
        $condition = array(
            'status' => array('neq', self::STATUS_DELETED)
        );
        if ($spus != '') {
            $condition['spu'] = ['in', $spus];
        }
        if ($lang != '') {
            $condition['lang'] = $lang;
        }
        try {
//redis 操作
            $redis_key = md5(json_encode($condition));
            if (redisExist($redis_key)) {
                return redisGet($redis_key);
            } else {
                $count = $this->field('count(id)')->where($condition)->group('spu')->select();
                redisSet($redis_key, $count);
                return $count ? $count : [];
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /* 通过ES 获取数据列表
     * @param string $sku // 商品名称 属性名称或属性值
     * @param string $lang // 展示分类编码
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
     * @param string $spu // 商品名称 属性名称或属性值
     * @param string $lang // 展示分类编码
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
     * @param mix $cat_no // 物料分类编码数组3f
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


            if (!$cat2s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name']];
                }
                return $newcat3s;
            }
            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }

            $cat1s = $this->table('erui_goods.t_material_cat')
                    ->field('id,cat_no,name')
                    ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $newcat1s = [];
            if (!$cat1s) {
                $newcat3s = [];
                $newcat2s = [];
                foreach ($cat2s as $val) {
                    $newcat2s[$val['cat_no']] = $val;
                }
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name'],
                        'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                        'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    ];
                }
                return $newcat3s;
            }
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
//  ->field('spu,attr_name,attr_value,attr_no')
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
                        'spec_flag' => 'N',
                        'status' => 'VALID'])
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
            $specs = $this->table('erui_goods.t_goods')->field('sku,spu,`name`,`model`,`show_name`')
                    ->where(['spu' => ['in', $spus], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $ret = [];
            if ($specs) {
                foreach ($specs as $spec) {
                    $spu = $spec['spu'];
                    $sku = $spec['sku'];
                    unset($spec['spu']);
                    // unset($spec['sku']);
                    $ret[$spu][$sku] = $spec;
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
     * 根据SPUS 获取产品展示分类信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  展示分类信息列表
     */

    public function getshow_catsbyspus($spus, $lang = 'en') {
        try {

            $show_cat_products = $this->table('erui_goods.t_show_cat_product scp')
                    ->join('erui_goods.t_show_cat sc on scp.cat_no=sc.cat_no')
                    ->field('scp.cat_no,scp.spu', 'left')
                    ->where(['scp.spu' => ['in', $spus],
                        'scp.status' => 'VALID',
                        'sc.status' => 'VALID',
                        'sc.lang' => $lang,
                        'sc.id>0',
                    ])
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

    public function getproduct_specsbyskus($spus, $lang = 'en') {
        try {
            $product_attrs = $this->table('erui_goods.t_product_attr')
                            ->field('spu,attr_name,attr_value,attr_no')
                            ->where(['spu' => ['in', $spus], 'lang' => $lang,
                                'spec_flag' => 'Y', 'status' => 'VALID'
                            ])->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
        $ret = [];
        if (is_array($product_attrs)) {
            foreach ($product_attrs as $item) {
                $sku = $item['spu'];
                unset($item['spu']);
                $ret[$sku][] = $item;
            }
        }
        return $ret;
    }

    /*
     * 根据分类编码数组获取物料分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  规格信息
     */

    public function getshow_material_cats($cat_nos, $lang = 'en') {

        try {
            $show_material_cats = $this->table('erui_goods.t_show_material_cat smc')
                    ->join('erui_goods.t_show_cat sc on smc.show_cat_no=sc.cat_no')
                    ->field('show_cat_no,material_cat_no')
                    ->where([
                        'smc.material_cat_no' => ['in', $cat_nos],
                        'sc.status' => 'VALID',
                        'sc.lang' => $lang,
                        'sc.id>0',
                        'smc.status' => 'VALID'])
                    ->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
        $ret = [];
        if ($show_material_cats) {
            foreach ($show_material_cats as $item) {

                $ret[$item['material_cat_no']][$item['show_cat_no']] = $item['show_cat_no'];
            }
        }

        return $ret;
    }

    /*
     * 根据展示分类编码数组获取展示分类信息
     * @param mix $show_cat_nos // 展示分类编码数组
     * @param string $lang // 语言 zh en ru es
     * @return mix
     */

    public function getshow_cats($show_cat_nos, $lang = 'en') {

        try {
            if ($show_cat_nos) {
                $cat3s = $this->table('erui_goods.t_show_cat')
                        ->field('parent_cat_no,cat_no,name')
                        ->where(['cat_no' => ['in', $show_cat_nos], 'lang' => $lang, 'status' => 'VALID'])
                        ->select();
                $cat1_nos = $cat2_nos = [];
            } else {
                return [];
            }

            if (!$cat3s) {
                return [];
            }

            foreach ($cat3s as $cat) {
                $cat2_nos[] = $cat['parent_cat_no'];
            }
            if ($cat2_nos) {
                $cat2s = $this->table('erui_goods.t_show_cat')
                                ->field('id,cat_no,name,parent_cat_no')
                                ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            }
            if (!$cat2s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name']
                    ];
                }
                return $newcat3s;
            }
            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }
            if ($cat1_nos) {
                $cat1s = $this->table('erui_goods.t_show_cat')->field('id,cat_no,name')
                                ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            }

            $newcat2s = [];
            foreach ($cat2s as $val) {
                $newcat2s[$val['cat_no']] = $val;
            }
            if (!$cat1s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name'],
                        'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                        'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    ];
                }
                return $newcat3s;
            }
            $newcat1s = [];
            foreach ($cat1s as $val) {
                $newcat1s[$val['cat_no']] = $val;
            }
            foreach ($cat3s as $val) {
                $newcat3s[$val['cat_no']] = [
                    'cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['cat_no'],
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

    /* 通过SKU获取数据商品文件列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     */

    public function getproduct_attachsbyspus($spus, $lang = 'en') {

        try {
            $product_attachs = $this->table('erui_goods.t_product_attach')
                    ->field('id,attach_type,attach_url,attach_name,attach_url,spu')
                    ->where(['spu' => ['in', $spus],
                        'attach_type' => ['in', ['BIG_IMAGE', 'MIDDLE_IMAGE', 'SMALL_IMAGE', 'DOC']],
                        'status' => 'VALID'])
                    ->select();
            $ret = [];
            if ($product_attachs) {
                foreach ($product_attachs as $item) {
                    $data['attach_name'] = $item['attach_name'];
                    $data['attach_url'] = $item['attach_url'];
                    $ret[$item['spu']][$item['attach_type']][] = $data;
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
     * 根据SPU数组获取展示属性信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es
     * @return mix
     */

    public function getproductattrsbyspus($spus, $lang = 'en') {
        try {
            $products = $this->where(['spu' => ['in', $spus], 'lang' => $lang])
                    ->field('spu,meterial_cat_no,brand,supplier_id,supplier_name,source,meterial_cat_no')
                    ->select();


            $brands = [];
            $supplier_ids = [];
            $supplier_names = [];
            $sources = [];
            $meterial_cat_nos = [];
            $spus = $mcat_nos = [];
            foreach ($products as $item) {

                $mcat_nos[] = $item['meterial_cat_no'];
                $spus[] = $item['spu'];
                $brands[$item['spu']] = $item['brand'];
                $supplier_ids[$item['spu']] = $item['supplier_id'];
                $supplier_names[$item['spu']] = $item['supplier_name'];
                $sources[$item['spu']] = $item['source'];
                $meterial_cat_nos[$item['spu']] = $item['meterial_cat_no'];
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
                $show_cat = [];
                $show_cat[$scats_no_spu[$item['spu']]] = $scats[$scats_no_spu[$item['spu']]];
                if (isset($scats_no_mcatsno[$item['meterial_cat_no']])) {
                    foreach ($scats_no_mcatsno[$item['meterial_cat_no']] as $show_cat_no) {
                        $show_cat[$show_cat_no] = $scats[$show_cat_no];
                    }
                }
                if (isset($mcats[$item['meterial_cat_no']])) {
                    $body['meterial_cat'] = json_encode($mcats[$item['meterial_cat_no']], JSON_UNESCAPED_UNICODE);
                } else {
                    $body['meterial_cat'] = json_encode(new stdClass(), JSON_UNESCAPED_UNICODE);
                }
                if (!empty($show_cat)) {
                    rsort($show_cat);
                    $body['show_cats'] = json_encode($show_cat, JSON_UNESCAPED_UNICODE);
                } else {
                    $body['show_cats'] = json_encode([], JSON_UNESCAPED_UNICODE);
                }
                if (isset($product_attrs[$item['spu']])) {

                    $body['attrs'] = json_encode($product_attrs[$item['spu']], JSON_UNESCAPED_UNICODE);
                } else {
                    $body['attrs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                }
                $body['brand'] = $brands[$item['spu']];
                $body['supplier_id'] = $supplier_ids[$item['spu']];
                $body['supplier_name'] = $supplier_names[$item['spu']];
                $body['source'] = $sources[$item['spu']];
                $body['meterial_cat_no'] = $meterial_cat_nos[$item['spu']];
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
            $count = $this->where(['lang' => $lang])->count('id');
            $max_id = 0;
            echo '共有', $count, '条记录需要导入!', PHP_EOL;
            $k = 1;
            for ($i = 0; $i < $count; $i += 100) {
                if ($i > $count) {
                    $i = $count;
                }

                $products = $this->where([
                                    'lang' => $lang,
                                    //  'status' => 'VALID',
                                    'id' => ['gt', $max_id]
                                ])
                                ->limit(0, 100)->order('id asc')->select();

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
                    $specs = $this->getproduct_specsbyskus($spus, $lang);
                    $SupplycapabilityModel = new SupplyCapabilityModel();

                    $supply_capabilitys = $SupplycapabilityModel->getlistbycat_nos($mcat_nos, $lang);
                    $es = new ESClient();

                    foreach ($products as $key => $item) {
                        $id = $item['spu'];
                        $body = $item;

                        if ($body['source'] == 'ERUI') {
                            $body['sort_order'] = 100;
                        } else {
                            $body['sort_order'] = 1;
                        }
                        if (in_array($body['brand'], ['KERUI', '科瑞'])) {
                            $body['sort_order'] += 20;
                        }
                        if (isset($skus[$item['spu']])) {
                            $json_skus = $skus[$item['spu']];
                            rsort($json_skus);
                            $body['skus'] = json_encode($json_skus, JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['skus'] = '[]';
                        }
                        if (isset($specs[$item['spu']])) {
                            $body['specs'] = json_encode($specs[$item['spu']], JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['specs'] = json_encode([], JSON_UNESCAPED_UNICODE);
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
                        if (isset($scats_no_mcatsno[$item['meterial_cat_no']])) {
                            foreach ($scats_no_mcatsno[$item['meterial_cat_no']] as $show_cat_no) {
                                $show_cat[$show_cat_no] = $scats[$show_cat_no];
                            }
                        }
                        if (isset($mcats[$item['meterial_cat_no']])) {
                            $body['meterial_cat'] = json_encode($mcats[$item['meterial_cat_no']], JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['meterial_cat'] = json_encode(new \stdClass(), JSON_UNESCAPED_UNICODE);
                        }
                        if ($show_cat) {
                            rsort($show_cat);
                            $body['show_cats'] = json_encode($show_cat, JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['show_cats'] = json_encode([], JSON_UNESCAPED_UNICODE);
                        }
                        if (isset($product_attrs[$item['spu']])) {
                            $body['attrs'] = json_encode($product_attrs[$item['spu']], JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['attrs'] = json_encode([], JSON_UNESCAPED_UNICODE);
                        }

                        if (isset($supply_capabilitys[$item['meterial_cat_no']])) {
                            $body['supply_capabilitys'] = json_encode($supply_capabilitys[$item['meterial_cat_no']], JSON_UNESCAPED_UNICODE);
                        } else {
                            $body['supply_capabilitys'] = json_encode([], JSON_UNESCAPED_UNICODE);
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

    public function getInsertCodition($condition, $lang = 'en') {
        $data = [];
        if (isset($condition['id'])) {
            $data['id'] = $condition['id'];
        }
        $data['lang'] = $lang;
        if (isset($condition['meterial_cat_no'])) {
            $material_cat_no = $data['meterial_cat_no'] = $condition['meterial_cat_no'];
            $mcatmodel = new MaterialcatModel();
            $data['meterial_cat'] = json_encode($mcatmodel->getinfo($material_cat_no, $lang), 256);
            $smmodel = new ShowmaterialcatModel();
            $show_cat_nos = $smmodel->getshowcatnosBymatcatno($material_cat_no, $lang);
            $scats = $this->getshow_cats($show_cat_nos, $lang);
            $data['show_cats'] = json_encode($scats[$material_cat_no], 256);
            $SupplycapabilityModel = new SupplyCapabilityModel();
            $supply_capabilitys = $SupplycapabilityModel->getlistbycat_nos([$material_cat_no], $lang);
            if (isset($supply_capabilitys[$material_cat_no])) {
                $data['supply_capabilitys'] = json_encode($supply_capabilitys[$material_cat_no], 256);
            } else {
                $data['supply_capabilitys'] = json_encode([]);
            }
        } else {
            $data['meterial_cat_no'] = '';
            $data['meterial_cat'] = json_encode(new \stdClass());
            $data['show_cats'] = json_encode([]);
            $data['supply_capabilitys'] = json_encode([]);
        }
        if (isset($condition['spu'])) {
            $spu = $data['spu'] = $condition['spu'];

            $product_attrs = $this->getproduct_attrbyspus([$spu], $lang);
            $specs = $this->getproduct_specsbyskus([$spu], $lang);
            $attachs = $this->getproduct_attachsbyspus([$spu], $lang);
            if (isset($product_attrs[$spu])) {
                $data['attrs'] = json_encode($product_attrs[$spu], 256);
            } else {
                $data['attrs'] = json_encode([], 256);
            }
            if (isset($specs[$spu])) {
                $data['specs'] = json_encode($specs[$spu], 256);
            } else {
                $data['specs'] = json_encode([], 256);
            }
            if (isset($attachs[$spu])) {
                $data['attachs'] = json_encode($attachs[$spu], 256);
            } else {
                $data['attachs'] = json_encode([], 256);
            }
        } else {
            $data['spu'] = '';
            $data['attrs'] = json_encode([], 256);
            $data['specs'] = json_encode([], 256);
            $data['attachs'] = json_encode([], 256);
        }
        if (isset($condition['qrcode'])) {
            $data['qrcode'] = $condition['qrcode'];
        } else {
            $data['qrcode'] = null;
        }
        if (isset($condition['name'])) {
            $data['name'] = $condition['name'];
        } else {
            $data['name'] = '';
        }
        if (isset($condition['show_name'])) {
            $data['show_name'] = $condition['show_name'];
        } else {
            $data['show_name'] = '';
        }
        if (isset($condition['keywords'])) {
            $data['keywords'] = $condition['keywords'];
        } else {
            $data['keywords'] = '';
        }

        if ($condition['exe_standard']) {
            $data['exe_standard'] = $condition['exe_standard'];
        } else {
            $data['exe_standard'] = '';
        }
        if (isset($condition['app_scope'])) {
            $data['app_scope'] = $condition['app_scope'];
        } else {
            $data['app_scope'] = '';
        }
        if (isset($condition['tech_paras'])) {
            $data['tech_paras'] = $condition['tech_paras'];
        } else {
            $data['tech_paras'] = '';
        }
        if (isset($condition['advantages'])) {
            $data['advantages'] = $condition['advantages'];
        } else {
            $data['advantages'] = '';
        }
        if (isset($condition['profile'])) {
            $data['profile'] = $condition['profile'];
        } else {
            $data['profile'] = '';
        }
        if ($condition['description']) {
            $data['description'] = $condition['description'];
        }

        if (isset($condition['supplier_id'])) {
            $data['supplier_id'] = $condition['supplier_id'];
        } else {
            $data['supplier_id'] = '';
        }
        if (isset($condition['supplier_name'])) {
            $data['supplier_name'] = $condition['supplier_name'];
        } else {
            $data['supplier_name'] = '';
        }
        if (isset($condition['brand'])) {
            $data['brand'] = $condition['brand'];
        } else {
            $data['brand'] = '';
        }
        if (isset($condition['warranty'])) {
            $data['warranty'] = $condition['warranty'];
        } else {
            $data['warranty'] = '';
        }
        if (isset($condition['customization_flag'])) {
            $data['customization_flag'] = $condition['customization_flag'] == 'Y' ? 'Y' : 'N';
        } else {
            $data['customization_flag'] = 'N';
        }
        if (isset($condition['customizability'])) {
            $data['customizability'] = $condition['customizability'];
        } else {
            $data['customizability'] = '';
        }

        if (isset($condition['availability'])) {
            $data['availability'] = $condition['availability'];
        } else {
            $data['availability'] = '';
        }
        if (isset($condition['resp_time'])) {
            $data['resp_time'] = $condition['resp_time'];
        } else {
            $data['resp_time'] = '';
        }
        if (isset($condition['resp_rate'])) {
            $data['resp_rate'] = $condition['resp_rate'];
        } else {
            $data['resp_rate'] = '';
        }
        if (isset($condition['delivery_cycle'])) {
            $data['delivery_cycle'] = $condition['delivery_cycle'];
        } else {
            $data['delivery_cycle'] = '';
        }
        if (isset($condition['target_market'])) {
            $data['target_market'] = $condition['target_market'];
        } else {
            $data['target_market'] = '';
        }

        if (isset($condition['source'])) {
            $data['source'] = $condition['source'];
        } else {
            $data['source'] = '';
        }

        if (isset($condition['source_detail'])) {
            $data['source_detail'] = $condition['source_detail'];
        } else {
            $data['source_detail'] = '';
        }
        if (isset($condition['recommend_flag'])) {
            $data['recommend_flag'] = $condition['recommend_flag'] == 'Y' ? 'Y' : 'N';
        } else {
            $data['recommend_flag'] = 'N';
        }
        if (isset($condition['status']) && in_array($condition['status'], ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {
            $data['status'] = strtoupper($condition['status']);
        } else {
            $data['status'] = 'CHECKING';
        }
        if (isset($condition['created_by'])) {
            $data['status'] = $condition['created_by'];
        } else {
            $data['status'] = '';
        }
        if (isset($condition['created_at'])) {
            $data['created_at'] = $condition['created_at'];
        } else {
            $data['created_at'] = '';
        }
        if (isset($condition['updated_by'])) {
            $data['updated_by'] = $condition['updated_by'];
        } else {
            $data['updated_by'] = '';
        }
        if (isset($condition['updated_at'])) {
            $data['updated_at'] = $condition['updated_at'];
        } else {
            $data['updated_at'] = '';
        }
        if (isset($condition['checked_by'])) {
            $data['checked_by'] = $condition['checked_by'];
        } else {
            $data['checked_by'] = '';
        }

        if (isset($condition['checked_at'])) {
            $data['checked_at'] = $condition['checked_at'];
        } else {
            $data['checked_at'] = '';
        }
        if (isset($condition['skus'])) {
            $goodsmodel = new GoodsModel();
            $data['skus'] = $goodsmodel->getskusbyspu($data, $lang);
        } else {
            $data['skus'] = json_encode([]);
        }
        return $data;
    }

    /*
     * 添加产品到Es
     * @param string $lang // 语言 zh en ru es
     * @return mix
     */

    public function create_data($data, $lang = 'en') {
        try {
            $es = new ESClient();
            $body = $this->getInsertCodition($data);
            $id = $data['spu'];
            $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
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

    /*
     * 添加产品到Es
     * @param string $lang // 语言 zh en ru es
     * @return mix
     */

    public function update_data($data, $spu, $lang = 'en') {
        try {
            $es = new ESClient();
            $body = $this->getInsertCodition($data);
            if (empty($spu)) {
                return false;
            }
            $id = $spu;
            $flag = $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
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
     *
     */

    public function changestatus($spu, $lang = 'en') {
        try {
            $es = new ESClient();
            if (empty($spu)) {
                return false;
            }
            if (in_array(strtoupper($status), ['VALID', 'TEST', 'CHECKING', 'CLOSED', 'DELETED'])) {
                $data['status'] = strtoupper($status);
            } else {
                $data['status'] = 'CHECKING';
            }
            $id = $spu;
            $flag = $es->update_document($this->dbName, $this->tableName . '_' . $lang, $data, $id);
            return true;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /* 新增ES
     *
     */

    public function Updateshowcats($spu = null, $lang = 'en') {

        if (empty($spu)) {
            return false;
        }
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $showcatproduct_model = new ShowCatProductModel();
        $show_cat_nos = $showcatproduct_model->getShowCatnosBySpu($spu, $lang);
        $scats = $this->getshow_cats($show_cat_nos, $lang);
        $data['show_cats'] = json_encode($scats, 256);
        $product = ['update' => [
                "_id" => $id,
                "_type" => $type,
                "_index" => $this->dbName,
            ]
        ];
        $product_doc['doc'] = $data;
        $esgoodsdata = [
            "doc" => [
                "show_cats" => $data['show_cats'],
            ],
            "query" => [
                ESClient::MATCH_PHRASE => [
                    "spu" => $spu
                ]
            ]
        ];
        $goods = ['_update_by_query' => [
                "_type" => 'goods_' . $lang,
                "_index" => $this->dbName,
            ]
        ];


        return [$product, $product_doc, $goods, $esgoodsdata];
    }

    /* 新增ES
     * $substr 替换前的内容, 需要替换的内容
     * $replacement 替换后的内容
     */

    public function update_showcats($spus, $lang = 'en') {
        if (empty($spus)) {
            return false;
        }
        $bulk = [];
        foreach ($spus as $spu) {
            $re = $this->Updateshowcats($spu, $lang);
            if ($re) {
                $bulk[] = $re[0];
                $bulk[] = $re[1];
                $bulk[] = $re[2];
                $bulk[] = $re[3];
            }
        }
        if ($bulk) {
            $es = new ESClient();
            $es->bulk($bulk);
            return true;
        } else {
            return false;
        }
    }

    /* 新增ES
     *
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
        $data['meterial_cat'] = json_encode($mcatmodel->getinfo($new_cat_no, $lang), 256);
        $smmodel = new ShowmaterialcatModel();
        $show_cat_nos = $smmodel->getshowcatnosBymatcatno($new_cat_no, $lang);
        $scats = $this->getshow_cats($show_cat_nos, $lang);
        $data['show_cats'] = json_encode($scats[$new_cat_no], 256);
        $SupplycapabilityModel = new SupplyCapabilityModel();
        $supply_capabilitys = $SupplycapabilityModel->getlistbycat_nos([$new_cat_no], $lang);
        $data['supply_capabilitys'] = json_encode($supply_capabilitys[$new_cat_no], 256);
        $data['material_cat_no'] = $new_cat_no;
        if ($spu) {
            $id = $spu;
            $es->update_document($this->dbName, $type, $data, $id);
        } else {
            $es_product_data = [
                "doc" => [
                    "meterial_cat" => $data['meterial_cat'],
                    "show_cats" => $data['show_cats'],
                    'material_cat_no' => $new_cat_no,
                    'supply_capabilitys' => $data['supply_capabilitys']
                ],
                "query" => [
                    ESClient::MATCH_PHRASE => [
                        "material_cat_no" => $material_cat_no
                    ]
                ]
            ];
            $es->UpdateByQuery($this->dbName, 'product_' . $lang, $es_product_data);
        }
        if ($spu) {
            $esgoodsdata = [
                "doc" => [
                    "meterial_cat" => $data['meterial_cat'],
                    "show_cats" => $data['show_cats'],
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
                    "meterial_cat" => $data['meterial_cat'],
                    "show_cats" => $data['show_cats'],
                ],
                "query" => [
                    ESClient::MATCH_PHRASE => [
                        "material_cat_no" => $material_cat_no
                    ]
                ]
            ];
        }
        $es->UpdateByQuery($this->dbName, 'goods_' . $lang, $esgoodsdata);
        return true;
    }

    /* 新增ES
     *
     */

    public function Update_Attrs($spu, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        $product_attrs = $this->getproduct_attrbyspus([$spu], $lang);
        $specs = $this->getproduct_specsbyskus([$spu], $lang);
        $id = $spu;
        $data['attrs'] = json_encode($product_attrs[$spu], 256);
        $data['specs'] = json_encode($specs[$spu], 256);
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);
        $goodsmodel = new GoodsModel();
        $sku_infos = $goodsmodel->getskusbyspu($spu, $lang);
        $esgoodsmodel = new EsgoodsModel();
        foreach ($sku_infos as $sku) {
            $esgoodsmodel->Update_Attrs($sku['sku'], $lang, $product_attrs, $specs);
        }
        return true;
    }

    /* 新增ES
     *
     */

    public function Update_Attachs($spu, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        $attachs = $this->getproduct_attachsbyspus([$spu], $lang);
        if (isset($attachs[$spu])) {
            $data['attachs'] = json_encode($attachs[$spu], 256);
        } else {
            $data['attachs'] = '[]';
        }
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);


        return true;
    }

    /* 新增ES
     *
     */

    public function Update_skus($spu, $skus, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        $goodsmodel = new GoodsModel();

        $skuinfos = $goodsmodel->getskusbyskus($skus, $lang);
        if ($skuinfos) {
            $data['skus'] = json_encode($skuinfos, 256);
        } else {
            $data['skus'] = '[]';
        }
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);
        return true;
    }

    /* 新增ES
     *
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

    /* 新增ES
     *
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

    /* 新增ES
     *
     */

    public function Update_supplier_name($spu, $supplier_name, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        if ($supplier_name) {
            $data['supplier_name'] = $supplier_name;
        } else {
            $data['supplier_name'] = '';
        }
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $es->update_document($this->dbName, $type, $data, $id);

        $esgoodsdata = [
            "doc" => [
                "supplier_name" => $supplier_name,
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

    public function delete_data($spu, $lang = 'en') {
        $es = new ESClient();
        if (empty($spu)) {
            return false;
        }
        $data['status'] = self::STATUS_DELETED;
        $id = $spu;
        $type = $this->tableName . '_' . $lang;
        $flag = $es->update_document($this->dbName, $type, $data, $id);
        return true;
    }

}
