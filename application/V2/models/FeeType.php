<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeeType
 * @author  zhongyg
 * @date    2017-8-1 16:32:21
 * @version V2.0
 * @desc   费率类型
 */
class FeeTypeModel extends PublicModel {

    //put your code here

    protected $dbName = 'erui2_dict'; //数据库名称
    protected $tableName = 'fee_type';

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    private function _getCondition(&$condition) {
        $data = [];
        $this->_getValue($data, $condition, 'lang');
        $this->_getValue($data, $condition, 'bn');
        $this->_getValue($data, $condition, 'name');
        $this->_getValue($data, $condition, 'status');
        if (!isset($data['status'])) {
            $data['status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'deleted_flag', 'bool');

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
            $where = $this->_getCondition($condition);

            $redis_key = md5(json_encode($where) . $order);
            if (redisHashExist('FeeType', $redis_key)) {
                return json_decode(redisHashGet('FeeType', $redis_key), true);
            }
            $this->field('id,bn,name')
                    ->where($where);
            $result = $this->order($order)
                    ->select();

            redisHashSet('FeeType', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
          LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
