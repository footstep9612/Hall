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
class System_FuncPermModel extends PublicModel {

    //put your code here
    protected $tableName = 'func_perm';
    Protected $autoCheckFields = true;
    protected $table_name = 'func_perm';
    protected $dbName = 'erui_sys';

    public function __construct() {
        parent::__construct();
    }

}
