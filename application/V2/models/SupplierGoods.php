<?php

class SupplierGoodsModel extends PublicModel
{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_goods';

    private $defaultConditions = ['deleted_flag' => 'N'];

    public function __construct()
    {
        parent::__construct();
    }

    public function getList(array $condition = [])
    {
        $field = ['spu', 'sku', 'model', 'exw_days', 'price', 'min_pack_unit', 'min_pack_naked_qty', 'nude_cargo_unit', 'min_order_qty'];
        $where = array_merge($this->defaultConditions, ['spu' => $condition['spu'] ]);

        return $this->where($where)->field($field)->order('id desc')->select();
    }

}