<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TransBoxType
 * @author  zhongyg
 * @date    2017-8-1 16:45:28
 * @version V2.0
 * @desc   运输方式对应发货箱型
 */
class TransBoxTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_dict';
    protected $tableName = 'trans_box_type';

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

        $this->_getValue($data, $condition, 'box_type_bn'); //名称
        $this->_getValue($data, $condition, 'trans_mode'); //贸易术语简称

        if (!isset($data['status'])) {
            $data['status'] = 'VALID';
        }

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
            $this->field('id,'
                            . '(select box_type_name from erui2_dict.box_type where bn= box_type and lang=\'zh\')  as box_type_name,'
                            . '(select trans_mode from erui2_dict.trans_mode where bn= trans_mode_bn and lang=\'zh\') as trans_mode')
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
    public function info($id = '') {
        $where['id'] = $id;
 
        return $this->where($where)
                        ->field('id,box_type_bn,trans_mode_bn')
                        ->find();
    }

    /**
     * 删除数据
     * @param  string $id id
     * @return bool
     * @author zyg
     */
    public function delete_data($id = '') {
        if (!$id) {
            return false;
        } else {
            $where['id'] = $id;
        }

        $flag = $this->where($where)
                ->save(['status' => 'INVALID','deleted_flag'=>'Y']);

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
     * @author jhw
     */
    public function create_data($create = []) {

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
