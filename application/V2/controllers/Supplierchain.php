<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 供应链列表
 * @author  zhongyg
 * @date    2017-11-10 13:32:36
 * @version V2.0
 * @desc
 */
class SupplierchainController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 供应链列表
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function listChainAction() {
        $condition = $this->getPut();

        $org_model = new OrgModel();
        $org_ids = $org_model->getOrgIdsById($this->user['group_id'], 'ERUI', null);

        if (!$org_ids) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('您不属于易瑞,没有查看权限!');
            $this->jsonReturn();
        }
        $supplier_model = new SupplierChainModel();
        $data = $supplier_model->getListChain($condition);
        if ($data) {
            $this->setvalue('count', $supplier_model->getCountChain($condition));
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('数据为空!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('获取失败!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 供应商列表
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function listAction() {
        $condition = $this->getPut();
        $supplier_model = new SupplierChainModel();
        /*
        $org_model = new OrgModel();
        $condition['org_id'] = $org_model->getOrgIdsById($this->user['group_id']);

        if (!$condition['org_id']) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('您不属于事业部或易瑞,没有查看权限!');
            $this->jsonReturn();
        }
        */
        if (!empty($condition['supplier_name'])) {
            if(preg_match("/^\d*$/",$condition['supplier_name'])) {
                $condition['id'] = $condition['supplier_name'];
                unset($condition['supplier_name']);
            }else {
                $condition['supplier_name'] = $condition['supplier_name'];
            }
        }

        $data = $supplier_model->getList($condition);

        foreach ($data as &$datum) {
            $datum['source'] = $datum['source'] == 'BOSS' ? 'BOSS' : '门户';
            $datum['org_name'] = (new OrgModel)->getNameById($datum['org_id'], '');
        }

        if ($data) {
            $this->setvalue('count', $supplier_model->getCount($condition));
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('数据为空!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('获取失败!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 批量更改供应商等级
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function batchUpdateLevelAction() {

        $supplier_ids = $this->getPut('supplier_id');
        $supplier_note = $this->getPut('note');
        $org_model = new OrgModel();
        $condition['org_id'] = $org_model->getOrgIdsById($this->user['group_id'], 'ERUI', null);

        if (!$condition['org_id']) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('您不属于易瑞,没有更改供应商等级权限!');
            $this->jsonReturn();
        }
        if (empty($supplier_ids)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择供应商!');
            $this->jsonReturn();
        }
        if (!is_array($supplier_ids)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID必须是数字数组!');
            $this->jsonReturn();
        }
        $supplier_level = $this->getPut('supplier_level');
        if (!$supplier_level) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商等级不能为空!');
            $this->jsonReturn();
        }
        if (!is_numeric($supplier_level) || $supplier_level > 4 || $supplier_level < 1) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商等级必须是大于等于1小于等于4的数字!');
            $this->jsonReturn();
        }
        $supplier_model = new SupplierChainModel();
        $data = $supplier_model->batchUpdateLevel($supplier_ids, $supplier_level, $supplier_note, $condition['org_id']);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 批量更改供应商等级
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function CheckedAction() {
        $supplier_level = $this->getPut('supplier_level');
        $supplier_note = $this->getPut('note');

        $org_model = new OrgModel();
        $org_ids = $org_model->getOrgIdsById($this->user['group_id'], 'ERUI', null);

        if (!$org_ids) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('您不属于易瑞,没有供应链审核权限!');
            $this->jsonReturn();
        }
        $supplier_id = $this->getPut('supplier_id');
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择供应商!');
            $this->jsonReturn();
        }


        if (!is_numeric($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID必须是数字!');
            $this->jsonReturn();
        }
        $is_erui = $this->getPut('is_erui');
        if (empty($is_erui)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择是否符合易瑞!');
            $this->jsonReturn();
        }

        $supplier_model = new SupplierChainModel();
        $supplier = $supplier_model->field(['supplier_level,erui_status,status,org_id'])->where(['id' => $supplier_id, 'deleted_flag' => 'N'])->find();
        if (!$supplier) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商不存在!');
            $this->jsonReturn();
        }

        if (!$supplier_level && !empty($supplier['supplier_level'])) {
            $supplier_level = $supplier['supplier_level'];
        }
//        if (!is_numeric($supplier_level) || $supplier_level > 4 || $supplier_level < 1) {
//            $this->setCode(MSG::ERROR_PARAM);
//            $this->setMessage('供应商等级必须是大于等于1小于等于4的数字!');
//            $this->jsonReturn();
//        }
        if (!in_array($supplier['status'], ['APPROVED', 'VALID'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('未通过供应商审核的供应商,不能进行供应链审核!!');
            $this->jsonReturn();
        }
        if ($supplier['erui_status'] == 'VALID') {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('已通过供应链审核,不需要重复审核!');
            $this->jsonReturn();
        }

        $data = $supplier_model->ChainChecked($supplier_id, $supplier_level, $supplier_note, $is_erui, $org_ids);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 公司基本信息
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function CompanyInfoAction() {

        $supplier_id = $this->getPut('supplier_id');
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择供应商!');
            $this->jsonReturn();
        }
        if (!is_numeric($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID必须是数字!');
            $this->jsonReturn();
        }
        $supplier_model = new SupplierChainModel();
        $data = $supplier_model->getBaseInfo($supplier_id);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
            $this->jsonReturn();
        }
    }

}
