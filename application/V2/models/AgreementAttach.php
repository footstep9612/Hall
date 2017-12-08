<?php
/**
 * @desc 审核日志模型
 * @author liujf 2017-07-01
 */
class AgreementAttachModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'agreement_attach';

    public function __construct() {
        parent::__construct();
    }
    //创建框架协议上传的附件
    public function createAgreeAttach($data){
        $arr['agreement_id'] = $data['agreement_id'];
        $arr['attach_name'] = $data['attach_name'];
        $arr['attach_url'] = $data['attach_url'];
        $arr['created_by'] = $data['created_by'];
        $arr['created_at'] = date('Y-m-d H:i:s');
        $res = $this -> add ($arr);
        if($res){
            return true;
        }else{
            return false;
        }
    }
    //保存编辑协议附件数据
    public function updateAgreeAttach($id,$data){
        $arr['agreement_id'] = $id;
        $arr['attach_name'] = $data['attach_name'];
        $arr['attach_url'] = $data['attach_url'];
        $arr['created_by'] = $data['created_by'];
        $arr['created_at'] = date('Y-m-d H:i:s');
        $this->startTrans();    //开启事务
        $exist = $this->where(array('agreement_id'=>$id))->find();
        if($exist){
            $this->where(array('agreement_id'=>$id))->save(array('deleted_flag'=>'Y'));
        }
        $res = $this->add($arr);
        if($res){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }
}
