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
      return 'Asia';
    }
  }

  /**
   * 根据IP自动获取国家(新浪接口)
   * @author klp
   */
  public function getCounryAction() {
    $IpModel = new MarketareaproductModel();
    $ip = get_client_ip();
    if ($ip != 'Unknown') {
      $country = getIpAddress($ip);
      $send = $IpModel->getbnbynameandlang($country, $this->getLang());
    } else {
      $send = 'China';
    }
    $this->setCode(1);
    $this->jsonReturn($send);
  }

  private function getMarketAreaBnByCountry() {
    $country = $this->put_data['country'];
    $lang = $this->getLang();
    $IpModel = new MarketareaproductModel();
    $market_area_bn = $IpModel->getbnbynameandlang($country, $lang);
    return $market_area_bn;
  }

  public function getProductsAction() {

      
    if (isset($this->put_data['country'])) {
      $bn = $condition['country'] = $this->getMarketAreaBnByCountry();
    } else {
      $bn = $this->getIp();
      $condition['market_area_bn'] = $bn;
    }
    $json = redisGet('MarketareaproductModel_' . $bn);

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
      $goods_model = new GoodsModel();
      $goodscounts = $goods_model->getCountBySpus($spus, $this->getLang());
      $spugoodscount = [];
      foreach ($goodscounts as $count) {
        $spugoodscount[$count['spu']] = $count['skunum'];
      }
      $spumodel = new EsproductModel();
      $ret = $spumodel->getproducts($condition, null, $this->getLang());
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
          if (isset($spugoodscount[$item["_source"]['spu']])) {
            $send[$key]['skunum'] = $spugoodscount[$item["_source"]['spu']];
          } else {
            $send[$key]['skunum'] = 0;
          }
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
