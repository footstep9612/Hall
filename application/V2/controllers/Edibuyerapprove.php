<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/21
 * Time: 11:35
 */
class EdiBuyerApproveController extends PublicController {

    public function init() {
        parent::init();
    }
    /**
     *
     *买家代码申请反馈
     * @author klp
     */
    public function BuyerApporovelAction(){

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