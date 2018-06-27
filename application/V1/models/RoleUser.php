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
    Protected $autoCheckFields = true;
    protected $table_name ='t_role_user';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }



    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getRolesUserlist($id,$order='id desc') {

        $sql = 'SELECT  `t_role_user`.`id`,`t_user`.`name`, `t_user`.`email` , `t_user`.`mobile`  , `t_user`.`user_no` ';
        $sql .= ' FROM t_role_user';
        $sql .= ' LEFT JOIN  `t_user` ON `t_user`.`id` =`t_role_user`.`user_id`';
       // $sql_where = '';
        if(!empty($id)) {
            $sql .= ' WHERE `t_role_user`.`role_id` =' . $id;
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
                    $this -> create_data(['role_id'=>$data['role_id'],'user_id' =>$user_arr[$i] ]);
                }
            }
        }
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
        if(isset($create['user_id'])){
            $arr['user_id'] = $create['user_id'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

}
