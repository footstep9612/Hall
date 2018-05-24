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
class SpecialKeywordModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_mall';
    protected $tableName = 'special_keyword';

    public function __construct() {
        parent::__construct();
    }

    public function getInfo($id){
        try{
            $condition = [
                'id' => $id,
                'deleted_at' => ['exp', 'is null']
            ];
            $result = $this->field('id,special_id,keyword')->where($condition)->find();
            return $result;
        }catch (Exception $e){
            return false;
        }
    }
}
