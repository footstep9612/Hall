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
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'floor_id');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'img_name', 'like');


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
        $where['floor_id'] = trim($condition['floor_id']);
        $where['img_name'] = trim($condition['img_name']);

        $where['lang'] = trim($condition['lang']);
        $where['group'] = trim($condition['group']);

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
        $condition['img_url'] = trim($condition['img_url']);
        $condition['lang'] = trim($condition['lang']);
        $condition['img_name'] = trim($condition['img_name']);
        $condition['floor_id'] = trim($condition['floor_id']);
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
    public function updateData($id, $condition) {
        $condition['country_bn'] = trim($condition['country_bn']);
        $condition['img_url'] = trim($condition['img_url']);
        $condition['img_name'] = trim($condition['img_name']);
        $condition['floor_id'] = trim($condition['floor_id']);
        $condition['lang'] = trim($condition['lang']);
        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['deleted_flag'] = 'N';
        $data = $this->create($condition);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;

        return $this->where(['id' => $id])->save($data);
    }

    /**
     * Description of 删除广告
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function DeletedData($id) {


        $data['deleted_flag'] = 'Y';
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $data['deleted_by'] = defined('UID') ? UID : 0;

        return $this->where(['id' => $id])->save($data);
    }

}
