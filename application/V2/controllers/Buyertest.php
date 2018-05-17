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
}
