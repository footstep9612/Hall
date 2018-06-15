<?php

class SupplierGoodsAttrModel extends PublicModel
{
    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_goods_attr';

    public function __construct()
    {
        parent::__construct();
    }

    public function getAttr($request)
    {
        $attrs = $this->where(['spu' => $request['spu'], 'sku' => $request['sku'], 'deleted_flag' => 'N'])->getField('ex_goods_attrs');
        return json_decode($attrs,true);
    }
}