<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货楼层关键词
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class HomeCountryNavModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_country_nav';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'nav_name', 'like');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'lang');

        return $where;
    }

    /**
     * Description of 判断现货楼层是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($condition, $id = null) {

        $where['country_bn'] = trim($condition['country_bn']);
        $where['nav_name'] = trim($condition['nav_name']);
        $where['nav_url'] = trim($condition['nav_url']);
        $where['lang'] = trim($condition['lang']);
        if ($id) {
            $where['id'] = ['neq', $id];
        }
        return $this->where($where)->getField('id');
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

    /**
     * Description of 获取SPU关联列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  SPU关联
     */
    public function getCont($condition) {
        $where = $this->_getCondition($condition);
        return $this->where($where)
                        ->count();
    }

    /**
     * Description of 获取现货楼层详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function getInfo($id) {
        $where['id'] = $id;

        return $this->where($where)->find();
    }

    /**
     * Description of 新加现货楼层
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function createData($condition) {
        $condition['country_bn'] = trim($condition['country_bn']);
        $condition['nav_name'] = trim($condition['nav_name']);
        $condition['nav_url'] = trim($condition['nav_url']);
        $condition['sort_order'] = intval($condition['sort_order']);

        $condition['deleted_flag'] = 'N';
        $data = $this->create($condition);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = defined('UID') ? UID : 0;
        return $this->add($data);
    }

    /**
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function updateData($condition, $id) {
        $condition['country_bn'] = trim($condition['country_bn']);

        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['nav_name'] = trim($condition['nav_name']);
        $condition['nav_url'] = trim($condition['nav_url']);
        $condition['deleted_flag'] = 'N';
        $data = $this->create($condition);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;

        return $this->where(['id' => $id])->save($data);
    }

}
