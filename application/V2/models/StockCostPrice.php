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
                        ->order('min_purchase_qty asc')
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
        $this->startTrans();
        $this->where($where)->save(['deleted_flag' => 'Y']);
        $current_model = new CurrencyModel();
        try {
            foreach ($cost_prices as $cost_price) {

                $id = empty($cost_price['id']) ? null : $cost_price['id'];
                if (isset($cost_price['id'])) {
                    unset($cost_price['id']);
                }

                if ($cost_price['price_cur_bn']) {
                    $cost_price['price_symbol'] = $current_model->getSymbolByBns($cost_price['price_cur_bn']);
                }
                $data = $this->create($cost_price);
                $data['supplier_id'] = empty($data['supplier_id']) ? 0 : intval($data['supplier_id']);
                $data['max_price'] = floatval($data['max_price']) > 0 ? intval($data['max_price']) : null;
                $data['max_promotion_price'] = floatval($data['max_promotion_price']) > 0 ? intval($data['max_promotion_price']) : null;
                $data['min_promotion_price'] = floatval($data['min_promotion_price']) > 0 ? intval($data['min_promotion_price']) : null;
                $data['min_purchase_qty'] = intval($data['min_purchase_qty']) > 0 ? intval($data['min_purchase_qty']) : 0;
                $data['max_purchase_qty'] = intval($data['max_purchase_qty']) > 0 ? intval($data['max_purchase_qty']) : null;
                if (empty($data['min_price'])) {
                    jsonReturn(null, '-1', '价格不能为空!');
                }
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
                    $this->rollback();
                    return false;
                }
            }
            $this->commit();
            return true;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            $this->rollback();
            return false;
        }
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
                . 'min_purchase_qty,max_purchase_qty,price_validity_start,'
                . 'price_validity as price_validity_end';
        $cost_prices = $goods_cost_price_model->field($field)
                        ->where(['sku' => $sku, 'deleted_flag' => 'N'])->select();

        if ($cost_prices) {
            $cost_prices['min_price'] = null;
            $cost_prices['max_price'] = null;
            $cost_prices['price_cur_bn'] = null;
            $cost_prices['min_purchase_qty'] = null;
            $cost_prices['max_purchase_qty'] = null;
//            $cost_prices['pricing_date'] = null;
            $cost_prices['price_validity_start'] = null;
            $cost_prices['price_validity_end'] = null;
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
    public function getCostPriceBySkus($skus = [], $country_bn = null) {
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
