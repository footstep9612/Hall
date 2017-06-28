<?php
/**
 * 物流时效
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:02
 */

class LogiPeriodController extends ShopMallController{
    public function listAction(){
        $this->put_data['to_country'] =  $this->put_data['to_country']? $this->put_data['to_country']:'巴西';
        $this->setLang('zh');

        $logiModel = new LogiPeriodModel();
        $logis = $logiModel->getList($this->lang, $this->put_data['to_country']);
        var_dump($logis);
    }
}