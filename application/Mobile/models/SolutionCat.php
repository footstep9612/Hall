<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SolutionCat
 * @author  zhongyg
 * @date    2018-5-15 9:36:16
 * @version V2.0
 * @desc
 */
class SolutionCatModel extends PublicModel {

    //put your code here
    protected $tableName = 'category';
    protected $dbName = 'erui_cms';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['catdir' => 'solutions', 'ismenu' => 1];
        $where['language'] = $condition['lang'];
        if ($condition['catid']) {
            $where['catid'] = trim($condition['catid']);
        }
        return $where;
    }

    public function getList($condition) {
        $where = $this->_getCondition($condition);


        return $this->field('catid,catname,arrchildid,letter,image')->where($where)
                        ->order('listorder desc')
                        ->select();
    }

}
