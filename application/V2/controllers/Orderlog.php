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

        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        $OrderLog->startTrans();
        $results = $OrderLog->addData($data);

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


            if($info['type'] == $data['type']){ //如果类型相同
                if($data['type'] == 'SPENDING'){    //支出
                    $creditavailable = $buyerinfo['credit_available'] + $info['amount'];
                    $creditavailable = $creditavailable - $data['amount'];
                }else{  //还款
                    $creditavailable = $buyerinfo['credit_available'] - $info['amount'];
                    $creditavailable = $creditavailable + $data['amount'];
                }
            }else{  //如果类型不同
                if($data['type'] == 'SPENDING'){    //支出
                    $creditavailable = $buyerinfo['credit_available'] - $info['amount'];
                    $creditavailable = $creditavailable - $data['amount'];
                }else{  //还款
                    $creditavailable = $buyerinfo['credit_available'] + $info['amount'];
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

        if(!empty($where['buyer_id'])){
            $buyer = new BuyerModel();
            //查找授信可用额度
            $buyerinfo = $buyer->field('line_of_credit,credit_available,credit_cur_bn')->where('id='.$where['buyer_id'])->find();

            //查询删除的授信记录信息
            $creditinfo = $OrderLog->getInfo($where);
            //判断是
            if($creditinfo['type'] == 'SPENDING'){
                $credit_available = $buyerinfo['credit_available']+$creditinfo['amount'];  //支出就加回去
            }else{
                $credit_available = $buyerinfo['credit_available']-$creditinfo['amount'];  //还款就减去
            }

            $OrderLog->startTrans();
            $results = $OrderLog->deleteData($where);
            if($results['code'] == 1){
                $re = $buyer->where('id='.$where['buyer_id'])->save(['credit_available' => $credit_available]);
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
            $results['message'] = '没有客户ID!';
            $this->jsonReturn($results);
        }



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

            if($re){
                $results = $orderaddress->addData($where);

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
}