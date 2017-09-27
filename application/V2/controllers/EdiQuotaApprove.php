<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:55
 */
class EdiQuotaApproveController extends PublicController{

    public function init()
    {
        parent::init();
    }

    /**
     *
     *出口险-限额批复通知
     * @author klp
     */
    public function QuotaApprovelAction(){

        $SinoSure = new Edi();
        $resQuotaApprove = $SinoSure->EdiQuotaApproveInfo();//print_r($resBuyer);die;
        if($resQuotaApprove && !isset($resQuotaApprove['code'])){
//            var_dump($resQuotaApprove);die;
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
                jsonReturn($data);
            }
        } elseif ($resQuotaApprove && isset($resQuotaApprove['code'])){
            jsonReturn('',MSG::MSG_FAILED,'参数错误!');
        } else {
            jsonReturn('',MSG::MSG_FAILED,'no data!');
        }
        exit;
    }
}