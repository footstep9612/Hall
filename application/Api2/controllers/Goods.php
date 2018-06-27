<?php

//class GoodsController extends PublicController
class GoodsController extends PublicController {

    private $input;

    public function init() {

        $this->token = false;
        parent::init();
        $this->input = $this->getPut();
        $this->lang = isset($this->input['lang']) ? strtolower($this->input['lang']) : (browser_lang() ? browser_lang() : 'en');
        if (!in_array($this->lang, array('en', 'ru', 'es', 'zh'))) {
            $this->lang = 'en';
        }
    }

    /**
     * sku基本详情 --- 门户2.0公共接口
     * @pararm  sku编码 lang status
     * @return array
     * @author klp
     */
    public function skuInfoAction() {
//        $this->put_data = [
//
//            'sku'=> '3303060000010001',
//            'lang'=> 'en',
//
//        ];
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getSkuInfo($this->put_data);
        if ($result && !isset($result['code'])) {
            jsonReturn($result);
        } else {
            jsonReturn('', MSG::MSG_FAILED, '失败!');
        }
    }

    /**
     * 商品（sku）基本信息  --- 公共接口1.0==暂废弃
     * @author link 2017-06-26
     */
    public function infoBaseAction() {
        if (empty($this->input['sku'])) {
            jsonReturn('', '1000');
        }

        $goods = new GoodsModel();
        $result = $goods->getInfoBase($this->input);
        if ($result) {
            jsonReturn(array('data' => $result));
        } else {
            jsonReturn('', 400, '');
        }
    }

}
