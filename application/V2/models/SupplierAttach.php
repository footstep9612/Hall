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
class SupplierAttachModel extends PublicModel
{

    protected $tableName = 'supplier_attach';
    protected $dbName = 'erui2_supplier'; //数据库名称

    public function __construct($str = '')
    {

        parent::__construct();
    }
    /**
     * 修改数据(更新)
     * @param  int $id id
     * @return bool
     * @author
     */
    public function update_data($data, $where)
    {

        if (isset($create['license_attach_url'])) {
            $arr['license_attach_url'] = $create['license_attach_url'];
        }
        if (isset($create['attach_name'])) {
            $arr['attach_name'] = $create['attach_name'];
        }
        if (!empty($where)) {
            return $this->where($where)->save($arr);
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
        if (isset($create['license_attach_url'])) {
            $arr['license_attach_url'] = $create['license_attach_url'];
        }
        if (isset($create['attach_name'])) {
            $arr['attach_name'] = $create['attach_name'];
        }
        if (isset($create['created_by'])) {
            $arr['created_by'] = $create['created_by'];
        }
        $arr['created_at']= date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }
    public function deleteall($create = [])
    {
        if ($create) {
            return $data = $this->where($create)->delete();
        }
    }
    public function info($data = [])
    {
        if(!empty($data['supplier_id'])){
            $arr['supplier_id'] =$data['supplier_id'];

            if(!empty($data['attach_group'])){
                $arr['attach_group'] =$data['attach_group'];
            }
            $arr['deleted_flag'] ='N';
            $row = $this->where($arr)
                ->select();
            return $row;
        }else{
            return false;
        }
    }

}


