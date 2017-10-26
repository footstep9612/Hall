<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Region
 * @author  zhongyg
 * @date    2017-8-1 16:20:48
 * @version V2.0
 * @desc
 */
class RegionModel extends PublicModel {

    //put your code here
    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'region';

    /*
     * 初始化
     */

    public function __construct() {
        parent::__construct();
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
        $this->_getValue($data, $condition, 'name');
        $this->_getValue($data, $condition, 'status');
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
            if (redisHashExist('Region', $redis_key)) {
                return json_decode(redisHashGet('Region', $redis_key), true);
            }
            $result = $this->field('id,lang,bn,name,`status`')->where($where)->order($order)->select();
            redisHashSet('Region', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
