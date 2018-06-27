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
class EskeywordController extends PublicController {

    protected $index = 'erui_keyword';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];

//put your code here
    public function init() {

        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } else {
            parent::init();
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
                $eskeywordmodel = new ESKeywordsModel();
                $eskeywordmodel->import($lang);
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
     * Description of 创建索引
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */
    public function indexAction() {

        $version = $this->getPut('version', 1);
        $old_version = $this->getPut('old_version', null);
        $body['mappings'] = [];
        $body['settings']['analysis']['analyzer']['caseSensitive'] = [
            'filter' => 'lowercase', 'type' => 'custom', 'tokenizer' => 'keyword'
        ];
        $keywordmapping = $this->_getkeywordmapping();
        foreach ($this->langs as $lang) {


            $body['mappings']['keyword_' . $lang]['properties'] = $keywordmapping;
            $body['mappings']['keyword_' . $lang]['_all'] = ['enabled' => true];
        }
        $es = new ESClient();
        $state = $es->getstate();
        if (!isset($state['metadata']['indices'][$this->index . '_' . $version])) {
            $es->create_index($this->index . '_' . $version, $body, 5, 1);
            if ($old_version && $es->index_existsAlias($this->index . '_' . $old_version, $this->index)) {
                $es->index_deleteAlias($this->index . '_' . $old_version, $this->index);
            }
            $es->index_alias($this->index . '_' . $version, $this->index);
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
        $keywordmapping = $this->_getkeywordmapping();
        $es = new ESClient();


        $es->close($this->index);
        foreach ($this->langs as $lang) {

            $keyword_mapParam = ['keyword_' . $lang => [
                    'properties' => $keywordmapping,
                    '_all' => ['enabled' => true]
            ]];


            $flag = $es->putMapping($this->index, 'keyword_' . $lang, $keyword_mapParam);
        }
        $es->open($this->index);

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
    public function _getkeywordmapping() {


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
            'cat_no' => $not_analyzed, //SPU
            'country_bn' => $not_analyzed, //SPU
            'market_area_bn' => $not_analyzed, //SPU
            'cat_name' => $ik_analyzed, //商品名称
            'name' => $ik_analyzed, //商品名称
            'status' => $not_analyzed, //状态
            'created_by' => $int_analyzed, //创建人
            'created_at' => $not_analyzed, //创建时间
            'updated_by' => $int_analyzed, //修改人
            'updated_at' => $not_analyzed, //修改时间
            'checked_by' => $int_analyzed, //审核人
            'checked_at' => $not_analyzed, //审核时间
            'deleted_flag' => $not_analyzed, //删除标志
        ];

        return $body;
    }

}
