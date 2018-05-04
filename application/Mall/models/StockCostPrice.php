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

    /**
     * 根据sku获取价格段
     */
    public function getSkuPriceBySku($sku = '', $country_bn = '') {
        if (!isset($sku) || empty($sku) || !isset($country_bn) || empty($country_bn)) {
            return '';
        }
        $scpTable = $this->getTableName();
        $condition = [
            'sku' => $sku,
            'country_bn' => $country_bn,
            'deleted_flag' => 'N',
            'price_validity_start' => ['elt', date('Y-m-d', time())],
        ];
        $map['price_validity_end'] = ['egt', date('Y-m-d', time())];
        $map[$scpTable . '.price_validity_end'] = ['exp', 'is null'];
        $map['_logic'] = 'or';
        $condition['_complex'] = $map;
        try {
            $priceInfo = $this->field('min_price as price,min_purchase_qty,max_purchase_qty,price_cur_bn,price_symbol')->where($condition)->order('min_purchase_qty ASC')->select();
            return $priceInfo ? $priceInfo : '';
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuPriceBySku:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 根据相应数量返回相应价格
     */
    public function getSkuPriceByCount($sku = '', $country_bn = '', $count = '') {
        if (!isset($sku) || empty($sku) || !isset($country_bn) || empty($country_bn) || !isset($count) || !is_numeric($count)) {
            return '';
        }

        $condition = ['sku' => $sku, 'country_bn' => $country_bn, 'deleted_flag'=>'N', 'price_validity_start' => ['elt', date('Y-m-d', time())], 'min_purchase_qty' => ['elt', $count]];
        try {
            $priceInfo = $this->field('min_price as price,min_purchase_qty,max_purchase_qty,price_validity_end,price_cur_bn,price_symbol')->where($condition)->order('min_purchase_qty DESC')->select();
            if ($priceInfo) {
                foreach ($priceInfo as $item) {
                    if (($item['price_validity_end'] >= date('Y-m-d', time()) || empty($item['price_validity_end'])) && (empty($item['max_purchase_qty']) || $item['max_purchase_qty'] >= $count)) {
                        return $item;
                        break;
                    }
                }
            }
            return '';
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuPriceByCount:' . $e, Log::ERR);
            return false;
        }
    }

}
