<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeeType
 * @author  zhongyg
 * @date    2017-8-1 17:34:40
 * @version V2.0
 * @desc   
 */
class TransboxtypeController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * 所有计费单位
     */

    public function listAction() {
        $data = $this->get() ? $this->get() : $this->getPut();

        $trans_box_type_model = new TransBoxTypeModel();
        if (redisGet('TransBoxType_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('TransBoxType_' . md5(json_encode($data))), true);
        } else {
            $arr = $trans_box_type_model->getlist($data);

            if ($arr) {
                redisSet('TransBoxType_' . md5(json_encode($data)), json_encode($arr));
            }
        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /*
     * 所有计费单位
     */

    public function infoAction() {
        $id = $this->get('id') ? $this->get('id') : $this->getPut('id');

        $trans_box_type_model = new TransBoxTypeModel();
        if (redisGet('TransBoxType_' . md5($id))) {
            $arr = json_decode(redisGet('TransBoxType_' . md5($id)), true);
        } else {
            $arr = $trans_box_type_model->info($id);
            if ($arr) {
                redisSet('TransBoxType_' . md5($id), json_encode($arr));
            }
        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    public function createAction() {
        $condition = $this->getPut(null);
        $trans_box_type_model = new TransBoxTypeModel();

        $result = $trans_box_type_model->create_data($condition);
        if ($result) {
            // $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function updateAction() {
        $trans_box_type_model = new TransBoxTypeModel();
        $condition = $this->getPut(null);

        if (!$condition['id']) {
            $condition['id'] = $this->get('id');
        }
        $result = $trans_box_type_model->update_data($condition);

        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {
        $trans_box_type_model = new TransBoxTypeModel();
        $id = $this->get('id') ? $this->get('id') : $this->getPut('id');
        $where['id'] = $id;
        if ($id) {
            $where['id'] = $id;
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $result = $trans_box_type_model->delete_data($id);
        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
