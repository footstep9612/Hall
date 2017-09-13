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
class BuyercontactModel extends PublicModel
{

    //put your code here
    protected $dbName = 'erui2_buyer';
    protected $tableName = 'buyer_contact';

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }


    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['buyer_id'])){
            $arr['buyer_id'] = $create['buyer_id'];
        }
        if(isset($create['first_name'])){
            $arr['first_name'] = $create['first_name'];
        }
        if(isset($create['last_name'])){
            $arr['last_name'] = $create['last_name'];
        }
        if(isset($create['gender'])){
            $arr['gender'] = $create['gender'];
        }
        if(isset($create['title'])){
            $arr['title'] = $create['title'];
        }
        if(isset($create['phone'])){
            $arr['phone'] = $create['phone'];
        }
        if(isset($create['email'])){
            $arr['email'] = $create['email'];
        }
        if(isset($create['remarks'])){
            $arr['remarks'] = $create['remarks'];
        }
        if(isset($create['created_by'])){
            $arr['created_by'] =$create['created_by'];
        }
        $arr['created_at'] =date("Y-m-d H:i:s");
        if(isset($create['fax'])){
            $arr['fax'] =$create['fax'];
        }
        if(isset($create['country_code'])){
            $arr['country_code'] =$create['country_code'];
        }
        if(isset($create['country_bn'])){
            $arr['country_bn'] =$create['country_bn'];
        }
        if(isset($create['province'])){
            $arr['province'] =$create['province'];
        }
        if(isset($create['city'])){
            $arr['city'] =$create['city'];
        }
        if(isset($create['address'])){
            $arr['address'] =$create['address'];
        }
        if(isset($create['zipcode'])){
            $arr['zipcode'] =$create['zipcode'];
        }
        try{
            $data = $this->create($arr);
            return $this->add($data);
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }

    }
    public function info($data) {
        if ($data['id']) {
            $info = $this->where(array("id" => $data['id']))
                ->find();
            return $info;
        } else {
            return false;
        }
    }
    public function getlist($data) {
        if ($data['buyer_id']) {
            $list = $this->where($data)
                ->order('id desc')
                ->select();
            return $list;
        }
        return false;

    }
    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition,$where){
        if(isset($condition['buyer_id'])){
            $arr['buyer_id'] = $condition['buyer_id'];
        }
        if(isset($condition['first_name'])){
            $arr['first_name'] = $condition['first_name'];
        }
        if(isset($condition['last_name'])){
            $arr['last_name'] = $condition['last_name'];
        }
        if(isset($condition['gender'])){
            $arr['gender'] = $condition['gender'];
        }
        if(isset($condition['title'])){
            $arr['title'] = $condition['title'];
        }
        if(isset($condition['phone'])){
            $arr['phone'] = $condition['phone'];
        }
        if(isset($condition['email'])){
            $arr['email'] = $condition['email'];
        }
        if(isset($condition['remarks'])){
            $arr['remarks'] = $condition['remarks'];
        }
        if(isset($condition['created_by'])){
            $arr['created_by'] =$condition['created_by'];
        }
        if(isset($condition['fax'])){
            $arr['fax'] =$condition['fax'];
        }
        if(isset($condition['country_code'])){
            $arr['country_code'] =$condition['country_code'];
        }
        if(isset($condition['country_bn'])){
            $arr['country_bn'] =$condition['country_bn'];
        }
        if(isset($condition['province'])){
            $arr['province'] =$condition['province'];
        }
        if(isset($condition['city'])){
            $arr['city'] =$condition['city'];
        }
        if(isset($condition['address'])){
            $arr['address'] =$condition['address'];
        }
        if(isset($condition['zipcode'])){
            $arr['zipcode'] =$condition['zipcode'];
        }
        return $this->where($where)->save($arr);
    }
}
