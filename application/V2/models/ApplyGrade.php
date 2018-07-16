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
        $time=time();
        if(count($data['attach'])<1){
            $arr['attach_url']=!empty($data['attach_url'])?$data['attach_url']:''; //附件url
            $arr['attach_name']=!empty($data['attach_name'])?$data['attach_name']:''; //附件名称
            $arr['attach_size']=!empty($data['attach_size'])?$data['attach_size']:''; //附件size
            $arr['created_by']=$data['created_by']; //申请经办人
            $arr['created_at']=$time; //申请时间
            $res=$this->add($arr);
        }else{
            $info=[];
            foreach($data['attach'] as $k => &$v){
                $info[$k]['grade_id']=$data['id'];
                $info[$k]['customer_grade']=$data['customer_grade'];
                $info[$k]['attach_url']=!empty($v['attach_url'])?$v['attach_url']:'';
                $info[$k]['attach_name']=!empty($v['attach_name'])?$v['attach_name']:'';
                $info[$k]['attach_size']=!empty($v['attach_size'])?$v['attach_size']:'';
                $info[$k]['created_by']=$data['created_by'];
                $info[$k]['created_at']=$time;

//                $v['grade_id']=$data['id'];
//                $v['customer_grade']=$data['customer_grade'];
//                $v['attach_url']=!empty($v['attach_url'])?$v['attach_url']:'';
//                $v['attach_name']=!empty($v['attach_name'])?$v['attach_name']:'';
//                $v['attach_size']=!empty($v['attach_size'])?$v['attach_size']:'';
//                $v['created_by']=$data['created_by'];
//                $v['created_at']=$time;
            }
            $res=$this->addAll($info);
        }
        if($res){
            return $res;
        }else{
            return false;
        }
    }
    public function findApplyGrade($grade_id){
        $field='id,grade_id,customer_grade,created_by,created_at,handler,handle_at';
        $gradeInfo=$this->table('erui_buyer.apply_grade')
            ->field($field)
            ->where(array('grade_id'=>$grade_id,'status'=>'Y'))
            ->order('id asc')
            ->find();
        if(empty($gradeInfo)){
            $gradeInfo=[];
        }else{
            $gradeInfo['handle_at']=date('Y-m-d H:i',$gradeInfo['handle_at']);
            $gradeInfo['created_at']=date('Y-m-d H:i',$gradeInfo['created_at']);
        }
        return $gradeInfo;
    }
    public function saveAppGrade($grade_id,$handler){
        $arr['status']='Y';
        $arr['handler']=$handler;
        $arr['handle_at']=time();
        $this->where(array('grade_id'=>$grade_id))->save($arr);
        return true;
    }
}
