<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货国家
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class StockcountryController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 获取现货国家列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function ListAction() {

        $condition = $this->getPut();
        $stock_country_model = new StockCountryModel();

        $list = $stock_country_model->getList($condition);

        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 获取现货国家详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function InfoAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $stock_country_model = new StockCountryModel();

        $list = $stock_country_model->getInfo($country_bn);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 新加现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function CreateAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $stock_country_model = new StockCountryModel();

        if ($stock_country_model->getExit($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择国家已经存在,请您重新选择!');
            $this->jsonReturn();
        }

        $show_flag = $this->getPut('show_flag', 'N');
        $display_position = $this->getPut('display_position');
        $list = $stock_country_model->createData($country_bn, $show_flag, $display_position);
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
     * Description of 更新现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function UpdateAction() {
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('ID不能为空!');
            $this->jsonReturn();
        }
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }

        $stock_country_model = new StockCountryModel();

        if ($stock_country_model->getExit($country_bn, $id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择国家已经存在,请您重新选择!');
            $this->jsonReturn();
        }
        $show_flag = $this->getPut('show_flag', 'N');
        $display_position = $this->getPut('display_position');


        $list = $stock_country_model->updateData($id, $country_bn, $show_flag, $display_position);
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

}
