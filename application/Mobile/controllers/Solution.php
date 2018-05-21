<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Solution
 * @author  zhongyg
 * @date    2018-5-15 9:31:17
 * @version V2.0
 * @desc
 */
class SolutionController extends PublicController {

    //put your code here
    public function init() {
        $this->token = false;
        parent::init();
    }

    /*
     * 获取解决方案列表
     */

    public function listAction() {
        $condition = $this->getPut();

        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $cats = $this->_getCats($condition);
        $cat_ids = [];
        foreach ($cats as $cat) {
            $cat_ids[] = $cat['catid'];
            if ($cat['arrchildid']) {
                $arrchildids = explode(',', $cat['arrchildid']);
                $cat_ids = array_merge($cat_ids, $arrchildids);
            }
        }
        $condition['catids'] = $cat_ids;
        $solution_model = new SolutionModel();
        $data = $solution_model->getList($condition);
        if ($data) {
            $this->setvalue('cats', $cats);
            $this->setvalue('count', $solution_model->getCount($condition));
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        }
    }

    /*
     * 获取解决方案列表
     */

    public function listByspuAction() {
        $condition = $this->getPut();

        if (empty($condition['spu'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('spu 不能为空!');
            $this->jsonReturn();
        }
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('lang 不能为空!');
            $this->jsonReturn();
        }
        $data = (new SolutionModel)->getListBySpu($condition);
        if ($data) {
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        }
    }

    private function _getCats($condition) {
        $solution_cat_model = new SolutionCatModel();

        $data = $solution_cat_model->getList($condition);

        return $data;
    }

    /*
     * 获取解决方案详情
     */

    public function InfoAction() {
        $id = $this->getPut('id');
        $solution_model = new SolutionModel();
        $info = $solution_model->Info($id);
        if ($info) {
            $solution_detail_model = new SolutionDetailModel();
            $detail = $solution_detail_model->Info($id);
            $info = array_merge($info, $detail);
            if ($info['goods']) {
                $spus = explode('|', $info['goods']);
                if ($spus) {
                    $esproduct_model = new EsProductModel();
                    $condition = [];
                    $condition['lang'] = $this->getPut('lang');
                    $condition['country_bn'] = $this->getPut('country_bn');
                    $condition['spus'] = $spus;
                    $products = $esproduct_model->getNewProducts($condition);

                    $info['products'] = $this->_getdata($products[0]);
                } else {
                    $info['products'] = [];
                }
            } else {
                $info['products'] = [];
            }
            if ($info['relation']) {
                $relation_ids = explode('|', $info['relation']);
                if ($relation_ids) {
                    $condition = [];
                    $condition['lang'] = str_replace('show_solution_', '', $info['template']);
                    $condition['ids'] = $relation_ids;
                    $relations = $solution_model->getList($condition);
                    $info['relations'] = $relations;
                } else {
                    $info['relations'] = [];
                }
            } else {
                $info['relations'] = [];
            }

            $this->jsonReturn($info);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        }
    }

    /*
     * 数据处理
     */

    private function _getdata($data) {
        $list = [];
        if (!empty($data['hits']['hits'])) {
            foreach ($data['hits']['hits'] as $key => $item) {
                $list[$key] = [];
                $list[$key]['show_name'] = $item['_source']['show_name'];
                $list[$key]['spu'] = $item['_source']['spu'];
                $list[$key]['sku_count'] = $item['_source']['sku_count'];
                $attachs = json_decode($item['_source']['attachs'], true);
                if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                    $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
                } else {
                    $list[$key]['img'] = null;
                }
                $list[$key]['id'] = $item['_source']['id'];
            }

            return $list;
        } else {
            return $list;
        }
    }

    /*
     * 获取解决方案分类
     */

    public function getCatsAction() {
        $condition = $this->getPut();
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $cats = $this->_getCats($condition);
        if ($cats) {

            $this->jsonReturn($cats);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        }
    }

}
