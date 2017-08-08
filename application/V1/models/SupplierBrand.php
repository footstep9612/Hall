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
class SupplierBrandModel extends PublicModel {

    protected $tableName = 'supplier_brand';
    protected $dbName = 'erui_supplier'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    public function getlistbybrands($brands) {

        try {
            $result = $this->alias('B')
                    ->join('erui_supplier.t_supplier S on S.supplier_id=B.supplier_id', 'left')
                    ->field('B.name as brand,S.name,B.supplier_id')
                    ->where(['B.name' => ['in', $brands], 'S.status' => 'VALID'])
                    ->group('B.name')
                    ->select();
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS ' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    public function getlistbybrand($brand) {

        try {
            $result = $this->alias('B')
                    ->join('erui_supplier.t_supplier S on S.supplier_id=B.supplier_id', 'left')
                    ->field('S.name as supplier_name,B.supplier_id')
                    ->where(['B.name' => $brand, 'S.status' => 'VALID'])
                    ->select();
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS ' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
