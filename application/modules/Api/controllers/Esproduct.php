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

        $this->es = new ESClient();
        parent::init();
    }

    /*
     * product数据导入
     */

    public function importAction($lang = 'en') {
        try {
            set_time_limit(0);
            foreach ($this->langs as $lang) {
                $espoductmodel = new EsproductModel();
                $espoductmodel->importproducts($lang);
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

    public function indexAction() {
        // $this->es->delete($this->index);
        //$model = new EsgoodsModel();

        $body['mappings'] = [];

        foreach ($this->langs as $lang) {
            $body['mappings']['goods_' . $lang] = $this->goodsAction($lang);

            $body['mappings']['product_' . $lang] = $this->productAction($lang);
        }

        $this->es->create_index($this->index, $body);
        $this->setCode(1);
        $this->setMessage('成功!');
        $this->jsonReturn();
    }

    public function listAction() {

        $model = new EsproductModel();
        $ret = $model->getproducts($this->put_data, null, $this->getLang());

        if ($ret) {
            $list = [];

            $data = $ret[0];
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($ret[3]) && $ret[3] > 0) {

                $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
            } else {
                $send['allcount'] = $send['count'];
            }
            foreach ($data['hits']['hits'] as $key => $item) {
                $list[$key] = $item["_source"];
                $list[$key]['id'] = $item['_id'];
            }
            $material_cat_nos = [];
            foreach ($data['aggregations']['meterial_cat_no']['buckets'] as $item) {
                $material_cats[$item['key']] = $item['doc_count'];
                $material_cat_nos[] = $item['key'];
            }

            $material_cat_nos = ksort($material_cat_nos);
            $catno_key = 'show_cats_' . md5(http_build_query($material_cat_nos) . '&lang=' . $this->getLang());
            $catlist = json_decode(redisGet($catno_key), true);
            if (!$catlist) {
                $matshowcatmodel = new ShowmaterialcatModel();

                $showcats = $matshowcatmodel->getshowcatsBymaterialcatno($material_cat_nos, $this->getLang());

                $new_showcats1 = $new_showcats2 = $new_showcats3 = [];
                $new_showcat2_nos = [];
                $new_showcat1_nos = [];

                foreach ($showcats as $showcat) {
                    $material_cat_no = $showcat['material_cat_no'];
                    unset($showcat['material_cat_no']);
                    $new_showcats3[$showcat['parent_cat_no']][$showcat['cat_no']] = $showcat;
                    if (isset($material_cats[$material_cat_no])) {
                        $new_showcats3[$showcat['parent_cat_no']][$showcat['cat_no']]['count'] = $material_cats[$material_cat_no];
                    } else {
                        $new_showcats3[$showcat['parent_cat_no']][$showcat['cat_no']]['count'] = 0;
                    }
                    $new_showcat2_nos[] = $showcat['parent_cat_no'];
                }

                $showcat2s = $matshowcatmodel->getshowcatsBycatno($new_showcat2_nos, $this->getLang());
                foreach ($showcat2s as $showcat2) {

                    $new_showcats2[$showcat2['parent_cat_no']][$showcat2['cat_no']] = $showcat2;
                    if (isset($new_showcats3[$showcat2['cat_no']])) {
                        foreach ($new_showcats3[$showcat2['cat_no']] as $showcat3) {

                            $new_showcats2[$showcat2['parent_cat_no']][$showcat2['cat_no']]['count'] += $showcat3['count'];
                        }
                        $new_showcats2[$showcat2['parent_cat_no']][$showcat2['cat_no']]['childs'] = $new_showcats3[$showcat2['cat_no']];
                    }

                    $new_showcat1_nos[] = $showcat2['parent_cat_no'];
                }

                $showcat1s = $matshowcatmodel->getshowcatsBycatno($new_showcat1_nos, $this->getLang());
                foreach ($showcat1s as $showcat1) {

                    $new_showcats1[$showcat1['cat_no']] = $showcat1;
                    if (isset($new_showcats2[$showcat1['cat_no']])) {
                        foreach ($new_showcats2[$showcat1['cat_no']] as $showcat2) {

                            $new_showcats1[$showcat1['cat_no']]['count'] += $showcat2['count'];
                        }
                        $new_showcats1[$showcat1['cat_no']]['childs'] = $new_showcats2[$showcat1['cat_no']];
                    }
                }

                $catlist = $new_showcats1;
                redisSet($catno_key, json_encode($catlist), 86400);
            }
            $send['catlist'] = $new_showcats1;
            $send['list'] = $list;
            $this->setCode(MSG::MSG_SUCCESS);
            if ($this->put_data['keyword']) {

                $search = [];
                $search['keywords'] = $this->put_data['keyword'];
                $search['user_email'] = $this->user['email'];
                $search['search_time'] = date('Y-m-d H:i:s');
                $usersearchmodel = new UsersearchhisModel();
                $condition = ['user_email' => $search['user_email'], 'keywords' => $search['keywords']];
                $row = $usersearchmodel->exist($condition);
                if ($row) {
                    $search['search_count'] = intval($row['search_count']) + 1;
                    $usersearchmodel->update_data($search);
                }
            }


            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function getcatsAction() {

        $model = new EsproductModel();
        $ret = $model->getshow_catlist($this->put_data, $this->getLang());
        if ($ret) {
            $list = [];

            $data = $ret[0];
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($ret[3]) && $ret[3] > 0) {

                $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
            } else {
                $send['allcount'] = $send['count'];
            }
            foreach ($data['hits']['hits'] as $key => $item) {
                $list[$key] = $item["_source"];
                $list[$key]['id'] = $item['_id'];
            }
            $send['list'] = $list;
            $this->setCode(MSG::MSG_SUCCESS);
            if ($this->put_data['keyword']) {
                $search = [];
                $search['keyword'] = $this->put_data['keyword'];
                $search['user_email'] = $this->user['email'];
                $search['search_time'] = date('Y-m-d H:i:s');
                $usersearchmodel = new UsersearchhisModel();
                if ($row = $usersearchmodel->exist($condition)) {
                    $search['search_count'] = intval($row['search_count']) + 1;
                    $usersearchmodel->update_data($search);
                }
            }
            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function goodsAction($lang = 'en') {
        if (!in_array($lang, $this->langs)) {

            $lang = 'en';
        }
        $type_string = 'text';
        $analyzer = 'ik_max_word';
        if ($this->version != 5) {
            $type_string = 'string';
            $analyzer = 'ik';
        }

        $body = ['properties' => [
                'id' => [
                    'type' => 'integer',
                    "index" => "not_analyzed",
                ],
                'lang' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'package_quantity' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'exw_day' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'spu' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'sku' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'attachs' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'qrcode' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'model' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'show_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'purchase_price1' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'purchase_price2' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'purchase_price_cur' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'purchase_unit' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'pricing_flag' => [
                    'type' => $type_string, "index" => "not_analyzed",
                ],
                'created_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'created_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ],
                'meterial_cat' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'show_cats' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'attrs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'specs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],]];

        return $body;
    }

    public function productAction($lang = 'en') {

        $type_string = 'text';
        $analyzer = 'ik_max_word';
        if ($this->version != 5) {
            $type_string = 'string';
            $analyzer = 'ik';
        }
        $body = ['properties' => [
                'id' => [
                    'type' => 'integer',
                    "index" => "not_analyzed",
                ],
                'lang' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'meterial_cat_no' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'spu' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'attachs' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'skus' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'qrcode' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'show_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'keywords' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'exe_standard' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'app_scope' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'tech_paras' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'advantages' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'profile' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'supplier_id' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'supplier_name' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'brand' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 2
                ],
                'source' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'source_detail' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 1
                ],
                'recommend_flag' => [
                    'type' => $type_string,
                    'analyzer' => 'whitespace'
                ],
                'status' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'created_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'created_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ], 'updated_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'updated_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ], 'checked_by' => [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ], 'checked_at' => [
                    'type' => 'date',
                    "index" => "not_analyzed",
                    "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd"
                ],
                'meterial_cat' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'supply_capabilitys' =>
                [
                    'type' => $type_string,
                    "index" => "not_analyzed",
                ],
                'show_cats' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'attrs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],
                'specs' => [
                    'type' => $type_string,
                    "analyzer" => $analyzer,
                    "search_analyzer" => $analyzer,
                    "include_in_all" => "true",
                    "boost" => 4
                ],]];
        return $body;
    }

}
