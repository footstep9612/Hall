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
class RoleModel extends PublicModel {

    //put your code here
    protected $tableName = 'role';
    Protected $autoCheckFields = true;
    protected $table_name ='t_role';
    protected $permtable ='t_url_perm';
    protected $rolepermtable ='t_role_access_perm';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }


    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data,$limit,$order='id desc') {
        if(!empty($limit)){
            return $this->field('id,name,description,status')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            return $this->field('id,name,description,status')
                ->where($data)
                ->order($order)
                ->select();
        }
    }
    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getRoleslist($id,$order='id desc') {

        $sql = 'SELECT `t_role_access_perm`.`url_perm_id`,`t_url_perm`.`url`, `t_url_perm`.`description` , `t_url_perm`.`parent_id` ';
        $sql .= ' FROM '.$this->table_name;
        $sql .= ' LEFT JOIN  `t_role_access_perm` ON `t_role_access_perm`.`role_id` =`t_role`.`id`';
        $sql .= ' LEFT JOIN  `t_url_perm` ON `t_url_perm`.`id` =`t_role_access_perm`.`url_perm_id`';
        $sql_where = '';
        if(!empty($id)) {
            $sql_where .= ' WHERE `t_role`.`id` =' . $id;
            $sql .=$sql_where;
        }

//        if ( $where ){
//            $sql .= $sql_where;
//        }
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
        if(isset($data['name'])){
            $arr['name'] = $data['name'];
        }
        if(isset($data['description'])){
            $arr['description'] = $data['description'];
        }
        if(isset($data['status'])){
            $arr['status'] = $data['status'];
        }
        if(!empty($where)&&isset($arr)){
            $result =$this->where($where)->save($arr);
            if (false === $result) {
                return false;
            }else{
                return true;
            }
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
