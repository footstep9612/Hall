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

    public function getgoods_attrbyskus($skus, $lang = 'en') {
        $product_attrs = $this->table('erui_db_ddl_goods.t_goods_attr')
                ->field('*')
                ->where(['sku' => ['in', $skus], 'lang' => $lang])
                ->select();
        $ret = [];
        foreach ($show_cat_products as $item) {

            $ret[$item['sku']][] = $item;
        }
        return $ret;
    }

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
        $ret = [];
        foreach ($goodss as $item) {
            $id = $item['id'];
            $body = $item;

            $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];
            $body['show_cat'] = $productattrs[$item['spu']]['show_cat'];

            $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);
            foreach ($goods_attrs[$item['sku']] as $attr) {

                array_push($product_attrs, $attr);
            }
          $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);

            $ret[$id] = $body;
        }
        return $ret;
    }

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

        foreach ($goodss as $item) {
            $id = $item['id'];
            $body = $item;

            $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];
            $body['show_cat'] = $productattrs[$item['spu']]['show_cat'];


            $product_attrs = json_decode($productattrs[$item['spu']]['attrs'], true);
            foreach ($goods_attrs[$item['sku']] as $attr) {

                array_push($product_attrs, $attr);
            }
            $body['attrs'] = json_encode($product_attrs, JSON_UNESCAPED_UNICODE);
            $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
        }
    }

}
