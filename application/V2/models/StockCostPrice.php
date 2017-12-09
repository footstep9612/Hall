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

    private function _getCondition($country_bn, $sku) {
        $where = ['deleted_flag' => 'N',
            'country_bn' => $country_bn,
            'sku' => $sku,
        ];



        return $where;
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getList($country_bn, $sku) {
        $where = $this->_getCondition($country_bn, $sku);
        return $this->field('id,supplier_id,min_price,max_price,max_promotion_price,'
                                . 'min_promotion_price,price_unit,price_cur_bn,min_purchase_qty,'
                                . 'max_purchase_qty,trade_terms_bn,price_validity_start,price_validity_end')
                        ->where($where)
                        ->select();
    }

    private function getSpu($sku, $lang) {
        $where = ['deleted_flag' => 'N',
            'lang' => $lang,
            'sku' => $sku,
        ];
        $goods_model = new GoodsModel();
        return $goods_model->where($where)->getField('spu');
    }

    /**
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function updateDatas($country_bn, $lang, $sku, $cost_prices) {

        $where['sku'] = $sku;
        $where['country_bn'] = $country_bn;
        $this->where($where)->save(['deleted_flag' => 'Y']);

        foreach ($cost_prices as $cost_price) {

            $id = empty($cost_price['id']) ? null : $cost_price['id'];
            if (isset($cost_price['id'])) {
                unset($cost_price['id']);
            }

            $data = $this->create($cost_price);
            $data['spu'] = $this->getSpu($sku, $lang);
            $data['sku'] = $sku;
            $data['country_bn'] = $country_bn;
            $flag = false;
            if (empty($id)) {
                $data['created_by'] = defined('UID') ? UID : 0;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['deleted_flag'] = 'N';
                $flag = $this->add($data);
            } else {
                $data['updated_by'] = defined('UID') ? UID : 0;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['deleted_flag'] = 'N';
                $flag = $this->where(['id' => $id])->save($data);
            }
            if (!$flag) {
                return false;
            }
        }

        return true;
    }

    /**
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function updateData($country_bn, $lang, $sku) {


        $goods_cost_price_model = new GoodsCostPriceModel();
        $field = 'supplier_id,price as min_price,max_price,price_unit,price_cur_bn,'
                . 'min_purchase_qty,max_purchase_qty,pricing_date,price_validity_start,'
                . 'price_validity as price_validity_end';
        $cost_prices = $goods_cost_price_model->field($field)
                        ->where(['sku' => $sku, 'deleted_flag' => 'N'])->select();

        if ($cost_prices) {

            return $this->updateDatas($country_bn, $lang, $sku, $cost_prices);
        } else {
            return true;
        }
    }

    /**
     * 获取商品价格属性
     * @param array $skus
     * @return array|mixed
     */
    public function getCostPriceBySkus($skus = [], $country_bn) {
        $where = array(
            'sku' => ['in', $skus],
            'deleted_flag' => 'N',
            'country_bn' => $country_bn,
            'price_validity_end' => ['gt', date('Y-m-d H:i:s')],
            'status' => 'VALID'
        );
        $field = 'sku,supplier_id,min_price,max_price,max_promotion_price,min_promotion_price,price_unit,price_cur_bn,min_purchase_qty,max_purchase_qty,trade_terms_bn,price_validity_start,price_validity_end';
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
