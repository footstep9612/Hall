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
        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR);
        } else {
            parent::init();
        }
    }

    /**
     * @desc 供应商的询单统计(重写版)
     * @author 买买提
     * @time 2018-04-19
     */
    public function listAction() {
        $request = $this->validateRequestParams();
        $supplier_inquiry_model = new SupplierInquiryModel();

        list($suppliers, $total) = $supplier_inquiry_model->suppliersWithFilterAndTotals($request);

        $suppliersStatics = $supplier_inquiry_model->setInquiryStatics($suppliers);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'count' => $total,
            'suppliercount' => $total,
            'inquirycount' => $supplier_inquiry_model->getInquiryCount($request),
            'data' => $suppliersStatics
        ]);

        p($total);
    }

    /*
     * 供应商询单统计
     */

    public function listOldAction() {
        $condition = $this->getPut();
        $supplier_inquiry_model = new SupplierInquiryModel();
        list($data, $count) = $supplier_inquiry_model->getList($condition);


        if ($data) {
            $suppliercount = $supplier_inquiry_model->getSupplierCount();
            $inquirycount = $supplier_inquiry_model->getInquiryCount($condition);
            // $count = 0; // $supplier_inquiry_model->getCount($condition);
            $this->setvalue('suppliercount', $suppliercount);
            $this->setvalue('inquirycount', $inquirycount);
            $this->setvalue('count', $count);
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

    public function infoAction() {
        $request = $this->validateRequestParams('supplier_id');
        $supplier_inquiry_model = new SupplierInquiryModel();

        $supplierInfo = $supplier_inquiry_model->Info($request['supplier_id']);

        $data = $supplier_inquiry_model->areaInquiryDataBy($request);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'supplier' => $supplierInfo,
            'count' => $supplier_inquiry_model->areaInquiryStaticsBy($request['supplier_id'], $request['area_bn']),
            'data' => $data
        ]);
    }

    /*     * **********----供应商询单明细----****************
     * |supplier_id|是|string|供应商id|
     * |current_no |否  |int    |当前页(默认1)|
     * |pagesize |否	|int	|每页显示条数|
     */

    public function InfoOldAction() {
        $supplier_id = $this->getPut('supplier_id');
        $condition = $this->getPut();
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID不能为空!');
            $this->jsonReturn();
        }
        $supplier_inquiry_model = new SupplierInquiryModel();
        $info = $supplier_inquiry_model->Info($supplier_id);
        $this->setvalue('supplier', $info);
        $data = $supplier_inquiry_model->getInquirysBySupplierId($supplier_id, $condition);

        if ($data) {
            $count = $supplier_inquiry_model->getInquiryCount($condition, $supplier_id);
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

    /*     * **********----导出询单列表----****************
     * |supplier_id|是|string|供应商id|
     * |current_no |否  |int    |当前页(默认1)|
     * |pagesize |否	|int	|每页显示条数|
     */

    public function InquiryexportAction() {
        ini_set('memory_limit', '4G');
        set_time_limit(0);
        $condition = $this->getPut();
        $supplier_inquiry_model = new SupplierInquiryModel();
        // 导出多少天以内的数据
//        if (empty($condition['created_at_start']) && !empty($condition['last_days'])) {
//            $days = intval($condition['last_days']) ?: 31;
//            $condition['created_at_start'] = $this->_getLastDaysDate($days);
//        }
        $inquiryModel = new InquiryModel();
        $inquiry_ids = $inquiryModel->getExportList($condition, $this->user['role_no'], $this->user['id'], $this->user['group_id']);
        $where = ['i.deleted_flag' => 'N',
            'i.status' => ['neq', 'DRAFT'],
        ];
//        if (!empty($condition['created_at_start']) && !empty($condition['created_at_end'])) {
//            $created_at_start = trim($condition['created_at_start']);
//            $created_at_end = date('Y-m-d H:i:s', strtotime(trim($condition['created_at_end'])) + 86399);
//            $where['i.created_at'] = ['between', $created_at_start . ',' . $created_at_end];
//        } elseif (!empty($condition['created_at_start'])) {
//
//            $created_at_start = trim($condition['created_at_start']);
//            $where['i.created_at'] = ['egt', $created_at_start];
//        } elseif (!empty($condition['created_at_end'])) {
//            $created_at_end = date('Y-m-d H:i:s', strtotime(trim($condition['created_at_end'])) + 86399);
//            $where['i.created_at'] = ['elt', $created_at_end];
//        }
//        if (!empty($condition['country_bn'])) {
//            $where['i.country_bn'] = ['in', explode(',', $condition['country_bn']) ?: ['-1']];
//        }
        if (!empty($inquiry_ids)) {
            $where['i.id'] = ['in', $inquiry_ids];
        } else {
            $where['i.id'] = -1;
        }
        $data = $supplier_inquiry_model->Inquiryexport($where);

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

    /*     * **********----导出询单列表----****************
     * |supplier_id|是|string|供应商id|
     * |current_no |否  |int    |当前页(默认1)|
     * |pagesize |否	|int	|每页显示条数|
     */

    public function InquiryToatolexportAction() {
        ini_set('memory_limit', '4G');
        set_time_limit(0);
        $condition = $this->getPut();
        $supplier_inquiry_model = new SupplierInquiryModel();
        // 导出多少天以内的数据
        if (empty($condition['created_at_start']) && !empty($condition['last_days'])) {
            $days = intval($condition['last_days']) ?: 31;
            $condition['created_at_start'] = $this->_getLastDaysDate($days);
        }
        $data = $supplier_inquiry_model->InquiryToatolexport($condition);

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

    /**
     * @desc 获取多少天之前的日期
     *
     * @return int $days 当前时间之前的天数
     * @author liujf
     * @time 2018-04-11
     */
    private function _getLastDaysDate($days) {
        return date('Y-m-d', strtotime(date('Y-m-d')) - $days * 24 * 3600);
    }

}
