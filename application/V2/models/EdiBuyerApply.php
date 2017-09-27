<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:15
 */
class EdiBuyerApplyModel extends PublicModel{

    public function __construct() {
        parent::__construct();
    }

    /**
     *
     * 测试信息
     * SCH017124-171700 测试环境的保单号(信保提供--正式环境客户经理会提供给你们)
     * (当天的限额余额 第二天才能看到   每天凌晨之后会有同步计算余额的程序执行)
     *
     *THE BUYER: EVERUP SCAFFOLDS TRADING L.L.C
     * 买方:EVERUP SCAFFOLDS TRADING L.L.C
     * ADDRESS:INDIGO OPTIMA 01-OFFICE 710,P.O.BOX 128203,DUBAI,UAE.
     * 地址:INDIGO OPTIMA 01-OFFICE 710,P.O.BOX 128203,DUBAI,UAE.
     *
     * 买方: KIRAN UDYOG PVT.LTD.
     * 地址:Plot No-32,Sector-3,IMT,Manesar(Gurgaon),India-122050.
     */

    /**
     *
     *买家代码申请
     * @author klp
     */
    public function BuyerApplyAction($buyer_id){

        $buyerModel = new BuyerModel();          //企业银行信息
        $BuyerCodeApply = $buyerModel->buyerCerdit($buyer_id);
        $this->checkParamBuyer($BuyerCodeApply);
        $SinoSure = new Edi();
        $resBuyer = $SinoSure->EdiBuyerCodeApply($BuyerCodeApply);
        if($resBuyer['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        return $resBuyer;
       /* $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('申请成功!');
        $this->jsonReturn($resBuyer);*/
    }
    public function checkParamBuyer(&$BuyerCodeApply){
        $results = array();
        if(!isset($BuyerCodeApply['lang']) || empty($BuyerCodeApply['lang'])){
            $results['code'] = -101;
            $results['message'] = '[lang]不能为空!';
        }
        if($BuyerCodeApply['lang'] == 'zh') {
            if(!isset($BuyerCodeApply['area_no']) || !is_numeric($BuyerCodeApply['area_no'])){
                $results['code'] = -101;
                $results['message'] = '[area_no]不能为空或不为整型!';
            }
        }
        if(!isset($BuyerCodeApply['buyer_no']) || empty($BuyerCodeApply['buyer_no'])){
            $results['code'] = -101;
            $results['message'] = '[buyer_no]不能为空!';
        }
        if(!isset($BuyerCodeApply['country_code']) || empty($BuyerCodeApply['country_code'])){
            $results['code'] = -101;
            $results['message'] = '[country_code]不能为空!';
        }
        if(strlen($BuyerCodeApply['country_code']) > 3){
            $results['code'] = -101;
            $results['message'] = '[country_code]不能超过三位!';
        }
        if(!isset($BuyerCodeApply['name']) || empty($BuyerCodeApply['name'])){
            $results['code'] = -101;
            $results['message'] = '[name]不能为空!';
        }
        if(!isset($BuyerCodeApply['address']) || empty($BuyerCodeApply['address'])){
            $results['code'] = -101;
            $results['message'] = '[address]不能为空!';
        }
        if($results){
            jsonReturn($results);
        }
    }

    /**
     *
     *银行代码申请
     * @author klp
     */
    public function BankApplyAction($buyer_id){
        $buyerModel = new BuyerModel();          //企业银行信息
        $BuyerBankApply = $buyerModel->buyerCerdit($buyer_id);
        $this->checkParamBank($BuyerBankApply);
        $SinoSure = new Edi();
        $resBank = $SinoSure->EdiBankCodeApply($BuyerBankApply);

        if($resBank['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        return $resBank;
      /*  $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('申请成功!');
        $this->jsonReturn($resBank);*/
    }
    public function checkParamBank(&$BuyerBankApply){
        $results = array();
        if(!isset($BuyerBankApply['buyer_no'])){
            $results['code'] = -101;
            $results['message'] = '[buyer_no]采购商编号不能为空!';
        }
        if(!isset($BuyerBankApply['bank_country_code'])){
            $results['code'] = -101;
            $results['message'] = '[bank_country_code]银行国家代码不能为空!';
        }
        if(strlen($BuyerBankApply['bank_country_code']) > 3){
            $results['code'] = -101;
            $results['message'] = '[bank_country_code]不能超过三位!';
        }
        if(!isset($BuyerBankApply['bank_name'])){
            $results['code'] = -101;
            $results['message'] = '[bank_name]银行名称不能为空!';
        }
        if(!isset($BuyerBankApply['bank_address'])){
            $results['code'] = -101;
            $results['message'] = '[bank_address]银行地址不能为空!';
        }
        if(isset($BuyerBankApply['swift_code'])){
            $BuyerBankApply['bank_swift'] = $BuyerBankApply['swift_code'];
        }
        if($results){
            jsonReturn($results);
        }
    }
}