<?php

/**
* 更新全部商品申报属性
* 运行cmd 进入本文件所在目录
* 执行方式 D:\Software\phpStudy\php\php-7.0.12-nts\php.exe update_ex_hs_attr.php
* 每种语言生成一个SQL文件
**/
class EditestController extends PublicController {
	
	static private	$host = '172.18.18.193';
	static private	$user = 'root';
	static private	$pass = 'xkJfeRcyC57ade';
	static private	$dbname = 'buyer_credit';

	private $serverIP = '172.18.20.74';
    private $serverPort = '8081';
    private $serverDir = 'ediserver';
    private $serverDirSec = 'ws_services';
    private $serviceUri = '';
    private $serviceInterface = 'SolEdiProxyWebService';
    private $mode = 'wsdl';

    static private $url_wsdl = 'localhost:8121/edi/ws_services/SolEdiShorttermWebService?wsdl';

    /**
     * 国家分类查询
     *
     */
    public function getEdiCountryClassifyAction(){
//            $return = $this->resultInfo("getEdiCountryClassify");
        $time['startDate'] = self::getStartDate();
        $time['endDate'] = self::getEndDate();
        // 2011-01-01     2017-01-01
        try{
            $client = new SoapClient(self::$url_wsdl);
            $CountryClassify = $client->getEdiCountryClassify(array('startDate'=>'2011-01-01T00:00:00','endDate'=>self::getEndDate()));
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
     * 获取买家代码申请反馈
     */
    public function EdiBuyerCodeApprove()
    {

        $result = $this->_EdiBuyerCodeApprove();
        if($result && !isset($result['code'])){
//            var_dump($result);die;
            return $result;
        } else {
            return $result;
        }
    }
    static public function _EdiBuyerCodeApprove(){
        try{
            $client = new SoapClient(self::$url_wsdl);
            $response = $client->doEdiBuyerCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));

            $buyerCodeApproveInfo = $response->out->BuyerCodeApproveInfo;
            if ($buyerCodeApproveInfo) {
                return self::object_array($buyerCodeApproveInfo);
//                date('Y-m-d H:i:s', strtotime('2011-04-01T00:00:00+08:00'));
            } else{
                return false;
            }
        }catch (Exception $e){
            $this->exception($e,$e->getMessage());
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
//            var_dump($result);die;
            return $result;
        } else {
            return $result;
        }
    }
    private  function _EdiBankCodeApprove(){
        try{
            $client = new SoapClient(self::$url_wsdl);
            $response = $client->doEdiBankCodeApprove(array('startDate'=>$this->getStartDate(),'endDate'=>self::getEndDate()));

            $BankCodeApproveInfo = $response->out->BankCodeApproveInfo;
            if ($BankCodeApproveInfo) {
                return self::object_array($BankCodeApproveInfo);
            } else{
                return false;
            }
        } catch (Exception $e) {
             $this->exception($e,$e->getMessage());
            $results = [
                'code' => $e->getCode(),
                'msg'  => $e->getMessage()
            ];
            return $results;
        }
    }
    public function exception($e,$msg){
        LOG::write('CLASS:' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
        LOG::write($msg, LOG::ERR);
        return $e;
    }

    static public function getStartDate(){
        return $startDate = date('Y-m-d\T00:00:00', mktime('-1'));
    }

    static public function getEndDate(){
        return $endDate =  date('Y-m-d\T23:59:59', time());
    }
	
	//json传过来的数组并不是标准的array是stdClass类型,转为数组方式一:
    static public function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = @self::object_array($value);
            }
        }
        return $array;
    }


}
