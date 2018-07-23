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
    const STATUS_DRAFT = 'DRAFT'; //待报审；
    const STATUS_CHECKING = 'APPROVING'; //审核；
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
    protected function _getcondition($condition = [], &$where = [], $is_Chain = true, $is_report = false) {
        if ($is_report) {
            $where = [
                'deleted_flag' => 'N',
                'status' => ['in', ['REVIEW', 'APPROVED', 'INVALID', 'APPROVING']]
            ];
        } elseif ($is_Chain) {
            $where = [
                'deleted_flag' => 'N',
                'status' => 'APPROVED',
                //'source' => 'BOSS'
            ];
        } else {
            $where = [
                'deleted_flag' => 'N',
                //'status' => ['in', ['APPROVED', 'INVALID', 'APPROVING']]
                'status' => 'APPROVING',
                //'source' => 'BOSS'
            ];
        }
        $this->_getValue($where, $condition, 'id');
        $this->_getValue($where, $condition, 'source');
        $this->_getValue($where, $condition, 'supplier_no');
        $this->_getValue($where, $condition, 'supplier_name', 'like', 'name');
        if (!empty($condition['created_at_end'])) {
            $condition['created_at_end'] = date('Y-m-d H:i:s', strtotime($condition['created_at_end']) + 86399);
        }
        $this->_getValue($where, $condition, 'created_at', 'between');
        $employee_model = new EmployeeModel();
        $supplierAgentModel = new SupplierAgentModel();
        $supplierMaterialCatModel = new SupplierMaterialCatModel();
        // 开发人
        if (!empty($condition['developer'])) {
            $developerIds = $employee_model->getUserIdByName($condition['developer']);
            $supplierIds = $developerIds ? ($supplierAgentModel->getSupplierIdsByUserIds($developerIds) ?: []) : [];
            $where['id'] = ['in', $supplierIds ?: ['-1']];
        }
        // 创建人
        if (!empty($condition['created_name'])) {
            $where['created_by'] = ['in', $employee_model->getUserIdByName($condition['created_name']) ?: ['-1']];
        }
        // 供货范围
        if (!empty($condition['cat_name'])) {
            $catSupplierIds = $supplierMaterialCatModel->getSupplierIdsByCat($condition['cat_name']) ?: [];
            if (isset($supplierIds)) {
                $catSupplierIds = array_intersect($catSupplierIds, $supplierIds);
            }
            $where['id'] = ['in', $catSupplierIds ?: ['-1']];
        }
        $this->_getValue($where, $condition, 'supplier_level');
        if ($is_Chain) {
            if (isset($condition['org_id'])) {
//                $map1['org_id'] = ['in', $condition['org_id'] ?: ['-1']];
//                $map1[] = 'org_id is null';
//                $map1['_logic'] = 'or';
//                $where['_complex'] = $map1;
                $where['org_id'] = ['in', $condition['org_id'] ?: ['-1']];
            }
            $this->_getValue($where, $condition, 'erui_status');
            $this->_getValue($where, $condition, 'is_erui', 'bool');
            if (!empty($condition['erui_checked_at_end'])) {
                $condition['erui_checked_at_end'] = date('Y-m-d H:i:s', strtotime($condition['erui_checked_at_end']) + 86399);
            }
            $this->_getValue($where, $condition, 'erui_checked_at', 'between');
            if (!empty($condition['erui_checked_name'])) {
                $userids = $employee_model->getUseridsByUserName(trim($condition['erui_checked_name']));
                if ($userids) {
                    $where['erui_checked_by'] = ['in', $userids];
                } else {
                    $where['erui_checked_by'] = '-1';
                }
            }
        } else {
            if (isset($condition['org_id'])) {
                //                $map1['org_id'] = ['in', $condition['org_id'] ?: ['-1']];
//                $map1[] = 'org_id is null';
//                $map1['_logic'] = 'or';
//                $where['_complex'] = $map1;
                $where['org_id'] = ['in', $condition['org_id'] ?: ['-1']];
            }
            //  $where['status'] = 'DRAFT';

            if (!empty($condition['status']) && $condition['status'] == 'APPROVING') {
                $where['status'] = ['in', ['APPROVING', 'REVIEW']];
            } else {
                $this->_getValue($where, $condition, 'status');
            }
            if (!empty($condition['checked_at_end'])) {
                $condition['checked_at_end'] = date('Y-m-d H:i:s', strtotime($condition['checked_at_end']) + 86399);
            }
            $this->_getValue($where, $condition, 'checked_at', 'between');
            if (!empty($condition['checked_name'])) {
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
        $data = $this->field('id,source,org_id,supplier_no,serial_no,name,status,checked_at,checked_by,created_by,'
                        . 'created_at')
                ->limit($offset, $size)
                ->where($where)
                ->order($order)
                ->select();


        //  $this->_setStatus($data);
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
        $data = $this->field('id,supplier_no,serial_no,name,erui_status,erui_checked_at,erui_checked_by,supplier_level,created_by,'
                        . 'org_id,is_erui,created_at')
                ->limit($offset, $size)
                ->where($where)
                ->order($order)
                ->select();
        //   $this->_setEruiStatus($data);
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
        $count = $this->where($where)
                ->count();
        return $count;
    }

    /**
     * 获取列表数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getReportCount($condition = []) {
        $where = [];
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getcondition($condition, $where, false, true);
        $count = $this->where($where)
                ->count();
        return $count;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getSupplierCount($condition = [], $is_report = false) {
        $where = ['deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'INVALID', 'APPROVING', 'REVIEW', 'DRAFT']]
        ];
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getcondition($condition, $where, false, $is_report);
        $count = $this->where($where)
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
        $count = $this->where($where)
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
                    case 'REVIEW':
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
            $employee_model = new EmployeeModel();
            $supplierQualificationModel = new SupplierQualificationModel();
            $erui_checked_bys = [];
            foreach ($data as $item) {
                if ($item['erui_checked_by']) {
                    $erui_checked_bys[] = $item['erui_checked_by'];
                }
            }
            if ($erui_checked_bys) {
                $usernames = $employee_model->getUserNamesByUserids($erui_checked_bys);
            }
            foreach ($data as $key => $val) {
                if ($val['erui_checked_by'] && isset($usernames[$val['erui_checked_by']])) {
                    $val['erui_checked_name'] = $usernames[$val['erui_checked_by']];
                } else {
                    $val['erui_checked_name'] = '';
                }
                $val['created_name'] = $employee_model->getUserNameById($val['created_by']);
                $count = $supplierQualificationModel->getExpiryDateCount($val['id']);
                $val['expiry_date'] = $count > 0 && $count <= 30 ? "剩{$count}天到期" : '';
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
            $employee_model = new EmployeeModel();
            $supplierQualificationModel = new SupplierQualificationModel();
            $supplierAgentModel = new SupplierAgentModel();
            $supplierMaterialCatModel = new SupplierMaterialCatModel();
            $checked_bys = [];
            foreach ($data as $item) {
                if ($item['checked_by']) {
                    $checked_bys[] = $item['checked_by'];
                }
            }
            if ($checked_bys) {
                $usernames = $employee_model->getUserNamesByUserids($checked_bys);
            }
            foreach ($data as $key => $val) {
                if ($val['checked_by'] && isset($usernames[$val['checked_by']])) {
                    $val['checked_name'] = $usernames[$val['checked_by']];
                } else {
                    $val['checked_name'] = '';
                }
                $val['created_name'] = $employee_model->getUserNameById($val['created_by']);
                $count = $supplierQualificationModel->getExpiryDateCount($val['id']);
                $val['expiry_date'] = $count > 0 && $count <= 30 ? "剩{$count}天到期" : '';
                // 开发人
                $val['dev_name'] = $employee_model->getUserNameById($supplierAgentModel->getUserIdBySupplierId($val['id']));
                // 供货范围
                $val['material_cat'] = $supplierMaterialCatModel->getCatBySupplierId($val['id']);
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
     * 批量更新供应商等级
     * @param mix $supplier_ids 供应商ID 数组
     * @param int $supplier_level 供应商等级
     * @param string $supplier_note 供应商评级内容
     * @return
     * @author zyg
     */
    public function batchUpdateLevel($supplier_ids, $supplier_level, $supplier_note, $org_ids = []) {

        $where = ['deleted_flag' => 'N',
            'id' => ['in', $supplier_ids],
            'status' => ['in', ['APPROVED', 'VALID']]];
        $data['supplier_level'] = $supplier_level;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;

        $this->startTrans();
        $flag = $this->where($where)->save($data);
        if (!$flag) {
            $this->rollback();
            return FALSE;
        }
        $suppliers = $this->field('id,name,is_erui,org_id')->where($where)->select();
        /*
         * 更新日志
         */
        $supplierchecklog_model = new SupplierCheckLogModel();

        foreach ($suppliers as $supplier) {
            $condition['supplier_id'] = $supplier['id'];
            $condition['erui_member_flag'] = $supplier['is_erui'];
            $condition['org_id'] = in_array($supplier['org_id'], $org_ids) ? $supplier['org_id'] : $org_ids[0];
            $condition['rating'] = $supplier_level;
            $condition['group'] = 'RATING';
            $condition['note'] = $supplier_note;
            $flag_log = $supplierchecklog_model->create_data($condition);
            if (!$flag_log) {
                $this->rollback();
                jsonReturn(null, MSG::MSG_FAILED, '更新供应商【' . $supplier['name'] . '】评级日志失败!');
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 供应链审核
     * @param int $supplier_id 供应商ID 数组
     * @param int $supplier_level 供应商等级
     * @param string $supplier_note 供应商评级内容
     * @return
     * @author zyg
     */
    public function ChainChecked($supplier_id, $supplier_level, $supplier_note, $is_erui = 'N', $org_ids = []) {

        $where = ['deleted_flag' => 'N',
            'id' => $supplier_id,
        ];

        $info = $this->field('status,org_id')->where($where)->find();
        $data['supplier_level'] = $supplier_level;
        $data['is_erui'] = $is_erui === 'Y' ? 'Y' : 'N';
        $data['erui_status'] = self::ERUI_STATUS_VALID;
        $data['erui_checked_at'] = date('Y-m-d H:i:s');
        $data['erui_checked_by'] = defined('UID') ? UID : 0;
        if (empty($info['org_id']) && $org_ids) {
            $data['org_id'] = $org_ids[0];
        }
        $this->startTrans();
        $flag = $this->where($where)->save($data);
        if (!$flag) {
            $this->rollback();
            return FALSE;
        }
        $supplierchecklog_model = new SupplierCheckLogModel();
        $condition['status'] = 'APPROVED';
        $condition['erui_member_flag'] = $data['is_erui'];
        $condition['supplier_id'] = $supplier_id;
        $condition['org_id'] = in_array($info['org_id'], $org_ids) ? $info['org_id'] : $org_ids[0];
        $condition['rating'] = $supplier_level;
        $flag_log = $supplierchecklog_model->create_data($condition);
        $condition['group'] = 'RATING';
        $condition['note'] = $supplier_note;
        $rating_log = $supplierchecklog_model->create_data($condition);
        if ((!$flag_log || !$rating_log) && $this->error) {
            $this->rollback();
            jsonReturn(null, MSG::MSG_FAILED, $this->error);
        } elseif ((!$flag_log || !$rating_log)) {
            $this->rollback();
            jsonReturn(null, MSG::MSG_FAILED, '更新审核日志失败!');
        } else {
            $this->commit();
            return true;
        }
    }

    /**
     * 供应商审核
     * @param int $supplier_id 供应商ID 数组
     * @param int $supplier_level 供应商等级
     * @return
     * @author zyg
     */
    public function Checked($supplier_id, $status, $note = '', $org_ids = []) {

        $where = ['deleted_flag' => 'N',
            'id' => $supplier_id,
        ];

        $info = $this->field('status,org_id')->where($where)->find();
        if ($info['status'] == 'APPROVING') {
            $data['status'] = ($status == 'APPROVED' ? 'APPROVED' : 'INVALID');
            if ($status == 'APPROVED') {
                $data['erui_status'] = 'CHECKING';
            }
            $data['checked_at'] = date('Y-m-d H:i:s');
            $data['checked_by'] = defined('UID') ? UID : 0;
            if ($info['org_id'] && $data['status'] == 'APPROVED') {
                $org_model = new OrgModel();
                $orgInfo = $org_model->field('membership,org_node,name')->where(['id' => $info['org_id'], 'deleted_flag' => 'N'])->find();
                if (isset($orgInfo['org_node']) && $orgInfo['org_node'] === 'erui') {
                    $data['erui_checked_at'] = date('Y-m-d H:i:s');
                    $data['erui_checked_by'] = defined('UID') ? UID : 0;
                    $data['erui_status'] = 'VALID';
                    $data['is_erui'] = 'Y';
                }
            }
            $this->startTrans();
            $flag = $this->where($where)->save($data);
            if (!$flag) {
                $this->rollback();
                return FALSE;
            }
            $supplierchecklog_model = new SupplierCheckLogModel();
            $condition['status'] = $status == 'APPROVED' ? 'APPROVED' : 'INVALID';
            $condition['supplier_id'] = $supplier_id;
            $condition['org_id'] = in_array($info['org_id'], $org_ids) ? $info['org_id'] : $org_ids[0];
            if (isset($orgInfo['org_node']) && $orgInfo['org_node'] === 'erui' && $condition['status'] === 'APPROVED') {
                $data['erui_member_flag'] = 'Y';
            }
            $condition['note'] = $note;
            $flag_log = $supplierchecklog_model->create_data($condition);
            if (!$flag_log && $this->error) {
                $this->rollback();
                jsonReturn(null, MSG::MSG_FAILED, $this->error);
            } elseif (!$flag_log) {
                $this->rollback();
                jsonReturn(null, MSG::MSG_FAILED, '更新审核日志失败!');
            } else {
                $this->commit();
                return true;
            }
        } elseif ($info && $info['status'] === 'VALID') {
            jsonReturn($data, MSG::MSG_FAILED, '供应商已审核通过!');
        } elseif ($info && $info['status'] === 'APPROVED') {
            jsonReturn($data, MSG::MSG_FAILED, '供应商已审核通过!');
        } elseif ($info && $info['status'] === 'INVALID') {
            jsonReturn($data, MSG::MSG_FAILED, '已拒绝的供应商不能审核!');
        } elseif ($info && $info['status'] !== 'APPROVING') {
            jsonReturn($data, MSG::MSG_FAILED, '未报审的供应商不能审核!');
        } elseif ($info && $info['status'] !== 'REVIEW') {
            jsonReturn($data, MSG::MSG_FAILED, '未报审的供应商不能审核!');
        } elseif ($info && $info['status'] === 'DRAFT') {
            jsonReturn($data, MSG::MSG_FAILED, '暂存状态的供应商不能审核!');
        } else {
            jsonReturn($data, MSG::MSG_FAILED, '供应商不存在!');
        }
    }

    /**
     * 获取供应商基本信息
     * @param int $supplier_id 供应商ID
     * @return
     * @author zyg
     */
    public function getBaseInfo($supplier_id) {

        return $this->where(['id' => $supplier_id, 'deleted_flag' => 'N'])
                        ->field('supplier_type,name,name_en,country_bn,addrss,reg_capital,'
                                . 'reg_capital_cur_bn,logo,profile,id')
                        ->find();
    }

    /**
     * 获取开户行基本信息
     * @param int $supplier_id 供应商ID
     * @return
     * @author zyg
     */
    public function getBankInfo($supplier_id) {
        $supplier_bankmodel = new SupplierBankInfoModel();
        return $supplier_bankmodel->where(['supplier_id' => $supplier_id])
                        ->field('bank_name,bank_account,address,supplier_id')
                        ->find();
    }

    /**
     * 获取联系信息
     * @param int $supplier_id 供应商ID
     * @return
     * @author zyg
     */
    public function getContacts($supplier_id) {
        $supplier_contactmodel = new SupplierContactModel();
        return $supplier_contactmodel->where(['supplier_id' => $supplier_id])
                        ->field('contact_name,phone,email,station,title,remarks,supplier_id')
                        ->select();
    }

}
