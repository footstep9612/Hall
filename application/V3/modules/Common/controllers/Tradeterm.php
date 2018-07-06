<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TradetermController extends PublicController {

    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $index = 'erui_dict';
    protected $es = '';

    public function __init() {
        parent::init();
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);

        $this->es = new ESClient();
    }

    /*
     * 所有功能清单
     */

    public function listAction() {
        if ($this->getMethod() === 'GET') {
            $data = $this->getParam();
            $data['lang'] = $this->getParam('lang', 'zh');
        } else {
            $data = $this->getPut();
            $data['lang'] = $this->getPut('lang', 'zh');
        }

        $country_model = new CountryModel();
        $arr = $country_model->getlistBycodition($data); //($this->put_data);
        $count = $country_model->getCount($data);
        $this->setvalue('count', $count);
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);

            $this->jsonReturn($arr);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
    }

    public function shortcutsAction() {
        if ($this->getMethod() === 'GET') {
            $data = $this->getParam();
            $data['lang'] = $this->getParam('lang', 'zh');
        } else {
            $data = $this->getPut();
            $data['lang'] = $this->getPut('lang', 'zh');
        }

        $country_model = new CountryModel();
        $arr = $country_model->getlistBycodition($data); //($this->put_data);
        $count = $country_model->getCount($data);
        $this->setvalue('count', $count);
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);

            $this->jsonReturn($arr);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
    }

}
