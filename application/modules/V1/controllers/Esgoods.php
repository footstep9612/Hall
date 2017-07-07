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
    // parent::init();
  }

  public function listAction() {

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
        $attachs = json_decode($item["_source"]['attachs'], true);
        if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
          $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
        } else {
          $list[$key]['img'] = null;
        }
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
