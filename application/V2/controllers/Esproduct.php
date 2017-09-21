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

        $username = $this->get('username');
        $password = $this->get('password');

        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } elseif ($username == '016417' && $password) {
            //$arr = ['user_no' => $username, 'password' => $password];
            $model = new EmployeeModel();
            $info = $model->field('password_hash')->where(['user_no' => $username])->find();
            if ($info && $info['password_hash'] == $password) {
                ini_set("display_errors", "On");
                error_reporting(E_ERROR | E_STRICT);
            } else {

                parent::init();
            }
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
        $lang = $this->getPut('lang', 'zh');

        $condition = $this->getPut();
        $ret = $model->getProducts($condition, null, $lang);
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
                $send['sku_count'] = $data['aggregations']['sku_count']['value'];
            } else {
                $send['sku_count'] = 0;
            }
            if (isset($this->put_data['onshelf_count']) && $this->put_data['onshelf_count'] == 'Y') {
                $condition['onshelf_flag'] = 'N';
                $condition['sku_count'] = 'Y';
                $ret_N = $model->getProducts($condition, $lang);
                $send['onshelf_count_N'] = intval($ret_N[0]['hits']['total']);
                $send['onshelf_sku_count_N'] = intval($ret_N[0]['aggregations']['sku_count']['value']);
                $condition['onshelf_flag'] = 'Y';

                $ret_y = $model->getProducts($condition, $lang);
                $send['onshelf_count_Y'] = intval($ret_y[0]['hits']['total']);
                $send['onshelf_sku_count_Y'] = intval($ret_y[0]['aggregations']['sku_count']['value']);
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
                $list[$key]['img'] = new stdClass();
            }
            $list[$key]['id'] = $item['_id'];
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
            $list[$key]['show_cats'] = $show_cats;
            $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
            $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
            $list[$key]['brand'] = json_decode($list[$key]['brand'], true);
            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
            if (!empty($list[$key]['material_cat'])) {
                $list[$key]['material_cat'] = json_decode($list[$key]['material_cat'], true);
            } else {
                $list[$key]['material_cat'] = new \stdClass();
            }
            if (!empty($list[$key]['material_cat_zh'])) {
                $list[$key]['material_cat_zh'] = json_decode($list[$key]['material_cat_zh'], true);
            } else {
                $list[$key]['material_cat_zh'] = new \stdClass();
            }
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
        $state = $es->getstate();
        if (!isset($state['metadata']['indices'][$this->index])) {
            $es->create_index($this->index, $body);
        }
        $this->setCode(1);
        $this->setMessage('成功!');
        $this->jsonReturn();
    }

    /**
     * Description of 创建索引
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function mappingAction() {
        $body['mappings'] = [];
        $product_properties = $this->productAction('en');
        $goods_properties = $this->goodsAction('en');
        $es = new ESClient();
        foreach ($this->langs as $lang) {
            $goods_mapParam = [
                'properties' => $goods_properties,
                '_all' => ['enabled' => false]
            ];
            $product_mapParam = [
                'properties' => $product_properties,
                '_all' => ['enabled' => false]
            ];
            $es->putMapping($this->index, 'goods_' . $lang, $goods_mapParam);
            $es->putMapping($this->index, 'product_' . $lang, $product_mapParam);
        }

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
        $es = new ESClient();
        $info = $es->getversion();
        if (substr($info['version']['number'], 0, 1) == 1) {
            $analyzer = 'ik';
            $type = 'string';
        } else {
            $analyzer = 'ik_max_word';
            $type = 'text';
        }
        $int_analyzed = ['type' => 'integer'];
        $ik_analyzed = [
            'index' => 'no',
            'type' => $type,
            'fields' => [
                'no' => [
                    'index' => 'no',
                    'type' => $type,
                ],
                'all' => [
                    'index' => 'not_analyzed',
                    'type' => $type
                ],
                'standard' => [
                    'analyzer' => 'standard',
                    'type' => $type
                ],
                'ik' => [
                    'analyzer' => $analyzer,
                    'type' => $type
                ],
                'whitespace' => [
                    'analyzer' => 'whitespace',
                    'type' => $type
                ]
            ]
        ];
        $not_analyzed = [
            'index' => 'not_analyzed',
            'type' => $type
        ];
        $body = [
            'id' => $int_analyzed, //id
            'lang' => $not_analyzed, //语言
            'spu' => $not_analyzed, //SPU
            'sku' => $not_analyzed, //SKU
            'qrcode' => $not_analyzed, //商品二维码
            'name' => $ik_analyzed, //商品名称
            'show_name_loc' => $ik_analyzed, //中文品名
            'show_name' => $ik_analyzed, //商品展示名称
            'model' => $ik_analyzed, //型号
            'description' => $ik_analyzed, //描述
            'exw_days' => $ik_analyzed, //出货周期（天）
            'min_pack_naked_qty' => $int_analyzed, //最小包装内裸货商品数量
            'nude_cargo_unit' => $ik_analyzed, //商品裸货单位
            'min_pack_unit' => $ik_analyzed, //最小包装单位
            'min_order_qty' => $int_analyzed, //最小订货数量
            'purchase_price' => $not_analyzed, //进货价格
            'purchase_price_cur_bn' => $not_analyzed, //进货价格币种
            'nude_cargo_l_mm' => $int_analyzed, //裸货尺寸长(mm)
            'nude_cargo_w_mm' => $int_analyzed, //裸货尺寸宽(mm)
            'nude_cargo_h_mm' => $int_analyzed, //裸货尺寸高(mm)
            'min_pack_l_mm' => $int_analyzed, //最小包装后尺寸长(mm)
            'min_pack_w_mm' => $int_analyzed, //最小包装后尺寸宽(mm)
            'min_pack_h_mm' => $int_analyzed, //最小包装后尺寸高(mm)
            'net_weight_kg' => $not_analyzed, //净重(kg)
            'gross_weight_kg' => $not_analyzed, //毛重(kg)
            'compose_require_pack' => $not_analyzed, //仓储运输包装及其他要求
            'pack_type' => $ik_analyzed, //包装类型
            'name_customs' => $ik_analyzed, //报关名称
            'hs_code' => $ik_analyzed, //海关编码
            'tx_unit' => $ik_analyzed, //成交单位
            'tax_rebates_pct' => $not_analyzed, //退税率(%)
            'regulatory_conds' => $ik_analyzed, //监管条件
            'commodity_ori_place' => $ik_analyzed, //境内货源地
            'source' => $ik_analyzed, //数据来源
            'source_detail' => $ik_analyzed, //数据来源详情
            'status' => $not_analyzed, //状态
            'created_by' => $int_analyzed, //创建人
            'created_at' => $not_analyzed, //创建时间
            'updated_by' => $int_analyzed, //修改人
            'updated_at' => $not_analyzed, //修改时间
            'checked_by' => $int_analyzed, //审核人
            'checked_at' => $not_analyzed, //审核时间
            'deleted_flag' => $not_analyzed, //删除标志
            /* 扩展内容 */
            'name_loc' => $ik_analyzed, //中文品名
            'brand' => $ik_analyzed, //品牌
            'suppliers' => $ik_analyzed, //供应商数组 json
            'sppplier_count' => $not_analyzed,
            'specs' => $ik_analyzed, //规格数组 json
            'material_cat_no' => $not_analyzed, //物料编码
            'show_cats' => $ik_analyzed, //展示分类数组 json
            'attrs' => $ik_analyzed, //属性数组 json
            'material_cat' => $ik_analyzed, //物料分类对象 json
            'material_cat_zh' => $ik_analyzed, //物料中文分类对象 json
            'onshelf_flag' => $not_analyzed, //上架状态
            'onshelf_by' => $not_analyzed, //上架人
            'onshelf_at' => $not_analyzed, //上架时间
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
        $es = new ESClient();
        $info = $es->getversion();
        if (substr($info['version']['number'], 0, 1) == 1) {
            $analyzer = 'ik';
            $type = 'string';
        } else {
            $analyzer = 'ik_max_word';
            $type = 'text';
        }

        $int_analyzed = ['type' => 'integer'];
        $ik_analyzed = [
            'index' => 'no',
            'type' => $type,
            'fields' => [
                'no' => [
                    'index' => 'no',
                    'type' => $type,
                ],
                'all' => [
                    'index' => 'not_analyzed',
                    'type' => $type
                ],
                'standard' => [
                    'analyzer' => 'standard',
                    'type' => $type
                ],
                'ik' => [
                    'analyzer' => $analyzer,
                    'type' => $type
                ],
                'whitespace' => [
                    'analyzer' => 'whitespace',
                    'type' => $type
                ]
            ]
        ];
        $not_analyzed = [
            'index' => 'not_analyzed',
            'type' => $type
        ];

        $body = [
            'id' => $int_analyzed, //ID
            'lang' => $not_analyzed, //语言
            'material_cat_no' => $not_analyzed, //物料分类编码
            'spu' => $not_analyzed, //SPU
            'qrcode' => $not_analyzed, //二维码
            'name' => $ik_analyzed, //产品名称
            'show_name' => $ik_analyzed, // 产品展示
            'brand' => $ik_analyzed, //品牌
            'keywords' => $ik_analyzed, //关键词
            'exe_standard' => $ik_analyzed, //执行标准
            'tech_paras' => $ik_analyzed, //简介',
            'advantages' => $ik_analyzed, //产品优势
            'description' => $ik_analyzed, //详情介绍
            'profile' => $ik_analyzed, //产品简介
            'principle' => $ik_analyzed, //工作原理
            'app_scope' => $ik_analyzed, //适用范围
            'properties' => $ik_analyzed, //使用特点
            'warranty' => $ik_analyzed, //质保期
            'customization_flag' => $not_analyzed, //定制标志
            'customizability' => $ik_analyzed, //定制能力
            'availability' => $ik_analyzed, //供应能力
            'availability_ratings' => $int_analyzed, //供应能力评级
            'resp_time' => $ik_analyzed, //响应时间
            'resp_rate' => $not_analyzed, //响应率
            'delivery_cycle' => $ik_analyzed, //出货周期
            'target_market' => $not_analyzed, //目标市场
            'supply_ability' => $ik_analyzed, //供应能力
            'source' => $ik_analyzed, //数据来源
            'source_detail' => $ik_analyzed, //数据来源详情
            'sku_count' => $int_analyzed, //SKU数
            'recommend_flag' => $not_analyzed, //推荐
            'status' => $not_analyzed, //状态
            'created_by' => $int_analyzed, //创建人
            'created_at' => $not_analyzed, //创建时间
            'updated_by' => $int_analyzed, //修改人
            'updated_at' => $not_analyzed, //修改时间
            'checked_by' => $int_analyzed, //审核人
            'checked_at' => $not_analyzed, //审核时间
            'deleted_flag' => $not_analyzed, //删除标志
            /* 扩展内容 */
            'attrs' => $ik_analyzed, //属性
            'attachs' => $ik_analyzed, //附件
            'name_loc' => $ik_analyzed, //中文品名
            'max_exw_day' => $not_analyzed, //出货周期（天）
            'min_exw_day' => $not_analyzed, //出货周期（天）
            'min_pack_unit' => $not_analyzed, //成交单位
            'minimumorderouantity' => $not_analyzed, //最小订货数量
            'specs' => $ik_analyzed, //规格数组 json
            'material_cat_no' => $not_analyzed, //物料编码
            'show_cats' => $ik_analyzed, //展示分类数组 json
            'attrs' => $ik_analyzed, //属性数组 json
            'material_cat' => $ik_analyzed, //物料分类对象 json
            'material_cat_zh' => $ik_analyzed, //物料中文分类对象 json
            'onshelf_flag' => $not_analyzed, //上架状态
            'onshelf_by' => $not_analyzed, //上架人
            'onshelf_at' => $not_analyzed, //上架时间
            'min_pack_unit' => $ik_analyzed, //成交单位
        ];
        return $body;
    }

}
