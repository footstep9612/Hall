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
class BuyerAccountModel extends PublicModel {

    protected $tableName = 'buyer_account';
    protected $dbName = 'erui_buyer'; //数据库名称
    public function __construct($str = '') {

        parent::__construct();
    }

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
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data,$where) {
        if(isset($data['group_id'])){
            $arr['group_id'] = $data['group_id'];
        }
        if(!empty($where)){
            return $this->where($where)->save($arr);
        }else{
            return false;
        }
    }



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

}
