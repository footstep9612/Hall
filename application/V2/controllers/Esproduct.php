<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Esgoods
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   ES 产品
 */
class EsproductController extends PublicController {

    protected $index = 'erui2_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '1';

    //put your code here
    public function init() {

        error_reporting(E_ERROR);
        //  parent::init();
        $this->es = new ESClient();
    }

    /*
     * 获取列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function listAction() {
        $model = new EsProductModel();
        $lang = $this->get('lang') ?: $this->getPut('lang', 'zh');
        $ret = $model->getProducts($this->put_data, null, $lang);
        if ($ret) {
            $data = $ret[0];

            $list = $this->_getdata($data);
            $send['count'] = intval($data['hits']['total']);
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($ret[3]) && $ret[3] > 0) {
                $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
            } else {
                $send['allcount'] = $send['count'];
            }
            if (isset($this->put_data['sku_count']) && $this->put_data['sku_count'] == 'Y') {
                $send['sku_count'] = $data['aggregations']['sku_num']['value'];
            }
            $send['data'] = $list;
            $this->_update_keywords();
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

        foreach ($data['hits']['hits'] as $key => $item) {
            $list[$key] = $item["_source"];
            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$key]['img'] = null;
            }
            $list[$key]['id'] = $item['_id'];
            $show_cats = json_decode($item["_source"]["show_cats"], true);
            if ($show_cats) {
                rsort($show_cats);
            }
            $list[$key]['show_cats'] = $show_cats;
            $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
            $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
            $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
            $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
        }
        return $list;
    }

    /* 获取分类列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getcatlist($material_cat_nos, $material_cats) {
        ksort($material_cat_nos);
        $condition = $this->put_data;
        $condition['token'] = $condition['show_cat_no'] = null;
        unset($condition['token'], $condition['show_cat_no']);
        $catno_key = 'ShowCats_' . md5(http_build_query($material_cat_nos) . '&lang=' . $this->getLang() . http_build_query($condition));
        $catlist = json_decode(redisGet($catno_key), true);
        if (!$catlist) {
            $matshowcatmodel = new ShowmaterialcatModel();
            $showcats = $matshowcatmodel->getshowcatsBymaterialcatno($material_cat_nos, $this->getLang());
            $new_showcats3 = [];
            foreach ($showcats as $showcat) {
                $material_cat_no = $showcat['material_cat_no'];
                unset($showcat['material_cat_no']);
                $new_showcats3[$showcat['cat_no']] = $showcat;
                if (isset($material_cats[$material_cat_no])) {
                    $new_showcats3[$showcat['cat_no']]['count'] = $material_cats[$material_cat_no];
                }
            }
            rsort($new_showcats3);
            foreach ($new_showcats3 as $key => $item) {
                $model = new EsproductModel();
                $condition['show_cat_no'] = $item['cat_no'];
                $item['count'] = $model->getcount($condition, $this->getLang());
                $new_showcats3[$key] = $item;
            }
            redisSet($catno_key, json_encode($new_showcats3), 86400);
            return $new_showcats3;
        }
        return $catlist;
    }

    /*
     * 更新关键词表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _update_keywords() {
        if ($this->put_data['keyword']) {
            $search = [];
            $search['keywords'] = $this->put_data['keyword'];
            if ($this->user['email']) {
                $search['user_email'] = $this->user['email'];
            } else {
                $search['user_email'] = '';
            }
            $search['search_time'] = date('Y-m-d H:i:s');
            $usersearchmodel = new BuyersearchhisModel();
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
    }

    /**
     * Description of 数据导入
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function importAction($lang = 'en') {
        try {
            set_time_limit(0);
            ini_set('memory_limi', '1G');
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
            $time = redisGet('ES_PRODUCT_TIME');
            foreach ($this->langs as $lang) {
                $espoductmodel = new EsproductModel();
                $espoductmodel->updateproducts($lang, $time);
            }
            redisSet('ES_PRODUCT_TIME', date('Y-m-d H:i:s'));
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
     * Description of 创建索引
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function indexAction() {
        $body['mappings'] = [];
        $product_properties = $this->productAction('en');
        $goods_properties = $this->goodsAction('en');
        foreach ($this->langs as $lang) {
            $body['mappings']['goods_' . $lang]['properties'] = $goods_properties;
            $body['mappings']['goods_' . $lang]['_all'] = ['enabled' => false];
            $body['mappings']['product_' . $lang]['properties'] = $product_properties;
            $body['mappings']['product_' . $lang]['_all'] = ['enabled' => false];
        }
        $this->es->create_index($this->index, $body);
        $this->setCode(1);
        $this->setMessage('成功!');
        $this->jsonReturn();
    }

    /**
     * Description of 商品索引信息组合
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function goodsAction() {

        $int_analyzed = ['type' => 'integer'];
        $not_analyzed = ['type' => 'float'];
        $ik_analyzed = [
            'index' => 'no',
            'type' => 'string',
            'fields' => [
                'all' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'standard' => [
                    'analyzer' => 'standard',
                    'type' => 'string'
                ],
                'ik' => [
                    'analyzer' => 'ik',
                    'type' => 'string'
                ],
                'whitespace' => [
                    'analyzer' => 'whitespace',
                    'type' => 'string'
                ]
            ]
        ];
        $not_analyzed = [
            'index' => 'not_analyzed',
            'type' => 'string'
        ];
        $body = [
            'min_order_qty' => $int_analyzed,
            'min_pack_naked_qty' => $int_analyzed,
            'min_pack_unit' => $ik_analyzed,
            'nude_cargo_w_mm' => $int_analyzed,
            'suppliers' => $ik_analyzed,
            'shelves_status' => $not_analyzed,
            'compose_require_pack' => $not_analyzed,
            'qrcode' => $not_analyzed,
            'checked_by' => $int_analyzed,
            'show_name' => $ik_analyzed,
            'nude_cargo_h_mm' => $int_analyzed,
            'created_at' => $not_analyzed,
            'description' => $ik_analyzed,
            'hs_code' => $ik_analyzed,
            'specs' => $ik_analyzed,
            'updated_at' => $not_analyzed,
            'commodity_ori_place' => $ik_analyzed,
            'nude_cargo_l_mm' => $int_analyzed,
            'tx_unit' => $ik_analyzed,
            'material_cat_no' => $not_analyzed,
            'model' => $ik_analyzed,
            'show_cats' => $ik_analyzed,
            'id' => $int_analyzed,
            'lang' => $not_analyzed,
            'sku' => $not_analyzed,
            'nude_cargo_unit' => $ik_analyzed,
            'min_pack_w_mm' => $int_analyzed,
            'name_customs' => $ik_analyzed,
            'checked_at' => $not_analyzed,
            'gross_weight_kg' => $not_analyzed,
            'regulatory_conds' => $ik_analyzed,
            'min_pack_h_mm' => $int_analyzed,
            'created_by' => $int_analyzed,
            'net_weight_kg' => $not_analyzed,
            'exw_days' => $ik_analyzed,
            'tax_rebates_pct' => $not_analyzed,
            'attrs' => $ik_analyzed,
            'pack_type' => $ik_analyzed,
            'min_pack_l_mm' => $int_analyzed,
            'name' => $ik_analyzed,
            'purchase_price' => $not_analyzed,
            'purchase_price_cur_bn' => $not_analyzed,
            'updated_by' => $int_analyzed,
            'spu' => $not_analyzed,
            'meterial_cat' => $ik_analyzed,
            'status' => $not_analyzed
        ];

        return $body;
    }

    /**
     * Description of 产品索引信息组合
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function productAction($lang = 'en') {


        $int_analyzed = ['type' => 'integer'];
        $not_analyzed = ['type' => 'float'];
        $ik_analyzed = [
            'index' => 'no',
            'type' => 'string',
            'fields' => [
                'all' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'standard' => [
                    'analyzer' => 'standard',
                    'type' => 'string'
                ],
                'ik' => [
                    'analyzer' => 'ik',
                    'type' => 'string'
                ],
                'whitespace' => [
                    'analyzer' => 'whitespace',
                    'type' => 'string'
                ]
            ]
        ];
        $not_analyzed = [
            'index' => 'not_analyzed',
            'type' => 'string'
        ];

        $body = [
            'principle' => $ik_analyzed,
            'recommend_flag' => $not_analyzed,
            'skus' => $ik_analyzed,
            'keywords' => $ik_analyzed,
            'suppliers' => $ik_analyzed,
            'supply_ability' => $ik_analyzed,
            'qrcode' => $not_analyzed,
            'checked_by' => $int_analyzed,
            'show_name' => $ik_analyzed,
            'created_at' => $not_analyzed,
            'description' => $ik_analyzed,
            'availability' => $ik_analyzed,
            'source' => $ik_analyzed,
            'resp_time' => $ik_analyzed,
            'specs' => $ik_analyzed,
            'updated_at' => $not_analyzed,
            'delivery_cycle' => $ik_analyzed,
            'material_cat_no' => $not_analyzed,
            'warranty' => $ik_analyzed,
            'show_cats' => $ik_analyzed,
            'customizability' => $ik_analyzed,
            'id' => $int_analyzed,
            'lang' => $not_analyzed,
            'brand' => $ik_analyzed,
            'checked_at' => $not_analyzed,
            'resp_rate' => $not_analyzed,
            'profile' => $ik_analyzed,
            'created_by' => $int_analyzed,
            'attrs' => $ik_analyzed,
            'exe_standard' => $ik_analyzed,
            'attachs' => $ik_analyzed,
            'source_detail' => $ik_analyzed,
            'advantages' => $ik_analyzed,
            'sku_count' => $int_analyzed,
            'name' => $ik_analyzed,
            'updated_by' => $int_analyzed,
            'spu' => $not_analyzed,
            'availability_ratings' => $int_analyzed,
            'app_scope' => $ik_analyzed,
            'target_market' => $ik_analyzed,
            'tech_paras' => $ik_analyzed,
            'properties' => $ik_analyzed,
            'meterial_cat' => $ik_analyzed,
            'status' => $not_analyzed
        ];
        return $body;
    }

}
