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
    protected $listFields = 'a.id,a.name,a.is_relation,a.relations_count,e.name created_by,a.quotations_count,o.name org_name,a.registration_time';

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

    public function getCount(array $condition, $is_join=true)
    {
        if ($is_join){
            return $this->alias('a')->where($this->setCondition($condition, true))->count();
        }
        return $this->where($condition)->count();
    }

    private function setCondition(array $condition, $getCount=false)
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

        if (!$getCount) {

            $where['o.deleted_flag'] = 'N';

            $where['e.deleted_flag'] = 'N';
        }

        return $where;
    }

}