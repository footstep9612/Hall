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

    public function getSupplierList($data)
    {
        return $this->where($data)->field('bizline_supplier.*,bz.name as bizline_name')
            ->join('`erui2_operation`.`bizline` bz on bz.id=bizline_supplier.bizline_id', 'left')->select();
    }
    public function create_data($create= []) {
        if(isset($create['bizline_id'])){
            $arr['bizline_id'] = $create['bizline_id'];
        }
        if(isset($create['supplier_id'])){
            $arr['supplier_id'] = $create['supplier_id'];
        }
        if(isset($create['first_name'])){
            $arr['first_name'] = $create['first_name'];
        }
        if(isset($create['last_name'])){
            $arr['last_name'] = $create['last_name'];
        }
        if(isset($create['email'])){
            $arr['email'] = $create['email'];
        }
        if(isset($create['phone'])){
            $arr['phone'] = $create['phone'];
        }
        if(isset($create['quote_group_id'])){
            $arr['quote_group_id'] = $create['quote_group_id'];
        }
        if(isset($create['supply_level'])){
            $arr['supply_level'] = $create['supply_level'];
        }
        $arr['created_at'] = date("Y-m-d H:i:s");
        if(isset($create['created_by'])){
            $arr['created_by'] = $create['created_by'];
        }
        $arr['created_at'] = date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }
    public function deletes($where){
        if($where){
            $this->where($where)->delete();
        }
    }
}
