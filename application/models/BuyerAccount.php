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
class BuyerAccountModel extends PublicModel
{

    //put your code here
    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer_account';

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_account($condition){
        if ($condition['customer_id']) {
            $where['customer_id'] = $condition['customer_id'];
        }
        if ($condition['email']) {
            $data['email'] = $condition['email'];
        }
        if ($condition['phone']) {
            $data['phone'] = $condition['phone'];
        }
        if ($condition['first_name']) {
            $data['first_name'] = $condition['first_name'];
        }
        if ($condition['last_name']) {
            $data['last_name'] = $condition['last_name'];
        }
        if ($condition['user_name']) {
            $data['user_name'] = $condition['user_name'];
        }
        if($condition['status']){
            switch ($condition['status']) {
                case self::STATUS_VALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_INVALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_DELETE:
                    $data['status'] = $condition['status'];
                    break;
            }
        }

        return $this->where($where)->save($data);
    }

    /**
     * 密码校验
     * @author klp
     */
    public function checkPassword($data){
        if(!empty($data['id'])){
            $where['id'] = $data['id'];
        } else{
            jsonReturn('','-1001','用户id不可以为空');
        }
        if(!empty($data['password'])){
            $password = $data['password'];
        }
        $pwd = $this->where($where)->field('password_hash')->find();
        if($pwd == $password){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 密码修改
     * @author klp
     * return bool
     */
    public function update_pwd($data){
        if(!empty($data['id'])){
            $where['id'] = $data['id'];
        } else{
            jsonReturn('','-1001','用户id不可以为空');
        }
        if(!empty($data['password'])){
            $new['password'] = $data['password'];
        } else {
            jsonReturn('','-1001','新密码不可以为空');
        }
        return $this->where($where)->save($new);
    }

}