<?php
/**
 *
 */
class ApplyGradeModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'apply_grade';

    public function __construct() {
        parent::__construct();
    }
    //经办人申请变更
    public function AddApplyGrade($data){
        $arr['grade_id']=$data['id'];   //
        $arr['customer_grade']=$data['customer_grade']; //申请级别
        $arr['attach_url']=!empty($data['attach_url'])?$data['attach_url']:''; //附件url
        $arr['attach_name']=!empty($data['attach_name'])?$data['attach_name']:''; //附件名称
        $arr['attach_size']=!empty($data['attach_size'])?$data['attach_size']:''; //附件size

        $arr['created_by']=$data['created_by']; //申请经办人
        $arr['created_at']=time(); //申请时间
        $res=$this->add($arr);
        if($res){
            return $res;
        }else{
            return false;
        }
    }
}
