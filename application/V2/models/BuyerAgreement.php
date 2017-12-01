<?php
//框架协议
class BuyerAgreementModel extends PublicModel
{
    protected  $dbName= 'erui_buyer';
    protected  $tableName= 'buyer_agreement';
    public function __construct()
    {
        parent::__construct();
    }
    //创建框架协议
    public function createAgree($data){
        if(empty($data['buyer_id']) || empty($data['created_by'])){
            return false;
        }
        unset($data['token']);
        $valid = $this -> validData($data);
        if($valid == false){
            return false;
        }
        //组装数据
        $arr = array(
            'buyer_id' => $data['buyer_id'],
            'created_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s'),
            'execute_no' => $data['execute_no'],
            'org_id' => $data['org_id'],
            'execute_company' => $data['execute_company'],
            'country_bn' => $data['country_bn'],
            'agent' => $data['agent'],
            'technician' => $data['technician'],
            'execute_start_at' => $data['execute_start_at'],
            'execute_end_at' => $data['execute_end_at'],
            'amount' => $data['amount']
        );
        if(!empty($data['product_name'])){  //品名中文
            $arr['product_name'] = $data['product_name'];
        }
        if(!empty($data['number'])){  //数量
            $arr['number'] = $data['number'];
        }
        if(!empty($data['unit'])){  //单位
            $arr['unit'] = $data['unit'];
        }
        if(!empty($data['payment_mode'])){  //汇款方式
            $arr['payment_mode'] = $data['payment_mode'];
        }
//        if(!empty($data['buyer_code'])){  //客户代码
//            $arr['buyer_code'] = $data['buyer_code'];
//        }
        $exRes = $this -> showAgree($arr['execute_no']);
        if(!empty($exRes)){
            return false;
        }
        //添加
        $res = $this -> addAgree($arr);
        if($res){
            return true;
        }
        return false;
    }
    //查看框架协议详情
    public function showAgreeDesc($data){
        if(empty($data['execute_no'])){
            return false;
        }
        $info = $this -> showAgree($data['execute_no']);
        if(empty($info)){
            return false;
        }
        return $info;
    }
    //按单号查看数据
    public function showAgree($execute_no){
        $info = $this -> where(array('execute_no'=>$execute_no)) -> find();
        return $info;
    }
    //添加数据
    public function addAgree($data){
        $res = $this -> add($data);
        if($res){
            return true;
        }
        return false;
    }
    //验证非空数据
    public function validData($data){
        $arr = array(
            'execute_no',   //框架执行单号
            'org_id',   //所属事业部
            'execute_company',  //执行分公司
            'area_bn',  //所属国家
            'agent',    //市场经办人
            'technician',   //商务技术经办人
            'execute_start_at', //框架开始时间
            'execute_end_at',   //框架结束时间
            'amount',   //项目金额
        );
        foreach($arr as $v){
            if(empty($data[$v])){
                return false;
            }
        }
        if($data['execute_start_at'] > $data['execute_end_at']){
            return false;
        }
        return true;
    }
}