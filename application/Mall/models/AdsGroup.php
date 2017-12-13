<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Adsgroup
 * @author  zhongyg
 * @date    2017-12-1 9:30:05
 * @version V2.0
 * @desc
 */
class AdsGroupModel extends Model {

    //put your code here
    protected $tableName = 'ads_group';
    protected $dbName = 'erui_ads'; //数据库名称

//    protected $autoCheckFields = false;

    public function __construct() {
        parent::__construct();
    }

    /*
     * 获取广告位列表
     */

    public function getList($country_bn, $lang, $channel_id, $parent_id = 0, $order = 'sort_order desc') {
        $where['deleted_flag'] = 'N';
        $where['status'] = 'VALID';
        $where['channel_id'] = $channel_id;
        $where['parent_id'] = $parent_id;
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;

        $data = $this->where($where)->limit(0, 10)->order($order)->select();
        if ($data) {
            return $data;
        } else {
            $where['country_bn'] = 'China';
            $data = $this->where($where)->limit(0, 10)->order($order)->select();
        }
    }

}
