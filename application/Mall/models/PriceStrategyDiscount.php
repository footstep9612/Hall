<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/23
 * Time: 9:59
 */
class PriceStrategyDiscountModel extends PublicModel {

    //put your code here
    protected $tableName = 'price_strategy_discount';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据id获取价格折扣
     * @param $id
     */
    public function getPriceDiscountById($id) {
        if (empty($id)) {
            return false;
        }
        try {
            $condition = [
                'id' => $id,
                'deleted_at' => ['exp', 'is null'],
                'validity_start' => [['exp', 'is null'], ['elt', date('Y-m-d H:i:s', time())], 'or'],
                'validity_end' => [['exp', 'is null'], ['gt', date('Y-m-d H:i:s', time())], 'or']
            ];
            $result = $this->field('discount,validity_end')->where($condition)->find();
            return $result ? $result : [];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取价格
     * @param $sku
     * @param $country_bn
     * @param $num
     * @param $price
     * @return array
     */
    public function getPrice($sku = '', $country_bn = '', $num = 'MIN', $price = '') {
        if (empty($sku) || empty($country_bn) || empty($price)) {
            return [];
        }
        $condition = [
            'country_bn' => $country_bn,
            'sku' => $sku,
            'deleted_at' => ['exp', 'is null'],
            'validity_start' => [['exp', 'is null'], ['elt', date('Y-m-d H:i:s', time())], 'or'],
            'validity_end' => [['exp', 'is null'], ['gt', date('Y-m-d H:i:s', time())], 'or']
        ];
        $order = 'min_purchase_qty DESC';
        if ($num == 'MIN') {
            $order = 'discount ASC';
        } else {
            $num = is_numeric($num) ? $num : 1;
            $condition['min_purchase_qty'] = ['elt', $num];
            $condition['max_purchase_qty'] = [['egt', $num], ['exp', 'is null'], 'or'];
        }
        $discount = $this->field('discount,validity_start,validity_end,min_purchase_qty,max_purchase_qty')->where($condition)->order($order)->find();
        $priceAry = [];
        if ($discount) {
            $priceAry['price'] = ($discount['discount'] && $price) ? ($price * ($discount['discount'] * 10) / 100) : null;
            $priceAry['discount'] = $discount['discount'];
            if (!empty($discount['validity_end'])) {
                $priceAry['validity'] = round((strtotime($discount['validity_end']) - time()) / (3600 * 24));
            }
        }
        return $priceAry;
    }

    /**
     * 获取价格
     * @param $sku
     * @param $country_bn
     * @param $price
     * @param $symbol
     * @return array
     */
    public function getPriceList($sku = '', $country_bn = '', $price = '', $symbol = []) {
        if (empty($sku) || empty($country_bn) || empty($price)) {
            return [];
        }
        $condition = [
            'country_bn' => $country_bn,
            'sku' => $sku,
            'deleted_at' => ['exp', 'is null'],
            'validity_start' => [['exp', 'is null'], ['elt', date('Y-m-d H:i:s', time())], 'or'],
            'validity_end' => [['exp', 'is null'], ['gt', date('Y-m-d H:i:s', time())], 'or']
        ];
        $order = 'min_purchase_qty DESC';
        $discount = $this->field('discount,validity_start,validity_end,min_purchase_qty,max_purchase_qty')->where($condition)->order($order)->select();
        if ($discount) {
            for ($i = 0; $i < count($discount); $i++) {
                $discount[$i]['price'] = ( $discount[$i]['discount'] && $price) ? ($price * ($discount[$i]['discount'] * 10) / 100) : null;
                if (!empty($symbol)) {
                    $discount[$i] = array_merge($discount[$i], $symbol);
                }
            }
        }
        return $discount ? $discount : [];
    }

    /**
     * 根据国家和SKU获取折扣
     * @param array $skus
     * @param string $country_bn
     * @return array
     */
    public function getDisCountBySkus($skus = [], $country_bn = '') {
        if (empty($skus) || empty($country_bn)) {
            return [];
        }
        $condition = [
            'country_bn' => $country_bn,
            'sku' => ['in', $skus],
            'deleted_at' => ['exp', 'is null'],
            'validity_start' => [['exp', 'is null'], ['elt', date('Y-m-d H:i:s', time())], 'or'],
            'validity_end' => [['exp', 'is null'], ['gt', date('Y-m-d H:i:s', time())], 'or']
        ];
        $order = 'min_purchase_qty DESC';
        $discounts = $this->field('sku,discount,min_purchase_qty,max_purchase_qty')->where($condition)->order($order)->select();
        $ret = [];
        if ($discounts) {
            foreach ($discounts as $discount) {
                $ret[$discount['sku']] = $discount;
            }
        }
        return !empty($ret) ? $ret : [];
    }

}
