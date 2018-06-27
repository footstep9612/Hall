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


