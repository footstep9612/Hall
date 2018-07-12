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
        $where = ['s.deleted_flag' => 'N', 's.stock' => ['gt', 0], 's.show_flag'=>'Y'];
        $where['s.special_id'] = trim($condition['special_id']);
        /*$where['s.country_bn'] = trim($condition['country_bn']);
        $where['s.lang'] = $condition['lang'];*/
        if (!empty($condition['keyword'])) {
            $keyword = trim($condition['keyword']);
            $map['s.show_name'] = ['like', '%' . $keyword . '%'];
            $map['s.sku'] = $keyword;
            $map['s.spu'] = $keyword;
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
//        $this->_getValue($where, $condition, 'keyword', 'like', 's.show_name');
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
    public function getList( $condition ) {
        if(isset($condition['special_id'])){
            $where['s.special_id'] = intval($condition['special_id']);
        }else{
            $where['s.country_bn'] = trim($condition['country_bn']);
            $where['s.lang'] = $condition['lang'];
        }
        $where['s.show_flag'] = 'Y';
        $where['s.deleted_at'] = ['exp','is null'];
        $where['s.floor_id'] = trim($condition['floor_id']);
        if(isset($condition['recommend_home']) && $condition['recommend_home']){
            $where['s.recommend_home'] = 'Y';
        }
        $where['s.stock'] = ['gt', 0];

        /* 有问题 一个现货 有多个价格体系时 可能重复显示 */

        $data = $this->alias('s')
                ->field('DISTINCTROW s.sku,s.spu,s.show_name,s.price,s.price_cur_bn,s.price_symbol,s.stock,s.country_bn,s.special_id,s.price_strategy_type,s.strategy_validity_start,s.strategy_validity_end')
                ->where($where)
                ->order('s.sort_order desc')
                ->select();

        return $data;
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
