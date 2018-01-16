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
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'floor_name', 'like');
        $this->_getValue($where, $condition, 'cat_name', 'like');
        $this->_getValue($where, $condition, 'cat_no');
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
    public function getExit($country_bn, $cat_no, $lang, $id = null) {

        $where['country_bn'] = trim($country_bn);
        $where['cat_no'] = trim($cat_no);

        $where['lang'] = trim($lang);
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
        $condition['cat_no'] = trim($condition['cat_no']);
        $condition['lang'] = trim($condition['lang']);
        if ($condition['cat_no']) {
            $show_cat_model = new ShowCatModel();
            $condition['cat_name'] = $show_cat_model
                            ->field('name, parent_cat_no')
                            ->where([
                                'cat_no' => $condition['cat_no'],
                                'status' => ShowCatModel::STATUS_VALID,
                                'lang' => $condition['lang'],
                                'country_bn' => $condition['country_bn']
                            ])->getField('name');
        }

        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['floor_id'] = intval($condition['floor_id']);
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
        $condition['cat_no'] = trim($condition['cat_no']);
        $condition['lang'] = trim($condition['lang']);
        if ($condition['cat_no']) {
            $show_cat_model = new ShowCatModel();
            $condition['cat_name'] = $show_cat_model
                            ->field('name, parent_cat_no')
                            ->where([
                                'cat_no' => $condition['cat_no'],
                                'status' => ShowCatModel::STATUS_VALID,
                                'lang' => $condition['lang'],
                                'country_bn' => $condition['country_bn']
                            ])->getField('name');
        }
        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['floor_id'] = intval($condition['floor_id']);
        $condition['deleted_flag'] = $condition['deleted_flag'] == 'Y' ? 'Y' : 'N';
        $data = $this->create($condition);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;

        return $this->where(['id' => $id])->save($data);
    }

    /**
     * Description of 更新产品数量
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function addCats($floor_id, $country_bn, $lang, $cat_nos) {


        foreach ($cat_nos as $cat_no) {
            $condition = [
                'floor_id' => $floor_id,
                'country_bn' => $country_bn,
                'lang' => $lang,
                'cat_no' => $cat_no
            ];
            $flag = $this->createData($condition);

            if (!$flag) {
                $this->rollback();
                return false;
            }
        }

        return true;
    }

}
