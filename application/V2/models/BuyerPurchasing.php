<?php
//客户业务信息 wangs
class BuyerPurchasingModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'buyer_purchasing'; //采购商业务信息表名
    public function __construct()
    {
        parent::__construct();
    }
    public function updatePurchase($purchase,$buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $arrId=array();
        $existId=$this->field('id')->where($cond)->select();
        foreach($existId as $v){
            $arrId[]=$v['id'];
        }
        $inputId=array();
        foreach($purchase as $v){
            if(empty($v['purchasing_at'])){
                $v['purchasing_at']=null;
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
    //创建采购计划
    public function createPurchase($purchase,$buyer_id,$created_by)
    {
//        $info = $this -> showPurchase($buyer_id,$created_by);
        //采购计划数据存在，则删除，再重新添加
//        if(!empty($info)){
//            $this->delPurchase($buyer_id,$created_by);
//        }
        $packageArr = array(
//            'buyer_id', //采购商ID
//            'purchasing_at',    //采购时间-date
//            'purchasing_budget',    //采购预算
//            'purchasing_plan',  //采购计划
//            'created_by',   //创建人
//            'created_at',   //创建时间
//            'attach_name',   //采购计划附件名称
//            'attach_url',   //采购计划附件url
        );
        $arr = [];
        $result = [];
        $flag = true;
        foreach($purchase as $key => $value){
            if(empty($value['purchasing_at'])){
                $value['purchasing_at']=null;
            }
            $value['buyer_id']=$buyer_id;
            $value['created_by']=$created_by;
            $value['created_at']=date('Y-m-d H:i:s');
//            print_r($value);die;
//            foreach($packageArr as $k => $v){
//                $arr[$key][$v] = $value[$v];
//                if(!empty($value[$v])){
//                    if(!empty($value['attach_name']) || !empty($value['attach_url'])){
//                        $arr['attach'][$key]['attach_name'] = $value['attach_name'];
//                        $arr['attach'][$key]['attach_url'] = $value['attach_url'];
//                    }
//                    $arr[$key][$v] = $value[$v];
//                    $arr[$key]['buyer_id'] = $buyer_id;
//                    $arr[$key]['created_by'] = $created_by;
//                    $arr[$key]['created_at'] = date('Y-m-d H:i:s');
//                }
//            }
            $res = $this -> add($value);
//            $res = $this -> add($arr[$key]);
            if(!$res && $flag){
                $flag=false;
            }
//            if($res && $flag){
//                $result[$res]=$arr['attach'][$key];
//                $result[]=$res;
//            }else{
//                $flag = false;
//            }
        }
        if($flag){
            return true;
        }else{
            return false;
        }
    }
    //采购计划删除
    public function delPurchase($buyer_id,$created_by){
        $map = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by
        );
        $res = $this -> where($map) -> delete();
        return $res;
    }
    //查询采购计划 和 采购计划附件
    public function showPurchase($buyer_id,$created_by){
        $map = array(
            'purchasing.buyer_id'=>$buyer_id,
            'purchasing.created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $fieldArr = array(
//            'id',   //采购计划id
            'buyer_id',   //采购商id
            'purchasing_at',   //采购计划日期
            'purchasing_budget',   //采购预算
            'purchasing_plan',   //采购计划
            'created_by',   //创建人
            'created_at',   //创建时间
        );
//        $field = 'attach.attach_name,attach.attach_url';
        $field = 'id';
        foreach($fieldArr as $v){
            $field .= ','.$v;
        }
        $info = $this->alias('purchasing')
//            ->join('erui_buyer.buyer_attach attach on purchasing.id=attach.purchasing_id','left')
            ->field($field)
            ->where($map)
            -> select();
//        print_r($info);die;
//        foreach($info as $k => $v){
//            $info[$k]['purchasing_at'] = substr($v['purchasing_at'],0,4);
//        }
//        if(empty($info)){
//            $info[0]['purchasing_budget']='';
//            $info[0]['purchasing_plan']='';
//            $info[0]['purchasing_at']='';
//        }
        return $info;
    }
}