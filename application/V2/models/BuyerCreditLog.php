<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 15:16
 */
class BuyerCreditLogModel extends PublicModel{
    protected $tableName = 'buyer_credit_log';
    protected $dbName = 'erui2_buyer'; //数据库名称

    public function __construct($str = ''){
        parent::__construct($str = '');
    }
    //空-未申请；APPROVING-待易瑞审核；REJECTED-易瑞驳回；APPROVED-易瑞审核通过；
    const STATUS_APPROVING = 'APPROVING'; //待审核
    const STATUS_REJECTED = 'REJECTED'; //驳回
    const STATUS_APPROVED = 'APPROVED'; //审核通过
    const STATUS_ERUI = 'ERUI'; //ERUI

    /**
     * 获取审核信息
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getInfo() {
        $userInfo = getLoinInfo();
        $where=array();
        if(!empty($userInfo['id'])){
            $where['buyer_id'] = $userInfo['id'];
        } else{
            jsonReturn('','-1001','用户[id]不可以为空');
        }
        $field = 'buyer_id, credit_grantor, credit_apply, credit_granted, credit_cur_bn, credit_apply_date, in_status, in_remarks, agent_by, agent_at, checked_by, checked_at, out_status, out_remarks, approved_by, approved_at';

        try {
            $result =  $this->field($field)->where($where)->select();
            return $result ? $result : array();
        } catch(Exception $e){
            return array();
        }
    }

    /**
     * 易瑞审核
     * @param mix $condition
     * @return mix
     * @author klp
     */
    protected function checkCredit($checkInfo) {
        $condition = $this->checkParam($checkInfo);
        if(empty($condition)){
            return false;
        }
        $data = [];
        if(!empty($condition['buyer_id'])){
            $data['buyer_id'] = $condition['buyer_id'];
        }
        if(!empty($condition['credit_grantor'])){
            $data['credit_grantor'] = $condition['credit_grantor'];
        } else{
            $data['credit_grantor'] = self::STATUS_ERUI;
        }
        if(!empty($condition['credit_apply'])){
            $data['credit_apply'] = $condition['credit_apply'];
        }
        if(!empty($condition['credit_cur_bn'])){
            $data['credit_cur_bn'] = $condition['credit_cur_bn'];
        }
        if(!empty($condition['in_status'])){
            $data['in_status'] = $condition['in_status'];
        }
        if(!empty($condition['in_remarks'])){
            $data['in_remarks'] = $condition['in_remarks'];
        }
        if(!empty($condition['checked_by'])){
            $data['checked_by'] = $condition['checked_by'];
        }
        $data['checked_at'] = date('Y-m-d H:i:s', time());

        $this->startTrans();
        try {
            //判断是新增审核结果还是重新审核,如果有buyer_id就是重新审核,反之为新增审核
            $result = $this->field('buyer_id')->where(['buyer_id' => $data['buyer_id']])->find();
            if($result){
                $res = $this->where(['buyer_id' => $data['buyer_id']])->save($data);
            } else {
                $res = $this->add($data);
            }
            if($res) {
                $this->commit();
                return $data['in_status'];
            } else {
                $this->rollback();
                return false;
            }
        } catch(Exception $e){
            $this->rollback();
            return false;
        }
    }

    /**
     * 信保审核
     * @param mix $condition
     * @return mix
     * @author klp
     */
    protected function sinosureCredit($checkInfo) {
        $condition = $this->checkParam($checkInfo);
        if(empty($condition)){
            return false;
        }
        $data = [];
        if(!empty($condition['buyer_id'])){
            $data['buyer_id'] = $condition['buyer_id'];
        }
        if(!empty($condition['credit_grantor'])){
            $data['credit_grantor'] = $condition['credit_grantor'];
        }
        if(!empty($condition['credit_granted'])){
            $data['credit_granted'] = $condition['credit_granted'];
        }
        if(!empty($condition['credit_cur_bn'])){
            $data['credit_cur_bn'] = $condition['credit_cur_bn'];
        }
        if(!empty($condition['out_remarks'])){
            $data['out_remarks'] = $condition['out_remarks'];
        }
        if(!empty($condition['approved_by'])){
            $data['approved_by'] = $condition['approved_by'];
        }
        if(!empty($condition['status_type'])){
            switch (strtoupper($data['status_type'])) {
                case 'approved':    //审核(通过)
                    $data['out_status'] = self::STATUS_APPROVED;
                    break;
                case 'rejected':    //审核(驳回)
                    $data['out_status'] = self::STATUS_REJECTED;
                    break;
            }
            unset($data['status_type']);
        }
        $data['approved_at'] = date('Y-m-d H:i:s', time());
        $this->startTrans();
        try {
            $res = $this->where(['buyer_id' => $data['buyer_id']])->save($data);
            if($res) {
                $this->commit();
                return $data['out_status'];
            } else {
                $this->rollback();
                return false;
            }
        } catch(Exception $e){
            $this->rollback();
            return false;
        }
    }

    /**
     * 参数校验,目前只测必须项
     * @author klp
     * @return array
     */
    public function checkParam($data){
        if(empty($data)) {
            return false;
        }
        $results = array();
        if(empty($data['buyer_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[buyer_id]缺失';
        }
        //新状态可以补充
        if(isset($data['status_type'])) {
            switch (strtoupper($data['status_type'])) {
                case 'approved':    //审核(通过)
                    $data['in_status'] = self::STATUS_APPROVED;
                    break;
                case 'rejected':    //审核(驳回)
                    $data['in_status'] = self::STATUS_REJECTED;
                    break;
            }
            unset($data['status_type']);
        } else{
            $results['code'] = '-1';
            $results['message'] = '[status_type]缺失';
        }
        if($results){
            jsonReturn($results);
        }
        return $data;
    }

}