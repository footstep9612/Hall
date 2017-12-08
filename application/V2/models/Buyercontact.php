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

    //put your code here
    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer_contact';

    public function __construct($str = '')
    {
        parent::__construct($str = '');
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
        if(isset($create['gender'])){
            $arr['gender'] = $create['gender'];
        }
        if(isset($create['title'])){
            $arr['title'] = $create['title'];
        }
        if(isset($create['phone'])){
            $arr['phone'] = $create['phone'];
        }
        if(isset($create['email'])){
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
            $arr['city'] =$create['city'];
        }
        if(isset($create['area_bn'])){
            $arr['area_bn'] =$create['area_bn'];
        }
        if(isset($create['address'])){
            $arr['address'] =$create['address'];
        }
        if(isset($create['zipcode'])){
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
        $sql = $this->field('buyer_contact.id,buyer_id,first_name,last_name,gender,title,phone,fax,email,country_code,country_bn,area_bn,
  province,city,address,zipcode,longitude,latitude,buyer_contact.remarks,buyer_contact.created_by,buyer_contact.created_at,area.name as area_name,country.name as country_name')
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
        );
        $arr = [];
        $flag = true;
        $this->startTrans();    //开启事务
        $exist = $this->showContactDel($buyer_id,$created_by);
        if($exist == false){
            $flag = false;
        }
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
            $this->commit();
            return true;
        }else{
            $this->rollback();
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
            'created_by'=>$created_by,
            'deleted_flag'=>'N',
        );
        $fieldArr = array(
            'name', //联系人名字
            'title', //联系人职位
            'phone', //联系人电话
            'email', //联系人邮箱
            'address', //联系人地址
            'hobby', //联系人爱好
            'experience', //联系人经验
            'role', //购买角色
            'social_relations', //联系人社会关系
        );
        $field = '';
        foreach($fieldArr as $v){
            $field .= ','.$v;
        }
        $field = substr($field,1);
        return $this->field($field)->where($cond)->select();
    }
}
