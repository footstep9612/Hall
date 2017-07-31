<?php
/**
 * 港口
 * User: linkai
 * Date: 2017/6/30
 * Time: 19:41
 */
class PortController extends Yaf_Controller_Abstract{
    private $input;
    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 港口
     */
    public function listAction(){
        $lang = isset($this->input['lang'])?$this->input['lang']:'en';
        //国家简称(bn)
        $country = isset($this->input['country'])?$this->input['country']:'';
        $portModel = new PortModel();
        $port = $portModel->getPort($lang,$country);
        jsonReturn(array('data'=>$port));
    }
}