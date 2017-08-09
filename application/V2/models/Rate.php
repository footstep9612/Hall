<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rate
 * @author  zhongyg
 * @date    2017-8-1 17:08:09
 * @version V2.0
 * @desc   
 */
class RateModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_dict';
    protected $tableName = 'rate';

    /*
     * 初始化
     */

    public function __construct() {
        parent::__construct();
    }

    /**
     * 搜索条件
     * @param array $condition;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    private function _getCondition(&$condition) {
        $data = [];
        $this->_getValue($data, $condition, 'lang'); //语言
        $this->_getValue($data, $condition, 'name'); //名称
        $this->_getValue($data, $condition, 'trade_terms_bn'); //贸易术语简称
        $this->_getValue($data, $condition, 'trans_mode_bn'); //运输方式简称 
        $this->_getValue($data, $condition, 'port_bn'); //港口简称
        $this->_getValue($data, $condition, 'country_bn'); //目的国简称
        $this->_getValue($data, $condition, 'box_type_bn'); //发货箱型简称
        $this->_getValue($data, $condition, 'shipowner_clause_bn'); //船东条款简称
        $this->_getValue($data, $condition, 'fee_type_bn'); //费用类型简称
        $this->_getValue($data, $condition, 'fee_type_notes'); //费用类别
        $this->_getValue($data, $condition, 'pricing_unit'); //计费单位
        $this->_getValue($data, $condition, 'unit_price'); //单价
        $this->_getValue($data, $condition, 'cur_bn'); //币种
        $this->_getValue($data, $condition, 'qty'); //数量
        $this->_getValue($data, $condition, 'remarks'); //备注
        $this->_getValue($data, $condition, 'status', 'string'); //状态
        if (!isset($data['status'])) {
            $data['status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'created_at', 'range'); //创建时间
        $this->_getValue($data, $condition, 'created_by', 'string'); //创建人
        $this->_getValue($data, $condition, 'updated_by', 'string'); //修改人
        $this->_getValue($data, $condition, 'updated_at', 'range'); //修改时间
        return $data;
    }

    /**
     * 获取列表
     * @param array $condition
     * @param string $order 排序
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function getlist($condition, $order = 'id desc') {
        try {
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where) . $order);
            if (redisHashExist('Rate', $redis_key)) {
                return json_decode(redisHashGet('Rate', $redis_key), true);
            }
            $this->field('id,name,trade_terms_bn,trans_mode_bn,country_bn,port_bn,'
                            . 'box_type_bn,shipowner_clause_bn,fee_type_bn,'
                            . 'fee_type_notes,pricing_unit,unit_price,cur_bn,'
                            . 'qty,remarks,status,created_by,created_at,updated_by,'
                            . 'updated_at')
                    ->where($where);
            $re = $this->order($order)->select();
            if ($re) {
                redisHashSet('Rate', $redis_key, json_encode($re));
            }
            return$re;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 获取总数
     * @param data $condition
     * @return int
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function getCount($condition) {
        try {
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where)) . '_COUNT';
            if (redisHashExist('Rate', $redis_key)) {
                return redisHashGet('Rate', $redis_key);
            }
            $count = $this->where($where)->count();
            redisHashSet('Rate', $redis_key, $count);
            return $count;
        } catch (Exception $ex) {

            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    /**
     * 获取情
     * @param  string $id 编码
     * @return mix
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function info($id = '') {
        $where['id'] = $id;

        $redis_key = $id;
        if (redisHashExist('Rate', $redis_key)) {
            return json_decode(redisHashGet('Rate', $redis_key), true);
        }
        $item = $this->where($where)
                ->field('id,name,trade_terms_bn,trans_mode_bn,country_bn,port_bn,'
                        . 'box_type_bn,shipowner_clause_bn,fee_type_bn,'
                        . 'fee_type_notes,pricing_unit,unit_price,cur_bn,'
                        . 'qty,remarks,status,created_by,created_at,updated_by,'
                        . 'updated_at')
                ->find();
        redisHashSet('Rate', $redis_key, json_encode($item));
        return$item;
    }

    /**
     * 删除数据
     * @param  string $id id
     * @param  string $uid 用户ID
     * @return bool
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function delete_data($id = '', $uid = 0) {
        if (!$id) {
            return false;
        } else {
            $where['id'] = $id;
        }
        $update_data['updated_by'] = $uid;
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        $update_data['status'] = 'DELETED';

        $flag = $this->where($where)
                ->save($update_data);

        return $flag;
    }

    /**
     * 修改数据
     * @param  array $update 更新条件
     * @return bool
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function update_data($update, $uid = 0) {
        $data = $this->create($update);
        $where['id'] = $data['id'];
        $update_data['updated_by'] = $uid;
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        $flag = $this->where($where)->save($data);
        if ($flag) {
            return $flag;
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function create_data($create = [], $uid = 0) {
        if (isset($create['id'])) {
            $create['id'] = null;
            unset($create['id']);
        }

        $create['created_by'] = $uid;
        $create['created_at'] = date('Y-m-d H:i:s');
        $data = $this->create($create);
        $data['status'] = $data['status'] == 'INVALID' ? 'INVALID' : 'VALID';
        $flag = $this->add($data);
        if ($flag) {
            return $flag;
        } else {
            return false;
        }
    }

}