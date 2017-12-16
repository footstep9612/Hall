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
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'display_position');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'show_flag', 'bool');
        return $where;
    }

    /**
     * Description of 获取现货国家列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getList($condition) {
        $where = $this->_getCondition($condition);
        return $this->where($where)->select();
    }

    /**
     * Description of 判断国家是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($country_bn, $id = null) {

        $where['country_bn'] = $country_bn;
        if ($id) {
            $where['id'] = ['neq', $id];
        }
        return $this->where($where)->field('id')->find();
    }

    /**
     * Description of 获取现货国家详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getInfo($country_bn) {
        $where['country_bn'] = $country_bn;

        return $this->where($where)->find();
    }

    /**
     * Description of 新加现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function createData($country_bn, $show_flag, $display_position = null) {

        $data['country_bn'] = $country_bn;
        $data['show_flag'] = $show_flag == 'Y' ? 'Y' : 'N';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = defined('UID') ? UID : 0;
        if ($display_position) {
            $data['display_position'] = $display_position;
        }
        return $this->add($data);
    }

    /**
     * Description of 更新现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function updateData($id, $country_bn, $show_flag, $display_position) {



        $data['country_bn'] = $country_bn;
        $data['show_flag'] = $show_flag == 'Y' ? 'Y' : 'N';
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;
        if ($display_position) {
            $data['display_position'] = $display_position;
        }
        return $this->where(['id' => $id])->save($data);
    }

}
