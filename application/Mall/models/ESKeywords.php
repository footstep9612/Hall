<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ESKeyword
 * @author  zhongyg
 * @date    2018-5-11 12:38:15
 * @version V2.0
 * @desc
 */
class ESKeywordsModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'show_cat_keywords';
    protected $es_index = 'erui_keyword';
    protected $es_type_prefix = 'keyword';

//put your code here

    public function __construct() {
        parent::__construct();
    }

    /* 条件组合
     * @param mix $condition // 搜索条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    private function _getCondition($condition, $lang = 'en') {
        $body = [];

        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }


        ESClient::getQurey($condition, $body, ESClient::TERM, 'id');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'cat_no');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'country_bn');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'market_area_bn');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'status');
        if ($condition['deleted_flag']) {
            ESClient::getQurey($condition, $body, ESClient::TERM, 'deleted_flag');
        } else {
            $body['query']['bool']['must'][] = [ESClient::TERM => ['deleted_flag' => 'N']];
        }
        ESClient::getQurey($condition, $body, ESClient::MATCH, 'cat_name', 'cat_name.' . $analyzer);
        ESClient::getQurey($condition, $body, ESClient::MATCH, 'name', 'name.' . $analyzer);
        if (!empty($condition['name'])) {
            $keyword = trim(strtolower($condition['name']));
            $body['query']['bool']['must'][] = ['bool' => [ESClient::SHOULD => [
                        [ESClient::WILDCARD => ['name.lower' => ['value' => '*' . strtolower($keyword), 'boost' => 99]]],
                        [ESClient::MATCH => ['name.' . $analyzer => ['query' => $keyword, 'minimum_should_match' => '75%', 'operator' => 'or']]],
            ]]];
        }
        return $body;
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @param mix  $_source //要搜索的字段
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getList($condition, $lang = 'en') {

        try {
            if ($lang == 'zh') {
                $analyzer = 'ik';
            } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
                $analyzer = $lang;
            } else {
                $analyzer = 'ik';
            }
            $body = $this->_getCondition($condition, $lang);

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

            if (!$body) {
                $body['query']['bool']['must'][] = ['match_all' => []];
            } elseif (isset($condition['name']) && $condition['name']) {
                $es->setbody($body)->setsort('_score')
                        ->setsort('created_at', 'DESC')
                        ->setsort('id', 'DESC');
                $es->setpreference('_primary_first');
            } else {
                $es->setbody($body)->setsort('created_at', 'DESC')->setsort('id', 'DESC');
            }

            $es->sethighlight(['name.' . $analyzer => new stdClass()]);
            $es->setfields(["id", "lang", "cat_no", "cat_name", "country_bn", "market_area_bn", "name"]);
            $data = [$es->search($this->es_index, $this->es_type_prefix . '_' . $lang, $from, $pagesize), $current_no, $pagesize];
            $es->body = $body = $es = null;
            unset($es, $body);
            return $data;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);

            return [];
        }
    }

}
