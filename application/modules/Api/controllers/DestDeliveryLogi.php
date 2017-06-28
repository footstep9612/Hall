<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:39
 */
class DestDeliveryLogiController extends ShopMallController{
    public function listAction(){
        $this->put_data['country'] = '';
        $this->setLang('zh');
        $ddlModel = new DestDeliveryLogiModel();
        $data = $ddlModel->getList($this->put_data['country'],$this->lang);
        //if($data){
            jsonReturn($data);
       //}
    }
}