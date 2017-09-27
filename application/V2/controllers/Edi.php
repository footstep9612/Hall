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

    private $params = array();

    private $serverIP = '172.18.20.74';

    private $serverPort = '8081';

    private $serverDir = 'ediserver';

    private $serverDirSec = 'ws_services';

    private $serviceUri = '';

    private $serviceInterface = 'SolEdiProxyWebService';

    private $mode = 'wsdl';

    private $client;

    public function init(){
        error_reporting(E_ALL & ~E_NOTICE);
        $this->params = json_decode(file_get_contents("php://input"), true);
        if (count($this->params) > 0) {
            foreach ($this->params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
        if ($this->serviceUri == '') {
//            $this->serverDir = '/' . pathinfo(dirname($_SERVER['SCRIPT_NAME']), PATHINFO_FILENAME) . '/';
            $this->serviceUri = 'http://'.$this->serverIP.':'.$this->serverPort.'/'.$this->serverDir.'/'.$this->serverDirSec.'/'.$this->serviceInterface;
        }
        if ($this->mode == 'wsdl') {
            $this->serviceUri .= '?wsdl';
        }
        $this->client = new SoapClient( $this->serviceUri);

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
            $client = new SoapClient("http://localhost:81/ediserver/ws_services/SolEdiProxyWebService?wsdl");
            echo '<pre>';
            print_r($client->__getFunctions());
            print_r($client->__getTypes());
        } catch (SOAPFault $e) {
            print $e;
        }
    }

    protected function testEdiBuyerCodeAction(){
        $buyerCodeApplyInfo['corpSerialNo'] = '1';
        $buyerCodeApplyInfo['corpSerialNo'] = '1';
        $buyerCodeApplyInfo['policyNo'] = 'SCH017067-161600';
        $buyerCodeApplyInfo['engName'] = 'Toyota Motor Sales, U.S.A., Inc';
        $buyerCodeApplyInfo['countryCode'] = 'USA';
        $buyerCodeApplyInfo['engAddress'] = 'USA';
        $buyerCodeApplyInfo['applyTime'] = strtotime('now');

        $data = array('buyerCodeApplyInfoList' => array('BuyerCodeApplyInfo' => array($buyerCodeApplyInfo)));
        try {
            $response = $this->client->doEdiBuyerCodeApply($data);
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
     */
    protected function doEdiBuyerCodeApplyAction(){

//        $BuyerModel = new BuyerModel();
//        $BuyerInfo = $BuyerModel->getBuyerInfo($this->params);

        $BuyerCodeApplyInfo['corpSerialNo'] = '1';       //企业内部买方代码--
        $BuyerCodeApplyInfo['clientNo'] = '1';       //被保险人信保通编号
        $BuyerCodeApplyInfo['policyNo'] = 'SCH017067-161600';  //保险单号  --动态配置项
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
            $response = $this->client->doEdiBuyerCodeApply($data);
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
            $buyerCodeApproveInfo = $this->client->doEdiBuyerCodeApprove(array('doEdiBuyerCodeApprove'=>array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate())));
            if ($buyerCodeApproveInfo) {
                var_dump($buyerCodeApproveInfo);
            } else {
                echo 456;
            }
        }catch (Exception $e){
            $this->exception($e);
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
     * 银行代码批复通知
     *
     */
    protected  function doEdiBankCodeApproveAction(){
//        return $this->resultInfo("doEdiBankCodeApprove", $xmlEdiBankCodeApprove);
        try{
            $BankCodeApproveInfo = $this->client->doEdiBankCodeApprove(array('doEdiBankCodeApprove'=>array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate())));
            if ($BankCodeApproveInfo) {
                var_dump($BankCodeApproveInfo->BankInfo);
            } else {
                echo 123231;
            }
        } catch (Exception $e) {
            $this->exception($e);
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

    public function exception($e){
        LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::ERR);
        LOG::write($e->getMessage, LOG::ERR);
        $this->setCode($e->getCode);
        $this->setMessage($e);
        $this->jsonReturn();
    }
}
