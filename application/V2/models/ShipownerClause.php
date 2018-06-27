<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShipownerClause
 * @author  zhongyg
 * @date    2017-8-1 16:48:21
 * @version V2.0
 * @desc   船东条款
 */
class ShipownerClauseModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'shipowner_clause';

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
        $this->_getValue($data, $condition, 'lang');
        $this->_getValue($data, $condition, 'bn');
        $this->_getValue($data, $condition, 'clause');
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
            if (redisHashExist('ShipownerClause', $redis_key)) {
                return json_decode(redisHashGet('ShipownerClause', $redis_key), true);
            }
            $result = $this->field('id,bn,clause')->where($where)->order($order)->select();
            redisHashSet('ShipownerClause', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
