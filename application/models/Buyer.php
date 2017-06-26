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
class BuyerModel extends PublicModel
{

    //put your code here
    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer';

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }

    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author klp
     */
    public function getInfo($data)
    {
        $where=array();
        if(!empty($data['id'])){
            $where['id'] = $data['id'];
        } else{
            jsonReturn('','-1001','用户id不可以为空');
        }
        //$lang = $data['lang'] ? strtolower($data['lang']) : (browser_lang() ? browser_lang() : 'en');
        $buyerInfo = $this->where($where)
                          ->field('customer_id,lang,name,bn,country,province,city,official_website,buyer_level')
                          ->find();
        if($buyerInfo){
            //通过顾客id查询用户信息
            $buyerAccount = new BuyerAccountModel();
            $userInfo = $buyerAccount->field('email,user_name,phone,first_name,last_name,status')
                ->where(array('customer_id' => $buyerInfo['customer_id']))
                ->find();
            //通过顾客id查询用户邮编
            $buyerAddress = new BuyerAddressModel();
            $zipCode = $buyerAddress->field('zipcode')->where(array('customer_id' => $buyerInfo['customer_id']))->find();
            $info = array_merge($buyerInfo,$userInfo);
            $info['zipCode'] = $zipCode;

            return $info;
        } else{
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition){
        if ($condition['customer_id']) {
            $where['customer_id'] = $condition['customer_id'];
        }
        if ($condition['name']) {
            $data['name'] = $condition['name'];
        }
        if ($condition['bn']) {
            $data['bn'] = $condition['bn'];
        }
        if ($condition['country']) {
            $data['country'] = $condition['country'];
        }
        if ($condition['official_website']) {
            $data['official_website'] = $condition['official_website'];
        }
        if ($condition['buyer_level']) {
            $data['buyer_level'] = $condition['buyer_level'];
        }
        if ($condition['province']) {
            $data['province'] = $condition['province'];
        }
        if ($condition['city']) {
            $data['city'] = $condition['city'];
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

}
