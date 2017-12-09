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

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getList($country_bn, $lang, $floor_id) {

        $stock_cost_price_model = new StockCostPriceModel();
        $stock_cost_price_table = $stock_cost_price_model->getTableName();
        $where = ['s.deleted_flag' => 'N'];
        $where['s.country_bn'] = trim($country_bn);
        $where['s.floor_id'] = trim($floor_id);
        $where['s.lang'] = $lang;
        $where['scp.price_validity_end'] = ['gt', date('Y-m-d H:i:s')];
        /* 有问题 一个现货 有多个价格体系时 可能重复显示 */

        return $this->alias('s')
                        ->field('s.sku,s.spu,s.show_name,s.stock,s.spu,s.country_bn,'
                                . 'scp.min_price,scp.max_price,scp.supplier_id,scp.price_unit,scp.min_promotion_price,scp.max_promotion_price,'
                                . 'scp.price_cur_bn,scp.min_purchase_qty,scp.max_purchase_qty,scp.price_validity_end,scp.trade_terms_bn')
                        ->join($stock_cost_price_table
                                . ' scp on  scp.sku=s.sku and scp.country_bn=s.country_bn and scp.deleted_flag=\'N\'', 'left')
                        ->where($where)
                        ->select();
    }

}
