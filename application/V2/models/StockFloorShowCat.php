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
class StockFloorShowCatModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock_floor_show_cat';
    protected $dbName = 'erui_stock';

    const SHOW_TYPE_P = 'P';
    const SHOW_TYPE_A = 'A';
    const SHOW_TYPE_M = 'M';
    const SHOW_TYPE_AP = 'AP';
    const SHOW_TYPE_MP = 'MP';
    const SHOW_TYPE_AM = 'AM';
    const SHOW_TYPE_AMP = 'AMP';

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
        switch ($condition['show_type']) {
            case self::SHOW_TYPE_P:
                $where['show_type'] = self::SHOW_TYPE_P;
                break;
            case self::SHOW_TYPE_M:
                $where['show_type'] = self::SHOW_TYPE_M;
                break;
            case self::SHOW_TYPE_A:
                $where['show_type'] = self::SHOW_TYPE_A;
                break;
            case self::SHOW_TYPE_AP:
                $where['show_type'] = self::SHOW_TYPE_AP;
                break;
            case self::SHOW_TYPE_AM:
                $where['show_type'] = self::SHOW_TYPE_AM;
                break;
            case self::SHOW_TYPE_MP:
                $where['show_type'] = self::SHOW_TYPE_MP;
                break;
            case self::SHOW_TYPE_AMP:
                $where['show_type'] = self::SHOW_TYPE_AMP;
                break;
        }
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
        $info = [];
        if (empty($condition['show_type']) && !empty($condition['floor_id'])) {
            $stock_floor_model = new StockFloorModel ();
            $info = $stock_floor_model->getInfo($condition['floor_id']);
        } elseif (!empty($condition['show_type'])) {
            $info['show_type'] = $condition['show_type'];
        }
        switch ($info['show_type']) {
            case self::SHOW_TYPE_A:
                $show_type = self::SHOW_TYPE_A;
                break;
            case self::SHOW_TYPE_P:
                $show_type = self::SHOW_TYPE_P;
                break;
            case self::SHOW_TYPE_M:
                $show_type = self::SHOW_TYPE_M;
                break;
            case self::SHOW_TYPE_MP:
                $show_type = self::SHOW_TYPE_MP;
                break;
            case self::SHOW_TYPE_AP:
                $show_type = self::SHOW_TYPE_AP;
                break;
            case self::SHOW_TYPE_AM:
                $show_type = self::SHOW_TYPE_AM;
                break;
            case self::SHOW_TYPE_AMP:
                $show_type = self::SHOW_TYPE_AMP;
                break;
            default : $show_type = self::SHOW_TYPE_P;
                break;
        }
        $condition['show_type'] = $show_type;
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
        $info = [];
        if (empty($condition['show_type']) && !empty($condition['floor_id'])) {
            $stock_floor_model = new StockFloorModel ();
            $info = $stock_floor_model->getInfo($condition['floor_id']);
        } elseif (!empty($condition['show_type'])) {
            $info['show_type'] = $condition['show_type'];
        }
        switch ($info['show_type']) {
            case self::SHOW_TYPE_A:
                $show_type = self::SHOW_TYPE_A;
                break;
            case self::SHOW_TYPE_P:
                $show_type = self::SHOW_TYPE_P;
                break;
            case self::SHOW_TYPE_M:
                $show_type = self::SHOW_TYPE_M;
                break;
            case self::SHOW_TYPE_MP:
                $show_type = self::SHOW_TYPE_MP;
                break;
            case self::SHOW_TYPE_AP:
                $show_type = self::SHOW_TYPE_AP;
                break;
            case self::SHOW_TYPE_AM:
                $show_type = self::SHOW_TYPE_AM;
                break;
            case self::SHOW_TYPE_AMP:
                $show_type = self::SHOW_TYPE_AMP;
                break;
        }
        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['floor_id'] = intval($condition['floor_id']);
        $condition['deleted_flag'] = 'N';
        if ($show_type) {
            $condition['show_type'] = $show_type;
        }
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
    public function addCats($floor_id, $country_bn, $lang, $cat_nos, $show_type = null) {

        $info = [];
        if (empty($show_type) && !empty($floor_id)) {
            $stock_floor_model = new StockFloorModel ();
            $info = $stock_floor_model->getInfo($floor_id);
        } elseif (!empty($show_type)) {
            $info['show_type'] = $show_type;
        }
        switch ($info['show_type']) {
            case self::SHOW_TYPE_A:
                $show_type = self::SHOW_TYPE_A;
                break;
            case self::SHOW_TYPE_P:
                $show_type = self::SHOW_TYPE_P;
                break;
            case self::SHOW_TYPE_M:
                $show_type = self::SHOW_TYPE_M;
                break;
            case self::SHOW_TYPE_MP:
                $show_type = self::SHOW_TYPE_MP;
                break;
            case self::SHOW_TYPE_AP:
                $show_type = self::SHOW_TYPE_AP;
                break;
            case self::SHOW_TYPE_AM:
                $show_type = self::SHOW_TYPE_AM;
                break;
            case self::SHOW_TYPE_AMP:
                $show_type = self::SHOW_TYPE_AMP;
                break;
            default : $show_type = self::SHOW_TYPE_P;
                break;
        }
        foreach ($cat_nos as $cat_no) {
            $condition = [
                'floor_id' => $floor_id,
                'country_bn' => $country_bn,
                'lang' => $lang,
                'cat_no' => $cat_no,
                'show_type' => $show_type
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
