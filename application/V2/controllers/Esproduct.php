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

        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } else {
            parent::init();
        }
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

        $user_ids = [];
        foreach ($data['hits']['hits'] as $key => $item) {
            $product = $list[$key] = $item["_source"];
            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$key]['img'] = null;
            }
            $list[$key]['id'] = $item['_id'];
            //$show_cats = json_decode($item["_source"]["show_cats"], true);
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
            //   $list[$key]['show_cats'] = $show_cats;
            $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
            $list[$key]['specs'] = json_decode($list[$key]['specs'], true);

            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
            $list[$key]['material_cat'] = json_decode($list[$key]['material_cat'], true);
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
                $model = new EsProductModel();
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
     * @desc   ES 产品
     */
    public function importAction($lang = 'en') {
        try {
            set_time_limit(0);
            ini_set('memory_limi', '1G');
            foreach ($this->langs as $lang) {
                $espoductmodel = new EsProductModel();
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
            redisSet('ES_PRODUCT_TIME', date('Y-m-d H:i:s'));
            foreach ($this->langs as $lang) {
                $espoductmodel = new EsProductModel();
                $espoductmodel->updateproducts($lang, $time);
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
        $es = new ESClient();
        $es->create_index($this->index, $body);
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
            'status' => $not_analyzed,
            'minimumorderouantity' => $not_analyzed,
            'onshelf_flag' => $not_analyzed,
            'onshelf_by' => $not_analyzed,
            'onshelf_at' => $not_analyzed,
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
            'status' => $not_analyzed,
            'max_exw_day' => $not_analyzed,
            'min_exw_day' => $not_analyzed,
            'min_pack_unit' => $not_analyzed,
            'minimumorderouantity' => $not_analyzed,
            'onshelf_flag' => $not_analyzed,
            'onshelf_by' => $not_analyzed,
            'onshelf_at' => $not_analyzed,
        ];
        return $body;
    }

}
