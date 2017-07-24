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
  protected $version = '1';

  //put your code here
  public function init() {

    $this->es = new ESClient();
    if ($this->getRequest()->isCli()) {
      ini_set("display_errors", "On");
      error_reporting(E_ERROR | E_STRICT);
    } else {
      //   parent::init();
    }
  }

  /*
   * product数据导入
   */

  public function importAction($lang = 'en') {
    try {
      set_time_limit(0);
      ini_set('memory_limi', '1G');
      foreach ($this->langs as $lang) {
        $espoductmodel = new EsproductModel();
        $espoductmodel->importproducts($lang);
      }
      $this->setCode(1);
      $this->setMessage('成功!');
      $this->jsonReturn();
    } catch (Exception $ex) {
      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
      LOG::write($ex->getMessage(), LOG::ERR);
      $this->setCode(-2001);
      $this->setMessage('系统错误!');
      $this->jsonReturn();
    }
  }

  public function indexAction() {
    // $this->es->delete($this->index);
    //$model = new EsgoodsModel();

    $body['mappings'] = [];

    $product_properties = $this->productAction('en');
    $goods_properties = $this->goodsAction('en');
    foreach ($this->langs as $lang) {
      $body['mappings']['goods_' . $lang]['properties'] = $goods_properties;
      $body['mappings']['goods_' . $lang]['_all'] = ['enabled' => false];
      $body['mappings']['product_' . $lang]['properties'] = $product_properties;
      $body['mappings']['product_' . $lang]['_all'] = ['enabled' => false];
    }
    $this->es->create_index($this->index, $body);
    $this->setCode(1);
    $this->setMessage('成功!');
    $this->jsonReturn();
  }

  public function listAction() {
    $model = new EsproductModel();
    $ret = $model->getproducts($this->put_data, null, $this->getLang());
    if ($ret) {
      $data = $ret[0];
      $list = $this->getdata($data);
      $send['count'] = intval($data['hits']['total']);
      $send['current_no'] = intval($ret[1]);
      $send['pagesize'] = intval($ret[2]);
      if (isset($ret[3]) && $ret[3] > 0) {
        $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
      } else {
        $send['allcount'] = $send['count'];
      }
      if (isset($this->put_data['sku_count']) && $this->put_data['sku_count'] == 'Y') {
        $es_goods_model = new EsgoodsModel();
        $send['sku_count'] = $es_goods_model->getgoodscount($this->put_data);
      }
      $send['data'] = $list;
      $this->update_keywords();
      $this->setCode(MSG::MSG_SUCCESS);
      $send['code'] = $this->getCode();
      $send['message'] = $this->getMessage();
      $this->jsonReturn($send);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  private function getdata($data) {

    foreach ($data['hits']['hits'] as $key => $item) {
      $list[$key] = $item["_source"];
      $attachs = json_decode($item["_source"]['attachs'], true);
      if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
        $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
      } else {
        $list[$key]['img'] = null;
      }
      $list[$key]['id'] = $item['_id'];
      $show_cats = json_decode($item["_source"]["show_cats"], true);
      if ($show_cats) {
        rsort($show_cats);
      }
      $list[$key]['show_cats'] = $show_cats;
      $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
      $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
      $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
      $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
      $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
      $list[$key]['skus'] = json_decode($list[$key]['skus'], true);
      $list[$key]['sku_num'] = count($list[$key]['skus']);
    }
    return $list;
  }

  private function getcatlist($material_cat_nos, $material_cats) {
    ksort($material_cat_nos);
    $catno_key = 'ShowCats_' . md5(http_build_query($material_cat_nos) . '&lang=' . $this->getLang() . md5(json_encode($this->put_data)));
    $catlist = json_decode(redisGet($catno_key), true);
    if (!$catlist) {
      $matshowcatmodel = new ShowmaterialcatModel();
      $showcats = $matshowcatmodel->getshowcatsBymaterialcatno($material_cat_nos, $this->getLang());
      $new_showcats3 = [];
      foreach ($showcats as $showcat) {
        $material_cat_no = $showcat['material_cat_no'];
        unset($showcat['material_cat_no']);
        $new_showcats3[$showcat['cat_no']] = $showcat;
        if (isset($material_cats[$material_cat_no])) {
          $new_showcats3[$showcat['cat_no']]['count'] = $material_cats[$material_cat_no];
        }
      }
      rsort($new_showcats3);
      foreach ($new_showcats3 as $key => $item) {
        $model = new EsproductModel();
        $this->put_data['show_cat_no'] = $item['cat_no'];
        $item['count'] = $model->getcount($this->put_data, $this->getLang());
        $new_showcats3[$key] = $item;
      }
      redisSet($catno_key, json_encode($new_showcats3), 86400);
      return $new_showcats3;
    }
    return $catlist;
  }

  private function update_keywords() {
    if ($this->put_data['keyword']) {
      $search = [];
      $search['keywords'] = $this->put_data['keyword'];
      if ($this->user['email']) {
        $search['user_email'] = $this->user['email'];
      } else {
        $search['user_email'] = '';
      }
      $search['search_time'] = date('Y-m-d H:i:s');
      $usersearchmodel = new UsersearchhisModel();
      $condition = ['user_email' => $search['user_email'], 'keywords' => $search['keywords']];
      $row = $usersearchmodel->exist($condition);
      if ($row) {
        $search['search_count'] = intval($row['search_count']) + 1;
        $search['id'] = $row['id'];
        $usersearchmodel->update_data($search);
      } else {
        $search['search_count'] = 1;
        $usersearchmodel->add($search);
      }
    }
  }

  public function getcatsAction() {
    $model = new EsproductModel();
    $ret = $model->getshow_catlist($this->put_data, $this->getLang());
    if ($ret) {
      $list = [];

      $data = $ret[0];
      $send['count'] = intval($data['hits']['total']);
      $send['current_no'] = intval($ret[1]);
      $send['pagesize'] = intval($ret[2]);
      if (isset($ret[3]) && $ret[3] > 0) {

        $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
      } else {
        $send['allcount'] = $send['count'];
      }
      foreach ($data['hits']['hits'] as $key => $item) {
        $list[$key] = $item["_source"];
        $list[$key]['id'] = $item['_id'];
      }
      $send['list'] = $list;
      $this->setCode(MSG::MSG_SUCCESS);
      if ($this->put_data['keyword']) {
        $search = [];
        $search['keyword'] = $this->put_data['keyword'];
        $search['user_email'] = $this->user['email'];
        $search['search_time'] = date('Y-m-d H:i:s');
        $usersearchmodel = new UsersearchhisModel();
        if ($row = $usersearchmodel->exist($condition)) {
          $search['search_count'] = intval($row['search_count']) + 1;
          $usersearchmodel->update_data($search);
        }
      }
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

    $fields = [
        'no' => [
            'index' => 'no',
            'type' => 'string'
        ],
        'all' => [
            'index' => 'not_analyzed',
            'type' => 'string'
        ],
        'standard' => [
            'analyzer' => 'standard',
            'type' => 'string'
        ],
        'whitespace' => [
            'analyzer' => 'whitespace',
            'type' => 'string'
        ]
    ];
    $ik_fields = [
        'type' => $type_string,
        "analyzer" => $analyzer,
        "search_analyzer" => $analyzer,
        "include_in_all" => "true",
        "boost" => 8,
        'fields' => $fields
    ];
    $not_analyzed = [
        'type' => $type_string,
        "index" => "not_analyzed",
        'fields' => [
            'no' => [
                'index' => 'no',
                'type' => 'string'
            ]]
    ];
    $date_analyzed = [
        'type' => 'date',
        "index" => "not_analyzed",
        "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd",
        'fields' => [
            'no' => [
                'index' => 'no',
                'type' => 'string'
            ],
            'all' => [
                'index' => 'not_analyzed',
                'type' => 'string'
            ]
        ]
    ];

    $body = [
        'id' => [
            'type' => 'integer',
            "index" => "not_analyzed",
            'fields' => [
                'no' => [
                    'index' => 'no',
                    'type' => 'string'
                ]
            ]
        ],
        'lang' => $not_analyzed,
        'package_quantity' => $not_analyzed,
        'exw_day' => $not_analyzed,
        'spu' => $not_analyzed,
        'sku' => $not_analyzed,
        'attachs' => $ik_fields,
        'qrcode' => $not_analyzed,
        'model' => $not_analyzed,
        'name' => $ik_fields,
        'show_name' => $ik_fields,
        'purchase_price1' => $not_analyzed,
        'purchase_price2' => $not_analyzed,
        'purchase_price_cur' => $not_analyzed,
        'purchase_unit' => $not_analyzed,
        'pricing_flag' => $not_analyzed,
        'created_by' => $not_analyzed,
        'created_at' => $date_analyzed,
        'updated_by' => $not_analyzed,
        'updated_at' => $date_analyzed,
        'checked_by' => $not_analyzed,
        'checked_at' => $date_analyzed,
        'meterial_cat' => $ik_fields,
        'show_cats' => $ik_fields,
        'attrs' => $ik_fields,
        'specs' => $ik_fields,
        'suppliers' => $ik_fields,
        'status' => $not_analyzed,
        'shelves_status' => $not_analyzed,];

    return $body;
  }

  public function productAction($lang = 'en') {

    $type_string = 'text';
    $analyzer = 'ik_max_word';
    if ($this->version != 5) {
      $type_string = 'string';
      $analyzer = 'ik';
    }

    $fields = [
        'no' => [
            'index' => 'no',
            'type' => 'string'
        ],
        'all' => [
            'index' => 'not_analyzed',
            'type' => 'string'
        ],
        'standard' => [
            'analyzer' => 'standard',
            'type' => 'string'
        ],
        'whitespace' => [
            'analyzer' => 'whitespace',
            'type' => 'string'
        ]
    ];
    $ik_fields = [
        'type' => $type_string,
        "analyzer" => $analyzer,
        "search_analyzer" => $analyzer,
        "include_in_all" => "true",
        "boost" => 8,
        'fields' => $fields
    ];
    $not_analyzed = [
        'type' => $type_string,
        "index" => "not_analyzed",
        'fields' => [
            'no' => [
                'index' => 'no',
                'type' => 'string'
            ]]
    ];
    $date_analyzed = [
        'type' => 'date',
        "index" => "not_analyzed",
        "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd",
        'fields' => [
            'no' => [
                'index' => 'no',
                'type' => 'string'
            ],
            'all' => [
                'index' => 'not_analyzed',
                'type' => 'string'
            ]
        ]
    ];

    $body = [
        'id' => [
            'type' => 'integer',
            "index" => "not_analyzed",
            'fields' => [
                'no' => [
                    'index' => 'no',
                    'type' => 'string'
                ]]
        ],
        'lang' => $not_analyzed,
        'meterial_cat_no' => $not_analyzed,
        'spu' => $not_analyzed,
        'attachs' => $ik_fields,
        'skus' => $ik_fields,
        'qrcode' => $not_analyzed,
        'name' => $ik_fields,
        'show_name' => $ik_fields,
        'keywords' => $ik_fields,
        'exe_standard' => $ik_fields,
        'app_scope' => $ik_fields,
        'tech_paras' => $ik_fields,
        'advantages' => $ik_fields,
        'profile' => $ik_fields,
        'supplier_id' => $not_analyzed,
        'supplier_name' => $ik_fields,
        'brand' => $ik_fields,
        'source' => $ik_fields,
        'source_detail' => $ik_fields,
        'recommend_flag' => $not_analyzed,
        'status' => $not_analyzed,
        'shelves_status' => $not_analyzed,
        'created_by' => $not_analyzed,
        'created_at' => $date_analyzed,
        'updated_by' => $not_analyzed,
        'updated_at' => $date_analyzed,
        'checked_by' => $not_analyzed,
        'checked_at' => $date_analyzed,
        'meterial_cat' => $ik_fields,
        'supply_capabilitys' => $ik_fields,
        'show_cats' => $ik_fields,
        'attrs' => $ik_fields,
        'suppliers' => $ik_fields,
        'specs' => $ik_fields,];
    return $body;
  }

}
