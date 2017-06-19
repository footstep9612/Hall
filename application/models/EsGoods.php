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

            $body['goods_attrs'] = $goods_attrs[$item['sku']];
            $body['product_attrs'] = $productattrs[$item['spu']]['attrs'];

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

        $productattrs = $espoducmodel->getproductattrsbyspus($spus, $lang);
        $goods_attrs = $this->getgoods_attrbyskus($spus, $lang);
        $ret = [];
        foreach ($goodss as $item) {
            $id = $item['id'];
            $body = $item;

            $body['meterial_cat'] = $productattrs[$item['spu']]['meterial_cat'];
            $body['show_cat'] = $productattrs[$item['spu']]['show_cat'];

            $body['goods_attrs'] = $goods_attrs[$item['sku']];
            $body['product_attrs'] = $productattrs[$item['spu']]['attrs'];


            $es->add_document($this->dbName, $this->tableName, $body, $id);
        }
    }

}
