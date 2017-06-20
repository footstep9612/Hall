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
   // Protected $autoCheckFields = ture;
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
        if (isset($condition['id'])) {
            $where['id'] = $condition['id'];
        }
        if (isset($condition['id'])) {
            $where['user_no'] = $condition['user_no'];
        }
        if (isset($condition['name'])) {
            $where['name'] = ['LIKE', '%' . $condition['name'] . '%'];
        }
        if (isset($condition['email'])) {
            $where['email'] = ['LIKE', '%' . $condition['email'] . '%'];
        }
        if (isset($condition['mobile'])) {
            $where['mobile'] = ['LIKE', '%' . $condition['mobile'] . '%'];
        }
        if (isset($condition['enc_password'])) {
            $where['enc_password'] = md5($condition['enc_password']);
        }
        if (isset($condition['status'])) {
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
                            ->field('id,user_id,name,email,mobile,status')
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
    public function getlist($condition = [],$order="id desc") {
        $where = $this->getcondition($condition);
        if (isset($condition['page']) && isset($condition['countPerPage'])) {
            $count = $this->getcount($condition);
            return $this->where($where)
                            ->limit($condition['page'] . ',' . $condition['countPerPage'])
                            ->field('id,user_no,name,email,mobile,status')
                            ->order($order)
                            ->select();
        } else {
            return $this->where($where)
                            ->field('id,user_no,name,email,mobile,status')
                            ->order($order)
                            ->select();
        }
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
                        ->field('id,user_id,name,email,mobile,status')
                        ->find();
    }

    /**
     * 登录
     * @param   array $data;
     * @author jhw
     */
    public function login($data) {
        $where=array();
        if(!empty($data['email'])){
            $where['email'] = $data['email'];
        }
        if(!empty($data['mobile'])){
            $where['mobile'] = $data['mobile'];
        }
        if(empty($where['mobile'])&&empty($where['mobile'])){
            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
            exit();
        }
        if(!empty($data['password'])){
            $where['password_hash'] = md5($data['password']);
        }
        $where['status'] = 'NORMAL';
        $this->where($where)
            ->field('id,user_no,name,email,mobile,status')
            ->find();
        $row = $this->where($where)
            ->field('id,user_no,name,email,mobile,status')
            ->find();
        return $row;
    }

    /**
     * 判断用户是否存在
     * @param  string $email 邮箱
     * @param  string $moblie 手机
     * @author jhw
     */
    public function Exist($email=null,$moblie=null) {
        $where='';
        if($email){
           $where ="email='".$email."'";
        }
        if($moblie){
            if($where){
                $where .= " or mobile='".$moblie."'";
            }else{
                $where = "mobile='".$moblie."'";
            }
        }
        //$where['enc_password'] = md5($enc_password);
        $row = $this->where($where)
                ->field('id,user_no,name,email,mobile,status')
                ->find();
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
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {
        $data = $this->create($createcondition);
        return $this->add($data);
    }

}
