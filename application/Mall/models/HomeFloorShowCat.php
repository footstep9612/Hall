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
class HomeFloorShowCatModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_floor_show_cat';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'floor_id');
        $this->_getValue($where, $condition, 'lang');
        switch ($condition['show_type']) {
            case 'P':
                $where['show_type'] = ['in', ['AMP', 'P', 'MP', 'AP']];
                break;
            case 'M':
                $where['show_type'] = ['in', ['AMP', 'M', 'MP', 'AM']];
                break;
            case 'A':
                $where['show_type'] = ['in', ['AMP', 'A', 'AP', 'AM']];
                break;
            default : $where['show_type'] = ['in', ['AMP', 'P', 'MP', 'AP']];
                break;
        }
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


        return $this->field('cat_name,cat_no,floor_id')
                        ->where($where)
                        ->order('sort_order desc')
                        ->select();
    }

}
