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
class StockCostPriceModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock_cost_price';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn', 'string', 'country_bn');
        $this->_getValue($where, $condition, 'sku', 'string', 'sku');


        return $where;
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getList($condition) {
        $where = $this->_getCondition($condition);
        return $this->where($where)
                        ->select();
    }

    /**
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function updateData($country_bn, $sku, $lang, $cost_prices) {
        $where['lang'] = $lang;
        $where['sku'] = $sku;
        $where['country_bn'] = $country_bn;
        $this->where($where)->save(['deleted_flag' => 'Y']);
        $this->startTrans();
        foreach ($cost_prices as $cost_price) {

            $id = empty($cost_price['id']) ? null : $cost_price['id'];
            if (isset($cost_price['id'])) {
                unset($cost_price['id']);
            }

            $data = $this->create($cost_price);
            $data['lang'] = $lang;
            $data['sku'] = $sku;
            $data['country_bn'] = $country_bn;
            $flag = false;
            if (empty($id)) {
                $data['created_by'] = defined('UID') ? UID : 0;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['deleted_flag'] = 'N';
                $flag = $this->add($data);
            } else {
                $data['updated_by'] = defined('UID') ? UID : 0;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['deleted_flag'] = 'N';
                $flag = $this->where(['id' => $id])->save($data);
            }
            if (!$flag) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

}
