<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Solution
 * @author  zhongyg
 * @date    2018-5-9 11:41:42
 * @version V2.0
 * @desc
 */
class SolutionModel extends PublicModel {

    //put your code here
    protected $tableName = 'sol_content';
    protected $dbName = 'erui_cms';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where['status'] = 99;
        if (!empty($condition['catids'])) {
            $where['catid'] = ['in', $condition['catids']];
        }
        $where[] = 'thumb is not null';
        return $where;
    }

    public function getList($condition) {
        $where = $this->_getCondition($condition);
        list($from, $size) = $this->_getPage($condition);

        return $this->field('catid,thumb,title,id,description')->where($where)
                        ->order('listorder desc')
                        ->limit($from, $size)
                        ->select();
    }

    public function getCount($condition) {
        $where = $this->_getCondition($condition);


        return $this->where($where)
                        ->count();
    }

    public function Info($id) {

        return $this->field('catid,thumb,title,id,description,username,inputtime')->where(['id' => $id])
                        ->find();
    }

}
