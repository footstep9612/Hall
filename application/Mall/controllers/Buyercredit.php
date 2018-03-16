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
            jsonReturn(null, -110, 'Company name cannot be empty!');
        }
        if (empty($data['registered_in'])) {
            jsonReturn(null, -110, 'Company address cannot be empty!');
        }
        if (empty($data['tel'])) {
            jsonReturn(null, -110, 'Phone number cannot be empty!');  //企业
        }
        /*if (empty($data['country_code'])) {
            jsonReturn(null, -110, '企业所在国家简称代码');
        }*/
        if (empty($data['bank_country_code'])) {
            jsonReturn(null, -110, 'Country cannot be empty!');
        }
        if (empty($data['bank_name'])) {
            jsonReturn(null, -110, 'Bank name cannot be empty!');
        }
        if (empty($data['bank_address'])) {
            jsonReturn(null, -110, 'Bank address cannot be empty!');
        }
        if (empty($data['tel_bank'])) {
            jsonReturn(null, -110, 'Phone number cannot be empty!'); //银行
        }
        $data['buyer_id'] = $this->user['buyer_id'];

        $company_model = new BuyerRegInfoModel();
        $credit_model = new BuyerCreditModel();
        $buyer_info = $this->_getBuyerNo($data['buyer_id']);
        if(!empty($buyer_info['country_bn'])) {
            $country_code = $this->_getCountryrCode($buyer_info['country_bn']);
            if($country_code['code']) {
                $data['country_code'] = $country_code['code'];
            } else {
                jsonReturn(null, -110, 'This country is out of credit service!');
            }
        }
        $data['buyer_no'] = $buyer_info['buyer_no'];
        $check = $company_model->field('id')->where(['buyer_no' => $buyer_info['buyer_no'], 'deleted_flag' => 'N'])->find();
        $status = $credit_model->field('status')->where(['buyer_no' => $buyer_info['buyer_no'], 'deleted_flag' => 'N'])->find();
        if($check){
            if($status['status']=='ERUI_REJECTED' || $status['status']=='EDI_REJECTED' || $status['status']=='INVALID') {
                $res = $company_model->update_data($data);
            }
            jsonReturn(null, ShopMsg::CUSTOM_FAILED, ' Please wait for the administrator to review!');
        } else {
            $res = $company_model->create_data($data);
        }
        if($res !== false) {
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, 'Submit successfully!');
        } else {
            jsonReturn(null, ShopMsg::CUSTOM_FAILED, 'Submit unsuccessfully!');
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
        $comInfo = $company_model->getInfo($buyer_no['buyer_no']);
        if($comInfo) {
            $comInfo['biz_nature'] = empty($comInfo['biz_nature'])?[]:json_decode($comInfo['biz_nature'],true);
            $comInfo['biz_scope'] = empty($comInfo['biz_scope'])?[]:json_decode($comInfo['biz_scope'],true);
            $comInfo['stock_exchange'] = empty($comInfo['stock_exchange'])?[]:json_decode($comInfo['stock_exchange'],true);
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
        $bankInfo = $bank_model->getInfo($buyer_no['buyer_no']);
        if($bankInfo) {
            jsonReturn($bankInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'data is empty!');
        }
    }

    /**
     * 获取申请日志信息  银行或企业信息--银行sign:2;企业sign:1
     */
    public function getListLogAction() {
        $data = $this->getPut();
        $model = new BuyerCreditLogModel();
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            $buyerModel = new BuyerModel();
            $buyer_no = $buyerModel->field('buyer_no')->where(['id' => $this->user['buyer_id'], 'deleted_flag' => 'N'])->find();
            $data['buyer_no'] = $buyer_no['buyer_no'];
        }
        $res = $model->getlist($data);
        $count = $model->getCount($data);
        if (!empty($res)) {
            $this->_setAgentName($res);
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
            $datajson['count'] = $count;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = ShopMsg::CUSTOM_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = 'Data is empty!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 获取申限额申请明细
     */
    public function getQuotaListAction() {
        $data = $this->getPut();
        $model = new BuyerQuotaLogModel();
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            $buyerModel = new BuyerModel();
            $buyer_no = $buyerModel->field('buyer_no')->where(['id' => $this->user['buyer_id'], 'deleted_flag' => 'N'])->find();
            $data['buyer_no'] = $buyer_no['buyer_no'];
        }
        $res = $model->getlist($data);
        $count = $model->getCount($data);
        if (!empty($res)) {
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
            $datajson['count'] = $count;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = ShopMsg::CUSTOM_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = 'Data is empty!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 获取授信明细
     */
    public function getCreditInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $buyerModel = new BuyerModel();
        $buyer_no = $buyerModel->field('buyer_no')->where(['id' => $this->user['buyer_id'], 'deleted_flag' => 'N'])->find();
        $credit_model = new BuyerCreditModel();
        $creditInfo = $credit_model->getInfo($buyer_no['buyer_no']);
        if($creditInfo) {
            if(!empty($creditInfo['approved_date'])){
                $time = strtotime('+90 days',strtotime($creditInfo['approved_date']));
                if($time <= time()) {
                    $creditInfo['status'] = 'INVALID';
                    $status['status'] = 'INVALID';
                    $credit_model->where(['buyer_no' => $creditInfo['buyer_no']])->save($status);
                }
            }
            jsonReturn($creditInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'data is empty!');
        }
    }

    private function _getBuyerNo($buyer_id){
        $buyerModel = new BuyerModel();
        return $buyerModel->field('buyer_no,country_bn')->where(['id' => $buyer_id, 'deleted_flag' => 'N'])->find();
    }

    private function _getCountryrCode($country_bn){
        $country_model = new CountryModel();
        return $country_model->field('code')->where(['bn' => $country_bn, 'deleted_flag' => 'N'])->find();
    }

    /* 代办人信息
     * @desc   企业/银行
     */
    private function _setAgentName(&$list) {
        foreach ($list as $log) {
            $agentids[] = $log['agent_by'];
        }

        $agent_model = new EmployeeModel();
        $agent_contact = $agent_model->getUserNamesByUserids($agentids);
        foreach ($list as $key => $val) {
            if (isset($agent_contact[$val['id']]) && $agent_contact[$val['id']]) {
                $val['agent_name'] = $agent_contact[$val['id']];
            } else {
                $val['agent_name'] = '';
            }
            $list[$key] = $val;
        }
    }
}