<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class RoleUserModel extends PublicModel {

    //put your code here
    protected $tableName = 'role_user';
    protected $table_name= 't_role_user';
    protected $table_role= 't_role';
    protected $table_url_perm= 't_url_perm';
    Protected $autoCheckFields = false;

    public function __construct($str = '') {
        parent::__construct($str = '');
    }


    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getRoleslist($where,$order='id desc') {

        $sql = 'SELECT `t_role_user`.`role_id`,`user_id`, `t_role`.`name`,`t_role_access_perm`.`url_perm_id`,`t_url_perm`.`url`, `t_url_perm`.`description` ';
        $sql .= ' FROM '.$this->table_name;
        $sql .= ' LEFT JOIN  `t_role` ON `t_role_user`.`role_id` =`t_role`.`id`';
        $sql .= ' LEFT JOIN  `t_role_access_perm` ON `t_role_access_perm`.`role_id` =`t_role`.`id`';
        $sql .= ' LEFT JOIN  `t_url_perm` ON `t_url_perm`.`id` =`t_role_access_perm`.`url_perm_id`';
        $sql_where = '';
        if(!empty($where['user_id'])) {
            $sql_where .= ' WHERE `user_id`=' . $where['user_id'];
        }
        if ( $where ){
            $sql .= $sql_where;
        }
        return $this->query( $sql );
    }

    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if(!empty($where['id'])){
            $row = $this->where($where)
                ->field('id,name,description,status')
                ->find();
            return $row;
        }else{
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if(!empty($where['id'])){
            return $this->where($where)
                ->save(['status' => 'DELETED']);
        }else{
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data,$where) {
        if(isset($data['parent_id'])){
            $arr['parent_id'] = $data['parent_id'];
        }
        if(isset($data['name'])){
            $arr['name'] = $data['name'];
        }
        if(isset($data['description'])){
            $arr['description'] = $data['description'];
        }
        if(isset($data['status'])){
            $arr['status'] = $data['status'];
        }
        if(!empty($where)){
            return $this->where($where)->save($data);
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
        if(isset($create['parent_id'])){
            $arr['parent_id'] = $create['parent_id'];
        }
        if(isset($create['name'])){
            $arr['name'] = $create['name'];
        }
        if(isset($create['description'])){
            $arr['description'] = $create['description'];
        }
        if(isset($create['status'])){
            $arr['status'] = $create['status'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

}
