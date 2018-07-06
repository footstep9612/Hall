<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Country
 *
 * @author jhw
 */
class Common_CountryModel extends PublicModel {

    const STATUS_VALID = 'VALID';    //有效的

    //put your code here

    protected $dbName = 'erui_dict';
    protected $tableName = 'country';

    public function __construct() {
        parent::__construct();
    }

    /*
     * 条件id,lang,bn,name,time_zone,region,pinyin
     */

    private function _getCondition(&$condition) {
        $data = ['deleted_flag' => 'N'];
        getValue($data, $condition, 'lang', 'string', 'lang');
        if (isset($condition['bn']) && $condition['bn']) {
            $data['bn'] = $condition['bn'];
        }
        return $data;
    }

    /**
     * 获取列表
     * @param data $condition;
     * @return array
     * @author jhw
     */
    public function GetList($condition, $order = 'id desc') {
        try {
            $where = $this->_getCondition($condition);
            unset($condition);
            $redis_key = md5(json_encode($where) . $order);
//            if (redisHashExist('Country', $redis_key)) {
//                return json_decode(redisHashGet('Country', $redis_key), true);
//            }
            $result = $this
                    ->field('bn,name')
                    ->where($where)
                    ->order($order)
                    ->select();

            redisHashSet('Country', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

    /**
     * 获取列表
     * @param data $condition;
     * @return array
     * @author jhw
     */
    public function getCount($condition, $order = 'id desc') {
        try {
            $where = $this->_getCondition($condition);
            unset($condition);

            $count = $this
                    ->where($where)
                    ->count();


            return $count;
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

}
