<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:40
 */
class EdiCodeApproveController extends PublicController{

    public function init(){
        parent::init();
    }

    private $serverIP = '172.18.20.125';
    private $serverPort = '8081';
    private $serverDir = 'ediserver';
    private $serverDirSec = 'ws_services';
    private $serviceUri = '';
    private $serviceInterface = 'SolEdiProxyWebService';
    private $mode = 'wsdl';
    private $ediPolicyNo = 'SCH043954-181800';

    static private $url_wsdl = '172.18.20.125:8081/edi/ws_services/SolEdiShorttermWebService?wsdl';

    /**
     * 企业代码批复通知
     *
     */
    static public function getByuerApproved() {
        try{
            $client = new SoapClient(self::$url_wsdl);
            $response = $client->doEdiBuyerCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));
            $buyerCodeApproveInfo = $response->out->BuyerCodeApproveInfo;
            if ($buyerCodeApproveInfo) {

                //存储结果日志
//                $path = "";
//                $time = date('Y-m-d h:i:s',time());
//                $file = $path."/".$time."_edi.txt";
//                $fp = fopen($file,"a+");
//                $start="time:".$time."\r\n"."edi/buyerCodeApprove:"."\r\n"."---------- content start ----------"."\r\n";
//                $end ="\r\n"."---------- content end ----------"."\r\n\n";
//                $content=$start."".$buyerCodeApproveInfo."".$end;
//                fwrite($fp,$content);
//                fclose($fp);

                $conn = mysqli_connect(self::$host,self::$user,self::$pass,self::$dbname) or die('connect fail');
                $conn->query("set names 'utf8';");

                $updata =  self::object_array($buyerCodeApproveInfo);
                foreach($updata as $item){
                    if($item['approveFlag'] == 1){
                        //先查看是否已经审核通过
                        $check = $conn->query("select status from buyer_credit.buyer_bank_info where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                        if($check['status'] =='EDI_APPROVED'){
                            $conn->query("update buyer_credit.buyer_credit set sinosure_no=".$item['buyerInfo']['buyerNo'].",approved_date=".date('Y-m-d H:i:s', strtotime($item['notifyTime'])).",status='EDI_APPROVED' where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                        } else {
                            $conn->query("update buyer_credit.buyer_credit set sinosure_no=".$item['buyerInfo']['buyerNo'].",approved_date=".date('Y-m-d H:i:s', strtotime($item['notifyTime']))." where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                        }
                        $conn->query("update buyer_credit.buyer_reg_info set status='EDI_APPROVED' where buyer_no = ".$item['buyerInfo']['corpSerialNo']);

                        $conn->query("insert into buyer_credit.buyer_credit_log(buyer_no,`name`,address,sign,checked_by,checked_at,out_status) values(".$item['buyerInfo']['corpSerialNo'].",".$item['buyerInfo']['engName'].",".$item['buyerInfo']['engAddress'].",1,'edi',".date('Y-m-d H:i:s', strtotime($item['notifyTime'])).",'EDI_APPROVED')");
                    } elseif($item['approveFlag'] == 0) {

                        $pre_mc = preg_match('/(存在)*(已存在)*(已经存在)*(重复提交)*(重复申请)*/', $item['unAcceptReason']);
                        if($pre_mc == 0){
                            $conn->query("update buyer_credit.buyer_credit set status='ERUI_REJECTED',remarks=".$item['unAcceptReason']." where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                            //添加日志
                            $conn->query("insert into buyer_credit.buyer_credit_log(buyer_no,`name`,address,sign,checked_by,checked_at,out_status,out_remarks) values(".$item['buyerInfo']['corpSerialNo'].",".$item['buyerInfo']['engName'].",".$item['buyerInfo']['engAddress'].",1,'edi',".date('Y-m-d H:i:s', strtotime($item['notifyTime'])).",'ERUI_REJECTED',".$item['unAcceptReason'].")");
                        }
                    }
                }
                $conn->close();
            }
        } catch (Exception $e) {
            LOG::write('CLASS:' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($e->getMessage(), LOG::ERR);
        }
    }
    /**
     * 银行代码批复通知
     *
     */
    static public function getBankApproved() {
        try{
            $client = new SoapClient(self::$url_wsdl);
            $response = $client->doEdiBankCodeApprove(array('startDate'=>self::getStartDate(),'endDate'=>self::getEndDate()));
            $buyerCodeApproveInfo = $response->out->BuyerCodeApproveInfo;
            if ($buyerCodeApproveInfo) {

                //存储结果日志
//                $path = "";
//                $time = date('Y-m-d h:i:s',time());
//                $file = $path."/".$time."_edi.txt";
//                $fp = fopen($file,"a+");
//                $start="time:".$time."\r\n"."edi/buyerCodeApprove:"."\r\n"."---------- content start ----------"."\r\n";
//                $end ="\r\n"."---------- content end ----------"."\r\n\n";
//                $content=$start."".$buyerCodeApproveInfo."".$end;
//                fwrite($fp,$content);
//                fclose($fp);

                $conn = mysqli_connect(self::$host,self::$user,self::$pass,self::$dbname) or die('connect fail');
                $conn->query("set names 'utf8';");

                $updata =  self::object_array($buyerCodeApproveInfo);
                foreach($updata as $item){
                    if($item['approveFlag'] == 1){
                        //先查看是否已经审核通过
                        $check = $conn->query("select status from buyer_credit.buyer_reg_info where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                        if($check['status'] == 'EDI_APPROVED'){
                            $conn->query("update buyer_credit.buyer_credit set bank_swift=".$item['bankInfo']['bankSwift'].",approved_date=".date('Y-m-d H:i:s', strtotime($item['notifyTime'])).",status='EDI_APPROVED' where buyer_no = ".$item['bankInfo']['corpSerialNo']);

                        } else {
                            $conn->query("update buyer_credit.buyer_credit set bank_swift=".$item['bankInfo']['bankSwift'].",approved_date=".date('Y-m-d H:i:s', strtotime($item['notifyTime']))." where buyer_no = ".$item['bankInfo']['corpSerialNo']);
                        }
                        $conn->query("update buyer_credit.buyer_bank_info set status='EDI_APPROVED' where buyer_no = ".$item['buyerInfo']['corpSerialNo']);

                        $conn->query("insert into buyer_credit.buyer_credit_log(buyer_no,`bank_name`,bank_address,sign,checked_by,checked_at,out_status) values(".$item['bankInfo']['corpSerialNo'].",".$item['bankInfo']['engName'].",".$item['bankInfo']['address'].",2,'edi',".date('Y-m-d H:i:s', strtotime($item['notifyTime'])).",'EDI_APPROVED')");
                    } else {
                        $pre_mc = preg_match('/(存在)*(已存在)*(已经存在)*(重复提交)*(重复申请)*/', $item['unAcceptReason']);
                        if($pre_mc == 0){
                            $conn->query("update buyer_credit.buyer_credit set status='ERUI_REJECTED',bank_remarks=".$item['unAcceptReason']." where buyer_no = ".$item['buyerInfo']['corpSerialNo']);
                            //添加日志
                            $conn->query("insert into buyer_credit.buyer_credit_log(buyer_no,`bank_name`,bank_address,sign,checked_by,checked_at,out_status,out_remarks) values(".$item['bankInfo']['corpSerialNo'].",".$item['bankInfo']['engName'].",".$item['bankInfo']['address'].",2,'edi',".date('Y-m-d H:i:s', strtotime($item['notifyTime'])).",'ERUI_REJECTED',".$item['unAcceptReason'].")");
                        }
                    }
                }
                $conn->close();
            }
        } catch (Exception $e) {
            LOG::write('CLASS:' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($e->getMessage(), LOG::ERR);
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