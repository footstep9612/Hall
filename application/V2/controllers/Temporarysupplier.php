<?php

/**
 * Class TemporarySupplier
 * @desc 临时供应API
 */
class TemporarySupplierController extends PublicController
{

    private $temporarySupplier;

    public function init()
    {
        parent::init();
        $this->temporarySupplier = new TemporarySupplierModel();
    }

    /**
     * @desc 临时供应商列表
     * @author 买买提
     * @time 2018--4-16
     */
    public function listAction()
    {

        $request = $this->validateRequestParams();
        $list = $this->temporarySupplier->getList($request);

        foreach ($list as &$item) {
            $item['relation_supplier_name'] = $this->temporarySupplier->relationSupplierById($item['id']);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => [
                'data' => $list,
                'total' => $this->temporarySupplier->getCount($request),
                'relations_count' => $this->temporarySupplier->getCount(['is_relation' => 'Y', 'deleted_flag' => 'N'], false),
                'un_relations_count' => $this->temporarySupplier->getCount(['is_relation' => 'N', 'deleted_flag' => 'N'], false),
            ]
        ]);

    }

    /**
     * @desc 临时供应商详情
     * @author 买买提
     * @time 2018--4-16
     */
    public function detailAction()
    {
        $request = $this->validateRequestParams('id');
        $data = $this->temporarySupplier->byId($request['id']);
        $data['created_by'] = (new EmployeeModel)->getNameByid($data['created_by'])['name'];
        $data['sku'] = $this->temporarySupplier->skuById($request['id']);

        $this->jsonReturn($data);
    }

    /**
     * @desc 关联供应商
     * @desc 正式供应商关联到临时供应商 hasMany的关系
     * @author 买买提
     * @time 2018--4-16
     */
    public function relationAction()
    {
        $request = $this->validateRequestParams('id,supplier_id,supplier_no');
        $response = (new TemporarySupplierRelationModel)->setRelation($request, $this->user['id']);
        $this->jsonReturn($response);
    }
}