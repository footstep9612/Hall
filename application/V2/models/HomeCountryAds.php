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
class HomeCountryAdsModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_country_ads';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'id');
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'img_name', 'like');
        $this->_getValue($where, $condition, 'group');
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
    public function getExit($country_bn, $img_name, $img_url, $group, $lang, $id = null) {

        $where['country_bn'] = $country_bn;
        $where['img_name'] = $img_name;
//        $where['img_url'] = $img_url;
        $where['group'] = $group;
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
    public function getInfo($id) {
        $where['id'] = $id;

        return $this->where($where)->find();
    }

    /**
     * Description of 新加现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function createData($country_bn, $img_name, $img_url, $group, $lang) {

        $data['country_bn'] = $country_bn;
        $data['img_name'] = $img_name;
        $data['img_url'] = $img_url;
        $data['group'] = $group;
        $data['lang'] = $lang;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = defined('UID') ? UID : 0;

        return $this->add($data);
    }

    /**
     * Description of 更新现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function updateData($id, $country_bn, $img_name, $img_url, $group, $lang) {



        $data['country_bn'] = $country_bn;
        $data['img_name'] = $img_name;
        $data['img_url'] = $img_url;
        $data['group'] = $group;
        $data['lang'] = $lang;

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
