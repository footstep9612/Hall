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
        parent::init();
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function ListAction() {
        $condition = $this->getPut();
        if (empty($condition['country_bn'])) {
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
        $stock_model = new StockModel();
        $list = $stock_model->getList($condition, $lang);
        if ($list) {
            $this->_setCountry($list);
            $this->_setConstPrice($list, $condition['country_bn']);
            $this->_setShowcats($list, $lang, $condition['country_bn']);
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
     * Description of 获取现货详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function InfoAction() {
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

        $sku = $this->getPut('sku');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择商品!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();

        $list = $stock_model->getInfo($country_bn, $lang, $sku);
        if ($list) {
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
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function CreateAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $skus = $this->getPut('skus');
        if (empty($skus)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();
        $flag = $stock_model->createData($country_bn, $skus, $lang);

        if ($flag) {
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function deletedAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $skus = $this->getPut('skus');
        if (empty($skus)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();
        $list = $stock_model->deleteData($country_bn, $skus, $lang);
        if ($list) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('删除成功!');
            $this->jsonReturn();
        } elseif ($list === FALSE) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('删除失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function setingPricesAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        $cost_prices = $this->getPut('cost_prices');
        if (empty($cost_prices)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请添加价格信息!');
            $this->jsonReturn();
        }
        $stock_cost_price_model = new StockCostPriceModel();


        $list = $stock_cost_price_model->updateDatas($country_bn, $lang, $sku, $cost_prices);
        if ($list) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } elseif ($list === FALSE) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getPricesAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }
        $stock_cost_price_model = new StockCostPriceModel();
        $list = $stock_cost_price_model->getList($country_bn, $sku);
        if ($list) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('获取成功!');
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
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
            $supplier_model = new SuppliersModel();
            $stockcostprices = $product_attach_model->getCostPriceBySkus($skus, $country_bn);

            $supplier_ids = [];
            foreach ($stockcostprices as $stockcostprice) {
                foreach ($stockcostprice as $costprice) {
                    $supplier_ids[] = $costprice['supplier_id'];
                }
            }
            $suppliers = $supplier_model->getSupplierNameByIds($supplier_ids);
            foreach ($arr as $key => $val) {

                if ($val['spu'] && isset($stockcostprices[$val['sku']])) {
                    if (isset($stockcostprices[$val['sku']])) {
                        $val['costprices'] = $stockcostprices[$val['sku']];
                        $val['supplier_names'] = $this->_getSuppliernames($stockcostprices[$val['sku']], $suppliers);
                    }
                } else {
                    $val['costprices'] = '';
                    $val['supplier_names'] = [];
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

    private function _getSuppliernames($stockcostprices, $suppliers) {
        foreach ($stockcostprices as $stockcostprice) {
            $supplier_id = $stockcostprice['supplier_id'];
            if (isset($suppliers[$supplier_id])) {
                $supplier_names[$supplier_id] = $suppliers[$supplier_id];
            }
        }
        rsort($supplier_names);
        unset($stockcostprices, $suppliers);
        return $supplier_names;
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setCountry(&$arr) {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, 'zh');
            foreach ($arr as $key => $val) {
                if (trim($val['country_bn']) && isset($countrynames[trim($val['country_bn'])])) {
                    $val['country_name'] = $countrynames[trim($val['country_bn'])];
                } else {
                    $val['country_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取展示分类
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setShowcats(&$arr, $lang, $country_bn) {
        if ($arr) {

            $show_cat_goods_model = new ShowCatGoodsModel();

            $skus = [];
            foreach ($arr as $key => $val) {
                $skus[] = trim($val['sku']);
            }

            $scats = $show_cat_goods_model->getshow_catsbyskus($skus, $lang, $country_bn);
            foreach ($arr as $key => $val) {
                if (trim($val['sku']) && isset($scats[trim($val['sku'])])) {
                    $val['show_cats'] = $scats[trim($val['sku'])];
                } else {
                    $val['show_cats'] = [];
                }
                rsort($val['show_cats']);
                $arr[$key] = $val;
            }
        }
    }

}