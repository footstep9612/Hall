<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:44
 */
class EdiLcApplyModel extends PublicModel{

    public function __construct(){
        parent::__construct();
    }

    /**
     *
     *出口险-非LC限额申请
     * @author klp
     */
    public function NoLcQuotaAction(){
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
        $NoLcQuotaApply = $this->checkParamNoLc($NoLc);
        $SinoSure = new Edi();
        $resNoLcQuota = $SinoSure->EdiNoLcQuotaApplyV2($NoLcQuotaApply);

        if($resNoLcQuota['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $this->returnInfo($resNoLcQuota);
    }

    /**
     *
     *出口险-LC限额申请
     * @author klp
     */
    public function LcQuotaAction(){
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
        $LcQuotaApply = $this->checkParamLc($Lc);
        $SinoSure = new Edi();
        $resLcQuota = $SinoSure->EdiLcQuotaApplyV2($LcQuotaApply);

        if($resLcQuota['code'] != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $this->returnInfo($resLcQuota);
    }

    public function checkParamNoLc($NoLcQuotaApply){
        $data = $results = array();
        if(isset($NoLcQuotaApply['buyer_no']) && !empty($NoLcQuotaApply['buyer_no'])){
            $data['corpSerialNo'] = $NoLcQuotaApply['buyer_no'];//流水号(企业内部非LC限额申请唯一标识)
        } else {
            $results['code'] = -101;
            $results['message'] = '[buyer_no]采购商编号缺失!';
        }
        if(isset($NoLcQuotaApply['clientNo']) && !empty($NoLcQuotaApply['clientNo'])){
            $data['clientNo'] = $NoLcQuotaApply['buyer_no'];//企业标识
        }

        if(isset($NoLcQuotaApply['policyNo']) && !empty($NoLcQuotaApply['policyNo'])){
            $data['policyNo'] = $NoLcQuotaApply['policyNo'];//易瑞保单号，固定且唯一
        } else {
            $results['code'] = -101;
            $results['message'] = '[policyNo]易瑞保单号缺失!';
        }
        if(isset($NoLcQuotaApply['sinosureBuyerNo']) && !empty($NoLcQuotaApply['sinosureBuyerNo'])){
            $data['sinosureBuyerNo'] = $NoLcQuotaApply['sinosureBuyerNo'];//中国信保买方代码
        } else {//---为空,以下必填
            if(isset($NoLcQuotaApply['corpBuyerNo']) && !empty($NoLcQuotaApply['corpBuyerNo'])){
                $data['corpBuyerNo'] = $NoLcQuotaApply['corpBuyerNo'];//企业买方代码
            } else {
                $results['code'] = -101;
                $results['message'] = '[corpBuyerNo]企业买方代码缺失!';
            }
            if(isset($NoLcQuotaApply['buyerChnName']) && !empty($NoLcQuotaApply['buyerChnName'])){
                $data['buyerChnName'] = $NoLcQuotaApply['buyerChnName'];//买方中文名称
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerChnName]买方中文名称缺失!';
            }
            if(isset($NoLcQuotaApply['buyerEngName ']) && !empty($NoLcQuotaApply['buyerEngName '])){
                $data['buyerEngName '] = $NoLcQuotaApply['buyerEngName '];//买方英文名称
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerEngName ]买方英文名称缺失!';
            }
            if(isset($NoLcQuotaApply['buyerCountryCode']) && !empty($NoLcQuotaApply['buyerCountryCode'])){
                $data['buyerCountryCode'] = $NoLcQuotaApply['buyerCountryCode'];//买方国家代码
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerCountryCode]买方国家代码缺失!';
            }
            if(isset($NoLcQuotaApply['buyerEngAddress']) && !empty($NoLcQuotaApply['buyerEngAddress'])){
                $data['buyerEngAddress'] = $NoLcQuotaApply['buyerEngAddress'];//买方英文地址
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerEngAddress]买方英文地址缺失!';
            }
            if(isset($NoLcQuotaApply['buyerChnAddress']) && !empty($NoLcQuotaApply['buyerChnAddress'])){
                $data['buyerChnAddress'] = $NoLcQuotaApply['buyerChnAddress'];//买方中文地址
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerChnAddress]买方中文地址缺失!';
            }
        }
        if(isset($NoLcQuotaApply['contractPayMode']) && !empty($NoLcQuotaApply['contractPayMode'])){
            $data['contractPayMode'] = $NoLcQuotaApply['contractPayMode'];//合同支付方式 LC/DP/OA/DA
        } else {
            $results['code'] = -101;
            $results['message'] = '[contractPayMode]合同支付方式缺失!';
        }
        if(isset($NoLcQuotaApply['payTermApply']) && !empty($NoLcQuotaApply['payTermApply'])){
            $data['payTermApply'] = $NoLcQuotaApply['payTermApply'];//申请信用期限
        } else {
            $results['code'] = -101;
            $results['message'] = '[payTermApply]申请信用期限缺失!';
        }
        if(isset($NoLcQuotaApply['quotaSumApply']) && !empty($NoLcQuotaApply['quotaSumApply'])){
            $data['quotaSumApply'] = $NoLcQuotaApply['quotaSumApply'];//申请信用限额
        } else {
            $results['code'] = -101;
            $results['message'] = '[quotaSumApply]申请信用限额缺失!';
        }
        if(isset($NoLcQuotaApply['orderSum']) && !empty($NoLcQuotaApply['orderSum'])){
            $data['orderSum'] = $NoLcQuotaApply['orderSum'];//当前在手订单金额
        } else {
            $results['code'] = -101;
            $results['message'] = '[orderSum]当前在手订单金额缺失!';
        }
        if(isset($NoLcQuotaApply['tradeNameCode']) && !empty($NoLcQuotaApply['tradeNameCode'])){
            $data['tradeNameCode'] = $NoLcQuotaApply['tradeNameCode'];//出口商品类别代码
        } else {
            $results['code'] = -101;
            $results['message'] = '[tradeNameCode]出口商品类别代码缺失!';
        }
        if (isset($NoLcQuotaApply['tradeElseName']) && !empty($NoLcQuotaApply['tradeElseName'])) {
            $data['tradeElseName'] = $NoLcQuotaApply['tradeElseName'];//商品名称
        }else{
            $results['code'] = -101;
            $results['message'] = '[tradeElseName]商品名称缺失!';
        }
        if(isset($NoLcQuotaApply['ifHistTrade'])){
            $data['ifHistTrade'] = $NoLcQuotaApply['ifHistTrade'];//是否有历史交易
        } else {
            $results['code'] = -101;
            $results['message'] = '[ifHistTrade]是否有历史交易缺失!';
        }
        if(isset($NoLcQuotaApply['earlyTradeYear']) && !empty($NoLcQuotaApply['earlyTradeYear'])){
            $data['earlyTradeYear'] = $NoLcQuotaApply['earlyTradeYear'];//最早成交年份
        } else {
            $results['code'] = -101;
            $results['message'] = '[earlyTradeYear]最早成交年份缺失!';
        }
        if(isset($NoLcQuotaApply['startDebtYear']) && !empty($NoLcQuotaApply['startDebtYear'])){
            $data['startDebtYear'] = $NoLcQuotaApply['startDebtYear'];//最早放账年份
        } else {
            $results['code'] = -101;
            $results['message'] = '[startDebtYear]最早放账年份缺失!';
        }
        if(isset($NoLcQuotaApply['declaration']) && !empty($NoLcQuotaApply['declaration'])){
            $data['declaration'] = 1;//被保险人声明(只能为1)
        }

        if(isset($NoLcQuotaApply['ifhavetradefinancing'])){
            $data['ifhavetradefinancing'] = intval($NoLcQuotaApply['ifhavetradefinancing']);//在本信用限额项下是否有贸易融资需求-->>是：1 否：0
        } else {
            $data['ifhavetradefinancing'] = 0;//默认为0
//            $results['code'] = -101;
//            $results['message'] = '[ifhavetradefinancing]贸易融资需求缺失!';
        }
        if(isset($NoLcQuotaApply['ifhaverelation'])){
            $data['ifhaverelation'] = intval($NoLcQuotaApply['ifhaverelation']);//被保险人及共保人、关联公司、代理人项下是否与买方存在关联关系-->>是：1 否：0
            if($data['ifhaverelation'] == 1){
                if(isset($NoLcQuotaApply['relationdetail']) && !empty($NoLcQuotaApply['relationdetail'])){
                    $data['relationdetail'] = $NoLcQuotaApply['relationdetail'];//具体关联为1时,不能为空
                } else {
                    $results['code'] = -101;
                    $results['message'] = '[relationdetail]具体关联情况缺失!';
                }
            }
        } else {
            $data['ifhaverelation'] = 0;//默认为0
//            $results['code'] = -101;
//            $results['message'] = '[ifhaverelation]关联关系缺失!';
        }
        if(isset($NoLcQuotaApply['issamewithcontract'])){
            $data['issamewithcontract'] = intval($NoLcQuotaApply['issamewithcontract']);//被保险人与买方历史交易记录中付款人是否与合同买方一致-->是：1 否：0
        } else {
            $data['issamewithcontract'] = 1;//默认为1
//            $results['code'] = -101;
//            $results['message'] = '[issamewithcontract]付款人是否与合同买方是否一致缺失!';
        }
        if(isset($NoLcQuotaApply['swift_code'])){
            $data['bank_swift'] = $NoLcQuotaApply['swift_code'];
        }
        if($results){
            jsonReturn($results);
        }
        return $data;
    }

    public function checkParamLc($LcQuotaApply){
        $data = $results = array();
        if(isset($LcQuotaApply['buyer_no']) && !empty($LcQuotaApply['buyer_no'])){
            $data['corpSerialNo'] = $LcQuotaApply['buyer_no'];//流水号(企业内部非LC限额申请唯一标识)
        } else {
            $results['code'] = -101;
            $results['message'] = '[buyer_no]采购商编号缺失!';
        }
        if(isset($LcQuotaApply['clientNo']) && !empty($LcQuotaApply['clientNo'])){
            $data['clientNo'] = $LcQuotaApply['buyer_no'];//企业标识
        }

        if(isset($LcQuotaApply['policyNo']) && !empty($LcQuotaApply['policyNo'])){
            $data['policyNo'] = $LcQuotaApply['policyNo'];//易瑞保单号，固定且唯一
        } else {
            $results['code'] = -101;
            $results['message'] = '[policyNo]易瑞保单号缺失!';
        }
        if(isset($LcQuotaApply['sinosureBuyerNo']) && !empty($LcQuotaApply['sinosureBuyerNo'])){
            $data['sinosureBuyerNo'] = $LcQuotaApply['sinosureBuyerNo'];//中国信保买方代码
        } else {//---为空,以下必填
            if(isset($LcQuotaApply['corpBuyerNo']) && !empty($LcQuotaApply['corpBuyerNo'])){
                $data['corpBuyerNo'] = $LcQuotaApply['corpBuyerNo'];//企业买方代码
            } else {
                $results['code'] = -101;
                $results['message'] = '[corpBuyerNo]企业买方代码缺失!';
            }
            if(isset($LcQuotaApply['buyerChnName']) && !empty($LcQuotaApply['buyerChnName'])){
                $data['buyerChnName'] = $LcQuotaApply['buyerChnName'];//买方中文名称
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerChnName]买方中文名称缺失!';
            }
            if(isset($LcQuotaApply['buyerEngName ']) && !empty($LcQuotaApply['buyerEngName '])){
                $data['buyerEngName '] = $LcQuotaApply['buyerEngName '];//买方英文名称
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerEngName ]买方英文名称缺失!';
            }
            if(isset($LcQuotaApply['buyerCountryCode']) && !empty($LcQuotaApply['buyerCountryCode'])){
                $data['buyerCountryCode'] = $LcQuotaApply['buyerCountryCode'];//买方国家代码
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerCountryCode]买方国家代码缺失!';
            }
            if(isset($LcQuotaApply['buyerEngAddress']) && !empty($LcQuotaApply['buyerEngAddress'])){
                $data['buyerEngAddress'] = $LcQuotaApply['buyerEngAddress'];//买方英文地址
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerEngAddress]买方英文地址缺失!';
            }
            if(isset($LcQuotaApply['buyerChnAddress']) && !empty($LcQuotaApply['buyerChnAddress'])){
                $data['buyerChnAddress'] = $LcQuotaApply['buyerChnAddress'];//买方中文地址
            } else {
                $results['code'] = -101;
                $results['message'] = '[buyerChnAddress]买方中文地址缺失!';
            }
        }
        if(isset($LcQuotaApply['ifRepeat'])){
            $data['ifRepeat'] = $LcQuotaApply['ifRepeat'];//是否循环-->>是：1 否：0
        } else {
            $results['code'] = -101;
            $results['message'] = '[ifRepeat]是否循环缺失!';
        }
        if(isset($LcQuotaApply['payTermApply']) && !empty($LcQuotaApply['payTermApply'])){
            $data['payTermApply'] = $LcQuotaApply['payTermApply'];//LC信用期限
        } else {
            $results['code'] = -101;
            $results['message'] = '[payTermApply]LC信用期限缺失!';
        }
        if(isset($LcQuotaApply['quotaSumApply']) && !empty($LcQuotaApply['quotaSumApply'])){
            $data['quotaSumApply'] = $LcQuotaApply['quotaSumApply'];//LC信用金额
        } else {
            $results['code'] = -101;
            $results['message'] = '[quotaSumApply]LC信用金额缺失!';
        }
        if(isset($LcQuotaApply['lcSum']) && !empty($LcQuotaApply['lcSum'])){
            $data['lcSum'] = $LcQuotaApply['lcSum'];//信用证金额
        } else {
            $results['code'] = -101;
            $results['message'] = '[lcSum]信用证金额缺失!';
        }
        if(isset($LcQuotaApply['lastLadeDate']) && !empty($LcQuotaApply['lastLadeDate'])){
            $data['lastLadeDate'] = $LcQuotaApply['lastLadeDate'];//最迟装船日
        } else {
            $results['code'] = -101;
            $results['message'] = '[lastLadeDate]最迟装船日缺失!';
        }
        if(isset($LcQuotaApply['goodsCode']) && !empty($LcQuotaApply['goodsCode'])){
            $data['goodsCode'] = $LcQuotaApply['goodsCode'];//出口商品类别代码
        } else {
            $results['code'] = -101;
            $results['message'] = '[goodsCode]出口商品类别代码缺失!';
        }
        if (isset($LcQuotaApply['elsegoodsName']) && !empty($LcQuotaApply['elsegoodsName'])) {
            $data['elsegoodsName'] = $LcQuotaApply['elsegoodsName'];//商品名称
        }else{
            $results['code'] = -101;
            $results['message'] = '[elsegoodsName]商品名称缺失!';
        }

        if(isset($LcQuotaApply['openBankSwift']) && !empty($LcQuotaApply['openBankSwift'])){
            $data['openBankSwift'] = $LcQuotaApply['openBankSwift'];//信保开证行SWIFT
        } else {
            if(isset($LcQuotaApply['corpOpenBankNo']) && !empty($LcQuotaApply['corpOpenBankNo'])){
                $data['corpOpenBankNo'] = $LcQuotaApply['corpOpenBankNo'];//开证行企业内部编码
            } else {
                $results['code'] = -101;
                $results['message'] = '[corpOpenBankNo]开证行企业内部编码缺失!';
            }
            if(isset($LcQuotaApply['openBankChnName']) && !empty($LcQuotaApply['openBankChnName'])){
                $data['openBankChnName'] = $LcQuotaApply['openBankChnName'];//开证行中文名称
            } else {
                $results['code'] = -101;
                $results['message'] = '[openBankChnName]开证行中文名称缺失!';
            }
            if(isset($LcQuotaApply['openBankEngName']) && !empty($LcQuotaApply['openBankEngName'])){
                $data['openBankEngName'] = $LcQuotaApply['openBankEngName'];//开证行英文名称
            } else {
                $results['code'] = -101;
                $results['message'] = '[openBankEngName]开证行英文名称缺失!';
            }
            if(isset($LcQuotaApply['openBankCountryCode']) && !empty($LcQuotaApply['openBankCountryCode'])){
                $data['openBankCountryCode'] = $LcQuotaApply['openBankCountryCode'];//开证行国家代码
            } else {
                $results['code'] = -101;
                $results['message'] = '[openBankCountryCode]开证行国家代码缺失!';
            }
            if(isset($LcQuotaApply['openBankAddress']) && !empty($LcQuotaApply['openBankAddress'])){
                $data['openBankAddress'] = $LcQuotaApply['openBankAddress'];//开证行地址（英文）
            } else {
                $results['code'] = -101;
                $results['message'] = '[openBankAddress]开证行地址（英文)缺失!';
            }
        }

        if(isset($LcQuotaApply['declaration']) && !empty($LcQuotaApply['declaration'])){
            $data['declaration'] = 1;//被保险人声明
        }

        if(isset($LcQuotaApply['ifhavetradefinancing'])){
            $data['ifhavetradefinancing'] = intval($LcQuotaApply['ifhavetradefinancing']);//在本信用限额项下是否有贸易融资需求-->>是：1 否：0
        } else {
            $data['ifhavetradefinancing'] = 0; //默认为0
//            $results['code'] = -101;
//            $results['message'] = '[ifhavetradefinancing]贸易融资需求缺失!';
        }
        if(isset($LcQuotaApply['ifhaverelation'])){
            $data['ifhaverelation'] = intval($LcQuotaApply['ifhaverelation']);//被保险人及共保人、关联公司、代理人项下是否与买方存在关联关系-->>是：1 否：0
            if($data['ifhaverelation'] == 1){
                if(isset($LcQuotaApply['relationdetail']) && !empty($LcQuotaApply['relationdetail'])){
                    $data['relationdetail'] = $LcQuotaApply['relationdetail'];//具体关联为1时,不能为空
                } else {
                    $results['code'] = -101;
                    $results['message'] = '[relationdetail]具体关联情况缺失!';
                }
            }
        } else {
            $data['ifhaverelation'] = 0; //默认为0
//            $results['code'] = -101;
//            $results['message'] = '[ifhaverelation]关联关系缺失!';
        }
        if(isset($LcQuotaApply['issamewithcontract'])){
            $data['issamewithcontract'] = intval($LcQuotaApply['issamewithcontract']);//被保险人与买方历史交易记录中付款人是否与合同买方一致-->是：1 否：0
        } else {
            $data['issamewithcontract'] = 1; //默认为1
//            $results['code'] = -101;
//            $results['message'] = '[issamewithcontract]付款人是否与合同买方是否一致缺失!';
        }
        if(isset($LcQuotaApply['swift_code'])){
            $data['bank_swift'] = $LcQuotaApply['swift_code'];
        }
        if($results){
            jsonReturn($results);
        }
        return $data;
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
}