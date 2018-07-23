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

    public function nameAction() {
        $org_model = new OrgModel();
        $org_id = $this->getPut('org_id');

        if ($org_id) {
            $name = $org_model->getNameById($org_id, $this->lang);
            if ($name) {
                $this->setMessage($this->lang == 'en' ? 'SUCCESS' : '获取成功!');
                $this->jsonReturn($name);
            } else {
                $this->setMessage($this->lang == 'en' ? 'EMPTY' : '数据为空!');

                $this->setCode(MSG::ERROR_EMPTY);
                $this->jsonReturn();
            }
        } else {



            $this->setMessage($this->lang == 'en' ? 'The parameter is wrong, org_id can not be empty' : '参数不对,org_id不能为空!');

            $this->setCode(MSG::ERROR_PARAM);
            $this->jsonReturn();
        }
    }

    public function listAction() {
        $condition = ['org_node' => ['ub', 'lg']];
        $org_model = new OrgModel();
        $parent_id = '';
        $org_id = $this->getPut('org_id');
        if ($org_id) {
            $parent_id = $org_model->getParentid($org_id);
        }
        $data = $org_model->getList($condition);
        array_unshift($data, ['id' => 'ERUI',
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
