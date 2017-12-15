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
class HomeFloorProductModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_floor_product';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['s.deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn', 'string', 's.country_bn');
        $this->_getValue($where, $condition, 'floor_name', 'like', 'sf.floor_name');

        $this->_getValue($where, $condition, 'floor_id', 'string', 's.floor_id');
        $employee_model = new EmployeeModel();
        if (isset($condition['created_by_name']) && $condition['created_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['created_by_name']));
            if ($userids) {
                $where['s.created_by'] = ['in', $userids];
            } else {
                $where['s.created_by'] = null;
            }
        }
        $this->_getValue($where, $condition, 'show_flag', 'bool', 'sf.show_flag');
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
    public function getExit($country_bn, $lang, $spu) {
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;
        $where['spu'] = $spu;
        return $this->where($where)->field('id,floor_id')->find();
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
        list($from, $size) = $this->_getPage($condition);
        $where['s.lang'] = $lang;
        return $this->alias('s')
                        ->field('s.sku,s.show_name,s.stock,s.spu,s.country_bn')
                        ->join($stock_floor_table
                                . ' sf on sf.lang=s.lang and sf.id=s.floor_id and sf.country_bn=s.country_bn and sf.deleted_flag=\'N\'', 'left')
                        ->where($where)
                        ->limit($from, $size)
                        ->select();
    }

    /**
     * Description of 获取现货详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getInfo($country_bn, $lang, $spu) {
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;
        $where['spu'] = $spu;
        return $this->where($where)->find();
    }

    private function getSpu($spu, $lang) {
        $where = ['deleted_flag' => 'N',
            'lang' => $lang,
            'spu' => $spu,
        ];
        $product_model = new ProductModel();
        $data = $product_model->field('spu,name,show_name')->where($where)->find();

        return $data;
    }

    /**
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function createData($country_bn, $spus, $floor_id, $lang) {

        $this->startTrans();
        foreach ($spus as $spu) {
            $row = $this->getExit($country_bn, $lang, $spu);

            if (!$row) {

                $product_name = $this->getSpu($spu, $lang);
                if (empty($product_name['spu'])) {

                    return false;
                }
                $data = [
                    'country_bn' => $country_bn,
                    'lang' => $lang,
                    'spu' => $spu,
                    'floor_id' => $floor_id,
                    'name' => $product_name['name'],
                    'show_name' => $product_name['show_name'],
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
    public function deleteData($country_bn, $spus, $lang) {
        $this->startTrans();
        $home_floor_model = new HomeFloorModel();
        foreach ($spus as $spu) {
            $row = $this->getExit($country_bn, $lang, $spu);
            if (!$row) {

                $where = [
                    'country_bn' => $country_bn,
                    'lang' => $lang,
                    'spu' => $spu,
                ];
                $flag = $this->where($where)->save(['deleted_flag' => 'Y',
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'deleted_by' => defined('UID') ? UID : 0]);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
                if ($row['floor_id']) {
                    $flag = $home_floor_model->ChangeSkuCount($row['floor_id'], -1);
                    if (!$flag) {
                        $this->rollback();
                        return false;
                    }
                }
            }
        }
        $this->commit();
        return true;
    }

}
