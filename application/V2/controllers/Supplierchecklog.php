<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Supplierchecklog
 * @author  zhongyg
 * @date    2017-11-10 21:06:08
 * @version V2.0
 * @desc
 */
class SupplierchecklogController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    public function ListAction() {
        $supplier_id = $this->getPut('supplier_id');
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID不能为空!');
            $this->jsonReturn();
        }
        $supplierchecklog_model = new SupplierCheckLogModel();
        $data = $supplierchecklog_model->getlist($supplier_id);
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
