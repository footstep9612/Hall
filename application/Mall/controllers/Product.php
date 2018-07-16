<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 10:35
 */
class ProductController extends PublicController {

    public function init() {
        $this->token = false;
       // parent::init();
        $this->input = $this->getPut();
    }

    /**
     * 询单商品详情
     */
    public function infoAction() {
        $input = $this->getPut();
        if (!isset($input['spu']) || empty($input['spu'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $stock = (isset($input['type']) && !empty($input['type'])) ? true : false;
        if ($stock && (!isset($input['country_bn']) || empty($input['country_bn']))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, '现货国家不能为开');
        }
        $country_bn = $input['country_bn'] ? $input['country_bn'] : '';
        $productModel = new ProductModel();
        $result = $productModel->getInfoBySpu($input['spu'], $input['lang'], $stock, $country_bn,isset($input['sku']) ? $input['sku'] : '');
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 关联商品
     */
    public function relationAction() {
        $input = $this->getPut();

        $productModel = new ProductModel();
        $result = $productModel->getRelationSpu($input);
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 购物车提交
     */
    public function getSkusAction(){
        $input = $this->getPut();
        if (!isset($input['skus']) || empty($input['skus']) || !is_array($input['skus'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        $productModel = new ProductModel();
        $result = $productModel->getSkusList($input);
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * sku列表
     */
    public function skusAction() {
        $input = $this->getPut();

        $productModel = new ProductModel();
        $result = $productModel->getSkuList($input);
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 价格库存
     */
    public function priceStockAction() {
        $input = $this->getPut();
        if (!isset($input['sku']) || empty($input['sku'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if (!isset($input['country_bn']) || empty($input['country_bn'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'Country_bn 不能为空');
        }

        if (!isset($input['special_id']) || empty($input['special_id'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'special_id 不能为空');
        }

        if (!isset($input['count']) || empty($input['count'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'Count 不能为空');
        }
        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $productModel = new ProductModel();
        $stockInfo = $productModel->getSkuStockBySku($input['sku'], $input['country_bn'], $input['lang']);

        $promotion_price = '';
        if($stockInfo && isset($stockInfo[$input['sku']]['price_strategy_type']) && $stockInfo[$input['sku']]['price_strategy_type']!='' && (($stockInfo[$input['sku']]['strategy_validity_start']<= date('Y-m-d H:i:s',time()) || $stockInfo[$input['sku']]['strategy_validity_start']==null) && ($stockInfo[$input['sku']]['strategy_validity_end']> date('Y-m-d H:i:s',time()) || $stockInfo[$input['sku']]['strategy_validity_end']==null) )){
            $psdM = new PriceStrategyDiscountModel();
            $promotion_price = $psdM->getSkuPriceByCount($input['sku'],'STOCK',$input['special_id'],$input['count']);
        }

        $data = [
            'price' => $promotion_price ? $promotion_price : ($stockInfo[$input['sku']]['price'] ? $stockInfo[$input['sku']]['price'] : ''),
            'price_cur_bn' => ($stockInfo && isset($stockInfo[$input['sku']]) && !empty($stockInfo[$input['sku']]['price_cur_bn'])) ? $stockInfo[$input['sku']]['price_cur_bn'] : (isset($priceInfo['price_cur_bn']) ? $priceInfo['price_cur_bn'] : ''),
            'price_symbol' => ($stockInfo && isset($stockInfo[$input['sku']]) && !empty($stockInfo[$input['sku']]['price_symbol'])) ? $stockInfo[$input['sku']]['price_symbol'] : (isset($priceInfo['price_symbol']) ? $priceInfo['price_symbol'] : ''),
            'stock' => ($stockInfo && isset($stockInfo[$input['sku']])) ? $stockInfo[$input['sku']]['stock'] : 0
        ];
        jsonReturn($data);
    }

    /**
     * 根据数量获取sku价格
     */
    public function priceAction() {
        $input = $this->getPut();
        if (!isset($input['sku']) || empty($input['sku'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if (!isset($input['country_bn']) || empty($input['country_bn'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'Country_bn 不能为空');
        }

        if (!isset($input['count']) || empty($input['count'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'Count 不能为空');
        }

        $productModel = new ProductModel();
        $result = $productModel->getSkuPriceByCount($input['sku'], $input['country_bn'], $input['count']);
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * sku库存
     */
    public function stockAction() {
        $input = $this->getPut();
        if (!isset($input['sku']) || empty($input['sku'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }
        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }
        if (!isset($input['country_bn']) || empty($input['country_bn'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'Country_bn 不能为空');
        }

        $productModel = new ProductModel();
        $result = $productModel->getSkuStockBySku($input['sku'], $input['country_bn'], $input['lang']);
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 商品购物车结构信息
     */
    public function shoppingcarAction(){
        $input = $this->getPut();
        if(!isset($input['skus']) || empty($input['skus'])){
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }
        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $productModel = new ProductModel();
        $result = $productModel->myShoppingCar($input);
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }



    }

}
