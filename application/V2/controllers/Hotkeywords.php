<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HotKeywords
 * @author  zhongyg
 * @date    2017-8-1 18:19:45
 * @version V2.0
 * @desc
 */
class HotkeywordsController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * 所有计费单位
     */

    public function listAction() {
        $data = $this->get();

        $hot_keywords_model = new HotKeywordsModel();

        $arr = $hot_keywords_model->getlist($data);

        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    //put your code here
}
