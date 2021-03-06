<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once MYPATH . "/vendor/autoload.php";

use Elasticsearch\ClientBuilder;

/**
 * Description of ESClient
 *
 * @author zyg
 */
class ESClient {
    /* match 模糊查询
     * 上面的查询匹配就会进行分词，比如"宝马多少马力"会被分词为"宝马 多少 马力",
     * 所有有关"宝马 多少 马力", 那么所有包含这三个词中的一个或多个的文档就会被搜索出来。
     * 并且根据lucene的评分机制(TF/IDF)来进行评分
     */

    const MATCH = 'match';
    /*
     * term是代表完全匹配，即不进行分词器分析，文档中必须包含整个搜索的词汇
     */
    const TERM = 'term';
    const TERMS = 'terms';
    const QUERY_STRING = 'query_string';
    const DEFAULT_FIELD = 'default_field';
    const QUERY = 'query';
    /*
     * 正则匹配
     * 使用regexp查询能够让你写下更复杂的模式
     */
    const REGEXP = 'regexp'; //term : 相当于正则式查询
    /* wildcard查询和prefix查询类似，也是一个基于词条的低级别查询。
     * 但是它能够让你指定一个模式(Pattern)，而不是一个前缀(Prefix)。
     * 它使用标准的shell通配符：?用来匹配任意字符，*用来匹配零个或者多个字符
     */
    const WILDCARD = 'wildcard';
    const PREFIX = 'prefix'; //prefix前缀匹配
    const RANGE = 'range'; //区间查询
    /*
     * 查询文档中不存在某字段(missing account.userId)的nested
     * 查询，简单意义上，你可以理解为，它不会被索引，只是被暂时隐藏起来，而查询的时候，
     * 开关就是使用nested query/filter去查询
     */
    const MISSING = 'missing';
    /* 如果我们希望两个字段进行匹配，其中一个字段有这个文档就满足的话，使用multi_match
     * 我们希望完全匹配的文档占的评分比较高，则需要使用best_fields
     * 我们希望越多字段匹配的文档评分越高，就要使用most_fields
     * 我们会希望这个词条的分词词汇是分配到不同字段中的，那么就使用cross_fields
     * {
     * "query": {
     *     "multi_match": {
     *       "query": "我的宝马发动机多少",
     *       "type": "most_fields", //type  most_fields cross_fields best_fields
     *       "fields": [
     *         "tag",
     *          "content"
     *           ]
     *     }
     *      }
     * }
     */
    const MULTI_MATCH = 'multi_match';
    /* 完全匹配 match_phrase
     * 完全匹配可能比较严，我们会希望有个可调节因子，
     * 少匹配一个也满足，那就需要使用到slop。
     * 类似
     * { "query": {
     * "match_phrase": {
     * "content" : {
     * "query" : "我的宝马多少马力",
     * "slop" : 1
     * }}}}
     */
    const MATCH_PHRASE = 'match_phrase';
    const NESTED = 'nested';


    /*
     * 主要根据fuzziniess和prefix_length进行匹配distance查询。
     * 根据type不同distance计算不一样。numeric类型的distance类似于区间，
     * string类型则依据Levenshtein distance，即从一个stringA变换到另一个stringB，
     * 需要变换的最小字母数。如果指定为AUTO，则根据term的length有以下规则：0-1：完全一致
     * 1-4：1
     * >4：2
     * 推荐指定prefix_length，表明这个范围的字符需要精准匹配，
     * 如果不指定prefix_lengh和fuzziniess参数，该查询负担较重。
     */
    const FUZZY = 'fuzzy';
    /*
     *  文档必须完全匹配条件
     */
    const MUST = 'must';
    /*
     * 文档必须不匹配条件
     */
    const MUST_NOT = 'must_not';
    /*
     * should下面会带一个以上的条件，至少满足一个条件，这个文档就符合should
     */
    const SHOULD = 'should';

//put your code here
    // "client", "custom", "filter_path", "human", "master_timeout", "timeout", "update_all_types", "wait_for_active_shards"

    private $server = '';
    private $_preference = null;
    public $body = [];
    private $regexp = []; //正则式查询
    private $wildcard = []; //模糊查询
    private $prefix = []; //前缀查询

    /*
     * $source_hosts = [
     *     '192.168.1.1:9200',         // IP + Port
     *     '192.168.1.2',              // Just IP
     *     'mydomain.server.com:9201', // Domain + Port
     *     'mydomain2.server.com',     // Just Domain
     *     'https://localhost',        // SSL to localhost
      'https://192.168.1.3:9200'  // SSL to IP + Port
     * ];
     * $hosts = [
     *     'http://user:pass@localhost:9200',       // HTTP Basic Authentication
     *     'http://user2:pass2@other-host.com:9200' // Different credentials on different host
     * ];$hosts = ['https://user:pass@localhost:9200'];
     * $myCert = 'path/to/cacert.pem';
     * $client = ClientBuilder::create()
     *                     ->setHosts($hosts)
     *                     ->setSSLVerification($myCert)
     *                     ->build();
     * 连接池
     * 请查看 https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_connection_pool.html
     */

    public function __construct() {
        $server = Yaf_Application::app()->getConfig()->esapi;
        $source_hosts = explode(',', $server);


        $this->server = ClientBuilder::create()
                        ->setHosts($source_hosts)->build();
    }

    /*     * ********************************---索引操作---***************************************
     * createindices
     * 创建索引
     * @access public
     * @param string $index 索引名称
     * @param mix $body 资源定义
     * @param int $number_of_replicas 从分片数量
     * @since 1.0
     * @return array     *
     */

    public function create_index($index, $body, $number_of_shards = 5, $number_of_replicas = 1) {
        $indexParams['index'] = $index;
        // $indexParams['type'] = $type;
        $indexParams['body'] = $body;
        $indexParams['body']['settings']['number_of_shards'] = $number_of_shards;
        $indexParams['body']['settings']['number_of_replicas'] = $number_of_replicas;
        return $this->server->indices()->create($indexParams);
    }

    /*     * ********************************---索引别名---***************************************
     * createindices
     * 创建索引
     * @access public
     * @param string $index 索引名称
     * @param mix $body 资源定义
     * @since 1.0
     * @return array     *
     */

    public function index_aliases($index, $body) {
        $indexParams['index'] = $index;
        $indexParams['body'] = $body;
        return $this->server->indices()->updateAliases($indexParams);
    }

    public function index_alias($index, $name) {
        $indexParams['index'] = $index;
        $indexParams['name'] = $name;
        return $this->server->indices()->putAlias($indexParams);
    }

    public function index_deleteAlias($index, $name) {
        $indexParams['index'] = $index;
        $indexParams['name'] = $name;
        return $this->server->indices()->deleteAlias($indexParams);
    }

    public function index_existsAlias($index, $name) {
        $indexParams['index'] = $index;
        $indexParams['name'] = $name;
        return $this->server->indices()->existsAlias($indexParams);
    }

    /*
     * 获取版本号
     */

    public function getversion() {
        return $this->server->info();
    }

    public function getstate() {
        return $this->server->cluster()->state();
    }

    public function getstats() {
        return $this->server->cluster()->stats();
    }

    public function gethealth() {
        return $this->server->cluster()->health();
    }

    public function getnodesinfo() {
        return $this->server->nodes()->info();
    }

    public function getnodesstats() {
        return $this->server->nodes()->stats();
    }

    public function getnodeshealth() {
        return $this->server->nodes()->health();
    }

    /*
     * 删除索引
     */

    public function delete_index($index) {
        $deleteParams['index'] = $index;
        try {
            $ret = $this->server->indices()->delete($deleteParams);

            return $ret;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 关闭索引
     */

    public function close($index) {
        $params['index'] = $index;
        try {
            $ret = $this->server->indices()->close($params);
            return $ret;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 开启索引
     */

    public function open($index) {
        $params['index'] = $index;
        try {
            $this->server->indices()->open($params);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
        }
    }

    /*
     * 开启索引
     */

    public function refresh($index) {
        $params['index'] = $index;
        try {
            $this->server->indices()->refresh($params);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
        }
    }

    /*
     * 查询
     *
     */

    public function analyze($index, $type, $analyzer = '', $from = 0, $size = 100) {
        $searchParams = array(
            'index' => $index,
            'type' => $type,
            'body' => $this->body,
        );
        if ($analyzer) {
            $searchParams ['analyzer'] = $analyzer;
        }

        $searchParams['from'] = $from;
        $searchParams['size'] = $size;

        try {
            return $this->server->indices()->analyze($searchParams);
        } catch (Exception $ex) {

            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
        //   var_dump($retDoc);
    }

    /*
     * 删除类型
     */

    public function delete_type($index) {
        $deleteParams['index'] = $index;
        try {
            $ret = $this->server->delete($deleteParams);

            return $ret;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /* 更新索引 设置 *
     *
     *
     */

    public function putSettings($index, $Settings) {

        $SettingsParam = ['index' => $index, 'body' => $Settings];
        try {
            $response = $this->server->indices()->putSettings($SettingsParam);
            return $response;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 获取索引设置
     */

    public function getSettings($index) {

        $SettingsParam = ['index' => $index];
        try {
            $response = $this->server->indices()->getSettings($SettingsParam);
            return $response;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*     * *********************************文档操作***************************************************
     * 增加文档
     */

    public function add_document($index, $type, $body, $id) {
        $params = array();
        $params['body'] = $body; /* array(
          'testField' => 'dfdsfdsf',
          'ok' => '1    '
          ); */

        $params['index'] = $index;
        $params['type'] = $type;
        $params['id'] = $id;
        try {
            $ret = $this->server->index($params);
            return $ret;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 批量创建文档
     */

    public function bulk($params) {

        $responses = $this->server->bulk($params);
        return $responses;
    }

    /*
     * 部分方法调用
     */

    public function __call($name, $arguments) {

        $Whitelist = $this->getParamWhitelist();
        if (in_array($name, $Whitelist)) {
            return $this->server->$name($arguments);
        } else {
            throw $name . " NOT ALLOWN!";
        }
    }

    /**
     * @return string[]
     */
    public function getParamWhitelist() {
        return [
            'reindex',
            'suggest',
            'explain',
            'searchShards',
            'searchTemplate',
            'scroll',
            'clearScroll',
            'getScript',
            'deleteScript',
            'putScript',
            'getTemplate',
            'deleteTemplate',
            'putTemplate',
            'fieldStats',
            'renderSearchTemplate',
            'cluster',
            'indices',
            'nodes',
            'snapshot',
            'cat',
            'ingest',
            'tasks',
            'extractArgument',
            'info',
            'ping'
        ];
    }

    /*
     * 获取文档
     */

    public function get_document($index, $type, $id, $_source = null) {
        $getParams = array();
        $getParams['index'] = $index;
        $getParams['type'] = $type;
        $getParams['id'] = $id;
        if ($_source) {
            $getParams['_source'] = $_source;
        }
        try {
            $retDoc = $this->server->get($getParams);
            return $retDoc;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 批量获取文档
     * 暂定获取同一索引 同一类型下的文档
     */

    public function mget_documents($index, $type, $body = []) {
        $getParams = [];


        $getParams['index'] = $index;
        $getParams['type'] = $type;

        $getParams['body'] = $body;

        try {
            $retDoc = $this->server->mget($getParams);
            return $retDoc;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 更改文档
     */

    public function update_document($index, $type, $body, $id, $doc_as_upsert = false) {
        $updateParams = array();
        $updateParams['index'] = $index;
        $updateParams['type'] = $type;
        $updateParams['id'] = $id;
        $updateParams['body']['doc'] = $body; //['doc']['testField'] = 'xxxx';
        $updateParams['body']['doc_as_upsert'] = $doc_as_upsert;
        try {
            return $this->server->update($updateParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 更改文档
     */

    public function UpdateByQuery($index, $type, $body) {


        try {
            $count = $this->setbody(['query' => $body['query']])->count($index, $type);
            for ($i = 0; $i < $count['count']; $i += 100) {
                $ret = $this->setbody(['query' => $body['query']])->search($index, $type, $i, 100);
                $updateParams = array();
                $updateParams['index'] = $index;
                $updateParams['type'] = $type;
                if ($ret) {
                    foreach ($ret['hits']['hits'] as $item) {
                        $updateParams['body'][] = ['update' => ['_id' => $item['_id']]];
                        $updateParams['body'][] = ['doc' => $body['doc']];
                    }

                    $this->bulk($updateParams);
                }
            }
            return true;
        } catch (Exception $ex) {

            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 删除文档
     */

    public function delete_document($index, $type, $id) {
        $deleteParams = array();
        $deleteParams['index'] = $index;
        $deleteParams['type'] = $type;
        $deleteParams['id'] = $id;
        try {
            return $this->server->delete($deleteParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 批量删除 查询 删除
     */

    public function deleteByQuery($index, $type, $body) {
        $deleteParams = array();
        $deleteParams['index'] = $index;
        $deleteParams['type'] = $type;
        $deleteParams['body'] = $body;
        try {
            return $this->server->deleteByQuery($deleteParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 模糊查询
     */

    public function search_($match = []) {
        $searchParams = array();
        $searchParams['body'] = array(
            'query' => array(
                'match' => $match
            )
        );

        try {
            $retDoc = $this->server->search($searchParams);
            return $retDoc;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 数据处理
     */

    public function setdata($match, $boost = 0) {
        if (!$boost) {
            return $match;
        } else {

            $keys = array_keys($match);
            if (is_string($match[$keys[0]])) {
                return [
                    $keys[0] => [
                        "value" => $match[$keys[0]],
                        "boost" => $boost
                    ]
                ];
            } else {
                $child = $match[$keys[0]];
                $child['boost'] = $boost;
                return [
                    $keys[0] => $child
                ];
            }
        }
    }

    /* 如果我们希望两个字段进行匹配，其中一个字段有这个文档就满足的话，使用multi_match
     * 我们希望完全匹配的文档占的评分比较高，则需要使用best_fields
     * 我们希望越多字段匹配的文档评分越高，就要使用most_fields
     * 我们会希望这个词条的分词词汇是分配到不同字段中的，那么就使用cross_fields
     * {
     * "query": {
     *     "multi_match": {
     *       "query": "我的宝马发动机多少",
     *       "type": "most_fields", //type  most_fields cross_fields best_fields
     *       "fields": [
     *         "tag",
     *          "content"
     *           ]
     *     }
     *      }
     * }
     */

    public function setmulti_match($query = '', $type = 'best_fields', $fields = []) {

        if (!in_array($type, ['most_fields',
                    'cross_fields',
                    'best_fields',
                ])) {

            $type = 'best_fields';
        }
        $this->body['query'] = [
            'multi_match' =>
            [
                "query" => $query,
                "type" => $type,
                "fields" => $fields
            ]
        ];
        return $this;
    }

    /* 完全匹配 match_phrase
     * 完全匹配可能比较严，我们会希望有个可调节因子，
     * 少匹配一个也满足，那就需要使用到slop。
     * 类似
     * { "query": {
     * "match_phrase": {
     * "content" : {
     * "query" : "我的宝马多少马力",
     * "slop" : 1
     * }}}}
     */

    public function setmatch_phrase($query, $slop = 0, $field = null) {
        $this->body['query']['match_phrase'][$field] = [
            "query" => $query,
            "slop" => $slop,
        ];
        return $this;
    }

    /*
     * must : 多个查询条件的完全匹配,相当于 and。
     * $bost 权重
     */

    public function setmust($must, $type = self::MATCH, $bost = 0, $type1 = NULL, $data = []) {



        if (!in_array($type, [self::MATCH,
                    self::PREFIX,
                    self::REGEXP,
                    self::FUZZY,
                    self::MISSING,
                    self::WILDCARD,
                    self::TERM,
                    self::RANGE
                ])) {

            $type = self::MATCH;
        }
        if (in_array($type1, [self::SHOULD,
                    self::MUST,
                    self::MUST_NOT])) {
            $val = $this->setdata($must, $bost);
            $this->body['query']['bool']['must'] [] = [$type => $val, $type1 => $data,];
            return $this;
        } else {
            $val = $this->setdata($must, $bost);
            $this->body['query']['bool']['must'] [] = [$type => $val];
            return $this;
        }
    }

    /*
     * must_not : 多个查询条件的相反匹配，相当于 not。
     */

    public function setdefault($match, $type = self::MATCH, $bost = 0) {

        $val = $this->setdata($match, $bost);
        if (!in_array($type, [self::MATCH,
                    self::PREFIX,
                    self::REGEXP,
                    self::FUZZY,
                    self::MISSING,
                    self::WILDCARD,
                    self::TERM,
                    self::RANGE
                ])) {

            $type = self::MATCH;
        }

        $this->body['query'][$type] = $val;

        return $this;
    }

    /*
     * must_not : 多个查询条件的相反匹配，相当于 not。
     */

    public function setmust_not($must_not, $type = self::MATCH, $bost = 0, $type1 = NULL, $data = []) {

        if (!in_array($type, [self::MATCH,
                    self::PREFIX,
                    self::REGEXP,
                    self::FUZZY,
                    self::MISSING,
                    self::WILDCARD,
                    self::TERM,
                    self::RANGE,
                    self::SHOULD,
                    self::MUST,
                    self::MUST_NOT
                ])) {

            $type = self::MATCH;
        }
        if (in_array($type1, [self::SHOULD,
                    self::MUST,
                    self::MUST_NOT])) {
            $val = $this->setdata($must_not, $bost);

            $this->body['query']['bool']['must_not'] [$type] = [$type => $val, $type1 => $data,];
            return $this;
        } else {
            $val = $this->setdata($must_not, $bost);
            $this->body['query']['bool']['must_not'] [] = [$type => $val];
            return $this;
        }
    }

    /*
     * should : 至少有一个查询条件匹配, 相当于 or。
     */

    public function setshould($should, $type = self::MATCH, $bost = 0, $type1 = NULL, $data = []) {

        if (!in_array($type, [self::MATCH,
                    self::PREFIX,
                    self::REGEXP,
                    self::FUZZY,
                    self::MISSING,
                    self::WILDCARD,
                    self::TERM,
                    self::RANGE,
                    self::SHOULD,
                    self::MUST,
                    self::MUST_NOT
                ])) {

            $type = self::MATCH;
        }
        if (in_array($type1, [self::SHOULD,
                    self::MUST,
                    self::MUST_NOT])) {
            $val = $this->setdata($should, $bost);
            $this->body['query']['bool']['should'] [] = [$type => $val, $type1 => $data,];

            return $this;
        } else {
            $val = $this->setdata($should, $bost);
            $this->body['query']['bool']['should'] [] = [$type => $val];
            return $this;
        }
    }

    public function popmust_not() {
        $must_not = $this->body['query']['bool']['must_not'];
        unset($this->body['query']['bool']['must_not']);
        return $must_not;
    }

    public function popmust() {
        $must = $this->body['query']['bool']['must'];
        unset($this->body['query']['bool']['must']);
        return $must;
    }

    public function popfilter() {
        $must = $this->body['query']['bool']['filter'];
        unset($this->body['query']['bool']['filter']);
        return $must;
    }

    public function popshouold() {
        $must = $this->body['query']['bool']['should'];
        unset($this->body['query']['bool']['should']);
        return $must;
    }

    public function setpreference($preference) {
        $this->_preference = $preference;
    }

    /*
     * filter : 过滤。
     */

    public function setfilter($filter, $type = self::MATCH, $bost = 0, $type1 = NULL, $data = []) {
        $val = $this->setdata($filter, $bost);
        if (!in_array($type, [self::MATCH,
                    self::PREFIX,
                    self::REGEXP,
                    self::FUZZY,
                    self::MISSING,
                    self::WILDCARD,
                    self::TERM,
                    self::RANGE,
                    self::SHOULD,
                    self::MUST,
                    self::MUST_NOT
                ])) {

            $type = self::MATCH;
        }
        if (in_array($type, [self::SHOULD,
                    self::MUST,
                    self::MUST_NOT])) {

            $val = $this->setdata($type, $bost);
            $this->body['query']['bool']['filter'] [] = [$type => $val, $type1 => $data,];
            return $this;
        } else {
            $val = $this->setdata($type, $bost);
            $this->body['query']['bool']['filter'] [] = [$type => $val];
            return $this;
        }
    }

    /*
     * 配置高亮显示
     *  $params['body'] = array(
     *     'query' => array(
     *         'match' => array(
     *             'content' => 'quick brown fox'
     *         )
     *     ),
     *     'highlight' => array(
     *         'fields' => array(
     *             'content' => new \stdClass()
     *         )
     *     )
     * );
     */

    public function sethighlight($fields) {

        $this->body['highlight']['require_field_match'] = true;
        $this->body['highlight']['fields'] = $fields;
        return $this;
    }

    /*
     * 配置高亮显示
     * $sort desc asc
     * $field 需要拍下的字段
     */

    public function setsort($field, $sort = null) {
        if (is_string($sort)) {
            $this->body['sort'][] = [$field => ['order' => $sort]];
        } elseif (is_array($sort)) {
            $this->body['sort'][] = [$field => $sort];
        } elseif (empty($sort)) {
            $this->body['sort'][] = $field;
        }
        return $this;
    }

    /* 聚合查询 类似group by
     *  @param string $field // 字段属性
     *  @param string $do// 指标(Metrics) terms 总条数, stats 统计 avg 平均 min 最小，mean，max 最大以及sum 合计
     *
     *  @param string $alis // 别名
     */

    public function setaggs($field, $alis, $do = 'terms', $size = null) {
        if ($size !== null) {

            $this->body['aggs'][$alis] = [$do => ['field' => $field,
                    'size' => $size
            ]];
        } else {
            $this->body['aggs'][$alis] = [$do => ['field' => $field
            ]];
        }

        return $this;
    }

    /*
     * 查询的字段
     */

    public function setfields($fields = []) {
        $this->body['_source'] = $fields;
        return $this;
    }

    public function setbody($body = []) {
        $this->body = $body;
        return $this;
    }

    /*
     * 查询
     *
     */

    public function search_nosize($index, $type) {


        $this->body['size'] = 1;
        $searchParams = array(
            'index' => $index,
            'type' => $type,
            'body' => $this->body,
        );
        try {
            return $this->server->search($searchParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
        //   var_dump($retDoc);
    }

    /*
     * 查询
     *
     */

    public function search($index, $type, $from = 0, $size = 10) {
        $searchParams = array(
            'index' => $index,
            'type' => $type,
            'body' => $this->body,
        );
        if ($from >= 0 && $size > 0) {
            $searchParams['body']['from'] = $from;
            $searchParams['body']['size'] = $size;
        } elseif ($size > 0) {
            $searchParams['body']['size'] = $size;
        }
        if ($this->_preference) {
            $searchParams['preference'] = $this->_preference;
        }
        try {

            return $this->server->search($searchParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
        //   var_dump($retDoc);
    }

    public function count($index, $type, $analyzer = '') {
        $searchParams = array(
            'index' => $index,
            'type' => $type,
            'body' => $this->body,
        );
        if ($analyzer) {
            $searchParams ['analyzer'] = $analyzer;
        }
        try {
            return $this->server->count($searchParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return ['count' => 0];
        }
        //   var_dump($retDoc);
    }

    /*
     * 检查文档是否存在
     */

    public function exists($index, $type, $id) {
        $getParams = array(
            'index' => $index,
            'type' => $type,
            'id' => $id
        );
        try {
            return $this->server->exists($getParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * $params['id']              = (string) The document ID (Required)
     *        ['index']           = (string) The name of the index (Required)
     *        ['type']            = (string) The type of the document (use `_all` to fetch the first document matching the ID across all types) (Required)
     *        ['ignore_missing']  = ??
     *        ['fields']          = (list) A comma-separated list of fields to return in the response
     *        ['parent']          = (string) The ID of the parent document
     *        ['preference']      = (string) Specify the node or shard the operation should be performed on (default: random)
     *        ['realtime']        = (boolean) Specify whether to perform the operation in realtime or search mode
     *        ['refresh']         = (boolean) Refresh the shard containing the document before performing the operation
     *        ['routing']         = (string) Specific routing value
     *        ['_source']         = (list) True or false to return the _source field or not, or a list of fields to return
     *        ['_source_exclude'] = (list) A list of fields to exclude from the returned _source field
     *        ['_source_include'] = (list) A list of fields to extract and return from the _source field
     *
     * @param $params array Associative array of parameters
     *
     * @return array
     */
    public function get($index, $type, $id, $_source = null) {
        $getParams = array(
            'index' => $index,
            'type' => $type,
            'id' => $id
        );
        if ($_source) {
            $getParams['_source'] = $_source;
        }
        try {
            return $this->server->get($getParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

//    /puting
    public function putMapping($index, $type, $mapParam) {

        $indexParam = array();
        $indexParam['index'] = $index;
        $indexParam['type'] = $type;

        $indexParam['body'] = $mapParam;
        try {

            $response = $this->server->indices()->putMapping($indexParam);
            return $response;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

//    /putMapping
    public function getMapping($index, $type) {

        $indexParam = array();
        $indexParam['index'] = $index;
        $indexParam['type'] = $type;
        try {
            $response = $this->server->indices()->getMapping($indexParam);
            return $response;
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    public function delete($index) {
        $params = ['client' => ['future' => true],];

        $params['index'] = $index;
        return $this->server->indices()->delete($params);
    }

    public function boolquery($index, $type, $must = [], $must_not = [], $should = []) {
        $params['index'] = $index;
        $params['type'] = $type;
        $params['body']['query']['bool']['must'] = $must;
        $params['body']['query']['bool']['must_not'] = $must_not;
        $params['body']['query']['bool']['should'] = $should;

        $results = $this->server->search($params);
        return $results;
    }

    /*
     * 判断搜索条件是否存在
     * 存在 则组合查询
     * @author  zhongyg
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public static function getQurey(&$condition, &$body, $qurey_type = self::MATCH, $name = '', $field = null) {
        if ($qurey_type == self::MATCH || $qurey_type == self::MATCH_PHRASE || $qurey_type == self::TERM) {
            if (isset($condition[$name]) && $condition[$name]) {
                $value = $condition[$name];
                if (!$field) {
                    $field = $name;
                }
                $body['query']['bool']['must'][] = [$qurey_type => [$field => $value]];
            }
        } elseif ($qurey_type == self::WILDCARD) {

            if (isset($condition[$name]) && $condition[$name]) {

                $value = $condition[$name];
                if (!$field) {
                    $field = $name;
                }
                $body['query']['bool']['must'][] = [$qurey_type => [$field => '*' . $value . '*']];
            }
        } elseif ($qurey_type == self::MULTI_MATCH) {
            if (isset($condition[$name]) && $condition[$name]) {
                $value = $condition[$name];
                if (!$field) {
                    $field = [$name];
                }
                $body['query']['bool']['must'][] = [$qurey_type => [
                        'query' => $value,
                        'type' => 'most_fields',
                        'operator' => 'and',
                        'fields' => $field
                ]];
            }
        } elseif ($qurey_type == self::RANGE) {
            if (!$field) {
                $field = $name;
            }
            if (isset($condition[$name . '_start']) && isset($condition[$name . '_end']) && $condition[$name . '_end'] && $condition[$name . '_start']) {
                $created_at_start = $condition[$name . '_start'];
                $created_at_end = $condition[$name . '_end'];
                $body['query']['bool']['must'][] = [self::RANGE => [$name => ['gte' => $created_at_start, 'lte' => $created_at_end,]]];
            } elseif (isset($condition[$name . '_start']) && $condition[$name . '_start']) {
                $created_at_start = $condition[$name . '_start'];

                $body['query']['bool']['must'][] = [self::RANGE => [$field => ['gte' => $created_at_start,]]];
            } elseif (isset($condition[$name . '_end']) && $condition[$name . '_end']) {
                $created_at_end = $condition[$name . '_end'];
                $body['query']['bool']['must'][] = [self::RANGE => [$field => ['lte' => $created_at_end,]]];
            }
        }
    }

    /*
     * 判断搜索状态是否存在
     * 存在 则组合查询
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @param string $default // 默认值
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public static function getStatus(&$condition, &$body, $qurey_type = self::MATCH_PHRASE, $name = '', $field = '', $array = [], $default = 'VALID') {
        if (!$field) {
            $field = [$name];
        }
        if (isset($condition[$name]) && $condition[$name]) {
            $status = $condition[$name];
            if ($status == 'ALL') {

            } elseif (in_array($status, $array)) {

                $body['query']['bool']['must'][] = [$qurey_type => [$field => $status]];
            } else {
                $body['query']['bool']['must'][] = [$qurey_type => [$field => $default]];
            }
        } else {
            $body['query']['bool']['must'][] = [$qurey_type => [$field => $default]];
        }
    }

    /*
     * 判断搜索状态是否存在
     * 存在 则组合查询
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public static function getQureyByArr(&$condition, &$body, $qurey_type = self::TERMS, $names = '', $field = '') {
        if (!$field) {
            $field = [$names];
        }
        if (isset($condition[$names]) && $condition[$names]) {
            $name_arr = $condition[$names];
            $body['query']['bool']['must'][] = [$qurey_type => [$field => $name_arr]];
        }
    }

    /*
     * 判断搜索状态是否存在
     * 存在 则组合查询
     * @param mix $condition // 搜索条件
     * @param mix $body // 返回的数据
     * @param string $qurey_type // 匹配类型
     * @param string $name // 查询的名称
     * @param string $field // 匹配的名称
     * @param string $default // 默认值
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public static function getQureyByBool(&$condition, &$body, $qurey_type = self::MATCH_PHRASE, $name = '', $field = '', $default = 'N') {
        if (!$field) {
            $field = $name;
        }
        if (isset($condition[$name]) && $condition[$name]) {
            $recommend_flag = $condition[$name] == 'Y' ? 'Y' : $default;
            $body['query']['bool']['must'][] = [$qurey_type => [$field => $recommend_flag]];
        }
    }

}
