<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 组织
 */

class OrgController extends PublicController {

    public function init() {
        parent::init();
    }

    public function eruiAction() {


        $condition = ['org_node' => 'erui'];
        $org_model = new OrgModel();
        $data = $org_model->getList($condition);
        if ($data) {

            $show_data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, $this->lang);
            $show_data['code'] = MSG::MSG_SUCCESS;
            $show_data['data'] = $data;
            $show_data['count'] = $org_model->getCount($condition);

            $this->jsonReturn($show_data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function ubAction() {

        $condition = ['org_node' => 'ub'];
        $org_model = new OrgModel();
        $data = $org_model->getList($condition);
        if ($data) {

            $show_data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, $this->lang);
            $show_data['code'] = MSG::MSG_SUCCESS;
            $show_data['data'] = $data;
            $show_data['count'] = $org_model->getCount($condition);
            $this->jsonReturn($show_data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function lgAction() {

        $condition = ['org_node' => 'lg'];
        $org_model = new OrgModel();
        $data = $org_model->getList($condition);
        if ($data) {

            $show_data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, $this->lang);
            $show_data['code'] = MSG::MSG_SUCCESS;
            $show_data['data'] = $data;
            $show_data['count'] = $org_model->getCount($condition);
            $this->jsonReturn($show_data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function listAction() {

        $org_nodes = $this->getPut('org_nodes');
        if (empty($org_nodes)) {
            $condition = ['org_node' => ['erui', 'ub', 'lg']];
        } elseif ($org_nodes && is_string($org_nodes)) {
            $condition = ['org_node' => explode(',', $org_nodes)];
        } elseif ($org_nodes && is_array($org_nodes)) {
            $condition = ['org_node' => $org_nodes];
        }
        $org_model = new OrgModel();
        $data = $org_model->getList($condition);
        if ($data) {
            $show_data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, $this->lang);
            $show_data['code'] = MSG::MSG_SUCCESS;
            $show_data['data'] = $data;
            $show_data['count'] = $org_model->getCount($condition);
            $this->jsonReturn($show_data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
