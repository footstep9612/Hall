<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 10:35
 */
class GoodsController extends PublicController{
    public function init() {
        //parent::init();
    }

    /**
     * 询单商品详情
     */
    public function infoAction(){
        jsonReturn('11');
        $input = $this->getPut();
        if(!isset($input['sku']) || empty($input['sku'])){
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $stock = (isset($input['type']) && !empty($input['type'])) ? true : false;
        if ($stock && (!isset($input['country_bn']) || empty($input['country_bn']))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, '现货国家不能为空');
        }
        $country_bn = $input['country_bn'] ? $input['country_bn'] : '';

        $goodsModel = new GoodsModel();
        $result = $goodsModel->getInfo($input['sku'], $input['lang'],$stock,$country_bn);
        if ($result !== false) {
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}