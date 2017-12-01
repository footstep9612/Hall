<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Adsgroup
 * @author  zhongyg
 * @date    2017-12-1 9:30:05
 * @version V2.0
 * @desc
 */
class AdvsgroupController extends PublicController {

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

        $channel_id = $this->getPut('channel_id', '');
        if (empty($channel_id)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('频道ID不能为空');
            $this->jsonReturn();
        }
        $parent_id = $this->getPut('parent_id', '0');
        $adsgroup_model = new AdsGroupModel();
        $list = $adsgroup_model->getList($country_bn, $lang, $channel_id, $parent_id);



        if ($list) {
            $this->jsonReturn($list);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('没有数据!');
            $this->jsonReturn();
        }
    }

}
