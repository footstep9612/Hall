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
     * 根据贸易术语查询物流时效国　　－　预留接口
     */
    public function countryAction(){
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
    }

    /**
     * 根据贸易术语与国家获取港口城市　　－　预留接口
     */
    public function portAction(){
        if(!isset($this->input['trade_terms']) || !isset($this->input['from_country'])){
            jsonReturn('','1000');
        }
        $this->input['lang'] = isset($this->input['lang'])?$this->input['lang']:'en';
        $field = 'from_port';
        $where = array(
            'lang' =>$this->input['lang'],
            'trade_terms' =>$this->input['trade_terms'],
            'from_country'=>$this->input['from_country'],
            'status' =>'VALID'
        );
        $logiModel = new LogiPeriodModel();
        $logis = $logiModel->getInfo($field,$where);
        if($logis){
            jsonReturn(array('data'=>$logis));
        }else{
            jsonReturn('','400','失败');
        }

    }


    /**
     * 根据贸易术语与国家 港口城市 目地国获取目的港口信息　　－　预留接口
     */
    public function toportAction(){
        if(!isset($this->input['trade_terms']) || !isset($this->input['from_country'])){
            jsonReturn('','1000');
        }
        $this->input['lang'] = isset($this->input['lang'])?$this->input['lang']:'en';
        $field = 'clearance_loc,to_port,packing_period_min,packing_period_max,collecting_period_min,collecting_period_max,declare_period_min';
        $where = array(
            'lang' =>$this->input['lang'],
            'trade_terms' =>$this->input['trade_terms'],
            'from_country'=>$this->input['from_country'],
            'from_port' =>$this->input['from_port'],
            'to_country'=>$this->input['from_port'],
            'status' =>'VALID'
        );
        $logiModel = new LogiPeriodModel();
        $logis = $logiModel->getInfo($field,$where);
        if($logis){
            jsonReturn(array('data'=>$logis));
        }else{
            jsonReturn('','400','失败');
        }

    }


}