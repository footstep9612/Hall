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
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix  
     */

    public function getgoods($condition, $lang = 'en') {


        $body = [];
        $name = $sku = $spu = $show_cat_no = $status = $show_name = $attrs = '';
        if (isset($condition['sku'])) {
            $sku = $condition['sku'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['sku' => $sku]];
        }
        if (isset($condition['spu'])) {
            $spu = $condition['spu'];
            $body['query']['bool']['must'][] = [ESClient::TERM => ['spu' => $spu]];
        }
        if (isset($condition['show_cat_no'])) {
            $show_cat_no = $condition['show_cat_no'];
            $body['query']['bool']['must'][] = [ESClient::MATCH => ['show_cats' => $show_cat_no]];
        }
        if (isset($condition['show_name'])) {
            $show_name = $condition['show_name'];
            $body['query']['bool']['must'][] = ['bool' => ['should' => [
                        [ESClient::MATCH => ['show_name' => $show_name]],
                        [ESClient::MATCH => ['attrs' => $show_name]],
                        [ESClient::MATCH => ['specs' => $show_name]],
                    ]
            ]];
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
        return $es->setbody($body)->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize);
    }

    /* 通过ES 获取数据列表
     * @param string $name // 商品名称 属性名称或属性值
     * @param string $show_cat_no // 展示分类编码
     * @return mix  
     */

    public function getGoodsbysku($sku, $lang = 'en') {
        $es = new ESClient();
        $es->setmust(['sku' => $sku], ESClient::TERM);
        return $es->search($this->dbName, $this->tableName . '_' . $lang);
    }

    /* 通过ES 获取数据列表
     * @param string $name // 商品名称 属性名称或属性值
     * @param string $show_cat_no // 展示分类编码
     * @return mix  
     */

    public function getGoodsbyspu($sku, $lang = 'en') {
        $es = new ESClient();
        $es->setmust(['sku' => $sku], ESClient::TERM);
        return $es->search($this->dbName, $this->tableName . '_' . $lang);
    }

    /* 通过SKU获取数据商品属性列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix  
     */

    public function getgoods_attrbyskus($skus, $lang = 'en') {
        $product_attrs = $this->table('erui_db_ddl_goods.t_goods_attr')
                ->field('*')
                ->where(['sku' => ['in', $skus], 'lang' => $lang])
                ->select();
        $ret = [];
        foreach ($product_attrs as $item) {

            $ret[$item['sku']][] = $item;
        }
        return $ret;
    }

    /* 通过SKU获取数据商品规格列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix  
     */

    public function getgoods_specsbyskus($skus, $lang = 'en') {
        $product_attrs = $this->table('erui_db_ddl_goods.t_goods_attr')
                ->field('sku,attr_name,attr_value,attr_no')
                ->where(['sku' => ['in', $skus],
                    'lang' => $lang,
                    'spec_flag' => 'Y',
                ])
                ->select();
        $ret = [];
        foreach ($product_attrs as $item) {
            $sku = $item['sku'];
            unset($item['sku']);
            $ret[$sku][] = $item;
        }
        return $ret;
    }

    /* 通过SKU获取数据商品产品属性分类等信息列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix  
     */

    public function getproductattrsbyspus($skus, $lang = 'en') {
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
    }

    /* 通过批量导入商品信息到ES

     * @param string $lang // 语言
     * @return mix  
     */

    public function importgoodss($lang = 'en') {
        $goodss = $this->where(['lang' => $lang])
                ->select();
        $spus = $skus = [];
        foreach ($goodss as $item) {
            $skus[] = $item['sku'];
            $spus[] = $item['spu'];
        }


        $spus = array_unique($spus);
        $skus = array_unique($skus);
        $espoducmodel = new EsProductModel();
        $es = new ESClient();
        $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);


        $goods_attrs = $this->getgoods_attrbyskus($spus, $lang);
        $specs = $this->getgoods_specsbyskus($skus, $lang);
        foreach ($goodss as $item) {
            $id = $item['id'];
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
    }

}
