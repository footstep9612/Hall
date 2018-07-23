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
        date_default_timezone_set('PRC');
    }

    /**
     * 获取列表信息--授信管理
     */
    public function getCreditListAction() {
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
        //$data['agent_id'] = '37934';
        $model = new BuyerCreditModel();
        $res = $model->getCreditlist($data, $limit);
        if (!empty($res)) {
            foreach($res['data'] as $item) {
                if(!empty($item['approved_date']) && $item['status']=='APPROVED'){
                    if($item['account_settle'] == "OA"){
                        $deadline = $item['nolc_deadline'];
                    }else {
                        $deadline = $item['lc_deadline'];
                    }
                   // $time = strtotime(date('Y-m-d H:i:s',strtotime($item['approved_date']." +".$deadline." day")));
                    $time = strtotime(date('Y-m-d H:i:s',strtotime($item['approved_date'])+$deadline*24*60*60));
                    $current_time = strtotime('now');
//                    $content = $time.'-<通过是时间-------当前时间>-'.$current_time;
//                    LOG::write($content, LOG::INFO);
                    if($time <= $current_time) {
                        $item['status'] = 'INVALID';
                        $status['status'] = 'INVALID';
                        $model->where(['buyer_no' => $item['buyer_no']])->save($status);
                    }
                    unset($time);
                    unset($current_time);
                }
            }
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
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
        $data = $this->getPut();
        $model = new BuyerCreditModel();
        //$data['agent_id'] = UID;      //待确定查看权限
        // 权限控制，只获取客户经办人是自己的数据
        $buyerAgentModel = new BuyerAgentModel();
        $buyerModel = new BuyerModel();
        $buyerTableName = $buyerModel->getTableName();
        $buyerNoArr = $buyerAgentModel->alias('a')
                                                                       ->join($buyerTableName . ' b ON a.buyer_id = b.id AND b.deleted_flag = \'N\'', 'LEFT')
                                                                       ->where(['a.agent_id' => UID, 'a.deleted_flag' => 'N'])
                                                                       ->getField('buyer_no', true) ? : [];
        $data['buyer_no_arr'] = array_unique($buyerNoArr);
        $res = $model->getlist($data);
        $count = $model->getCount($data);
        if (!empty($res)) {
            foreach($res['data'] as $item) {
                if(!empty($item['approved_date']) && $item['status']=='APPROVED'){
                    if($item['account_settle'] == "OA"){
                        $deadline = $item['nolc_deadline'];
                    }else {
                        $deadline = $item['lc_deadline'];
                    }
                    $time = strtotime(date('Y-m-d H:i:s',strtotime($item['approved_date'])+$deadline*24*60*60));
                    $current_time = strtotime('now');
//                    $content = $time.'-<通过是时间-------当前时间>-'.$current_time;
//                    LOG::write($content, LOG::INFO);
                    if($time <= $current_time) {
                        $item['status'] = 'INVALID';
                        $status['status'] = 'INVALID';
                        $model->where(['buyer_no' => $item['buyer_no']])->save($status);
                    }
                    unset($time);
                    unset($current_time);
                }
            }
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
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
            $this->_setAgentName($res,'agent_by');
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
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
        $lang = empty($data['lang']) ? 'en' : $data['lang'];
        if($lang == 'zh') {
            if (empty($data['area_no'])) {
                jsonReturn(null, -110, '区域代码缺少!');
            }
        }
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
            jsonReturn(null, -110, '此国家不符合申请条件!');
            jsonReturn(null, -110, '企业所在国家简称代码');
        }
        if(empty($data['account_settle'])){      //结算方式
            jsonReturn(null, -110, '请选择结算方式!');
        }
        $company_model = new BuyerRegInfoModel();
        if($data['sign'] == 'ADD') {
            $check = $company_model->field('id')->where(['buyer_no' => $data['buyer_no'], 'deleted_flag' => 'N'])->find();
            if($check) {
                jsonReturn(null, -110, '该用户已申请!');
            }
            $res = $company_model->create_data($data);
        } else {
            $data['agent_by'] = UID;
            $check = $company_model->field('id')->where(['buyer_no' => $data['buyer_no'], 'deleted_flag' => 'N'])->find();
            if($check){
                $res = $company_model->update_data($data);
            } else {
                $res = $company_model->create_data($data);
            }
        }

        if($res) {
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, '成功!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED, '申请失败,请稍后再试!');
        }
    }

    /**
     * 银行代码申请
     */
    public function editBankAction(){
        $bank_data = json_decode(file_get_contents("php://input"), true);
        $lang = $bank_data['lang'] ? $bank_data['lang'] : 'zh';
        if (empty($bank_data['buyer_no'])) {
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
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, '成功!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED, '申请失败,请稍后再试!');
        }
    }

    /**
     * 获取企业申请信息
     */
    public function getCompanyInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $company_model = new BuyerRegInfoModel();
        $credit_model = new BuyerCreditModel();
        $comInfo = $company_model->getInfo($data['buyer_no']);
        if($comInfo) {
            $comInfo['biz_nature'] = empty($comInfo['biz_nature'])?[]:json_decode($comInfo['biz_nature'],true);
            $comInfo['biz_scope'] = empty($comInfo['biz_scope'])?[]:json_decode($comInfo['biz_scope'],true);
            $comInfo['stock_exchange'] = empty($comInfo['stock_exchange'])?[]:json_decode($comInfo['stock_exchange'],true);
            $comInfo['account_settle'] = $credit_model->getAccountSettleByNo($comInfo['buyer_no'],'account_settle');
            jsonReturn($comInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'数据为空!');
        }
    }

    /**
     * 获取银行申请信息
     */
    public function getBankInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $bank_model = new BuyerBankInfoModel();
        $credit_model = new BuyerCreditModel();
        $bankInfo = $bank_model->getInfo($data['buyer_no']);
        if($bankInfo) {
            $bankInfo['account_settle'] = $credit_model->getAccountSettleByNo($bankInfo['buyer_no'],'account_settle');
            jsonReturn($bankInfo, ShopMsg::CUSTOM_SUCCESS, '成功!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'数据为空!');
        }
    }

    /**
     * erui易瑞审核  --暂不用此方法
     */
    public function checkCreditAction(){
        $data = $this->getPut();
        //$edi_res= $this->EdiApplyAction($data);jsonReturn($edi_res); //先调用信保
        $lang = empty($data['lang']) ? 'zh' : $data['lang'];
        if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $data['status'] = $this->_checkStatus($data['status']);
        $credit_model = new BuyerCreditModel();
        $credit_log_model = new BuyerCreditLogModel();
        if($data['status']== 'EDI_APPROVING'){
            $data['buyer_no'] = 'ERUI_APPROVING';
            $res = $credit_model->update_data($data);
            if($res) {
                $dataArr['buyer_no'] = $data['buyer_no'];
                $dataArr['agent_by'] = UID;
                $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                $dataArr['sign'] = 1;
                $dataArr['in_status'] = 'EDI_APPROVING';
                $credit_log_model->create_data($dataArr);
                $dataArr['sign'] = 2;
                $credit_log_model->create_data($dataArr);
                //调用信保申请接口
               /* $edi_res= $this->EdiApplyAction($data);
                if(1 !== $edi_res){
                    jsonReturn('', ShopMsg::CREDIT_FAILED ,'正与信保调试中...!');
                }*/
            }
        } else {
            if (empty($data['bank_remarks']) && empty($data['remarks'])) {
                jsonReturn(null, -110, '请至少填写一项原因!');               //原因
            }
            $res = $credit_model->update_data($data);
            if($res){
                $dataArr['buyer_no'] = $data['buyer_no'];
                $dataArr['agent_by'] = UID;
                $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                $dataArr['in_status'] = $data['status'];
                if (isset($data['remarks']) && !empty($data['remarks'])) {
                    $dataArr['in_remarks'] = $data['remarks'];                    //企业原因
                }
                $dataArr['sign'] = 1;
                $credit_log_model->create_data($dataArr);
                if (isset($data['bank_remarks']) && !empty($data['bank_remarks'])) {
                    $dataArr['in_remarks'] = $data['bank_remarks'];                   //银行原因
                }
                $dataArr['sign'] = 2;
                $credit_log_model->create_data($dataArr);
            }
        }
        if($res) {
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, '成功!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'审核未通过,请稍后再试!');
        }
    }

    private function _checkStatus($status){

        switch ($status) {
            case 'APPROVED':    //审核通过
                $status = 'EDI_APPROVING';
                break;
            case 'REJECTED':    //审核驳回
                $status = 'ERUI_REJECTED';
                break;
            default:
                $status = 'EDI_APPROVING';
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
            $config_email = $config_obj->email;
            $email = $this->_getBuyerEmail($data['buyer_no']);
            $this->creditEmail($email['official_email'], '', $lang, (array)$config_email['url']);
            jsonReturn($result, ShopMsg::CUSTOM_SUCCESS, '成功!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'操作失败,请稍后再试!');
        }
    }

    //分配额度发送邮件
    function creditEmail($email,$arr, $lang, $emailUrl, $title= 'Erui.com') {
        $body = $this->getView()->render('credit/credit_approved_'.$lang.'.html', $arr);
        $data = [
            "title"        => $title,
            "content"      => $body,
            "groupSending" => 0,
            "useType"      => "Credit"
        ];
        if(is_array($email)) {
            $arr_email = implode(',',$email);
            $data["to"] = "[$arr_email]";
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
        $lang = empty($data['lang']) ? 'zh' : $data['lang'];
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
            $datajson['message'] = '暂无数据!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 获取授信明细
     */
    public function getCreditInfoAction(){
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $credit_model = new BuyerCreditModel();
        $creditInfo = $credit_model->getInfo($data['buyer_no']);
        if($creditInfo) {
            if(!empty($creditInfo['approved_date']) && $creditInfo['status']=='APPROVED'){
                if($creditInfo['account_settle'] == "OA"){
                    $deadline = $creditInfo['nolc_deadline'];
                }else {
                    $deadline = $creditInfo['lc_deadline'];
                }
                $time = strtotime(date('Y-m-d H:i:s',strtotime($creditInfo['approved_date'])+$deadline*24*60*60));
                $current_time = strtotime('now');
//                $content = $time.'-<通过是时间-------当前时间>-'.$current_time;
//                LOG::write($content, LOG::INFO);
                if($time <= $current_time) {
                    $creditInfo['status'] = 'INVALID';
                    $status['status'] = 'INVALID';
                    $credit_model->where(['buyer_no' => $creditInfo['buyer_no']])->save($status);
                }
                unset($time);
                unset($current_time);
            }
            jsonReturn($creditInfo, ShopMsg::CUSTOM_SUCCESS, '成功!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'暂无数据!');
        }
    }

    /**
     * 请求信保审核
     */
//    public function EdiApplyAction() {
//        $data = $this->getPut();
//        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
//            jsonReturn(null, -110, '客户编号缺失!');
//        }
//        //$edi_apply_model = new EdiBuyerApplyModel();
//        $res_buyer = $this->BuyerApply($data['buyer_no']);
//        $res_bank = $this->BankApply($data['buyer_no']);
//        if($res_buyer['code'] != 1 || $res_bank['code'] != 1) {
//            jsonReturn('', ShopMsg::CREDIT_FAILED ,'正与信保调试中...!');
//        }
//        $credit_model = new BuyerCreditModel();
//        $arr['status'] = 'EDI_APPROVING';
//        $credit_model->where(['buyer_no' => $data['buyer_no']])->save($arr);;
//        jsonReturn(null, ShopMsg::CREDIT_SUCCESS, '成功!');
//    }

    /**
     *
     *买家代码申请
     * @author klp
     */
//    public function BuyerApply($buyer_no){
//
//        $buyerModel = new BuyerModel();          //企业信息
////        $BuyerCodeApply = $buyerModel->buyerCerdit($buyer_no);
//        $company_model = new BuyerRegInfoModel();
//        $BuyerCodeApply = $company_model->getInfo($buyer_no);
//        $lang = $buyerModel->field('lang')->where(['buyer_no'=> $buyer_no, 'deleted_flag'=>'N'])->find();
//        if(!$BuyerCodeApply || !$lang){
//            jsonReturn(null, -101 ,'企业信息不存在或已删除!');
//        }
//        $BuyerCodeApply['lang'] = $lang['lang'];
//        //$SinoSure = new Edi();
//        $resBuyer = Edi::EdiBuyerCodeApply($BuyerCodeApply);
//        if($resBuyer['code'] != 1) {
//            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
//        }
//        jsonReturn($resBuyer);
//        /* $this->setCode(MSG::MSG_SUCCESS);
//         $this->setMessage('申请成功!');
//         $this->jsonReturn($resBuyer);*/
//    }

    /**
     *
     *银行代码申请
     * @author klp
     */
//    public function BankApply($buyer_no){
////        $buyerModel = new BuyerModel();          //银行信息
////        $BuyerBankApply = $buyerModel->buyerCerdit($buyer_id);
//        $bank_model = new BuyerBankInfoModel();
//        $BuyerBankApply = $bank_model->getInfo($buyer_no);
//        if(!$BuyerBankApply){
//            jsonReturn(null, -101 ,'银行信息不存在或已删除!');
//        }
//        $SinoSure = new Edi();
//        $resBank = $SinoSure->EdiBankCodeApply($BuyerBankApply);
//
//        if($resBank['code'] != 1) {
//            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
//        }
//        jsonReturn($resBank);
//        /*  $this->setCode(MSG::MSG_SUCCESS);
//          $this->setMessage('申请成功!');
//          $this->jsonReturn($resBank);*/
//    }

    /* 代办人信息
     * @desc   企业/银行
     */
    private function _setAgentName(&$list,$name) {
        foreach ($list as $log) {
            $agentids[] = $log[$name];
        }

        $agent_model = new EmployeeModel();
        $agent_contact = $agent_model->getUserNamesByUserids($agentids);
        foreach ($list as $key => $val) {
            if (isset($agent_contact[$val[$name]]) && $agent_contact[$val[$name]]) {
                $val['agent_name'] = $agent_contact[$val[$name]];
            } else {
                $val['agent_name'] = '';
            }
            $list[$key] = $val;
        }
    }

    /**授信订单使用收支
     * @desc
     */
    public function buyerCreditPaymentByOrderAction(){
        $data = $this->getPut();
        $money = '100.00';

        if(!isset($data['contract_no']) || empty($data['contract_no'])){
            jsonReturn('',MSG::MSG_FAILED,'');
        }
        if(!isset($data['crm_code']) || empty($data['crm_code'])){
            jsonReturn('',MSG::MSG_FAILED,'');
        }

        $lockKey = MYPATH . '/public/tmp/orderpay_' . $data['contract_no'] . '.lock';
        if(!file_exists($lockKey)){
            $dirName = MYPATH . '/public/tmp';
            if (!is_dir($dirName)) {
                if (!mkdir($dirName, 0777, true)) {
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建订单合同授信文件锁失败，请尝试手动创建', Log::NOTICE);
                }
            }
        }

        $buyer_model = new BuyerModel();
        $buyerInfo = $buyer_model->field('buyer_no')->where(['buyer_code'=>$data['crm_code'],'deleted_flag'=>'N'])->find();
        if(!$buyerInfo){
            jsonReturn('',MSG::MSG_FAILED,'客户信息不存在或已被删除!');
        }
        $buyer_credit_model = new BuyerCreditModel();
        $buyer_credit_Info = $buyer_credit_model->getInfo($buyerInfo['buyer_no']);
        if(!$buyer_credit_Info){
            jsonReturn('',MSG::MSG_FAILED,'客户没有进行授信申请或已被删除!');
        }
        if(empty($buyer_credit_Info['approved_date']) || empty($buyer_credit_Info['credit_valid_date'])){
            jsonReturn('',MSG::MSG_FAILED,'客户授信还未分配!');
        }
        if($buyer_credit_Info['status']!='APPROVED'){
            jsonReturn('',MSG::MSG_FAILED,'客户授信额度已失效!');
        }

        if(empty($buyer_credit_Info['credit_available']) || $buyer_credit_Info['credit_available'] < $money){
            jsonReturn('',MSG::MSG_FAILED,'可用授信额度已不足!');
        }
        $buyer_credit_model->startTrans();
        try{
            //上锁
            $handle_res = $this->getLock($lockKey);

            if($handle_res[0] && $handle_res[1]){

                $left = $buyer_credit_Info['credit_available'] - $money;  //余额
                $dataCredit['credit_available'] = $left;
                $dataCredit['buyer_no'] = $buyerInfo['buyer_no'];
                $dataCredit['crm_code'] = $data['crm_code'];
                $updateRes = $buyer_credit_model->update_data($dataCredit);
                if(!$updateRes){
                    $buyer_credit_model->rollback();
                }
                //授信使用明细
                $type = ($money<0)? 'SPENDING' : 'REFUND';    //SPENDING-支出；REFUND-还款
                $creditLogArr = [
                    'buyer_no'=> $buyerInfo['buyer_no'],
                    'credit_type'=> $buyer_credit_Info['account_settle'],
                    'credit_cur_bn'=> $buyer_credit_Info['credit_cur_bn'],
                    'use_credit_granted'=> '-'.$money,//使用额度
                    'credit_available'=> $left,//剩余可用额度(使用后)
                    'content'=> '', //备注内容
                   // 'order_id'=> '',
                    'contract_no'=> $data['contract_no'], //销售合同号
                    'crm_code'=> $data['crm_code'], //crm编码
                    'type'=> $type, //收支类型
                    'credit_at'=> date('Y-m-d H:i:s',time())
                ];
                $buyer_credit_order_log_model = new BuyerCreditOrderLogModel();
                $log_res = $buyer_credit_order_log_model->addRecord($creditLogArr);

                if(!$log_res){
                    $buyer_credit_model->rollback();
                }
            } else {
                fclose($handle_res[0]);
                jsonReturn('',MSG::MSG_FAILED,'系统繁忙,请稍后再试!');
            }

            if($updateRes && $log_res) {
                $buyer_credit_model->commit();
                $res= true;
            }else{
                $buyer_credit_model->rollback();
                $res = false;
            }
            //释放锁
            $this->releaseLock($handle_res[0]);

            if($res){
                $this->setCode(MSG::MSG_SUCCESS);
                $datajson['data'] = '操作成功!';
                $this->jsonReturn($datajson);
            }else{
                $this->setCode(MSG::MSG_FAILED);
                $this->setMessage(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }catch (Exception $e) {
            $buyer_credit_model->rollback();
        //释放锁
            $this->releaseLock($handle_res[0]);
            $res = false;
            $this->jsonReturn($res);
        }

    }

//上锁
    function getLock($lockKey){
        $handle = fopen($lockKey, "w");
        if ($handle === false) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Lock Error: Lock file [' . $lockKey . '] create faild.', Log::ERR);
            return [false,false];
        }
        return [$handle,flock($handle, LOCK_EX)];

    }
//释放
    function releaseLock($handle){
        if ($handle !== false) {
            flock($handle, LOCK_UN);
        }
        //进行关闭
        fclose($handle);

    }

    /**授信订单使用回滚机制
     * @desc
     */
    public function buyerCreditPaymentByOrderRollbackAction(){
        $data = $this->getPut();
        if(!isset($data['contract_no']) || empty($data['contract_no'])){
            jsonReturn('',MSG::MSG_FAILED,'');
        }
        if(!isset($data['crm_code']) || empty($data['crm_code'])){
            jsonReturn('',MSG::MSG_FAILED,'');
        }

        $lockKey = MYPATH . '/public/tmp/orderpayrollback_' . $data['contract_no'] . '.lock';
        if(!file_exists($lockKey)){
            $dirName = MYPATH . '/public/tmp';
            if (!is_dir($dirName)) {
                if (!mkdir($dirName, 0777, true)) {
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建订单合同授信文件锁失败，请尝试手动创建', Log::NOTICE);
                }
            }
        }

        $buyer_credit_order_log_model = new BuyerCreditOrderLogModel();
        $orderLogWhere = [
            'crm_code'=>$data['crm_code'],
            'contract_no'=>$data['contract_no'],
            'deleted_flag'=>'N'
        ];
        $buyerCreditOrderLogInfo = $buyer_credit_order_log_model->getInfo($orderLogWhere);
        if(!$buyerCreditOrderLogInfo){
            jsonReturn('',MSG::MSG_FAILED,'没有此合同操作日志!');
        }
        $buyer_credit_model = new BuyerCreditModel();

        $buyer_credit_model->startTrans();
        try{
            //上锁
            $handle_res = $this->getLock($lockKey);

            if($handle_res[0] && $handle_res[1]){

                $rollback_left = $buyerCreditOrderLogInfo['credit_available'] - $buyerCreditOrderLogInfo['use_credit_granted'];  //余额
                $dataCreditRollback['credit_available'] = $rollback_left;
                $dataCreditRollback['buyer_no'] = $buyerCreditOrderLogInfo['buyer_no'];
                $dataCreditRollback['crm_code'] = $data['crm_code'];
                $updateRollback = $buyer_credit_model->update_data($dataCreditRollback);
                if(!$updateRollback){
                    $buyer_credit_model->rollback();
                }
                //授信使用明细
                $creditLogRollbackArr = [
                    'buyer_no'=> $buyerCreditOrderLogInfo['buyer_no'],
                    'credit_type'=> $buyerCreditOrderLogInfo['credit_type'],
                    'credit_cur_bn'=> $buyerCreditOrderLogInfo['credit_cur_bn'],
                    'use_credit_granted'=> -$buyerCreditOrderLogInfo['use_credit_granted'],//使用额度
                    'credit_available'=> $rollback_left,//剩余可用额度(使用后)
                    'content'=> '回滚,合同号:'.$data['contract_no'].',合同金额:'.-$buyerCreditOrderLogInfo['use_credit_granted'], //备注内容
                    // 'order_id'=> '',
                    'contract_no'=> $data['contract_no'], //销售合同号
                    'crm_code'=> $data['crm_code'], //crm
                    'credit_at'=> date('Y-m-d H:i:s',time())
                ];

                $log_rollback = $buyer_credit_order_log_model->addRecord($creditLogRollbackArr);

                if(!$log_rollback){
                    $buyer_credit_model->rollback();
                }
            } else {
                fclose($handle_res[0]);
                jsonReturn('',MSG::MSG_FAILED,'系统繁忙,请稍后再试!');
            }

            if($updateRollback && $log_rollback) {
                $buyer_credit_model->commit();
                $res= true;
            }else{
                $buyer_credit_model->rollback();
                $res = false;
            }
            //释放锁
            $this->releaseLock($handle_res[0]);

            if($res){
                $this->setCode(MSG::MSG_SUCCESS);
                $datajson['data'] = '回滚成功!';
                $this->jsonReturn($datajson);
            }else{
                $this->setCode(MSG::MSG_FAILED);
                $this->setMessage(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }catch (Exception $e) {
            $buyer_credit_model->rollback();
            //释放锁
            $this->releaseLock($handle_res[0]);
            $res = false;
            $this->jsonReturn($res);
        }

    }

    /**
     * 获取订单授信使用明细
     */
    public function getOrderLogListAction() {
        $data = $this->getPut();
        $buyer_credit_order_log_model = new BuyerCreditOrderLogModel();
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        if(!isset($data['crm_code']) || empty($data['crm_code'])) {
            jsonReturn(null, -110, '客户crm编号缺失!');
        }
        $res = $buyer_credit_order_log_model->getlist($data);
        $count = $buyer_credit_order_log_model->getCount($data);
        if (!empty($res)) {
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
            $datajson['count'] = $count;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = ShopMsg::CUSTOM_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * java订单授信金额
     */
    public function getCreditInfoByCrmCodeAction() {
        $data = $this->getPut();
        if(empty($data['crm_code']) && empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编码缺失!');
        }
        $buyer_no = '';
        if(isset($data['crm_code']) && !empty($data['crm_code'])){
            $buyer_model = new BuyerModel();
            $buyerInfo = $buyer_model->field('buyer_no')->where(['buyer_code'=>$data['crm_code'],'deleted_flag'=>'N'])->find();
            if(!$buyerInfo){
                jsonReturn('',MSG::MSG_FAILED,'客户信息不存在或已被删除!');
            }
            $buyer_no = $buyerInfo['buyer_no'];
        }
        if(isset($data['buyer_no']) && !empty($data['buyer_no'])){
            $buyer_no = $data['buyer_no'];
        }
        $buyer_credit_model = new BuyerCreditModel();
        $buyer_credit_Info = $buyer_credit_model->getInfo($buyer_no);
        if(!$buyer_credit_Info){
            jsonReturn('',MSG::MSG_FAILED,'客户没有进行授信申请或已被删除!');
        }
        if(empty($buyer_credit_Info['approved_date']) || empty($buyer_credit_Info['credit_valid_date'])){
            jsonReturn('',MSG::MSG_FAILED,'客户授信还未分配!');
        }
        if($buyer_credit_Info['status']!='APPROVED'){
            jsonReturn('',MSG::MSG_FAILED,'客户授信额度已失效!');
        }
        if (!empty($buyer_credit_Info)) {
            $datajson['code'] = MSG::MSG_SUCCESS;
            $datajson['data'] = $buyer_credit_Info;
            $datajson['message'] = '成功!';
        } else {
            $datajson['code'] = MSG::MSG_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 获取订单授信待办事项
     */
    public function getBuyerCreditToDoListAction() {
        $data = $this->getPut();
        $model = new BuyerCreditModel();
        // 权限控制，只获取客户经办人是自己的数据
        $buyerAgentModel = new BuyerAgentModel();
        $buyerModel = new BuyerModel();
        $buyerTableName = $buyerModel->getTableName();
        $buyerNoArr = $buyerAgentModel->alias('a')
            ->join($buyerTableName . ' b ON a.buyer_id = b.id AND b.deleted_flag = \'N\'', 'LEFT')
            ->where(['a.agent_id' => UID, 'a.deleted_flag' => 'N'])
            ->getField('buyer_no', true) ? : [];
        $data['buyer_no_arr'] = array_unique($buyerNoArr);
        //$data['status'] = ['APPROVING','ERUI_APPROVING'];
        $res = $model->getlist($data);

        if (!empty($res)) {
            $datajson['code'] = MSG::MSG_SUCCESS;
            $datajson['data'] = $res;
            $datajson['message'] = '成功!';
        } else {
            $datajson['code'] = MSG::MSG_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }


}