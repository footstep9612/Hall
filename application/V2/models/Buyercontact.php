<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class BuyercontactModel extends PublicModel
{

    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer_contact';

    public function __construct() {
        parent::__construct();
    }
    public function showContact($data){
        if(empty($data['id'])){
            return false;
        }
        $cond=array(
            'id'=>$data['id'],
            'deleted_flag'=>'N'
        );
        $fieldArr=$this->getThisField();
        $field=implode(',',$fieldArr);
        $info = $this->field($field)->where($cond)->find();
        if(empty($info)){
            $info=[];
            unset($fieldArr['id']);
            unset($fieldArr['buyer_id']);
            unset($fieldArr['created_by']);
            foreach($fieldArr as $k => $v){
                $info[$v]='';
            }
        }
        return $info;
    }
    private function getThisField(){
        $fieldArr=array(
            'id'=>'id',
            'buyer_id'=>'buyer_id',
            'is_main'=>'is_main',  //是否为主要联系人标识: 1 / 0
            'name'=>'name',
            'title'=>'title',    //职位及部门
            'phone'=>'phone',
            'email'=>'email',
            'address'=>'address',
            'hobby'=>'hobby',
            'experience'=>'experience',   //工作经历
            'role'=>'role', //购买角色
            'social_relations'=>'social_relations', //社会关系
            'key_concern'=>'key_concern',  //决策主要关注点
            'attitude_kerui'=>'attitude_kerui',   //对科瑞的态度
            'social_habits'=>'social_habits',    //常去社交场所
            'relatives_family'=>'relatives_family',  //家庭亲戚相关信息
            'created_by'=>'created_by'  //家庭亲戚相关信息
        );
        return $fieldArr;
    }
    private function verifyData($data){
//        if(!empty($value['phone'])){
//            if(!preg_match ("/^(\d{2,4}-)?\d{6,11}$/",$value['phone'])){
//                return '联系人电话:(选)2~4位区号-6~11位电话号码';
//            }
//        }
        $fieldArr=$this->getThisField();
        unset($fieldArr['id']);
        unset($fieldArr['id']);
        print_r($fieldArr);die;
        foreach($fieldArr as $k => $v){

        }
        if(empty($data['name'])){

        }
//        print_r($data);die;




        if(!empty($value['email'])){
            $value['email']=trim($value['email'],' ');
            if(!preg_match ("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$value['email'])){
                return $contactExtra['email'].L('format_error');
            }else{
                $buyerContact=new BuyercontactModel();
                if(empty($value['id'])){
                    $email=$buyerContact->field('email')->where(array('email'=>$value['email'],'deleted_flag'=>'N'))->find();
                    if($email){
                        return $contactExtra['email'].L('already existed');
                    }
                }else{
                    $email=$buyerContact->field('email')->where(array('id'=>$value['id']))->find();//默认邮箱
                    if($value['email']!=$email['email']){  //修改邮箱
                        $exist=$buyerContact->field('email')->where(array('email'=>$value['email'],'deleted_flag'=>'N'))->find();
                        if($exist){
                            return $contactExtra['email'].L('already existed');
                        }
                    }
                }

            }
            $contactEmail[]=$value['email'];
        }
        $emailTotal=count($contactEmail);   //联系人邮箱总数
        $validTotal=count(array_flip(array_flip($contactEmail)));   //联系人邮箱过滤重复后总数
        if($emailTotal!=$validTotal){
            return $contactExtra['email'].L('repeat');
        }
        $contactEmail=array();  //crm
    }
    public function editContact($data){
//        $res=$this->verifyData($data);
//        var_dump($res);die;
        $fieldArr=$this->getThisField();    //获取字段
        foreach($fieldArr as $k => $v){
            if(empty($data[$v])){
                $data[$v]='';
            }
            $arr[$v]=$data[$v];
        }
        $arr['created_at']=date('Y-m-d H:i:s');
        if(!empty($arr['id'])){
            unset($arr['buyer_id']);
            $this->where(array('id'=>$arr['id']))->save($arr);
        }else{
            unset($arr['id']);
            $this->add($arr);
        }
        return true;
    }
    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['buyer_id'])){
            $arr['buyer_id'] = $create['buyer_id'];
        }
        if(isset($create['first_name'])){
            $arr['first_name'] = $create['first_name'];
        }
        if(isset($create['last_name'])){
            $arr['last_name'] = $create['last_name'];
        }
        if(isset($create['name'])){
            if (strlen($create['name']) > 70) jsonReturn('', -101, '您输入的收货人（公司）超出长度!');
            $arr['name'] = $create['name'];
        }
        if(isset($create['gender'])){
            $arr['gender'] = $create['gender'];
        }
        if(isset($create['title'])){
            $arr['title'] = $create['title'];
        }
        if(isset($create['phone'])){
            if (strlen($create['phone']) > 50) jsonReturn('', -101, '您输入的电话超出长度!');
            $arr['phone'] = $create['phone'];
        }
        if(isset($create['email'])){
            if (strlen($create['email']) > 50) jsonReturn('', -101, '您输入的邮箱超出长度!');
            $arr['email'] = $create['email'];
        }
        if(isset($create['remarks'])){
            $arr['remarks'] = $create['remarks'];
        }
        if(isset($create['created_by'])){
            $arr['created_by'] =$create['created_by'];
        }
        $arr['created_at'] =date("Y-m-d H:i:s");
        if(isset($create['fax'])){
            if (strlen($create['fax']) > 40) jsonReturn('', -101, '您输入的传真超出长度!');
            $arr['fax'] =$create['fax'];
        }
        if(isset($create['country_code'])){
            $arr['country_code'] =$create['country_code'];
        }
        if(isset($create['country_bn'])){
            $arr['country_bn'] =$create['country_bn'];
        }
        if(isset($create['province'])){
            $arr['province'] =$create['province'];
        }
        if(isset($create['city'])){
            if (strlen($create['city']) > 30) jsonReturn('', -101, '您输入的市超出长度!');
            $arr['city'] =$create['city'];
        }
        if(isset($create['area_bn'])){
            $arr['area_bn'] =$create['area_bn'];
        }
        if(isset($create['address'])){
            if (strlen($create['address']) > 200) jsonReturn('', -101, '您输入的详细地址超出长度!');
            $arr['address'] =$create['address'];
        }
        if(isset($create['zipcode'])){
            if (strlen($create['zipcode']) > 10) jsonReturn('', -101, '您输入的邮编超出长度!');
            $arr['zipcode'] =$create['zipcode'];
        }
        try{
            $data = $this->create($arr);
            return $this->add($data);
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }

    }
    //新建联系人
    public function createContact($data){
        $data['created_at']=date('Y-m-d H:i:s');
        if(empty($data['id'])){
            $this->add($data);
        }else{
            $this->where(array('id'=>$data['id']))->save($data);
        }
        return true;
    }
    public function info($data) {
        if ($data['id']) {
            $info = $this->where(array("id" => $data['id']))
                ->find();
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author jhw
     */
    protected function getCondition($data = []) {
        if (!empty($data['first_name'])) {
            $where['first_name'] =  ['like',"%".$data['first_name']."%"];
        }
        if (!empty($data['last_name'])) {
            $where['last_name'] =  ['like',"%".$data['last_name']."%"];
        }
        if (!empty($data['name'])) {
            $where['name'] =  ['like',"%".$data['name']."%"];
        }
        if (!empty($data['country_bn'])) {
            $where['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['area_bn'])) {
            $where['area_bn'] = $data['area_bn'];
        }
        if ($data['buyer_id']) {
            $where['buyer_id'] = $data['buyer_id'];
        }
        return $where;
    }
    public function getcount($data = []) {
        $where =$this -> getCondition($data);
        $count = $this->where($where)
            ->count();
        return $count;
    }
    public function getlist($data) {
        $where =$this -> getCondition($data);
        $sql = $this->field('buyer_contact.id,buyer_id,first_name,last_name,buyer_contact.name,gender,title,phone,fax,email,country_code,country_bn,area_bn,
  province,city,address,zipcode,buyer_contact.remarks,buyer_contact.created_by,buyer_contact.created_at,area.name as area_name,country.name as country_name')
                ->where($where)
                ->join('`erui_operation`.`market_area` area on area.lang="zh" and area.bn=erui_buyer.`buyer_contact`.`area_bn` ', 'left')
                ->join('`erui_dict`.`country`  on country.lang="zh" and country.bn=erui_buyer.`buyer_contact`.`country_bn`  ', 'left')
                ->order('id desc');
        if ( $data['num'] ){
            $sql->limit($data['page'],$data['num']);
        }
        $list =  $sql ->select();
        return $list;

    }
    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition,$where){
        if(isset($condition['buyer_id'])){
            $arr['buyer_id'] = $condition['buyer_id'];
        }
        if(isset($condition['first_name'])){
            $arr['first_name'] = $condition['first_name'];
        }
        if(isset($condition['last_name'])){
            $arr['last_name'] = $condition['last_name'];
        }
        if(isset($condition['name'])){
            $arr['name'] = $condition['name'];
        }
        if(isset($condition['gender'])){
            $arr['gender'] = $condition['gender'];
        }
        if(isset($condition['title'])){
            $arr['title'] = $condition['title'];
        }
        if(isset($condition['phone'])){
            $arr['phone'] = $condition['phone'];
        }
        if(isset($condition['email'])){
            $arr['email'] = $condition['email'];
        }
        if(isset($condition['remarks'])){
            $arr['remarks'] = $condition['remarks'];
        }
        if(isset($condition['created_by'])){
            $arr['created_by'] =$condition['created_by'];
        }
        if(isset($condition['fax'])){
            $arr['fax'] =$condition['fax'];
        }
        if(isset($condition['country_code'])){
            $arr['country_code'] =$condition['country_code'];
        }
        if(isset($condition['area_bn'])){
            $arr['area_bn'] =$condition['area_bn'];
        }

        if(isset($condition['country_bn'])){
            $arr['country_bn'] =$condition['country_bn'];
        }
        if(isset($condition['province'])){
            $arr['province'] =$condition['province'];
        }
        if(isset($condition['city'])){
            $arr['city'] =$condition['city'];
        }
        if(isset($condition['address'])){
            $arr['address'] =$condition['address'];
        }
        if(isset($condition['zipcode'])){
            $arr['zipcode'] =$condition['zipcode'];
        }
        return $this->where($where)->save($arr);
    }

    /**
     * @param $contact  联系人arr
     * @param $buyer_id
     * @param $created_by
     * 王帅
     * 编辑联系人信息
     */
    public function updateBuyerContact($contact,$buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $exist=$this->field('id')->where($cond)->select();
        $arrId=$this->packageId($exist);
        $contactId=$this->packageId($contact);  //编辑---------id----------------------------------------------
        $delId=array_diff($arrId,$contactId);   //删除---------id----------------------------------------------
        if(!empty($delId)){
            $strId=implode(',',$delId);
            $this->where("id in ($strId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
        }
        $validArr = array(
            'name', //联系人姓名+
            'title', //联系人职位+
            'phone', //联系人电话+
            'email', //联系人邮箱
            'address', //联系人姓名
            'hobby', //爱好
            'experience', //经历
            'role', //角色
            'social_relations', //社会关系
            'key_concern', //决策主要关注点
            'attitude_kerui', //对科瑞的态度
            'social_habits', //常去社交场所
            'relatives_family', //家庭亲戚相关信息
        );
        foreach($contact as $key => $value){
            $value['buyer_id']=$buyer_id;
            $value['created_by']=$created_by;
            $value['created_at']=date('Y-m-d H:i:s');
            if(!empty($value['id'])){
                unset($value['created_by']);
                $this->where(array('id'=>$value['id']))->save($value);   //编辑
            }else{
                unset($value['id']);
                $this->add($value);  //添加
            }
        }
        return true;
    }
    //循环打包id
    public function packageId($data){
        $arr=array();
        foreach($data as $k => $v){
            if(!empty($v['id'])){
                $arr[]=$v['id'];
            }
        }
        return $arr;
    }
    /**
     * 客户管理，基本信息--新建-客户的-联系人
     * wangs
     */
    public function createBuyerContact($contact,$buyer_id,$created_by){
        $validArr = array(
            'name', //联系人姓名+
            'title', //联系人职位+
            'phone', //联系人电话+
            'email', //联系人邮箱
            'address', //联系人姓名
            'hobby', //爱好
            'experience', //经历
            'role', //角色
            'social_relations', //社会关系
            'key_concern', //决策主要关注点
            'attitude_kerui', //对科瑞的态度
            'social_habits', //常去社交场所
            'relatives_family', //家庭亲戚相关信息
        );
        $arr = [];
        $flag = true;
        foreach($contact as $key => $value){
            foreach($validArr as $v){
                if(!empty($value[$v])){
                    $arr[$key][$v]=$value[$v];
                    $arr[$key]['buyer_id']=$buyer_id;
                    $arr[$key]['created_by']=$created_by;
                    $arr[$key]['created_at']=date('Y-m-d H:i:s');
                }
            }
            $res = $this->add($arr[$key]);
            if(!$res && $flag){
                $flag = false;
            }
        }
        if($flag){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 创建联系人若存在，则删除，修改为删除状态Y
     * wangs
     */
    public function showContactDel($buyer_id,$created_by){
        $cond = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'deleted_flag'=>'N',
        );
        $exist = $this->where($cond)->select();
        if(!empty($exist)){
            $del = $this->where($cond)->save(array('deleted_flag'=>'Y'));
            if(!$del){
                return false;
            }
        }
        return true;
    }
    /**
     * 查询客户的联系人-----exist
     * wangs
     */
    public function showBuyerExistContact($buyer_id,$created_by){
        $cond = array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N',
        );
        $fieldArr = array(
            'id', //id
            'name', //联系人名字
            'title', //联系人职位
            'phone', //联系人电话
            'email', //联系人邮箱
            'address', //联系人地址
            'hobby', //联系人爱好
            'experience', //联系人经验
            'role', //购买角色
            'social_relations', //联系人社会关系
            'key_concern', //决策主要关注点
            'attitude_kerui', //对科瑞的态度
            'social_habits', //常去社交场所
            'relatives_family', //家庭亲戚相关信息
        );
        $field = '';
        foreach($fieldArr as $v){
            $field .= ','.$v;
        }
        $field = substr($field,1);
        return $this->field($field)->where($cond)->select();
    }
    public function showContactsList($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $cond = array(
            'buyer_id'=>$data['buyer_id'],
            'deleted_flag'=>'N'
        );
        $fieldArr=$this->getThisField();
        $field=implode(',',$fieldArr);
        $info=$this->field($field)->where($cond)->select();
        if(empty($info)){
            $info=[];
        }
        return $info;
    }
    public function delContact($data){
        if(empty($data['id'])){
            return false;
        }
        $save=array(
            'deleted_flag'=>'Y',
            'created_by'=>$data['created_by'],
            'created_at'=>date('Y-m-d H:i:s')
        );
        $this->where(array('id'=>$data['id']))->save($save);
        return true;
    }
}
