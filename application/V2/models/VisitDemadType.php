<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zhongyg
 * @desc 汇率列表
 */
class VisitDemadTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_config';
    protected $tableName = 'visit_demand_type';

    public function __construct() {
        parent::__construct();
    }



}
