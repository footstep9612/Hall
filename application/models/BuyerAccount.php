<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
<<<<<<< HEAD
 * @author jhw
 */
class BuyerAccountModel extends PublicModel {

    protected $tableName = 'buyer_account';
    protected $dbName = 'erui_buyer'; //数据库名称
    public function __construct($str = '') {

        parent::__construct();
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string $lang 语言
     * @return mix
     * @author jhw
     */
    public function Exist($data) {
        if ( !empty($data['email']) && !empty($data['user_name']) ){
            $map1['email']=$data['email'];
            $map1['user_name']=$data['user_name'];
            $map1['_logic'] = 'or';
            $map['_complex'] = $map1;
            var_dump($data);
            $row = $this->table('erui_buyer.t_buyer_account')->where($map)->select();
        }else{
            $row = $this->table('erui_buyer.t_buyer_account')->where($data)->select();
        }
        return empty($row) ? false : (isset($row['customer_id']) ? $row['customer_id'] : true);
    }

//    /**
//     * 获取列表
//     * @param data $data;
//     * @return array
//     * @author jhw
//     */
//    public function getlist($data,$limit,$order='ug.id desc') {
//        $sql  = 'SELECT ug.id,ug.group_id,g.name as group_name,ug.user_id,u.name as user_name ';
//        $sql .= ' FROM '.$this->tableName.'as ug';
//        $sql .= ' LEFT JOIN t_group AS g ON t_group.`id` = ug.`group_id`';
//        $sql .= ' LEFT JOIN t_user AS u ON u.`id` = ug.`user_id`';
//        if(!empty($data['group_id'])){
//            $sql .= ' WHERE g.`group_id` = '.$data['group_id'];
//        }
//        if(!empty($limit)){
//            $sql .= ' LIMIT '.$limit['page'].','.$limit['num'];
//        }
//        $sql .= ' ORDER BY '.$order;
//        $res = $this->query( $sql );
//        return $res;
//    }

//    /**
//     * 获取列表
//     * @param  int  $id
//     * @return array
//     * @author jhw
//     */
//    public function detail($id = '') {
//        $where['id'] = $id;
//        if(!empty($where['id'])){
//            $row = $this->where($where)
//                ->field('id,parent_id,name,description,status')
//                ->find();
//            return $row;
//        }else{
//            return false;
//        }
//    }

//    /**
//     * 删除数据
//     * @param  int  $id
//     * @return bool
//     * @author jhw
//     */
//    public function delete_data($user_id = '') {
//        $where['user_id'] = $user_id;
//        if(!empty($where['user_id'])){
//            return $this->where($where)
//                ->save(['status' => 'DELETED']);
//        }else{
//            return false;
//        }
//    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['customer_id'])){
            $arr['customer_id'] = $create['customer_id'];
        }
        if(isset($create['email'])){
            $arr['email'] = $create['email'];
        }
        if(isset($create['mobile'])){
            $arr['mobile'] = $create['mobile'];
        }
        if(isset($create['password_hash'])){
            $arr['password_hash'] = md5($create['password_hash']);
        }
        if(isset($create['role'])){
            $arr['role'] = md5($create['role']);
        }
        if(isset($create['first_name'])){
            $arr['first_name'] = md5($create['first_name']);
        }
        if(isset($create['last_name'])){
            $arr['last_name'] = md5($create['last_name']);
        }
        if(isset($create['phone'])){
            $arr['phone'] = md5($create['phone']);
        }
        if(isset($create['phone'])){
            $arr['created_at'] = Data;
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition,$where){
        if ($condition['email']) {
            $data['email'] = $condition['email'];
        }
        if ($condition['phone']) {
            $data['phone'] = $condition['phone'];
        }
        if ($condition['first_name']) {
            $data['first_name'] = $condition['first_name'];
        }
        if ($condition['last_name']) {
            $data['last_name'] = $condition['last_name'];
        }
        if ($condition['user_name']) {
            $data['user_name'] = $condition['user_name'];
        }
        if($condition['status']){
            switch ($condition['status']) {
                case self::STATUS_VALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_INVALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_DELETE:
                    $data['status'] = $condition['status'];
                    break;
            }
        }

        return $this->where($where)->save($data);
    }

    /**
     * 密码校验
     * @author klp
     */
    public function checkPassword($data){
        if(!empty($data['id'])){
            $where['id'] = $data['id'];
        } else{
            jsonReturn('','-1001','用户id不可以为空');
        }
        if(!empty($data['password'])){
            $password = $data['password'];
        }
        $pwd = $this->where($where)->field('password_hash')->find();
        if($pwd == $password){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 密码修改
     * @author klp
     * return bool
     */
    public function update_pwd($data){
        if(!empty($data['id'])){
            $where['id'] = $data['id'];
        } else{
            jsonReturn('','-1001','用户id不可以为空');
        }
        if(!empty($data['password'])){
            $new['password'] = $data['password'];
        } else {
            jsonReturn('','-1001','新密码不可以为空');
        }
        return $this->where($where)->save($new);
    }

}

