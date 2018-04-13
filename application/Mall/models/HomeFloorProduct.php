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

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getFloorProducts($condition) {


        $where = $this->_getCondition($condition);

        $data = $this->field('spu,floor_id')
                ->where($where)
                ->order('sort_order desc')
                //->limit(0, 8)
                ->select();
        $spus = [];
        $floors = [];
        foreach ($data as $spu) {
            $floors[$spu['floor_id']][] = $spu['spu'];
            $spus [] = $spu['spu'];
        }
        $products = $this->_getProducts($condition, $spus);
        $ret = [];
        foreach ($floors as $floor_id => $spus) {

            foreach ($spus as $spu) {
                if (isset($products[$spu]) && $products[$spu] && count($ret[$floor_id]) < 8) {
                    $ret[$floor_id] [] = $products[$spu];
                }
            }
        }


        return $ret;
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    private function _getProducts($condition, $spus) {
        $country_bn = $condition['country_bn'];
        $lang = $condition['lang'];
        $condition['spus'] = $spus;
        $condition['onshelf_flag'] = 'Y';
        $condition['pagesize'] = count($spus);
        $model = new EsProductModel();
        $data = $model->getNewProducts($condition, $lang, $country_bn);

        foreach ($data[0]['hits']['hits'] as $item) {
            $list[$item["_source"]['spu']] = $item["_source"];

            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$item["_source"]['spu']]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$item["_source"]['spu']]['img'] = null;
            }
            $list[$item["_source"]['spu']]['id'] = $item['_id'];
            $list[$item["_source"]['spu']]['specs'] = $list[$item["_source"]['spu']]['attrs']['spec_attrs'];
            $list[$item["_source"]['spu']]['attachs'] = json_decode($list[$item["_source"]['spu']]['attachs'], true);
        }

        unset($data, $model, $condition, $lang, $country_bn);

        return $list;
    }

}
