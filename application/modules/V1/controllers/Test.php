<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**

 * Description of Test
 *
 * @author zhongyg
 */
class TestController extends PublicController {

  public function init() {
    //parent::init();
  }

  public function sendAction() {

    $data=send_Mail('297238770@qq.com', '注册认证邮件', '注册认证邮件', '87725826@qq.com');
    $this->jsonReturn($data);
  }

}
