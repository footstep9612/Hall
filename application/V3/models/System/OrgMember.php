<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Org
 * @author  zhongyg
 * @date    2017-8-5 9:54:14
 * @version V2.0
 * @desc
 */
class System_OrgMemberModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_sys';
    protected $tableName = 'org_member';

    public function __construct() {
        parent::__construct();
    }

    public function getOrgIdsByUserid($user_id) {

        return $this->where(['employee_id' => $user_id])->getField('org_id', true);
    }

}
