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
        $this->_getValue($where, $condition, 'lang');
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

        list($row_start, $pagesize) = $this->_getPage($condition);
        return $this->where($where)
                        ->order('id desc')
                        ->limit($row_start, $pagesize)
                        ->select();
    }

    /**
     * 获取数据条数
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getCount($condition) {
        $where = $this->_getCondition($condition);


        try {
            $count = $this->where($where)
                    ->count('id');


            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
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
    public function createData($country_bn, $show_flag, $lang = 'en', $display_position = null) {

        $data['country_bn'] = $country_bn;
        $data['lang'] = $lang;
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
    public function updateData($id, $country_bn, $show_flag, $lang = 'en', $display_position = null) {



        $data['country_bn'] = $country_bn;
        $data['lang'] = $lang;
        $data['show_flag'] = $show_flag == 'Y' ? 'Y' : 'N';
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;
        if ($display_position) {
            $data['display_position'] = $display_position;
        }
        return $this->where(['id' => $id])->save($data);
    }

}
