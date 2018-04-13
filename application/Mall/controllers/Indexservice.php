<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货
 * @author  zhongyg
 * @date    2017-12-6 9:07:59
 * @version V2.0
 * @desc
 */
class IndexserviceController extends PublicController {

    //put your code here
    public function init() {
        $this->token = false;
        parent::init();
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function IndexAction() {

        ini_set('memory_limit', '800M');
        set_time_limit(360);
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $data = [];
        $data['show_cat'] = $this->_getShowCat($country_bn, $lang);
        $data['recommendcats'] = $this->_getRecommendCats($country_bn, $lang);
        $data['hots'] = $this->_gethots($country_bn, $lang);
        $data['floors'] = $this->_getFloors($country_bn, $lang);
        if ($data) {
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    private function _getShowCat($country_bn, $lang) {
        $jsondata = ['lang' => $lang];
        $jsondata['level_no'] = 1;
        $jsondata['country_bn'] = $country_bn;
        $show_model = new ShowCatModel();
        $arr = $show_model->tree($jsondata);
        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            foreach ($arr as $key => $val) {
                $children_data = $jsondata;
                $children_data['level_no'] = 2;
                $children_data['parent_cat_no'] = $val['value'];
                $arr[$key]['children'] = $show_model->tree($children_data);
                if ($arr[$key]['children']) {
                    foreach ($arr[$key]['children'] as $k => $item) {
                        $children_data['level_no'] = 3;
                        $children_data['parent_cat_no'] = $item['value'];
                        $arr[$key]['children'][$k]['children'] = $show_model->tree($children_data);
                    }
                }
            }
        }
        return $arr;
    }

    private function _getRecommendCats($country_bn, $lang) {
        $jsondata = ['lang' => $lang];
        $jsondata['group'] = 'ICO';
        $jsondata['country_bn'] = $country_bn;
        $home_country_ads_model = new HomeCountryAdsModel();
        $list = $home_country_ads_model->getList($jsondata);
        return $list;
    }

    private function _gethots($country_bn, $lang) {
        $jsondata = ['lang' => $lang];
        $jsondata['group'] = 'HOT';
        $jsondata['country_bn'] = $country_bn;
        $home_country_ads_model = new HomeCountryAdsModel();
        $list = $home_country_ads_model->getList($jsondata);
        return $list;
    }

    private function _getFloors($country_bn, $lang) {
        $jsondata = ['lang' => $lang];
        $jsondata['country_bn'] = $country_bn;
        $home_floor_model = new HomeFloorModel();
        $floors = $home_floor_model->getList($jsondata);
        if ($floors) {
            $floor_ids = [];
            foreach ($floors as $key => $floor) {
                $floor_ids[] = $floor['id'];
            }

            $jsondata['floor_id'] = $floor_ids;
            $keywords = $this->_getFloorKeyword($jsondata);
            $ads = $this->_getFloorads($jsondata, 'BACKGROUP');
            $cats = $this->_getFloorcats($jsondata);
            $products = $this->_getProducts($jsondata);
            foreach ($floors as $key => $floor) {
                $floors[$key]['keywords'] = isset($keywords[$floor['id']]) ? $keywords[$floor['id']] : [];
                $floors[$key]['ads'] = isset($ads[$floor['id']][0]) ? $ads[$floor['id']][0] : [];
                $floors[$key]['show_cat'] = isset($cats[$floor['id']]) ? $cats[$floor['id']] : [];
                $floors[$key]['products'] = isset($products[$floor['id']]) ? $products[$floor['id']] : [];
            }

            unset($jsondata['floor_id'], $products, $cats, $ads, $keywords);
        }


        return $floors;
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    private function _getFloorKeyword($jsondata) {
        $home_floor_keyword_model = new HomeFloorKeywordModel();
        $list = $home_floor_keyword_model->getList($jsondata);
        $ret = [];
        foreach ($list as $val) {
            $ret[$val['floor_id']] [] = $val;
        }
        return $ret;
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    private function _getFloorcats($condition) {
        $home_floor_show_cat_model = new HomeFloorShowCatModel();

        $list = $home_floor_show_cat_model->getList($condition);
        $ret = [];
        foreach ($list as $val) {
            $ret[$val['floor_id']] [] = $val;
        }
        return $ret;
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    private function _getProducts($condition) {
        $home_floor_product_model = new HomeFloorProductModel();
        $list = $home_floor_product_model->getFloorProducts($condition);

        return $list;
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    private function _getFloorads($condition, $group = 'BACKGROUP') {
        $condition['group'] = $group;

        $home_floor_ads_model = new HomeFloorAdsModel();

        $list = $home_floor_ads_model->getList($condition);
        $ret = [];
        foreach ($list as $val) {
            $ret[$val['floor_id']] [] = $val;
        }
        return $ret;
    }

}
