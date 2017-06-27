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
class BuyerAddressModel extends PublicModel {

    protected $tableName = 'buyer_address';
    protected $dbName = 'erui_buyer'; //数据库名称
    public function __construct($str = '') {

        parent::__construct();
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
        $data = $this->create($arr);
        return $this->add($data);
    }

}
