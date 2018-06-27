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
class HomeFloorAdsModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_floor_ads';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'floor_id');
        $this->_getValue($where, $condition, 'group');
        $this->_getValue($where, $condition, 'lang');
        switch ($condition['show_type']) {
            case 'P':
                $where['show_type'] = ['in', ['APM', 'P', 'PM', 'AP']];
                break;
            case 'M':
                $where['show_type'] = ['in', ['APM', 'M', 'PM', 'AM']];
                break;
            case 'A':
                $where['show_type'] = ['in', ['APM', 'A', 'AP', 'AM']];
                break;
            default : $where['show_type'] = ['in', ['APM', 'M', 'PM', 'AM']];
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
        $start_no = 0;

        $this->field('img_name,img_url,group,link,floor_id')
                ->where($where)
                ->order('sort_order desc');
        if (isset($condition['pagesize'])) {
            $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        }
        if (isset($condition['current_no'])) {
            $start_no = intval($condition['current_no']) > 0 ? (intval($condition['current_no']) * $pagesize - $pagesize) : 0;
        }
        if ($pagesize) {
            $this->limit($start_no, $pagesize);
        }
        return $this->select();
    }

}
