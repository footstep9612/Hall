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
class HotKeywordsController extends PublicController {

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
        if (redisGet('HotKeywords_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('HotKeywords_' . md5(json_encode($data))), true);
        } else {
            $arr = $hot_keywords_model->getlist($data);
            if ($arr) {
                redisSet('HotKeywords_' . md5(json_encode($data)), json_encode($arr));
            }
        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    //put your code here
}
