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
        if(!empty($info)){
            $resDel =$this -> delPurchase($buyer_id,$created_by);
            if(!$resDel){
                return false;
            }
        }
        $arr = array();
        foreach($purchase as $key => $value){
            $value['buyer_id'] = $buyer_id;
            $value['created_by'] = $created_by;
            $value['created_at'] = date('Y-m-d H:i:s');
            $arr[] = $value;
        }
        $res = $this -> addAll($arr);
        if($res){
            return true;
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
    //查询采购计划
    public function showPurchase($buyer_id,$created_by){
        $map = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
        );
        $info = $this ->where($map) -> select();
        return $info;
    }
}