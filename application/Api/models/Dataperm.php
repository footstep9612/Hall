<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DatapermModel
 *
 * @author zhongyg
 */
class DatapermModel extends PulicModel{

    //put your code here
    protected $tableName = 'data_perm';
    Protected $autoCheckFields = true;

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param mix $data; 条件
     * @return mix
     * @author zyg
     */
    public function getlist($olddata, $limit, $order = 'id desc') {
        if (is_string($olddata)) {

            $data = [$olddata];
        } elseif (!$olddata) {

            $data = [];
        }
        try {
            if (!empty($limit)) {
                return $this->field('id,obj_type,obj_id')
                                ->where($data)
                                ->limit($limit['page'] . ',' . $limit['num'])
                                ->order($order)
                                ->select();
            } else {
                return $this->field('id,obj_type,obj_id')
                                ->where($data)
                                ->order($order)
                                ->select();
            }
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 获取想起
     * @param  int  $id
     * @return mix
     * @author zyg
     */
    public function detail($id = '') {
        $where['id'] = $id;

        if (!empty($where['id'])) {
            try {
                $row = $this->where($where)
                        ->field('id,obj_type,obj_id')
                        ->find();
                return $row;
            } catch (Exception $ex) {
                Log::write($ex->getMessage());
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author zyg
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        try {
            if (!empty($where['id'])) {
                return $this->where($where)
                                ->delete();
            } else {
                return false;
            }
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 更新数据
     * @param  int $id id
     * @return bool
     * @author zyg
     */
    public function update_data($data, $where) {
        if (isset($data['obj_type'])) {
            $arr['obj_type'] = $data['obj_type'];
        }
        if (isset($data['obj_id'])) {
            $arr['obj_id'] = $data['obj_id'];
        }
        try {
            if (!empty($where)) {
                return $this->where($where)->save($arr);
            } else {
                return false;
            }
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool or int 
     * @author zyg
     */
    public function create_data($create = []) {
        if (isset($data['obj_type'])) {
            $arr['obj_type'] = $data['obj_type'];
        }
        if (isset($data['obj_id'])) {
            $arr['obj_id'] = $data['obj_id'];
        }
        try {
            $data = $this->create($arr);

            return $this->add($data);
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

}
