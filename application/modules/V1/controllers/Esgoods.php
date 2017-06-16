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
class EsgoodsController extends PublicController {

    protected $index = 'erui_db_ddl_goods';
    protected $es = '';

    //put your code here
    public function init() {

        
//        $espoductmodel = new EsgoodsModel();
//        $flag = $espoductmodel->getproductattrsbyspus(['01']);
//        echo '<pre>';
//        var_dump($flag);
//        die();
         $this->es = new ESClient();
        //  parent::init();
    }

    public function importgoods_enAction() {
        
    }

    public function importgoods_zhAction() {
        
    }

    public function importgoods_esAction() {
        
    }

    public function importgoods_ruAction() {
        
    }

    public function indexAction() {



        $this->es->delete_index($this->index);
        $this->goods_enAction();
        $this->goods_esAction();
        $this->goods_ruAction();
        $this->goods_zhAction();
        $this->product_enAction();
        $this->product_esAction();
        $this->product_zhAction();
        $this->product_ruAction();

        echo '1';
        die();
    }

    public function goods_enAction() {
        $type = 'goods_en';
        $id = 0;
        $body = [
            'lang' => "en",
            'spu' => "",
            'sku' => "",
            'qrcode' => "",
            'name' => "",
            'show_name' => "",
            'model' => "",
            'description' => "",
            'purchase_price1' => "",
            'purchase_price2' => "",
            'purchase_price_cur' => "",
            'purchase_unit' => "",
            'created_by' => "",
            'created_at' => "",
            "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attr" => [['id' => "",
            'lang' => "ru",
            'spu' => "",
            'sku' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'spec_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function goods_zhAction() {
        $type = 'goods_zh';
        $id = 0;
        $body = [
            'lang' => "zh",
            'spu' => "",
            'sku' => "",
            'qrcode' => "",
            'name' => "",
            'show_name' => "",
            'model' => "",
            'description' => "",
            'purchase_price1' => "",
            'purchase_price2' => "",
            'purchase_price_cur' => "",
            'purchase_unit' => "",
            'created_by' => "",
            'created_at' => "",
            "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attr" => [['id' => "",
            'lang' => "ru",
            'spu' => "",
            'sku' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'spec_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function goods_esAction() {
        $type = 'goods_es';
        $id = 0;
        $body = [
            'lang' => "es",
            'spu' => "",
            'sku' => "",
            'qrcode' => "",
            'name' => "",
            'show_name' => "",
            'model' => "",
            'description' => "",
            'purchase_price1' => "",
            'purchase_price2' => "",
            'purchase_price_cur' => "",
            'purchase_unit' => "",
            'created_by' => "",
            'created_at' => "",
            "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attr" => [['id' => "",
            'lang' => "ru",
            'spu' => "",
            'sku' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'spec_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function goods_ruAction() {
        $type = 'goods_ru';
        $id = 0;
        $body = [
            'lang' => "ru",
            'spu' => "",
            'sku' => "",
            'qrcode' => "",
            'name' => "",
            'show_name' => "",
            'model' => "",
            'description' => "",
            'purchase_price1' => "",
            'purchase_price2' => "",
            'purchase_price_cur' => "",
            'purchase_unit' => "",
            'created_by' => "",
            'created_at' => "",
            "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attr" => [['id' => "",
            'lang' => "ru",
            'spu' => "",
            'sku' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'spec_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function product_enAction() {
        $type = 'product_en';
        $id = 0;
        $body = [
            'lang' => "en",
            'spu' => "",
            'meterial_cat_no' => "",
            'name' => "",
            'show_name' => "",
            'keywords' => "",
            'description' => "",
            'supplier_id' => "",
            'brand' => "",
            'source' => "",
            'source_detail' => "",
            'recommend_flag' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",
            'updated_by' => "",
            'updated_at' => "",
            'checked_by' => "",
            'checked_at' => "",
            "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attrs" => [['id' => "",
            'lang' => "",
            'spu' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function product_zhAction() {
        $type = 'product_zh';
        $id = 0;
        $body = [
            'lang' => "zh",
            'spu' => "",
            'meterial_cat_no' => "",
            'name' => "",
            'show_name' => "",
            'keywords' => "",
            'description' => "",
            'supplier_id' => "",
            'brand' => "",
            'source' => "",
            'source_detail' => "",
            'recommend_flag' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",
            'updated_by' => "",
            'updated_at' => "",
            'checked_by' => "",
            'checked_at' => "", "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attrs" => [['id' => "",
            'lang' => "",
            'spu' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function product_esAction() {
        $type = 'product_es';

        $id = 0;
        $body = [
            'lang' => "es",
            'spu' => "",
            'meterial_cat_no' => "",
            'name' => "",
            'show_name' => "",
            'keywords' => "",
            'description' => "",
            'supplier_id' => "",
            'brand' => "",
            'source' => "",
            'source_detail' => "",
            'recommend_flag' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",
            'updated_by' => "",
            'updated_at' => "",
            'checked_by' => "",
            'checked_at' => "",
            "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attrs" => [['id' => "",
            'lang' => "",
            'spu' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function product_ruAction() {
        $type = 'product_ru';

        $id = 0;
        $body = [
            'lang' => "ru",
            'spu' => "",
            'meterial_cat_no' => "",
            'name' => "",
            'show_name' => "",
            'keywords' => "",
            'description' => "",
            'supplier_id' => "",
            'brand' => "",
            'source' => "",
            'source_detail' => "",
            'recommend_flag' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",
            'updated_by' => "",
            'updated_at' => "",
            'checked_by' => "",
            'checked_at' => "",
            "meterial_cat" => ['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',],
            'show_cat' => [['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]],
            "attrs" => [['id' => "",
            'lang' => "",
            'spu' => "",
            'attr_group' => "",
            'attr_no' => "",
            'attr_name' => "",
            'attr_value_type' => "",
            'attr_value' => "",
            'goods_flag' => "",
            'logistics_flag' => "",
            'hs_flag' => "",
            'required_flag' => "",
            'search_flag' => "",
            'sort_order' => "",
            'status' => "",
            'created_by' => "",
            'created_at' => "",]]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

}
