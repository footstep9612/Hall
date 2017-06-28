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
        $this->setLang('en');
        //parent::init();
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
            $ret = $spumodel->getproducts($condition, null, $this->getLang());


            if ($ret) {
                $send = [];

                $data = $ret[0];
                $send[0] = $data['hits']['hits'][0]["_source"];
                $send[0]['id'] = $data['hits']['hits'][0]['_id'];
//                foreach ($data['hits']['hits'] as $key => $item) {
//                    $send[$key] = $item["_source"];
//                    $send[$key]['id'] = $item['_id'];
//                }
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
