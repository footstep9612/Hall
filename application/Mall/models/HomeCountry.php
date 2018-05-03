<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货国家
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class HomeCountryModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_country';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = [];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'display_position');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'show_flag', 'bool');

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
        $this->_getValue($where, $condition, 'lang');
        return $where;
    }

    /**
     * Description of 判断国家是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($country_bn, $lang = 'en', $id = null) {

        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;
        if ($id) {
            $where['id'] = ['neq', $id];
        }
        return $this->where($where)->field('id')->find();
    }

}
