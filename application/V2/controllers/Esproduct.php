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

    protected $index = 'erui_goods';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = null;

//put your code here
    public function init() {

        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } else {
            parent::init();
        }
    }

    public function clearCacheAction() {
        $redis = new phpredis();
        $keys = $redis->getKeys('*');
        $config = Yaf_Registry::get("config");
        $rconfig = $config->redis->config->toArray();
        $rconfig['dbname'] = 3;
        $redis3 = new phpredis($rconfig);
        $key3s = $redis3->getKeys('*');
        $delkeys = [];
        foreach ($keys as $key) {
            if (strpos($key, 'user_info_') === false) {
                $delkeys[] = $key;
            }
        }
        $redis->delete($delkeys);
        $delkeys = [];
        foreach ($key3s as $key) {
            if (strpos($key, 'shopmall_user_info') === false) {
                $delkeys[] = $key;
            }
        }
        $redis3->delete($delkeys);

        unset($redis, $redis3, $keys, $delkeys);
        $this->jsonReturn();
    }

    public function SearchAction() {
        $body = $this->getPut('body');
        $action = $this->getPut('action');
        $type = $this->getRequest()->getMethod();
        $server = Yaf_Application::app()->getConfig()->esapi;
        $source_hosts = explode(',', $server);

        $ch = curl_init($source_hosts[0] . '/' . $action);
        echo $source_hosts[0] . '/' . $action, PHP_EOL;
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $source_hosts[0] . '/' . $action);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        switch ($type) {
            case "GET" : curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST": curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
            case "PUT" : curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
            case "DELETE":curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
        }
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            die(curl_error($ch));
        }
        curl_close($ch);
        die($response);
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

        $this->_handleCondition($condition);
        $ret = $model->getProducts($condition, null, $lang);
        if ($ret) {
            $data = $ret[0];
            $list = $this->_getdata($data, $lang);
            $send['count'] = isset($data['hits']['total']) ? intval($data['hits']['total']) : 0;
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($ret[3]) && $ret[3] > 0) {
                $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
            } else {
                $send['allcount'] = $send['count'];
            }
            $send['sku_count'] = $model->getSkuCountByCondition($condition, $lang);
            $send['image_count'] = $model->getImageCountByCondition($condition, $lang);
            if (isset($data['aggregations']['brands']['buckets']) && $data['aggregations']['brands']['buckets']) {
                $send['brand_count'] = count($data['aggregations']['brands']['buckets']);
            } else {
                $send['brand_count'] = 0;
            }
            if (isset($data['aggregations']['suppliers']['buckets']) && $data['aggregations']['suppliers']['buckets']) {
                $send['supplier_count'] = count($data['aggregations']['suppliers']['buckets']);
            } else {
                $send['supplier_count'] = 0;
            }
            if (isset($this->put_data['onshelf_count']) && $this->put_data['onshelf_count'] == 'Y') {
                $condition['onshelf_flag'] = 'N';
                $condition['sku_count'] = 'Y';
                $condition['pagesize'] = 0;
                $ret_N = $model->getProducts($condition, null, $lang);
                $send['onshelf_count_N'] = intval($ret_N[0]['hits']['total']);
//    $send['onshelf_sku_count_N'] =$model->getSkuCountByCondition($condition, $lang);
                $condition['onshelf_flag'] = 'Y';
                $ret_y = $model->getProducts($condition, null, $lang);
                $send['onshelf_count_Y'] = intval($ret_y[0]['hits']['total']);
//  $send['onshelf_sku_count_Y'] = $model->getSkuCountByCondition($condition, $lang);
            }
            $condition['deleted_flag'] = 'Y';
            $condition['onshelf_flag'] = 'A';
            //  $send['deleted_flag_count_Y'] = $model->getCount($condition, $lang);
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
     * 搜索条件处理
     */

    private function _handleCondition(&$condition) {
        switch ($condition['user_type']) {
            case 'create':
                $condition['created_by_name'] = $condition['user_name'];
                break;
            case 'updated':
                $condition['updated_by_name'] = $condition['user_name'];
                break;
            case 'checked':
                $condition['checked_by_name'] = $condition['user_name'];
                break;
        }
        switch ($condition['date_type']) {
            case 'create':
                $condition['created_at_start'] = isset($condition['date_start']) ? trim($condition['date_start']) : null;
                $condition['created_at_end'] = isset($condition['date_end']) ? trim($condition['date_end']) : null;
                break;
            case 'updated':
                $condition['updated_at_start'] = isset($condition['date_start']) ? trim($condition['date_start']) : null;
                $condition['updated_at_end'] = isset($condition['date_end']) ? trim($condition['date_end']) : null;
                break;
            case 'checked':
                $condition['checked_at_start'] = isset($condition['date_start']) ? trim($condition['date_start']) : null;
                $condition['checked_at_end'] = isset($condition['date_end']) ? trim($condition['date_end']) : null;
                break;
        }
    }

    /*
     * 获取回收站列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function recycledAction() {
        $model = new EsProductModel();
        $lang = $this->getPut('lang', 'zh');
        $condition = $this->getPut();

        $this->_handleCondition($condition);
        $condition['deleted_flag'] = 'Y';
        $condition['onshelf_flag'] = 'A';
        $ret = $model->getProducts($condition, null, $lang);
        if ($ret) {
            $data = $ret[0];
            $list = $this->_getdata($data, $lang, true);
            $send['count'] = isset($data['hits']['total']) ? intval($data['hits']['total']) : 0;
            $send['current_no'] = intval($ret[1]);
            $send['pagesize'] = intval($ret[2]);
            if (isset($data['aggregations']['image_count']['value']) && $data['aggregations']['image_count']['value']) {
                $send['image_count'] = $data['aggregations']['image_count']['value'];
            } else {
                $send['image_count'] = 0;
            }
            if (isset($data['aggregations']['brands']['buckets']) && $data['aggregations']['brands']['buckets']) {
                $send['brand_count'] = count($data['aggregations']['brands']['buckets']);
            } else {
                $send['brand_count'] = 0;
            }
            if (isset($data['aggregations']['suppliers']['buckets']) && $data['aggregations']['suppliers']['buckets']) {
                $send['supplier_count'] = count($data['aggregations']['suppliers']['buckets']);
            } else {
                $send['supplier_count'] = 0;
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

    public function deleteAction() {
        $es = new ESClient();
        $index = $this->getPut('index');
        $old_version = $this->getPut('old_version');
        if ($index) {
            $ret = $es->delete_index($index);
        } elseif ($old_version) {
            $ret = $es->delete_index($this->index . '_' . $old_version);
        } else {
            $ret = $es->delete_index($this->index);
        }
        echo json_encode($ret, 256);
        exit;
    }

    public function getSettingsAction() {
        $es = new ESClient();
        if ($this->version) {
            $ret = $es->getSettings($this->index . '_' . $this->version);
        } else {
            $ret = $es->getSettings($this->index);
        }

        echo json_encode($ret, 256);
        exit;
    }

    public function getmappingsAction() {
        $es = new ESClient();
        $type = $this->getPut('type');
        if ($this->version) {
            $ret = $es->getMapping($this->index . '_' . $this->version, $type);
        } else {
            $ret = $es->getMapping($this->index, $type);
        }

        echo json_encode($ret, 256);
        exit;
    }

    public function setmappingsAction() {
        $es = new ESClient();
        $type = $this->getPut('type');
        $mapParam = $this->getPut('mapParam');
        if ($this->version) {
            $ret = $es->putMapping($this->index . '_' . $this->version, $type, $mapParam);
        } else {
            $ret = $es->putMapping($this->index, $type, $mapParam);
        }

        echo json_encode($ret, 256);
        exit;
    }

    public function deleteMappingAction() {
        $es = new ESClient();
        $type = $this->getPut('type');


        $mapParam = array();

        $mapParam['type'] = $type;
        if ($this->version) {
            $mapParam['index'] = $this->index . '_' . $this->version;
            $ret = $es->deleteMapping($mapParam);
        } else {
            $mapParam['index'] = $this->index . '_' . $this->version;
            $ret = $es->deleteMapping($mapParam);
        }

        echo json_encode($ret, 256);
        exit;
    }

    public function setSettingsAction() {
        $es = new ESClient();
        if ($this->version) {
            $ret = $es->getSettings($this->index . '_' . $this->version);
        } else {
            $ret = $es->getSettings($this->index);
        }

        echo json_encode($ret, 256);
        exit;
    }

    public function getStateAction() {
        $es = new ESClient();
        $ret = $es->getstate();
        echo json_encode($ret, 256);
        exit;
    }

    /*
     * 获取节点信息
     */

    public function getnodesAction() {
        $es = new ESClient();
        $ret = $es->getnodesinfo();
        echo json_encode($ret, 256);
        exit;
    }

    /*
     * 新建别名
     */

    public function setAliasAction() {
        $es = new ESClient();
        $index = $this->getPut('index');
        $body = $this->getPut('body');
        $ret = $es->index_alias($index, $body);
        echo json_encode($ret, 256);
        exit;
    }

    /*
     * 新建别名
     */

    public function setAliasesAction() {
        $es = new ESClient();
        $index = $this->getPut('index');
        $name = $this->getPut('name');
        $ret = $es->index_Aliases($index, $name);
        echo json_encode($ret, 256);
        exit;
    }

    /*
     * 删除别名
     */

    public function deleteAliasAction() {
        $index = $this->getPut('index');
        $name = $this->getPut('name');
        return $this->server->indices()->deleteAlias($index, $name);
    }

    public function existsAliasAction() {
        $index = $this->getPut('index');
        $name = $this->getPut('name');
        return $this->server->indices()->existsAlias($index, $name);
    }

    /*
     * 处理ES 数据
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getdata($data = [], $lang = 'en', $is_recycled = false) {

        $user_ids = [];
        $spus = [];
//  $esgoods = new EsGoodsModel();
        foreach ($data['hits']['hits'] as $key => $item) {
            $product = $list[$key] = $item["_source"];
            $attachs = json_decode($item["_source"]['attachs'], true);
            if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
                $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
            } else {
                $list[$key]['img'] = new stdClass();
            }
            $list[$key]['id'] = $item['_id'];

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
//  if ($is_recycled && !empty($product['spu'])) {
            $spus[] = $product['spu'];
//  }
            $list[$key]['specs'] = $list[$key]['attrs']['spec_attrs'];
            $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
        }
        $esgoods = new EsGoodsModel();
        $status_sku_counts = $esgoods->getStatusSkuCountBySpu($spus, $lang);

        foreach ($list as $k => $product) {
            if (isset($status_sku_counts[$product['spu']]) && $status_sku_counts[$product['spu']]) {
                $status_sku_count = $status_sku_counts[$product['spu']];
                $list[$k]['sku_count'] = $product['sku_count'] . PHP_EOL . '(' .
                        ($status_sku_count['draft_count'] > 0 ? '   暂存:' . $status_sku_count['draft_count'] : '') .
                        ($status_sku_count['checking_count'] > 0 ? '   待审核:' . $status_sku_count['checking_count'] : '') .
                        ($status_sku_count['valid_count'] > 0 ? '   通过:' . $status_sku_count['valid_count'] : '') .
                        ($status_sku_count['invalid_count'] > 0 ? '   已驳回:' . $status_sku_count['invalid_count'] : '') . ')';
            }
        }

//        if ($is_recycled && !empty($spus)) {
//            $esgoods_model = new EsGoodsModel();
//            $skucount = $esgoods_model->getskucountBySpus($spus, $lang);
//            $skucounts = [];
//            if (isset($skucount['aggregations']['spu']['buckets']) && $skucount['aggregations']['spu']['buckets']) {
//                foreach ($skucount['aggregations']['spu']['buckets'] as $sku_count) {
//                    $skucounts[$sku_count['key']] = $sku_count['value'];
//                }
//            }
//        }
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
//            if ($is_recycled && isset($skucounts[$val['spu']])) {
//                $val['sku_count'] = intval($skucounts[$val['spu']]);
//            }
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
            $spus = $this->getPut('spus');
            $deleted_flag = $this->getPut('deleted_flag');
            foreach ($this->langs as $lang) {
                $espoductmodel = new EsProductModel();
                $espoductmodel->importproducts($lang, $spus, $deleted_flag);
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
        $body['settings']['analysis']['analyzer']['caseSensitive'] = [
            'filter' => 'lowercase', 'type' => 'custom', 'tokenizer' => 'keyword'
        ];
        foreach ($this->langs as $lang) {
            $product_properties = $this->productAction($lang);
            $goods_properties = $this->goodsAction($lang);
            $body['mappings']['goods_' . $lang]['properties'] = $goods_properties;
            $body['mappings']['goods_' . $lang]['_all'] = ['enabled' => true];
            $body['mappings']['product_' . $lang]['properties'] = $product_properties;
            $body['mappings']['product_' . $lang]['_all'] = ['enabled' => true];
        }
        $es = new ESClient();
        $state = $es->getstate();
        if (!$this->version) {
            if (!isset($state['metadata']['indices'][$this->index])) {
                $es->create_index($this->index, $body, 5, 1);
            }
        } else {
            if (!isset($state['metadata']['indices'][$this->index . '_' . $this->version])) {
                $es->create_index($this->index . '_' . $this->version, $body, 5, 1);
            }
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
        $body = [];
        $body['mappings'] = [];
        $product_properties = $this->productAction('en');
        $goods_properties = $this->goodsAction('en');
        $es = new ESClient();
        if ($this->version) {
            $es->close($this->index . '_' . $this->version);
            foreach ($this->langs as $lang) {
                $goods_mapParam = ['goods_' . $lang => [
                        'properties' => $goods_properties,
                        '_all' => ['enabled' => true]
                ]];
                $product_mapParam = ['product_' . $lang => [
                        'properties' => $product_properties,
                        '_all' => ['enabled' => true]
                ]];
                logs(json_encode($product_mapParam));
//logs(json_encode($goods_mapParam));
                $flag = $es->putMapping($this->index . '_' . $this->version, 'goods_' . $lang, $goods_mapParam);

                $flag = $es->putMapping($this->index . '_' . $this->version, 'product_' . $lang, $product_mapParam);
            }
            $es->open($this->index . '_' . $this->version);
        } else {
            $es->close($this->index);
            foreach ($this->langs as $lang) {
                $goods_mapParam = ['goods_' . $lang => [
                        'properties' => $goods_properties,
                        '_all' => ['enabled' => true]
                ]];
                $product_mapParam = ['product_' . $lang => [
                        'properties' => $product_properties,
                        '_all' => ['enabled' => true]
                ]];
                logs(json_encode($product_mapParam));
//logs(json_encode($goods_mapParam));
                $flag = $es->putMapping($this->index, 'goods_' . $lang, $goods_mapParam);

                $flag = $es->putMapping($this->index, 'product_' . $lang, $product_mapParam);
            }
            $es->open($this->index . '_' . $this->version);
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
    public function goodsAction($lang) {


        $int_analyzed = ['type' => 'integer',];
        $type = 'string';
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
                'lower' => [
                    'analyzer' => 'caseSensitive',
                    'search_analyzer' => 'caseSensitive',
                    'type' => $type
                ],
                'standard' => [
                    'analyzer' => 'standard',
                    'type' => $type
                ],
                'ik' => [
                    'analyzer' => 'ik',
                    'type' => $type
                ],
                'en' => [
                    'analyzer' => 'english',
                    'type' => $type
                ],
                'es' => [
                    'analyzer' => 'spanish',
                    'type' => $type
                ],
                'ru' => [
                    'analyzer' => 'russian',
                    'type' => $type
                ],
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
            'bizline_id' => $not_analyzed, //产品线ID
            'bizline' => [
                'properties' => [
                    'name' => $ik_analyzed,
                    'name_en' => $ik_analyzed,
                    'id' => $not_analyzed,
                ],
            ],
            'costprices' => [
                'properties' => [
                    'supplier_id' => $not_analyzed,
                    'contact_first_name' => $not_analyzed,
                    'contact_last_name' => $not_analyzed,
                    'price' => $not_analyzed,
                    'max_price' => $not_analyzed,
                    'price_unit' => $not_analyzed,
                    'price_cur_bn' => $not_analyzed,
                    'min_purchase_qty' => $not_analyzed,
                    'max_purchase_qty' => $not_analyzed,
                    'pricing_date' => $not_analyzed,
                    'price_validity' => $not_analyzed,
                ],
            ],
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
// 'brand' => $ik_analyzed, //品牌
// 'suppliers' => $ik_analyzed, //供应商数组 json
            'brand' => [
                'properties' => [
                    'lang' => $not_analyzed,
                    'name' => $ik_analyzed,
                    'id' => $not_analyzed,
                    'logo' => $not_analyzed,
                    'manufacturer' => $not_analyzed,
                    'style' => $not_analyzed,
                    'label' => $not_analyzed,
                ],
            ],
            'suppliers' => [
                'properties' => [
                    'supplier_id' => $not_analyzed,
                    'supplier_name' => $ik_analyzed,
                    'brand' => ['type' => $type,],
                    'pn' => $ik_analyzed,
                ],
            ],
            'supplier_count' => $not_analyzed,
            'image_count' => $int_analyzed,
            //   'specs' => $ik_analyzed, //规格数组 json
            'material_cat_no' => $not_analyzed, //物料编码
            'show_cats' => [
                'properties' => [
                    'cat_no1' => $not_analyzed,
                    'cat_no2' => $not_analyzed,
                    'cat_no3' => $not_analyzed,
                    'cat_name1' => $ik_analyzed,
                    'cat_name2' => $ik_analyzed,
                    'cat_name3' => $ik_analyzed,
                    'market_area_bn' => $not_analyzed,
                    'country_bn' => $not_analyzed,
                    'onshelf_flag' => $not_analyzed,
                ]], //展示分类数组 json
            'attrs' => [
                'properties' => [//属性数组 json
                    'spec_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]],
                    'ex_goods_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]],
                    'ex_hs_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]],
                    'other_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]]
                ]],
            'material_cat' => [
                'properties' => [
                    'cat_no1' => $not_analyzed,
                    'cat_no2' => $not_analyzed,
                    'cat_no3' => $not_analyzed,
                    'cat_name1' => $ik_analyzed,
                    'cat_name2' => $ik_analyzed,
                    'cat_name3' => $ik_analyzed,
                ]], // $ik_analyzed, //物料分类对象 json
            'material_cat_zh' => [
                'properties' => [
                    'cat_no1' => $not_analyzed,
                    'cat_no2' => $not_analyzed,
                    'cat_no3' => $not_analyzed,
                    'cat_name1' => $ik_analyzed,
                    'cat_name2' => $ik_analyzed,
                    'cat_name3' => $ik_analyzed,
                ]], //物料中文分类对象 json
            'spec_attrs' => [
                'type' => 'nested',
                'include_in_parent' => true,
                'properties' => [
                    'name' => $ik_analyzed,
                    'value' => $ik_analyzed,
                ]],
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


        $type = 'string';
        $int_analyzed = ['type' => 'integer',];
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
                'lower' => [
                    'analyzer' => 'caseSensitive',
                    'search_analyzer' => 'caseSensitive',
                    'type' => $type
                ],
                'standard' => [
                    'analyzer' => 'standard',
                    'type' => $type
                ],
                'ik' => [
                    'analyzer' => 'ik',
                    'type' => $type
                ],
                'en' => [
                    'analyzer' => 'english',
                    'type' => $type
                ],
                'es' => [
                    'analyzer' => 'spanish',
                    'type' => $type
                ],
                'ru' => [
                    'analyzer' => 'russian',
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
//'brand' => $ik_analyzed, //品牌
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
            'sku_count_notvalid' => $int_analyzed, //SKU未审核的SKU数
            'view_count' => ['type' => $type], //浏览数量
            'bizline_id' => $not_analyzed, //产品线ID
            'bizline' => [
                'properties' => [
                    'name' => $ik_analyzed,
                    'name_en' => $ik_analyzed,
                    'id' => $not_analyzed,
                ],
            ],
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
//  'specs' => $ik_analyzed, //规格数组 json
            'material_cat_no' => $not_analyzed, //物料编码
            'show_cats' => [
                'properties' => [
                    'cat_no1' => $not_analyzed,
                    'cat_no2' => $not_analyzed,
                    'cat_no3' => $not_analyzed,
                    'cat_name1' => $ik_analyzed,
                    'cat_name2' => $ik_analyzed,
                    'cat_name3' => $ik_analyzed,
                    'market_area_bn' => $not_analyzed,
                    'country_bn' => $not_analyzed,
                    'onshelf_flag' => $not_analyzed,
                ]], //展示分类数组 json
            'specials' => [
                'include_in_parent' => true,
                'type' => 'nested',
                'properties' => [
                    'special_id' => $not_analyzed,
                    'keyword_id' => $not_analyzed,
                    'country_bn' => $not_analyzed,
                    'special_name' => $ik_analyzed,
                    'keyword' => $ik_analyzed,
                    'keyword_id' => $not_analyzed
                ]], //展示分类数组 json
            'show_cats_nested' => [
                'type' => 'nested',
                'include_in_parent' => true,
                'properties' => [
                    'cat_no1' => $not_analyzed,
                    'cat_no2' => $not_analyzed,
                    'cat_no3' => $not_analyzed,
                    'cat_name1' => $ik_analyzed,
                    'cat_name2' => $ik_analyzed,
                    'cat_name3' => $ik_analyzed,
                    'market_area_bn' => $not_analyzed,
                    'country_bn' => $not_analyzed,
                    'onshelf_flag' => $not_analyzed,
                ]], //展示分类数组 json
            'attrs' => [
                'properties' => [
                    'spec_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]],
                    'ex_goods_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]],
                    'ex_hs_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]],
                    'other_attrs' => [
                        'properties' => [
                            'name' => $ik_analyzed,
                            'value' => $ik_analyzed,
                        ]]
                ]],
            'brand' => [
                'properties' => [
                    'lang' => $not_analyzed,
                    'name' => $ik_analyzed,
                    'id' => $not_analyzed,
                    'logo' => $not_analyzed,
                    'manufacturer' => $not_analyzed,
                    'style' => $not_analyzed,
                    'label' => $not_analyzed,
                ],
            ],
            'suppliers' => [
                'properties' => [
                    'supplier_id' => $not_analyzed,
                    'supplier_name' => $ik_analyzed,
                    'pn' => $ik_analyzed,
                ],
            ],
            'spec_attrs' => [
                'type' => 'nested',
                'include_in_parent' => true,
                'properties' => [
                    'name' => $ik_analyzed,
                    'value' => $ik_analyzed,
                ]],
            'supplier_count' => $not_analyzed,
            'image_count' => $int_analyzed,
            'relation_flag' => $not_analyzed,
            'material_cat' => [
                'properties' => [
                    'cat_no1' => $not_analyzed,
                    'cat_no2' => $not_analyzed,
                    'cat_no3' => $not_analyzed,
                    'cat_name1' => $ik_analyzed,
                    'cat_name2' => $ik_analyzed,
                    'cat_name3' => $ik_analyzed,
                ]], // $ik_analyzed, //物料分类对象 json
            'material_cat_zh' => [
                'properties' => [
                    'cat_no1' => $not_analyzed,
                    'cat_no2' => $not_analyzed,
                    'cat_no3' => $not_analyzed,
                    'cat_name1' => $ik_analyzed,
                    'cat_name2' => $ik_analyzed,
                    'cat_name3' => $ik_analyzed,
                ]], //物料中文分类对象 json
            'onshelf_flag' => $not_analyzed, //上架状态
            'onshelf_by' => $not_analyzed, //上架人
            'onshelf_at' => $not_analyzed, //上架时间
            'min_pack_unit' => $ik_analyzed, //成交单位
        ];
        return $body;
    }

    /**
     * 产品导出
     */
    public function exportAction() {
        $esproduct_model = new EsProductModel();
        $condition = $this->getPut();
        $process = $this->getPut('process', '');
        $lang = $this->getPut('lang');
        $this->_handleCondition($condition);
        if (empty($lang)) {
            jsonReturn('', MSG::ERROR_PARAM, '请选择语言!');
        }
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        $localDir = $esproduct_model->export($condition, $process, $lang);

        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
