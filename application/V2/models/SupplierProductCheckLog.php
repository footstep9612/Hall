<?php

/**
 * 供应商产品审核记录
 * Class SupplierProductCheckLogModel
 */
class SupplierProductCheckLogModel extends PublicModel
{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_product_check_log';

    public function __construct()
    {
        parent::__construct();
    }

    public function createReviewLogFor($product, $user, $options = [])
    {
        $productInfo = (new SupplierProductModel)->where(['id' => $product])->find();

        $data = [];

        $data['status'] = 'PASS';

        if (!empty($options)) {
            $data['remarks'] = $options['remarks'];
            $data['status'] = 'REJECTED';
        }

        return $this->add($this->create(array_merge($data, [
            'supplier_id' => $productInfo['supplier_id'],
            'spu' => $productInfo['spu'],
            'lang' => $productInfo['lang'],
            'approved_by' => $user,
            'approved_at' => date('Y-m-d H:i:s')
        ])));

    }
}