<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货
 * @author  zhongyg
 * @date    2017-12-6 9:07:59
 * @version V2.0
 * @desc
 */
class StockModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['s.deleted_flag' => 'N', 's.stock' => ['gt', 0]];
        $where['s.country_bn'] = trim($condition['country_bn']);
        $where['s.lang'] = $condition['lang'];
        $this->_getValue($where, $condition, 'keyword', 'like', 's.show_name');
        $this->_getValue($where, $condition, 'floor_id', 'string', 's.floor_id');

        return $where;
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getListByKeyword($condition) {

        $where = $this->_getCondition($condition);
        list($from, $size) = $this->_getPage($condition);

        return $this->alias('s')
                        ->field('s.sku,s.spu,s.show_name,s.stock,s.spu,s.country_bn,s.model')
                        ->where($where)
                        ->order('sort_order desc')
                        ->limit($from, $size)
                        ->select();
    }

    public function getCountByKeyword($condition) {

        $where = $this->_getCondition($condition);

        return $this->alias('s')
                        ->where($where)
                        ->count();
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getList($country_bn, $lang, $floor_id) {

        //$stock_cost_price_model = new StockCostPriceModel();

        $where = ['s.deleted_flag' => 'N'];
        $where['s.country_bn'] = trim($country_bn);
        $where['s.floor_id'] = trim($floor_id);
        $where['s.lang'] = $lang;
        $where['s.stock'] = ['gt', 0];
        /* 有问题 一个现货 有多个价格体系时 可能重复显示 */

        return $this->alias('s')
                        ->field('s.sku,s.spu,s.show_name,s.stock,s.spu,s.country_bn')
                        ->where($where)
                        ->order('sort_order desc')
                        ->select();
    }

    /**
     * Description of 判断国家现货是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($country_bn, $lang) {
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;
        return $this->where($where)->field('id,floor_id')->find();
    }

}
