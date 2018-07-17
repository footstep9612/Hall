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

    /*
     * Description of 价格策略
     * @param array $arr
     * @author  link
     * @date    2018-07-09
     * @desc
     */
    private function _setDisCount(&$arr, $special_id) {
        if ($arr) {
            $skus = [];
            foreach ($arr as $key => $val) {
                $skus[] = $val['sku'];
            }
            $price_strategy_discount_model = new PriceStrategyDiscountModel();
            $disCounts = $price_strategy_discount_model->getDisCountBySkus($skus,'STOCK', $special_id);
            foreach ($arr as $key => $val) {
                if(isset($disCounts[$val['sku']]) && $val['price_strategy_type'] !='' && (empty($val['strategy_validity_start']) || $val['strategy_validity_start']<=date('Y-m-d H:i:s',time())) && (empty($val['strategy_validity_end']) || $val['strategy_validity_end']>date('Y-m-d H:i:s',time()))){
                    $val['price_range'] = $disCounts[$val['sku']];
                    if(!empty($val['strategy_validity_end'])){
                        $days = (strtotime($val['strategy_validity_end'])-time())/86400;
                        $val['validity_days'] = $days > 1 ? ceil($days) : substr(sprintf( "%.2f ",$days),0,-2);
                        $val['validity_hours'] = floor((strtotime($val['strategy_validity_end'])-time())%86400/3600);
                        $val['validity_minutes'] = floor((strtotime($val['strategy_validity_end'])-time())%3600/60);
                        $val['validity_seconds'] = floor((strtotime($val['strategy_validity_end'])-time())%86400%60);
                    }
                }else{
                    $val['price_range'] = [];
                }
                $arr[$key] = $val;
            }
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
        $condition = $this->getPut();
        if(!isset($condition['special_id'])){
            jsonReturn('',MSG::ERROR_PARAM, '请选择现货');
        }
        if(!isset($condition['floor_id'])){
            jsonReturn('',MSG::ERROR_PARAM, '请选择楼层');
        }

        $stock_model = new StockModel();
        $list = $stock_model->getList($condition);
        if ($list) {
            $this->_setImage($list);
            //$this->_setConstPrice($list, $country_bn);
            $this->_setDisCount($list,isset($condition['special_id']) ? $condition['special_id'] : '');
            jsonReturn($list);
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
    public function ListByKeywordAction() {
        $condition = $this->getPut();
        if (empty($condition['special_id'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货!');
            $this->jsonReturn();
        }

        $stock_model = new StockModel();
        $list = $stock_model->getListByKeyword($condition);
        if ($list) {
            $this->_setImage($list);
            $count = $stock_model->getCountByKeyword($condition);
            $this->setvalue('count', $count);
            //$this->_setConstPrice($list, $condition['country_bn']);
            $this->_setDisCount($list, $condition['special_id']);
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
