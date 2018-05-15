<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author klp
 */
class SupplierAccountModel extends PublicModel
{

    protected $tableName = 'supplier_account';
    protected $dbName = 'erui_supplier'; //数据库名称

    public function __construct($str = '')
    {

        parent::__construct();
    }

    /**
     * 登录
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     */
    public function login($data, $lang) {
        $where = array();
        if (isset($data['email'])) {
            $where['email'] = $data['email'];
        }
        if (isset($data['user_name'])) {
            $where['user_name'] = $data['user_name'];
        }
        if (empty($where['user_name']) && empty($where['email'])) {
            jsonReturn(null, -124, ShopMsg::getMessage('-124',$lang));
            exit();
        }
        if (!empty($data['password'])) {
            $where['password_hash'] = md5($data['password']);
        }
        $where['deleted_flag'] = 'N';
        //$where['status'] = 'VALID';
        $row = $this->where($where)->find();
        return $row;
    }

    /**
     * 判断用户是否存在
     * @param  string $data 用户名
     * @param  string $sql 语言
     * @return mix
     */

    public function Exist($data, $sql = 'or') {
        if (!empty($data['email']) && !empty($data['user_name'])) {
            if ($sql == 'or') {
                $map1['email'] = $data['email'];
                $map1['user_name'] = $data['user_name'];
                $map1['_logic'] = 'or';
                $map['_complex'] = $map1;
            } else {
                $map['email'] = $data['email'];
                $map['user_name'] = $data['user_name'];
            }
            $row = $this->table('erui_supplier.supplier_account')->where($map)->select();
        } else {
            $row = $this->table('erui_supplier.supplier_account')->where($data)->select();
        }
        return empty($row) ? false : $row;
    }

    /**
     * 修改数据(更新)
     * @param  int $id id
     * @return bool
     * @author
     */
    public function update_data($create, $where)
    {
        if (isset($create['email'])) {
            $arr['email'] = $create['email'];
        }
        if (isset($create['user_name'])) {
            $arr['user_name'] = $create['user_name'];
        }
        if (isset($create['mobile'])) {
            $arr['mobile'] = $create['mobile'];
        }
        if (isset($create['password_hash'])) {
            $arr['password_hash'] = $create['password_hash'];
        }
        if (isset($create['role'])) {
            $arr['role'] = $create['role'];
        }
        if (isset($create['first_name'])) {
            $arr['first_name'] = $create['first_name'];
        }
        if (isset($create['last_name'])) {
            $arr['last_name'] = $create['last_name'];
        }
        if (!empty($where)&&isset($arr)) {
            $info = $this->where($where)->find();
            if(!$info){
                $arr['supplier_id']=$where['supplier_id'];
                $this->create_data($arr);
            }else{
                return $this->where($where)->save($arr);
            }
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author
     */
    public function create_data($create = [])
    {
        if (isset($create['supplier_id'])) {
            $arr['supplier_id'] = $create['supplier_id'];
        }
        if (isset($create['email'])) {
            $arr['email'] = $create['email'];
        }
        if (isset($create['user_name'])) {
            $arr['user_name'] = $create['user_name'];
        }
        if (isset($create['mobile'])) {
            $arr['mobile'] = $create['mobile'];
        }
        if (isset($create['password_hash'])) {
            $arr['password_hash'] = $create['password_hash'];
        }
        if (isset($create['role'])) {
            $arr['role'] = $create['role'];
        }
        if (isset($create['first_name'])) {
            $arr['first_name'] = $create['first_name'];
        }
        if (isset($create['last_name'])) {
            $arr['last_name'] = $create['last_name'];
        }
        if (isset($create['created_by'])) {
            $arr['created_by'] = $create['created_by'];
        }
        $arr['created_at'] = Date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }
    /**
     * 获取用户信息
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function info($data) {
        if(!empty($data['supplier_id'])){
            $row = $this->where(['supplier_id' => $data['supplier_id'] ,'deleted_flag' => 'N'])
                ->find();
            return $row;
        }else{
            return false;
        }
    }
}


