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
 *        [2015-05-04T00:00:00,2015-05-05T23:59:59]
 * */

class Edi {

    private $params = array();
    private $serviceUri = '';
    private $client;

    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = new Yaf_Config_Ini('./conf/application.ini', 'sinosure');
//        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->sinosure->config->toArray();
        $this->serverIP = $config_db['host'];
        $this->serverPort = $config_db['port'];
        $this->serverDir = $config_db['path'];
        $this->serviceInterface = $config_db['interface'];
        $this->mode = $config_db['mode'];

        error_reporting(E_ALL & ~E_NOTICE);

        $this->params = json_decode(file_get_contents("php://input"), true);

        if (count($this->params) > 0) {
            foreach ($this->params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
        if ('' == $this->serviceUri) {
            $this->serviceUri = 'http://'.$this->serverIP.':'.$this->serverPort.'/'.$this->serverDir.'/'.$this->serviceInterface;
        }
        if ('wsdl' == $this->mode) {
            $this->serviceUri .= '?wsdl';
        }
        $this->client = new SoapClient($this->serviceUri);
    }


    static public function getStartDate(){
        return $startDate = date('Y-m-d\T00:00:00', mktime('-1'));
    }

    static public function getEndDate(){
        return $endDate =  date('Y-m-d\T23:59:59', time());
    }

    public function test(){
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

    protected function testEdiBuyerCode(){
        $buyerCodeApplyInfo['corpSerialNo'] = '1';
        $buyerCodeApplyInfo['corpSerialNo'] = '1';
        $buyerCodeApplyInfo['policyNo'] = 'SCH017067-161600';
        $buyerCodeApplyInfo['engName'] = 'Toyota Motor Sales, U.S.A., Inc';
        $buyerCodeApplyInfo['countryCode'] = 'USA';
        $buyerCodeApplyInfo['engAddress'] = 'USA';
        $buyerCodeApplyInfo['applyTime'] =  strtotime('now');

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
     *
     */
    public function EdiBuyerCodeApply($BuyerCodeApply){
        $this->checkParamBuyer($BuyerCodeApply);
        $result = $this->_EdiBuyerCodeApply($BuyerCodeApply);
        if($result && $result['code']  == 1){
            $res['code'] = 1;
            $res['message'] = '申请成功!';
        } else{
            $res['code'] = -101;
            $res['message'] = '申请失败!';
        }
        return $res;
    }

    public function checkParamBuyer(&$BuyerCodeApply){
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
        if(!isset($BuyerCodeApply['address'])){
            $results['code'] = -101;
            $results['message'] = '[address]不能为空!';
        }
        if($results){
            jsonReturn($results);
        }
    }

    private function _EdiBuyerCodeApply($BuyerCodeApply){
        $BuyerCodeApplyInfo['corpSerialNo'] = $BuyerCodeApply['buyer_no'];
        //企业内部买方代码--(必填)
        $BuyerCodeApplyInfo['clientNo'] = '';
        //被保险人信保通编号(非必填)
        $BuyerCodeApplyInfo['policyNo'] = '';
        //保险单号  --动态配置项-SCH017067-161600
        $BuyerCodeApplyInfo['countryCode'] = $BuyerCodeApply['country_code'];
        //买方国家代码--(必填)
        $BuyerCodeApplyInfo['applyTime'] =  strtotime('now');
        //申请时间--(必填)
        if($BuyerCodeApply['lang'] == 'zh') {
            //-----------国内买家必填项:
            $BuyerCodeApplyInfo['chnName'] = $BuyerCodeApply['name'];
            //买方中文名称(必填)  --国内买方中文名称必填
            $BuyerCodeApplyInfo['areano'] = intval($BuyerCodeApply['area_no']);
            //区域代码--(必填)    --国内买家 必填
            $BuyerCodeApplyInfo['chnAddress'] = $BuyerCodeApply['address'];
            //买方中文地址--(必填)--国内买家 必填
        } else {
            //-----------国外买家必填项:
            $BuyerCodeApplyInfo['engName'] = $BuyerCodeApply['name'];
            //买方英文名称(必填) --国外买家英文名称必填
            $BuyerCodeApplyInfo['engAddress'] = $BuyerCodeApply['address'];
            //买方英文地址(必填) --国外买家 英文地址必填
        }

        $data = array('buyerCodeApplyInfoList' => array('BuyerCodeApplyInfo' => array($BuyerCodeApplyInfo)));
        try{
            $response = $this->client->doEdiBuyerCodeApply($data);
            if (is_object($response)) {
                $results['code'] = 1;
            } else {
                $results['code'] = -101;
            }
            return $results;
        } catch (Exception $e) {
            $this->exception($e);
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return $results;
        }

    }

    /**
     * 获取买家代码申请反馈
     */
    public function EdiBuyerCodeApprove()
    {
        $result = $this->_EdiBuyerCodeApprove();
        if($result && !isset($result['code'])){
            $data = self::object_array($result);
            var_dump($data);die;
            return $data;
        } else {
            return $result;
        }
    }
    private function _EdiBuyerCodeApprove(){
        try{

            $response = $this->client->doEdiBuyerCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));

            $buyerCodeApproveInfo = $response->out->BuyerCodeApproveInfo;
            if ($buyerCodeApproveInfo) {
                return $buyerCodeApproveInfo;
//                date('Y-m-d H:i:s', strtotime('2011-04-01T00:00:00+08:00'));
            } else{
                return false;
            }
        }catch (Exception $e){
            $this->exception($e);
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return $results;
        }
    }

    /**
     * 银行代码申请
     */
    public function EdiBankCodeApply($BuyerBankApply){
        $this->checkParamBank($BuyerBankApply);
        $result = $this->_EdiBankCodeApply($BuyerBankApply);
        if($result && $result['code']  == 1){
            $res['code'] = 1;
            $res['message'] = '申请成功!';
        } else{
            $res['code'] = -101;
            $res['message'] = '申请失败!';
        }
        return $res;
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

    private function _EdiBankCodeApply($BankCodeApply){

        $BankCodeApplyInfo['corpSerialNo'] = $BankCodeApply['buyer_no'];
        //企业内部银行代码--(必填)
        $BankCodeApplyInfo['policyNo'] = '';
        //保险单号(非必填)
        $BankCodeApplyInfo['engName'] =  $BankCodeApply['bank_name'];
        //银行英文名称--(必填)
        $BankCodeApplyInfo['countryCode'] = $BankCodeApply['bank_country_code'];
        //银行国家代码--(必填)
        $BankCodeApplyInfo['address'] = $BankCodeApply['bank_address'];
        //银行地址(英文)--(必填)
        $BankCodeApplyInfo['bankswift'] = $BankCodeApply['bank_swift'];
        //企业填写的开证行swift--(非必填)

        $data = array('bankCodeApplyInfoList' => array('BankCodeApplyInfo' => array($BankCodeApplyInfo)));
        try{
            $response = $this->client->doEdiBankCodeApply($data);
//           var_dump($response);die;
            if (is_object($response)) {
                $results['code'] = 1;
            } else {
                $results['code'] = -101;
            }
            return $results;
        } catch (Exception $e) {
            $this->exception($e);
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return $results;
        }
    }

    /**
     * 银行代码批复通知
     *
     */
    public function EdiBankCodeApprove(){
        $result = $this->_EdiBankCodeApprove();
        if($result && !isset($result['code'])){
            $data = self::object_array($result);
            var_dump($data);die;
            return $data;
        } else {
            return $result;
        }
    }
    private  function _EdiBankCodeApprove(){
        try{
            $response = $this->client->doEdiBankCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));

            $BankCodeApproveInfo = $response->out->BankCodeApproveInfo;
            if ($BankCodeApproveInfo) {
                return $BankCodeApproveInfo;
            } else{
                return false;
            }
        } catch (Exception $e) {
            $this->exception($e);
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return $results;
        }
    }

    /**
     * 出口险-非LC限额申请
     */
    public function EdiNoLcQuotaApplyV2(){
        return $this->resultInfo("doEdiNoLcQuotaApplyV2");
    }

    /**
     * 出口险-LC限额申请
     */
    public function EdiLcQuotaApplyV2(){
        return $this->resultInfo("doEdiLcQuotaApplyV2");
    }

    /**
     * 出口险-限额批复通知
     *
     */
    public function EdiQuotaApproveInfo(){
        $result = $this->_EdiQuotaApproveInfo();
        if($result && !isset($result['code'])){
            $data = self::object_array($result);
            var_dump($data);die;
            return $data;
        } else {
            return $result;
        }
    }

    private function _EdiQuotaApproveInfo(){
        try{
            $response = $this->client->getEdiQuotaApproveInfo(array('getEdiQuotaApproveInfo'=>array('policyNo'=>'','startDate'=>self::getStartDate(),'endDate'=>self::getEndDate())));
            var_dump($response);die;
            $QuotaApproveInfo = $response->out->QuotaApproveInfo;
            if (is_object($QuotaApproveInfo)) {
                return $QuotaApproveInfo;
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->exception($e);
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return $results;
        }
    }

    /**
     * 出口险-出运明细申报
     */
    public function EdiShipmentApply(){
        return $this->resultInfo("doEdiShipmentApply");
    }

    /**
     * 出口险-出运申报受理反馈
     *
     */
    public function EdiShipmentApproveInfo(){
        return $this->resultInfo("getEdiShipmentApproveInfo");
    }

    /**
     * 出口险-出运变更申请
     */
    public function doEdiShipmentAlterApply(){
        return $this->resultInfo("doEdiShipmentAlterApply");
    }

    /**
     * 出口险-出运变更受理反馈
     *
     */
    public function EdiShipmentAlterApproveInfo(){
        return $this->resultInfo("getEdiShipmentAlterApproveInfo");
    }

    /**
     * 出口险-收汇确认
     */
    public function EdiReceiptApply(){
        return $this->resultInfo("doEdiReceiptApply");
    }

    /**
     * 出口险-收汇确认反馈
     *
     */
    public function EdiReceiptApproveInfo(){
        return $this->resultInfo("getEdiReceiptApproveInfo");
    }

    /**
     * 内贸险-限额申请
     */
    public function EdiDomQuotaApply(){
        return $this->resultInfo("doEdiDomQuotaApply");
    }

    /**
     * 内贸险-限额批复
     *
     */
    public function DomEdiQuotaApproveInfo(){
        return $this->resultInfo("getDomEdiQuotaApproveInfo");
    }

    /**
     * 出口险-限额余额查询V2(新版)
     *
     */
    public function QuotaBalanceInfoByPolicyNo(){
        $result = $this->_QuotaBalanceInfoByPolicyNo();
        if($result && !isset($result['code'])){
            $data = self::object_array($result);
            var_dump($data);die;
            return $data;
        } else {
            return $result;
        }
    }

    private function _QuotaBalanceInfoByPolicyNo(){
//        return $this->resultInfo("getQuotaBalanceInfoByPolicyNo",$xmlGetQuotaBalanceInfoByPolicyNo);
//        policyNoList  保险单号集合(必填)
        try{
            $QuotaBalanceInfo = $this->client->getQuotaBalanceInfoByPolicyNo(array('policyNoList'=>array()));
//            var_dump($QuotaBalanceInfo);die;
            if (is_object($QuotaBalanceInfo->out) && !empty($QuotaBalanceInfo->out)) {
                return $QuotaBalanceInfo->out;
//                var_dump($QuotaBalanceInfo);
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->exception($e);
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return $results;
        }
    }

    /**
     * 出口险-自行掌握限额判断
     *
     */
    public function doEdiCheckAutoQuota(){
        $xmlEdiCheckAutoQuota =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>

            ";
        return $this->resultInfo("EdiCheckAutoQuota", $xmlEdiCheckAutoQuota);
    }

    /**
     * 国家分类查询
     *
     */
    public function EdiCountryClassify(){
        try{

            $CountryClassify = $this->client->getEdiCountryClassify(array('startDate'=>'2011-01-01T00:00:00','endDate'=>self::getEndDate()));
            if ($CountryClassify) {
                $data = @self::object_array($CountryClassify->out->CountryClassify);
                var_dump($data);die;
            } else {
                echo '数据为空!';
            }
        } catch (Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * 出口险-基础费率查询
     *
     */
    public function EdiBasicFeeRate(){
        try{
            $BasicFeeRateInfo = $this->client->getEdiBasicFeeRate();
            if ($BasicFeeRateInfo) {
                var_dump($BasicFeeRateInfo->BasicFeeRate);
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
    public function EdiSpecialFeeRate(){
        try{
            $result = $this->client->getEdiSpecialFeeRate;
            var_dump($result);die;//组织处理数据
        }catch (Exception $e){
            $this->exception($e);
        }
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
    //转为数组方式二:
    static function object2array_pre(&$object) {
        if (is_object($object)) {
            $arr = (array)($object);
        } else {
            $arr = &$object;
        }
        if (is_array($arr)) {
            foreach($arr as $varName => $varValue){
                $arr[$varName] = @self::object2array($varValue);
            }
        }
        return $arr;
    }
    static function object2array(&$object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }
    //xml转为数组方式:
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
    //公共调用返回结果 --暂不用
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
             return $e;
        }
    }

    public function exception($e){
        LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
        LOG::write($e->getMessage, LOG::ERR);
        return $e;
    }
}
