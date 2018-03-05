<?php

/*
 * @desc 定时任务控制器
 *
 * @author liujf
 * @time 2018-03-02
 */

class TimedtaskController extends PublicController {

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);

        $this->suppliersModel = new SuppliersModel();
        $this->supplierQualificationModel = new SupplierQualificationModel();
        
        $this->time = date('Y-m-d H:i:s');
    }
        
    /**
     * @desc 供应商资质过期改状态接口（定时任务：每天00:00:00执行）
     *
     * @author liujf
     * @time 2018-03-02
     */
    public function updateSupplierQualificationStatusAction() {
        $supplierIds = $this->supplierQualificationModel->getOverdueSupplierIds();
        try {
            $supplierIds && $this->suppliersModel->updateInfo(['id' => ['in', $supplierIds]], ['updated_by' => null, 'updated_at' => $this->time, 'status' => 'OVERDUE']);
            $this->jsonReturn(true);
        } catch (Exception $e) {
            $this->jsonReturn(false);
        }
    }
        
    /**
     * @desc 重写jsonReturn方法
     *
     * @author liujf
     * @time 2017-11-10
     */
    public function jsonReturn($data = [], $type = 'JSON') {
        if ($data) {
            $this->setCode('1');
            $this->setMessage('成功!');
            parent::jsonReturn($data, $type);
        } else {
            $this->setCode('-101');
            $this->setMessage('失败!');
            parent::jsonReturn();
        }
    }

}
