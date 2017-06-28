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
class MarketareaproductController extends ShopMallController {

    //put your code here
    public function init() {
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

    public function listAction() {

        $bn = $this->getIp();
        $condition['market_area_bn'] = $bn;
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
            $spumodel = new EsproductModel();
            $data = $spumodel->getproducts($condition, null, $this->getLang());
            $this->setCode(1);
            $send['data'] = $data;
            $this->jsonReturn($send);
        } else {
            $this->setCode(-1);
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
