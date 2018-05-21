<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Solution
 * @author  zhongyg
 * @date    2018-5-15 9:31:17
 * @version V2.0
 * @desc
 */
class ShowcatsolutionController extends PublicController {

    //put your code here
    public function init() {
        $this->token = false;
        parent::init();
    }

    /*
     * 获取解决方案列表
     */

    public function listAction() {
        $condition = $this->getPut();
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        if (empty($condition['cat_no'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('展示分类编码不能为空!');
            $this->jsonReturn();
        }

        $show_cat_solution_model = new ShowCatSolutionModel();
        $data = $show_cat_solution_model->getList($condition);

        if ($data) {

            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        }
    }

    /*
     * 获取解决方案列表
     */

    public function InfoAction() {
        $id = $this->getPut($id);
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('ID 不能为空!');
            $this->jsonReturn();
        }


        $show_cat_solution_model = new ShowCatSolutionModel();
        $data = $show_cat_solution_model->Info($id);

        if ($data) {
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 解决方案关联展示分类
     * @author  zhongyg
     * @date    2018-05-15 16:50:09
     * @version V2.0
     * @desc  解决方案关联展示分类
     */
    public function CreateAction() {
        $condition = $this->getPut();
        if (empty($condition['cat_no'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请输入展示分类编码!');
            $this->jsonReturn();
        }
        if (empty($condition['solution_id'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择解决方案!');
            $this->jsonReturn();
        }
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $show_cat_solution_model = new ShowCatSolutionModel();

        if ($show_cat_solution_model->getExit($condition['solution_id'], $condition['cat_no'], $condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在展示分类已经存在相同解决方案,请您添加不同解决方案!');
            $this->jsonReturn();
        }


        $list = $show_cat_solution_model->createData($condition);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 解决方案关联展示分类
     * @author  zhongyg
     * @date    2018-05-15 16:50:09
     * @version V2.0
     * @desc  解决方案关联展示分类
     */
    public function UpdateAction() {
        $condition = $this->getPut();
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择要编辑的关联ID!');
            $this->jsonReturn();
        }
        if (empty($condition['cat_no'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请输入展示分类编码!');
            $this->jsonReturn();
        }
        if (empty($condition['solution_id'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择解决方案!');
            $this->jsonReturn();
        }
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $show_cat_solution_model = new ShowCatSolutionModel();

        if ($show_cat_solution_model->getExit($condition['solution_id'], $condition['cat_no'], $condition['lang'], $id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在展示分类已经存在相同解决方案,请您添加不同解决方案!');
            $this->jsonReturn();
        }


        $list = $show_cat_solution_model->updateData($id, $condition);
        if ($list) {
            $this->jsonReturn();
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
