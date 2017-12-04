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
    //框架协议管理
    public function manageAgree($data){
        $cond = "1=1";
        if(!empty($data['execute_start_at'])){  //执行时间
            $cond .= " and execute_start_at='$data[execute_start_at]'";
        }
        if(!empty($data['org_id'])){    //事业部
            $cond .= " and org_id='$data[org_id]'";
        }
        if(!empty($data['execute_no'])){    //执行单号txt
            $cond .= " and execute_no like '%$data[execute_no]%'";

        }
        if(!empty($data['execute_company'])){   //执行分公司txt
            $cond .= " and execute_company like '%$data[execute_company]%'";
        }
        $page = 1;
        if(!empty($data['page']) && is_numeric($data['page']) && $data['page'] >0   ){    //事业部
            $page = ceil($data['page']);
        }
        $info = $this -> manageAgreeList($page,$cond);
        return $info;
    }
    //框架协议管理数据列表
    public function manageAgreeList($page=1,$cond){
        //条件
        $totalCount = $this ->alias('agree')
            ->join('erui_buyer.buyer buyer on buyer.id=agree.buyer_id','left')
            -> where($cond)
            ->count();
        $pageSize = 10;
        $totalPage = ceil($totalCount/$pageSize);
        if($page > $totalPage && $totalPage > 0){
            $page = $totalPage;
        }
        $offset = ($page-1)*$pageSize;
        $fields = array(
            'id',
            'execute_no',       //框架执行单号
            'org_id',           //事业部
            'execute_company',  //执行分公司
            'area_bn',          //所属地区
//            'name',       //客户名称  buyer
//            'buyer_code',       //客户代码  buyer
            'product_name',     //品名中文
            'number',           // 数量
            'unit',             //单位
            'amount',           //项目金额
            'execute_start_at', //项目开始执行时间
            'agent',            //市场经办人
            'technician'        //商务技术经办人
        );
        $field = 'buyer.buyer_code,buyer.name';
        foreach($fields as $v){
            $field .= ',agree.'.$v;
        }
        $info = $this ->alias('agree')
            ->field($field)
            ->join('erui_buyer.buyer buyer on buyer.id=agree.buyer_id','left')
            -> where($cond)
            ->limit($offset,$pageSize)
            ->select();
        $arr = array(
            'info'=>$info,
            'page'=>$page
        );
        return $arr;
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
            'country_bn',  //所属国家
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