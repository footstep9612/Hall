<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Supplier
 *
 * @author zhongyg
 */
class SupplierController extends PublicController {

  public function init() {
    parent::init();
  }
  //put your code here

  public function listAction() {
    $model = new SupplierModel();
    $data = $model->getlist($this->put_data, $this->getPut('lang', 'en'));
    $this->setCode(MSG::MSG_SUCCESS);
    $data['code'] = MSG::MSG_SUCCESS;
    $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, $this->getPut('lang', 'en'));
    $this->jsonReturn($data);
  }

}
