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
class EsgoodsController extends ShopMallController {

    protected $index = 'erui_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '5';

    //put your code here
    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
        $lang = $this->getPut('lang', 'en');
        $this->setLang($lang);
        $this->es = new ESClient();

         parent::init();
    }


    public function listAction() {
        $this->setLang('zh');
        $model = new EsgoodsModel();
        $ret = $model->getgoods($this->put_data, null, $this->getLang());
        if ($ret) {
            $list = [];
            $data = $ret[0];
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);

            foreach ($data['hits']['hits'] as $key => $item) {
                $list[$key] = $item["_source"];
                $list[$key]['id'] = $item['_id'];
            }


            $send['list'] = $list;
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function goodsAction($lang = 'en') {
        if (!in_array($lang, $this->langs)) {

            $lang = 'en';
        }
        $type_string = 'text';
        $analyzer = 'ik_max_word';
        if ($this->version != 5) {
            $type_string = 'string';
            $analyzer = 'ik';
        }

        $body = ['properties' => [
                'id' => [
                    'type' => 'integer',
                    "index" => "not_analyzed",
                ],
                'lang' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'spu' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'sku' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'package_quantity' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'exw_day' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'qrcode' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'attachs' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'model' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'show_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'purchase_price1' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'purchase_price2' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'purchase_price_cur' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'purchase_unit' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'pricing_flag' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'created_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'created_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ],
                'meterial_cat' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'show_cats' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'attrs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'specs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],]];

        return $body;
    }

    public function productAction($lang = 'en') {

        $type_string = 'text';
        $analyzer = 'ik_max_word';
        if ($this->version != 5) {
            $type_string = 'string';
            $analyzer = 'ik';
        }
        $body = ['properties' => [
                'id' => [
                    'type' => 'integer',
                    "index" => "not_analyzed",
                ],
                'lang' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'meterial_cat_no' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'spu' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'skus' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'attachs' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'qrcode' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'show_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'keywords' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'exe_standard' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'app_scope' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'tech_paras' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'advantages' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'profile' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'supplier_id' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'supplier_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'brand' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 2
                ],
                'source' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'source_detail' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'recommend_flag' => [
                    'type' => $type_string,
                    'analyzer' => 'whitespace'
                ],
                'status' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'created_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'created_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ], 'updated_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'updated_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ], 'checked_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'checked_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ],
                'meterial_cat' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'show_cats' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'attrs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'specs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],]];
        return $body;
    }

}
