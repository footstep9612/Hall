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
      parent::init();
    }
  }

  public function listAction() {
    $lang = $this->getPut('lang', 'en');
    $model = new EsgoodsModel();
    $ret = $model->getgoods($this->put_data, null, $lang);
    if ($ret) {
      $list = [];
      $data = $ret[0];
      $send['count'] = intval($data['hits']['total']);
      $send['current_no'] = intval($ret[1]);
      $send['pagesize'] = intval($ret[2]);
      $skus = [];
      if ($lang != 'en') {
        foreach ($data['hits']['hits'] as $key => $item) {
          $skus[] = $item["_source"]['sku'];
        }
        $ret_en = $model->getgoods(['skus' => $skus], ['sku', 'name'], 'en');
        $list_en = [];
        foreach ($ret_en[0]['hits']['hits'] as $item) {
          $list_en[$item["_source"]['sku']] = $item["_source"]['name'];
        }
      } elseif ($lang == 'en') {
        foreach ($data['hits']['hits'] as $key => $item) {
          $skus[] = $item["_source"]['sku'];
        }
        $ret_zh = $model->getgoods(['skus' => $skus], ['sku', 'name'], 'zh');
        $list_zh = [];
        foreach ($ret_zh[0]['hits']['hits'] as $item) {
          $list_zh[$item["_source"]['sku']] = $item["_source"]['name'];
        }
      }
      foreach ($data['hits']['hits'] as $key => $item) {
        $list[$key] = $item["_source"];
        $attachs = json_decode($item["_source"]['attachs'], true);
        if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
          $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
        } else {
          $product_attach_model = new ProductAttachModel();
          $list[$key]['img'] = $product_attach_model->getimgBySpu($item["_source"]['spu']);
        }
        $show_cats = json_decode($item["_source"]["show_cats"], true);
        if ($show_cats) {
          rsort($show_cats);
        }
        $sku = $item["_source"]['sku'];

        if (isset($list_en[$sku])) {
          $list[$key]['name'] = $list_en[$sku];
          $list[$key]['name_' . $lang] = $item["_source"]['name'];
        } elseif (isset($list_zh[$sku])) {
          $list[$key]['name_zh'] = $item["_source"]['name'];
        } else {
          $list[$key]['name'] = $item["_source"]['name'];
          $list[$key]['name_' . $lang] = $item["_source"]['name'];
        }

        $list[$key]['show_cats'] = $show_cats;
        $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
        $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
        $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
        $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
        $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
      }
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
          $usersearchmodel->update_data($search);
        } else {
          $search['search_count'] = 1;
          $usersearchmodel->add($search);
        }
      }
      $send['data'] = $list;
      $this->setCode(MSG::MSG_SUCCESS);
      $send['code'] = $this->getCode();
      $send['message'] = $this->getMessage();
      $this->jsonReturn($send);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  /*
   * goods 数据导入
   */

  public function importAction($lang = 'en') {
    try {
      //$lang = 'zh';
      set_time_limit(0);
      ini_set('memory_limi', '1G');
      foreach ($this->langs as $lang) {
        $espoductmodel = new EsgoodsModel();
        $espoductmodel->importgoodss($lang);
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
            'meterial_cat_no' => [
                'type' => $type_string,
                "index" => "not_analyzed",
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
