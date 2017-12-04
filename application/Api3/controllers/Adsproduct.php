<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Adsproduct
 * @author  zhongyg
 * @date    2017-12-1 9:29:39
 * @version V2.0
 * @desc
 */
class AdsproductController extends PublicController {

    //put your code here
    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        $this->_model = new ShowCatModel();
    }

    /*
     * 获取广告位列表
     */

    public function listAction() {
        $lang = $this->getPut('lang', 'en');

        $country_bn = $this->getPut('country_bn', '');
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }

        $group_id = $this->getPut('group_id', '');
        if (empty($group_id)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('广告位ID不能为空');
            $this->jsonReturn();
        }
        $adsproduct_model = new AdsProductModel();
        $list = $adsproduct_model->getList($country_bn, $lang, $group_id);
        if ($list) {
            $this->jsonReturn($list);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('没有数据!');
            $this->jsonReturn();
        }
    }

}
