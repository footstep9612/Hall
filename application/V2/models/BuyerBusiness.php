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
        if(!empty($info['net_subject'])){
            $info['net_subject']=explode(',',$info['net_subject']);
        }
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
//                if(empty($data[$v])){
//                    return false;
//                }
                if(!empty($data[$v])){
                    if(in_array($key,$validArr)  && strlen($data[$v])>100){
                        return false;
                    }
                }
            }
            if(!in_array($key,$validArr)){
                if(strlen($value)>200){
                    return false;
                }
            }
        }
        if(!empty($data['net_at']) ||  !empty($data['net_invalid_at'])){
            if($data['net_at'] <= $data['net_invalid_at']){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }
    //客户档案管理搜索列表index,wangs
    public function createBusiness($data)
    {
        //验证
//        $validRes = $this -> validData($data);
//        if($validRes == false){
//            return false;
//        }
        //组装数据
        $optArr = array(
//            'buyer_id', //客户id
//            'created_by',   // 创建人
//            'created_at' => date('Y-m-d H:i:s'),
            'product_type',    //产品类型-------业务基础信息
            'Product_service_preference',    //产品服务偏好
            'Origin_preference',    //原产地偏好
            'Brand_preference',    //品牌偏好
            'purchasing_model',    //采购模式
            'purchasing_cycle',    //采购周期
            'cycle_remarks',    //采购周期写入时间
            'usage',  //使用情况
            'is_warehouse',  //是否有仓库
            'warehouse_address', //仓库地址
//            'competitor_info',   //竞争对手情况-------放入上下游
            'trade_code',  //贸易术语-------------结算情况  trade_code  trade_name  trade_terms
            'pay_code',    //结算方式 pay_code pay_name  settlement
            'is_local_settlement',  //是否支持本地结算
            'is_purchasing_relationship',    //是否有采购关系
            'is_net',    //是否入网-------------入网管理
            'net_subject',   //入网主题
            'net_at',    //入网时间
            'net_invalid_at',    //失效时间
            'net_goods'   //入网商品
        );
        foreach($optArr as $v){
            if(!empty($data[$v])){
                $arr[$v] = $data[$v];
            }else{
                $arr[$v]=null;
            }
        }
        $arr['buyer_id'] = $data['buyer_id'];
        $arr['created_by'] = $data['created_by'];
        $arr['created_at'] = date('Y-m-d H:i:s');
        if(!empty($arr['trade_code'])){
            $arr['trade_terms']=$arr['trade_code'];
            unset($arr['trade_code']);
        }
        if(!empty($arr['pay_code'])){
            $arr['settlement']=$arr['pay_code'];
            unset($arr['pay_code']);
        }
        if(empty($arr['net_at'])){
            $arr['net_at']=null;
        }
        if(empty($arr['net_invalid_at'])){
            $arr['net_invalid_at']=null;
        }
        if(!empty($arr['net_subject'])){    //入网主题可多选
            $arr['net_subject']=implode(',',$arr['net_subject']);
        }
        if($data['is_edit'] == true){
            //业务数据
            $addRes = $this ->where(array('buyer_id'=>$data['buyer_id'],'created_by'=>$data['created_by']))->save($arr);
            //里程碑事件
            $event = new MilestoneEventModel();
            $eventRes = $event->updateMilestoneEvent($data['milestone_event'],$data['buyer_id'],$data['created_by']);
            //采购计划
            $purchase = new BuyerPurchasingModel();
            $purchaseRes = $purchase->updatePurchase($data['purchase'],$data['buyer_id'],$data['created_by']);
            if($addRes || $eventRes || $purchaseRes){
                return true;
            }
        }else{
            //添加数据
            $addRes = $this -> add($arr);
            //里程碑事件
            $event = new MilestoneEventModel();
            $eventRes = $event->createMilestoneEvent($data['milestone_event'],$data['buyer_id'],$data['created_by']);
            //采购计划
            $purchase = new BuyerPurchasingModel();
            $purchaseRes = $purchase->createPurchase($data['purchase'],$data['buyer_id'],$data['created_by']);
            if($addRes || $eventRes || $purchaseRes){
                return true;
            }
        }

    }
    //删除业务信息
    public function delBusiness($buyer_id,$created_by){
        $map = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by
        );
        return $this -> where($map) -> delete();
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
        $fieldArr = array(
            'buyer_id', //客户id
            'product_type', //产品类型
            'Product_service_preference', //产品服务偏好
            'Origin_preference', //原产地偏好
            'Brand_preference', //品牌偏好
            'purchasing_model', //采购模式
            'purchasing_cycle', //采购周期
            'cycle_remarks',    // 采购周期的备注时间
            'usage',    // 设备使用情况
            'is_warehouse', //是否有仓库
            'warehouse_address',    //仓库所在地
            'competitor_info',  //竞争对手信息
            'trade_terms',  //贸易术语
            'settlement',   //结算方式
            'is_local_settlement',  //是否本地结算
            'is_purchasing_relationship',   //采购关系
            'is_net',   //是否入网
            'net_subject',  //入网主题
            'net_at',   //入网时间
            'net_invalid_at',   //失效时间
            'net_goods',    //入网商品
            'created_by',   //创建人
            'created_at'    //床架时间
        );
        $field = '';
        foreach($fieldArr as $v){
            $field .= ','.$v;
        }
        $field = substr($field,1);
        $info = $this ->field($field)-> where($map) -> find();
        return $info;
    }
}