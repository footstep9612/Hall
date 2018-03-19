<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 15:16
 */
class BuyerCreditLogModel extends PublicModel{
    protected $tableName = 'buyer_credit_log';
    protected $dbName = 'buyer_credit'; //数据库名称

    public function __construct($str = ''){
        parent::__construct($str = '');
    }
    //空-未申请；APPROVING-待易瑞审核；REJECTED-易瑞驳回；APPROVED-易瑞审核通过；
    const STATUS_APPROVING = 'APPROVING'; //待审核
    const STATUS_REJECTED = 'REJECTED'; //驳回
    const STATUS_APPROVED = 'APPROVED'; //审核通过
    const STATUS_ERUI = 'ERUI'; //ERUI


    /**
     * 获取申请日志列表   银行或企业信息--银行sign:1;企业sign:2
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {

        $where = $this->_getCondition($condition);
        $condition['current_no'] = $condition['currentPage'];

        list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,buyer_no,name,address,bank_name,bank_address,in_status,sign,in_remarks,agent_by,agent_at,checked_by,checked_at,out_status,out_remarks';
        return $this->field($field)
            ->where($where)
            ->limit($start_no, $pagesize)
            ->order('id desc')
            ->select();
    }

    /**
     *获取定制数量
     * @param array $condition
     * @author  klp
     */
    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
    }

    /**
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getCondition($condition = []) {
        $where = [];
        if (isset($condition['buyer_no']) && $condition['buyer_no']) {
            $where['buyer_no'] = $condition['buyer_no'];                  //客户编号
        }
        if (isset($condition['sign']) && $condition['sign']) {
            $where['sign'] = intval($condition['sign']);         //银行或企业信息--银行:1;企业:2
        }
        $where['name'] = array('neq', '');
        $where['bank_name'] = array('neq', '');
        return $where;
    }


    /**
     * 新建信息
     */
    public function create_data($data) {

        if(isset($data['buyer_no']) && !empty($data['buyer_no'])){
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if(isset($data['name']) && !empty($data['name'])){
            $dataInfo['name'] = trim($data['name']);
        }
        if(isset($data['bank_name']) && !empty($data['bank_name'])){
            $dataInfo['bank_name'] = trim($data['bank_name']);
        }
        if(isset($data['address']) && !empty($data['address'])){
            $dataInfo['address'] = trim($data['address']);
        }
        if(isset($data['bank_address']) && !empty($data['bank_address'])){
            $dataInfo['bank_address'] = trim($data['bank_address']);
        }
        if(isset($data['in_status']) && !empty($data['in_status'])){
            $dataInfo['in_status'] = strtoupper($data['in_status']);
        }
        if(isset($data['out_status']) && !empty($data['out_status'])){
            $dataInfo['out_status'] = strtoupper($data['out_status']);
        }
        if(isset($data['nolc_apply']) && !empty($data['nolc_apply'])){
            $dataInfo['nolc_apply'] = trim($data['nolc_apply']);
        }
        if(isset($data['nolc_granted']) && !empty($data['nolc_granted'])){
            $dataInfo['nolc_granted'] = trim($data['nolc_granted']);
        }
        if(isset($data['deadline_deadline']) && !empty($data['deadline_deadline'])){
            $dataInfo['deadline_deadline'] = trim($data['deadline_deadline']);
        }
        if(isset($data['lc_apply']) && !empty($data['lc_apply'])){
            $dataInfo['lc_apply'] = trim($data['lc_apply']);
        }
        if(isset($data['lc_granted']) && !empty($data['lc_granted'])){
            $dataInfo['lc_granted'] = trim($data['lc_granted']);
        }
        if(isset($data['lc_deadline']) && !empty($data['lc_deadline'])){
            $dataInfo['lc_deadline'] = trim($data['lc_deadline']);
        }
        if(isset($data['credit_cur_bn']) && !empty($data['credit_cur_bn'])){
            $dataInfo['credit_cur_bn'] = trim($data['credit_cur_bn']);
        }
        if(isset($data['credit_apply_date']) && !empty($data['credit_apply_date'])){
            $dataInfo['credit_apply_date'] = trim($data['credit_apply_date']);
        }
        if(isset($data['in_remarks']) && !empty($data['in_remarks'])){
            $dataInfo['in_remarks'] = trim($data['in_remarks']);
        }
        if(isset($data['agent_by']) && !empty($data['agent_by'])){
            $dataInfo['agent_by'] = trim($data['agent_by']);
        }
        if(isset($data['agent_at']) && !empty($data['agent_at'])){
            $dataInfo['agent_at'] = trim($data['agent_at']);
        }
        if(isset($data['checked_by']) && !empty($data['checked_by'])){
            $dataInfo['checked_by'] = trim($data['checked_by']);
        }
        if(isset($data['checked_at']) && !empty($data['checked_at'])){
            $dataInfo['checked_at'] = trim($data['checked_at']);
        }
        if(isset($data['credit_at']) && !empty($data['credit_at'])){
            $dataInfo['credit_at'] = trim($data['credit_at']);
        }
        if(isset($data['credit_invalid_date']) && !empty($data['credit_invalid_date'])){
            $dataInfo['credit_invalid_date'] = trim($data['credit_invalid_date']);
        }
        if(isset($data['out_remarks']) && !empty($data['out_remarks'])){
            $dataInfo['out_remarks'] = trim($data['out_remarks']);
        }
        if(isset($data['approved_by']) && !empty($data['approved_by'])){
            $dataInfo['approved_by'] = trim($data['approved_by']);
        }
        if(isset($data['approved_at']) && !empty($data['approved_at'])){
            $dataInfo['approved_at'] = trim($data['approved_at']);
        }
        if(isset($data['sign']) && !empty($data['sign'])){
            $dataInfo['sign'] = intval($data['sign']);
        }
        $result = $this->add($this->create($dataInfo));
        if($result){
            return true;
        }
        return false;
    }


    /**
     * 更新信息
     */
    public function update_data($data) {

        if(isset($data['buyer_no']) && !empty($data['buyer_no'])){
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if(isset($data['name']) && !empty($data['name'])){
            $dataInfo['name'] = trim($data['name']);
        }
        if(isset($data['bank_name']) && !empty($data['bank_name'])){
            $dataInfo['bank_name'] = trim($data['bank_name']);
        }
        if(isset($data['address']) && !empty($data['address'])){
            $dataInfo['address'] = trim($data['address']);
        }
        if(isset($data['bank_address']) && !empty($data['bank_address'])){
            $dataInfo['bank_address'] = trim($data['bank_address']);
        }
        if(isset($data['in_status']) && !empty($data['in_status'])){
            $dataInfo['in_status'] = strtoupper($data['in_status']);
        }
        if(isset($data['out_status']) && !empty($data['out_status'])){
            $dataInfo['out_status'] = strtoupper($data['out_status']);
        }
        if(isset($data['nolc_apply']) && !empty($data['nolc_apply'])){
            $dataInfo['nolc_apply'] = trim($data['nolc_apply']);
        }
        if(isset($data['nolc_granted']) && !empty($data['nolc_granted'])){
            $dataInfo['nolc_granted'] = trim($data['nolc_granted']);
        }
        if(isset($data['deadline_deadline']) && !empty($data['deadline_deadline'])){
            $dataInfo['deadline_deadline'] = trim($data['deadline_deadline']);
        }
        if(isset($data['lc_apply']) && !empty($data['lc_apply'])){
            $dataInfo['lc_apply'] = trim($data['lc_apply']);
        }
        if(isset($data['lc_granted']) && !empty($data['lc_granted'])){
            $dataInfo['lc_granted'] = trim($data['lc_granted']);
        }
        if(isset($data['lc_deadline']) && !empty($data['lc_deadline'])){
            $dataInfo['lc_deadline'] = trim($data['lc_deadline']);
        }
        if(isset($data['credit_cur_bn']) && !empty($data['credit_cur_bn'])){
            $dataInfo['credit_cur_bn'] = trim($data['credit_cur_bn']);
        }
        if(isset($data['credit_apply_date']) && !empty($data['credit_apply_date'])){
            $dataInfo['credit_apply_date'] = trim($data['credit_apply_date']);
        }
        if(isset($data['in_remarks']) && !empty($data['in_remarks'])){
            $dataInfo['in_remarks'] = trim($data['in_remarks']);
        }
        if(isset($data['agent_by']) && !empty($data['agent_by'])){
            $dataInfo['agent_by'] = trim($data['agent_by']);
        }
        if(isset($data['agent_at']) && !empty($data['agent_at'])){
            $dataInfo['agent_at'] = trim($data['agent_at']);
        }
        if(isset($data['checked_by']) && !empty($data['checked_by'])){
            $dataInfo['checked_by'] = trim($data['checked_by']);
        }
        if(isset($data['checked_at']) && !empty($data['checked_at'])){
            $dataInfo['checked_at'] = trim($data['checked_at']);
        }
        if(isset($data['credit_at']) && !empty($data['credit_at'])){
            $dataInfo['credit_at'] = trim($data['credit_at']);
        }
        if(isset($data['credit_invalid_date']) && !empty($data['credit_invalid_date'])){
            $dataInfo['credit_invalid_date'] = trim($data['credit_invalid_date']);
        }
        if(isset($data['out_remarks']) && !empty($data['out_remarks'])){
            $dataInfo['out_remarks'] = trim($data['out_remarks']);
        }
        if(isset($data['approved_by']) && !empty($data['approved_by'])){
            $dataInfo['approved_by'] = trim($data['approved_by']);
        }
        if(isset($data['approved_at']) && !empty($data['approved_at'])){
            $dataInfo['approved_at'] = trim($data['approved_at']);
        }
        if(isset($data['sign']) && !empty($data['sign'])){
            $dataInfo['sign'] = intval($data['sign']);
        }
        $res = $this->where(['buyer_no' => $dataInfo['buyer_no']])->save($this->create($dataInfo));
        if ($res !== false) {
            return true;
        }
        return false;
    }










    /**
     * 获取审核信息
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getInfo($user) {
//        $userInfo = getLoinInfo();
        $where=array();
        if(isset($user['buyer_id']) && !empty($user['buyer_id'])) {
            $where['buyer_id'] = $user['buyer_id'];
        } else{
            jsonReturn('','-1001','用户[buyer_id]不可以为空');
        }
        $field = 'buyer_id, credit_grantor, credit_apply, credit_granted, credit_cur_bn, credit_apply_date, in_status, in_remarks, agent_by, agent_at, checked_by, checked_at,  out_remarks, approved_by, approved_at';
        try {
            $result =  $this->field($field)->where($where)->find();
            return $result;
        } catch(Exception $e){
            return false;
        }
    }

    /**
     * 易瑞审核
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function checkCredit($checkInfo) {
        $condition = $this->checkParam($checkInfo);
        if(empty($condition)){
            return false;
        }
        $data = [];
        if(!empty($condition['id'])){
            $data['buyer_id'] = $condition['id'];
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
    protected function sinosureCredit($condition) {
//        $condition = $this->checkParam($checkInfo);
        if(empty($condition)){
            return false;
        }
        $data = [];
        if(!empty($condition['id'])){
            $data['buyer_id'] = $condition['id'];
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
        if(empty($data['id'])) {
            $results['code'] = '-1';
            $results['message'] = '[id]缺失';
        }
        //新状态可以补充
        if(isset($data['status_type'])) {
            switch ($data['status_type']) {
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
    /**
     * 采购商个人信息更新
     * @author klp
     */
   /* public function update_data($data, $where) {
        if (!empty($data['credit_granted'])) {
            $array_data['credit_granted'] = $data['credit_granted'];
        }
        if (!empty($data['in_remarks'])) {
            $array_data['in_remarks'] = $data['in_remarks'];
        }
        if (!empty($data['in_status'])) {
            $array_data['in_status'] = $data['in_status'];
        }
        if (!empty($data['checked_at'])) {
            $array_data['checked_at'] = $data['checked_at'];
        }
        if (!empty($data['approved_at'])) {
            $array_data['approved_at'] = $data['approved_at'];
        }
        if (!empty($data['checked_by'])) {
            $array_data['checked_by'] = $data['checked_by'];
        }
        if (isset($data['status'])) {
            $array_data['status'] = $data['status'];
        }
        return $this->where($where)->save($array_data);
    }*/
    /**
     *
     */
   /* public function create_data($data) {
        if (!empty($data['credit_granted'])) {
            $array_data['credit_granted'] = $data['credit_granted'];
        }
         if (!empty($data['buyer_id'])) {
             $array_data['buyer_id'] = $data['buyer_id'];
         }
        if (!empty($data['in_remarks'])) {
            $array_data['in_remarks'] = $data['in_remarks'];
        }
        if (!empty($data['in_status'])) {
            $array_data['in_status'] = $data['in_status'];
        }
        if (!empty($data['checked_at'])) {
            $array_data['checked_at'] = $data['checked_at'];
        }
        if (!empty($data['approved_at'])) {
            $array_data['approved_at'] = $data['approved_at'];
        }
        if (!empty($data['checked_by'])) {
            $array_data['checked_by'] = $data['checked_by'];
        }
        if (isset($data['status'])) {
            $array_data['status'] = $data['status'];
        }
        $array_data['credit_apply_date'] = date("Y-m-d H:i:s");
        return $this->add($array_data);
    }*/
}