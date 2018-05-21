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
class ShowCatSolutionModel extends PublicModel {

    //put your code here
    protected $tableName = 'show_cat_solution';
    protected $dbName = 'erui_goods';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['scat.deleted_flag' => 'N'];
        if (!empty($condition['lang'])) {
            $where['scat.lang'] = trim($condition['lang']);
        }
        if (!empty($condition['cat_no'])) {
            $where['scat.cat_no'] = trim($condition['cat_no']);
        }
        if (!empty($condition['solution_id'])) {
            $where['scat.solution_id'] = trim($condition['solution_id']);
        }
        $where['sol.id'] = ['gt', 0];
        $where[] = ' sol.thumb is not null';
        return $where;
    }

    public function getList($condition) {

        $solution_model = new SolutionModel();
        $solution_table = $solution_model->getTableName();
        $where = $this->_getCondition($condition);
        list($from, $size) = $this->_getPage($condition);

        return $this->field('sol.id,sol.catid,sol.thumb,sol.title,scat.cat_no')
                        ->alias('scat')
                        ->where($where)
                        ->join($solution_table . ' sol on sol.id=scat.solution_id', 'left')
                        ->limit($from, $size)
                        ->select();
    }

}
