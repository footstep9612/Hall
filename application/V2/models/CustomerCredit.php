<?php
/**
*wangs-客户管理-信用评价
 */
class CustomerCreditModel extends PublicModel{
    protected $dbName = 'erui_buyer';
    protected $tableName = 'customer_credit';

    public function __construct()
    {
        parent::__construct();
    }
    //查看客户的信用评价
    public function showCredit($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $cond=array(
            'buyer_id'=>$data['buyer_id'],
            'deleted_flag'=>'N'
        );
        $info=$this->field('buyer_id,credit_level,credit_type,line_of_credit,credit_available,payment_behind,behind_time,reputation,violate_treaty,treaty_content,comments')->where($cond)->find();
        if(empty($info)){
            return new $this;
        }
        if($info['behind_time']==0){
            $info['behind_time']='';
        }else{
            $info['behind_time']=date('Y-m-d',$info['behind_time']);
        }
        return $info;
    }
    public function editCredit($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        if(!empty($data['behind_time'])){
            $data['behind_time']=strtotime($data['behind_time']);
        }
        if(!empty($data['line_of_credit']) && !is_numeric($data['line_of_credit'])){
            return '授信额度格式错误';
        }
        if(!empty($data['credit_available']) && !is_numeric($data['credit_available'])){
            return '剩余授信额度格式错误';
        }
        $arr=array(
            'buyer_id'=>$data['buyer_id'],    //授信额度
            'line_of_credit'=>!empty($data['line_of_credit'])?$data['line_of_credit']:0,    //授信额度
            'credit_available'=>!empty($data['credit_available'])?$data['credit_available']:0,    //可用额度

            'payment_behind'=>!empty($data['payment_behind'])?$data['payment_behind']:'',    //是否拖欠过货款
            'behind_time'=>!empty($data['behind_time'])?$data['behind_time']:0,    //拖欠货款时间
            'reputation'=>!empty($data['reputation'])?$data['reputation']:'',    //业内口碑
            'violate_treaty'=>!empty($data['violate_treaty'])?$data['violate_treaty']:'',  //是否有针对KERUI/ERUI的违约
            'treaty_content'=>!empty($data['treaty_content'])?$data['treaty_content']:'',    //有违约内容
            'comments'=>!empty($data['comments'])?$data['comments']:'',    //KERUI/ERUI、KERUI对其评价

            'credit_type'=>$data['credit_type'],    //授信类型
            'credit_level'=>$data['credit_level'],    //信用等级
        );
        $arr['created_by']=$data['created_by'];
        $arr['created_at']=time();
        if($arr['payment_behind']=='N'){
            $arr['behind_time']=0;
        }
        if($arr['violate_treaty']=='N'){
            $arr['treaty_content']='';
        }
        $cond=array(
            'buyer_id'=>$data['buyer_id'],
            'deleted_flag'=>'N'
        );
        $info=$this->where($cond)->find();
        if(!empty($info)){  //编辑
            $this->where($cond)->save($arr);
        }else{
            $this->add($arr);
        }
        return true;
    }
    //信用评价完整度
    public function percentCredit($data){
        $cond=array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N');
        $creditField=array(
            'line_of_credit', //授信额度
            'credit_available', //可用额度
            'credit_type', //授信类型
            'credit_level', //信用等级
            'payment_behind', //是否拖欠过货款:Y/N
            'behind_time', //拖欠货款时间
            'reputation', //业内口碑
            'violate_treaty', //有违约内容
            'treaty_content', //是否有针对KERUI/ERUI的违约
            'comments', //KERUI/ERUI、KERUI对其评价
        );
        $info=$this->field($creditField)->where($cond)->find();
        if(!empty($info)){
//            foreach($info as $k => &$v){
//                if(empty($v) || $v==0){
//                    $v='';
//                }
//            }
        }else{
            $info=[];
            foreach($creditField as $k => $v){
                $info[$v]='';
            }
        }
        return $info;
    }
}