<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/2/26
 * Time: 10:43
 */

class BuyercreditController extends PublicController {

    public function init(){
        parent::init();
    }

    /**
     * 获取列表信息--授信管理
     */
    public function getCreditListAction() {
        $data = [
            0=>[
                "id"=> "1",
                "name"=> "test1",
                "buyer_no"=> "C20171208000019",
                "bank_swift"=> null,
                "sinosure_no"=> null,
                "credit_apply_date"=> "2018-03-01 00:00:00",
                "credit_valid_date"=> null,
                "source"=> "PORTAL",
                "status"=> "DRAFT",
                "agent_id"=> "37959",
                "country_code"=> "ALB",
                "country"=> "Albania",
                "crm_code"=> null
            ],
            1=>[
                "id"=> "2",
                "name"=> "test1",
                "buyer_no"=> "C20171208000020",
                "bank_swift"=> null,
                "sinosure_no"=> null,
                "credit_apply_date"=> "2018-03-01 00:00:00",
                "credit_valid_date"=> null,
                "source"=> "PORTAL",
                "status"=> "DRAFT",
                "agent_id"=> "37959",
                "country_code"=> "ALB",
                "country"=> "Albania",
                "crm_code"=> null
            ]
        ];
        jsonReturn($data);

        $data = $this->getPut();
        $limit = [];
        if(!empty($data['pageSize'])){
            $limit['num'] = $data['pageSize'];
        } else {
            $limit['num'] = 10;
        }
        if(!empty($data['currentPage'])) {
            $limit['page'] = ($data['currentPage'] - 1) * $limit['num'];
        } else {
            $limit['page'] = 1;
        }
        //$data['agent_id'] = UID;
        $model = new BuyerCreditModel();
        $res = $model->getCreditlist($data, $limit);
        if (!empty($res)) {
            $datajson['code'] = ShopMsg::CREDIT_SUCCESS;
            $datajson['count'] = $res['count'];
            $datajson['data'] = $res['data'];
        } else {
            $datajson['code'] = ShopMsg::CREDIT_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = 'Data is empty!';
        }

        $this->jsonReturn($datajson);
    }

    /**
     * 获取列表信息--代码申请管理
     */
    public function getListAction() {
        $data = [
            0=>[
                "id"=> "2",
                "agent_id"=> "37959",
                "name"=> "test",
                "buyer_no"=> "C20171208000019",
                "sinosure_no"=> null,
                "credit_apply_date"=> "2018-03-01 00:00:00",
                "status"=> "ERUI_APPROVED, 待提交-DRAFT,易瑞审核中-ERUI_APPROVING,易瑞审核通过-ERUI_APPROVED,信保审核中-EDI_APPROVING,信保审核通过-EDI_APPROVED,易瑞驳回-ERUI_REJECTED,信保驳回-EDI_REJECTED,审核过期-INVALID'"
            ],
            1=>[
                "id"=> "1",
                "agent_id"=> "37959",
                "name"=> "test",
                "buyer_no"=> "C20171208000019",
                "sinosure_no"=> null,
                "credit_apply_date"=> "2018-03-01 00:00:00",
                "status"=> "ERUI_APPROVING, 待提交-DRAFT,易瑞审核中-ERUI_APPROVING,易瑞审核通过-ERUI_APPROVED,信保审核中-EDI_APPROVING,信保审核通过-EDI_APPROVED,易瑞驳回-ERUI_REJECTED,信保驳回-EDI_REJECTED,审核过期-INVALID'"
            ]
        ];
        jsonReturn($data);

        $data = $this->getPut();
        $model = new BuyerCreditModel();
        $data['agent_id'] = UID;
        $res = $model->getlist($data);
        $count = $model->getCount($data);
        if (!empty($res)) {
            $datajson['code'] = ShopMsg::CREDIT_SUCCESS;
            $datajson['count'] = $count;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = ShopMsg::CREDIT_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = 'Data is empty!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 获取申请日志信息  银行或企业信息--银行sign:2;企业sign:1
     */
    public function getListLogAction() {
        $data = $this->getPut();
        $model = new BuyerCreditLogModel();
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, ShopMsg::CREDIT_FAILED, '客户编码缺失!');
        }
        $res = $model->getlist($data);
        $count = $model->getCount($data);
        if (!empty($res)) {
            $datajson['code'] = ShopMsg::CREDIT_SUCCESS;
            $datajson['count'] = $count;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = ShopMsg::CREDIT_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = 'Data is empty!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 企业代码申请
     */
    public function editCompanyAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
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
            jsonReturn(null, -110, '不符合申请条件!');
            jsonReturn(null, -110, '企业所在国家简称代码');
        }
        $buyerModel = new BuyerModel();
        $company_model = new BuyerRegInfoModel();

        $data['agent_by'] = UID;
        $check = $company_model->field('id')->where(['buyer_no' => $data['buyer_no'], 'deleted_flag' => 'N'])->find();
        if($check){
            $res = $company_model->update_data($data);
        } else {
            $res = $company_model->create_data($data);
        }
        if($res) {
            jsonReturn($res, ShopMsg::CREDIT_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED, 'failed!');
        }
    }

    /**
     * 银行代码申请
     */
    public function editBankAction(){
        $bank_data = json_decode(file_get_contents("php://input"), true);
        $lang = $bank_data['lang'] ? $bank_data['lang'] : 'en';
        if (empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        if (empty($bank_data['bank_country_code'])) {
            jsonReturn(null, -110, '银行所在国家简称代码');
        }
        if (empty($bank_data['bank_name'])) {
            jsonReturn(null, -110, '开户银行英文名称');
        }
        if (empty($bank_data['bank_address'])) {
            jsonReturn(null, -110, '银行地址');
        }
        if (empty($bank_data['tel_bank'])) {
            jsonReturn(null, -110, '银行电话');
        }
        $buyerModel = new BuyerModel();
        $bank_model = new BuyerBankInfoModel();

        $bank_data['agent_by'] = UID;
        $check = $bank_model->field('id')->where(['buyer_no' => $bank_data['buyer_no'], 'deleted_flag' => 'N'])->find();
        if($check){
            $res = $bank_model->update_data($bank_data);
        } else {
            $res = $bank_model->create_data($bank_data);
        }
        if($res) {
            jsonReturn($res, ShopMsg::CREDIT_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED, 'failed!');
        }
    }

    /**
     * 获取企业申请信息
     */
    public function getCompanyInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $buyerModel = new BuyerModel();
        $buyer_no = $buyerModel->field('buyer_no')->where(['id' => UID, 'deleted_flag' => 'N'])->find();
        $company_model = new BuyerRegInfoModel();
        $comInfo = $company_model->getInfo($buyer_no);
        if($comInfo) {
            jsonReturn($comInfo, ShopMsg::CREDIT_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'data is empty!');
        }
    }

    /**
     * 获取银行申请信息
     */
    public function getBankInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $buyerModel = new BuyerModel();
        $buyer_no = $buyerModel->field('buyer_no')->where(['id' => UID, 'deleted_flag' => 'N'])->find();
        $bank_model = new BuyerBankInfoModel();
        $bankInfo = $bank_model->getInfo($buyer_no);
        if($bankInfo) {
            jsonReturn($bankInfo, ShopMsg::CREDIT_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'data is empty!');
        }
    }

    /**
     * erui易瑞审核
     */
    public function checkCreditAction(){
        $data = $this->getPut();
        if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }

        $data['status'] = $this->_checkStatus($data['status']);
        $credit_model = new BuyerCreditModel();
        $credit_log_model = new BuyerCreditLogModel();
        if($data['status']== 'ERUI_APPROVED'){
            $res = $credit_model->update_data($data);
            if($res) {
                $dataArr['agent_by'] = UID;
                $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                $dataArr['sign'] = 1;
                $dataArr['in_status'] = 'ERUI_APPROVED';
                $credit_log_model->create_data($dataArr);
                $dataArr['sign'] = 2;
                $credit_log_model->create_data($dataArr);
            }
        } else {
            $res = $credit_model->update_data($data);
            if($res) {
                $dataArr['agent_by'] = UID;
                $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                $dataArr['in_status'] = $data['status'];
                if (isset($data['remarks']) && !empty($data['remarks'])) {
                    $data['in_remarks'] = $data['remarks'];                    //企业原因
                }
                $dataArr['sign'] = 1;
                $credit_log_model->create_data($dataArr);
                if (isset($data['bank_remarks']) && !empty($data['bank_remarks'])) {
                    $data['in_remarks'] = $data['bank_remarks'];                   //银行原因
                }
                $dataArr['sign'] = 2;
                $credit_log_model->create_data($dataArr);
            }

        }

    }

    private function _checkStatus($status){

        switch ($status) {
            case 'APPROVED':    //审核通过
                $status = 'ERUI_APPROVED';
                break;
            case 'REJECTED':    //审核驳回
                $status = 'ERUI_REJECTED';
                break;
            default:
                $status = 'ERUI_APPROVED';
                break;
        }
        return $status;
    }

    /**
     * 分配额度
     */
    public function grantQuotaAction() {
        $data = $this->getPut();
        $lang = empty($data['lang']) ? 'en' : $data['lang'];
        if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $credit_model = new BuyerCreditModel();
        $result = $credit_model->grantInfo($data);
        if($result) {
            //发送邮件
            $config_obj = Yaf_Registry::get("config");
            $config_email = $config_obj->email->toArray();
            $email = $this->_getBuyerEmail($data['buyer_no']);
            $this->orderEmail($email['official_email'], '', $lang, $config_email['url']);
            jsonReturn($result, ShopMsg::CREDIT_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'failed!');
        }
    }

    //分配额度发送邮件
    function orderEmail($email,$arrEmail, $lang, $emailUrl, $title= 'Erui.com') {
        $body = $this->getView()->render('credit/credit_approved_'.$lang.'.html', $arrEmail);
        $data = [
            "title"        => $title,
            "content"      => $body,
            "groupSending" => 0,
            "useType"      => "Credit"
        ];
        if(is_array($email)) {
            $arr = implode(',',$email);
            $data["to"] = "[$arr]";
        }elseif(is_string($email)){
            $data["to"] = "[$email]";
        }
        PostData($emailUrl, $data, true);
    }

    private function _getBuyerEmail($buyer_no){
        $buyerModel = new BuyerModel();
        return $buyerModel->field('official_email')->where(['buyer_no' => $buyer_no, 'deleted_flag' => 'N'])->find();
    }

    /**
     * 获取申限额申请明细
     */
    public function getQuotaListAction() {
        $data = $this->getPut();
        $model = new BuyerQuotaLogModel();
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
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
        $data = [
            0=>[
                "id"=> "1",
                "agent_id"=> "37959",
                "name"=> "name",
                "buyer_no"=> "C20171208000019",
                "bank_swift"=> null,
                "sinosure_no"=> null,
                "nolc_granted"=> null,
                "nolc_deadline"=> null,
                "lc_granted"=> null,
                "lc_deadline"=> null,
                "deadline_cur_unit"=> "day",
                "credit_cur_bn"=> null,
                "credit_apply_date"=> "2018-03-01 00:00:00",
                "credit_valid_date"=> null,
                "source"=> "PORTAL",
                "status"=> "DRAFT"
            ],
            1=>[
                "id"=> "2",
                "agent_id"=> "37959",
                "name"=> "name",
                "buyer_no"=> "C20171208000020",
                "bank_swift"=> null,
                "sinosure_no"=> null,
                "nolc_granted"=> null,
                "nolc_deadline"=> null,
                "lc_granted"=> null,
                "lc_deadline"=> null,
                "deadline_cur_unit"=> "day",
                "credit_cur_bn"=> null,
                "credit_apply_date"=> "2018-03-01 00:00:00",
                "credit_valid_date"=> null,
                "source"=> "PORTAL",
                "status"=> "DRAFT"
            ]
        ];
        jsonReturn($data);

        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $bank_model = new BuyerCreditModel();
        $bankInfo = $bank_model->getInfo($data['buyer_no']);
        if($bankInfo) {
            jsonReturn($bankInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'data is empty!');
        }
    }
}