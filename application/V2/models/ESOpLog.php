<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OpLogModel
 * @author  zhongyg
 * @date    2017-8-3 13:38:48
 * @version V2.0
 * @desc
 */
class ESOpLogModel {

    protected $index = 'oplogs';
    protected $version = '1';

    /**
     * Description of 产品索引信息组合
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function __construct() {
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
            'mappings' => [
                'logs' => [
                    'properties' => [
                        'module' => $not_analyzed, //语言
                        'controller' => $not_analyzed, //语言
                        'action' => $not_analyzed, //语言
                        'uri' => $not_analyzed, //语言
                        'data' => $ik_analyzed, //SPU
                        'lang' => $not_analyzed, //产品名称
                        'created_at' => $not_analyzed, //产品名称
                        'created_by' => $not_analyzed,
                        'created_name' => $ik_analyzed,
                    ],
                    '_all' => ['enabled' => true]
                ]]
        ];

        $es = new ESClient();
        $state = $es->getstate();
        if (!isset($state['metadata']['indices'][$this->index . '_' . $this->version])) {
            $es->create_index($this->index . '_' . $this->version, $body, 5, 0);
            $es->index_alias($this->index . '_' . $this->version, $this->index);
        }
    }

    public function Created($requst, $data, $lang, $user) {
        $es = new ESClient();
        $this->Deleted();
        $body['module'] = 'V2';
        $body['controller'] = strtolower($requst->getControllerName());
        $body['action'] = strtolower($requst->getActionName());
        if ($data['token']) {
            unset($data['token']);
        }
        $body['uri'] = strtolower($requst->getRequestUri());
        $body['data'] = json_encode($data, 256);

        $body['lang'] = $lang;
        $body['created_by'] = isset($user['id']) ? $user['id'] : '';
        $body['created_name'] = isset($user['name']) ? $user['name'] : '';
        $body['created_at'] = date('Y-m-d H:i:s');
        $id = null;
        $es->add_document($this->index, 'logs', $body, $id);
    }

    public function Deleted() {
        $es = new ESClient();
        $body = [];
        $body['query']['bool']['must'][] = [ESClient::RANGE => ['created_at' => ['lte' => date('Y-m-d H:i:s', strtotime(' -1 Month')),]]];
        $flag = $es->deleteByQuery($this->index, 'logs', $body);
    }

    public function getList($condition) {
        $es = new ESClient();
        $body = [];
        if (!empty($condition['controller']) && $condition['controller']) {
            $condition['controller'] = explode(',', $condition['controller']);
            foreach ($condition['controller'] as $key => $controller) {
                $condition['controller'][$key] = strtolower(trim($controller));
            }
        }
        if (!empty($condition['uri']) && $condition['uri']) {
            $condition['uri'] = strtolower(trim($condition['uri']));
        }
        if (!empty($condition['action']) && $condition['action']) {
            $condition['action'] = strtolower(trim($condition['action']));
        }
        ESClient::getQurey($condition, $body, ESClient::TERM, 'uri', 'uri');
        ESClient::getQureyByArr($condition, $body, ESClient::TERMS, 'controller', 'controller');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'action', 'action');
        ESClient::getQurey($condition, $body, ESClient::WILDCARD, 'data', 'data.all');
        ESClient::getQurey($condition, $body, ESClient::RANGE, 'created_at');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'lang', 'lang');
        ESClient::getQurey($condition, $body, ESClient::MATCH, 'created_name', 'created_name.ik');
        ESClient::getQurey($condition, $body, ESClient::TERM, 'created_by', 'created_by');
        $es->body = $body;

        $pagesize = 10;
        $current_no = 1;
        if (isset($condition['current_no'])) {
            $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
        }
        if (isset($condition['pagesize'])) {
            $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        }
        $from = ($current_no - 1) * $pagesize;
        return $es->search($this->index, 'logs', $from, $pagesize);
    }

}
