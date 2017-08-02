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
class BoxShipownerClauseController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * 所有计费单位
     */

    public function listAction() {
        $data = $this->get();

        $box_shipowner_clause_model = new BoxShipownerClauseModel();
        if (redisGet('BoxShipowner_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('BoxShipowner_' . md5(json_encode($data))), true);
        } else {
            $arr = $box_shipowner_clause_model->getlist($data);
            if ($arr) {
                redisSet('BoxShipowner_' . md5(json_encode($data)), json_encode($arr));
            }
        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /*
     * 所有计费单位
     */

    public function infoAction() {
        $id = $this->get('id');

        $box_shipowner_clause_model = new BoxShipownerClauseModel();
        if (redisGet('BoxShipowner_' . md5($id))) {
            $arr = json_decode(redisGet('BoxShipowner_' . md5($id)), true);
        } else {
            $arr = $box_shipowner_clause_model->info($id);
            if ($arr) {
                redisSet('BoxShipowner_' . md5($id), json_encode($arr));
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
        $box_shipowner_clause_model = new BoxShipownerClauseModel();

        $result = $box_shipowner_clause_model->create_data($condition);
        if ($result) {
//            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function updateAction() {

        $condition = $this->getPut(null);
        $box_shipowner_clause_model = new BoxShipownerClauseModel();
        $condition['id'] = $this->get('id');
        $result = $box_shipowner_clause_model->update_data($condition);
        if ($result) {
//            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {

        $id = $this->get('id');
        $where['id'] = $id;
        if ($id) {
            $where['id'] = $id;
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $result = $this->_model->where($where)->delete();
        if ($result) {
//            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
