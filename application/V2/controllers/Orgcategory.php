<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PortController
 * @author  zhongyg
 * @date    2018-8-9 16:50:09
 * @version V2.0
 * @desc   事业部与分类映射关系
 */
class OrgCategoryController extends PublicController {

    public function init() {
        parent::init();
    }

    /*
     * Description of 列表
     * @author  zhongyg
     * @date    2018-8-9 13:07:21
     * @version V2.0
     * @desc   事业部与分类映射关系
     */

    public function listAction() {
        $condtion = $this->getPut(null);

        $model = new Rfq_OrgCategoryModel();
        $arr = $model->getlist($condtion);
        if ($arr) {
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::MSG_SUCCESS;
            $data['data'] = $arr;
            $this->jsonReturn($data);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 删除缓存
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('org_category');
        $redis->delete($keys);
    }

    /*
     * Description of 新增城市
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function createAction() {
        $condition = $this->getPut();
        if (empty($condition['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('分类名称不能为空');
            $this->jsonReturn();
        }
        $model = new Rfq_OrgCategoryModel();
        $data = $model->create_data($condition);
        if ($data === true) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 更新城市
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function updateAction() {

        $condition = $this->getPut();
        if (empty($condition['id'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择要删除事业部分类映射');
            $this->jsonReturn();
        }
        if (empty($condition['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('分类名称不能为空');
            $this->jsonReturn();
        }
        $model = new Rfq_OrgCategoryModel();
        $result = $model->update_data($condition);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 删除城市
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function deleteAction() {

        $id = $this->getPut($id);
        if (empty($id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择要删除事业部分类映射');
            $this->jsonReturn();
        }
        $model = new Rfq_OrgCategoryModel();
        $result = $model->delete_data($id);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 新增城市
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function batchCreateAction() {
        $condition = $this->getPut();
        if (empty($condition['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('分类名称不能为空');
            $this->jsonReturn();
        }

        $model = new Rfq_OrgCategoryModel();
        $data = $model->batchcreate_data($condition);
        if ($data === true) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
