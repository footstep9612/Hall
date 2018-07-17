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

    public function listAction() {
        $condition = ['org_node' => ['ub', 'lg']];
        $org_id = $this->getPut('org_id');
        $parent_id = '';
        $org_model = new OrgModel();
        if ($org_id) {
            $parent_id = $org_model->getParentid($org_id);
        }
        $data = $org_model->getList($condition);
        array_push($data, ['id' => 'ERUI',
            'name' => '易瑞',
            'name_en' => 'ERUI',
            'name_es' => 'ERUI',
            'name_ru' => 'ERUI']);
        if ($data) {
            $show_data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, $this->lang);
            $show_data['code'] = MSG::MSG_SUCCESS;
            $show_data['data'] = $data;
            $show_data['parent_id'] = $parent_id;
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

    public function childsAction() {


        $parent_id = $this->getPut('parent_id');
        if ($parent_id == 'ERUI') {
            $condition = ['org_node' => ['erui', 'eub', 'elg']];
        } elseif ($parent_id) {
            $condition['parent_id'] = $parent_id;
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
