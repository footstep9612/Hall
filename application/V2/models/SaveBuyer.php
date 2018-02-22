<?php
class SaveBuyerModel extends PublicModel{
    protected $dbName = 'erui_buyer';
    protected $tableName = 'savebuyer';

    public function __construct()
    {
        parent::__construct();
    }
    public function saveBuyer(){
        set_time_limit(0);
        $arr=[];
        $field=array(
            'buyer_no',
            'name',
            'buyer_code',
            'official_email',
            'official_phone',
            'country_bn',
            'status',
            'source',
            'agent_id',
            'created_by',
            'intent_product',
            'purchase_amount',
            'biz_scope'
        );
        $info=$this->field($field)->select();
        //buyer
        $buyer=new BuyerModel();
        $agent=new BuyerAgentModel();
        //save
        foreach($info as $key => $value){
            $addAgent=$value['agent_id'];
            unset($value['agent_id']);
            if(!empty($value['buyer_no'])){
                $res=$buyer->where(array('buyer_no'=>$value['buyer_no']))->save($value);
            }else{
                $res=$buyer->where(array('buyer_code'=>$value['buyer_code']))->save($value);
            }
            //经办人
            if(!empty($addAgent)){
                $buyerId=$buyer->field('id')->where(" buyer_no='$value[buyer_no]' or buyer_code='$value[buyer_code]' ")->find();
                $addArr=array(
                    'buyer_id'=>$buyerId['id'],
                    'agent_id'=>$addAgent,
                    'role'=>'ADMIN',
                    'created_by'=>$value['created_by'],
                    'created_at'=>date('Y-m-d H:s:i'),
                );
                $add=$agent->add($addArr);
            }else{
                $add='kong';
            }

            $arr[$key]['save']=$res;
            $arr[$key]['agent']=$add;
        }
        print_r(count($arr));
        echo '<br>';
        print_r($arr);
    }
    public function saveAgent(){
        $res=$this->field('id')->where(" buyer_no='C20171230000001' or buyer_code='JN1730' ")->find();
        print_r($res);die;
//        $sql="select id from erui_buyer.buyer where buyer_no='' or buyer_code=''";
    }

}