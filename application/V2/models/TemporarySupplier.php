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
    protected $tableName = 'supplier';

    /**
     * @var string
     */
    protected $listFields = 'a.id,a.name,a.name_en,a.created_by created_by_id,e.name created_by,a.created_at registration_time,a.org_id,a.is_relation';

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
     * 营销区域
     * @var array
     */
    protected $areas = ['Middle East', 'South America', 'North America', 'Africa', 'Pan Russian', 'Asia-Pacific', 'Europe'];

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

    public function skuById($id, $request)
    {
        $fields = 'i.name,i.name_zh,i.qty,i.unit,i.brand,i.model,i.remarks,q.purchase_unit_price';
        $where = [
            'a.id' => $id,
            'a.deleted_flag' => 'N',
            'q.deleted_flag' => 'N',
            'i.deleted_flag' => 'N',
        ];

        $currentPage = empty($request['currentPage']) ? 1 : $request['currentPage'];
        $pageSize = empty($request['pageSize']) ? 10 : $request['pageSize'];

        $sku = $this->alias('a')
            ->join('erui_rfq.quote_item q ON a.id=q.supplier_id')
            ->join('erui_rfq.inquiry_item i ON q.inquiry_item_id=i.id')
            ->field($fields)
            ->where($where)
            ->page($currentPage, $pageSize)
            ->select();
        $total = $this->alias('a')
            ->join('erui_rfq.quote_item q ON a.id=q.supplier_id')
            ->join('erui_rfq.inquiry_item i ON q.inquiry_item_id=i.id')
            ->field($fields)
            ->where($where)
            ->count('a.id');

        return [$sku, $total];
    }

    public function getList(array $condition=[])
    {

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $where = $this->setCondition($condition);

        $data = $this->alias('a')
                    ->join($this->joinEmployyeTable, 'LEFT')
                    ->where($where)
                    ->field($this->listFields)
                    ->page($currentPage, $pageSize)
                    ->order('a.id DESC')
                    ->select();
        return $data;
    }


    public function getCount(array $condition)
    {
        return $this->alias('a')
            ->join($this->joinEmployyeTable, 'LEFT')
            ->where($this->setCondition($condition))
            ->field($this->listFields)
            ->count();
    }

    private function setCondition(array $condition)
    {
        //公司名称
        if (!empty($condition['name'])) {
            if(preg_match("/^\d*$/",$condition['name'])) {
                $where['a.id'] = $condition['name'];
            }else {
                $where['a.name'] = ['like', '%' . $condition['name'] . '%'];
            }
        }

        //状态
        if (!empty($condition['is_relation']) && $condition['is_relation'] !='ALL') {
            $where['a.is_relation'] = $condition['is_relation'];
        }
        //创建人
        if (!empty($condition['created_by'])) {
            $where['a.created_by'] = (new EmployeeModel)->getUserIdByName($condition['created_by'])[0];
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

        $where['a.status'] = 'DRAFT';

        $where['e.deleted_flag'] = 'N';

        return $where;
    }

    /**
     * @desc 已关联的供应商名称
     * @param $id 供应商
     * @return mixed
     * @author 买买提
     * @time 2018-04-20
     */
    public function relationSupplierById($id)
    {
        $temporarySupplier = (new TemporarySupplierRelationModel)->where(['temporary_supplier_id' => $id, 'deleted_flag' => 'N'])->find();
        return (new SupplierModel)->where(['id' => $temporarySupplier['supplier_id'], 'deleted_flag' => 'N'])->getField('name');
    }

    /**
     * @desc 删除关联关系
     * @param $id 临时供应商
     * @param $user 用户
     * @author 买买提
     * @time 2018-04-20
     */
    public function setDeleteWithRelationBy($id, $user)
    {
        $this->where(['id' => $id])->save([
            'deleted_flag' => 'Y',
            'is_relation' => 'N',
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        (new TemporarySupplierRelationModel)->where(['temporary_supplier_id' => $id])->save([
            'deleted_flag' => 'Y',
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

    }

    /**
     * @desc 正式供应商列表
     * @param array $condition
     * @return mixed
     * @throws Exception
     * @author 买买提
     * @time 2018-04-20
     */
    public function getRegularSupplierList(array $condition)
    {

        if (!empty($condition['is_relation'])) {
            return $this->isRelationRegularSuppliers($condition);
        }

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

    /**
     * @desc 是否关关联供应商(正式=临时)
     * @param array $condition
     * @return mixed
     * @throws Exception
     * @author 买买提
     * @time 2018-04-20
     */
    private function isRelationRegularSuppliers(array $condition)
    {
        $supplier = new SuppliersModel();

        if ($condition['is_relation']=='Y') {
            $ts_id = $condition['temporary_supplier_id'];
        }

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $supplier->alias('a')
                        ->join('erui_supplier.temporary_supplier_relation ts ON a.id=ts.supplier_id', 'LEFT')
                        ->join($this->joinTable1, 'LEFT')
                        ->join($this->joinTable5, 'LEFT')
                        ->where([
                            'ts.temporary_supplier_id' => $ts_id,
                            'ts.deleted_flag' => 'N',
                            'a.deleted_flag' => 'N',
                        ])
                        ->field($this->joinField)
                        ->page($currentPage, $pageSize)
                        ->order('a.id DESC')
                        ->select();

    }

    /**
     * @desc 正式供应商统计
     * @param array $condition
     * @return mixed
     * @throws Exception
     * @author 买买提
     * @time 2018-04-20
     */
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

    /**
     * @desc 正式供应商筛选条件
     * @param array $condition
     * @return mixed
     * @author 买买提
     * @time 2018-04-20
     */
    private function setRegularCondition(array $condition)
    {
        $where['a.deleted_flag'] = 'N';
        $where['a.status'] = ['neq', 'DRAFT'];

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

    /**
     * @desc 供应商的报价次数
     * @param $temporarySupplierId 供应商(临时)
     * @return mixed
     * @throws Exception
     * @author 买买提
     * @time 2018-04-20
     */
    public function temporarySupplierInquiryCountsBy($temporarySupplierId)
    {
        $where = [
            'i.deleted_flag' => 'N',
            'i.status' => 'QUOTE_SENT',
            'i.quote_status' => 'COMPLETED',
            'fqi.supplier_id' => $temporarySupplierId,
            'mac.market_area_bn' => !empty($area) ? trim($area) : ['IN', $this->areas]
        ];

        $inquiry = new InquiryModel();
        return $inquiry->alias('i')
            ->join('erui_rfq.final_quote_item fqi ON i.id=fqi.inquiry_id')
            ->join('erui_operation.market_area_country mac ON i.country_bn=mac.country_bn')
            ->where($where)
            ->count("DISTINCT(i.id)");
    }
}