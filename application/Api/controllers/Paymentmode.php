<?php
/**
 * 支付方式
 * User: linkai
 * Date: 2017/6/30
 * Time: 21:31
 */
class PaymentmodeController extends Yaf_Controller_Abstract
{
    private $input;

    public function init()
    {
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 支付方式列表
     */
    public function listAction()
    {
        $lang = isset($this->input['lang'])? $this->input['lang']:'';
        $pModel = new PaymentmodeModel();
        $payment = $pModel->getPaymentmode($lang);
        jsonReturn(array('data' => $payment));
    }
}