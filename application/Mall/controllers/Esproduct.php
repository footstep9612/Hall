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
class EsproductController extends PublicController {

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

    public function SearchAction() {
        $es = new ESClient();
        $index = $this->getPut('index', 'erui_goods');
        $type = $this->getPut('type', 'product_en');
        $from = $this->getPut('from', '0');
        $size = $this->getPut('size', '10');
        $body = $this->getPut('body');
        $body['_source'] = ['spu', 'show_name'];
        $es->setbody($body);
        $ret = $es->search($index, $type, $from, $size);

        echo json_encode($ret);
        die;
    }

    /*
     * 门户新需求 产品列表
     */

    public function listAction() {
        $model = new EsProductModel();
        $condition = $this->getPut();

        $country_bn = $this->getPut('country_bn');
        $is_show_cat = false;
        $show_cat_name = null;
        $is_brand = false;
        $brand_name = null;
        $ret = $model->getNewProducts($condition, $this->getLang(), $country_bn, $is_show_cat, $show_cat_name, $is_brand, $brand_name);

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
     * 门户新需求 产品列表
     */

    public function listNewAction() {
        $model = new EsProductModel();
        $condition = $this->getPut();
        $country_bn = $this->getPut('country_bn');
        $is_show_cat = false;
        $show_cat_name = null;
        $is_brand = false;
        $brand_name = null;
        $ret = $model->getNewProducts($condition, $this->getLang(), $country_bn, $is_show_cat, $show_cat_name, $is_brand, $brand_name);


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
            $this->update_keywords();
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
     * 获取分类列表
     */

    private function getcatlist($show_cat_nos, $show_cats) {
        ksort($show_cat_nos);
        $condition = $this->getPut();
        $condition['token'] = $condition['show_cat_no'] = null;
        unset($condition['token'], $condition['show_cat_no']);
        $catno_key = 'ShowCats_' . md5(http_build_query($show_cat_nos) . '&lang = ' . $this->getLang() . http_build_query($condition));
        $showcats = json_decode(redisGet($catno_key), true);
        if (!$showcats) {
            $showcatmodel = new ShowCatModel();
            $showcats = $showcatmodel->getshowcatsByshowcatnos($show_cat_nos, $this->getLang(), true, $condition['country_bn']);
            redisSet($catno_key, json_encode($showcats), 3600);
        }
        $new_showcats3 = [];
        foreach ($showcats as $showcat) {
            $new_showcats3[$showcat['cat_no']] = $showcat;
            if (isset($show_cats[$showcat['cat_no']])) {
                $new_showcats3[$showcat['cat_no']]['count'] = $show_cats[$showcat['cat_no']];
            }
        }
        rsort($new_showcats3);
        return $new_showcats3;
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
