<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/19
 * Time: 9:34
 */
/**
 * 实现接口如下:
 *          1、买方企业信息：买方代码申请接口
 *          2、买方银行信息：买方银行代码申请接口
 *          3、获取买方企业信息申请反馈：买方代码申请反馈接口
 *          4、获取买方银行信息申请反馈：银行代码申请反馈接口
 *          5、获取买方授信额度：出口险-限额批复
 *          6、获取买方可用余额：出口险-限额余额查询<新版>
 * [备注]以下反馈查询调用频率为:
 *         调用频率：15分钟以上【15分钟、30分钟、1小时等】
 * 调用日期区间：
 *        如 2015-05-05这天的调用全部为(两天数据)：
 *        [2015-05-04 00:00:00,2015-05-05 23:59:59]
 * */

class EdiController extends PublicController{


    private $serverIP = '39.107.75.138';

    private $serverPort = '8086';

    private $serverDir = 'ediserver';

    private $serverDirSec = 'ws_services';

    static private $serviceUri = '';

    private $serviceInterface = 'SolEdiProxyWebService';

    private $mode = 'wsdl';

    static private $policyNo = 'SCH043954-181800';

    static private $client;

    static private $url_wsdl = "http://credit.eruidev.com:80/ediserver/ws_services/SolEdiProxyWebService?wsdl";

    public function init(){
        parent::init();
        //error_reporting(E_ALL & ~E_NOTICE);

        //$config_obj = Yaf_Registry::get("config");
        //$this->serverIP = $config_obj->database->config->toArray();
        if (self::$serviceUri == '') {
//            $this->serverDir = '/' . pathinfo(dirname($_SERVER['SCRIPT_NAME']), PATHINFO_FILENAME) . '/';
            self::$serviceUri = 'http://'.$this->serverIP.':'.$this->serverPort.'/'.$this->serverDir.'/'.$this->serverDirSec.'/'.$this->serviceInterface;
        }
        if ($this->mode == 'wsdl') {
            self::$serviceUri .= '?wsdl';
        }

        //self::$client = new SoapClient($this->serviceUri);

    }

    /**
     * erui易瑞审核
     */
    public function checkCreditAction(){
        $data = $this->getPut();
        $lang = empty($data['lang']) ? 'zh' : $data['lang'];
        if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $data['status'] = $this->_checkStatus($data['status']);
        $credit_model = new BuyerCreditModel();
        $credit_log_model = new BuyerCreditLogModel();
        if($data['status']== 'EDI_APPROVING'){
            $data['status'] = 'ERUI_APPROVING';
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
                 $edi_res= $this->EdiApply($data);
                 if(1 !== $edi_res){
                     jsonReturn('', ShopMsg::CREDIT_FAILED ,'正与信保调试中...!');
                 }
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
            jsonReturn($res, ShopMsg::CREDIT_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CREDIT_FAILED ,'failed!');
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
     * 请求信保审核
     */
    public function EdiApply($data) {
        //$data = $this->getPut();
        if(!isset($data['buyer_no']) || empty($data['buyer_no'])) {
            jsonReturn(null, -110, '客户编号缺失!');
        }
        $res_buyer = $this->BuyerApply($data['buyer_no']);
        $res_bank = $this->BankApply($data['buyer_no']);
        if($res_buyer['code'] == 1 && $res_bank['code'] == 1) {
            $credit_model = new BuyerCreditModel();
            $arr['status'] = 'EDI_APPROVING';
            $credit_model->where(['buyer_no' => $data['buyer_no']])->save($arr);
            return ShopMsg::CREDIT_SUCCESS;
            //jsonReturn(null, ShopMsg::CREDIT_SUCCESS, '成功!');
        }
        return ShopMsg::CREDIT_FAILED;
        //jsonReturn('', ShopMsg::CREDIT_FAILED ,'正与信保调试中...!');

    }
    /**
     *
     *买家代码申请
     * @author klp
     */
    public function BuyerApply($buyer_no){

        $buyerModel = new BuyerModel();          //企业信息
        $company_model = new BuyerRegInfoModel();
        $BuyerCodeApply = $company_model->getInfo($buyer_no);
        $lang = $buyerModel->field('lang,official_email')->where(['buyer_no'=> $buyer_no, 'deleted_flag'=>'N'])->find();
        if(!$BuyerCodeApply || !$lang){
            jsonReturn(null, -101 ,'企业信息不存在或已删除!');
        }
        $BuyerCodeApply['lang'] = $lang['lang'];
        $BuyerCodeApply['official_email'] = $lang['official_email'];
        $resBuyer = self::EdiBuyerCodeApply($BuyerCodeApply);
        if($resBuyer['code'] != 1) {
            jsonReturn(null,MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        return $resBuyer;
    }

    /**
     *
     *银行代码申请
     * @author klp
     */
    public function BankApply($buyer_no){

        $bank_model = new BuyerBankInfoModel();
        $BuyerBankApply = $bank_model->getInfo($buyer_no);
        if(!$BuyerBankApply){
            jsonReturn(null, -101 ,'银行信息不存在或已删除!');
        }
        $resBank = self::EdiBankCodeApply($BuyerBankApply);

        if($resBank['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        return $resBank;
    }

    static public function getStartDate(){
        return $startDate = date('Y-m-d\T00:00:00', mktime('-1'));
    }

    static public function getEndDate(){
        return $endDate =  date('Y-m-d\T23:59:59', time());
    }


    public function testAction(){
        header("content-type:text/html;charset=utf-8");
        try {
            $client = new SoapClient("http://localhost:8121/ediserver/ws_services/SolEdiProxyWebService?wsdl");
            echo '<pre>';
            print_r($client);
            print_r($client->__getFunctions());
            print_r($client->__getTypes());
        } catch (SOAPFault $e) {
            print $e;
        }
    }

    protected function testEdiBuyerCodeAction(){
        $buyerCodeApplyInfo['corpSerialNo'] = '1';
        $buyerCodeApplyInfo['corpSerialNo'] = '1';
        $buyerCodeApplyInfo['policyNo'] = 'SCH043954-181800';
        $buyerCodeApplyInfo['engName'] = 'Toyota Motor Sales, U.S.A., Inc';
        $buyerCodeApplyInfo['countryCode'] = 'USA';
        $buyerCodeApplyInfo['engAddress'] = 'USA';
        $buyerCodeApplyInfo['applyTime'] = strtotime('now');

        $data = array('buyerCodeApplyInfoList' => array('BuyerCodeApplyInfo' => array($buyerCodeApplyInfo)));
        try {
            $response = self::$client->doEdiBuyerCodeApply($data);
            var_dump($response);
            $buyerCodeApproveInfo = $response->BuyerCodeApproveInfo;
            if ($buyerCodeApproveInfo) {
    //            foreach ($countryClassifyArr as $countryClassify) {
                    var_dump($buyerCodeApproveInfo->BuyerInfo);
    //            }
            } else {
                echo 123;
    //            $this->output->writeln('no data');
            }
        } catch(\Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * 买家代码申请
     *
     */
    static public function EdiBuyerCodeApply($BuyerApply){
        self::checkParamBuyer($BuyerApply);
        $BuyerCodeApply = self::_getBuyerValue($BuyerApply);
        $result = self::_EdiBuyerCodeApply($BuyerCodeApply);
        if($result['code']  == 1){
            $res['code'] = 1;
        } else{
            $res['code'] = -101;
        }
        return $res;
    }

    static public function checkParamBuyer(&$BuyerCodeApply){
        $results = array();
        if(!isset($BuyerCodeApply['lang'])){
            $results['code'] = -101;
            $results['message'] = '[lang]不能为空!';
        }
        if($BuyerCodeApply['lang'] == 'zh') {
            if(!isset($BuyerCodeApply['area_no']) || !is_numeric($BuyerCodeApply['area_no'])){
                $results['code'] = -101;
                $results['message'] = '[area_no]不能为空或不为整型!';
            }
            if(!isset($BuyerCodeApply['social_credit_code'])){
                $results['code'] = -101;
                $results['message'] = '[social_credit_code]社会信用代码不能为空!';
            }
        }
        if(!isset($BuyerCodeApply['buyer_no'])){
            $results['code'] = -101;
            $results['message'] = '[buyer_no]不能为空!';
        }
        if(!isset($BuyerCodeApply['country_code'])){
            $results['code'] = -101;
            $results['message'] = '[country_code]不能为空!';
        }
        if(strlen($BuyerCodeApply['country_code']) > 3){
            $results['code'] = -101;
            $results['message'] = '[country_code]不能超过三位!';
        }
        if(!isset($BuyerCodeApply['name'])){
            $results['code'] = -101;
            $results['message'] = '[name]不能为空!';
        }
        if(!isset($BuyerCodeApply['registered_in'])){
            $results['code'] = -101;
            $results['message'] = '[address]不能为空!';
        }
        if($results){
            jsonReturn($results);
        }
    }
    static private function _getBuyerValue($BuyerApply){
        $BuyerCodeApplyInfo['corpSerialNo'] = $BuyerApply['buyer_no'];
        //企业内部买方代码--(必填)
        $BuyerCodeApplyInfo['clientNo'] = '';
        //被保险人信保通编号(非必填)
        $BuyerCodeApplyInfo['policyNo'] = self::$policyNo;
        //保险单号  --动态配置项-SCH017067-161600
        $BuyerCodeApplyInfo['countryCode'] = $BuyerApply['country_code'];
        //买方国家代码--(必填)
        $BuyerCodeApplyInfo['applyTime'] =  strtotime('now');
        //申请时间--(必填)
        if($BuyerApply['lang'] == 'zh') {
            //-----------国内买家必填项:
            $BuyerCodeApplyInfo['chnName'] = $BuyerApply['name'];
            //买方中文名称(必填)  --国内买方中文名称必填
            $BuyerCodeApplyInfo['areano'] = intval($BuyerApply['area_no']);
            //区域代码--(必填)    --国内买家 必填
            $BuyerCodeApplyInfo['chnAddress'] = $BuyerApply['registered_in'];
            //买方中文地址--(必填)--国内买家 必填
            $BuyerCodeApplyInfo['creditno'] = $BuyerApply['social_credit_code'];
            //统一社会信用代码--(必填)--国内买家 必填
        } else {
            //-----------国外买家必填项:
            $BuyerCodeApplyInfo['engName'] = $BuyerApply['name'];
            //买方英文名称(必填) --国外买家英文名称必填
            $BuyerCodeApplyInfo['engAddress'] = $BuyerApply['registered_in'];
            //买方英文地址(必填) --国外买家 英文地址必填
        }
        if(isset($BuyerApply['reg_address'])) {
            $BuyerCodeApplyInfo['regAddress'] = $BuyerApply['reg_address'];  //注册地址
        }
        if(isset($BuyerApply['registered_no'])) {
            $BuyerCodeApplyInfo['regNo'] = $BuyerApply['registered_no'];  //买方注册号
        }
        if(isset($BuyerApply['tel'])) {
            $BuyerCodeApplyInfo['tel'] = $BuyerApply['tel_code'].$BuyerApply['tel'];  //买方电话
        }
        if(isset($BuyerApply['fax'])) {
            $BuyerCodeApplyInfo['fax'] = $BuyerApply['fax_code'].$BuyerApply['fax'];  //买方传真
        }
        if(isset($BuyerApply['official_website'])) {
            $BuyerCodeApplyInfo['webAddress'] = $BuyerApply['official_website'];  //网站地址
        }
        if(isset($BuyerApply['official_email'])) {
            $BuyerCodeApplyInfo['eMail'] = $BuyerApply['official_email'];  //电子邮件
        }
        if(isset($BuyerApply['establish_data'])) {
            $BuyerCodeApplyInfo['setDate'] = intval(date('Y',strtotime($BuyerApply['establish_data'])));  //成立日期
        }
        if(isset($BuyerApply['reg_date'])) {
            $BuyerCodeApplyInfo['regyear'] = intval($BuyerApply['reg_date']);  //注册年份
        }
        if(isset($BuyerApply['legal_person_name'])) {
            $BuyerCodeApplyInfo['corporation'] = $BuyerApply['legal_person_name'];  //法人代表
        }
        if(isset($BuyerApply['equitiy'])) {
            $BuyerCodeApplyInfo['equity'] = number_format($BuyerApply['equitiy'],2,".","");  //资产净值
        }
        if(isset($BuyerApply['turnover'])) {
            $BuyerCodeApplyInfo['yearSale'] = number_format($BuyerApply['turnover'],2,".","");  //年销售额
        }
        return $BuyerCodeApplyInfo;
    }

    static private function _EdiBuyerCodeApply($BuyerCodeApplyInfo){
        $data = array('buyerCodeApplyInfoList' => array('BuyerCodeApplyInfo' => array($BuyerCodeApplyInfo)));
        try{
            self::$client = new SoapClient(self::$serviceUri);
            $response = self::$client->doEdiBuyerCodeApply($data);
//            }
            self::saveinfo($BuyerCodeApplyInfo,'BuyerCodeApply');
            $results['code'] = 1;
            return $results;
        } catch (Exception $e) {
            self::exception($e,$e->getMessage());
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            //jsonReturn($e->getMessage());
            return false;
        }

    }
    /**
     * 买家代码申请测试
     */
    protected function doEdiBuyerCodeApplyAction(){

        $BuyerCodeApplyInfo['corpSerialNo'] = '1';       //企业内部买方代码--
        $BuyerCodeApplyInfo['clientNo'] = '1';       //被保险人信保通编号
        $BuyerCodeApplyInfo['policyNo'] = 'SCH043954-181800';  //保险单号  --动态配置项
        $BuyerCodeApplyInfo['countryCode'] = 'USA';    //买方国家代码--
        $BuyerCodeApplyInfo['applyTime'] = strtotime('now'); //申请时间--

        $BuyerCodeApplyInfo['engName'] = 'Toyota Motor Sales, U.S.A., Inc';   //买方英文名称 --国外买家英文名称必填
        $BuyerCodeApplyInfo['engAddress'] = 'USA';     //买方英文地址--    ---国外买家 英文地址必填

        $BuyerCodeApplyInfo['chnName'] = 'Toyota Motor Sales, U.S.A., Inc';   //买方中文名称 --国内买方中文名称必填
        $BuyerCodeApplyInfo['areano'] = 'USA';     //区域代码--    ---国内买家 必填
        $BuyerCodeApplyInfo['chnAddress'] = 'USA';     //买方中文地址--    ---国内买家 必填

        $data = array('buyerCodeApplyInfoList' => array('BuyerCodeApplyInfo' => array($BuyerCodeApplyInfo)));
         //$this->resultInfo("doEdiBuyerCodeApply", $xmlBuyerCodeApplyInfo);
        try{
            $response = self::$client->doEdiBuyerCodeApply($data);
            if (is_object($response)) {
               return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->exception($e);
        }

    }


    /**
     * 银行代码申请
     */
    static public function EdiBankCodeApply($BankApply){
        self::checkParamBank($BankApply);
        $BuyerBankApply = self::_getBankValue($BankApply);
        $result = self::_EdiBankCodeApply($BuyerBankApply);
        if($result['code']  == 1){
            $res['code'] = 1;
        } else{
            $res['code'] = -101;
        }
        return $res;
    }

    static public function checkParamBank(&$BuyerBankApply){
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
    static private function _getBankValue($BankCodeApply){
        $BankCodeApplyInfo['corpSerialNo'] = $BankCodeApply['buyer_no'];
        //企业内部银行代码--(必填)
        $BankCodeApplyInfo['policyNo'] = self::$policyNo;
        //保险单号(非必填)
        $BankCodeApplyInfo['engName'] =  $BankCodeApply['bank_name'];
        //银行英文名称--(必填)
        $BankCodeApplyInfo['countryCode'] = $BankCodeApply['bank_country_code'];
        //银行国家代码--(必填)
        $BankCodeApplyInfo['address'] = $BankCodeApply['bank_address'];
        //银行地址(英文)--(必填)
        $BankCodeApplyInfo['bankswift'] = $BankCodeApply['bank_swift'];
        //企业填写的开证行swift--(非必填)
        if(isset($BankCodeApply['bank_name_zh'])) {
            $BankCodeApplyInfo['chnName'] = $BankCodeApply['bank_name_zh'];  //银行中文名称
        }
        if(isset($BankCodeApply['bank_zipcode'])) {
            $BankCodeApplyInfo['zip'] = $BankCodeApply['bank_zipcode'];  //邮政编码
        }
        if(isset($BankCodeApply['tel_bank'])) {
            $BankCodeApplyInfo['tel'] = $BankCodeApply['tel_code_bank'].$BankCodeApply['tel_bank'];  //银行电话
        }
        if(isset($BankCodeApply['fax_bank'])) {
            $BankCodeApplyInfo['fax'] = $BankCodeApply['fax_code_bank'].$BankCodeApply['fax_bank'];  //银行传真
        }
        if(isset($BankCodeApply['bank_website'])) {
            $BankCodeApplyInfo['webAddress'] = $BankCodeApply['bank_website'];  //网站地址
        }
        if(isset($BankCodeApply['bank_reg_date'])) {
            $BankCodeApplyInfo['setupDate'] = intval(date('Y',strtotime($BankCodeApply['bank_reg_date'])));  //成立日期
        }
        if(isset($BankCodeApply['legal_person_bank'])) {
            $BankCodeApplyInfo['corporation'] = $BankCodeApply['legal_person_bank'];  //法人代表
        }
        if(isset($BankCodeApply['bank_turnover'])) {
            $BankCodeApplyInfo['turnover'] = number_format($BankCodeApply['bank_turnover'],2,".","");  //营业额
        }
        if(isset($BankCodeApply['profit'])) {
            $BankCodeApplyInfo['profit'] = number_format($BankCodeApply['profit'],2,".","");  //利润
        }
        if(isset($BankCodeApply['total_assets'])) {
            $BankCodeApplyInfo['totalAsset'] = number_format($BankCodeApply['total_assets'],2,".","");;  //总资产
        }
        if(isset($BankCodeApply['equity_capital'])) {
            $BankCodeApplyInfo['selfCapital'] = number_format($BankCodeApply['equity_capital'],2,".","");;  //自有资本
        }
        if(isset($BankCodeApply['equity_ratio'])) {
            $BankCodeApplyInfo['selfAssetRate'] = number_format($BankCodeApply['equity_ratio'],3,".","");;  //自有资产比率
        }
        if(isset($BankCodeApply['branch_count'])) {
            $BankCodeApplyInfo['nodeSum'] = intval($BankCodeApply['branch_count']);  //分支数目
        }
        if(isset($BankCodeApply['employee_count'])) {
            $BankCodeApplyInfo['employeeSum'] = intval($BankCodeApply['employee_count']);  //员工人数
        }
        if(isset($BankCodeApply['bank_group_name'])) {
            $BankCodeApplyInfo['belongBankName'] = $BankCodeApply['bank_group_name'];  //所属银行集团名称
        }
        if(isset($BankCodeApply['bank_group_swift'])) {
            $BankCodeApplyInfo['belongBankSwift'] = $BankCodeApply['bank_group_swift'];  //所属银行集团swift
        }
        if(isset($BankCodeApply['cn_ranking'])) {
            $BankCodeApplyInfo['interOrder'] = intval($BankCodeApply['cn_ranking']);  //国内排名
        }
        if(isset($BankCodeApply['intl_ranking'])) {
            $BankCodeApplyInfo['nationOrder'] = intval($BankCodeApply['intl_ranking']);  //国际排名
        }
        if(isset($BankCodeApply['stockholder'])) {
            $BankCodeApplyInfo['stockHolder'] = $BankCodeApply['stockholder'];  //股东
        }

        return $BankCodeApplyInfo;
    }

    static private function _EdiBankCodeApply($BankCodeApplyInfo){
        $data = array('bankCodeApplyInfoList' => array('BankCodeApplyInfo' => array($BankCodeApplyInfo)));
        try{
            self::$client = new SoapClient(self::$serviceUri);
            $response = self::$client->doEdiBankCodeApply($data);
//            if (is_object($response)) {
//                $results['code'] = 1;
//            } else {
//                $results['code'] = -101;
//            }
            self::saveinfo($BankCodeApplyInfo,'BankCodeApply');
            $results['code'] = 1;
            return $results;
        } catch (Exception $e) {
            self::exception($e,$e->getMessage());
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return false;
        }
    }

    /**
     * 银行代码申请
     */
    protected function doEdiBankCodeApplyAction(){
      //  $BuyerModel = new BuyerModel();
       // $BuyerInfo = $BuyerModel->getBuyerInfo($this->params);
        $BankCodeApplyInfo = [
            "serial_no" => 'jd0017',    ///流水号
            "bank_address" => 'qq',        //银行地址-英文
            "bank_name" => 'qq',        //银行英文名称
            "Bank_country_code" => 'ARG',    //银行国家代码
        ];
        $BankCodeApplyInfo['corpSerialNo'] = '111';   //企业内部银行代码--
        $BankCodeApplyInfo['policyNo'] = '';   //保险单号
        $BankCodeApplyInfo['engName'] = '222';   //银行英文名称--
        $BankCodeApplyInfo['countryCode'] = '333';   //银行国家代码--
        $BankCodeApplyInfo['address'] = '444';   //银行地址(英文)--
        $data = array('bankCodeApplyInfoList' => array('BankCodeApplyInfo' => array($BankCodeApplyInfo)));
//        return $this->resultInfo("doEdiBankCodeApply", $xmlEdiBankCodeApply);
        try{
            $response = $this->client->doEdiBankCodeApply($data);
            if (is_object($response)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * 获取买家代码申请反馈
     */
    protected function doEdiBuyerCodeApproveAction(){
        //return $this->resultInfo("doEdiBuyerCodeApprove", $xmlBuyerCodeApprove);
        $time['startDate'] = self::getStartDate();
        $time['endDate'] = self::getEndDate();//var_dump($time);die;
        try{
            $time = array('startDate'=>date('Y-m-d\T14:00:00', time()),'endDate'=>date('Y-m-d\T23:00:00', time()));
            $client = new SoapClient(self::$serviceUri);
            $buyerCodeApproveInfo = $client->doEdiBuyerCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));

            if ($buyerCodeApproveInfo) {
                var_dump($buyerCodeApproveInfo);die;
            } else {
                echo 456;
            }
        }catch (Exception $e){
            $this->exception($e);
            jsonReturn($e->getMessage());
        }
    }

    /**
     * 银行代码批复通知
     *
     */
    protected  function doEdiBankCodeApproveAction(){
//        return $this->resultInfo("doEdiBankCodeApprove", $xmlEdiBankCodeApprove);
        try{
            $time = array('doEdiBankCodeApprove'=>array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));
            self::$client = new SoapClient(self::$serviceUri);
            $BankCodeApproveInfo = self::$client->doEdiBankCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));
            if ($BankCodeApproveInfo) {
//                $BankCodeApproveInfo = self::object_array($BankCodeApproveInfo);
//                //存储结果日志
//                $path = MYPATH.'/logs/';
//                $time = date('Y-m-d h:i:s',time());
//                $file = $path."/".$time."_edi.txt";
////                $fp = fopen($file,"a+");
//                $start="time:".$time."\r\n"."edi/buyerBankCodeApprove:"."\r\n"."---------- content start ----------"."\r\n";
//                $end ="\r\n"."---------- content end ----------"."\r\n\n";
//                $content=$start."".$BankCodeApproveInfo."".$end;
//                file_put_contents($file,$content);
////                fwrite($fp,$content);
////                fclose($fp);

                var_dump($BankCodeApproveInfo);die;
                //var_dump($BankCodeApproveInfo->BankInfo);
            } else {
                echo 123231;
            }
        } catch (Exception $e) {
            $this->exception($e);
            jsonReturn($e->getMessage());
        }
    }

    /**
     * 出口险-非LC限额申请
     */
    protected function doEdiNoLcQuotaApplyV2Action(){
        $xmlEdiNoLcQuotaApplyV2 =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("doEdiNoLcQuotaApplyV2", $xmlEdiNoLcQuotaApplyV2);
    }

    /**
     * 出口险-LC限额申请
     */
    protected function doEdiLcQuotaApplyV2Action(){
        $xmlEdiLcQuotaApplyV2 =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("doEdiLcQuotaApplyV2", $xmlEdiLcQuotaApplyV2);
    }

    /**
     * 出口险-限额批复通知
     *
     */
    protected function getEdiQuotaApproveInfoAction(){
//        return $this->resultInfo("getEdiQuotaApproveInfo", $xmlGetEdiQuotaApproveInfo);
        try{
            $QuotaApproveInfo = $this->client->getEdiQuotaApproveInfo();
            if ($QuotaApproveInfo) {
                var_dump($QuotaApproveInfo->BuyerQuotaInfo);
            } else {
                echo 333;
            }
        } catch (Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * 出口险-出运明细申报
     */
    protected function doEdiShipmentApplyAction(){
        $xmlEdiShipmentApply =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("doEdiShipmentApply", $xmlEdiShipmentApply);
    }

    /**
     * 出口险-出运申报受理反馈
     *
     */
    protected function getEdiShipmentApproveInfoAction(){
        $startDate = '2017-07-19 00:00:00';
        $endDate = '2017-07-20 23:59:59';

        $xmlGetEdiShipmentApproveInfo =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("getEdiShipmentApproveInfo", $xmlGetEdiShipmentApproveInfo);
    }

    /**
     * 出口险-出运变更申请
     */
    protected function doEdiShipmentAlterApplyAction(){
        $xmlEdiShipmentAlterApply =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("doEdiShipmentAlterApply", $xmlEdiShipmentAlterApply);
    }

    /**
     * 出口险-出运变更受理反馈
     *
     */
    protected function getEdiShipmentAlterApproveInfoAction(){
        $startDate = '2017-07-19 00:00:00';
        $endDate = '2017-07-20 23:59:59';

        $xmlGetEdiShipmentAlterApproveInfo =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("getEdiShipmentAlterApproveInfo", $xmlGetEdiShipmentAlterApproveInfo);
    }

    /**
     * 出口险-收汇确认
     */
    protected function doEdiReceiptApplyAction(){
        $xmlEdiReceiptApply =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("doEdiReceiptApply", $xmlEdiReceiptApply);
    }

    /**
     * 出口险-收汇确认反馈
     *
     */
    protected function getEdiReceiptApproveInfoAction(){
        $startDate = '2017-07-19 00:00:00';
        $endDate = '2017-07-20 23:59:59';

        $xmlGetEdiReceiptApproveInfo =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("getEdiReceiptApproveInfo", $xmlGetEdiReceiptApproveInfo);
    }

    /**
     * 内贸险-限额申请
     */
    protected function doEdiDomQuotaApplyAction(){
        $xmlEdiDomQuotaApply =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("doEdiDomQuotaApply", $xmlEdiDomQuotaApply);
    }

    /**
     * 内贸险-限额批复
     *
     */
    protected function getDomEdiQuotaApproveInfoAction(){

        $startDate = '2017-07-19 00:00:00';
        $endDate = '2017-07-20 23:59:59';

        $xmlGetDomEdiQuotaApproveInfo =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("getDomEdiQuotaApproveInfo", $xmlGetDomEdiQuotaApproveInfo);
    }

    /**
     * 出口险-限额余额查询V2(新版)
     *
     */
    protected function getQuotaBalanceInfoByPolicyNoAction(){
//        return $this->resultInfo("getQuotaBalanceInfoByPolicyNo",$xmlGetQuotaBalanceInfoByPolicyNo);
        try{
            $QuotaBalanceInfo = $this->client->getQuotaBalanceInfoByPolicyNo(array('doEdiBuyerCodeApprove'=>array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate())));
            if ($QuotaBalanceInfo) {
                var_dump($QuotaBalanceInfo);
            } else {
                echo 555;
            }
        } catch (Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * 出口险-自行掌握限额判断
     *
     */
    protected function EdiCheckAutoQuotaAction(){
        $xmlEdiCheckAutoQuota =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("EdiCheckAutoQuota", $xmlEdiCheckAutoQuota);
    }

    /**
     * 国家分类查询
     *
     */
    protected function getEdiCountryClassifyAction(){
//            $return = $this->resultInfo("getEdiCountryClassify");
        $time['startDate'] = self::getStartDate();
        $time['endDate'] = self::getEndDate();
       // 2011-01-01     2017-01-01
        try{
            $CountryClassify = $this->client->getEdiCountryClassify(array('startDate'=>'2011-01-01T00:00:00','endDate'=>self::getEndDate()));
            if ($CountryClassify) {
                var_dump($CountryClassify->out->CountryClassify);
            } else {
                echo 666;
            }
        } catch (Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * 出口险-基础费率查询
     *
     */
    protected function getEdiBasicFeeRateAction(){
//        return $this->resultInfo("getEdiBasicFeeRate", $xmlGetEdiBasicFeeRate);
        $rate['policyNo'] = '';
        $rate['startDate'] = self::getStartDate();
        $rate['endDate'] = self::getEndDate();
        try{
            $BasicFeeRateInfo = $this->client->getEdiBasicFeeRate(array('getEdiBasicFeeRate' => array($rate)));
            if ($BasicFeeRateInfo) {
                var_dump($BasicFeeRateInfo);
                //var_dump($BasicFeeRateInfo->BasicFeeRate);
            } else {
                echo 777;
            }
        } catch (Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * 出口险-特殊费率查询
     *
     */
    protected function getEdiSpecialFeeRateAction(){

//        return $this->resultInfo("getEdiSpecialFeeRate", $xmlGetEdiSpecialFeeRate);
        try{
            $result = $this->client->getEdiSpecialFeeRate;
            var_dump($result);die;//组织处理数据
        }catch (Exception $e){
            $this->exception($e);
        }
    }


    static function xml_to_array($xml){
        $array = (array)(@simplexml_load_string($xml, null, LIBXML_NOCDATA));
        foreach ($array as $key=>$item){
            $array[$key]  =  @self::struct_to_array((array)$item);
        }
        return $array;
    }

    static function struct_to_array($item) {
        if(!is_string($item)) {
            $item = (array)$item;
            foreach ($item as $key=>$val){
                $item[$key]  =  @self::struct_to_array($val);
            }
        }
        return $item;
    }
    //公共调用返回结果
     private function resultInfo($calltype='', $xml){
        if(empty($calltype)){
            return false;
        }
         try {
            $client = new SoapClient($this->serviceUri);
            $paramters = array('xml'=>$xml);
             $result=call_user_func_array($calltype, $paramters);var_dump($result);die;
//            $result=$client->doEdiBuyerCodeApprove($paramters);
//            return $result? $result : '';
        } catch (Exception $e) {
             LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
             LOG::write($e->getMessage(), LOG::ERR);
             $this->setCode(-2003);
             $this->setMessage($e);
             $this->jsonReturn();
        }
    }
    public function testCountryClassifyAction(){
            $client = new SoapClient("http://172.18.20.74:8081/ediserver/ws_services/SolEdiProxyWebService?wsdl");
            $result=$client->getEdiCountryClassify([]);
            //var_dump(get_object_vars($result));
            var_dump($result);

    }


    //json传过来的数组并不是标准的array是stdClass类型,转为数组方式一:
    static function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = @self::object_array($value);
            }
        }
        return $array;
    }

    public static function saveinfo($data,$name)
    {
        //存储结果日志
        $time = date('Y-m-d h:i:s',time());
        $start="time:".$time."\r\n"."Edi/buyerCodeApprove:"."\r\n"."---------- content start ----------"."\r\n";
        $end ="\r\n"."---------- content end ----------"."\r\n\n";
        $content=$start."【".$name."】:".$data['corpSerialNo']."".$end;
        LOG::write($content, LOG::INFO);
    }

    static public function exception($e){
        LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::ERR);
        LOG::write($e->getMessage, LOG::ERR);
//        $this->setCode($e->getCode);
//        $this->setMessage($e);
//        $this->jsonReturn();
    }
}
