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
class EsproductController extends PublicController {

    protected $index = 'erui_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];

    //put your code here
    public function init() {


        $this->es = new ESClient();
        //  parent::init();
    }

    

    /*
     * product数据导入
     */

    public function importAction($lang = 'en') {
        try {
            
            $espoductmodel = new EsProductModel();
            $espoductmodel->importproducts($lang);
            $this->setCode(1);
            $this->setMessage('成功!');

            $this->jsonReturn();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            $this->setCode(-2001);
            $this->setMessage('系统错误!');
            $this->jsonReturn([]);
        }
    }

    public function indexAction() {
//        $this->es->delete('index');
    
        //$model = new EsgoodsModel();

        $body['mappings'] = [];

        foreach ($this->langs as $lang) {
            $body['mappings']['goods_' . $lang] = $this->goodsAction($lang);

            $body['mappings']['product_' . $lang] = $this->productAction($lang);
        }

        $this->es->create_index($this->index, $body);
        $this->setCode(1);
        $this->setMessage('成功!');
        $this->jsonReturn($data);
    }

   
    public function listAction() {

        $model = new EsProductModel();
        $ret = $model->getproducts($this->put_data, $this->getLang());
        if ($ret) {
            $list = [];
            $data = $ret[0];       
            $send['count'] = intval($flag['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);

            foreach ($data['hits']['hits'] as $key => $item) {
                $list[$key] = $item["_source"];
                $list[$key]['id'] = $item['_id'];
            }
            $send['data'] = $list;
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
        $type = 'goods_' . $lang;
        $id = 0;
        if ($lang != 'zh') {
            $body = ['properties' => [
                    'lang' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'spu' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'sku' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'qrcode' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'model' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'show_name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'purchase_price1' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'purchase_price2' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'purchase_price_cur' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'purchase_unit' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'created_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'created_at' => [
                        'type' => 'date', "index" => "not_analyzed",
                    ],
                    'meterial_cat' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'show_cats' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'attrs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'specs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                ]
            ];
        } else {
            $body = ['properties' => [
                    'lang' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'spu' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'sku' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'qrcode' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'model' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'show_name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'purchase_price1' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'purchase_price2' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'purchase_price_cur' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'purchase_unit' => [
                        'type' => 'text', "index" => "not_analyzed",
                    ],
                    'created_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'created_at' => [
                        'type' => 'date',
                    ],
                    'meterial_cat' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'show_cats' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'attrs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'specs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                ]
            ];
        }
        return $body;
    }

    public function productAction($lang = 'en') {

        if (!in_array($lang, $this->langs)) {

            $lang = 'en';
        }

        $id = 0;
        if ($lang != 'zh') {
            $body = ['properties' => [
                    'lang' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'spu' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'skus' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'show_name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'keywords' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 1
                    ],
                    'supplier_id' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'brand' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 2
                    ],
                    'source' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 1
                    ],
                    'source_detail' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 1
                    ],
                    'recommend_flag' => [
                        'type' => 'text',
                        'analyzer' => 'whitespace'
                    ],
                    'status' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'created_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ], 'created_at' => [
                        'type' => 'date',
                        "index" => "not_analyzed",
                    ], 'updated_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ], 'updated_at' => [
                        'type' => 'date',
                        "index" => "not_analyzed",
                    ], 'checked_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ], 'checked_at' => [
                        'type' => 'date',
                        "index" => "not_analyzed",
                    ],
                    'meterial_cat' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'show_cats' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'attrs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'specs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                ]
                    ]
            ;
        } else {

            $body = ['properties' => [
                    'lang' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'spu' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'skus' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'show_name' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 8
                    ],
                    'keywords' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 1
                    ],
                    'supplier_id' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'brand' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 2
                    ],
                    'source' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 1
                    ],
                    'source_detail' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 1
                    ],
                    'recommend_flag' => [
                        'type' => 'text',
                        'analyzer' => 'whitespace'
                    ],
                    'status' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ],
                    'created_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ], 'created_at' => [
                        'type' => 'date',
                        "index" => "not_analyzed",
                    ], 'updated_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ], 'updated_at' => [
                        'type' => 'date',
                        "index" => "not_analyzed",
                    ], 'checked_by' => [
                        'type' => 'text',
                        "index" => "not_analyzed",
                    ], 'checked_at' => [
                        'type' => 'date',
                        "index" => "not_analyzed",
                    ],
                    'meterial_cat' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'show_cats' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'attrs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                    'specs' => [
                        'type' => 'text',
                        "analyzer" => "ik_max_word",
                        "search_analyzer" => "ik_max_word",
                        "include_in_all" => "true",
                        "boost" => 4
                    ],
                ]
            ];
        }

        return $body;
        // $this->es->create_index($this->index,  $body);
    }

}
