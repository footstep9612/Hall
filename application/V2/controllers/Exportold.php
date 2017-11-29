<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Exportold
 * @author  zhongyg
 * @date    2017-11-28 18:38:49
 * @version V2.0
 * @desc
 */
class ExportoldController extends PublicController {

    //put your code here
    //put your code here
    public function init() {

        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } else {
            parent::init();
        }
    }

    /**
     * 产品导出
     */
    public function exportAction() {
        $gadget_model = new GadgetModel();
        set_time_limit(0);
        $localDir = $gadget_model->excelAll();

        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
