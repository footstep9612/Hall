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
class EsgoodsController extends ShopMallController {

    protected $index = 'erui_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '5';

    //put your code here
    public function init() {

        //  $this->es = new ESClient();
        //   parent::init();
    }

    public function GoodsAttrAction() {
        set_time_limit(0);
        $goods_attr_model = new GoodsAttrModel();
        $where = [
                //  'deleted_flag' => 'N',
                // 'status' => 'VALID',
                //'spec_attrs is not null or ex_hs_attrs is not null',
        ];
        $count = $goods_attr_model
                ->where($where)
                ->count();


        for ($i = 0; $i <= $count; $i += 1000) {

            $goods_attrs = $goods_attr_model
                    ->where($where)
                    ->order('id asc')
                    ->limit($i, 1000)
                    ->select();

            foreach ($goods_attrs as $goods_attr) {
                if ($goods_attr['spec_attrs'] && !json_decode($goods_attr['spec_attrs'])) {

                    $new_spec_attrs = [];
                    $spec_attrs = str_replace('{', '', $goods_attr['spec_attrs']);
                    $spec_attrs = str_replace('}', '', $spec_attrs);
                    $spec_attrs = str_replace('\\"', '', $spec_attrs);
                    $spec_attrs = str_replace('"', '', $spec_attrs);
                    $spec_attrs = explode(',', $spec_attrs);

                    foreach ($spec_attrs as $spec_attr) {
                        $spec_attrinfo = explode(':', $spec_attr);
                        $new_spec_attrs[$spec_attrinfo[0]] = $spec_attrinfo[1];
                    }
                    LOG::write('Update goods_attr set spec_attrs=\'' . json_encode($new_spec_attrs, 256)
                            . '\' where sku=\'' . $goods_attr['sku'] . '\' and lang=\'' . $goods_attr['lang'] . '\';');
                }
                if ($goods_attr['ex_hs_attrs'] && !json_decode($goods_attr['ex_hs_attrs'])) {
                    $new_spec_attrs = [];
                    $ex_hs_attrs = str_replace('{', '', $goods_attr['ex_hs_attrs']);
                    $ex_hs_attrs = str_replace('}', '', $ex_hs_attrs);
                    $ex_hs_attrs = str_replace('\\"', '', $ex_hs_attrs);
                    $ex_hs_attrs = str_replace('"', '', $ex_hs_attrs);
                    $ex_hs_attrs = explode(',', $ex_hs_attrs);
                    $new_ex_hs_attrs = [];
                    foreach ($ex_hs_attrs as $ex_hs_attr) {
                        $ex_hs_attrinfo = explode(':', $ex_hs_attr);
                        $new_ex_hs_attrs[$ex_hs_attrinfo[0]] = $ex_hs_attrinfo[1];
                    }
                    LOG::write('Update goods_attr set ex_hs_attrs=\'' . json_encode($new_ex_hs_attrs, 256) . '\' where sku=\'' . $goods_attr['sku'] . '\' and lang=\'' . $goods_attr['lang'] . '\';');
                }
                if ($goods_attr['ex_goods_attrs'] && !json_decode($goods_attr['ex_goods_attrs'])) {

                    $ex_goods_attrs = str_replace('{', '', $goods_attr['ex_goods_attrs']);
                    $ex_goods_attrs = str_replace('}', '', $ex_goods_attrs);
                    $ex_goods_attrs = str_replace('\\"', '', $ex_goods_attrs);
                    $ex_goods_attrs = str_replace('"', '', $ex_goods_attrs);
                    $ex_goods_attrs = explode(',', $ex_goods_attrs);
                    $new_ex_goods_attrs = [];
                    foreach ($ex_goods_attrs as $ex_goods_attr) {
                        $ex_goods_attrinfo = explode(':', $ex_goods_attr);
                        $new_ex_goods_attrs[$ex_goods_attrinfo[0]] = $ex_goods_attrinfo[1];
                    }
                    LOG::write('Update goods_attr set ex_goods_attrs=\'' . json_encode($new_ex_goods_attrs, 256) . '\' where sku=\'' . $goods_attr['sku'] . '\' and lang=\'' . $goods_attr['lang'] . '\';');
                }
                if ($goods_attr['other_attrs'] && !json_decode($goods_attr['other_attrs'])) {

                    $other_attrs = str_replace('{', '', $goods_attr['other_attrs']);
                    $other_attrs = str_replace('}', '', $other_attrs);
                    $other_attrs = str_replace('\\"', '', $other_attrs);
                    $other_attrs = str_replace('"', '', $other_attrs);
                    $other_attrs = explode(',', $other_attrs);
                    $new_other_attrs = [];
                    foreach ($other_attrs as $other_attr) {
                        $other_attri = explode(':', $other_attr);
                        $new_other_attrs[$ex_hs_attrinfo[0]] = $ex_hs_attrinfo[1];
                    }
                    LOG::write('Update goods_attr set other_attrs=\'' . json_encode($new_other_attrs, 256) . '\' where sku=\'' . $goods_attr['sku'] . '\' and lang=\'' . $goods_attr['lang'] . '\';');
                }
//                if ($goods_attr['other_attrs'] && !json_decode($goods_attr['other_attrs'])) {
//                    LOG::write($goods_attr['sku']);
//                }
            }
        }
        echo '成功!';
    }

    public function listAction() {
        $lang = $this->getPut('lang', 'en');
        $model = new EsgoodsModel();
        $ret = $model->getgoods($this->put_data, null, $lang);
        if ($ret) {
            $list = [];
            $data = $ret[0];
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            $skus = [];
            if ($lang != 'en') {
                foreach ($data['hits']['hits'] as $key => $item) {
                    $skus[] = $item["_source"]['sku'];
                }

                $ret_en = $model->getgoods(['skus' => $skus], ['sku', 'name'], 'en');

                $list_en = [];
                foreach ($ret_en[0]['hits']['hits'] as $item) {
                    $list_en[$item["_source"]['sku']] = $item["_source"]['name'];
                }
            }
            foreach ($data['hits']['hits'] as $key => $item) {
                $list[$key] = $item["_source"];
                $attachs = json_decode($item["_source"]['attachs'], true);
                if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                    $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
                } else {
                    $list[$key]['img'] = null;
                }
                $show_cats = json_decode($item["_source"]["show_cats"], true);
                if ($show_cats) {
                    rsort($show_cats);
                }
                $sku = $item["_source"]['sku'];

                if (isset($list_en[$sku])) {
                    $list[$key]['name'] = $list_en[$sku];
                    $list[$key]['name_' . $lang] = $item["_source"]['name'];
                } else {
                    $list[$key]['name'] = $item["_source"]['name'];
                    $list[$key]['name_' . $lang] = $item["_source"]['name'];
                }

                $list[$key]['show_cats'] = $show_cats;
                $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
                $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
                $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
                $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
                $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
            }
            if ($this->put_data['keyword']) {
                $search = [];
                $search['keywords'] = $this->put_data['keyword'];
                if ($this->user['email']) {
                    $search['user_email'] = $this->user['email'];
                } else {
                    $search['user_email'] = '';
                }
                $search['search_time'] = date('Y-m-d H:i:s');
                $usersearchmodel = new BuyerSearchHisModel();
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

}
