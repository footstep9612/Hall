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
    //创建采购计划
    public function createPurchase($purchase,$buyer_id,$created_by)
    {
        $info = $this -> showPurchase($buyer_id,$created_by);
        //采购计划数据存在，则删除，再重新添加
        $this->startTrans();    //开启事务
        if(!empty($info)){
            $this->delPurchase($buyer_id,$created_by);
        }
        $packageArr = array(
            'buyer_id', //采购商ID
            'purchasing_at',    //采购时间-date
            'purchasing_budget',    //采购预算
            'purchasing_plan',  //采购计划
            'created_by',   //创建人
            'created_at',   //创建时间
//            'attach_name',   //采购计划附件名称
//            'attach_url',   //采购计划附件url
        );
        $arr = [];
        $result = [];
        $flag = true;
        foreach($purchase as $key => $value){
            foreach($packageArr as $k => $v){
                if(!empty($value[$v])){
                    if(!empty($value['attach_name']) || !empty($value['attach_url'])){
                        $arr['attach'][$key]['attach_name'] = $value['attach_name'];
                        $arr['attach'][$key]['attach_url'] = $value['attach_url'];
                    }
                    $arr[$key][$v] = $value[$v];
                    $arr[$key]['buyer_id'] = $buyer_id;
                    $arr[$key]['created_by'] = $created_by;
                    $arr[$key]['created_at'] = date('Y-m-d H:i:s');
                }
            }
            $res = $this -> add($arr[$key]);
            if($res && $flag){
                $result[$res]=$arr['attach'][$key];
            }else{
                $flag = false;
            }
        }
        if($flag){
            $this->commit();
            return $result;
        }else{
            $this->rollback();
            return $flag;
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
        );
        $fieldArr = array(
            'id as purchasing_id',   //采购计划id
            'buyer_id',   //采购商id
            'purchasing_at',   //采购计划日期
            'purchasing_budget',   //采购预算
            'purchasing_plan',   //采购计划
            'created_by',   //创建人
            'created_at',   //创建时间
        );
        $field = 'attach.attach_name,attach.attach_url';
        foreach($fieldArr as $v){
            $field .= ',purchasing.'.$v;
        }
        $info = $this->alias('purchasing')
            ->join('erui_buyer.buyer_attach attach on purchasing.id=attach.purchasing_id','left')
            ->field($field)
            ->where($map)
            -> select();
        foreach($info as $k => $v){
            $info[$k]['purchasing_at'] = substr($v['purchasing_at'],0,4);
        }
        if(empty($info)){
            $info['purchasing_budget']='';
            $info['purchasing_plan']='';
        }
        return $info;
    }
}