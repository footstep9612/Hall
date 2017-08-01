<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TransBoxType
 * @author  zhongyg
 * @date    2017-8-1 16:45:28
 * @version V2.0
 * @desc   运输方式对应发货箱型
 */
class TransBoxTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_dict';
    protected $tableName = 'trans_box_type';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

}
