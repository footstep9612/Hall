<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Boxshipownerclause
 * @author  zhongyg
 * @date    2017-8-1 17:34:40
 * @version V2.0
 * @desc   发货箱型对应船东条款
 */
class BoxshipownerclauseController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * 所有发货箱型对应船东条款
     * @param data $data;
     * @return array
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   所有发货箱型对应船东条款
     */

    public function listAction() {
        $data = $this->getPut();

        $box_shipowner_clause_model = new BoxShipownerClauseModel();

        $arr = $box_shipowner_clause_model->getlist($data);


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
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   所有发货箱型对应船东条款
     */

    public function infoAction() {
        $id = $this->get('id') ? $this->get('id') : $this->getPut('id');

        $box_shipowner_clause_model = new BoxShipownerClauseModel();

        $arr = $box_shipowner_clause_model->info($id);


        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /* 新增
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   所有发货箱型对应船东条款
     */

    public function createAction() {
        $condition = $this->getPut(null);
        $box_shipowner_clause_model = new BoxShipownerClauseModel();
        $result = $box_shipowner_clause_model->create_data($condition);
        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /* 更新
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   所有发货箱型对应船东条款
     */

    public function updateAction() {

        $condition = $this->getPut(null);
        $box_shipowner_clause_model = new BoxShipownerClauseModel();
        $result = $box_shipowner_clause_model->update_data($condition);
        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /* 删除
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   所有发货箱型对应船东条款
     */

    public function deleteAction() {

        $id = $this->getPut('id');
        $where['id'] = $id;
        if ($id) {
            $where['id'] = $id;
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $result = $this->_model->where($where)->save(['status' => 'DELETED']);
        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
