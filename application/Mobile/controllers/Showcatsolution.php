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
        $condition['lang'] = 'en';
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $condition['cat_no'] = '44:00:00';
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

}
