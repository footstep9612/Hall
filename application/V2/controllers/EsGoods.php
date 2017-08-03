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
class EsGoodsController extends PublicController {

    protected $index = 'erui2_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '1';

    //put your code here
    public function init() {
        $this->es = new ESClient();
        //  parent::init();
    }

    /**
     * Description of 列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */
    public function listAction() {
        $lang = $this->get('lang', '')?:$this->getPut('lang', 'zh');
        $data= $this->getPut();
        $model = new EsgoodsModel();
//        $_source = ['id', 'sku', 'spu', 'name', 'show_name', 'model'
//            , 'purchase_price1', 'purchase_price2', 'attachs', 'package_quantity', 'exw_day',
//            'purchase_price_cur', 'purchase_unit', 'pricing_flag', 'show_cats',
//            'meterial_cat', 'brand', 'supplier_name', 'warranty', 'status', 'created_at',
//            'created_by', 'checked_by', 'checked_at', 'update_by', 'update_at', 'shelves_by', 'shelves_at', 'shelves_status', 'checked_desc',];
        $ret = $model->getgoods($data, null, $lang);
        if ($ret) {
            $list = [];
            $data = $ret[0];
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            foreach ($data['hits']['hits'] as $key => $item) {
                $list[$key] = $item["_source"];
                $attachs = json_decode($item["_source"]['attachs'], true);
                if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                    $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
                } else {
                    $product_attach_model = new ProductAttachModel();
                    $list[$key]['img'] = $product_attach_model->getimgBySpu($item["_source"]['spu']);
                }
                $show_cats = json_decode($item["_source"]["show_cats"], true);
                if ($show_cats) {
                    rsort($show_cats);
                }
                $sku = $item["_source"]['sku'];

                if (isset($list_en[$sku])) {
                    $list[$key]['name'] = $list_en[$sku];
                    $list[$key]['name_' . $lang] = $item["_source"]['name'];
                } elseif (isset($list_zh[$sku])) {
                    $list[$key]['name_zh'] = $item["_source"]['name'];
                } else {
                    $list[$key]['name'] = $item["_source"]['name'];
                    $list[$key]['name_' . $lang] = $item["_source"]['name'];
                }

                $list[$key]['show_cats'] = $show_cats;
                if (isset($list[$key]['attrs']) && $list[$key]['attrs']) {
                    $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
                }

                if (isset($list[$key]['specs']) && $list[$key]['specs']) {
                    $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
                }
                $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
                $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
            }
            if (isset($this->put_data['keyword']) && $this->put_data['keyword']) {
                $search = [];
                $search['keywords'] = $this->put_data['keyword'];
                if ($this->user['email']) {
                    $search['user_email'] = $this->user['email'];
                } else {
                    $search['user_email'] = '';
                }
                $search['search_time'] = date('Y-m-d H:i:s');
                $usersearchmodel = new UsersearchhisModel();
                $condition = ['user_email' => $search['user_email'], 'keywords' => $search['keywords']];
                $row = $usersearchmodel->exist($condition);
                if ($row) {
                    $search['search_count'] = intval($row['search_count']) + 1;
                    $search['id'] = $row['id'];
                    $usersearchmodel->update_data($search);
                } else {
                    $search['search_count'] = 1;
                    $usersearchmodel->add($search);
                }
            }
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
                $espoductmodel = new EsgoodsModel();
                $espoductmodel->importgoodss($lang);
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
