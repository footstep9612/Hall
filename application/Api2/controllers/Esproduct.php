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

    public function listAction() {
        $model = new EsProductModel();
        $condition = $this->getPut();
        $ret = $model->getproducts($condition, null, $this->getLang());

        if ($ret) {
            $data = $ret[0];
            $list = $this->getdata($data);
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($ret[3]) && $ret[3] > 0) {
                $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
            } else {
                $send['allcount'] = $send['count'];
            }
            if ($this->getPut('sku_count') == 'Y') {
                if (isset($data['aggregations']['sku_count']['value'])) {
                    $send['sku_count'] = $data['aggregations']['sku_count']['value'];
                }
            }
            if (isset($condition['is_catlist']) && $condition['is_catlist'] === 'N') {

            } else {
                $show_cat_nos = $show_cats = [];


                if (!$condition['show_cat_no']) {

                    foreach ($data['aggregations']['show_cat_no3']['buckets'] as $item) {
                        $show_cats[$item['key']] = $item['doc_count'];
                        $show_cat_nos[] = $item['key'];
                    }
                } else {
                    $show_cat_no = $condition['show_cat_no'];
                    unset($condition['show_cat_no']);
                    $ret1 = $model->getproducts($condition, null, $this->getLang());
                    if ($ret1) {
                        $show_cats[$show_cat_no] = $send['count'];
                        $show_cat_nos[] = $show_cat_no;
                        foreach ($ret1[0]['aggregations']['show_cat_no3']['buckets'] as $item) {
                            $show_cats[$item['key']] = $item['doc_count'];
                            $show_cat_nos[] = $item['key'];
                        }
                    }
                }

                $catlist = $this->getcatlist($show_cat_nos, $show_cats);
                $send['catlist'] = $catlist;
            }
            $send['data'] = $list;
            $this->update_keywords();
            $this->setCode(MSG::MSG_SUCCESS);
            $send['code'] = $this->getCode();
            $send['message'] = $this->getMessage();
            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    private function getdata($data) {
        $keyword = $this->getPut('keyword');
        foreach ($data['hits']['hits'] as $key => $item) {
            $list[$key] = $item["_source"];
            if (isset($item['highlight']['show_name.ik'][0]) && $item['highlight']['show_name.ik'][0]) {
                $list[$key]['highlight_show_name'] = $item['highlight']['show_name.ik'][0];
            } elseif (!$list[$key]['show_name'] && isset($item['highlight']['name.ik'][0]) && $item['highlight']['name.ik'][0]) {
                $list[$key]['highlight_show_name'] = $item['highlight']['name.ik'][0];
            } elseif ($list[$key]['show_name']) {
                $list[$key]['highlight_show_name'] = str_replace($keyword, '<em>' . $keyword . '</em>', $list[$key]['show_name']);
            } else {
                $list[$key]['highlight_show_name'] = str_replace($keyword, '<em>' . $keyword . '</em>', $list[$key]['name']);
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

    private function getcatlist($show_cat_nos, $show_cats) {
        ksort($show_cat_nos);
        $condition = $this->getPut();
        $condition['token'] = $condition['show_cat_no'] = null;
        unset($condition['token'], $condition['show_cat_no']);
        $catno_key = 'ShowCats_' . md5(http_build_query($show_cat_nos) . '&lang = ' . $this->getLang() . http_build_query($condition));
        $showcats = json_decode(redisGet($catno_key), true);
        if (!$showcats) {
            $showcatmodel = new ShowCatModel();
            $showcats = $showcatmodel->getshowcatsByshowcatnos($show_cat_nos, $this->getLang());
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

    private function update_keywords() {
        if ($this->getPut('keyword')) {
            $search = [];
            $search['keywords'] = $this->getPut('keyword');
            $this->_getUser();

            if ($this->user['id']) {
                $search['buyer_id'] = $this->user['buyer_id'];
            } else {
                $search['buyer_id'] = 0;
            }
            $search['search_time'] = date('Y-m-d H:i:s');
            $search['created_by'] = null;
            $search['created_at'] = date('Y-m-d H:i:s');
            $usersearchmodel = new BuyerSearchHisModel();
            $condition = ['keywords' => $search['keywords']];
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
    }

}
