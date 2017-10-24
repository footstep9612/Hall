<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/8
 * Time: 16:27
 */

class TestCreditController extends PublicController{

    public function init() {
        $this->put_data = $this->put_data ? $this->put_data : json_decode(file_get_contents("php://input"), true);
    }

    public function testAction(){

        $SinoSure = new Edi();
        $resBuyer = $SinoSure->EdiCountryClassify();

    }
    /**
     *
     * 测试信息
     * SCH017124-171700 测试环境的保单号(信保提供--正式环境客户经理会提供给你们)
     * (当天的限额余额 第二天才能看到   每天凌晨之后会有同步计算余额的程序执行)
     *
     *THE BUYER: EVERUP SCAFFOLDS TRADING L.L.C
     * 买方:EVERUP SCAFFOLDS TRADING L.L.C
     * ADDRESS:INDIGO OPTIMA 01-OFFICE 710,P.O.BOX 128203,DUBAI,UAE.
     * 地址:INDIGO OPTIMA 01-OFFICE 710,P.O.BOX 128203,DUBAI,UAE.
     *
     * 买方: KIRAN UDYOG PVT.LTD.
     * 地址:Plot No-32,Sector-3,IMT,Manesar(Gurgaon),India-122050.
     */



    /**
     *
     *买家代码申请
     * @author klp
     */
    public function testBuyerAction(){
        $BuyerCodeApply['lang'] = 'en';
        $BuyerCodeApply['buyer_no'] = '20170914000001';
        $BuyerCodeApply['country_code'] = 'CHN';
        $BuyerCodeApply['name'] = 'EVERUP SCAFFOLDS TRADING L.L.C';
        $BuyerCodeApply['area_no'] = 1101;//区域代码
        $BuyerCodeApply['address'] = 'INDIGO OPTIMA 01-OFFICE 710,P.O.BOX 128203,DUBAI,UAE.';

//        $buyerModel = new BuyerModel();          //企业银行信息
//        $resultBuyerInfo = $buyerModel->buyerCerdit($this->put_data);
//        jsonReturn($resultBuyerInfo);

        $SinoSure = new Edi();
        $resBuyer = $SinoSure->EdiBuyerCodeApply($BuyerCodeApply);
        var_dump($resBuyer);die;
        if($resBuyer['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $this->returnInfo($resBuyer);
    }
    /**
     *
     *买家代码申请反馈
     * @author klp
     */
    public function testBuyerApporovelAction(){

        $SinoSure = new Edi();
        $resBuyer = $SinoSure->EdiBuyerCodeApprove();
//        print_r($resBuyer);die;
        if($resBuyer && !isset($resBuyer['code'])){
            foreach($resBuyer as $item){
                var_dump($resBuyer);die;
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
                $where = array( 'buyer_no'=> $item['BuyerInfo']['corpSerialNo']);
                $buyerModel = new BuyerModel();
            }
        } elseif ($resBuyer && isset($resBuyer['code'])){
            jsonReturn('',MSG::MSG_FAILED,'参数错误!');
        } else {
            jsonReturn('',MSG::MSG_FAILED,'no data!');
        }

        $this->returnInfo($resBuyer);
    }
    /**
     *
     *银行代码申请
     * @author klp
     */
    public function testBankAction(){
        $BuyerCodeApply['buyer_no'] = '20170914000001';
        $BuyerCodeApply['bank_name'] = 'Bank of IDN';
        $BuyerCodeApply['bank_country_code'] = 'IDN';
        $BuyerCodeApply['bank_address'] = 'IDN';
        $BuyerCodeApply['swift_code'] = '';
//        $buyerModel = new BuyerModel();          //企业银行信息
//        $resultBuyerInfo = $buyerModel->buyerCerdit($this->put_data);
        $SinoSure = new Edi();
        $resBank = $SinoSure->EdiBankCodeApply($BuyerCodeApply);//print_r($resBuyer);die;
        var_dump($resBank);die;
        if($resBuyer['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $this->returnInfo($resBuyer);
    }
    /**
     *
     *买银行代码申请反馈
     * @author klp
     */
    public function testBankApporovelAction(){

        $SinoSure = new Edi();
        $resBuyer = $SinoSure->EdiBankCodeApprove();
        //print_r($resBuyer);die;
        if($resBuyer && !isset($resBuyer['code'])){
            foreach($resBuyer as $item){
                var_dump($resBuyer);die;
                //1.判断
                $data = [
                    'approveFlag'=> $item['approveFlag'],//审批标志 1通过  0-申请退回/不通过
                    'noticeSerialNo'=> $item['noticeSerialNo'],//信保通知序号（唯一）
                    'remarks'=> $item['unAcceptReason'],//申请退回/不通过原因
                    'corpSerialNo'=> $item['corpSerialNo'],//中信保买家代码
                    'notifyTime'=> date('Y-m-d H:i:s', strtotime($item['notifyTime'])),//最新通知时间
                    'buyer_no'=> $item['bankInfo']['corpSerialNo'],
                    'buyerNo'=> $item['bankInfo']['buyerNo'], //中信保买家代码
                    'clientNo'=> $item['bankInfo']['clientNo'], //企业标识
                    'address'=> $item['bankInfo']['address'], //银行地址(英文)
                    'bankSwift'=> $item['bankInfo']['bankSwift'], //中信保银行swift码
                    'countryCode'=> $item['bankInfo']['countryCode'], //银行国家代码
                    'engName'=> $item['bankInfo']['engName'], //银行英文名称
                ];
                $where = array( 'buyer_no'=> $item['BuyerInfo']['corpSerialNo']);
                $buyerModel = new BuyerModel();
            }
        } elseif ($resBuyer && isset($resBuyer['code'])){
            jsonReturn('',MSG::MSG_FAILED,'参数错误!');
        } else {
            jsonReturn('',MSG::MSG_FAILED,'no data!');
        }
        $this->returnInfo($resBuyer);
    }

    /**
     *
     *出口险-非LC限额申请
     * @author klp
     */
    public function testNoLcQuotaAction(){
        $NoLc = [
            'buyer_no' =>'20170920000011',
            'corpSerialNo' =>'20170920000011',
            'clientNo' =>'',
            'policyNo' =>'SCH017124-171700',

            'sinosureBuyerNo' =>'USA/387022',//如不提供，请参填以下6行
            'corpBuyerNo' =>'',
            'buyerChnName' =>'',
            'buyerEngName ' =>'',
            'buyerCountryCode' =>'',
            'buyerEngAddress' =>'',
            'buyerChnAddress' =>'',

            'contractPayMode' =>'2', //1.LC  2.DP 3.DA 4.OA
            'payTermApply' =>6,  //申请信用期限
            'quotaSumApply' =>100000.00,
            'orderSum' =>10000.00,
            'tradeNameCode' =>'21',
            'tradeElseName' =>'船舶及修理',
            'ifHistTrade' =>'0',
            'earlyTradeYear' =>'2017',
            'startDebtYear' =>'2017',
            'declaration' =>'1',
            'ifhavetradefinancing' =>'0',
            'ifhaverelation' =>'0',//具体关联为1时,relationdetail不能为空
            'relationdetail'=>'',
            'issamewithcontract' =>'1'
        ];

        $SinoSure = new Edi();
        $resNoLcQuota = $SinoSure->EdiNoLcQuotaApplyV2($NoLc);//print_r($resBuyer);die;
        var_dump($resNoLcQuota);die;
        if($resBuyer['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $this->returnInfo($resBuyer);
    }

    /**
     *
     *出口险-LC限额申请
     * @author klp
     */
    public function testLcQuotaAction(){
        $Lc = [
            'buyer_no' =>'20170920000009',
            'corpSerialNo' =>'20170920000009',
            'clientNo' =>'',
            'policyNo' =>'SCH017124-171700',

            'sinosureBuyerNo' =>'USA/387022',//如不提供，请参填以下6行
            'corpBuyerNo' =>'',
            'buyerChnName' =>'',
            'buyerEngName ' =>'',
            'buyerCountryCode' =>'',
            'buyerEngAddress' =>'',
            'buyerChnAddress' =>'',

            'ifRepeat' =>'0',
            'payTermApply' =>6,//LC信用期限
            'quotaSumApply' =>100000.00,
            'lcSum' =>100000.00,
            'lastLadeDate' =>2017-10-01,
            'goodsCode' =>'19,21',
            'elsegoodsName' =>'工程机械及其零部件',

            'openBankSwift' =>'KOFM YS TG',
            'declaration' =>'1',
            'ifhavetradefinancing' =>'0',
            'ifhaverelation' =>'0',//具体关联为1时,relationdetail不能为空
            'relationdetail'=>'',
            'issamewithcontract' =>'1'
        ];

        $SinoSure = new Edi();
        $resLcQuota = $SinoSure->EdiLcQuotaApplyV2($Lc);//print_r($resBuyer);die;
        var_dump($resLcQuota);die;
        if($resBuyer['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $this->returnInfo($resBuyer);
    }
    /**
     *
     *出口险-限额批复通知
     * @author klp
     */
    public function testApprovelAction(){

        $SinoSure = new Edi();
        $resQuotaApprove = $SinoSure->EdiQuotaApproveInfo();//print_r($resBuyer);die;
        if($resQuotaApprove && !isset($resQuotaApprove['code'])){
            var_dump($resQuotaApprove);die;
            foreach($resQuotaApprove as $item){
                //1.判断
                $data = [
                    'approveFlag'=> $item['approveFlag'],//审批标志 1通过  0-申请退回/不通过
                    'ifLc'=> $item['ifLc'],//是否LC申请 num
                    'noticeSerialNo'=> $item['noticeSerialNo'],//信保通知序号（唯一）
                    'remarks'=> $item['unAcceptReason'],//申请退回/不通过原因
                    'corpSerialNo'=> $item['corpSerialNo'],//中信保买家代码
                    'notifyTime'=> date('Y-m-d H:i:s', strtotime($item['notifyTime'])),//最新通知时间
                    'buyer_no'=> $item['BuyerQuotaInfo']['corpSerialNo'],//企业内部限额申请唯一标识，分为LC和非LC
                    'buyerNo'=> $item['BuyerQuotaInfo']['buyerNo'], //中信保买家代码(余额查询唯一)
                    'corpBuyerNo'=> $item['BuyerQuotaInfo']['corpBuyerNo'], //企业买方代码/企业内部买方唯一标识
                    'clientNo'=> $item['BuyerQuotaInfo']['clientNo'], //客户标识，信保通编号
                    'policyNo'=> $item['BuyerQuotaInfo']['policyNo'], //保险单号
                    'quotaNo'=> $item['BuyerQuotaInfo']['quotaNo'], //中信保限额编号

                ];
                $where = array( 'buyer_no'=> $item['BuyerInfo']['corpSerialNo']);
                $buyerModel = new BuyerModel();
            }
        } elseif ($resQuotaApprove && isset($resQuotaApprove['code'])){
            jsonReturn('',MSG::MSG_FAILED,'参数错误!');
        } else {
            jsonReturn('',MSG::MSG_FAILED,'no data!');
        }
        $this->returnInfo($resQuotaApprove);
    }
    /**
     *
     *出口险-限额余额查询V2(新版)
     * @author klp
     */
    public function testBalanceInfoAction(){

        $SinoSure = new Edi();
        $resBuyer = $SinoSure->QuotaBalanceInfoByPolicyNo();
        var_dump($resBuyer);die;
        if($resBuyer['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $this->returnInfo($resBuyer);
    }

    //统一回复调用方法
    public function returnInfo($result) {
        if ($result && !empty($result)) {
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('', '-1002', '失败!');
        }
        exit;
    }


    //返回样例bank
    public function arr(){
       //bank
        [
            5 =>[
              'approveFlag' =>  0,
              'bankInfo' => null,
              'corpSerialNo' =>  '20170913000012' ,
              'noticeSerialNo' =>  86603,
              'notifyTime' =>  '2017-09-13T13:56:52+08:00' ,
              'unAcceptCode' => null,
              'unAcceptReason' =>  '银行英文名称[engname]:Bank of MYS和银行地址[address]:MYS,记录已经存在,不能重复申请;流水号[corpserialno]：20170913000012;'
                ],
            6 => [
              'approveFlag' =>  1,
              'bankInfo' => [
                  'address' =>  'MYS' ,
                  'bankSwift' =>  'KOFM YS TG' ,
                  'belongBankName' => null,
                  'belongBankSwift' => null,
                  'chnName' => null,
                  'clientNo' =>  '17005529' ,
                  'corpSerialNo' =>  '20170913000011' ,
                  'corporation' => null,
                  'countryCode' =>  'MYS' ,
                  'employeeSum' => null,
                  'engName' =>  'BANK OF MYS',
                  'fax' => null,
                  'interOrder' => null,
                  'modifyTime' =>  '2017-09-13 14:10:27',
                  'nationOrder' => null,
                  'nodeSum' => null,
                  'profit' => null,
                  'remark' => null,
                  'selfAssetRate' => null,
                  'selfCapital' => null,
                  'setupDate' => null,
                  'stockHolder' => null,
                  'tel' => null,
                  'totalAsset' => null,
                  'turnover' => null,
                  'webAddress' => null,
                  'zip' => null
                    ],
              'corpSerialNo' =>  '20170913000011' ,
              'noticeSerialNo' => 86604,
              'notifyTime' =>  '2017-09-13T14:10:27+08:00' ,
              'unAcceptCode' => null,
              'unAcceptReason' => null
            ]
        ];
        //Buyer
        [
            0 =>[
              'approveFlag' =>  0,
              'buyerInfo' => null,
              'corpSerialNo' => '20170808000001' ,
              'noticeSerialNo' =>  141891,
              'notifyTime' =>  '2017-09-11T14:15:38+08:00' ,
              'unAcceptCode' => null,
              'unAcceptReason' =>  '买家代码申请已存在,请勿重复提交;流水号[corpserialno]：20170808000001;'
                ],
            1=>[
                'approveFlag' => 1,
                'buyerInfo' =>[
                   'buyerNo' =>  'USA/387022',
                   'chnAddress' => null,
                   'chnName' => null,
                   'clientNo' =>  '17005529' ,
                   'corpSerialNo' =>  '20170808000001' ,
                   'corporation' => null,
                   'countryCode' =>  'USA' ,
                   'creditno' => null,
                   'eMail' => null,
                   'engAddress' =>  'USA,UNITED STATES OF AMERICA' ,
                   'engName' =>  'J&C IMPEX LIMITED' ,
                   'equity'  => null,
                   'fax' => null,
                   'mergeNo' => null,
                   'modifyTime' =>  '2017-09-12 16:44:37',
                   'orgno' => null,
                   'regAddress' => null,
                   'regNo' => null,
                   'regyear' => null,
                   'remark' => null,
                   'setDate' => null,
                   'shtName' => null,
                   'tel' => null,
                   'webAddress' => null,
                   'yearSale' => null,
                    ],
                'corpSerialNo' =>  '20170808000001' ,
                'noticeSerialNo' =>  141952,
                'notifyTime' => '2017-09-12T16:44:41+08:00' ,
                'unAcceptCode' => null,
                'unAcceptReason' => null
              ]
        ];
        //批复
        //余额
    }

    //匹配英文
    function isEn($str) {
        $mode = '/^[A-Za-z0-9]+$/';
        if (preg_match($mode, $str)) {
            return true;
        } else {
            return false;
        }
    }
    function isEgt8($str) {
        $mode = '/[1-9][0-9]{8,}/';
        if (preg_match($mode, $str)) {
            return true;
        } else {
            return false;
        }
    }
    function isCode($str) {
        $mode = '/^(0|[1-9][0-9]*)$/';
        if (preg_match($mode, $str)) {
            return true;
        } else {
            return false;
        }
    }
    function isNum($str) {
        $mode = '/^[1-9]\d*$/';
        if (preg_match($mode, $str)) {
            return true;
        } else {
            return false;
        }
    }
    //半角转全角
    function abc2sbc($str) {
        $t = array(' ', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '.', '-', '_', '@');
        $f = array('　', '０', '１', '２', '３', '４', '５', '６', '７', '８', '９', 'ａ', 'ｂ', 'ｃ', 'ｄ', 'ｅ', 'ｆ', 'ｇ', 'ｈ', 'ｉ', 'ｊ', 'ｋ', 'ｌ', 'ｍ', 'ｎ', 'ｏ', 'ｐ', 'ｑ', 'ｒ', 'ｓ', 'ｔ', 'ｕ', 'ｖ', 'ｗ', 'ｘ', 'ｙ', 'ｚ', 'Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ', 'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ', 'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ', 'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ', 'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ', 'Ｚ', '．', '－', '＿', '＠');
        $str = str_replace($t, $f, $str);
        return $str;
    }
    //符号转换
     function symbol2text($str,$lang) {
         if($lang == 'zh'){
             $str = str_replace(",", "，", $str);
             $str = str_replace(".", "。", $str);
             $str = str_replace("?", "？", $str);
             $str = str_replace("!",  "！", $str);
             $str = str_replace(":", "：", $str);
             $str = str_replace(" ", "、", $str);
             $str = str_replace(";", "；", $str);
             $str = str_replace("\"", "”", $str);
             $str = str_replace("\"", "“", $str);
             $str = str_replace("(", "（", $str);
             $str = str_replace(")", "）", $str);
             $str = str_replace("[", "【", $str);
             $str = str_replace("]", "】", $str);
             $str = str_replace("{", "{", $str);
             $str = str_replace("}", "}", $str);
             $str = str_replace("…", "……", $str);
             $str = str_replace("-", "－",$str);
             $str = str_replace("\"", "《", $str);
             $str = str_replace("\"", "》", $str);
         }
         if($lang == 'en'){
             $str = str_replace("，", ",", $str);
             $str = str_replace("。", ".", $str);
             $str = str_replace("？", "?", $str);
             $str = str_replace("！", "!", $str);
             $str = str_replace("：", ":", $str);
             $str = str_replace("、", " ", $str);
             $str = str_replace("；", ";", $str);
             $str = str_replace("”", "\"", $str);
             $str = str_replace("“", "\"", $str);
             $str = str_replace("（", "(", $str);
             $str = str_replace("）", ")", $str);
             $str = str_replace("【", "[", $str);
             $str = str_replace("】", "]", $str);
             $str = str_replace("{", "{", $str);
             $str = str_replace("}", "}", $str);
             $str = str_replace("……", "…", $str);
             $str = str_replace("－", "-", $str);
             $str = str_replace("《", "\"", $str);
             $str = str_replace("》", "\"", $str);
         }
        return $str;
    }

    function str_format($params) {
        if(is_string($params)) {

        } if(is_array($params)) {
            foreach($params as $key=>$value) {
                self::str_format($value);
            }
        }
        return $params;
    }
}