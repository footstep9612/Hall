<?php

class TemporarySupplierRelationModel extends PublicModel
{
    /**
     * @var string
     */
    protected $dbName = 'erui_supplier';

    /**
     * @var string
     */
    protected $tableName = 'temporary_supplier_relation';

    /**
     * TemporarySupplierModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function setRelation(array $condition, $user)
    {
        $temporarySupplierModel = new TemporarySupplierModel();
        $temporarySupplier = $temporarySupplierModel->byId($condition['id']);
        //一个临时供应商只能跟一个正式供应商关联
        $flag = $this->where([
            'temporary_supplier_id' => $condition['id'],
            //'supplier_no' => $condition['supplier_no'],
        ])->delete();

        $temporarySupplierModel->where(['id' => $condition['id'], 'deleted_flag'=> 'N'])->save([
            'is_relation' => 'N',
            'relations_count' => 0
        ]);

        $this->startTrans();
        $result = $this->add($this->create([
            'temporary_supplier_id' => $temporarySupplier['id'],
            'temporary_supplier_no' => $temporarySupplier['supplier_no'],
            'supplier_id' => $condition['supplier_id'],
            'supplier_no' => $condition['supplier_no'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $user
        ]));

        if ($result) {
            $this->commit();
            $temporarySupplierModel->where(['id' => $condition['id'], 'deleted_flag'=> 'N'])->save([
                'is_relation' => 'Y',
                'relations_count' => $temporarySupplier['relations_count'] + 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $user
            ]);
        }

        $this->rollback();
        return $result;
    }

    public function checkTemporaryRegularRelationBy($temporarySupplier, $regularSupplier)
    {
        return $this->where(['temporary_supplier_id' => $temporarySupplier, 'supplier_id' => $regularSupplier])->count();
    }
}