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
class StockcountryadsController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 获取现货国家广告列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function ListAction() {

        $condition = $this->getPut();
        $stock_country_ads_model = new StockCountryAdsModel();

        $list = $stock_country_ads_model->getList($condition);

        if ($list) {
            $this->_setCountry($list);
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

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setCountry(&$arr) {
        if ($arr) {
            $country_model = new CountryModel();

            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, 'zh');

            foreach ($arr as $key => $val) {
                if (trim($val['country_bn']) && isset($countrynames[trim($val['country_bn'])])) {
                    $val['country_name'] = $countrynames[trim($val['country_bn'])];
                } else {
                    $val['country_name'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /**
     * Description of 获取现货国家广告详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function InfoAction() {
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择广告!');
            $this->jsonReturn();
        }
        $stock_country_ads_model = new StockCountryAdsModel();

        $list = $stock_country_ads_model->getInfo($id);
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
     * Description of 新加现货国家广告
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function CreateAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $img_name = $this->getPut('img_name');
        if (empty($img_name)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择输入广告名称!');
            $this->jsonReturn();
        }

        $img_url = $this->getPut('img_url');
        if (empty($img_url)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择上传广告图片!');
            $this->jsonReturn();
        }
        $group = $this->getPut('group');
        if (empty($img_url)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择广告分组!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择广告语言!');
            $this->jsonReturn();
        }

        $stock_country_ads_model = new StockCountryAdsModel();

        if ($stock_country_ads_model->getExit($country_bn, $img_name, $img_url, $group, $lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择的国家广告名称已经存在,请您重新输入!');
            $this->jsonReturn();
        }
        $list = $stock_country_ads_model->createData($country_bn, $img_name, $img_url, $group, $lang);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function UpdateAction() {
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('ID不能为空!');
            $this->jsonReturn();
        }
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $img_name = $this->getPut('img_name');
        if (empty($img_name)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择输入广告名称!');
            $this->jsonReturn();
        }

        $img_url = $this->getPut('img_url');
        if (empty($img_url)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择上传广告图片!');
            $this->jsonReturn();
        }
        $group = $this->getPut('group');
        if (empty($img_url)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择广告分组!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择广告语言!');
            $this->jsonReturn();
        }
        $stock_country_model = new StockCountryModel();

        if ($stock_country_model->getExit($country_bn, $img_name, $img_url, $group, $lang, $id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择的国家广告名称已经存在,请您重新输入!');
            $this->jsonReturn();
        }
        $list = $stock_country_model->updateData($id, $country_bn, $img_name, $group, $lang);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    public function DeletedAction() {
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('ID不能为空!');
            $this->jsonReturn();
        }
        $stock_country_ads_model = new StockCountryAdsModel();
        $list = $stock_country_ads_model->DeletedData($id);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
