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
     * 商品（sku）基本信息  --- 公共接口
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
