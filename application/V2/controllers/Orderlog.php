<?php
/**
 * name: OrderLog.php
 * desc: 订单流程控制器
 * User: 张玉良
 * Date: 2017/9/12
 * Time: 17:10
 */
class OrderlogController extends PublicController{
    public function init() {
        parent::init();
    }

    /*
     * 流程日志列表
     * Author:张玉良
     */

    public function getListAction() {
        $OrderLog = new OrderLogModel();
        $where = $this->put_data;

        $results = $OrderLog->getList($where);
        $this->jsonReturn($results);
    }

    /*
     * 物流日志列表
     * Author:张玉良
     */

    public function getLogiListAction() {
        $OrderLog = new OrderLogModel();
        $where = $this->put_data;

        $results = $OrderLog->getLogiList($where);
        
        foreach ($results['data'] as &$res) {
            $orderLogList = $OrderLog->field('waybill_no')->where(['order_id' => $res['order_id'], 'log_group' => 'LOGISTICS', 'deleted_flag' => 'N'])->select();
            $waybillNo = [];
            foreach ($orderLogList as $orderLog) {
                if (trim($orderLog['waybill_no']) != '') $waybillNo[] = $orderLog['waybill_no'];
            }
            if ($waybillNo) $res['waybill_no'] = implode(',', $waybillNo);
        }
        
        $this->jsonReturn($results);
    }

    /*
     * 流程日志详情
     * Author:张玉良
     */

    public function getInfoAction() {
        $OrderLog = new OrderLogModel();
        $orderattach = new OrderAttachModel();
        $where = $this->put_data;
        $results = $OrderLog->getInfo($where);
        if($results['code'] == 1) {
            //查找有没有附件
            $attachwhere['order_id'] = $results['data']['order_id'];
            $attachwhere['attach_group'] = $results['data']['Log_group'];
            $attachwhere['log_id'] = $results['data']['id'];
            $attach = $orderattach->getlist($attachwhere);
            if($attach['code'] == 1) {
                $results['data']['attach_array'] = $attach['data'];
            }
            $this->jsonReturn($results);
        }else{

            $this->jsonReturn($results);
        }
    }

    /*
     * 添加流程日志
     * Author:张玉良
     */

    public function addAction() {
        $OrderLog = new OrderLogModel();
        $order_model = new OrderModel();
        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];
        $where = ['id' => $data['order_id'], 'deleted_flag' => 'N'];
        $logWhere = ['order_id' => $data['order_id'], 'deleted_flag' => 'N'];

        $OrderLog->startTrans();
        if($data['log_group']=="CREDIT"&&isset($data["order_id"])&&$data["order_id"]) {
            $order_info = $order_model->where($where)->find();
            if($order_info){
                if($order_info['buyer_id']){
                    $buyer_model = new BuyerModel();
                    $buyer_info = $buyer_model->where(['id'=>$order_info['buyer_id']])->find();
                    if($buyer_info){
                        if($buyer_info['line_of_credit']<=0){
                            $results['code'] = '-101';
                            $results['message'] = '该采购商未开通信保或者信保审核未通过，无法授信!';
                            $this->jsonReturn($results);
                        }
                        if($data['type']=="REFUND"){
                            $buyer_info['credit_available']=$buyer_info['credit_available']+$data['amount'];
                        }else{
                            $buyer_info['credit_available']=$buyer_info['credit_available'] - $data['amount'];
                            if($buyer_info['credit_available']<0){
                                $results['code'] = '-101';
                                $results['message'] = '可用余额已不足支付，授信使用失败!';
                                $this->jsonReturn($results);
                            }
                        }
                        if($buyer_info['line_of_credit']>=$buyer_info['credit_available']) {
                            $buyer_model->where(['id' => $order_info['buyer_id']])->setField(['credit_available' => $buyer_info['credit_available']]);
                        }else{
                            $results['code'] = '-101';
                            $results['message'] = '还款后的总额度，大于授信额度请认真核对!';
                            $this->jsonReturn($results);
                        }
                    }else{
                        $results['code'] = '-101';
                        $results['message'] = '没有获取采购商信息，无法授信!';
                        $this->jsonReturn($results);
                    }
                }else{
                    $results['code'] = '-101';
                    $results['message'] = '订单没有采购商，无法授信!';
                    $this->jsonReturn($results);
                }
            }else{
                $results['code'] = '-101';
                $results['message'] = '修改失败,请输入正确的订单号!';
                $this->jsonReturn($results);
            }
        }
        if($data['log_group'] == 'OUTBOUND') {
            $hasOut = $OrderLog->where(array_merge($logWhere, ['log_group' => 'OUTBOUND']))->getField('id');
            if (!$hasOut) {
                $order_model->where($where)->setField(['show_status'=>'OUTGOING']);
            }
        }
        if($data['log_group'] == 'LOGISTICS') {
            $hasLogi = $OrderLog->where(array_merge($logWhere, ['log_group' => 'LOGISTICS']))->getField('id');
            if (!$hasLogi) {
                $order_model->where($where)->setField(['show_status'=>'DISPATCHED']);
            }
        }
        $results = $OrderLog->addData($data);
        if($data['log_group']=="COLLECTION"&&isset($data["order_id"])&&$data["order_id"]) {
            $order_model->where($where)->setField(['pay_status'=>'PARTPAY']);
        }
        if($results['code'] == 1){
            //如果有附件，添加附件
            if(!empty($data['attach_array'])){
                $orderattach = new OrderAttachModel();
                $data['log_id'] = $results['data'];

                $rs = $orderattach->addAllData($data);
                if($rs['code'] == 1){
                    $OrderLog->commit();
                    $this->jsonReturn($results);
                }else{
                    $OrderLog->rollback();
                    $this->jsonReturn($rs);
                }
            }else{
                $OrderLog->commit();
                $this->jsonReturn($results);
            }
        }else{
            $this->jsonReturn($results);
        }
    }

    /*
     * 修改流程日志
     * Author:张玉良
     */

    public function updateAction() {
        $OrderLog = new OrderLogModel();

        $data = $this->put_data;

        $OrderLog->startTrans();
        $results = $OrderLog->updateData($data);
        if($results['code'] == 1){
            //如果有附件，添加附件
            if(!empty($data['attach_array'])){
                $orderattach = new OrderAttachModel();
                $data['log_id'] = $data['id'];
                $data['created_by'] = $this->user['id'];

                $rs = $orderattach->addAllData($data);
                if($rs['code'] == 1){
                    $OrderLog->commit();
                    $this->jsonReturn($results);
                }else{
                    $OrderLog->rollback();
                    $this->jsonReturn($rs);
                }
            }else{
                $OrderLog->commit();
                $this->jsonReturn($results);
            }
        }else{
            $this->jsonReturn($results);
        }
    }

    /*
     * 添加授信流程日志
     * Author:张玉良
     */

    public function addCreditAction() {
        $OrderLog = new OrderLogModel();

        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        if(!empty($data['buyer_id']) && !empty($data['amount'])){
            $buyer = new BuyerModel();
            //查找授信额度和可用额度
            $buyerinfo = $buyer->field('line_of_credit,credit_available,credit_cur_bn')->where('id='.$data['buyer_id'])->find();

            if(!empty($buyerinfo['line_of_credit']) && $buyerinfo['line_of_credit']>0){

                if($data['type'] == 'SPENDING'){
                    $creditavailable = $buyerinfo['credit_available'] - $data['amount'];  //支出
                }else{
                    $creditavailable = $buyerinfo['credit_available'] + $data['amount'];  //还款
                }

                if($creditavailable>0 || $buyerinfo['line_of_credit']>$creditavailable) {
                    $OrderLog->startTrans();
                    $results = $OrderLog->addData($data);

                    if($results['code'] == 1){
                        $re = $buyer->where('id='.$data['buyer_id'])->save(['credit_available' => $creditavailable]);
                        if($re){
                            $OrderLog->commit();
                            $this->jsonReturn($results);
                        }else{
                            $OrderLog->rollback();
                            $results['code'] = '-101';
                            $results['message'] = '修改失败!';
                            $this->jsonReturn($results);
                        }
                    }else{
                        $OrderLog->rollback();
                        $this->jsonReturn($results);
                    }

                }else{

                    $results['code'] = '-103';
                    $results['message'] = '可用授信额度不够!';
                    $this->jsonReturn($results);
                }


            }else{
                $results['code'] = '-103';
                $results['message'] = '没有可用授信额度!';
                $this->jsonReturn($results);
            }
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有客户ID或金额!';
            $this->jsonReturn($results);
        }
    }

    /*
     * 修改授信流程日志
     * Author:张玉良
     */

    public function updateCreditAction() {
        $OrderLog = new OrderLogModel();

        $data = $this->put_data;

        if(!empty($data['buyer_id']) && !empty($data['id'] )){
            if(empty($data['type']) || empty($data['amount'])){
                $results['code'] = '-103';
                $results['message'] = '授信类型或授信金为空，请从新填写!';
                $this->jsonReturn($results);
            }
            //查找日志记录信息
            $info = $OrderLog->getInfo($data);

            $buyer = new BuyerModel();
            //查找授信额度和可用额度
            $buyerinfo = $buyer->field('line_of_credit,credit_available,credit_cur_bn')->where('id='.$data['buyer_id'])->find();


            if($info['data']['type'] == $data['type']){ //如果类型相同
                if($data['type'] == 'SPENDING'){    //支出
                    $creditavailable = $buyerinfo['credit_available'] + $info['data']['amount'];
                    $creditavailable = $creditavailable - $data['amount'];
                }else{  //还款
                    $creditavailable = $buyerinfo['credit_available'] - $info['data']['amount'];
                    $creditavailable = $creditavailable + $data['amount'];
                }
            }else{  //如果类型不同
                if($data['type'] == 'SPENDING'){    //支出
                    $creditavailable = $buyerinfo['credit_available'] - $info['data']['amount'];
                    $creditavailable = $creditavailable - $data['amount'];
                }else{  //还款
                    $creditavailable = $buyerinfo['credit_available'] + $info['data']['amount'];
                    $creditavailable = $creditavailable + $data['amount'];
                }
            }

            if($creditavailable>0 || $buyerinfo['line_of_credit']>$creditavailable) {
                $OrderLog->startTrans();
                $results = $OrderLog->updateData($data);

                if($results['code'] == 1){
                    $re = $buyer->where('id='.$data['buyer_id'])->save(['credit_available' => $creditavailable]);
                    if($re){
                        $OrderLog->commit();
                        $this->jsonReturn($results);
                    }else{
                        $OrderLog->rollback();
                        $results['code'] = '-101';
                        $results['message'] = '修改失败!';
                        $this->jsonReturn($results);
                    }
                }else{
                    $OrderLog->rollback();
                    $this->jsonReturn($results);
                }
            }else{
                $results['code'] = '-103';
                $results['message'] = '可用授信额度不够!';
                $this->jsonReturn($results);
            }
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有客户ID或日志ID!';
            $this->jsonReturn($results);
        }
    }

    /*
     * 删除流程日志
     * Author:张玉良
     */

    public function deleteAction() {
        $OrderLog = new OrderLogModel();
        $where = $this->put_data;
        $results = $OrderLog->deleteData($where);
        $this->jsonReturn($results);
    }

    /*
     * 删除授信流程日志
     * Author:张玉良
     */

    public function deleteCreditAction() {
        $OrderLog = new OrderLogModel();
        $where = $this->put_data;

        $results = $OrderLog->deleteData($where);

        $this->jsonReturn($results);
    }

    /*
     * 工作流程附件列表
     * Author:张玉良
     */
    public function getAttachListAction() {
        $orderattach = new OrderAttachModel();
        $where = $this->put_data;

        $results = $orderattach->getList($where);

        $this->jsonReturn($results);
    }

    /*
     * 删除工作流程附件
     * Author:张玉良
     */
    public function deleteAttachAction() {
        $orderattach = new OrderAttachModel();
        $where = $this->put_data;

        $results = $orderattach->deleteData($where);

        $this->jsonReturn($results);

    }

    /*
     * 发货地址列表
     * Author:张玉良
     */
    public function getAddressListAction() {
        $orderaddress = new OrderAddressModel();
        $where = $this->put_data;

        $results = $orderaddress->getList($where);

        $this->jsonReturn($results);
    }

    /*
     * 添加发货地址
     * Author:张玉良
     */
    public function addAddressAction() {
        $orderaddress = new OrderAddressModel();
        $where = $this->put_data;

        if(!empty($where['log_id']) && !empty($where['order_id'])){
            $re = $orderaddress->where('order_id='.$where['order_id'].' and log_id='.$where['log_id'])->save(['deleted_flag' => 'Y']);
            $results = $orderaddress->addData($where);
            if($results!==false){
                $this->jsonReturn($results);
            }else{
                $results['code'] = '-101';
                $results['message'] = '修改失败!';
                $this->jsonReturn($results);
            }

        }else{
            $results['code'] = '-103';
            $results['message'] = '没有订单ID或流程ID!';
            $this->jsonReturn($results);
        }
    }

    /*
     * 工作流程附件列表
     * Author:张玉良
     */
    public function getLogListAction() {
        $orderlog = new OrderLogModel();
        $where = $this->put_data;

        $results = $orderlog->getBuyerLogList($where);

        $this->jsonReturn($results);
    }
}