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
class GroupUserModel extends PublicModel {

    //put your code here
    protected $tableName = 'org_member';
    protected $table = 'org_member';
    Protected $autoCheckFields = true;

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    public function addusers($data){
        if(!empty($data['user_ids'])){
            $user_arr = explode(",",$data['user_ids']);
        }
        for($i=0;$i<count($user_arr);$i++){
            $arr['employee_id'] = $user_arr[$i];
            $arr['org_id'] = $data['group_id'];
            $this->create_data($arr);
        }
        return true ;
    }
    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data,$limit,$order='ug.id desc') {
        $sql  = 'SELECT ug.id ,ug.group_id,g.name as group_name,ug.user_id,u.name as user_name,ug.user_id as value ,u.name as label';
        $sql .= ' FROM '.$this->table.' as ug';
        $sql .= ' LEFT JOIN t_group AS g ON g.`id` = ug.`group_id`';
        $sql .= ' LEFT JOIN t_user AS u ON u.`id` = ug.`user_id`';
        if(!empty($data['group_id'])){
            $sql .= ' WHERE ug.`group_id` = '.$data['group_id'];
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
    public function delete_data($user_id = '') {
        $where['user_id'] = $user_id;
        if(!empty($where['user_id'])){
            return $this->where($where)
                ->save(['status' => 'DELETED']);
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
        if(isset($create['employee_id'])){
            $arr['employee_id'] = $create['employee_id'];
        }
        if(isset($create['org_id'])){
            $arr['org_id'] = $create['org_id'];
        }
        $info = $this->where($arr)->find();
        if(!$info){
            $arr['created_at'] = Date("Y-m-d H:i:s");
            $data = $this->create($arr);
            return $this->add($data);
        }else{
            return true;
        }
    }

}
