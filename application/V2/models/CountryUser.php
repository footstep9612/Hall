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
        if($data['user_id']) {
            $this->where(['employee_id' => $data['user_id']])->delete();
            if (!empty($data['country_bns'])) {
                $country_arr = explode(",", $data['country_bns']);
            }
            for ($i = 0; $i < count($country_arr); $i++) {
                $arr['country_bn'] = $country_arr[$i];
                $arr['employee_id'] = $data['user_id'];
                $this->create_data($arr);
            }
        }
        return true ;
    }
    /*
     * 获取用户国家
     */
    public function userCountry($user_id,$pid = ''){
        if($user_id){
            $sql = 'SELECT country_member.*,country.`id`,`lang`,`region_bn`,`code`,`bn`,`name`,`int_tel_code`,`time_zone`,`status`,`deleted_flag`  ';
            $sql .= ' FROM  `country_member` ';
            $sql .= ' LEFT JOIN  `erui_dict`.`country` ON `erui_dict`.`country`.`bn` =`country_member`.`country_bn` and `erui_dict`.`country`.`lang` ="zh"';
            $sql .= " WHERE 1=1  ";
            if(!empty($user_id)) {
                $sql .= ' and `country_member`.`employee_id` =' . $user_id;
            }
            $sql .= ' group by country_member.`country_bn`';
            $sql .= ' order by country.`id` desc';
            return $this->query( $sql );
        }
    }
    /*
    * 新增数据
    * @param  mix $createcondition 新增条件
    * @return bool
    * @author jhw
    */
    public function create_data($create= []) {
        $data = $this->create($create);
        return $this->add($data);
    }
}
