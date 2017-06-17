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
class RoleAccessPermModel extends PublicModel {

    //put your code here
    protected $tableName = 'role_access_perm';
    Protected $autoCheckFields = true;

    public function __construct($str = '') {
        parent::__construct($str = '');
    }


    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data,$limit,$order='rap.id desc') {
        $sql  = 'SELECT rap.id,rap.role_id,r.name as role_name,rap.url_perm_id,up.description as url_perm_name,rap.perm_flag ';
        $sql .= ' FROM '.$this->tableName.'as ug';
        $sql .= ' LEFT JOIN t_url_perm AS up ON up.`id` = rap.`url_perm_id`';
        $sql .= ' LEFT JOIN t_role AS r ON r.`id` = rap.`role_id`';
        if(!empty($data['role_id'])){
            $sql .= ' WHERE rap.`role_id` = '.$data['role_id'];
        }
        if(!empty($limit)){
            $sql .= ' LIMIT '.$limit['page'].','.$limit['num'];
        }
        $sql .= ' ORDER BY '.$order;
        $res = $this->query( $sql );
        return $res;
    }

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

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if(!empty($where['id'])){
            return $this->where($where)
                ->dete(['status' => 'DELETED']);
        }else{
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data,$where) {
        if(isset($data['id'])){
            $arr['id'] = $data['id'];
        }
        if(!empty($where)){
            return $this->where($where)->save($arr);
        }else{
            return false;
        }
    }



    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['role_id'])){
            $arr['role_id'] = $create['role_id'];
        }
        if(isset($create['url_perm_id'])){
            $arr['url_perm_id'] = $create['url_perm_id'];
        }
        if(isset($create['perm_flag'])){
            $arr['perm_flag'] = $create['perm_flag'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

}
