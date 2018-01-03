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
class StockCountryModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock_country';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 判断国家是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($country_bn, $lang = 'en') {

        $where['country_bn'] = trim($country_bn);
        $where['show_flag'] = 'Y';
        $where['lang'] = $lang;
        return $this->where($where)->field('id,country_bn')->find();
    }

}
