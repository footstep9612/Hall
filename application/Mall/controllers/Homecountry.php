<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货国家
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class HomecountryController extends PublicController {

    //put your code here
    public function init() {
        //  parent::init();
        $this->token = false;
        parent::init();
    }

    /**
     * Description of 判断当前国家是否存在现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExitAction() {

        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('请选择国家!');
            $this->jsonReturn(null);
        }
        $lang = $this->getPut('lang', 'en');
        if (empty($lang)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('请选择语言!');
            $this->jsonReturn(null);
        }
        $home_country_model = new ShowCatModel();

        $list = $home_country_model->getExit($country_bn, $lang);

        if ($list) {
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

    /**
     * Description of 获取广告列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  广告
     */
    public function getAdsAction() {

        $condition = $this->getPut();
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $home_country_ads_model = new HomeCountryAdsModel();
        $list = $home_country_ads_model->getList($condition);
        if ($list) {
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

    /**
     * Description of 获取导航列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  广告
     */
    public function getNavAction() {
        $condition = $this->getPut();
        if (empty($condition['group'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择应用!');
            $this->jsonReturn();
        }

        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        $home_country_nav_model = new HomeCountryNavModel();
        $list = $home_country_nav_model->getList($condition);
        if ($list) {
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

}
