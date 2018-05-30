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
class SolutionController extends PublicController {

    //put your code here
    public function init() {

        parent::init();
    }

    /*
     * 获取解决方案列表
     */

    public function UpdateAction() {
        $id = $this->getPut('id');
        $thumb = $this->getPut('thumb');


        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择解决方案!');
            $this->jsonReturn();
        }

        if (empty($thumb)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请上传thumb 图片!');
            $this->jsonReturn();
        }
        $solution_model = new SolutionModel();
        $flag = $solution_model->UpdateData($id, $thumb);
        if ($flag) {
            $this->jsonReturn(true);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        }
    }

}
