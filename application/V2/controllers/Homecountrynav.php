<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 首页显示国家
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class HomecountrynavController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 获取现货国家列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function ListAction() {

        $condition = $this->getPut();
        $home_country_nav_model = new HomeCountryNavModel();

        $list = $home_country_nav_model->getList($condition);

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
            $market_area_country_model = new MarketAreaCountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, 'zh');
            $market_areas = $market_area_country_model->getAreasBybns($country_bns, 'zh');
            foreach ($arr as $key => $val) {
                if (trim($val['country_bn']) && isset($countrynames[trim($val['country_bn'])])) {
                    $val['country_name'] = $countrynames[trim($val['country_bn'])];
                } else {
                    $val['country_name'] = '';
                }

                if (trim($val['country_bn']) && isset($market_areas[trim($val['country_bn'])])) {
                    $val['market_area_name'] = $market_areas[trim($val['country_bn'])]['market_area_name'];
                    $val['market_area_bn'] = $market_areas[trim($val['country_bn'])]['market_area_bn'];
                } else {
                    $val['country_name'] = '';
                    $val['market_area_bn'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /**
     * Description of 获取现货国家详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function InfoAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $home_country_nav_model = new HomeCountryNavModel();

        $list = $home_country_nav_model->getInfo($country_bn);
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
     * Description of 新加现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function CreateAction() {
        $condition = $this->getPut();
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }

        if (empty($condition['nav_name'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('导航名称不能为空!');
            $this->jsonReturn();
        }

        if (empty($condition['nav_url'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('导航名称链接不能为空!');
            $this->jsonReturn();
        }
        $home_country_nav_model = new HomeCountryNavModel();

        if ($home_country_nav_model->getExit($condition)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您新建的导航已经存在,请您重新输入!');
            $this->jsonReturn();
        }


        $list = $home_country_nav_model->createData($condition);
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
        $condition = $this->getPut();
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }

        if (empty($condition['nav_name'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('导航名称不能为空!');
            $this->jsonReturn();
        }

        if (empty($condition['nav_url'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('导航名称链接不能为空!');
            $this->jsonReturn();
        }

        $home_country_nav_model = new HomeCountryNavModel();

        if ($home_country_nav_model->getExit($condition, $id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择国家已经存在,请您重新选择!');
            $this->jsonReturn();
        }



        $list = $home_country_nav_model->updateData($condition, $id);
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
