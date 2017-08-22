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

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取列表
     * @param array $condition
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    private function _getCondition($condition) {
        $where = [];
        $where['status'] = 'VALID';
        $employee_model = new EmployeeModel();
        if (isset($condition['created_by_name']) && $condition['created_by_name']) {
            $userids = $employee_model->getUseridsByUserName($condition['created_by_name']);
            $where['created_by'] = ['in', $userids];
        }
        if (isset($condition['license']) && $condition['license']) {
            $map1['license'] = ['like', '%' . $condition['license'] . '%'];
        }
        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = $condition['keyword'];

            $userids = $employee_model->getUseridsByUserName($keyword);
            $map1['created_by'] = ['in', $userids];
            $map1['license'] = ['like', '%' . $keyword . '%'];
            $map1['_logic'] = 'or';
            $where['_complex'] = $map1;
        }

        //$data['deleted_flag'] = 'N';
        return $where;
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
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where) . $order);
            if (redisHashExist('SupervisedCriteria', $redis_key)) {
                return json_decode(redisHashGet('SupervisedCriteria', $redis_key), true);
            }

            $result = $this->field('id,criteria_no,license,authority,issued_at,created_by,created_at')->where($where)->order($order)->select();
            redisHashSet('SupervisedCriteria', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

}
