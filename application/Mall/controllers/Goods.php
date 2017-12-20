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
        $input = $this->getPut();
        if(!isset($input['sku']) || empty($input['sku'])){
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $goodsModel = new GoodsModel();
        $result = $goodsModel->getInfoBySku($input['sku'], $input['lang']);
        if ($result !== false) {
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}