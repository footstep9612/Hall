<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class System_CountryMemberModel extends PublicModel {

    //put your code here
    protected $tableName = 'country_member';
    Protected $autoCheckFields = true;
    protected $table_name = 'country_member';
    protected $dbName = 'erui_sys';

    public function __construct() {
        parent::__construct();
    }

    public function getCountryBnsByUserid($user_id) {

        return $this->where(['employee_id' => $user_id])->getField('country_bn', true);
    }

}
