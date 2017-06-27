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