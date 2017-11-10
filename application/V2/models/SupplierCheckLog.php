<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 审核日志
 * @author  zhongyg
 * @date    2017-11-10 20:55:07
 * @version V2.0
 * @desc
 */
class SupplierCheckLogModel extends PublicModel {

//put your code here
    protected $tableName = 'supplier_check_log';
    protected $dbName = 'erui_supplier';

    public function __construct() {
        parent::__construct();
    }

    /*
     * 新加审核日志
     */

    public function create_data($condition) {

        if (empty($condition['supplier_id'])) {
            $this->error = '采购商ID不能为空';
            return false;
        }
        $data = $this->create($condition);
        $data['created_by'] = defined('UID') ? UID : 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->add($data);
    }

    public function getlist($supplier_id) {
        $data = $this->where(['supplier_id' => $supplier_id])->select();
        $this->_setOrgName($data);
        $this->_setCheckedName($data);
        $this->_setStatus($data);
        return $data;
    }

    /**
     * 获取处理状态
     * @param mix $data
     * @return
     * @author zyg
     */
    private function _setOrgName(&$data) {
        if ($data) {
            $org_ids = [];
            foreach ($data as $item) {
                if ($item['org_id']) {
                    $org_ids[] = $item['org_id'];
                }
            }
            if ($org_ids) {
                $org_model = new OrgModel();
                $orgs = $org_model->field('id,name')->where(['id' => ['in', $org_ids],
                            'deleted_flag' => 'N', 'status' => 'NORMAL'])->select();
                $orgnames = [];
                if ($orgs) {
                    foreach ($orgs as $org) {
                        $orgnames[$org['id']] = $org['name'];
                    }
                }
            }

            foreach ($data as $key => $val) {
                if ($val['org_id'] && isset($orgnames[$val['org_id']])) {
                    $val['org_name'] = $orgnames[$val['org_id']];
                } else {
                    $val['org_name'] = '';
                }

                $data[$key] = $val;
            }
        }
    }

    /**
     * 获取处理状态
     * @param mix $data
     * @return
     * @author zyg
     */
    private function _setCheckedName(&$data) {
        if ($data) {
            $checked_bys = [];
            foreach ($data as $item) {
                if ($item['created_by']) {
                    $checked_bys[] = $item['created_by'];
                }
            }
            if ($checked_bys) {
                $employee_model = new EmployeeModel();
                $usernames = $employee_model->getUserNamesByUserids($checked_bys);
            }
            foreach ($data as $key => $val) {
                if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                    $val['created_name'] = $usernames[$val['created_by']];
                } else {
                    $val['created_name'] = '';
                }

                $data[$key] = $val;
            }
        }
    }

    /**
     * 获取处理状态
     * @param mix $data
     * @return
     * @author zyg
     */
    private function _setStatus(&$data) {
        if ($data) {
            foreach ($data as $key => $item) {

                switch ($item['status']) {

                    case 'INVALID':
                        $item['status'] = '已驳回';
                        break;
                    case 'VALID':
                        $item['status'] = '已通过';
                        break;

                    default :
                        $item['status'] = '';
                        break;
                }
                $item['erui_member_flag'] = $item['erui_member_flag'] === 'Y' ? '是' : '否';
                $data[$key] = $item;
            }
        }
    }

}
