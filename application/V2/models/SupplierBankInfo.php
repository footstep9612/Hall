<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class SupplierBankInfoModel extends PublicModel {

  protected $tableName = 'supplier_bank_info';
  protected $dbName = 'erui2_supplier'; //数据库名称

  public function __construct($str = '') {
    parent::__construct();
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
        if (isset($create['bank_name'])) {
            $arr['bank_name'] = $create['bank_name'];
        }
        if (isset($create['address'])) {
            $arr['address'] = $create['address'];
        }
        if (isset($create['bank_account'])) {
            $arr['bank_account'] = $create['bank_account'];
        }
        if (isset($create['created_by'])) {
            $arr['created_by'] = $create['created_by'];
        }
        $arr['created_at']= date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }
    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author
     */
    public function update_data($create = [],$where)
    {
        if (isset($create['bank_name'])) {
            $arr['bank_name'] = $create['bank_name'];
        }
        if (isset($create['address'])) {
            $arr['address'] = $create['address'];
        }
        if (isset($create['bank_account'])) {
            $arr['bank_account'] = $create['bank_account'];
        }
        if (!empty($where)&&isset($arr)) {
            return $this->where($where)->save($arr);
        } else {
            return false;
        }
    }
}
