<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Adschannel
 * @author  zhongyg
 * @date    2017-12-1 9:29:23
 * @version V2.0
 * @desc
 */
class AdsChannelModel extends Model {

    //put your code here
    protected $tableName = 'ads_channel';
    protected $dbName = 'erui_ads'; //数据库名称

//    protected $autoCheckFields = false;

    public function __construct() {
        parent::__construct();
    }

    /*
     * 获取广告频道列表
     */

    public function getList($country_bn, $type = 'HOME', $lang = 'en', $order = 'sort_order desc') {
        $where['deleted_flag'] = 'N';
        $where['status'] = 'VALID';
        $where['country_bn'] = $country_bn;
        $where['type'] = $type;
        $where['lang'] = $lang;

        $data = $this->where($where)->limit(0, 10)->order($order)->select();
        if ($data) {
            return $data;
        } else {
            $where['country_bn'] = 'China';
            $data = $this->where($where)->limit(0, 10)->order($order)->select();
        }
    }

    /*
     * 获取广告频道列表
     */

    public function getInfo($country_bn, $type = 'HOME', $lang = 'en', $order = 'sort_order desc') {
        $where['deleted_flag'] = 'N';
        $where['status'] = 'VALID';
        $where['country_bn'] = $country_bn;
        $where['type'] = $type;
        $where['lang'] = $lang;

        $data = $this->where($where)->limit(0, 10)->order($order)->find();
        if ($data) {
            return $data;
        } elseif ($type == 'HOME') {
            $where['country_bn'] = 'China';
            $data = $this->where($where)->limit(0, 10)->order($order)->select();
        } else {
            return [];
        }
    }

}
