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
class EsProductModel extends PublicModel {

    //put your code here
    protected $tableName = 'product';
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    public function getmaterial_cat($cat_no, $lang = 'en') {
        $cat3 = $this->table('erui_db_ddl_goods.t_material_cat')
                ->field('id,cat_no,name')
                ->where(['cat_no' => $cat_no, 'lang' => $lang])
                ->find();
        $cat2 = $this->table('erui_db_ddl_goods.t_material_cat')
                ->field('id,cat_no,name')
                ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang])
                ->find();
        $cat1 = $this->table('erui_db_ddl_goods.t_material_cat')
                ->field('id,cat_no,name')
                ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang])
                ->find();
        return [$cat1['cat_no'], $cat1['name'], $cat2['cat_no'], $cat2['name'], $cat3['cat_no'], $cat3['name']];
    }

    public function getmaterial_cats($cat_nos, $lang = 'en') {
        $cat3s = $this->table('erui_db_ddl_goods.t_material_cat')
                ->field('id,cat_no,name,parent_cat_no')
                ->where(['cat_no' => ['in', $cat_nos], 'lang' => $lang])
                ->select();
        $cat1_nos = $cat2_nos = [];
        foreach ($cat3s as $cat) {
            $cat2_nos[] = $cat['parent_cat_no'];
        }
        $cat2s = $this->table('erui_db_ddl_goods.t_material_cat')
                ->field('id,cat_no,name,parent_cat_no')
                ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang])
                ->select();
        foreach ($cat2s as $cat2) {
            $cat1_nos[] = $cat2['parent_cat_no'];
        }
        $cat1s = $this->table('erui_db_ddl_goods.t_material_cat')
                ->field('id,cat_no,name')
                ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang])
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
    }

    public function getproduct_attrbyspus($spus, $lang = 'en') {
        $product_attrs = $this->table('erui_db_ddl_goods.t_product_attr')
                ->field('*')
                ->where(['spu' => ['in', $spus], 'lang' => $lang])
                ->select();
        $ret = [];

        foreach ($product_attrs as $item) {

            $ret[$item['spu']][] = $item;
        }
        return $ret;
    }

    public function getshow_catsbyspus($spus, $lang = 'en') {
        $show_cat_products = $this->table('erui_db_ddl_goods.t_show_cat_product')
                ->field('show_cat_no,spu')
                ->where(['spu' => ['in', $spus]])
                ->select();
        $ret = [];
        foreach ($show_cat_products as $item) {

            $ret[$item['spu']] = $item['show_cat_no'];
        }
        return $ret;
    }

    public function getshow_material_cats($cat_nos, $lang = 'en') {


        $show_material_cats = $this->table('erui_db_ddl_goods.t_show_material_cat')
                ->field('show_cat_no,material_cat_no')
                ->where(['material_cat_no' => ['in', $cat_nos]])
                ->select();

        $ret = [];
        foreach ($show_material_cats as $item) {

            $ret[$item['material_cat_no']][$item['show_cat_no']] = $item['show_cat_no'];
        }

        return $ret;
    }

    public function getshow_cats($show_cat_nos, $lang = 'en') {
        $cat3s = $this->table('erui_db_ddl_goods.t_show_cat')
                ->field('parent_cat_no,cat_no,name')
                ->where(['cat_no' => ['in', $show_cat_nos], 'lang' => $lang])
                ->select();
        $cat1_nos = $cat2_nos = [];
        foreach ($cat3s as $cat) {
            $cat2_nos[] = $cat['parent_cat_no'];
        }

        $cat2s = $this->table('erui_db_ddl_goods.t_show_cat')
                        ->field('id,cat_no,name,parent_cat_no')
                        ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang])->select();
        foreach ($cat2s as $cat2) {
            $cat1_nos[] = $cat2['parent_cat_no'];
        }
        $cat1s = $this->table('erui_db_ddl_goods.t_show_cat')->field('id,cat_no,name')
                        ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang])->select();
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
    }

    public function getproductattrsbyspus($spus, $lang = 'en') {
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
    }

    public function importproducts($lang = 'en') {
        $products = $this->where(['lang' => $lang])
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
        }
        foreach ($scats_no_mcatsno as $showcatnos) {
            foreach ($showcatnos as $show_cat_no) {
                $show_cat_nos[] = $show_cat_no;
            }
        }
        $show_cat_nos = array_unique($show_cat_nos);


        $scats = $this->getshow_cats($show_cat_nos, $lang);

        $es = new ESClient();

        foreach ($products as $item) {
            $id = $item['id'];
            $body = $item;
            $show_cat[$scats_no_spu[$item['spu']]] = $scats[$scats_no_spu[$item['spu']]];


            foreach ($scats_no_mcatsno[$item['meterial_cat_no']] as $show_cat_no) {

                $show_cat[$show_cat_no] = $scats[$show_cat_no];
            }


            $body['meterial_cat'] = json_encode($mcats[$item['meterial_cat_no']], JSON_UNESCAPED_UNICODE);
            $body['show_cats'] = json_encode($show_cat, JSON_UNESCAPED_UNICODE); // $mcats[$item['meterial_cat_no']];
            $body['attrs'] = json_encode($product_attrs[$item['spu']], JSON_UNESCAPED_UNICODE);

           $flag= $es->add_document($this->dbName, $this->tableName . '_' . $lang, $body, $id);
           
          return $flag;
        }
    }

}
