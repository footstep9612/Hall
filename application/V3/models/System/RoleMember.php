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
class System_RoleMemberModel extends PublicModel {

    //put your code here
    protected $tableName = 'role_member';
    Protected $autoCheckFields = true;
    protected $table_name = 'role_member';
    protected $dbName = 'erui_sys';

    public function __construct() {
        parent::__construct();
    }

    public function getRoleIdsByUserid($user_id) {

        $data = $this->where(['employee_id' => $user_id,])->getField('role_id', true);

        return $data;
    }

}
