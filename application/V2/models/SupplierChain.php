<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SupplierChain
 * @author  zhongyg
 * @date    2017-11-10 13:42:02
 * @version V2.0
 * @desc
 */
class SupplierChainModel extends PublicModel {

    //put your code here
    protected $tableName = 'supplier';
    protected $dbName = 'erui_supplier';

    const STATUS_VALID = 'APPROVED'; //有效,通过
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_TEST = 'TEST'; //待报审；
    const STATUS_CHECKING = 'STATUS_CHECKING'; //审核；
    const STATUS_DELETED = 'DELETED'; //删除；
    const ERUI_STATUS_VALID = 'VALID';          //有效
    const ERUI_STATUS_CHECKING = 'CHECKING';          //审核中

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition 查询条件
     * @param mix $where 返回条件
     * @return mix
     * @author zyg
     *
     */
    protected function _getcondition($condition = [], &$where = [], $is_Chain = true) {
        if ($is_Chain) {
            $where = ['deleted_flag' => 'N',
                'status' => ['in', ['APPROVED', 'VALID']]
            ];
        } else {
            $where = ['deleted_flag' => 'N',
                'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
            ];
        }
        $this->_getValue($where, $condition, 'supplier_no');
        $this->_getValue($where, $condition, 'supplier_name', 'like', 'name');
        $this->_getValue($where, $condition, 'erui_status');
        $this->_getValue($where, $condition, 'is_erui', 'bool');
        if ($is_Chain) {
            $this->_getValue($where, $condition, 'erui_checked_at', 'between');
        } else {
            $this->_getValue($where, $condition, 'checked_at', 'between');
        }
        $this->_getValue($where, $condition, 'created_at', 'between');
        if ($is_Chain) {
            if (!empty($condition['erui_checked_name'])) {
                $employee_model = new EmployeeModel();
                $userids = $employee_model->getUseridsByUserName(trim($condition['erui_checked_name']));
                if ($userids) {
                    $where['erui_checked_by'] = ['in', $userids];
                } else {
                    $where['erui_checked_by'] = '-1';
                }
            }
        } else {
            if (!empty($condition['checked_name'])) {
                $employee_model = new EmployeeModel();
                $userids = $employee_model->getUseridsByUserName(trim($condition['checked_name']));
                if ($userids) {
                    $where['checked_by'] = ['in', $userids];
                } else {
                    $where['checked_by'] = '-1';
                }
            }
        }
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getList($condition = [], $order = " id desc") {
        $where = [];
        $this->_getcondition($condition, $where, false);
        list($offset, $size) = $this->_getPage($condition);
        $data = $this->field('id,supplier_no,serial_no,name,erui_status,checked_at,checked_by,'
                        . 'org_id')
                ->limit($offset, $size)
                ->where($where)
                ->order($order)
                ->select();
        $this->_setStatus($data);
        $this->_setCheckedName($data);

        return $data;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getListChain($condition = [], $order = " id desc") {
        $where = [];
        $this->_getcondition($condition, $where);
        list($offset, $size) = $this->_getPage($condition);
        $data = $this->field('id,supplier_no,serial_no,name,erui_status,erui_checked_at,erui_checked_by,supplier_level,'
                        . 'org_id,is_erui')
                ->limit($offset, $size)
                ->where($where)
                ->order($order)
                ->select();
        $this->_setEruiStatus($data);
        $this->_setEruiCheckedName($data);
        $this->_setOrgName($data);
        return $data;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getCount($condition = []) {
        $where = [];
        $this->_getcondition($condition, $where, false);
        $count = $this->field('id,supplier_no,serial_no,name,erui_status,erui_checked_at,erui_checked_by,supplier_level,'
                        . 'org_id,is_erui')
                ->where($where)
                ->count();
        return $count;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getCountChain($condition = []) {
        $where = [];
        $this->_getcondition($condition, $where);
        $count = $this->field('id,supplier_no,serial_no,name,erui_status,erui_checked_at,erui_checked_by,supplier_level,'
                        . 'org_id,is_erui')
                ->where($where)
                ->count();
        return $count;
    }

    /**
     * 获取处理状态
     * @param mix $data
     * @return
     * @author zyg
     */
    private function _setEruiStatus(&$data) {
        if ($data) {
            foreach ($data as $key => $item) {

                switch ($item['erui_status']) {
                    case self::ERUI_STATUS_CHECKING:
                        $item['erui_status'] = '待审核';
                        break;
                    case self::ERUI_STATUS_VALID:
                        $item['erui_status'] = '已通过';
                        break;
                    default :
                        $item['erui_status'] = '';
                        break;
                }
                if (empty($item['erui_checked_at'])) {
                    $item['erui_checked_at'] = '';
                }
                if (empty($item['org_id'])) {
                    $item['org_id'] = '';
                }
                if (empty($item['supplier_level'])) {
                    $item['supplier_level'] = '';
                }
                if (empty($item['erui_checked_by'])) {
                    $item['erui_checked_by'] = '';
                }
                if (empty($item['serial_no'])) {
                    $item['serial_no'] = '';
                }
                if (empty($item['supplier_no'])) {
                    $item['supplier_no'] = '';
                }
                $data[$key] = $item;
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
                    case 'APPLING':
                        $item['status'] = '待审核';
                        break;
                    case 'APPROVED':
                        $item['status'] = '已通过';
                        break;
                    case 'VALID':
                        $item['status'] = '已通过';
                        break;
                    case 'DRAFT':
                        $item['status'] = '暂存';
                        break;
                    default :
                        $item['status'] = '';
                        break;
                }
                if (empty($item['checked_at'])) {
                    $item['checked_at'] = '';
                }

                if (empty($item['checked_by'])) {
                    $item['checked_by'] = '';
                }
                if (empty($item['serial_no'])) {
                    $item['serial_no'] = '';
                }
                if (empty($item['supplier_no'])) {
                    $item['supplier_no'] = '';
                }
                $data[$key] = $item;
            }
        }
    }

    /**
     * 获取易瑞处理状态
     * @param mix $data
     * @return
     * @author zyg
     */
    private function _setEruiCheckedName(&$data) {
        if ($data) {
            $erui_checked_bys = [];
            foreach ($data as $item) {
                if ($item['erui_checked_by']) {
                    $erui_checked_bys[] = $item['erui_checked_by'];
                }
            }
            if ($erui_checked_bys) {
                $employee_model = new EmployeeModel();
                $usernames = $employee_model->getUserNamesByUserids($erui_checked_bys);
            }
            foreach ($data as $key => $val) {
                if ($val['erui_checked_by'] && isset($usernames[$val['erui_checked_by']])) {
                    $val['erui_checked_name'] = $usernames[$val['erui_checked_by']];
                } else {
                    $val['erui_checked_name'] = '';
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
                if ($item['checked_by']) {
                    $erui_checked_bys[] = $item['checked_by'];
                }
            }
            if ($checked_bys) {
                $employee_model = new EmployeeModel();
                $usernames = $employee_model->getUserNamesByUserids($checked_bys);
            }
            foreach ($data as $key => $val) {
                if ($val['checked_by'] && isset($usernames[$val['checked_by']])) {
                    $val['checked_name'] = $usernames[$val['checked_by']];
                } else {
                    $val['checked_name'] = '';
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
                $orgs = $org_model->field('id,name')->where(['id' => $org_ids,
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
     * 批量更新供应商等级
     * @param mix $supplier_ids 供应商ID 数组
     * @param int $supplier_level 供应商等级
     * @return
     * @author zyg
     */
    public function batchUpdateLevel($supplier_ids, $supplier_level) {

        $where = ['deleted_flag' => 'N',
            'id' => ['in', $supplier_ids]
            , 'status' => ['in', ['APPROVED', 'VALID']]];
        $data['supplier_level'] = $supplier_level;
        return $this->where($where)->save($data);
    }

    /**
     * 批量更新供应商等级
     * @param int $supplier_id 供应商ID 数组
     * @param int $supplier_level 供应商等级
     * @return
     * @author zyg
     */
    public function Checked($supplier_id, $supplier_level, $is_erui = 'N') {

        $where = ['deleted_flag' => 'N',
            'id' => $supplier_id,
                // 'status' => ['in', ['APPROVED', 'VALID']]
        ];

        $info = $this->field('status')->where($where)->find();
        if (in_array($info['status'], ['APPROVED', 'VALID'])) {
            $data['supplier_level'] = $supplier_level;
            $data['is_erui'] = $is_erui === 'Y' ? 'Y' : 'N';
            $data['erui_status'] = self::ERUI_STATUS_VALID;
            $data['erui_checked_at'] = date('Y-m-d H:i:s');
            $data['erui_checked_by'] = defined('UID') ? UID : 0;
            return $this->where($where)->save($data);
        } elseif ($info) {
            jsonReturn($data, MSG::MSG_FAILED, '供应商审核未通过,不能进行供应链审核!');
        } else {
            jsonReturn($data, MSG::MSG_FAILED, '供应商不存在!');
        }
    }

}
