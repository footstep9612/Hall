<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author klp
 */
class SupplierAddressModel extends PublicModel
{

    protected $tableName = 'supplier_address';
    protected $dbName = 'erui2_supplier'; //数据库名称

    public function __construct($str = '')
    {

        parent::__construct();
    }

    public function info($data = [])
    {
        if(!empty($data['supplier_id'])){
            $row = $this->where(['supplier_id' => $data['supplier_id'] ])
                ->select();
            return $row;
        }else{
            return false;
        }
    }
    /**
     * 修改数据(更新)
     * @param  int $id id
     * @return bool
     * @author
     */
    public function update_data($data, $where)
    {

        if (isset($data['address'])) {
            $arr['address'] = $data['address'];
        }
        if (!empty($where)&&isset($arr)) {
            $info = $this->where($where)->find();
            if(!$info){
                $arr['supplier_id']=$where['supplier_id'];
                $this->create_data($arr);
            }else{
                return $this->where($where)->save($arr);
            }
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author
     */
    public function create_data($create = [])
    {
        if (isset($create['supplier_id'])) {
            $arr['supplier_id'] = $create['supplier_id'];
        }
        if (isset($create['address'])) {
            $arr['address'] = $create['address'];
        }
        if (isset($create['created_by'])) {
            $arr['created_by'] = $create['created_by'];
        }
        $arr['created_at'] = Date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }
}


