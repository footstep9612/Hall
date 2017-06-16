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
    const TERM = 'term'; //term : 相当于精确查询
    const REGEXP = 'regexp'; //term : 相当于正则式查询
    const WILDCARD = 'wildcard'; //term : 相当于模糊查询
    const PREFIX = 'prefix'; //term : 相当于前缀查询

//put your code here
    // "client", "custom", "filter_path", "human", "master_timeout", "timeout", "update_all_types", "wait_for_active_shards"

    private $server = '';
    private $body = [];
    private $regexp = []; //正则式查询
    private $wildcard = []; //模糊查询
    private $prefix = []; //前缀查询

    public function __construct() {
        $server = Yaf_Application::app()->getConfig()->esapi;
        $source_hosts = [$server];
        $this->server = ClientBuilder::create()
                        ->setHosts($source_hosts)->build();
    }

    /**
     * createindices
     * 创建索引
     * @access public
     * @param string $index 索引名称
     * @param string $type 类型名称
     * @param int $number_of_shards 主分片数量
     * @param int $number_of_replicas 从分片数量
     * @since 1.0
     * @return array
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

    /* 更新索引 
     * 更新索引的Updating Index Analysis 必须 先关闭索引 然后再更新 再开启索引
     * 
     * 
     */

    public function updateanalyzers($index) {
        $this->close($index);
        $SettingsParam = ['index' => $index,
            'body' => [
                "analysis" => [
                    "analyzer" => [
                        "content" => [
                            "type" => "custom",
                            "tokenizer" => "whitespace"
                        ]
                    ]
                ]
            ]
        ];
        try {
            $response = $this->server->indices()->putSettings($index, $Settings);
        } catch (Exception $ex) {
            LOG::write($ex->getMessage(), LOG::ERR);
        }
        $this->open($index);

        return $response;
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

    /* 更新索引 
     * 更新索引的Updating Index Analysis 必须 先关闭索引 然后再更新 再开启索引
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

    /*
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
     * 前缀查询（prefix Query）为了找到所有以 W1 开始的邮编，我们可以使用简单的前缀查询：
     */

    public function setprefix($prefix) {
        return ['prefix' => $prefix];
    }

    /*
     * 与前缀查询的特性类似，模糊（wildcard）查询也是一种低层次基于术语的查询，
     * 与前缀查询不同的是它可以让我们给出匹配的正则式。
     * 它使用标准的 shell 模糊查询：? 匹配任意字符，* 匹配0个或多个字符。
     */

    public function setwildcard($wildcard) {
        return ['wildcard' => $wildcard];
    }

    /*
     * #1 ? 匹配 1 和 2，* 与空格以及 7 和 8 匹配。
     * 如果现在我们只想匹配 W 区域的所有邮编，前缀匹配也会匹配以 WC 为开始的所有邮编，
     * 与模糊匹配碰到的问题类似，如果我们只想匹配以 W 开始并跟着一个数字的所有邮编，
     * 正则式（regexp）查询让我们能写出这样更复杂的模式：
     */

    public function setregexp($regexp) {
        return ['regexp' => $regexp];
    }

    /*
     * 模糊匹配。
     */

    public function setmatch($match) {
        return ['match' => $match];
    }

    /*
     * 模糊匹配。
     */

    public function setterm($match) {
        return [self::TERM => $match];
    }

    /*
     * must : 多个查询条件的完全匹配,相当于 and。
     */

    public function setmust($must, $type = self::TERM) {

        $val = [];
        switch ($type) {
            case self::MATCH:
                $val = $this->setmatch($must);
                break;
            case self::PREFIX:
                $val = $this->setprefix($must);
                break;
            case self::REGEXP:
                $val = $this->setregexp($must);
                break;
            case self::WILDCARD:
                $val = $this->setwildcard($must);
                break;
            case self::TERM:
                $val = $this->setterm($must);
                break;
            default :$val = $this->setmatch($must);
                break;
        }
        $this->body['query']['bool']['must'] [] = $val;
        return $this;
    }

    /*
     * must_not : 多个查询条件的相反匹配，相当于 not。
     */

    public function setdefault($must_not, $type = self::TERM) {

        $val = [];
        switch ($type) {
            case self::MATCH:
                $this->body['query'][self::MATCH] = $must_not;
                break;
            case self::PREFIX:
                $this->body['query'][self::PREFIX] = $must_not;
                break;
            case self::REGEXP:
                $this->body['query'][self::REGEXP] = $must_not;
                break;
            case self::WILDCARD:
                $this->body['query'][self::WILDCARD] = $must_not;
                break;
            case self::TERM:
                $this->body['query'][self::TERM] = $must_not;
                break;
            default : $this->body['query'][self::MATCH] = $must_not;
                break;
        }



        return $this;
    }

    /*
     * must_not : 多个查询条件的相反匹配，相当于 not。
     */

    public function setmust_not($must_not, $type = self::TERM) {

        $val = [];
        switch ($type) {
            case self::MATCH:
                $val = $this->setmatch($must_not);
                break;
            case self::PREFIX:
                $val = $this->setprefix($must_not);
                break;
            case self::REGEXP:
                $val = $this->setregexp($must_not);
                break;
            case self::WILDCARD:
                $val = $this->setwildcard($must_not);
                break;
            case self::TERM:
                $val = $this->setterm($must_not);
                break;
            default :$val = $this->setmatch($must_not);
                break;
        }

        $this->body['query']['bool']['must_not'] [] = $val;
        return $this;
    }

    /*
     * should : 至少有一个查询条件匹配, 相当于 or。
     */

    public function setshould($should, $type = self::TERM) {
        $val = [];
        switch ($type) {
            case self::MATCH:
                $val = $this->setmatch($should);
                break;
            case self::PREFIX:
                $val = $this->setprefix($should);
                break;
            case self::REGEXP:
                $val = $this->setregexp($should);
                break;
            case self::WILDCARD:
                $val = $this->setwildcard($should);
                break;
            case self::TERM:
                $val = $this->setterm($should);
                break;
            default :$val = $this->setmatch($should);
                break;
        }
        $this->body['query']['bool']['should'] [] = $val;
        return $this;
    }

    /*
     * 空查询
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
            echo json_encode($searchParams['body']);
            return $this->server->search($searchParams);
        } catch (Exception $ex) {

            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
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
