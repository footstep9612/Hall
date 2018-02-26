<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/2/26
 * Time: 10:43
 */

class BuyercreditController extends PublicController {

    public function init(){
        //$this->token = false;
        parent::init();
    }
    /**
     * 银行企业代码申请
     */
    public function editCreditAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';

        if (empty($data['name'])) {
            //jsonReturn(null, -110, ShopMsg::getMessage('-110', $lang));
            jsonReturn(null, -110, '采购商企业英文名称');
        }
        if (empty($data['registered_in'])) {
            jsonReturn(null, -110, '采购商英文地址');
        }
        if (empty($data['tel'])) {
            jsonReturn(null, -110, '企业电话');
        }
        /*if (empty($data['country_bn'])) {
            jsonReturn(null, -110, '企业所在国家简称');
        }*/
        if (empty($data['country_code'])) {
            jsonReturn(null, -110, '企业所在国家简称代码');
        }
        if (empty($data['bank_country_code'])) {
            jsonReturn(null, -110, '银行所在国家简称代码');
        }
        if (empty($data['bank_name'])) {
            jsonReturn(null, -110, '开户银行英文名称');
        }
        if (empty($data['bank_address'])) {
            jsonReturn(null, -110, '银行地址');
        }
        if (empty($data['tel_bank'])) {
            jsonReturn(null, -110, '银行电话');
        }
        $buyerModel = new BuyerModel();
        $company_model = new BuyerRegInfoModel();

        $data['buyer_id'] = $this->user['buyer_id'];
        $data['buyer_no'] = $buyerModel->field('buyer_no')->where(['id' => $this->user['buyer_id'], 'deleted_flag' => 'N'])->find();
        $check = $company_model->field('id')->where(['buyer_no' => $data['buyer_no'], 'deleted_flag' => 'N'])->find();
        if($check){
            $res = $company_model->update_data($data);
        } else {
            $res = $company_model->create_data($data);
        }
        if($res) {
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
        }
    }

    /**
     * 获取企业申请信息
     */
    public function getCompanyInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $buyerModel = new BuyerModel();
        $buyer_no = $buyerModel->field('buyer_no')->where(['id' => $this->user['buyer_id'], 'deleted_flag' => 'N'])->find();
        $company_model = new BuyerRegInfoModel();
        $comInfo = $company_model->getInfo($buyer_no);
        if($comInfo) {
            jsonReturn($comInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'data is empty!');
        }
    }

    /**
     * 获取银行申请信息
     */
    public function getBankInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $buyerModel = new BuyerModel();
        $buyer_no = $buyerModel->field('buyer_no')->where(['id' => $this->user['buyer_id'], 'deleted_flag' => 'N'])->find();
        $bank_model = new BuyerBankInfoModel();
        $bankInfo = $bank_model->getInfo($buyer_no);
        if($bankInfo) {
            jsonReturn($bankInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'data is empty!');
        }
    }

}