<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CountryController extends PublicController {

    public function __init() {
        parent::init();
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);
    }

    /*
     * 所有功能清单
     */

    public function listAction() {
        if ($this->getMethod() === 'GET') {
            $condtion = $this->getParam();

            $condtion['lang'] = $this->getParam('lang', 'zh');
        } else {
            $condtion = $this->getPut();
            $condtion['lang'] = $this->getPut('lang', 'zh');
        }

        $country_model = new Common_CountryModel();
        $arr = $country_model->getList($condtion); //($this->put_data);
        $count = $country_model->getCount($condtion);
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
