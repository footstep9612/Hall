<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RateController
 * @author  zhongyg
 * @date    2017-8-2 13:07:21
 * @version V2.0
 * @desc   物流费率
 */
class EsoplogController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * Description of 物流费率列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    public function listAction() {
        $condtion = $this->getPut();
        $es_model = new ESOpLogModel();
        $arr = $es_model->getList($condtion);

        if (isset($arr['hits']['total']) && $arr['hits']['total'] > 0) {

            $data['count'] = isset($arr['hits']['total']) ? intval($arr['hits']['total']) : 0;
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::MSG_SUCCESS;
            $data['data'] = [];
            foreach ($arr['hits']['hits'] as $val) {
                $val['_source']['data'] = json_decode($val['_source']['data'], true);
                $data['data'][] = $val['_source'];
            }
            $this->jsonReturn($data);
        } else {
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::ERROR_EMPTY;
            $data['data'] = $arr;
            $data['count'] = 0;

            $this->jsonReturn(null);
        }
    }

}
