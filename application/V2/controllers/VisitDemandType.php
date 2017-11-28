<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportTariffController
 * @author  jianghw
 * @date    2017-11-28
 * @version V2.0
 * @desc   需求类型
 */
class VisitDemandTypeController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 列表
     * @author  jianghw
     * @date    2017-11-28
     * @version V2.0
     * @desc   需求类型列表
     */
    public function listAction() {
        $data = $this->getPut();
        $visit_demand_model = new VisitDemadTypeModel();
        $arr = $visit_demand_model->getlist($data, false);
        if ($arr) {

        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        }else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /**
     * Description of 详情
     * @author  jianghw
     * @date    2017-11-28
     * @version V2.0
     * @desc   需求类型
     */
    public function infoAction() {

    }

    /**
     * Description of 新增
     * @author  jianghw
     * @date    2017-11-28
     * @version V2.0
     * @desc   需求类型
     */
    public function createAction() {

    }

    /**
     * Description of 编辑
     * @author  jianghw
     * @date    2017-11-28
     * @version V2.0
     * @desc   需求类型
     */
    public function updateAction() {

    }

    /**
     * Description of 删除增值税、关税信息
     * @author  jianghw
     * @date    2017-11-28
     * @version V2.0
     * @desc   需求类型
     */
    public function deleteAction() {

    }

}
