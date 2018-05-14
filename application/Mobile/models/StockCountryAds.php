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

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        if (isset($condition['group']) && $condition['group'] == 'ALL') {
            $where['group'] = ['in', ['BANNER', 'HOT', 'RHOT', 'BACKGROUP']];
        } else {
            $this->_getValue($where, $condition, 'group');
        }
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
        $this->_getValue($where, $condition, 'lang');
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
        $data = $this->field('img_name,img_url,group,link')
                ->where($where)
                ->order('sort_order desc')
                ->select();
        return $data;
    }

    /**
     * Description of 获取现货国家列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getALLList($condition) {
        $condition['group'] = 'ALL';
        $where = $this->_getCondition($condition);
        $data = $this->field('img_name,img_url,group,link')
                ->where($where)
                ->order('sort_order desc')
                ->select();
        $ret = [];
        foreach ($data as $item) {
            $ret[strtolower($item['group'])][] = $item;
        }
        return $ret;
    }

}
