<?php

/**
 * 产品线供应商
 * Class BizlineSupplierModel
 * @author 买买提
 */
class BizlineSupplierModel extends PublicModel
{
    /**
     * 数据库名称
     * @var string
     */
    protected $dbName = 'erui2_operation';

    /**
     * 数据表名称
     * @var string
     */
    protected $tableName = 'bizline_supplier';

    /**
     * 获取当前产品线对应的供应商
     * @param $bizline_id   产品线id
     *
     * @return mixed
     */
    public function getList($bizline_id)
    {
        $field = ['bizline_id','supplier_id','first_name','last_name','phone'];

        //TODO 这里后期关联到供应商表获取供应商相关信息
        return $this->where(['bizline_id'=>$bizline_id])->field($field)->select();
    }
}
