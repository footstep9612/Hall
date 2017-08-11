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
class UserModel extends PublicModel {

    //put your code here
    protected $tableName = 'employee';
    protected $g_table ='employee';
    const STATUS_NORMAL = 'NORMAL'; //NORMAL-正常；
    const STATUS_DISABLED = 'DISABLED'; //DISABLED-禁止；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除
    public function __construct($str = '') {
        parent::__construct($str = '');
    }
    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author jhw
     */
    protected function getCondition($condition = []) {
        $sql = ' WHERE 1 = 1';
        if ( !empty($condition['status']) ){
            $sql .= ' AND `employee`.`status`= "'.$condition['status'].'"';
        }
        if ( !empty($condition['group_id']) ){
            $sql .= ' AND org_member.org_id ='.$condition['group_id'];
        }
        if ( !empty($condition['mobile']) ){
            $sql .= ' AND employee.mobile ="'.$condition['mobile'].'"';
        }
        if ( !empty($condition['role_id']) ){
            $sql .= ' AND role_member.role_id ='.$condition['role_id'];
        }
        if ( !empty($condition['username']) ){
            $sql .= ' AND employee.name like "%'.$condition['username'].'%"';
        }
        if ( !empty($condition['employee_flag']) ){
            $sql .= ' AND employee.employee_flag ='.$condition['employee_flag'];
        }
        if ( !empty($condition['user_no']) ){
            $sql .= ' AND employee.user_no = "'.$condition['user_no'].'"';
        }

        return $sql;
    }
    /**
     * 获取列表
     * @param  array $condition;
     * @return array
     * @author jhw
     */
    public function getlist($condition = [],$order=" employee.id desc") {
        $where = $this->getCondition($condition);
        $sql = 'SELECT `employee`.`id`,`employee`.`status`,`employee`.`gender`,`user_no`,`employee`.`name`,`email`,`mobile` ,group_concat(`org`.`name`) as group_name,group_concat(`role`.`name`) as role_name';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' left join  org_member on employee.id = org_member.employee_id ';
        $sql .= ' left join  org on org_member.org_id = org.id ';
        $sql .= ' left join  role_member on employee.id = role_member.employee_id ';
        $sql .= ' left join  role on role_member.role_id = role.id ';
        $sql .=$where;
        $sql .= ' group by `employee`.`id`';
        if ( $condition['num'] ){
            $sql .= ' LIMIT '.$condition['page'].','.$condition['num'];
        }
        return $this->query( $sql );
    }
    public function getcount($condition = [],$order=" employee.id desc") {
        $where = $this->getCondition($condition);
        $sql = 'SELECT count(`employee`.`id`) as num';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' left join  org_member on employee.id = org_member.employee_id ';
        $sql .= ' left join  org on org_member.org_id = org.id ';
        $sql .= ' left join  role_member on employee.id = role_member.employee_id ';
        $sql .= ' left join  role on role_member.role_id = role.id ';
        $sql .=$where;
        return $this->query( $sql );
    }
    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($id = '') {
        $where['id'] = $id;
        return $this->where($where)->find();
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function findInfo($where) {
        $sql = 'SELECT * FROM regi.user_main where username = '.$where;
//        if ( !empty($condition['where']) ){
//            $sql .= ' AND '.$condition['where'];
//        }
//        $sql .= ' Order By '.$order;
//        if ( $condition['page'] ){
//            $sql .= ' LIMIT '.$condition['page'].','.$condition['countPerPage'];
//        }
//return $this->query( $sql );
        $db =db_Db::getInstance($this->db_config);
        return $db->query($sql);
    }
    /**
     * 登录
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function login($data) {
        $where=array();
        if(!empty($data['email'])){
            $where['email'] = $data['email'];
        }
        if(!empty($data['mobile'])){
            $where['mobile'] = $data['mobile'];
        }
        if(!empty($data['user_no'])){
            $where['user_no'] = $data['user_no'];
        }
        if(empty($where['mobile'])&&empty($where['email'])&&empty($where['user_no'])){
            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
            exit();
        }
        if(!empty($data['password'])){
            $where['password_hash'] = md5($data['password']);
        }
        $where['status'] = 'NORMAL';
        $row = $this->where($where)
            ->field('id,user_no,name,email,mobile,status')
            ->find();
        return $row;
    }

    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function Exist($data) {
        $sql = 'SELECT `id`,`user_no`,`name`,`email`,`mobile`';
        $sql .= ' FROM '.$this->g_table;
        $where = '';
        if ( !empty($data['email']) ){
            $where .= " where email = '" .$data['email']."'";
        }
        if ( !empty($data['mobile']) ){
            if($where){
                $where .= " or mobile = '" .$data['mobile']."'";
            }else{
                $where .= " where mobile = '" .$data['mobile']."'";
            }

        }
        if ( !empty($data['user_no']) ){
            if($where){
                $where .= " or user_no = '" .$data['user_no']."'";
            }else{
                $where .= " where user_no = '" .$data['user_no']."'";
            }
        }

        if ( $where){
            $sql .= $where;
        }
        $row = $this->query( $sql );
        return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author zyg
     */
    public function delete_data($id = '') {

        $where['id'] = $id;
        return $this->where($where)
                        ->save(['status' => 'DELETED']);
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update_data($create = [],$where) {
        if(isset($create['user_no'])){
            $data['user_no']=$create['user_no'];
        }
        if(isset($create['name'])){
            $data['name']=$create['name'];
        }
        if(isset($create['email'])){
            $data['email']=$create['email'];
        }
        if(isset($create['mobile'])){
            $data['mobile']=$create['mobile'];
        }
        if(isset($create['password_hash'])){
            $data['password_hash']=$create['password_hash'];
        }
        if(isset($create['name_en'])){
            $data['name_en']=$create['name_en'];
        }
        if(isset($create['gender'])){
            $data['gender']=$create['gender'];
        }
        if(isset($create['mobile2'])){
            $data['mobile2']=$create['mobile2'];
        }
        if(isset($create['phone'])){
            $data['phone']=$create['phone'];
        }
        if(isset($create['ext'])){
            $data['ext']=$create['ext'];
        }
        if(isset($create['remarks'])){
            $data['remarks']=$create['remarks'];
        }
        if(isset($data)){
            $data['created_at']=date("Y-m-d H:i:s");
        }
        switch ($create['status']) {
            case self::STATUS_DELETED:
                $data['status'] = $create['status'];
                break;
            case self::STATUS_DISABLED:
                $data['status'] = $create['status'];
                break;
            case self::STATUS_NORMAL:
                $data['status'] = $create['status'];
                break;
        }
        if(!$where){
            return false;
        }else{

            return $this->where($where)->save($data);
        }

    }

    /**
     * 新增数据
     * @param  array $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if(isset($create['user_no'])){
            $data['user_no']=$create['user_no'];
        }
        if(isset($create['name'])){
            $data['name']=$create['name'];
        }
        if(isset($create['email'])){
            $data['email']=$create['email'];
        }
        if(isset($create['mobile'])){
            $data['mobile']=$create['mobile'];
        }
        if(isset($create['password_hash'])){
            $data['password_hash']=$create['password_hash'];
        }
        if(isset($create['name_en'])){
            $data['name_en']=$create['name_en'];
        }
        if(isset($create['gender'])){
            $data['gender']=$create['gender'];
        }
        if(isset($create['mobile2'])){
            $data['mobile2']=$create['mobile2'];
        }
        if(isset($create['phone'])){
            $data['phone']=$create['phone'];
        }
        if(isset($create['ext'])){
            $data['ext']=$create['ext'];
        }
        if(isset($create['remarks'])){
            $data['remarks']=$create['remarks'];
        }
        if(isset($data)){
            $data['created_at']=date("Y-m-d H:i:s");
        }
        $datajson = $this->create($data);
        return $this->add($datajson);
    }

}
