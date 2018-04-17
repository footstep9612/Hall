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
}