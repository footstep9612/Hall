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
    public function createAgreeAttach($agree,$agreement_id,$created_by){
        foreach($agree as $key => $value){
            $value['agreement_id']=$agreement_id;
            $value['created_by'] = $created_by;
            $value['created_at'] = date('Y-m-d H:i:s');
            $this -> add ($value);
        }
        return true;
    }
    //保存编辑协议附件数据
    public function updateAgreeAttach($agree,$agreement_id,$created_by){
        $cond=array(
            'agreement_id'=>$agreement_id,
            'created_by'=>$created_by,
            'deleted_flag'=>'N',
        );
        $exist=$this->field('id')->where($cond)->select();
        $existId=array();
        foreach($exist as $v){
            $existId[]=$v['id'];
        }
        $inputId=array();
        foreach($agree as $v){
            if(!empty($v['id'])){
                $inputId[]=$v['id'];
            }
        }
        $delId=array_diff($existId,$inputId);
        if(!empty($delId)){ //del
            $strId=implode(',',$delId);
            $this->where("id in ($strId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
        }
        foreach($agree as $k => $v){
            if(empty($v['id'])){
                $v['agreement_id']=$agreement_id;
                $v['created_by']=$created_by;
                $v['deleted_flag']='N';
                $v['created_at']=date('Y-m-d H:i:s');
                $this->add($v);
            }
        }
        return true;
    }
}
