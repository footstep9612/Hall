<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SupplierManufacturer
 * @author  zhongyg
 * @date    2017-8-10 9:45:19
 * @version V2.0
 * @desc   
 */
class SupplierManufacturerModel extends PublicModel {

    //put your code here
    protected $tableName = 'supplier_manufacturer';
    protected $dbName = 'erui2_supplier'; //数据库名称

//    protected $autoCheckFields = false;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 判断数据是否存在
     * @param array $where 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function Exits($where) {

        return $this->_exist($where);
    }

    /**
     * Description of 增
     * @param array $create 新增的数据
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function create_data($create = []) {

        $data = $this->create($create);
        unset($data['id']);
        $create['updated_at'] = date('Y-m-d H:i:s');
        $create['updated_by'] = UID;
        $this->add($data);
        return true;
    }

    /**
     * 修改数据
     * @param  array $update 
     * @return bool
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function update_data($update) {
        if (!isset($update['id']) || !$update['id']) {
            return false;
        } else {
            $where['id'] = $update['id'];
        }

        $flag = $this->where($where)->save($update);



        return $flag;
    }

}
