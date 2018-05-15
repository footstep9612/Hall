<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SolutionDetail
 * @author  zhongyg
 * @date    2018-5-15 9:37:18
 * @version V2.0
 * @desc
 */
class SolutionDetailModel extends PublicModel {

    //put your code here
    protected $tableName = 'sol_content_data';
    protected $dbName = 'erui_cms';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {

        if (!empty($condition['ids'])) {
            $where['id'] = ['in', $condition['ids']];
        }

        return $where;
    }

    public function getList($condition) {
        $where = $this->_getCondition($condition);
        list($from, $size) = $this->_getPage($condition);

        return $this->field('id,content,readpoint,groupids_view,paginationtype,maxcharperpage,template,paytype,allow_comment,relation,goods')
                        ->where($where)
                        ->order('listorder desc')
                        ->limit($from, $size)
                        ->select();
    }

    public function Info($id) {
        $where = ['id' => intval($id)];


        return $this->field('id,content,readpoint,groupids_view,paginationtype,maxcharperpage,template,paytype,allow_comment,relation,goods')
                        ->where($where)
                        ->find();
    }

}
