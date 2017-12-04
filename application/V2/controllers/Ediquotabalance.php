<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 12:00
 */
class EdiQuotaBalanceController extends PublicController{

    public function init()
    {
        parent::init();
    }

    /**
     *
     *出口险-限额余额查询V2(新版)
     * @author klp
     */
    public function QuotaBalanceInfoAction(){

        $SinoSure = new Edi();
        $resQuotaBalance = $SinoSure->QuotaBalanceInfoByPolicyNo();
//        var_dump($resQuotaBalance);die;
        if($resQuotaBalance && !isset($resQuotaBalance['code'])){
//            var_dump($resQuotaBalance);die;
            foreach($resQuotaBalance as $item){
                //1.判断
                $data = [
                    'approveFlag'=> $item['approveFlag'],//审批标志 1通过  0-申请退回/不通过


                ];
                $where = array( 'buyer_no'=> $item['BuyerInfo']['corpSerialNo']);
                $buyerModel = new BuyerModel();
                jsonReturn($data);
            }
        } elseif ($resQuotaBalance && isset($resQuotaBalance['code'])){
            jsonReturn('',MSG::MSG_FAILED,'参数错误!');
        } else {
            jsonReturn('',MSG::MSG_FAILED,'no data!');
        }
        exit;
    }
}