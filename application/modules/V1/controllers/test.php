<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LoginController
 *
 * @author  jhw
 */
class TestController extends Yaf_Controller_Abstract {

    function testAction(){
        $this->getView()->display('test/test.html');
        exit;
    }

}
