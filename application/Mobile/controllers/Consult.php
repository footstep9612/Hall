<?php
/**
 * 咨询
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/18
 * Time: 15:23
 */
class ConsultController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
        $this->input = $this->getPut();
    }

    /**
     * 咨询添加
     */
    public function createAction(){
        $data = $this->getPut();
        $ConsultsModel = new ConsultsModel();
        $result = $ConsultsModel->addInfo($data);
        if($result && $result!== false){
            jsonReturn($result);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }
}