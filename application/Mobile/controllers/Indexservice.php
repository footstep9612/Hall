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

        $data['banners'] = $this->_getAds($country_bn, $lang, 'BANNER');
        $data['floors'] = $this->_getFloors($country_bn, $lang);
        $data['popularity_recommendation'] = $this->_getPopularity_recommendation([], $lang, $country_bn, 4);
        //  $data['solution'] = $this->_getAds($country_bn, $lang, 'SOLUTION', 1); // $this->_getSolutions($lang);
        //$data['products'] = $this->_getProducts($country_bn, $lang);

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

    private function _getAds($country_bn, $lang, $group = 'BANNER', $pagesize = null) {
        $jsondata = ['lang' => $lang];
        $jsondata['group'] = $group;
        $jsondata['show_type'] = 'M';
        $jsondata['country_bn'] = $country_bn;
        $home_country_ads_model = new HomeCountryAdsModel();
        $list = $home_country_ads_model->getList($jsondata, $pagesize);

        return $list;
    }

    private function _getFloors($country_bn, $lang) {
        $jsondata = ['lang' => $lang];
        $jsondata['country_bn'] = $country_bn;

        $show_cat_model = new ShowCatModel();
        $jsondata['level_no'] = 1;
        $floors = $show_cat_model->getlist($jsondata, $lang);
        if ($floors) {

            $es_product_model = new EsProductModel();
            foreach ($floors as $key => $floor) {

                $jsondata['cat_no1'] = $floor['cat_no'];
                $jsondata['page_size'] = 4;
                $list = $es_product_model->getNewProducts($jsondata, $lang, $country_bn);
                $floors[$key]['products'] = $this->_getdata($list, $lang);
            }
        }
//        $jsondata['show_type'] = 'M';
//        $home_floor_model = new HomeFloorModel();
//        $floors = $home_floor_model->getList($jsondata);
//        if ($floors) {
//            $floor_ids = [];
//            foreach ($floors as $key => $floor) {
//                $floor_ids[] = $floor['id'];
//            }
//            $jsondata['show_type'] = 'M';
//            $jsondata['floor_ids'] = $floor_ids;
//            $products = $this->_getFloorProducts($jsondata);
//            foreach ($floors as $key => $floor) {
//                $floors[$key]['products'] = isset($products[$floor['id']]) ? $products[$floor['id']] : [];
//            }
//
//            unset($jsondata['floor_id'], $products);
//        }


        return $floors;
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    private function _getFloorProducts($condition) {
        $home_floor_product_model = new HomeFloorProductModel();
        $list = $home_floor_product_model->getFloorProducts($condition);
        return $list;
    }

    /**
     * Description of 获取人气推荐产品
     * @author  zhongyg
     * @date    2018-05-09 16:50:09
     * @version V2.0
     * @desc  M站首页
     */
    private function _getPopularity_recommendation($condition, $lang = 'en', $country_bn = null, $page_size = 4) {
        $condition['lang'] = $lang;
        $condition['recommend_flag'] = 'Y';
        $condition['page_size'] = $page_size;
        $es_product_model = new EsProductModel();


        $list = $es_product_model->getNewProducts($condition, $lang, $country_bn);

        $ret = $this->_getdata($list, $lang);
        return $ret;
    }

    /**
     * Description of 获取人气推荐产品
     * @author  zhongyg
     * @date    2018-05-09 16:50:09
     * @version V2.0
     * @desc  M站首页
     */
    private function _getSolutions($lang = 'en') {


        $jsondata = ['lang' => $lang];
        $solution_cat_model = new SolutionCatModel();

        $cats = $solution_cat_model->getList($jsondata);
        $cat_ids = [];
        foreach ($cats as $cat) {
            $cat_ids[] = $cat['catid'];
            if ($cat['arrchildid']) {
                $arrchildids = explode(',', $cat['arrchildid']);
                $cat_ids = array_merge($cat_ids, $arrchildids);
            }
        }
        $jsondata['catids'] = $cat_ids;
        $jsondata['pagesize'] = 1;
        $solution_model = new SolutionModel();
        $data = $solution_model->getList($jsondata);

        return $data;
    }

    /**
     * Description of 获取人气推荐产品
     * @author  zhongyg
     * @date    2018-05-09 16:50:09
     * @version V2.0
     * @desc  M站首页
     */
    public function _getProducts($country_bn, $lang, $pagesize = 10) {



        $condition['page_size'] = $pagesize;
        $es_product_model = new EsProductModel();
        $list = $es_product_model->getNewProducts($condition, $lang, $country_bn);
        $ret = $this->_getdata($list, $lang);


        return $ret;
    }

    /**
     * Description of 获取人气推荐产品
     * @author  zhongyg
     * @date    2018-05-09 16:50:09
     * @version V2.0
     * @desc  M站首页
     */
    public function getProductsAction() {
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

        $pagesize = $this->getPut('pagesize', 10);
        $current_no = $this->getPut('current_no', 1);
        $condition['current_no'] = $current_no;
        $condition['page_size'] = $pagesize;
        $es_product_model = new EsProductModel();
        $list = $es_product_model->getNewProducts($condition, $lang, $country_bn);
        $ret = $this->_getdata($list, $lang);

        if ($ret) {
            $this->jsonReturn($ret);
        } else {

            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
    }

    /*
     * 数据处理
     */

    private function _getdata($data, $lang = 'en') {

        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }
        foreach ($data[0]['hits']['hits'] as $key => $item) {
            $list[$key] = $item["_source"];

            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$key]['img'] = null;
            }
            $list[$key]['id'] = $item['_id'];

            $list[$key]['specs'] = $list[$key]['attrs']['spec_attrs'];

            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
        }
        return $list;
    }

}
