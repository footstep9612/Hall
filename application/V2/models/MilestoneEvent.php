<?php

/**
 * 里程碑事件
 * Created by PhpStorm.
 * 王帅
 */
class MilestoneEventModel extends Model {

    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'milestone_event';

    /**
     * @param $event 事件数据arr
     * @param $buyer_id 客户id
     * @param $created_by   创建人
     */
    public function createMilestoneEvent($event,$buyer_id,$created_by){
        $arr=array(
            'event_name', //事件名称project
            'event_content', //事件内容Content
            'event_contact', //该事件KERUI/ERUI负责人KERUI/ERUI
            'event_time', //时间date
        );
        $flag=true;
        foreach($event as $key => $value){
            if(empty($value['event_time'])){
                $value['event_time']=null;
            }
            $value['buyer_id']=$buyer_id;
            $value['created_by']=$created_by;
            $value['created_at']=date('Y-m-d H:i:s');
            $res=$this->add($value);
            if(!$res && $flag){
                $flag=false;
            }
        }
        return $flag;
    }
    public function showMilestoneEvent($buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N',
        );
        $info=$this->where($cond)->select();
        return $info;
    }
    public function updateMilestoneEvent($event,$buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $arrId=array();
        $existId=$this->field('id')->where($cond)->select();
        foreach($existId as $v){
            $arrId[]=$v['id'];
        }
        $inputId=array();
        foreach($event as $v){
            if(empty($v['event_time'])){
                $v['event_time']=null;
            }
            $v['buyer_id']=$buyer_id;
            $v['created_by']=$created_by;
            $v['created_at']=date('Y-m-d H:i:s');
            if(!empty($v['id'])){
                $inputId[]=$v['id'];
                $this->where(array('id'=>$v['id']))->save($v);
            }else{
                unset($v['id']);
                $this->add($v);
            }
        }
        $diff=array_diff($arrId,$inputId);
        if(!empty($diff)){
            $strId=implode(',',$diff);
            $this->where("id in ($strId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
        }
        return true;
    }
}
