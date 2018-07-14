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

    /**
     * 获取供应商产品列表
     * @param array $condition 筛选条件
     * @return mixed
     */
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

    /**
     * 获取数据总条数
     * @param array $condition
     * @return mixed
     */
    public function getCount(array $condition = [])
    {

        $where = array_merge($this->defaultConditions(), $this->setConditions($condition));
        return $this->alias('a')->join($this->joinSupplier, 'LEFT')->where($where)->count();

    }

    public function getDetail($id)
    {
        $field = ['spu', 'name', 'material_cat_no', 'brand', 'warranty', 'tech_paras', 'description', 'status'];

        return $this->alias('a')->where(array_merge($this->defaultConditions(), ['id' => $id]))->field($field)->find();
    }

    public function reviewList(array $conditions = [])
    {
        $where = array_merge($this->defaultConditions(), [
            'id' => ['in', $conditions['id']]
        ]);

        $field = ['id', 'status'];

        return $this->alias('a')->where($where)->field($field)->select();
    }

    public function checkIsApproving($product)
    {
        return $this->where(['id' => $product])->getField('status');
    }

    public function updateStatusFor($product, $status, $user)
    {
        return $this->where(['id' => $product])->save([
            'status' => $status,
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s'),
            'checked_by' => $user,
            'checked_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 设置筛选条件
     * @param array $condition
     * @return array
     */
    public function setConditions(array $condition = [])
    {
        $conditions = [];

        //供应商名称
        if (!empty($condition['supplier_name'])) {
            $conditions['s.name'] = ['like', '%' . $condition['supplier_name'] . '%'];
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
        }else {
            $conditions['a.status'] = ['neq', 'DRAFT'];
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

    /**
     * 模型默认调操作条件
     * @return array
     */
    protected function defaultConditions()
    {
        return [
            'a.deleted_flag' => 'N'
        ];
    }

    /**
     * 模型默认查询字段
     * @return array
     */
    protected function defaultFields()
    {
        return ['a.id', 'a.supplier_id', 'a.lang', 'a.name', 'a.material_cat_no', 'a.spu', 'a.brand', 'a.status', 'a.checked_at'];
    }

    /**
     * 设置分页
     * @param array $condition
     * @return array
     */
    protected function setPage(array $condition = [])
    {
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        return [$currentPage, $pageSize];
    }

}