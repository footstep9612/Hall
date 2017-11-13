<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SupplierExtraInfo
 * @author  zhongyg
 * @date    2017-11-11 16:31:18
 * @version V2.0
 * @desc
 */
class SupplierExtraInfoModel extends PublicModel {

    //put your code here
    protected $tableName = 'supplier_extra_info';
    protected $dbName = 'erui_supplier'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取其他信息
     * @param int $supplier_id 供应商ID
     * @return
     * @author zyg
     */
    public function getExtrainfo($supplier_id) {

        return $this->where(['supplier_id' => $supplier_id])
                        ->field('sign_agreement_flag,providing_sample_flag,'
                                . 'distribution_products,distribution_amount,stocking_place,info_upload_flag,photo_upload_flag,sign_agreement_time')
                        ->select();
    }

}
