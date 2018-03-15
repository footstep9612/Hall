<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:40
 */
class TimedtaskEdiController extends PublicController{

    private $serverIP = '172.18.20.125';
    private $serverPort = '8081';
    private $serverDir = 'ediserver';
    private $serverDirSec = 'ws_services';
    private $serviceInterface = 'SolEdiProxyWebService';
    private $mode = 'wsdl';
    static private $policyNo = 'SCH043954-181800';
    static private $serviceUri = '';
    static private $url_wsdl = '172.18.20.125:8081/edi/ws_services/SolEdiShorttermWebService?wsdl';

    public function init(){
        parent::init();
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);

        $this->buyerCreditModel = new BuyerCreditModel();
        $this->buyerCreditLogModel = new BuyerCreditLogModel();
        $this->buyerBankInfoModel = new BuyerBankInfoModel();
        $this->buyerRegInfoModel = new BuyerRegInfoModel();

        if (self::$serviceUri == '') {
            self::$serviceUri = 'http://'.$this->serverIP.':'.$this->serverPort.'/'.$this->serverDir.'/'.$this->serverDirSec.'/'.$this->serviceInterface;
        }
        if ($this->mode == 'wsdl') {
            self::$serviceUri .= '?wsdl';
        }
    }


    /**
     * 企业代码批复通知
     *
     */
     public function getByuerApproved()
     {
         try {
             $client = new SoapClient(self::$serviceUri);
             $response = $client->doEdiBuyerCodeApprove(array('startDate' => self::getStartDate(), 'endDate' => self::getEndDate()));
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

                 $updata = self::object_array($buyerCodeApproveInfo);
                 foreach ($updata as $item) {
                     if ($item['approveFlag'] == 1) {
                         //先查看是否已经审核通过
                         $check = $this->buyerBankInfoModel->field('status')->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->find();
                         if ($check['status'] == 'EDI_APPROVED') {
                             //授信表
                             $updata = [
                                 'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                                 'sinosure_no' => $item['buyerInfo']['buyerNo'],
                                 'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                 'status' => 'EDI_APPROVED'
                             ];
                             $this->buyerCreditModel->update_data($updata);
                         } else {
                             $updata = [
                                 'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                                 'sinosure_no' => $item['buyerInfo']['buyerNo'],
                                 'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime']))
                             ];
                             $this->buyerCreditModel->update_data($updata);
                         }
                         //企业表
                         $reg['status'] = 'EDI_APPROVED';
                         $this->buyerRegInfoModel->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->save($reg);
                         //日志
                         $log_arr = [
                             'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                             'name' => $item['buyerInfo']['engName'],
                             'address' => $item['buyerInfo']['engAddress'],
                             'sign' => 1,
                             'checked_by' => 'edi',
                             'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                             'out_status' => 'EDI_APPROVED'
                         ];
                         $this->buyerCreditLogModel->create_data($log_arr);
                     } elseif ($item['approveFlag'] == 0) {
                         $pre_mc = preg_match('/(存在)*(已存在)*(已经存在)*(重复提交)*(重复申请)*/', $item['unAcceptReason']);
                         if($pre_mc == 0) {
                             $updata = [
                                 'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                                 'remarks' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])).'\r'.$item['unAcceptReason'],
                                 'status' => 'EDI_REJECTED'
                             ];
                             $this->buyerCreditModel->update_data($updata);
                             //日志
                             $log_arr = [
                                 'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                                 'sign' => 1,
                                 'checked_by' => 'edi',
                                 'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                 'out_remarks' => $item['unAcceptReason'],
                                 'out_status' => 'EDI_REJECTED'
                             ];
                             $this->buyerCreditLogModel->create_data($log_arr);
                         }
                     }
                 }
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
     public function getBankApproved() {
        try{
            $client = new SoapClient(self::$serviceUri);
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
                        $check = $this->buyerRegInfoModel->field('status')->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->find();
                        if($check['status'] == 'EDI_APPROVED'){
                            //授信表
                            $updata = [
                                'buyer_no' => $item['bankInfo']['corpSerialNo'],
                                'bank_swift' => $item['bankInfo']['bankSwift'],
                                'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                'status' => 'EDI_APPROVED'
                            ];
                            $this->buyerCreditModel->update_data($updata);
                        } else {
                            //授信表
                            $updata = [
                                'buyer_no' => $item['bankInfo']['corpSerialNo'],
                                'bank_swift' => $item['bankInfo']['bankSwift'],
                                'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime']))
                            ];
                            $this->buyerCreditModel->update_data($updata);
                        }
                        //银行表
                        $reg['status'] = 'EDI_APPROVED';
                        $this->buyerBankInfoModel->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->save($reg);
                        //日志
                        $log_arr = [
                            'buyer_no' => $item['bankInfo']['corpSerialNo'],
                            'bank_name' => $item['bankInfo']['engName'],
                            'bank_address' => $item['bankInfo']['address'],
                            'sign' => 2,
                            'checked_by' => 'edi',
                            'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                            'out_status' => 'EDI_APPROVED'
                        ];
                        $this->buyerCreditLogModel->create_data($log_arr);
                    } else {
                        $pre_mc = preg_match('/(存在)*(已存在)*(已经存在)*(重复提交)*(重复申请)*/', $item['unAcceptReason']);
                        if($pre_mc == 0){
                            $updata = [
                                'buyer_no' => $item['bankInfo']['corpSerialNo'],
                                'bank_remarks' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])).'\r'.$item['unAcceptReason'],
                                'status' => 'EDI_REJECTED'
                            ];
                            $this->buyerCreditModel->update_data($updata);
                            //日志
                            $log_arr = [
                                'buyer_no' => $item['bankInfo']['corpSerialNo'],
                                'bank_name' => $item['bankInfo']['engName'],
                                'bank_address' => $item['bankInfo']['address'],
                                'sign' => 2,
                                'checked_by' => 'edi',
                                'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                'out_remarks' => $item['unAcceptReason'],
                                'out_status' => 'EDI_REJECTED'
                            ];
                            $this->buyerCreditLogModel->create_data($log_arr);
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