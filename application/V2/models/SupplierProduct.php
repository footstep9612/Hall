<?php


class SupplierProductModel extends PublicModel
{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_product';

    protected $joinSupplier = 'erui_supplier.supplier s ON a.supplier_id = s.id';

    public function __construct()
    {
        parent::__construct();
    }

    public function getList(array $condition = [])
    {
        list($currentPage, $pageSize) = $this->setPage($condition);

        $joinFields = ['s.name supplier_name'];

        $fields = array_merge($this->defaultFields(), $joinFields);
        $where = array_merge($this->defaultConditions(), $this->setConditions($condition));


        return $this->alias('a')
                    ->join($this->joinSupplier, 'LEFT')
                    ->field($fields)
                    ->where($where)
                    ->page($currentPage, $pageSize)
                    ->order('a.id desc')
                    ->select();
    }

    public function getCount(array $condition = [])
    {

        $where = array_merge($this->defaultConditions(), $this->setConditions($condition));
        return $this->alias('a')->join($this->joinSupplier, 'LEFT')->where($where)->count();

    }

    public function setConditions(array $condition = [])
    {
        $conditions = [];

        //供应商名称
        if (!empty($condition['supplier_name'])) {
            $conditions['s.name'] = ['like', '%' . $condition['name'] . '%'];
        }

        //产品名称
        if (!empty($condition['name'])) {
            $conditions['a.name'] = ['like', '%' . $condition['name'] . '%'];
        }

        //产品分类
        if (!empty($condition['material_cat_no'])) {
            $conditions['a.material_cat_no'] = $condition['material_cat_no'];
        }

        //状态
        if (!empty($condition['status'])) {
            $conditions['a.status'] = $condition['status'];
        }

        //提交时间
        if (!empty($condition['check_start_time']) && !empty($condition['check_end_time'])) {
            $conditions['a.checked_at'] = [
                ['egt', $condition['check_start_time']],
                ['elt', $condition['check_end_time'] . ' 23:59:59']
            ];
        }

        return $conditions;
    }

    public function defaultConditions()
    {
        return [
            'a.deleted_flag' => 'N'
        ];
    }

    protected function defaultFields()
    {
        return ['a.id', 'a.supplier_id', 'a.lang', 'a.name', 'a.material_cat_no', 'a.spu', 'a.brand', 'a.status', 'a.checked_at'];
    }

    protected function setPage(array $condition = [])
    {
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        return [$currentPage, $pageSize];
    }

}