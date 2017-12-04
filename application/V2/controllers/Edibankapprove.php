<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:40
 */
class EdiBankApproveController extends PublicController{

    public function init(){
        parent::init();
    }

    /**
     *
     *买银行代码申请反馈
     * @author klp
     */
    public function BankApporovelAction(){

        $SinoSure = new Edi();
        $resBuyer = $SinoSure->EdiBankCodeApprove();
        //print_r($resBuyer);die;
        if($resBuyer && !isset($resBuyer['code'])){
            foreach($resBuyer as $item){
                var_dump($resBuyer);die;
                //1.判断,保存到数据库
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
                jsonReturn($data);
            }
        } elseif ($resBuyer && isset($resBuyer['code'])){
            jsonReturn('',MSG::MSG_FAILED,'参数错误!');
        } else {
            jsonReturn('',MSG::MSG_FAILED,'no data!');
        }
       exit;
    }

}