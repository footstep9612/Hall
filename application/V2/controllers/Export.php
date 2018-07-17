<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ExportController extends PublicController {

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

    public function RfqAction() {
        ini_set('memory_limit', '4G');
        set_time_limit(0);
        $condition = $this->getPut();
        $export_model = new ExportModel();
        // 导出多少天以内的数据
        if (empty($condition['created_at_start']) && !empty($condition['last_days'])) {
            $days = intval($condition['last_days']) ?: 31;
            $condition['created_at_start'] = $this->_getLastDaysDate($days);
        }
        $data = $export_model->Rfq($condition);

        if ($data) {
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
