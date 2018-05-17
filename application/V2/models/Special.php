<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SpecialGoods
 * @author  zhongyg
 * @date    2018-05-17 13:38:48
 * @version V2.0
 * @desc
 */
class SpecialModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_mall';
    protected $tableName = 'special';

    public function __construct() {
        parent::__construct();
    }

}
