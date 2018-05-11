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
class EskeywordsController extends PublicController {

    protected $index = 'erui_keyword';
    protected $es = '';
    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $version = '1';

//put your code here
    public function init() {
        $this->token = false;
        parent::init();
        $this->es = new ESClient();
    }

    public function listAction() {
        $model = new ESKeywordsModel();
        $condition = $this->getPut();

        $country_bn = $this->getPut('country_bn', 'Argentina');
        $condition['country_bn'] = $country_bn;
        $ret = $model->getList($condition, $this->getLang());

        if ($ret) {
            $data = $ret[0];
            $list = $this->_getdata($data, $this->getLang());
            $this->setvalue('count', intval($data['hits']['total']));
            $this->setvalue('current_no', intval($ret[1]));
            $this->setvalue('pagesize', intval($ret[2]));
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($list);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 数据处理
     */

    private function _getdata($data, $lang = 'en') {

        if ($lang == 'zh') {
            $analyzer = 'ik';
        } elseif (in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $analyzer = $lang;
        } else {
            $analyzer = 'ik';
        }
        foreach ($data['hits']['hits'] as $key => $item) {
            $list[$key] = $item["_source"];
            if (isset($item['highlight']['name.' . $analyzer][0]) && $item['highlight']['show_name.' . $analyzer][0]) {
                $list[$key]['highlight_name'] = $item['highlight']['show_name.' . $analyzer][0];
            } else {
                $list[$key]['highlight_name'] = str_ireplace($keyword, '<em>' . $keyword . '</em>', $list[$key]['name']);
            }
        }
        return $list;
    }

}
