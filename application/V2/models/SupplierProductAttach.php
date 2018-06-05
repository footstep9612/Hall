<?php

class SupplierProductAttachModel extends PublicModel
{
    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_product_attach';

    private $defaultConditions = ['deleted_flag' => 'N'];

    public function __construct()
    {
        parent::__construct();
    }

    public function getList(array $request = [])
    {
        $field = ['attach_type', 'attach_name', 'attach_url', 'sort_order', 'default_flag', 'status'];
        $where = array_merge($this->defaultConditions, ['spu' => $request['spu'] ]);

        return $this->where($where)->field($field)->order('sort_order desc')->select();
    }

}