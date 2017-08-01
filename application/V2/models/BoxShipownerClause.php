<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BoxShipownerClause
 * @author  zhongyg
 * @date    2017-8-1 16:47:33
 * @version V2.0
 * @desc   发货箱型对应船东条款
 */
class BoxShipownerClauseModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_dict';
    protected $tableName = 'box_shipowner_clause';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

}
