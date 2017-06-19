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
    protected $langs = ['en', 'es', 'ru', 'zh'];

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

    /*
     * goods 数据导入
     */

    public function importgoodsAction($lang = 'en') {
        $espoductmodel = new EsgoodsModel();
        $espoductmodel->importgoodss($lang);
    }

    /*
     * product数据导入
     */

    public function importproductsAction($lang = 'en') {
        $espoductmodel = new EsProductModel();
        $espoductmodel->importproducts($lang);
    }

    public function indexAction() {
        $model = new EsgoodsModel();

        foreach ($this->langs as $lang) {
            $this->goodsAction($lang);

            $this->productAction($lang);
        }
        $data['code'] = 0;
        $data['message'] = '初始化成功!';
        $this->jsonReturn($data);
       
    }

    public function goodsAction($lang = 'en') {
        if (!in_array($lang, $this->langs)) {

            $lang = 'en';
        }
        $type = 'goods_' . $lang;
        $id = 0;
        $body = [
            'lang' => $lang,
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
            "meterial_cat" => json_encode(['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',], JSON_UNESCAPED_UNICODE),
            'show_cats' => json_encode([['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]], JSON_UNESCAPED_UNICODE),
            "attrs" => json_encode([['id' => "",
            'lang' => $lang,
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
            'created_at' => "",]], JSON_UNESCAPED_UNICODE)
        ];
//        $body['settings'] ['analysis'] = [
//            'analyzer' => [
//                'indexAnalyzer' => [
//                    'type' => 'custom',
//                    'tokenizer' => 'ik',
//                    'filter' => ['name', 'show_name']
//                ],
//                'searchAnalyzer' => [
//                    'type' => 'custom',
//                    'tokenizer' => 'ik',
//                    'filter' => ['name', 'show_name', 'meterial_cat', 'show_cat', 'attrs']
//                ]
//            ],
//            'filter' => [
//                'name' => [
//                    'type' => 'string',
//                    'language' => $lang,
//                    'analyzer' => 'ik'
//                ],
//                'show_name' => [
//                    'type' => 'custom',
//                    'language' => $lang
//                ,
//                    'analyzer' => 'ik'
//                ],
//                'meterial_cat' => [
//                    'type' => 'custom',
//                    'language' => $lang,
//                    'analyzer' => 'ik'
//                ],
//                'show_cat' => [
//                    'type' => 'custom',
//                    'language' => $lang,
//                    'analyzer' => 'ik'
//                ],
//                'attrs' => [
//                    'type' => 'custom',
//                    'language' => $lang,
//                    'analyzer' => 'ik'
//                ],
//            ]
//        ];
        $body[$type] = [
            '_source' => [
                'enabled' => true
            ],
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'show_name' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'meterial_cat' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'show_cat' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'attrs' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
            ]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

    public function productAction($lang = 'en') {

        if (!in_array($lang, $this->langs)) {

            $lang = 'en';
        }
        $type = 'product_' . $lang;
        $id = 0;
        $body = [
            'lang' => $lang,
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
            "meterial_cat" => json_encode(['mcat_no1' => '',
                'mcat_no2' => '',
                'mcat_no3' => '',
                'mcat_name1' => '',
                'mcat_name2' => '',
                'mcat_name3' => '',], JSON_UNESCAPED_UNICODE),
            'show_cats' => json_encode([['cat_no1' => '',
            'cat_no2' => '',
            'cat_no3' => '',
            'cat_name1' => '',
            'cat_name2' => '',
            'cat_name3' => '',]], JSON_UNESCAPED_UNICODE),
            "attrs" => json_encode([['id' => "",
            'lang' => $lang,
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
            'created_at' => "",]], JSON_UNESCAPED_UNICODE)
        ];
        $body[$type] = [
            '_source' => [
                'enabled' => true
            ],
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'show_name' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'meterial_cat' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'show_cat' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
                'attrs' => [
                    'type' => 'string',
                    'analyzer' => 'ik'
                ],
            ]
        ];
        $this->es->create_index($this->index, $type, $body, $id);
    }

}
