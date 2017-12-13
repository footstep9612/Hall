<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 10:35
 */
class ProductController extends PublicController{
    public function init() {
        //parent::init();
    }

    /**
     * 询单商品详情
     */
    public function infoAction(){
        $input = $this->getPut();
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $stock = (isset($input['stock']) && !empty($input['stock'])) ? true : false;
        if($stock && (!isset($input['country_bn']) || empty($input['country_bn']))){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '现货国家不能为开');
        }
        $country_bn = $input['country_bn'] ? $input['country_bn'] : '';
        $productModel = new ProductModel();
        $result = $productModel->getInfoBySpu($input['spu'], $input['lang'], $stock,$country_bn);
        if ($result !== false) {
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 关联商品
     */
    public function relationAction(){
        $input = $this->getPut();

        $productModel = new ProductModel();
        $result = $productModel->getRelationSpu($input);
        if ($result !== false) {
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * sku列表
     */
    public function skusAction(){
        $input = $this->getPut();

        $productModel = new ProductModel();
        $result = $productModel->getSkuList($input);
        if ($result !== false) {
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 根据数量获取sku价格
     */
    public function priceAction(){
        $input = $this->getPut();
        if(!isset($input['sku']) || empty($input['sku'])){
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if(!isset($input['country_bn']) || empty($input['country_bn'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'Country_bn 不能为空');
        }

        if(!isset($input['count']) || empty($input['count'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'Count 不能为空');
        }

        $productModel = new ProductModel();
        $result = $productModel->getSkuPriceByCount($input['sku'],$input['country_bn'],$input['count']);
        if ($result !== false) {
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }
}