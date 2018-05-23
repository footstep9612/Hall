<?php

/*
 * @desc 部门成员模型
 *
 * @author liujf
 * @time 2017-08-29
 */

class OrgMemberModel extends PublicModel {

    protected $dbName = 'erui_sys';
    protected $tableName = 'org_member';

    public function __construct() {
        parent::__construct();
    }

}
