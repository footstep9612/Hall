<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货
 * @author  zhongyg
 * @date    2017-12-6 9:07:59
 * @version V2.0
 * @desc
 */
class StockModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['s.deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn', 'string', 's.country_bn');
        $this->_getValue($where, $condition, 'floor_name', 'like', 'sf.floor_name');
        $employee_model = new EmployeeModel();
        if (isset($condition['created_by_name']) && $condition['created_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['created_by_name']));
            if ($userids) {
                $where['s.created_by'] = ['in', $userids];
            } else {
                $where['s.created_by'] = null;
            }
        }
        $this->_getValue($where, $condition, 'show_flag', 'bool');
        $this->_getValue($where, $condition, 'created_at', 'between', 's.created_at');
        return $where;
    }

    /**
     * Description of 判断国家现货是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($country_bn, $lang, $sku) {
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;
        $where['sku'] = $sku;
        return $this->where($where)->field('id')->find();
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getList($condition, $lang) {
        $stock_floor_model = new StockFloorModel();
        $stock_floor_table = $stock_floor_model->getTableName();

        $where = $this->_getCondition($condition);
        $where['lang'] = $lang;
        return $this->alias('s')
                        ->field('s.sku,s.show_name,s.stock,s.spu,s.country_bn')
                        ->join($stock_floor_table
                                . ' sf on sf.lang=s.lang and sf.id=s.floor_id and sf.country_bn=s.country_bn', 'left')
                        ->where($where)
                        ->select();
    }

    /**
     * Description of 获取现货详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getInfo($country_bn, $lang, $sku) {
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;
        $where['sku'] = $sku;
        return $this->where($where)->find();
    }

    /**
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function createData($country_bn, $skus, $lang) {
        $this->startTrans();
        foreach ($skus as $sku) {
            if (!$this->getExit($country_bn, $lang, $sku)) {

                $data = [
                    'country_bn' => $country_bn,
                    'lang' => $lang,
                    'sku' => $sku,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => defined('UID') ? UID : 0
                ];
                $flag = $this->add($data);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
            }
        }
        $this->commit();
        return true;
    }

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function deleteData($country_bn, $skus, $lang) {
        $this->startTrans();
        foreach ($skus as $sku) {
            if (!$this->getExit($country_bn, $lang, $sku)) {

                $where = [
                    'country_bn' => $country_bn,
                    'lang' => $lang,
                    'sku' => $sku,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => defined('UID') ? UID : 0
                ];
                $flag = $this->where($where)->delete();
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
            }
        }
        $this->commit();
        return true;
    }

}
