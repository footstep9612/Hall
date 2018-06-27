<?php
/**
 * 货币
 * User: linkai
 * Date: 2017/6/30
 * Time: 21:15
 */
class CurrencyController extends Yaf_Controller_Abstract{
    private $input;
    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 货币
     */
    public function listAction(){
        $curModel = new CurrencyModel();
        $currency = $curModel->getCurrency();
        jsonReturn(array('data'=>$currency));
    }
}