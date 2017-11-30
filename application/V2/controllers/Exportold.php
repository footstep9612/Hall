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
        $export_model = new ExportOldModel();
        set_time_limit(0);
        ini_set('memory_limit', '8G');
        $condition['created_at_end'] = '2017-11-01';
        $condition['created_at_start'] = '2017-08-01';
        $condition['status'] = 'ALL';
        //$condition['spu'] = '1402260000700000';

        $condition['onshelf_flag'] = 'A';
        $localDir = $export_model->export($condition, '', 'zh');

        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
