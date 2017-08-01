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

    public function __construct($str = '') {
        parent::__construct($str = '');
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
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function getlist($condition, $order = 'id desc') {
        try {
            $data = $this->_getCondition($condition);
            $this->field('id,lang,name,trade_terms_bn,country_bn,port_bn,'
                            . 'box_type_bn,shipowner_clause_bn,fee_type_bn,'
                            . 'fee_type_notes,pricing_unit,unit_price,cur_bn,'
                            . 'qty,remarks,status,created_by,created_at,updated_by,'
                            . 'updated_at,deleted_flag')
                    ->where($data);
            return $this->order($order)
                            ->select();
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

    /**
     * 获取情
     * @param  string $bn 编码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($bn = '', $lang = 'en') {
        $where['bn'] = $bn;
        $where['lang'] = $lang;
        return $this->where($where)
                        ->field('id,lang,name,trade_terms_bn,country_bn,port_bn,'
                                . 'box_type_bn,shipowner_clause_bn,fee_type_bn,'
                                . 'fee_type_notes,pricing_unit,unit_price,cur_bn,'
                                . 'qty,remarks,status,created_by,created_at,updated_by,'
                                . 'updated_at,deleted_flag')
                        ->find();
    }

    /**
     * 删除数据
     * @param  string $id id
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($id = '', $lang = '') {
        if (!$id) {
            return false;
        } else {
            $where['id'] = $id;
        }
        if ($lang) {
            $where['lang'] = $lang;
        }
        $flag = $this->where($where)
                ->save(['status' => 'INVALID']);

        return $flag;
    }

    /**
     * 修改数据
     * @param  array $update id
     * @return bool
     * @author jhw
     */
    public function update_data($update) {
        $data = $this->create($update);
        $where['id'] = $data['id'];
        $arr['status'] = $data['status'] == 'VALID' ? 'VALID' : 'INVALID';
        $flag = $this->where($where)->save($arr);
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
     * @author jhw
     */
    public function create_data($create = []) {

        $data = $this->create($create);
        $flag = $this->add($data);
        if ($flag) {
            return $flag;
        } else {
            return false;
        }
    }

}
