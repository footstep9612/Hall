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
class EsGoodsModel extends Model {

    //put your code here
    protected $tableName = 'goods';
    protected $dbName = 'erui_goods'; //数据库名称
    protected $update_dbName = 'erui_goods'; //数据库名称

    const STATUS_DELETED = 'DELETED';

    public function __construct() {
        parent::__construct();
    }

    /* 条件组合
     * @author  zhongyg
     * @param mix $condition // 搜索条件
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    private function getCondition($condition, $lang = 'en') {
        $body = [];
        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }


        ESClient::getQurey($condition, $body, ESClient::TERM, 'sku');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'spu');
        if (isset($condition['skus']) && $condition['skus']) {
            $name_arr = $condition['skus'];
            $body['query']['bool']['must'][] = [ESClient::TERMS => ['sku' => $name_arr]];
        }
        if (isset($condition['spus']) && $condition['spus']) {
            $name_arr = $condition['spus'];
            $body['query']['bool']['must'][] = [ESClient::TERMS => ['spu' => $name_arr]];
        }
        ESClient::getQurey($condition, $body, ESClient::TERM, 'market_area_bn', 'show_cats.market_area_bn');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'country_bn', 'show_cats.country_bn');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'scat_no1', 'show_cats.cat_no1');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'scat_no2', 'show_cats.cat_no2');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'scat_no3', 'show_cats.cat_no3');
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'name', 'name.all');
        ESClient::getQurey($condition, $body, ESClient::MATCH, 'show_name', 'show_name.' . $analyzer);
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'real_name', 'name.all');
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'brand', 'brand.name.all');
        ESClient::getStatus($condition, $body, ESClient::TERM, 'status', 'status', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'CLOSED',
            'DELETED', 'DRAFT', 'INVALID']);
        ESClient::getQurey($condition, $body, ESClient::MATCH_PHRASE, 'model', 'model');
        $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        $body['query']['bool']['must'][] = [ESClient::TERM => ['onshelf_flag' => 'Y']];
        return $body;
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getgoods($condition, $_source = null, $lang = 'en') {
        try {


            $body = $this->getCondition($condition, $lang);

            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            }
            $pagesize = 10;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }

            $from = ($current_no - 1) * $pagesize;
            $es = new ESClient();

            $es->setbody($body);
            if (isset($condition['keyword']) && $condition['keyword']) {
                $es->setsort('_score', 'desc')->setsort('created_at', 'desc')->setsort('sku', 'desc');
                $es->setpreference('_primary_first');
            } else {
                $es->setsort('created_at', 'desc')
                        ->setsort('sku', 'desc');
            }


            return [$es->search($this->dbName, $this->tableName . '_' . $lang, $from, $pagesize), $current_no, $pagesize];
        } catch (Exception $ex) {
//            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
//            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
