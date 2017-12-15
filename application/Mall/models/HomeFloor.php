<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货楼层
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class HomeFloorModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_floor';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'floor_name', 'like');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'onshelf_flag', 'bool');
        $this->_getValue($where, $condition, 'lang');

        return $where;
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function getList($condition) {
        $where = $this->_getCondition($condition);
        list($from, $size) = $this->_getPage($condition);

        return $this->where($where)
                        ->limit($from, $size)
                        ->select();
    }

}
