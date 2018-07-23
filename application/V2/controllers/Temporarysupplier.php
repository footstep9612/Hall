<?php

/**
 * Class TemporarySupplier
 * @desc 临时供应API
 */
class TemporarySupplierController extends PublicController {

    /**
     * @var 模型
     */
    private $temporarySupplier;

    public function init() {
        parent::init();
        $this->temporarySupplier = new TemporarySupplierModel();
    }

    /**
     * @desc 临时供应商列表
     * @author 买买提
     * @time 2018--4-16
     */
    public function listAction() {

        $request = $this->validateRequestParams();
        $list = $this->temporarySupplier->getList($request);

        foreach ($list as &$item) {
            //已经关联的供应商
            $item['relation_supplier_name'] = $this->temporarySupplier->relationSupplierById($item['id']);

            $item['quotations_count'] = $this->temporarySupplier->temporarySupplierInquiryCountsBy($item['id']);
            //事业部
            $item['org_name'] = (new OrgModel)->getNameById($item['org_id'], '');
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => [
                'total' => $this->temporarySupplier->getCount($request),
                'relations_count' => $this->temporarySupplier->getCount(['is_relation' => 'Y']),
                'un_relations_count' => $this->temporarySupplier->getCount(['is_relation' => 'N']),
                'data' => $list,
            ]
        ]);
    }

    /**
     * @desc 临时供应商详情
     * @author 买买提
     * @time 2018--4-16
     */
    public function detailAction() {
        $request = $this->validateRequestParams('id');
        $data = $this->temporarySupplier->byId($request['id']);
        $data['created_by'] = (new EmployeeModel)->getNameByid($data['created_by'])['name'];
        list($sku, $total) = $this->temporarySupplier->skuById($request['id'], $request);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data,
            'sku' => $sku,
            'total' => $total
        ]);
    }

    /**
     * @desc 关联供应商
     * @desc 正式供应商关联到临时供应商 hasMany的关系
     * @author 买买提
     * @time 2018--4-16
     */
    public function relationAction() {
        $request = $this->validateRequestParams('id,supplier_id');
        $response = (new TemporarySupplierRelationModel)->setRelation($request, $this->user['id']);
        $this->jsonReturn($response);
    }

    /**
     * @desc 取消关联
     * @desc 正式供应商关联到临时供应商 hasMany的关系
     * @author 买买提
     * @time 2018-4-23
     */
    public function unRelationAction() {
        $request = $this->validateRequestParams('id,supplier_id,supplier_no');
        $response = (new TemporarySupplierRelationModel)->unRelation($request['id'], $this->user['id']);
        $this->jsonReturn($response);
    }

    /**
     * @desc 关联供应商(正式供应商列表)
     * @author 买买提
     * @time 2018--4-17
     */
    public function regularAction() {
        $request = $this->validateRequestParams();

        $isErui = (new InquiryModel)->getDeptOrgId($this->user['group_id'], ['in', ['erui', 'elg', 'eub']]);
        if (!$isErui) {
            // 非易瑞事业部门的看他所在事业部和易瑞的
            $orgUb = (new InquiryModel)->getDeptOrgId($this->user['group_id'], 'ub');
            $request['org_id'] = $orgUb ? array_merge((new OrgModel)->where(['org_node' => 'erui', 'deleted_flag' => 'N'])->getField('id', true), $orgUb) : [];
        }

        // 开发人
        if ($request['developer'] != '') {
            $request['agent_ids'] = (new EmployeeModel)->getUserIdByName($request['developer']) ?: [];
        }

        $suppliers = $this->temporarySupplier->getRegularSupplierList($request);

        foreach ($suppliers as &$supplier) {

            // 开发人
            $supplier['dev_name'] = (new EmployeeModel)->getUserNameById($supplier['agent_id']);
            //创建人
            $supplier['created_by'] = (new EmployeeModel)->getUserNameById($supplier['created_by']);
            // 供货范围
            $supplier['material_cat'] = (new SupplierMaterialCatModel)->getCatBySupplierId($supplier['id']);
            //是否关联供应商
            $supplier['is_relation'] = (new TemporarySupplierRelationModel)->checkTemporaryRegularRelationBy($request['temporary_supplier_id'], $supplier['id']) ? 'Y' : 'N';
        }

        if (count($suppliers) == 1) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '成功',
                'total' => count($suppliers),
                'data' => $suppliers
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'total' => $this->temporarySupplier->getRegularCount($request),
            'data' => $suppliers
        ]);
    }

}
