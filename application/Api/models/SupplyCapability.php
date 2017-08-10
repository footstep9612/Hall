<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Supplycapability
 *
 * @author zhongyg
 */
class SupplycapabilityModel extends PublicModel {

    //put your code here
    protected $tableName = 'supply_capability';
    protected $dbName = 'erui_goods'; //数据库名称

    public function __construct($str = '') {
        parent::__construct($str);
    }

    function getcondition($condistion, $lang = 'en') {
        $data = [];

        if ($lang) {
            $data['lang'] = $lang;
        } else {
            $data['lang'] = 'en';
        }
        if (isset($condistion['cat_no']) && is_array($condistion['cat_no'])) {
            $data['cat_no'] = ['in', $condistion['cat_no']];
        } elseif (isset($condistion['cat_no'])) {
            $data['cat_no'] = $condistion['cat_no'];
        }
        if (isset($condistion['cat_nos']) && is_array($condistion['cat_nos'])) {
            $data['cat_no'] = ['in', $condistion['cat_nos']];
        }
        if (isset($condistion['status']) && in_array($condistion['status'], ['DRAFT', 'APPROVING', 'VALID', 'DELETED'])) {
            $data['status'] = $condistion['status'];
        } else {
            $data['status'] = 'VALID';
        }
        if (isset($condistion['created_by'])) {
            $data['created_by'] = $condistion['created_by'];
        }
        if (isset($condistion['created_at']) && is_string($condistion['created_at'])) {
            $data['created_at'] = $condistion['created_at'];
        }

        if (isset($condistion['created_at_start']) && isset($condistion['created_at_end'])) {
            $data['created_at'] = ['between', $condistion['created_at_start'], $condistion['created_at_end']];
        } elseif (isset($condistion['created_at_start'])) {

            $data['created_at'] = ['egt', $condistion['created_at_start']];
        } elseif (isset($condistion['created_at_end'])) {

            $data['created_at'] = ['elt', $condistion['created_at_end']];
        }
        if (isset($condistion['ability_name'])) {
            $data['ability_name'] = ['like', '%' . $condistion['ability_name'] . '%'];
        }
        if (isset($condistion['ability_value'])) {
            $data['ability_value'] = ['like', '%' . $condistion['ability_value'] . '%'];
        }
        return $data;
    }

    function getlist($condistion, $lang = 'en') {

        $where = $this->getcondition($condistion, $lang);
        try {

            return $this->field('id,cat_no,ability_name,ability_value')
                            ->where($where)->order('sort_order desc')->select();
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    function getlistbycat_nos($cat_nos, $lang = 'en') {
        $condistion['cat_nos'] = $cat_nos;
        $where = $this->getcondition($condistion, $lang);
        try {

            $rows = $this->field('id,cat_no,ability_name,ability_value')
                            ->where($where)->order(' sort_order desc')->select();

            if ($rows) {
                $data = [];
                foreach ($rows as $key => $val) {
                    $data[$val['cat_no']][] = $val;
                }
                return $data;
            } else {

                return [];
            }
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

}

