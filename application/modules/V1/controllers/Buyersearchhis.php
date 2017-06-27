<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Buyersearchhis
 *
 * @author zhongyg
 */
class BuyersearchhisController extends ShopMallController {

    //put your code here
    public function init() {
       parent::init();
    }

    public function listAction() {
        $model = new BuyersearchhisModel();
        $email = $this->user['email'];
        $condition['user_email'] = $email;
        $data = json_decode(redisGet('Buyersearchhis_' . $email), true);
        if ($data) {
            $this->setCode(1);
            $this->jsonReturn($data);
        } else {
            $data = $model->getlist($condition);
            if ($data) {

                redisSet('Buyersearchhis_' . $email, json_encode($data), 60);
                $this->setCode(1);
                $this->jsonReturn($data);
            } else {
              
                      $this->setCode(-1);
                $this->setMessage('空数据!');
                $this->jsonReturn();
            }
        }
    }

}
