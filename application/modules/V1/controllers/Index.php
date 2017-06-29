<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MarketareaproductController
 *
 * @author zhongyg
 */
class IndexController extends ShopMallController {

  //put your code here
  public function init() {
    ini_set("display_errors", "On");
    error_reporting(E_ERROR | E_STRICT);
    $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
    $lang = $this->getPut('lang', 'en');
    $this->setLang($lang);
  }

  /**
   * 根据IP自动获取国家(新浪接口)
   * @author klp
   */
  public function getIp() {
    $IpModel = new MarketareaproductModel();
    $ip = get_client_ip();
    if ($ip != 'Unknown') {
      $country = getIpAddress($ip);
      return $IpModel->getbnbynameandlang($country, 'zh');
    } else {
      return 'China';
    }
  }

  public function getProductsAction() {

    $bn = $this->getIp();
    $condition['market_area_bn'] = $bn;

    if (isset($this->put_data['market_area_bn'])) {
      $condition['market_area_bn'] = $this->put_data['market_area_bn'];
    }
    $json = null;
    redisGet('MarketareaproductModel_' . $bn);
    if (!$json) {
      $model = new MarketareaproductModel();
      $data = $model->getlist($condition);
      redisSet('MarketareaproductModel_' . $bn, json_encode($data), 3600);
    } else {
      $data = json_decode($json, true);
    }

    $spus = [];
    if ($data) {
      foreach ($data as $item) {
        $spus[] = $item['spu'];
      }
    }
    if ($spus) {
      $condition['spus'] = $spus;
      $spumodel = new EsproductModel();
      //  $_source = ['meterial_cat_no', 'spu', 'show_name', 'profile', 'supplier_name', 'attachs', 'brand',];
      $ret = $spumodel->getproducts($condition, null, $this->getLang());
      $goods_model = new GoodsModel();

      if ($ret) {
        $send = [];

        $data = $ret[0];

        foreach ($data['hits']['hits'] as $key => $item) {
          $send[$key] = $item["_source"];
          $attachs = json_decode($item["_source"]['attachs'], true);
          if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
            $send[$key]['img'] = $attachs['BIG_IMAGE'][0];
          } else {
            $send[$key]['img'] = null;
          }
          $send[$key]['skunum'] = $goods_model->getCountBySpu($item["_source"]['spu'], $this->getLang());
          $send[$key]['id'] = $item['_id'];
        }
        $this->setCode(1);
        $this->jsonReturn($send);
      } else {
        $this->setCode(-1);
        $this->setMessage('空数据');
      }
    } else {
      $this->setCode(-1);
      $this->setMessage('空数据');
      // $send['data'] = $data;
      $this->jsonReturn();
    }
  }

  public function indexAction() {
    
  }

  public function createAction() {
    
  }

  public function deleteAction() {
    
  }

  public function infoAction() {
    
  }

}
