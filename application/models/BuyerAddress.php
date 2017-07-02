<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
<<<<<<< HEAD
 * @author jhw
 */
class BuyerAddressModel extends PublicModel
{

    //put your code here
    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer_address';

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
        if(isset($create['customer_id'])){
            $arr['customer_id'] = $create['customer_id'];
        }
        if(isset($create['lang'])){
            $arr['lang'] = $create['lang'];
        }
        if(isset($create['address'])){
            $arr['address'] = $create['address'];
        }
        if(isset($create['zipcode'])){
            $arr['zipcode'] = $create['zipcode'];
        }
        if(isset($create['longitude'])){
            $arr['longitude'] = $create['longitude'];
        }
        if(isset($create['latitude'])){
            $arr['latitude'] = $create['latitude'];
        }
        if(isset($create['tel_country_code'])){
            $arr['tel_country_code'] = md5($create['tel_country_code']);
        }
        if(isset($create['tel_area_code'])){
            $arr['tel_area_code'] = $create['tel_area_code'];
        }
        if(isset($create['tel_local_number'])){
            $arr['tel_local_number'] =$create['tel_local_number'];
        }
        if(isset($create['tel_ext_number'])){
            $arr['tel_ext_number'] =$create['tel_ext_number'];
        }
        if(isset($create['official_email'])){
            $arr['official_email'] =$create['official_email'];
        }
        $arr['created_at'] =date("Y-m-d H:i:s");
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
    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition,$where){
        if ($condition['address']) {
            $data['address'] = $condition['address'];
        }
        if ($condition['zipcode']) {
            $data['zipcode'] = $condition['zipcode'];
        }
        if ($condition['tel_country_code']) {
            $data['tel_country_code'] = $condition['tel_country_code'];
        }
        if ($condition['tel_area_code']) {
            $data['tel_area_code'] = $condition['tel_area_code'];
        }
        if ($condition['tel_ext_number']) {
            $data['tel_ext_number'] = $condition['tel_ext_number'];
        }
        if ($condition['official_email']) {
            $data['official_email'] = $condition['official_email'];
        }

        return $this->where($where)->save($data);
    }
}
