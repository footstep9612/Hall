<?php
/**
 *
 */
class CustomerGradeModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'customer_grade';

    public function __construct() {
        parent::__construct();
    }
    public function buyerGradeList($data){
        if(empty($data['buyer_id'])){
            return false;
        }
            $info=$this
            ->field('amount,position,year_keep,re_purchase,credit_grade,purchase,enterprise,income,scale')
            ->where(array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N'))
            ->select();
            $arr=[];
            foreach($info as $k => $v){
                $arr[$k]['customer_grade']=$v['amount'];  //客户等级
                $arr[$k]['created_name']=$v['position'];    //创建人
                $arr[$k]['created_at']=$v['year_keep'];   //创建时间
                $arr[$k]['updated_at']=$v['re_purchase']; //更新时间
                $arr[$k]['customer_admin']=$v['credit_grade'];    //1客户管理员
                $arr[$k]['checked_at']=$v['purchase'];    //1审核时间
                $arr[$k]['status']=$v['enterprise'];  //1状态
            }
            return $arr;
    }
    private function oldBuyer($data){
        $field=array(
            'buyer_id',
            'amount', //客户历史成单金额
            'amount_score',
            'position', //易瑞产品采购量占客户总需求量地位
            'position_score',
            'year_keep',   //连续N年及以上履约状况良好
            'keep_score',
            're_purchase',   //年复购次数
            're_score',
            'final_score',  //综合分值
            'customer_grade',   //客户等级
            'flag'  //提交 flag=1 保存 flag=0
        );
        $arr=['type'=>1];
        foreach($field as $k => $v){
            if(!empty($data[$v])){
                $arr[$v]=$data[$v];
            }else{
                $arr[$v]='';
            }
        }
        return $arr;
    }
    private function newBuyer($data){
        $arr['type']=0;
        $field=array(
            'buyer_id',
            'credit_grade', //客户资信等级
            'credit_score',
            'purchase', //零配件年采购额
            'purchase_score',
            'enterprise',   //企业性质
            'enterprise_score',
            'income',   //营业收入
            'income_score',
            'scale',    //资产规模
            'scale_score',
            'final_score',  //综合分值
            'customer_grade',   //客户等级
            'flag'  //提交 flag=1 保存 flag=0
        );
        $arr=['type'=>0];
        foreach($field as $k => $v){
            if(!empty($data[$v])){
                $arr[$v]=$data[$v];
            }else{
                $arr[$v]='';
            }
        }
        return $arr;
    }
    public function addGrade($data){
        if($data['type']==1){
            $arr=$this->oldBuyer($data);    //老客户
        }else{
            $arr=$this->newBuyer($data);    //潜在客户
        }
        $res=$this->add($arr);
        if($res){
            return true;
        }
        return false;
    }
    public function delGrade($data){
        if(empty($data['id'])){
            return false;
        }
        $cond=array('id'=>$data['id']);
        $res=$this->where($cond)->save(array('deleted_flag'=>'Y'));
        if($res){
            return true;
        }
        return false;
    }
}
