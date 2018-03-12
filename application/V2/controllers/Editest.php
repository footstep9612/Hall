<?php

/**
* 更新全部商品申报属性
* 运行cmd 进入本文件所在目录
* 执行方式 D:\Software\phpStudy\php\php-7.0.12-nts\php.exe update_ex_hs_attr.php
* 每种语言生成一个SQL文件
**/
class Editest{
	
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
    private $ediPolicyNo = 'SCH043954-181800';

    static private $url_wsdl = 'localhost:8121/edi/ws_services/SolEdiShorttermWebService?wsdl';


    static public function getByuerApproved() {
        try{
            $client = new SoapClient(self::$url_wsdl);
            $response = $client->doEdiBuyerCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));
            $buyerCodeApproveInfo = $response->out->BuyerCodeApproveInfo;
            if ($buyerCodeApproveInfo) {

                //存储结果日志
                $path = "";
                $time = date('Y-m-d h:i:s',time());
                $file = $path."/".$time."_edi.txt";
                $fp = fopen($file,"a+");
                $start="time:".$time."\r\n"."edi/buyerCodeApprove:"."\r\n"."---------- content start ----------"."\r\n";
                $end ="\r\n"."---------- content end ----------"."\r\n\n";
                $content=$start."".$buyerCodeApproveInfo."".$end;
                fwrite($fp,$content);
                fclose($fp);

                $conn = mysqli_connect(self::$host,self::$user,self::$pass,self::$dbname) or die('connect fail');
                $conn->query("set names 'utf8';");

                $updata =  self::object_array($buyerCodeApproveInfo);
                foreach($updata as $item){
                    //1.corpSerialNo中信保买家代码(buyer_no),查询是否为空,为空第一次申请的反馈,查看反馈approveFlag,为1保存记录;为0保存记录并提示申请失败.||不为空,查看表approveFlag状态值.,为1,表示通过,不处理continue;为0更新记录(),
                    // 2.保存判断buyerInfo是否为空,不通过的保存;不为空,通过的保存
                    $data = [
                        'approveFlag'=> $item['approveFlag'],//审批标志 1通过  0-申请退回/不通过
                        'remarks'=> $item['unAcceptReason'],//申请退回/不通过原因
                        'noticeSerialNo'=> $item['noticeSerialNo'],//信保通知序号（唯一）
                        'corpSerialNo'=> $item['corpSerialNo'],//中信保买家代码
                        'notifyTime'=> date('Y-m-d H:i:s', strtotime($item['notifyTime'])),//最新通知时间
                        'buyer_no'=> $item['buyerInfo']['corpSerialNo'],
                        'buyerNo'=> $item['buyerInfo']['buyerNo'], //中信保买家代码
                        'clientNo'=> $item['buyerInfo']['clientNo'], //企业标识
                        'engAddress'=> $item['buyerInfo']['engAddress'], //企业英文地址
                        'engName'=> $item['buyerInfo']['engName'], //企业英文名称
                    ];
                    if($item['approveFlag'] == 1){
                        //先查看是否已经审核通过
                        $check = $conn->query("select status from buyer_credit.buyer_credit where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                        if($check['status'] !== 'EDI_APPROVED'){
                            $conn->query("update buyer_credit.buyer_credit set sinosure_no=".$item['buyerInfo']['buyerNo'].",approved_date=".date('Y-m-d H:i:s', strtotime($item['notifyTime']))." where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                            $conn->query("insert into buyer_credit.buyer_credit_log(buyer_no,sign,checked_by,checked_at,out_status) values(".$item['buyerInfo']['corpSerialNo'].",1,'edi',".date('Y-m-d H:i:s', time()).",'EDI_APPROVED')");
                        }
                     } else {
                        $conn->query("update buyer_credit.buyer_credit set status='ERUI_REJECTED' where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                        $conn->query("insert into buyer_credit.buyer_credit_log(buyer_no,sign,checked_by,checked_at,out_status,out_remarks) values(".$item['buyerInfo']['corpSerialNo'].",1,'edi',".date('Y-m-d H:i:s', time()).",'ERUI_REJECTED',".$item['unAcceptReason'].")");
                    }

                //              date('Y-m-d H:i:s', strtotime('2011-04-01T00:00:00+08:00'));
            }

        }
            } catch (Exception $e) {
        LOG::write('CLASS:' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
        LOG::write($msg, LOG::ERR);
        }
    }
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
