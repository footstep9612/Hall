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
class StockController extends PublicController {

    //put your code here
    public function init() {
        $this->token = false;
        parent::init();
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function ListByKeywordAction() {
        $condition = $this->getPut();
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }

        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }


        $stock_model = new StockModel();
        $list = $stock_model->getListByKeyword($condition);
        if ($list) {
            $this->_setImage($list);
            $count = $stock_model->getCountByKeyword($condition);
            $this->setvalue('count', $count);
            $this->_setConstPrice($list, $condition['country_bn']);
            $this->_setDisCount($list, $condition['country_bn']);
            $this->_SetProductInfo($list, $condition['lang']);
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function ListAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $floor_id = $this->getPut('floor_id');
        if (empty($floor_id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('楼层ID不能为空!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();
        $list = $stock_model->getList($country_bn, $lang, $floor_id);
        if ($list) {
            $this->_setImage($list);
            $this->_setConstPrice($list, $country_bn);
            $this->_setDisCount($list, $country_bn);
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /*
     * Description of 获取图片
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setImage(&$arr) {
        if ($arr) {

            $spus = [];
            foreach ($arr as $key => $val) {
                $spus[] = $val['spu'];
            }

            $product_attach_model = new ProductAttachModel();
            $images = $product_attach_model->getImgBySpus($spus);

            foreach ($arr as $key => $val) {

                if ($val['spu'] && isset($images[$val['spu']])) {
                    if (isset($images[$val['spu']])) {
                        $val['image_url'] = $images[$val['spu']][0]['attach_url'];
                        $val['image_name'] = $images[$val['spu']][0]['attach_name'];
                    }
                } else {
                    $val['image_url'] = '';
                    $val['image_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取图片
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setDisCount(&$arr, $country_bn) {
        if ($arr) {

            $skus = [];
            foreach ($arr as $key => $val) {
                $skus[] = $val['sku'];
            }

            $price_strategy_discount_model = new PriceStrategyDiscountModel();
            $disCounts = $price_strategy_discount_model->getDisCountBySkus($skus, $country_bn);

            foreach ($arr as $key => $val) {

                if ($val['sku'] && isset($disCounts[$val['sku']])) {
                    if (isset($disCounts[$val['sku']])) {
                        $val['discount'] = $disCounts[$val['sku']]['discount'];
                        $val['min_purchase_qty'] = $disCounts[$val['sku']]['min_purchase_qty'];
                        $val['max_purchase_qty'] = $disCounts[$val['sku']]['max_purchase_qty'];
                    }
                } else {
                    $val['discount'] = '';
                    $val['min_purchase_qty'] = '';
                    $val['max_purchase_qty'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取价格属性
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setConstPrice(&$arr, $country_bn) {
        if ($arr) {

            $skus = [];
            foreach ($arr as $key => $val) {
                $skus[] = $val['sku'];
            }

            $product_attach_model = new StockCostPriceModel();
            $stockcostprices = $product_attach_model->getCostPriceBySkus($skus, $country_bn);

            foreach ($arr as $key => $val) {

                if ($val['spu'] && isset($stockcostprices[$val['sku']])) {
                    if (isset($stockcostprices[$val['sku']])) {
                        $val['costprices'] = $stockcostprices[$val['sku']];
                    }
                } else {
                    $val['costprices'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取价格属性
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _SetProductInfo(&$arr, $lang = 'en') {
        if ($arr) {

            $spus = [];
            foreach ($arr as $key => $val) {
                $spus[] = $val['spu'];
            }

            $product_model = new ProductModel();
            $products = $product_model->GetProductBySpus($spus, $lang);

            foreach ($arr as $key => $val) {

                if ($val['spu'] && isset($products[$val['spu']])) {
                    $val['tech_paras'] = $products[$val['spu']]['tech_paras'];
                    $val['exe_standard'] = $products[$val['spu']]['exe_standard'];
                    //  $val['customization_flag'] = $products[$val['spu']]['customization_flag'];
                    //  $val['warranty'] = $products[$val['spu']]['warranty'];
                    $brand = json_decode($products[$val['spu']]['brand'], true);
                    if ($brand && isset($brand['name'])) {
                        $val['brand'] = $brand['name'];
                    } else {
                        $val['brand'] = $products[$val['spu']]['brand'];
                    }
                } else {
                    $val['tech_paras'] = '';
                    $val['exe_standard'] = '';
                    // $val['customization_flag'] = 'N';
                    $val['brand'] = '';
                    //  $val['warranty'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
