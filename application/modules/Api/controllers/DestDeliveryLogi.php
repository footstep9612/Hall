<?php
/**
 * 落地配
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:39
 */
class DestDeliveryLogiController extends Yaf_Controller_Abstract{
    private $input;
    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 落地配
     */
    public function listAction(){
        echo 123;die;
        $this->input['country'] = '巴西';
        $this->input['lang'] = 'zh';
        if(!isset($this->input['country'])){
            jsonReturn('','1000');
        }

        $this->input['lang'] = isset($this->input['lang'])? $this->input['lang']:(browser_lang()?browser_lang():'en');
        $ddlModel = new DestDeliveryLogiModel();
        $data = $ddlModel->getList( $this->input['country'],$this->input['lang']);
        if($data){
            jsonReturn(array('data'=>$data));
        }else{
            jsonReturn('','400','失败');
        }
    }
}