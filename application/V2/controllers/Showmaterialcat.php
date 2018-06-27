<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Showmaterialcat
 *
 * @author zhongyg
 * @date 2017-07-26 10:16
 */
class ShowmaterialcatController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    public function listAction() {
        $model = new ShowMaterialCatModel();
        $material_cat_no = $this->getPut('material_cat_no');
        $lang = $this->getPut('lang', 'en');
        if (!$material_cat_no) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $show_cat_nos = $model->getshowcatnosBymatcatno($material_cat_no, $lang);

        $show_cat_nos_arr = [];

        if ($show_cat_nos) {
            foreach ($show_cat_nos as $show_cat_no) {
                $show_cat_nos_arr[] = $show_cat_no['cat_no'];
            }
        }
        if ($show_cat_nos_arr) {
            $show_cat_model = new ShowCatModel();
            $data = $show_cat_model->getshow_cats($show_cat_nos_arr, $lang);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn();
        }

        rsort($data);
        $this->_setMarketAreaName($data);
        $this->_setCountryName($data);
        if ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn();
        } elseif ($data === false) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        }
    }

    private function _setCountryName(&$arr) {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = $val['country_bn'];
            }
            $country_bns = $country_model->getNamesBybns($country_bns);
            foreach ($arr as $key => $val) {
                if ($val['country_bn'] && isset($country_bns[$val['country_bn']])) {
                    $val['country_name'] = $country_bns[$val['country_bn']];
                } else {
                    $val['country_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    private function _setMarketAreaName(&$arr) {
        if ($arr) {
            $market_area_model = new MarketAreaModel();
            $market_area_bns = [];
            foreach ($arr as $key => $val) {
                $market_area_bns[] = $val['market_area_bn'];
            }
            $areas = $market_area_model->getNamesBybns($market_area_bns);
            foreach ($arr as $key => $val) {
                if ($val['market_area_bn'] && isset($areas[$val['market_area_bn']])) {
                    $val['market_area_name'] = $areas[$val['market_area_bn']];
                } else {
                    $val['market_area_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
