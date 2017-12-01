<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdsGoodsModel
 * @author  zhongyg
 * @date    2017-12-1 9:29:39
 * @version V2.0
 * @desc
 */
class AdsGoodsModel extends Model {

    //put your code here
    protected $tableName = 'ads_goods';
    protected $dbName = 'erui_ads'; //数据库名称

//    protected $autoCheckFields = false;

    public function __construct() {
        parent::__construct();
    }

    /*
     * 获取广告位列表
     */

    public function getList($country_bn, $lang, $group_id, $order = 'sort_order desc') {
        $where['deleted_flag'] = 'N';
        $where['status'] = 'VALID';
        $where['group_id'] = $group_id;
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;

        $data = $this->field($where)->where($where)->limit(0, 10)->order($order)->select();
        if ($data) {
            return $data;
        } else {
            $where['country_bn'] = 'China';
            $data = $this->field('spu,img')->where($where)->limit(0, 10)->order($order)->select();
        }
    }

}
