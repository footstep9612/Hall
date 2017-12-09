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
class StockCostPriceModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock_cost_price';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

}
