<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 供应商询单统计列表
 * @author  zhongyg
 * @date    2017-11-7 11:18:05
 * @version V2.0
 * @desc
 */
class SupplierinquiryController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * 供应商询单统计
     */

    public function listAction() {
        $condition = $this->getPut();
        $supplier_inquiry_model = new SupplierInquiryModel();
        $data = $supplier_inquiry_model->getList($condition);

        if ($data) {
            $suppliercount = $supplier_inquiry_model->getSupplierCount();
            $inquirycount = $supplier_inquiry_model->getInquiryCount();
            $this->setvalue('suppliercount', $suppliercount);
            $this->setvalue('inquirycount', $inquirycount);
            $this->jsonReturn($data);
        } elseif ($data === []) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    public function InfoAction() {
        $supplier_id = $this->getPut('supplier_id');
        $condition = $this->getPut();
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID不能为空!');
        }
        $supplier_inquiry_model = new SupplierInquiryModel();
        $info = $supplier_inquiry_model->Info($supplier_id);
        $this->setvalue('supplier', $info);
        $data = $supplier_inquiry_model->getInquirysBySupplierId($supplier_id, $condition);

        if ($data) {
            $count = $supplier_inquiry_model->getInquiryCount($supplier_id);
            $this->setvalue('count', $count);
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
