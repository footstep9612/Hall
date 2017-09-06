<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MarketAreaModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   ES 商品
 */
class EsgoodsController extends PublicController {

    protected $index = 'erui2_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '1';

    //put your code here
    public function init() {
        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } else {
            parent::init();
        }
    }

    /**
     * Description of 列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */
    public function listAction() {
        $lang = $this->getPut('lang', 'zh');
        $data = $this->getPut();
        $model = new EsGoodsModel();
        $ret = $model->getgoods($data, null, $lang);

        if ($ret) {
            $data = $ret[0];
            $list = $this->_getdata($data);
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);

            $this->_update_keywords();
            $send['data'] = $list;
            $this->setCode(MSG::MSG_SUCCESS);
            $send['code'] = $this->getCode();
            $send['message'] = $this->getMessage();
            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 处理ES 数据
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getdata($data) {

        $user_ids = [];
        foreach ($data['hits']['hits'] as $key => $item) {
            $product = $list[$key] = $item["_source"];
            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$key]['img'] = new stdClass();
            }
            $show_cats = json_decode($item["_source"]["show_cats"], true);
            if ($show_cats) {
                rsort($show_cats);
            }
            if ($product['created_by']) {
                $user_ids[] = $product['created_by'];
            }
            if ($product['updated_by']) {
                $user_ids[] = $product['updated_by'];
            }
            if ($product['checked_by']) {
                $user_ids[] = $product['checked_by'];
            }
            if ($product['onshelf_by']) {
                $user_ids[] = $product['onshelf_by'];
            }
            if (json_decode($list[$key]['brand'], true)) {
                $list[$key]['brand'] = json_decode($list[$key]['brand'], true);
            }
            $list[$key]['show_cats'] = $show_cats;
            $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
            $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
            $list[$key]['material_cat'] = json_decode($list[$key]['material_cat'], true);
            $list[$key]['material_cat_zh'] = json_decode($list[$key]['material_cat_zh'], true);
        }

        $employee_model = new EmployeeModel();
        $usernames = $employee_model->getUserNamesByUserids($user_ids);
        foreach ($list as $key => $val) {
            if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                $val['created_by_name'] = $usernames[$val['created_by']];
            } else {
                $val['created_by_name'] = '';
            }
            if ($val['updated_by'] && isset($usernames[$val['updated_by']])) {
                $val['updated_by_name'] = $usernames[$val['updated_by']];
            } else {
                $val['updated_by_name'] = '';
            }
            if ($val['checked_by'] && isset($usernames[$val['checked_by']])) {
                $val['checked_by_name'] = $usernames[$val['checked_by']];
            } else {
                $val['checked_by_name'] = '';
            }
            if ($val['onshelf_by'] && isset($usernames[$val['onshelf_by']])) {
                $val['onshelf_by_name'] = $usernames[$val['onshelf_by']];
            } else {
                $val['onshelf_by_name'] = '';
            }
            $list[$key] = $val;
        }
        return $list;
    }

    /*
     * 更新关键词表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _update_keywords() {
        $keyword = $this->getPut('keyword');
        $show_cat_no = $this->getPut('show_cat_no');
        $country_bn = $this->getPut('country_bn');
        if ($keyword) {
            $search = [];
            $search['keywords'] = $keyword;
            $search['show_cat_no'] = $show_cat_no;
            $search['country_bn'] = $country_bn;
            $search['search_time'] = date('Y-m-d H:i:s');
            $usersearchmodel = new HotKeywordsModel();
            $uid = defined('UID') ? UID : 0;
            $condition = ['keywords' => $search['keywords']];
            $row = $usersearchmodel->exist($condition);
            if ($row) {
                $search['search_count'] = intval($row['search_count']) + 1;
                $search['id'] = $row['id'];
                $search['updated_by'] = $uid;
                $search['updated_at'] = date('Y-m-d H:i:s');
                $usersearchmodel->update_data($search);
            } else {
                $search['created_by'] = $uid;
                $search['created_at'] = date('Y-m-d H:i:s');
                $search['search_count'] = 1;
                $usersearchmodel->add($search);
            }
        }
    }

    /**
     * Description of 数据导入
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */
    public function importAction($lang = 'en') {
        try {
            //$lang = 'zh';
            set_time_limit(0);
            ini_set('memory_limi', '1G');
            foreach ($this->langs as $lang) {
                $espoductmodel = new EsGoodsModel();
                $espoductmodel->importgoodss($lang);
            }
            $es = new ESClient();
            $es->refresh($this->index);
            $this->setCode(1);
            $this->setMessage('成功!');
            $this->jsonReturn();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            $this->setCode(-2001);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 数据导入
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function updateAction($lang = 'en') {
        try {
            set_time_limit(0);
            ini_set('memory_limi', '1G');
            $time = redisGet('ES_GOODS_TIME');
            redisSet('ES_GOODS_TIME', date('Y-m-d H:i:s'));
            foreach ($this->langs as $lang) {
                $es_goods_model = new EsGoodsModel();
                $es_goods_model->updategoodss($lang, $time);
            }

            $this->setCode(1);
            $this->setMessage('成功!');
            $this->jsonReturn();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            $this->setCode(-2001);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
