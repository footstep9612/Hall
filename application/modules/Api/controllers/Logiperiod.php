<?php
/**
 * 物流时效
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:02
 */

class LogiperiodController extends Yaf_Controller_Abstract{
    private $input;
    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 物流时效借口
     */
    public function listAction(){
        if(!isset($this->input['to_country'])){
            jsonReturn('','1000');
        }
        $this->input['lang'] = isset($this->input['lang'])?$this->input['lang']:(browser_lang()?browser_lang():'en');

        $logiModel = new LogiPeriodModel();
        $logis = $logiModel->getList($this->input['lang'], $this->input['to_country']);
        if($logis){
            jsonReturn(array('data'=>$logis));
        }else{
            jsonReturn('','400','失败');
        }
    }

    /**
     * 根据贸易术语查询物流时效国
     */
 /*   public function countryAction(){
        if(!isset($this->input['trade_terms'])){
            jsonReturn('','1000');
        }

        $this->input['lang'] = isset($this->input['lang'])?$this->input['lang']:'en';
        $field = 'from_country';
        $where = array(
            'lang' =>$this->input['lang'],
            'trade_terms' =>$this->input['trade_terms'],
            'status' =>'VALID'
        );
        $logiModel = new LogiPeriodModel();
        $logis = $logiModel->getInfo($field,$where);
        if($logis){
            jsonReturn(array('data'=>$logis));
        }else{
            jsonReturn('','400','失败');
        }
    }*/


}