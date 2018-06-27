<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author zhongyg
 */
class TestModel extends Model {

    protected $tablePrefix = 't_';
    protected $tableName = 'items';
    protected $dbName = 'test'; //数据库名称
    protected $es = '';
    protected $dns = '';

    public function __construct() {

        $this->dns = [
            'type' => 'pdo',
            'host' => 'localhost',
            'name' => 'test',
            'user' => 'root',
            'pwd' => 'root',
            'port' => '3306',
            'charset' => 'utf8'
        ]; // 'mysql://root:root@localhost:3306/test#utf8';


        $this->es = new ESClient();
    }

    public function __call($method, $args) {

        parent::__call($method, $args);
    }

    /* 条件组合
     * @param mix $condition // 搜索条件
     */

    private function getCondition($condition) {
        $body = [];

        if (isset($condition['cate_id'])) {
            $cate_id = $condition['cate_id'];
            $body['query']['bool']['must'][] = [ESClient::TERM => ['cate_id' => $cate_id]];
        }

        if (isset($condition['ordid'])) {
            $ordid = $condition['ordid'];
            $body['query']['bool']['must'][] = [ESClient::TERM => ['ordid' => $ordid]];
        }

        if (isset($condition['num_iid'])) {
            $num_iid = $condition['num_iid'];
            $body['query']['bool']['must'][] = [ESClient::TERM => ['num_iid' => $num_iid]];
        } if (isset($condition['sellerId'])) {
            $sellerId = $condition['sellerId'];
            $body['query']['bool']['must'][] = [ESClient::TERM => ['sellerId' => $sellerId]];
        }
        if (isset($condition['show_name'])) {
            $show_name = $condition['show_name'];
//            $body['query']['bool']['must'][] = ['bool' => ['should' => [
//                        [ESClient::MATCH => ['title' => $show_name]],
//                        [ESClient::MATCH => ['intro' => $show_name]],
//                        [ESClient::MATCH => ['tags' => $show_name]],
//                    ]
//            ]];
            $body['query'] = ['multi_match' => [
                    "query" => $show_name,
                    "type" => "best_fields",
                    "fields" => ["title", "intro", 'tags']
            ]];
        }
        return $body;
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix  
     */

    public function getgoods($condition, $lang = 'en') {
        try {
            $body = $this->getCondition($condition);

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


            return $es->setbody($body)
                            ->sethighlight(
                                    [
                                        'title' => new \stdClass(),
                                        'intro' => new \stdClass(),
                                        'tags' => new \stdClass(),
                                    ]
                            )
                            ->search($this->dbName, $this->tableName, $from, $pagesize);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过搜索条件获取数据列表
     * @param mix $condition // 搜索条件
     * @param string $lang // 语言
     * @return mix  
     */

    public function getshow_catlist($condition, $lang = 'en') {

        try {
            $body = $this->getCondition($condition);

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
            return $es->setbody($body)
                            ->setaggs('cate_id', 'cate_id', 'terms')
                            ->setfields(['title', 'cate_id'])
                            ->search($this->dbName, $this->tableName, $from);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    public function create_data() {
        $body['mappings']['items'] = ['properties' => [
                'cate_id' => [
                    'type' => 'integer', "index" => "not_analyzed",
                ],
                'ordid' => [
                    'type' => 'integer', "index" => "not_analyzed",
                ],
                'num_iid' => [
                    'type' => 'string', "index" => "not_analyzed",
                ],
                'sellerId' => [
                    'type' => 'string', "index" => "not_analyzed",
                ],
                'title' => [
                    'type' => 'string',
                    "analyzer" => "ik",
                    "search_analyzer" => "ik",
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'intro' => [
                    'type' => 'string',
                    "analyzer" => "ik",
                    "search_analyzer" => "ik",
                    "include_in_all" => "true",
                    "boost" => 8
                ],
                'tags' => [
                    'type' => 'string',
                    "analyzer" => "ik",
                    "search_analyzer" => "ik",
                    "include_in_all" => "true",
                    "boost" => 8
                ]
            ]
        ];
        $this->es->create_index($this->dbName, $body);
    }

    public function import() {

//        $sql = 'select id,tags,intro,title,sellerId,num_iid,ordid,id,cate_id  from t_items limit 0,1000';
//        $items = $this->db(1, $this->dns)->query($sql);
        $db = db_Db::getInstance($this->dns);
      


        foreach ($items as $item) {
            $id = $item['id'];
            unset($item['id']);
            $body = $item;
            $this->es->add_document($this->dbName, 'items', $body, $id);
        }
    }

    //put your code here
}
