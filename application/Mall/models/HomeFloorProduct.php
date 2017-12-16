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
class HomeFloorProductModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_floor_product';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'floor_id', 'string', 'floor_id');
        $this->_getValue($where, $condition, 'lang', 'string', 'lang');
        return $where;
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getList($condition) {


        $where = $this->_getCondition($condition);

        $data = $this->field('spu')
                ->where($where)
                ->order('sort_order desc')
                ->limit(0, 8)
                ->select();
        $spus = [];
        foreach ($data as $spu) {
            $spus[] = $spu['spu'];
        }
        return $spus;
    }

}
