<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BoxShipownerClause
 * @author  zhongyg
 * @date    2017-8-1 16:47:33
 * @version V2.0
 * @desc   发货箱型对应船东条款
 */
class BoxShipownerClauseModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'box_shipowner_clause';

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
        $this->_getValue($data, $condition, 'shipowner_clause_bn'); //贸易术语简称
        if (isset($condition['keyword']) && $condition['keyword']) {
            $map = [];
            $this->_getValue($map, $condition, 'keyword', 'like', 'bt.box_type_name');
            $this->_getValue($map, $condition, 'keyword', 'like', 'sc.clause');
            $map['_logic'] = 'or';
            $data['_complex'] = $map;
        }
        $this->_getValue($data, $condition, 'status', 'string', 'tbt.status'); //状态
        if (!isset($data['tbt.status'])) {
            $data['tbt.status'] = 'VALID';
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
    public function getlist($condition, $order = 'tbt.id desc') {
        try {
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where) . $order);
            if (redisHashExist('BoxShipownerClause', $redis_key)) {
                return json_decode(redisHashGet('BoxShipownerClause', $redis_key), true);
            }
            $result = $this->alias('tbt')
                            ->join('erui_dict.box_type bt on bt.bn=tbt.box_type_bn and bt.lang=\'zh\'', 'left')
                            ->join('erui_dict.shipowner_clause sc on sc.bn=tbt.shipowner_clause_bn and sc.lang=\'zh\'', 'left')
                            ->field('tbt.id,bt.box_type_name,sc.clause ')
                            ->where($where)->order($order)->select();


            redisHashSet('BoxShipownerClause', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), $level);
            return [];
        }
    }

    /**
     * 获取情
     * @param  string $id 编码

     * @return mix
     * @author zyg
     */
    public function info($id = '') {
        $where['id'] = $id;

        return $this->where($where)
                        ->field('id,box_type_bn,shipowner_clause_bn')
                        ->find();
    }

    /**
     * 删除数据
     * @param  string $id id
     * @param  string $lang 语言
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
        unset($data['id']);
        $data['status'] = $data['status'] == 'INVALID' ? 'INVALID' : 'VALID';
        $flag = $this->add($data);

        if ($flag) {
            return $flag;
        } else {
            return false;
        }
    }

}
