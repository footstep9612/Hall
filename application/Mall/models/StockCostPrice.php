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

    /**
     * 获取商品价格属性
     * @param array $skus
     * @return array|mixed
     */
    public function getCostPriceBySkus($skus = [], $country_bn) {
        $date = date('Y-m-d H:i:s');
        $where = array(
            'sku' => ['in', $skus],
            'deleted_flag' => 'N',
            'country_bn' => $country_bn,
            'price_validity_start' => ['elt', $date],
            //  'price_validity_end' => ['gt', date('Y-m-d H:i:s')],
            'status' => 'VALID'
        );
        $table_name = $this->getTableName();
        $map2[$table_name . '.`price_validity_end`'] = ['gt', $date];
        $map2['price_validity_end'] = '0000-00-00';
        $map2[] = 'price_validity_end is null';
        $map2['_logic'] = 'or';
        $where[]['_complex'] = $map2;
        $field = 'sku,supplier_id,min_price,price_symbol,max_price,max_promotion_price,min_promotion_price,price_unit,price_cur_bn,min_purchase_qty,max_purchase_qty,trade_terms_bn,price_validity_start,price_validity_end';
        $result = $this->field($field)->where($where)
                ->order('id asc')
                ->select();

        if ($result) {
            $data = array();
            //按类型分组

            foreach ($result as $item) {
                $data[$item['sku']][] = $item;
            }
            $result = $data;
            return $result;
        }
        return array();
    }

}
