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
class CountryUserModel extends PublicModel {

    //put your code here
    protected $tableName = 'country_member';
    protected $table = 'country_member';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    public function addCountry($data){
        if(!empty($data['country_bns'])){
            $country_arr = explode(",",$data['country_bns']);
        }
        for($i=0;$i<count($country_arr);$i++){
            $arr['country_bn'] = $country_arr[$i];
            $arr['employee_id'] = $data['user_id'];
            $info = $this -> where($arr)->select();
            if(!$info){
                $this -> create_data($arr);
            }
        }
        return true ;
    }

}
