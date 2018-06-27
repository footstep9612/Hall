<?php

/**
 * 产品
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 11:45
 */
class GoodsModel extends PublicModel {

    protected $tableName = 'goods';
    protected $dbName = 'erui_goods'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    public function getInfo($sku, $lang, $stock = false, $country_bn = '') {
        if (empty($sku) || empty($lang)) {
            return false;
        }
        try {
            $condition = [
                "sku" => $sku,
                "lang" => $lang,
                "status" => 'VALID',
                "deleted_flag" => 'N'
            ];
            $field_goods = "sku,spu,name,show_name,show_name_loc,lang,model,min_pack_unit,min_pack_naked_qty,nude_cargo_unit,min_order_qty,description,exw_days";
            $goodsInfo = $this->field($field_goods)->where($condition)->find();
            if ($goodsInfo) {
                $productModel = new ProductModel();
                $field_product = "name as spu_name,show_name as spu_show_name,brand,exe_standard,tech_paras,advantages,description as spu_description,profile,principle,app_scope,properties,warranty,customization_flag,customizability,availability,availability_ratings,resp_time,resp_rate,supply_ability";
                $condition_spu = [
                    'spu' => $goodsInfo['spu'],
                    'lang' => $goodsInfo['lang'],
                    'deleted_flag' => 'N',
                    'status' => 'VALID'
                ];
                $productInfo = $productModel->field($field_product)->where($condition_spu)->find();
                if ($productInfo) {
                    $goodsInfo['name'] = empty($goodsInfo['name']) ? $productInfo['spu_name'] : $goodsInfo['name'];
                    $goodsInfo['show_name'] = empty($goodsInfo['show_name']) ? $productInfo['spu_show_name'] : $goodsInfo['show_name'];
                    $goodsInfo['description'] = empty($goodsInfo['description']) ? $productInfo['spu_description'] : $goodsInfo['description'];
                    unset($productInfo['spu_name'], $productInfo['spu_show_name'], $productInfo['spu_description']);
                }
                $goodsInfo = array_merge($goodsInfo, $productInfo);

                if ($stock && $country_bn) {    //现货处理
                    $stockModel = new StockModel();
                    $field_stock = "name,show_name,stock,price,price_strategy_type,price_cur_bn,price_symbol";
                    $condition_stock = [
                        'sku' => $goodsInfo['sku'],
                        'country_bn' => $country_bn,
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ];
                    $stockInfo = $stockModel->field($field_stock)->where($condition_stock)->find();
                    if ($stockInfo) {
                        $goodsInfo['price_strategy_type'] = $stockInfo['price_strategy_type'];
                        $goodsInfo['stock'] = $stockInfo['stock'];
                        $goodsInfo['price_cur_bn'] = $stockInfo['price_cur_bn'];
                        $goodsInfo['price_symbol'] = $stockInfo['price_symbol'];
                        $goodsInfo['name'] = empty($stockInfo['name']) ? $goodsInfo['name'] : $stockInfo['name'];
                        $goodsInfo['show_name'] = empty($stockInfo['show_name']) ? $goodsInfo['show_name'] : $stockInfo['show_name'];
                        $goodsInfo['price'] = $stockInfo['price'];
                        switch ($stockInfo['price_strategy_type']) {
                            case 1:    //阶梯价
                                $scpriceM = new StockCostPriceModel();
                                $goodsInfo['priceList'] = $scpriceM->getSkuPriceBySku($goodsInfo['sku'], $country_bn);
                                $goodsPrice = $this->my_array_multisort($goodsInfo['priceList'], 'price');
                                $goodsInfo['priceAry'] = $goodsPrice[0];
                                break;
                            case 2:    //折扣
                                $psdM = new PriceStrategyDiscountModel();
                                $priceAry = $psdM->getPrice($goodsInfo['sku'], $country_bn, 'MIN', $goodsInfo['price']);
                                $goodsInfo['priceAry'] = $priceAry;
                                break;
                        }
                    } else {
                        return [];
                    }
                }

                //商品属性
                $gaModel = new GoodsAttrModel();
                $field_attr = "spec_attrs";
                $condition_attr = [
                    'sku' => $goodsInfo['sku'],
                    'lang' => $goodsInfo['lang'],
                    'status' => 'VALID',
                    'deleted_flag' => 'N'
                ];
                $goodsAttr = $gaModel->field($field_attr)->where($condition_attr)->find();
                $goodsInfo['spec_attrs'] = $goodsAttr ? $goodsAttr['spec_attrs'] : '';
                //商品图片附件
                $gaModel = new GoodsAttachModel();
                $attachInfo = $gaModel->field('sku,attach_url,attach_name')->where(['sku' => $goodsInfo['sku'], 'deleted_flag' => 'N', 'status' => 'VALID'])->order('default_flag DESC')->select();
                if ($attachInfo) {
                    $goodsInfo['attach'] = $attachInfo;
                }
                if (empty($goodsInfo['attachAry'])) {    //当sku无附件图时，取spu图
                    $paModel = new ProductAttachModel();
                    $attachInfo = $paModel->field('spu,attach_url,attach_name')->where(['spu' => $goodsInfo['spu'], 'deleted_flag' => 'N', 'status' => 'VALID'])->order('default_flag DESC')->select();
                    if ($attachInfo) {
                        $goodsInfo['attach'] = $attachInfo;
                    }
                }
            }
            return $goodsInfo ? $goodsInfo : [];
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Goods】getInfoBySku:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 根据sku获取信息
     * @param $sku
     * @param $lang
     * @return array|bool|mixed
     */
    public function getInfoBySku($sku, $lang) {
        if (empty($sku) || empty($lang)) {
            return false;
        }
        try {

            if (is_array($sku)) {
                $condition = ["g.sku" => ['in', $sku]];
            } else {
                $condition = ["g.sku" => $sku];
            }
            $condition["g.lang"] = $lang;
            $condition["g.status"] = 'VALID';
            $condition["g.deleted_flag"] = 'N';

            $productModel = new ProductModel();
            $productTable = $productModel->getTableName();
            $gaModel = new GoodsAttrModel();
            $gaTable = $gaModel->getTableName();
            $result = $this->alias('g')->field("g.spu,g.sku,g.name,"
                                    . "g.show_name,g.show_name_loc,"
                                    . "g.model,g.lang,"
                                    . "g.min_pack_unit,g.min_pack_naked_qty,"
                                    . "g.nude_cargo_unit,p.brand,p.name as spu_name,"
                                    . "p.show_name as spu_show_name,ga.spec_attrs")
                            ->join($productTable . " p ON g.spu=p.spu AND g.lang=p.lang")
                            ->join($gaTable . " ga ON g.sku=ga.sku AND g.lang=ga.lang")
                            ->where($condition)->select();

            if ($result) {
                foreach ($result as $index => $item) {
                    $item['name'] = empty($item['show_name']) ? (empty($item['name']) ? (empty($item['spu_show_name']) ? $item['spu_name'] : $item['spu_show_name']) : $item['name']) : $item['show_name'];
                    $result[$item['sku']] = $item;
                    unset($result[$index]);
                }
            }
            return $result ? $result : [];
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Goods】getInfoBySku:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 多维数组排序
     * @param $data
     * @param $sort_order_field
     * @param int $sort_order
     * @param int $sort_type
     */
    private function my_array_multisort($data, $sort_order_field, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC) {
        if (is_array($data)) {
            foreach ($data as $val) {
                $key_arrays[] = $val[$sort_order_field];
            }
            array_multisort($key_arrays, $sort_order, $sort_type, $data);
            return $data;
        }
        return [];
    }

}
