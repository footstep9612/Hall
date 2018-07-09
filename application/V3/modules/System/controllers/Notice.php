<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 系统公告
 */

class NoticeController extends PublicController {

    public function __init() {
        parent::init();
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);
    }

    public function listAction() {
        if ($this->getMethod() === 'GET') {
            $condtion = $this->getParam();

            $condtion['lang'] = $this->getParam('lang', 'zh');
        } else {
            $condtion = $this->getPut();
            $condtion['lang'] = $this->getPut('lang', 'zh');
        }
        $notice_model = new System_NoticeModel();
        $data = $notice_model->getList($condtion);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);
    }

    public function countAction() {
        if ($this->getMethod() === 'GET') {
            $condtion = $this->getParam();
            $condtion['lang'] = $this->getParam('lang', 'zh');
        } else {
            $condtion = $this->getPut();
            $condtion['lang'] = $this->getPut('lang', 'zh');
        }
        $notice_model = new System_NoticeModel();
        $total = $notice_model->getCount($condtion);
        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => ['count' => $total]
        ]);
    }

}
