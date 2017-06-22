<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author zyg
 */
class TestController extends Yaf_Controller_Abstract {

    public function __init() {
        //   parent::__init();
    }

    public function testAction() {
       $data = $this->getRequest()->getQuery();
       if(!empty($data)){
            for($i=0;$i<count($data['busyer_unit_price']);$i++){
                $arr[$i]['busyer_unit_price']=$data['busyer_unit_price'][$i];
                $arr[$i]['num']=$data['num'][$i];
            }
            $data_exw=exw($arr,$data['gross_profit_rate']);
           $data_exw['trade_terms']=$data['trade_terms'];
           $logistics=logistics($data_exw);
           var_dump($logistics);
            $this->getView()->assign("data_exw", $data_exw);
            $this->getView()->assign("data", $data);
       }
        $this->getView()->display('test/test.html');
    }



}
