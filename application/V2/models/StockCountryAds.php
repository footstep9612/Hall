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
class StockCountryAdsModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock_country_ads';
    protected $dbName = 'erui_stock';

    const SHOW_TYPE_P = 'P';
    const SHOW_TYPE_A = 'A';
    const SHOW_TYPE_M = 'M';
    const SHOW_TYPE_AP = 'AP';
   const SHOW_TYPE_PM = 'PM';
    const SHOW_TYPE_AM = 'AM';
    const SHOW_TYPE_APM = 'APM';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'img_name', 'like');
        $this->_getValue($where, $condition, 'group');
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
            case self::SHOW_TYPE_PM:
                $where['show_type'] = self::SHOW_TYPE_PM;
                break;
            case self::SHOW_TYPE_APM:
                $where['show_type'] = self::SHOW_TYPE_APM;
                break;
        }
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
    public function getExit($country_bn, $img_name, $img_url, $group, $lang, $id = null, $show_type = 'P') {

        $where['country_bn'] = $country_bn;
        $where['img_name'] = $img_name;
        $where['img_url'] = $img_url;
        $where['group'] = $group;
        $where['lang'] = $lang;
        if ($id) {
            $where['id'] = ['neq', $id];
        }
        switch ($show_type) {
            case 'P':
                $where['show_type'] = ['in', ['APM', 'P', 'PM', 'AP']];
                break;
            case 'M':
                $where['show_type'] = ['in', ['APM', 'M', 'PM', 'AM']];
                break;
            case 'A':
                $where['show_type'] = ['in', ['APM', 'A', 'AP', 'AM']];
                break;
            default : $where['show_type'] = ['in', ['APM', 'P', 'PM', 'AP']];
                break;
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
    public function createData($country_bn, $img_name, $img_url, $link, $group, $lang, $show_type = self::SHOW_TYPE_P) {

        $data['country_bn'] = $country_bn;
        $data['img_name'] = $img_name;
        $data['img_url'] = $img_url;
        $data['group'] = $group;
        $data['link'] = $link;
        $data['lang'] = $lang;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = defined('UID') ? UID : 0;
        switch ($show_type) {
            case self::SHOW_TYPE_A:
                $data['show_type'] = self::SHOW_TYPE_A;
                break;
            case self::SHOW_TYPE_P:
                $data['show_type'] = self::SHOW_TYPE_P;
                break;
            case self::SHOW_TYPE_M:
                $data['show_type'] = self::SHOW_TYPE_M;
                break;
            case self::SHOW_TYPE_PM:
                $data['show_type'] = self::SHOW_TYPE_PM;
                break;
            case self::SHOW_TYPE_AP:
                $data['show_type'] = self::SHOW_TYPE_AP;
                break;
            case self::SHOW_TYPE_AM:
                $data['show_type'] = self::SHOW_TYPE_AM;
                break;
            case self::SHOW_TYPE_APM:
                $data['show_type'] = self::SHOW_TYPE_APM;
                break;
            default : $data['show_type'] = self::SHOW_TYPE_P;
                break;
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
    public function updateData($id, $country_bn, $img_name, $img_url, $link, $group, $lang, $show_type = null) {
        $data['country_bn'] = $country_bn;
        $data['img_name'] = $img_name;
        $data['img_url'] = $img_url;
        $data['group'] = $group;
        $data['lang'] = $lang;
        $data['link'] = $link;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;
        switch ($show_type) {
            case self::SHOW_TYPE_A:
                $data['show_type'] = self::SHOW_TYPE_A;
                break;
            case self::SHOW_TYPE_P:
                $data['show_type'] = self::SHOW_TYPE_P;
                break;
            case self::SHOW_TYPE_M:
                $data['show_type'] = self::SHOW_TYPE_M;
                break;
            case self::SHOW_TYPE_PM:
                $data['show_type'] = self::SHOW_TYPE_PM;
                break;
            case self::SHOW_TYPE_AP:
                $data['show_type'] = self::SHOW_TYPE_AP;
                break;
            case self::SHOW_TYPE_AM:
                $data['show_type'] = self::SHOW_TYPE_AM;
                break;
            case self::SHOW_TYPE_APM:
                $data['show_type'] = self::SHOW_TYPE_APM;
                break;
        }
        return $this->where(['id' => $id])->save($data);
    }

    /**
     * 删除
     * @param $id
     * @return bool
     */
    public function deleteData($id) {
        $data['deleted_flag'] = 'Y';
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $data['deleted_by'] = defined('UID') ? UID : 0;

        return $this->where(['id' => $id])->save($data);
    }

}
