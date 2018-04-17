<?php

/**
 * Class TemporarySupplierModel
 * @desc 临时供应商Model
 *
 *
 */
class TemporarySupplierModel extends PublicModel
{

    /**
     * @var string
     */
    protected $dbName = 'erui_supplier';

    /**
     * @var string
     */
    protected $tableName = 'temporary_supplier';

    /**
     * @var string
     */
    protected $listFields = 'a.id,a.name,a.name_en,a.is_relation,a.relations_count,a.created_by created_by_id,e.name created_by,a.quotations_count,a.org_id,o.name org_name,a.registration_time';

    /**
     * @var string
     */
    protected $joinOrgTable = 'erui_sys.org o ON a.org_id=o.id';

    /**
     * @var string
     */
    protected $joinEmployyeTable = 'erui_sys.employee e ON a.created_by=e.id';

    /**
     * @var string
     */
    protected $joinTable1 = 'erui_sys.org b ON a.org_id = b.id';
    protected $joinTable4 = 'erui_supplier.supplier_extra_info e ON a.id = e.supplier_id ';
    protected $joinTable5 = 'erui_supplier.supplier_agent f ON a.id = f.supplier_id AND f.agent_type = \'DEVELOPER\'';
    protected $joinField = 'a.id,a.supplier_no,a.name,a.created_by, b.name AS org_name, f.agent_id';

    /**
     * TemporarySupplierModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function byId($id)
    {
        return $this->where(['id' => $id, 'deleted_flag' => 'N'])->find();
    }

    public function skuById($id)
    {
        $fields = 'i.name,i.name_zh,i.qty,i.unit,i.brand,i.model,i.remarks,q.purchase_unit_price';
        $where = [
            'a.id' => $id,
            'a.deleted_flag' => 'N',
            'q.deleted_flag' => 'N',
            'i.deleted_flag' => 'N',
        ];

        return $this->alias('a')
            ->join('erui_rfq.quote_item q ON a.id=q.supplier_id')
            ->join('erui_rfq.inquiry_item i ON q.inquiry_item_id=i.id')
            ->field($fields)
            ->where($where)
            ->select();
    }

    public function getList(array $condition=[])
    {

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $where = $this->setCondition($condition);

        $data = $this->alias('a')
                    ->join($this->joinOrgTable, 'LEFT')
                    ->join($this->joinEmployyeTable, 'LEFT')
                    ->where($where)
                    ->field($this->listFields)
                    ->page($currentPage, $pageSize)
                    ->select();

        return $data;
    }


    public function getCount(array $condition)
    {
        return $this->alias('a')
            ->join($this->joinOrgTable, 'LEFT')
            ->join($this->joinEmployyeTable, 'LEFT')
            ->where($this->setCondition($condition))
            ->field($this->listFields)
            ->count();
    }

    private function setCondition(array $condition)
    {
        //公司名称
        if (!empty($condition['name'])) {
            $where['a.name'] = ['like', '%' . $condition['name'] . '%'];
        }
        //状态
        if (!empty($condition['is_relation'])) {
            $where['a.is_relation'] = $condition['is_relation'];
        }
        //创建人
        if (!empty($condition['created_by'])) {
            $where['a.created_by'] = (new EmployeeModel)->getUserIdByName($condition['created_by']);
        }
        //注册时间
        if (!empty($condition['create_start_time']) && !empty($condition['create_end_time'])) {
            $where['a.created_at'] = [
                ['egt', $condition['create_start_time']],
                ['elt', $condition['create_end_time'] . ' 23:59:59']
            ];
        }
        //删除标识
        $where['a.deleted_flag'] = 'N';

        $where['o.deleted_flag'] = 'N';

        $where['e.deleted_flag'] = 'N';

        return $where;
    }

    public function relationSupplierById($id)
    {
        $temporarySupplier = (new TemporarySupplierRelationModel)->where(['temporary_supplier_id' => $id])->find();
        return (new SupplierModel)->where(['supplier_no' => $temporarySupplier['supplier_no'], 'deleted_flag' => 'N'])->getField('name');
    }

    public function setDeleteWithRelationBy($id, $user)
    {
        $this->where(['id' => $id])->save([
            'deleted_flag' => 'Y',
            'is_relation' => 'Y',
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        (new TemporarySupplierRelationModel)->where(['temporary_supplier_id' => $id])->save([
            'deleted_flag' => 'Y',
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

    }

    public function getRegularSupplierList(array $condition)
    {
        $where = $this->setRegularCondition($condition);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $supplier = new SuppliersModel();

        return $supplier->alias('a')
            ->join($this->joinTable1, 'LEFT')
            ->join($this->joinTable5, 'LEFT')
            ->join($this->joinTable4, 'LEFT')
            ->field($this->joinField)
            ->where($where)
            ->page($currentPage, $pageSize)
            ->order('a.id DESC')
            ->select();

    }

    public function getRegularCount(array $condition)
    {
        $where = $this->setRegularCondition($condition);
        $supplier = new SuppliersModel();

        return $supplier->alias('a')
            ->join($this->joinTable1, 'LEFT')
            ->join($this->joinTable5, 'LEFT')
            ->join($this->joinTable4, 'LEFT')
            ->field($this->joinField)
            ->where($where)
            ->order('a.id DESC')
            ->count();
    }

    private function setRegularCondition(array $condition)
    {
        $where['a.deleted_flag'] = 'N';
        $where['a.status'] = ['neq', 'OVERDUE'];

        if (!empty($condition['id'])) {
            $where['a.id'] = $condition['id'];
        }

        if (!empty($condition['name'])) {
            $where['a.name'] = ['like', '%' . $condition['name'] . '%'];
        }

        if (!empty($condition['status'])) {
            $where['a.status'] = [['eq', $condition['status']], $where['a.status']];
        }

        if (isset($condition['agent_ids'])) {
            $where['f.agent_id'] = ['in', $condition['agent_ids'] ? : ['-1']];
        }
        return $where;
    }
}