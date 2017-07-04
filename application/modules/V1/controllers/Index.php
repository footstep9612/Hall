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
    $this->put_data = $jsondata = $data = json_decode(file_get_contents("php://input"), true);
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
    $iplocation = new IpLocation();
    if ($ip != 'Unknown' && $ip != '127.0.1') {
      $country = $iplocation->getlocation($ip);

      return $IpModel->getbnbynameandlang($country['country'], 'zh');
    } else {

      return 'Asia';
      //   $this->jsonReturn($send);
    }
  }

  /**
   * 根据IP自动获取国家(新浪接口)
   * @author klp
   */
  public function getCounryAction() {
    $IpModel = new MarketareaproductModel();

    $ip = get_client_ip();
    $iplocation = new IpLocation();

    if ($ip != 'Unknown') {
      $country = $iplocation->getlocation($ip);


      $send = $IpModel->getCountrybynameandlang($country['country'], $this->getLang());
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
    if ($market_area_bn) {
      return $market_area_bn;
    } else {
      return 'Asia';
    }
  }

  /*
   * 按区域获取首页推荐产品
   */

  public function getProductsAction() {
    if (isset($this->put_data['country'])) {
      $bn = $condition['market_area_bn'] = $this->getMarketAreaBnByCountry();
    } else {
      $bn = $this->getIp();
      if ($bn) {
        $condition['market_area_bn'] = $bn;
      } else {
        $bn = 'Asia-Paific Region';
        $condition['market_area_bn'] = $bn;
      }
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

      $send = $this->getproducts($condition, $spus);

      if ($send) {
        $this->setCode(1);
        $this->jsonReturn($send);
      } else {
        $this->setCode(-1);
        $this->setMessage('空数据');
        $this->jsonReturn();
      }
    } else {
      $send = $this->getproducts($condition);
      if ($send) {
        $this->setCode(1);
        $this->jsonReturn($send);
      } else {
        $this->setCode(-1);
        $this->setMessage('空数据');
        $this->jsonReturn();
      }
    }
  }

  private function getproducts($condition, $spus = []) {

    $spumodel = new EsproductModel();
    $condition['pagesize'] = 7;
    if (!$spus) {
      $condition['source'] = 'ERUI';
    }
    $ret = $spumodel->getproducts($condition, null, $this->getLang());
    var_dump($ret);
    if ($ret) {
      $send = [];
      $data = $ret[0];
      if (!$spus) {
        foreach ($data['hits']['hits'] as $key => $item) {
          $spus[] = $item["_source"]['spu'];
        }
      }
      $goods_model = new GoodsModel();
      $goodscounts = $goods_model->getCountBySpus($spus, $this->getLang());

      $spugoodscount = [];
      foreach ($goodscounts as $count) {
        $spugoodscount[$count['spu']] = $count['skunum'];
      }
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
      return $send;
    } else {
      return [];
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
