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
    parent::init();
  }

  public function listAction() {
    $lang = $this->getPut('lang', 'en');
    $model = new EsgoodsModel();
    $_source = ['sku', 'spu', 'name', 'show_name', 'model'
        , 'purchase_price1', 'purchase_price2', 'attachs', 'package_quantity', 'exw_day',
        'purchase_price_cur', 'purchase_unit', 'pricing_flag', 'show_cats',
        'meterial_cat', 'brand', 'supplier_name', 'warranty', 'status', 'create_at',
        'create_by'];
    $ret = $model->getgoods($this->put_data, $_source, $lang);
    if ($ret) {
      $list = [];
      $data = $ret[0];
      $send['count'] = intval($data['hits']['total']);
      $send['current_no'] = intval($ret[1]);
      $send['pagesize'] = intval($ret[2]);
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
          $search['id'] = $row['id'];
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

}
