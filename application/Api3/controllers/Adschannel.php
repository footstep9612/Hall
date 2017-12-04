<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Adschannel
 * @author  zhongyg
 * @date    2017-12-1 9:29:23
 * @version V2.0
 * @desc
 */
class AdschannelController extends PublicController {

    //put your code here
    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
    }

    /*
     * 获取广告频道列表
     */

    public function listAction() {
        $lang = $this->getPut('lang', 'en');

        $country_bn = $this->getPut('country_bn', '');
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }

        $adschannel_model = new AdsChannelModel();
        $ret = [];
        $ret['HOME'] = $adschannel_model->getInfo($country_bn, 'HOME', $lang);

        $ret['STOCK'] = $adschannel_model->getInfo($country_bn, 'STOCK', $lang);
        if ($ret) {
            $this->jsonReturn($ret);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('没有数据!');
            $this->jsonReturn();
        }
    }

    /*
     * 获取广告频道列表
     */

    public function getInfoAction() {
        $lang = $this->getPut('lang', 'en');

        $country_bn = $this->getPut('country_bn', '');
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }
        $type = $this->getPut('type', 'HOME'); //HOME 首页 STOCK 现货
        $adschannel_model = new AdsChannelModel();
        $info = $adschannel_model->getInfo($country_bn, $type, $lang);
        if ($info) {
            $this->jsonReturn($info);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('没有数据!');
            $this->jsonReturn();
        }
    }

}