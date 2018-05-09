<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Esgoods
 *
 * @author zhongyg
 */
class SearchserviceController extends PublicController {

    protected $index = 'erui_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '1';

//put your code here
    public function init() {
        $this->token = false;
        parent::init();
        $this->es = new ESClient();
    }

    /*
     * 门户新需求 产品列表
     */

    public function listAction() {
        $model = new EsProductModel();
        $condition = $this->getPut();
        $description = $this->getPut('description') ? $this->getPut('description') : false;
        $country_bn = $this->getPut('country_bn');
        $is_show_cat = false;
        $show_cat_name = null;
        $is_brand = false;
        $brand_name = null;
        $ret = $model->getNewProducts($condition, $this->getLang(), $country_bn, $is_show_cat, $show_cat_name, $is_brand, $brand_name, $description);

        if ($ret) {
            $data = $ret[0];
            $list = $this->getdata($data, $this->getLang());
            $this->setvalue('count', intval($data['hits']['total']));
            $this->setvalue('current_no', intval($ret[1]));
            $this->setvalue('pagesize', intval($ret[2]));
            $sku_count = $model->getSkuCountByCondition($condition, $this->getLang());
            $this->setvalue('sku_count', $sku_count);
            $this->setvalue('country_bn', $country_bn);
            $this->setvalue('is_show_cat', $is_show_cat);
            $this->setvalue('is_brand', $is_brand);
            $this->setvalue('show_cat_name', $show_cat_name);
            $this->setvalue('brand_name', $brand_name);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($list);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 数据处理
     */

    private function getdata($data, $lang = 'en') {
        $keyword = $this->getPut('keyword');
        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }
        foreach ($data['hits']['hits'] as $key => $item) {
            $list[$key] = $item["_source"];

            if (isset($item['highlight']['show_name.' . $analyzer][0]) && $item['highlight']['show_name.' . $analyzer][0]) {
                $list[$key]['highlight_show_name'] = $item['highlight']['show_name.' . $analyzer][0];
            } elseif (!$list[$key]['show_name'] && isset($item['highlight']['name.' . $analyzer][0]) && $item['highlight']['name.' . $analyzer][0]) {
                $list[$key]['highlight_show_name'] = $item['highlight']['name.' . $analyzer][0];
            } elseif ($list[$key]['show_name']) {
                $list[$key]['highlight_show_name'] = str_ireplace($keyword, '<em>' . $keyword . '</em>', $list[$key]['show_name']);
            } else {
                $list[$key]['highlight_show_name'] = str_ireplace($keyword, '<em>' . $keyword . '</em>', $list[$key]['name']);
            }
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

    /*
     * h获取品牌列表
     */

    public function getBrandsAction() {
        $model = new EsProductModel();
        $condition = $this->getPut();
        unset($condition['token']);
        $data = $model->getBrandsList($condition, $this->getLang());
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 获取分类列表
     */

    public function getCastsAction() {
        $model = new EsProductModel();
        $condition = $this->getPut();
        unset($condition['token']);
        $data = $model->getCatList($condition, $this->getLang());
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 获取规格列表
     */

    public function getSpecsAction() {
        $model = new EsProductModel();
        $condition = $this->getPut();
        unset($condition['token']);
        $data = $model->getSpecsList($condition, $this->getLang());
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
