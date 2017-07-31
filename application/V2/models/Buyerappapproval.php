<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Buyerappapproval
 *
 * @author zhongyg
 */
class BuyerappapprovalModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer_app_approval';
    protected $dbName = 'erui_buyer';
    Protected $autoCheckFields = false;

    //APPROVED-通过；PARTLY-部分通过；REJECTED-未通过
    const STATUS_PARTLY = 'PARTLY'; //部分通过
    const STATUS_CHECKING = 'CHECKING'; //CHECKING-审核
    const STATUS_APPROVED = 'APPROVED'; //CHECKING-审核通过
    const STATUS_REJECTED = 'REJECTED'; //CHECKING-审核驳回

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 易瑞审核
     * @param mix $condition
     * @return mix
     * @author klp
     */
    protected function checkCredit($condition = []) {
        if(empty($condition)){
            return false;
        }
        $data = [];
        $data['lang'] = $condition['lang'] ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');
        if(isset($condition['serial_no'])){
            $data['buyer_app_serial_no'] = $condition['serial_no'];
        }else{
            JsonReturn('','-1001','[serial_no]不能为空');
        }
        if(isset($condition['approved_name'])){
            $data['approved_name'] = $condition['approved_name'];
        }else{
            JsonReturn('','-1003','[approved_name]不能为空');
        }
        if(isset($condition['approved_by'])){
            $data['approved_by'] = $condition['approved_by'];
        }
        if(isset($condition['remarks'])){
            $data['remarks'] = $condition['remarks'];
        }
        $data['approved_at'] = date('Y-m-d H:i:s', time());
        //新状态可以补充
        if(isset($condition['status_type'])) {
            switch (strtoupper($condition['status_type'])) {
                case 'approved':    //审核(通过)
                    $data['status'] = self::STATUS_APPROVED;
                    break;
                case 'rejected':    //审核(驳回)
                    $data['status'] = self::STATUS_REJECTED;
                    break;
//                case 'partly':    //审核(部分通过)
//                    $data['status'] = self::STATUS_PARTLY;
//                    break;
            }
        } else{
            JsonReturn('','-1003','[status_type]不能为空');
        }
        $this->startTrans();
        try {
            //判断是新增审核结果还是重新审核,如果有customer_id就是重新审核,反之为新增审核
            $result = $this->field('customer_id')->where(['customer_id' => $data['customer_id'], 'lang' => $data])->find();
            if($result){
                $this->where(['customer_id' => $data['customer_id'], 'lang' => $data])->save($data);
            } else {
                $this->add($data);
            }
            $this->commit();
            return $data['status'];
        } catch(Exception $e){
            $this->rollback();
            return false;
        }

    }

    /**
     * 审核信息
     * @param mix $condition
     * @return mix
     * @author klp
     */
    protected function getCheckInfo($condition = []) {
        if(empty($condition)){
            return false;
        }
        $where=array();
        if(!empty($info['serial_no'])){
            $where['buyer_app_serial_no'] = $condition['serial_no'];
        } else{
            jsonReturn('','-1001','用户[id]不可以为空');
        }
        $where['lang'] = $condition['lang'] ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');

        $field = 'buyer_app_serial_no,lang,approved_name,status,remarks,approved_by,approved_at';

        try {
            $result =  $this->field($field)->where($where)->select();
            return $result ? $result : array();
        } catch(\Kafka\Exception $e){
            return array();
        }

    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($condition = []) {

    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = []) {

    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($code = '', $id = '', $lang = '') {

    }

    /**
     * 删除数据
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($code = '', $id = '', $lang = '') {

    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update_data($upcondition = []) {

    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {

    }
}
