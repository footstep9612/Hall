<?php

/**
 * 购物车
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/9
 * Time: 21:16
 */
class ShoppingCarModel extends PublicModel {

    protected $tableName = 'shopping_car';
    protected $dbName = 'erui_mall';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 我的购物车
     */
    public function myShoppingCar($condition, $country_bn = '') {
        if (empty($condition) || !isset($condition['lang'])) {
            return false;
        }
        $condition['type'] = $condition['type'] ? $condition['type'] : 0;
        $condition['deleted_flag'] = 'N';
        try {
            $goodsModel = new GoodsModel();
            $goodsTable = $goodsModel->getTableName();
            $result = $this->field('id,lang,sku,spu,buy_number')->where($condition)->select();
            if ($result) {
                $skus = [];
                $spus = [];
                foreach ($result as $index => $item) {
                    $skus[] = $item['sku'];
                    $spus[] = $item['spu'];
                    $result[$item['sku']] = $item;
                    unset($result[$index]);
                }

                $goodsModel = new GoodsModel();

                $productModel = new ProductModel();
                $goods = $goodsModel->field('spu,sku,name,show_name,min_order_qty,min_pack_naked_qty,nude_cargo_unit,'
                                        . 'min_pack_unit,lang,model,status,deleted_flag')
                                ->where(['sku' => ['in', $skus],
                                    'lang' => $condition['lang'],
                                    'deleted_flag' => 'N'])->select();

                $this->_getSpuInfoByGoods($goods, $condition['lang']);
                $goodsAry = [];
                foreach ($goods as $r) {
                    $r['name'] = empty($r['show_name']) ? (empty($r['name']) ? (empty($r['spu_show_name']) ? $r['spu_name'] : $r['spu_show_name']) : $r['name']) : $r['show_name'];
                    if ($condition['type']) {
                        $r['priceAry'] = $productModel->getSkuPriceByCount($r['sku'], $country_bn, $result[$r['sku']]['buy_number']);
                        $r['priceList'] = $productModel->getSkuPriceBySku($r['sku'], $country_bn);
                    }
                    $brand_ary = json_decode($r['brand'], true);
                    $r['brand'] = $brand_ary['name'];
                    $goodsAry[$r['sku']] = $r;
                }

                //库存
                $stockAry = [];
                if ($condition['type']) {
                    $stockAry = $productModel->getSkuStockBySku($skus, $country_bn, $condition['lang']);
                }

                //扩展属性
                $gattrModel = new GoodsAttrModel();
                $condition_attr = ['sku' => ['in', $skus], 'lang' => $condition['lang'], 'deleted_flag' => 'N'];
                $attrs = $gattrModel->field('sku,spec_attrs')->where($condition_attr)->select();
                $attrAry = [];
                foreach ($attrs as $attr) {
                    $attrAry[$attr['sku']] = json_decode($attr['spec_attrs'], true);
                }

                //图
                $attachModel = new ProductAttachModel();
                $attachs = $attachModel->getAttachBySpu($spus);
                $dataAttach = [''];
                foreach ($attachs as $r) {
                    if (isset($dataAttach[$r['spu']])) {
                        $r['default_flag'] == 'Y' ? $dataAttach[$r['spu']] = $r['attach_url'] : '';
                        continue;
                    }
                    $dataAttach[$r['spu']] = $r['attach_url'];
                }
            }
            return $result ? ['skuAry' => $result, 'infoAry' => $goodsAry, 'thumbs' => $dataAttach, 'attrAry' => $attrAry, 'stockAry' => $stockAry] : [];
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】 myShoppingCar:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 我的购物车
     */
    private function _getSpuInfoByGoods(&$goods, $lang = 'en') {

        try {
            $spus = [];
            foreach ($goods as $key => $goodsinfo) {
                $goods[$key]['spu_name'] = '';
                $goods[$key]['spu_show_name'] = '';
                $spus[] = $goodsinfo['spu'];
            }
            $where = ['deleted_flag' => 'N'];
            $where['lang'] = $lang;
            if ($spus) {
                $where['spu'] = ['in', $spus];
            } else {

                return [];
            }
            $productModel = new ProductModel();
            $data = $productModel->field('name ,show_name,spu')->where($where)->select();
            $ret = [];
            if ($data) {
                foreach ($data as $item) {
                    $ret[$item['spu']] = $item;
                }
            }

            foreach ($goods as $key => $goodsinfo) {
                if (isset($ret[$goodsinfo['spu']])) {
                    $goods[$key]['spu_name'] = $ret[$goodsinfo['spu']]['name'];
                    $goods[$key]['spu_show_name'] = $ret[$goodsinfo['spu']]['show_name'];
                }
            }
            return $ret;
        } catch (Exception $ex) {
            Log::write(__FILE__ . PHP_EOL . __CLASS__ . PHP_EOL . __LINE__, Log::ERR);
            Log::write($ex->getMessage(), Log::ERR);

            return [];
        }
    }

    /**
     * 添加/编辑车
     * @param $input
     * @param type 0 询单车  1购物车
     */
    public function edit($input, $userInfo) {
        if (!isset($input['spu']) || empty($input['spu'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if (!isset($input['skus']) || empty($input['skus']) || !is_array($input['skus'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        try {
            $this->startTrans();
            foreach ($input['skus'] as $sku => $count) {
                $data = [
                    'lang' => $input['lang'],
                    'buyer_id' => isset($input['buyer_id']) ? $input['buyer_id'] : $userInfo['buyer_id'],
                    'spu' => trim($input['spu']),
                    'sku' => trim($sku),
                    'buy_number' => trim($count),
                    'type' => $input['type'] ? $input['type'] : 0,
                    'deleted_flag' => 'N'
                ];

                $condition = [
                    'spu' => trim($input['spu']),
                    'sku' => trim($sku),
                    'lang' => $input['lang'],
                    'buyer_id' => isset($input['buyer_id']) ? $input['buyer_id'] : $userInfo['buyer_id']
                ];
                $result = $this->field('id,buy_number')->where($condition)->find();
                if ($result) {
                    $data['buy_number'] = $data['buy_number'] + $result['buy_number'];
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $result = $this->where(['id' => $result['id']])->save($data);
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $result = $this->add($this->create($data));
                }
                if (!$result) {
                    $this->rollback();
                    return false;
                }
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】edit:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 删除
     */
    public function del($input) {
        if (!isset($input['idAry']) || empty($input['idAry'])) {
            jsonReturn('', '请选择要删除的ID');
        }
        if (!isset($input['type'])) {
            jsonReturn('', 'type不能为空');
        }

        $userInfo = getLoinInfo();
        $condition['type'] = $input['type'] ? 1 : 0;
        if (is_array($input['idAry'])) {
            $condition['id'] = ['in', $input['idAry']];
        } else {
            $condition['id'] = $input['idAry'];
        }
        try {
            $data = [
                'deleted_flag' => 'Y',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $result = $this->where($condition)->save($data);
            return $result ? $result : false;
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】del:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 清用户车
     */
    public function clear($sku = '', $buyer_id = '', $type = '') {
        if (empty($sku) || empty($buyer_id)) {
            return false;
        }
        if (is_array($sku)) {
            $condition['sku'] = ['in', $sku];
        } else {
            $condition['sku'] = $sku;
        }
        $condition['type'] = $type;
        $condition['buyer_id'] = $buyer_id;
        try {
            $data = [
                'deleted_flag' => 'Y',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $result = $this->where($condition)->save($data);
            return $result ? $result : false;
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】clear:' . $e, Log::ERR);
            return false;
        }
    }

}
