<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once MYPATH . "/vendor/autoload.php";

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

/**
 * Description of ESClient
 *
 * @author zyg
 */
class ESClient {

    const MATCH = 'match'; //match : 相当于模糊查询
    const TERM = 'term'; //term：代表完全匹配，即不进行分词器分析，文档中必须包含整个搜索的词汇
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

//put your code here
    // "client", "custom", "filter_path", "human", "master_timeout", "timeout", "update_all_types", "wait_for_active_shards"

    private $server = '';
    private $body = [];
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
        $source_hosts = [$server];
        $this->server = ClientBuilder::create()
                        ->setHosts($source_hosts)->build();
    }

    /*     * ********************************---索引操作---***************************************
     * createindices
     * 创建索引
     * @access public
     * @param string $index 索引名称
     * @param string $type 类型名称
     * @param int $number_of_shards 主分片数量
     * @param int $number_of_replicas 从分片数量
     * @since 1.0
     * @return array     *
     */

    public function create_index($index, $type, $body, $id) {
        $indexParams['index'] = $index;
        $indexParams['type'] = $type;
        $indexParams['body'] = $body;
        $indexParams['body']['settings']['number_of_shards'] = 5;
        $indexParams['body']['settings']['number_of_replicas'] = 0;
        /*
         * 使用自己的ID只要再添加一个id字段即可。例：
         */
        $indexParams['id'] = $id;
        return $this->server->create($indexParams);
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
            echo json_encode($searchParams['body']);
            return $this->server->indices()->analyze($searchParams);
        } catch (Exception $ex) {

            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
        //   var_dump($retDoc);
    }

    /*
     * 删除索引
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

    public function bulk() {
        $params = [];
        for ($i = 0; $i < 100; $i++) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'my_index',
                    '_type' => 'my_type',
                    '_id' => $i
                ]
            ];

            $params['body'][] = [
                'my_field' => 'my_value',
                'second_field' => 'some more values'
            ];
        }

        $responses = $this->server->bulk($params);
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

    public function get_document($index, $type, $id) {
        $getParams = array();
        $getParams['index'] = $index;
        $getParams['type'] = $type;
        $getParams['id'] = $id;
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

        $getParams[] = [];

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

    public function update_document($index, $type, $body, $id) {
        $updateParams = array();
        $updateParams['index'] = $index;
        $updateParams['type'] = $type;
        $updateParams['id'] = $id;
        $updateParams['body'] = $body; //['doc']['testField'] = 'xxxx';
        try {
            return $this->server->update($updateParams);
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

    /*
     * must : 多个查询条件的完全匹配,相当于 and。
     * $bost 权重
     */

    public function setmust($must, $type = self::MATCH, $bost = 0) {


        $val = $this->setdata($must, $bost);
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

        $this->body['query']['bool']['must'] [] = [$type => $val];
        return $this;
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

    public function setmust_not($must_not, $type = self::MATCH, $bost = 0) {

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

        $this->body['query']['bool']['must_not'] [] = [$type => $val];
        return $this;
    }

    /*
     * should : 至少有一个查询条件匹配, 相当于 or。
     */

    public function setshould($should, $type = self::MATCH, $bost = 0) {
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
        $this->body['query']['bool']['should'] [] = [$type => $val];
        return $this;
    }

    /*
     * filter : 过滤。
     */

    public function setfilter($filter, $type = self::MATCH) {
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
        $this->body['query']['bool']['filter'] [] = [$type => $val];
        return $this;
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
        $this->body['highlight']['fields'] = $fields;
        return $this;
    }

    /*
     * 配置高亮显示
     * $sort desc asc
     * $field 需要拍下的字段
     */

    public function setsort($field, $sort) {
        $this->body['sort'][] = [$field => ['order' => $sort]];
        return $this;
    }

    /*
     * 查询
     *
     */

    public function search($index, $type, $analyzer = '', $from = 0, $size = 100) {
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

            return $this->server->search($searchParams);
        } catch (Exception $ex) {

            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
        //   var_dump($retDoc);
    }

    public function count($index, $type, $analyzer = '', $from = 0, $size = 100) {
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

            return $this->server->count($searchParams);
        } catch (Exception $ex) {

            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
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
    public function get($index, $type, $id) {
        $getParams = array(
            'index' => $index,
            'type' => $type,
            'id' => $id
        );
        try {
            return $this->server->get($getParams);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

//    /putMapping
    public function putMapping($index, $type, $mapParam) {

        $indexParam = array();
        $indexParam['index'] = $index;
        $indexParam['type'] = $type;

        $indexParam['body'] = [
            $type => $mapParam
        ];
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
        $params['body']['query']['bool']['should'] = $must_not;


        $results = $this->server->search($params);
        var_dump($results);
    }

}
