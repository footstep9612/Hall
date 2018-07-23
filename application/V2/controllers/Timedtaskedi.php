<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:40
 */
class TimedtaskediController extends PublicController{

//    private $serverIP = 'credit.eruidev.com';
//    private $serverPort = '80';
    private $serverDir = 'ediserver';
    private $serverDirSec = 'ws_services';
    private $serviceInterface = 'SolEdiProxyWebService';
    private $mode = 'wsdl';
    static private $policyNo = 'SCH043954-181800';
    static private $serviceUri = '';
    static private $url_wsdl = 'http://39.107.75.138:8086/ediserver/ws_services/SolEdiProxyWebService?wsdl';

    public function init(){
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);

        $this->buyerCreditModel = new BuyerCreditModel();
        $this->buyerCreditLogModel = new BuyerCreditLogModel();
        $this->buyerBankInfoModel = new BuyerBankInfoModel();
        $this->buyerRegInfoModel = new BuyerRegInfoModel();
        $config_obj = Yaf_Registry::get("config");
        $serverIP = $config_obj->ediserver->toArray();

        if (self::$serviceUri == '') {
            self::$serviceUri = 'http://'.$serverIP['host'].':'.$serverIP['port'].'/'.$this->serverDir.'/'.$this->serverDirSec.'/'.$this->serviceInterface;
        }
        if ($this->mode == 'wsdl') {
            self::$serviceUri .= '?wsdl';
        }
    }
    /**
     * 定时任务:
     *  企业代码批复:v2/Edi/doEdiBuyerCodeApprove   定时任务：每天23:00:00执行,每天01:00:00执行,每天03:00:00执行
     *  银行代码批复:v2/Edi/doEdiBankCodeApprove    定时任务：每天23:00:00执行,每天01:00:00执行,每天03:00:00执行
     *
     */


    /**
     * @desc 企业代码批复通知接口（定时任务：每天23:00:00执行,每天01:00:00执行,每天03:00:00执行）
     * @author klp
     * @time 2018-03-16
     */
     public function getByuerApprovedAction()
     {
         try {
             $client = new SoapClient(self::$serviceUri);
             $response = $client->doEdiBuyerCodeApprove(array('startDate' => self::getStartDate(), 'endDate' => self::getEndDate()));
             $buyerCodeApproveInfo = $response->out->BuyerCodeApproveInfo;
             if ($buyerCodeApproveInfo) {

             //存储结果日志
            $time = date('Y-m-d h:i:s',time());
            $start="time:".$time."\r\n"."Edi/buyerCodeApprove:"."\r\n"."---------- content start ----------"."\r\n";
            $end ="\r\n"."---------- content end ----------"."\r\n\n";
            $content=$start."".print_r(self::object_array($buyerCodeApproveInfo),true)."".$end;
            LOG::write($content, LOG::INFO);

                 $updata = self::object_array($buyerCodeApproveInfo);

                 foreach ($updata as $item) {
                     $check_credit = $this->buyerCreditModel->field('status,account_settle,approved_date')->where(['buyer_no' => $item['corpSerialNo']])->find();
                     if(strtotime($check_credit['approved_date']) < strtotime($item['notifyTime'])) {
                         if ($item['approveFlag'] == 1) {
                             //先查看是否已经审核通过
                             if ($check_credit['account_settle'] == 'OA') {
                                 $updata_credit = [
                                     'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                                     'sinosure_no' => $item['buyerInfo']['buyerNo'],
                                     'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                     'status' => 'EDI_APPROVED',
                                     'remarks' => ''
                                 ];
                                 if ($check_credit['status'] != 'APPROVED') {
                                     $this->buyerCreditModel->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->save($updata_credit);
                                 }
                             } else {
                                 $check = $this->buyerBankInfoModel->field('status')->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->find();
                                 if ($check['status'] == 'EDI_APPROVED') {
                                     //授信表
                                     $updata_credit = [
                                         'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                                         'sinosure_no' => $item['buyerInfo']['buyerNo'],
                                         'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                         'status' => 'EDI_APPROVED',
                                         'remarks' => ''
                                     ];
                                     if ($check_credit['status'] != 'APPROVED') {
                                         $this->buyerCreditModel->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->save($updata_credit);
                                     }
                                 } else {
                                     $updata_credit = [
                                         'buyer_no' => $item['buyerInfo']['corpSerialNo'],
                                         'sinosure_no' => $item['buyerInfo']['buyerNo'],
                                         'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime']))
                                     ];
                                     $this->buyerCreditModel->where(['buyer_no' => $item['buyerInfo']['corpSerialNo']])->save($updata_credit);
                                 }
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
                                 'checked_by' => '1001',
                                 'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                 'credit_at' => date('Y-m-d H:i:s', time()),
                                 'out_status' => 'EDI_APPROVED'
                             ];
                             $this->buyerCreditLogModel->create_data($log_arr);
                         } elseif ($item['approveFlag'] == 0) {
                             $pre_mc1 = preg_match('/存在/', $item['unAcceptReason']);
                             $pre_mc2 = preg_match('/已存在/', $item['unAcceptReason']);
                             $pre_mc3 = preg_match('/已经存在/', $item['unAcceptReason']);
                             $pre_mc4 = preg_match('/重复提交/', $item['unAcceptReason']);
                             $pre_mc5 = preg_match('/重复申请/', $item['unAcceptReason']);
                             if ($pre_mc1 == 1 || $pre_mc2 == 1 || $pre_mc3 == 1 || $pre_mc4 == 1 || $pre_mc5 == 1) {
                             } else {
                                 if ($check_credit['status'] != 'APPROVED') {
                                     $updata_credit = [
                                         'buyer_no' => $item['corpSerialNo'],
                                         'remarks' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])) . '\r' . $item['unAcceptReason'],
                                         'status' => 'EDI_REJECTED'
                                     ];
                                     $this->buyerCreditModel->where(['buyer_no' => $item['corpSerialNo']])->save($updata_credit);
                                     //日志
                                     $log_arr = [
                                         'buyer_no' => $item['corpSerialNo'],
                                         'sign' => 1,
                                         'checked_by' => '1001',
                                         'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                         'credit_at' => date('Y-m-d H:i:s', time()),
                                         'out_remarks' => $item['unAcceptReason'],
                                         'out_status' => 'EDI_REJECTED'
                                     ];
                                     $this->buyerCreditLogModel->create_data($log_arr);
                                 }
                             }
                         }
                     }
                 }
             }
         } catch (Exception $e) {
             LOG::write('CLASS:' . __CLASS__ . PHP_EOL . '【ByuerApproved】 LINE:' . __LINE__, LOG::EMERG);
             LOG::write($e->getMessage(), LOG::ERR);
         }
     }

    /**
     * @desc 银行代码批复通知接口（定时任务：每天23:00:00执行,每天01:00:00执行,每天03:00:00执行）
     * @author klp
     * @time 2018-03-16
     */
     public function getBankApprovedAction() {
        try{
            $client = new SoapClient(self::$serviceUri);
            $response = $client->doEdiBankCodeApprove(array('startDate'=>"2018-03-09T23:59:59", 'endDate'=>self::getEndDate()));
            $buyerBankApproveInfo = $response->out->BankCodeApproveInfo;
            if ($buyerBankApproveInfo) {

                //存储结果日志
                $time = date('Y-m-d h:i:s',time());
                $start="time:".$time."\r\n"."Edi/buyerBankApproveInfo:"."\r\n"."---------- content start ----------"."\r\n";
                $end ="\r\n"."---------- content end ----------"."\r\n\n";
                $content=$start."".print_r(self::object_array($buyerBankApproveInfo),true)."".$end;
                LOG::write($content, LOG::INFO);

                $updata =  self::object_array($buyerBankApproveInfo);
                foreach($updata as $item){
                    $check_credit = $this->buyerCreditModel->field('status,approved_date')->where(['buyer_no' => $item['corpSerialNo']])->find();
                    if(strtotime($check_credit['approved_date']) < strtotime($item['notifyTime'])) {
                        if ($item['approveFlag'] == 1) {
                            //先查看是否已经审核通过
                            $check = $this->buyerRegInfoModel->field('status')->where(['buyer_no' => $item['bankInfo']['corpSerialNo']])->find();
                            if ($check['status'] == 'EDI_APPROVED') {
                                //授信表
                                $updata_credit = [
                                    'buyer_no' => $item['bankInfo']['corpSerialNo'],
                                    'bank_swift' => $item['bankInfo']['bankSwift'],
                                    'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                    'status' => 'EDI_APPROVED',
                                    'bank_remarks' => ''
                                ];
                                if ($check_credit['status'] != 'APPROVED') {
                                    $this->buyerCreditModel->where(['buyer_no' => $item['bankInfo']['corpSerialNo']])->save($updata_credit);
                                }
                            } else {
                                //授信表
                                $updata_credit = [
                                    'buyer_no' => $item['bankInfo']['corpSerialNo'],
                                    'bank_swift' => $item['bankInfo']['bankSwift'],
                                    'approved_date' => date('Y-m-d H:i:s', strtotime($item['notifyTime']))
                                ];
                                $this->buyerCreditModel->where(['buyer_no' => $item['bankInfo']['corpSerialNo']])->save($updata_credit);
                            }
                            //银行表
                            $reg['status'] = 'EDI_APPROVED';
                            $this->buyerBankInfoModel->where(['buyer_no' => $item['bankInfo']['corpSerialNo']])->save($reg);
                            //日志
                            $log_arr = [
                                'buyer_no' => $item['bankInfo']['corpSerialNo'],
                                'bank_name' => $item['bankInfo']['engName'],
                                'bank_address' => $item['bankInfo']['address'],
                                'sign' => 2,
                                'checked_by' => '1001',
                                'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                'credit_at' => date('Y-m-d H:i:s', time()),
                                'out_status' => 'EDI_APPROVED'
                            ];
                            $this->buyerCreditLogModel->create_data($log_arr);

                        } elseif ($item['approveFlag'] == 0) {
                            $pre_mc1 = preg_match('/存在/', $item['unAcceptReason']);
                            $pre_mc2 = preg_match('/已存在/', $item['unAcceptReason']);
                            $pre_mc3 = preg_match('/已经存在/', $item['unAcceptReason']);
                            $pre_mc4 = preg_match('/重复提交/', $item['unAcceptReason']);
                            $pre_mc5 = preg_match('/重复申请/', $item['unAcceptReason']);
                            if ($pre_mc1 == 1 || $pre_mc2 == 1 || $pre_mc3 == 1 || $pre_mc4 == 1 || $pre_mc5 == 1) {
                            } else {
                                if($check_credit['status'] != 'APPROVED'){
                                    $updata_credit = [
                                        'buyer_no' => $item['corpSerialNo'],
                                        'bank_remarks' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])) . '\r' . $item['unAcceptReason'],
                                        'status' => 'EDI_REJECTED'
                                    ];
                                    $this->buyerCreditModel->where(['buyer_no' => $item['corpSerialNo']])->save($updata_credit);
                                    //日志
                                    $log_arr = [
                                        'buyer_no' => $item['corpSerialNo'],
                                        'sign' => 2,
                                        'checked_by' => '1001',
                                        'checked_at' => date('Y-m-d H:i:s', strtotime($item['notifyTime'])),
                                        'credit_at' => date('Y-m-d H:i:s', time()),
                                        'out_remarks' => $item['unAcceptReason'],
                                        'out_status' => 'EDI_REJECTED'
                                    ];
                                    $this->buyerCreditLogModel->create_data($log_arr);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            LOG::write('CLASS:' . __CLASS__ . PHP_EOL . '【BankApproved】LINE:' . __LINE__, LOG::EMERG);
            LOG::write($e->getMessage(), LOG::ERR);
        }
    }

    //v2/Edi/checkEdiValid    定时任务：每天04:00:00执行一次
    public function checkEdiValidAction(){
        $check_credit = $this->buyerCreditModel->field('status,account_settle,approved_date,buyer_no,nolc_deadline,lc_deadline')->where(['status'=>'APPROVED'])->select();
        if (!empty($check_credit)) {
            foreach($check_credit as $item) {
                if(!empty($item['approved_date']) && $item['status']=='APPROVED'){
                    if($item['account_settle'] == "OA"){
                        $deadline = $item['nolc_deadline'];
                    }else {
                        $deadline = $item['lc_deadline'];
                    }
                    $time = strtotime(date('Y-m-d H:i:s',strtotime($item['approved_date'])+$deadline*24*60*60));
                    $current_time = strtotime('now');

                    if($time <= $current_time) {
                        $item['status'] = 'INVALID';
                        $status['status'] = 'INVALID';
                        $this->buyerCreditModel->where(['buyer_no' => $item['buyer_no']])->save($status);

                        //记录
                        $note_time = date('Y-m-d h:i:s',time());
                        $start="time:".$note_time."\r\n"."Edi/checkEdiValidAction:"."\r\n"."---------- check start ----------"."\r\n";
                        $end ="\r\n"."---------- check end ----------"."\r\n\n";
                        $content=$start."客户:".$item['buyer_no'].' ,结算方式:'.$item['account_settle'].' ,时间:'.$note_time."检测,授信到期失效记录".$end;
                        LOG::write($content, LOG::INFO);
                    }
                    unset($note_time);
                    unset($time);
                    unset($current_time);
                }
            }
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
    static function struct_to_array($item) {
        if(!is_string($item)) {
            $item = (array)$item;
            foreach ($item as $key=>$val){
                $item[$key]  =  @self::struct_to_array($val);
            }
        }
        return $item;
    }

}