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
class UserModel extends PublicModel {

    //put your code here
    protected $tableName = 'user';
    protected $g_table ='t_user';
    Protected $autoCheckFields = false;
    const STATUS_NORMAL = 'NORMAL'; //NORMAL-正常；
    const STATUS_DISABLED = 'DISABLED'; //DISABLED-禁止；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zyg
     *
     */
    protected function getcondition($condition = []) {
        $where = [];
        if ($condition['id']) {
            $where['id'] = $condition['id'];
        }
        if ($condition['user_id']) {
            $where['user_id'] = $condition['user_id'];
        }
        if ($condition['name']) {
            $where['name'] = ['LIKE', '%' . $condition['name'] . '%'];
        }
        if ($condition['email']) {
            $where['email'] = ['LIKE', '%' . $condition['email'] . '%'];
        }
        if ($condition['mobile']) {
            $where['mobile'] = ['LIKE', '%' . $condition['mobile'] . '%'];
        }
        if ($condition['enc_password']) {
            $where['enc_password'] = md5($condition['enc_password']);
        }
        if ($condition['status']) {
            $where['status'] = $condition['status'];
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($condition = []) {
        $where = $this->getcondition($condition);
        try {
            return $this->where($where)
                            ->field('id,user_no,name,email,mobile,status')
                            ->count('id');
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        $sql = 'SELECT `id`,`user_no`,`name`,`email`,`mobile`,`description`';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' WHERE `status`= "NORMAL"';
        if ( !empty($condition['where']) ){
            $sql .= ' AND '.$condition['where'];
        }
        $sql .= ' Order By '.$order;
        if ( $condition['page'] ){
            $sql .= ' LIMIT '.$condition['page'].','.$condition['countPerPage'];
        }
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
        return $this->where($where)
                        ->field('id,user_no,name,email,mobile,status')
                        ->find();
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
        if(empty($where['mobile'])&&empty($where['email'])){
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
        $sql = 'SELECT `id`,`user_no`,`name`,`email`,`mobile`,`description`';
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
    public function update_data($upcondition = []) {
        $data = [];
        $where = [];
        if ($condition['id']) {
            $where['id'] = $condition['id'];
        }
        if ($condition['user_id']) {
            $data['user_id'] = $condition['user_id'];
        }
        if ($condition['name']) {
            $data['name'] = $condition['name'];
        }
        if ($condition['email']) {
            $data['email'] = $condition['email'];
        }
        if ($condition['mobile']) {
            $data['mobile'] = $condition['mobile'];
        }
        if ($condition['enc_password']) {
            $data['enc_password'] = md5($condition['enc_password']);
        }
        switch ($condition['status']) {
            case self::STATUS_DELETED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_DISABLED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_NORMAL:
                $data['status'] = $condition['status'];
                break;
        }


        return $this->where($where)->save($data);
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
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
        if(isset($create['description'])){
            $data['description']=$create['description'];
        }
        $datajson = $this->create($data);
        return $this->add($datajson);
    }

}
