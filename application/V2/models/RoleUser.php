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
    protected $tableName = 'role_member';
    Protected $autoCheckFields = true;
    protected $table_name ='role_member';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    public function userRoleList($user_id,$pid = ''){
        if($user_id){
            $sql = 'SELECT  `func_perm`.`id` as func_perm_id,`func_perm`.`url`,`func_perm`.`sort`,`func_perm`.`fn`,`func_perm`.`parent_id` ';
            $sql .= ' FROM employee';
            $sql .= ' LEFT JOIN  `role_member` ON `employee`.`id` =`role_member`.`employee_id`';
            $sql .= ' LEFT JOIN  `role_access_perm` ON `role_access_perm`.`role_id` =`role_member`.`role_id`';
            $sql .= ' LEFT JOIN  `func_perm` ON `func_perm`.`id` =`role_access_perm`.`func_perm_id`';
            $sql .= "WHERE `func_perm`.`id` is not null ";
            if(!empty($user_id)) {
                $sql .= ' and `role_member`.`employee_id` =' . $user_id;
            }
            if($pid!=='') {
                $sql .= ' and `func_perm`.`parent_id` = ' . $pid;
            }
            $sql .= ' group by func_perm_id';
            $sql .= ' order by `func_perm`.`sort` desc';
            return $this->query( $sql );
        }
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getRolesUserlist($id,$order='id desc') {

        $sql = 'SELECT  `role_member`.`id` as role_member_id,`role_member`.`employee_id`,`employee`.`name`, `employee`.`email` , `employee`.`mobile`  , `employee`.`user_no` ';
        $sql .= ' FROM employee';
        $sql .= ' LEFT JOIN  `role_member` ON `employee`.`id` =`role_member`.`employee_id`';
       // $sql_where = '';
        if(!empty($id)) {
            $sql .= ' WHERE `role_member`.`role_id` =' . $id;
           // $sql .=$sql_where;
        }
//        if ( $where ){
//            $sql .= $sql_where;
//        }
        return $this->query( $sql );
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
                ->delete();
        }else{
            return false;
        }
    }

    public function update_datas($data) {
        if($data['role_id']){
            $this->where(['role_id'=>$data['role_id']])->delete();
            if($data['role_user_ids']){
                $user_arr = explode(',',$data['role_user_ids']);
                $count = count($user_arr);
                for($i=0;$i<$count;$i++){
                   $info = $this -> where(['role_id'=>$data['role_id'],'employee_id' =>$user_arr[$i] ])->select();
                   if(!$info){
                       $this -> create_data(['role_id'=>$data['role_id'],'employee_id' =>$user_arr[$i] ]);
                   }
                }
            }
        }
        return true ;
    }
    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['role_id'])){
            $arr['role_id'] = $create['role_id'];
        }
        if(isset($create['employee_id'])){
            $arr['employee_id'] = $create['employee_id'];
        }
        $arr['created_at'] = date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }

}
