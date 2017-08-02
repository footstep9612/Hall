<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SupervisedCriteriaModel
 * @author  zhongyg
 * @date    2017-8-2 9:36:04
 * @version V2.0
 * @desc   监管条件
 */
class SupervisedCriteriaModel extends PublicModel {

    //put your code here
    //put your code here
    protected $dbName = 'erui2_dict';
    protected $tableName = 'supervised_criteria';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    private function _getCondition(&$condition) {
        $data = [];
        $data['status'] = 'VALID';
        $data['deleted_flag'] = 'N';
        return $data;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function getlist($condition, $order = 'id desc') {
        try {
            $data = $this->_getCondition($condition);
            $this->field('id,criteria_no,license,authority,issued_at')
                    ->where($data);
            return $this->order($order)
                            ->select();
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

}
