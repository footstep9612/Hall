<?php
class BuyertestController extends PublicController
{

    public function init() {
        parent::init();
    }
    //信用评价
    public function creditAction(){
        set_time_limit(0);
        $buyer=new BuyerModel();
        $fieldArr=array(
            'id as buyer_id',
            'credit_level',
            'credit_type',
            'line_of_credit',
            'credit_available',
            'payment_behind',
            'behind_time',
            'reputation',
            'violate_treaty',
            'treaty_content',
            'comments'
        );
        $info=$buyer->field(implode(',',$fieldArr))->where(array('deleted_flag'=>'N'))->select();
        $credit=new CustomerCreditModel();
        $res=$credit->addAll($info);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    //客户附件类型
    public function fileTypeAction(){
        set_time_limit(0);
        $attach=new BuyerattachModel();
        $info=$attach->field('id,attach_name')->select();
        $arr=[];
        foreach($info as $k => $v){
            $site=strripos($v['attach_name'],'.');
            if($site>=0){
                $name=strtoupper(substr($v['attach_name'],$site+1));
                $arr[]=$attach->where(array('id'=>$v['id']))->save(array('attach_type'=>$name));
            }
        }
        echo count($arr);
    }
}
