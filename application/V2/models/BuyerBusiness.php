<?php
//客户业务信息 wangs
class BuyerBusinessModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'buyer_business'; //采购商业务信息表名
    public function __construct()
    {
        parent::__construct();
    }
    //业务信息详情
    public function businessList($data){
        if(empty($data['buyer_id']) || empty($data['created_by'])){
            return false;
        }
        $info = $this -> showBusinessFind($data['buyer_id'],$data['created_by']);
        return $info;
    }
    //验证有效数据性
    public function validData($data){
        $validArr = array(
            'buyer_id',
            'product_type', //所需产品类型
            'purchasing_model', //采购模式
            'purchasing_cycle', //采购周期
            'trade_terms',  //客户惯用贸易术语+
            'settlement',   //结算方式
            'is_local_settlement',  //是否本地决算
            'is_purchasing_relationship',   //采购关系
            'is_net',   //是否入网
            'net_at',   //入网时间
            'net_invalid_at',   //失效时间
            'net_goods',    //入网产品+
        );
        unset($data['token']);
        unset($data['purchase']);
        foreach($data as $key => $value){
            foreach($validArr as $v){
                if(empty($data[$v])){
                    return false;
                }
            }
            if(!in_array($key,$validArr)){
                if(strlen($value)>100*3){
                    return false;
                }
            }
        }
        if($data['net_at'] > $data['net_invalid_at'] || $data['net_at'] < date('Y-m-d')){
            return false;
        }
        return true;
    }
    //客户档案管理搜索列表index,wangs
    public function createBusiness($data)
    {
        //验证
        $validRes = $this -> validData($data);
        if(!$validRes){
            return false;
        }
        $buyer_id = $data['buyer_id'];
        $created_by = $data['created_by'];
        //组装数据
        $arr = array(
            'buyer_id' => $buyer_id,
            'created_by' => $created_by,
            'created_at' => date('Y-m-d H:i:s'),
            'product_type' => $data['product_type'],
            'purchasing_model' => $data['purchasing_model'],
            'purchasing_cycle' => $data['purchasing_cycle'],
            'trade_terms' => $data['trade_terms'],
            'settlement' => $data['settlement'],
            'is_local_settlement' => $data['is_local_settlement'],
            'is_purchasing_relationship' => $data['is_purchasing_relationship'],
            'is_net' => $data['is_net'],
            'net_at' => $data['net_at'],
            'net_invalid_at' => $data['net_invalid_at'],
            'net_goods' => $data['net_goods']
        );
        $arrOpt = array();
        if(!empty($data['usage'])){
            $arrOpt['usage'] = $data['usage'];
        }
        if(!empty($data['is_warehouse'])){
            $arrOpt['is_warehouse'] = $data['is_warehouse'];
        }
        if(!empty($data['warehouse_address'])){
            $arrOpt['warehouse_address'] = $data['warehouse_address'];
        }
        if(!empty($data['competitor_info'])){
            $arrOpt['competitor_info'] = $data['competitor_info'];
        }
        if(!empty($data['net_subject'])){
            $arrOpt['net_subject'] = $data['net_subject'];
        }
        if(!empty($arrOpt)){
            $arr = array_merge($arr,$arrOpt);
        }
        //数据存在，删除
        $showRes = $this -> showBusiness($buyer_id,$created_by);
        if(!empty($showRes)){
            $delRes = $this -> delBusiness($buyer_id,$created_by);
            if(!$delRes){
                return false;
            }
        }
        //添加数据
        $addRes = $this -> add($arr);
        if($addRes){
            return true;
        }
    }
    //删除业务信息
    public function delBusiness($buyer_id,$created_by){
        $map = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by
        );
        $res = $this -> where($map) -> delete();
        if($res){
            return true;
        }
    }
    //查询业务信息
    public function showBusiness($buyer_id,$created_by){
        $map = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by
        );
        $info = $this -> where($map) -> select();
        return $info;
    }
    //查询业务信息find
    public function showBusinessFind($buyer_id,$created_by){
        $map = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by
        );
        $info = $this -> where($map) -> find();
        return $info;
    }
}