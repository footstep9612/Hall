<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OpLogModel
 * @author  zhongyg
 * @date    2017-8-3 13:38:48
 * @version V2.0
 * @desc
 */
class SysOpLogModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_sys';
    protected $tableName = 'sys_op_log';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        try {
            $create['op_id'] = defined('UID') ? UID : 0;
            $create['created_at'] = $create['op_at'] = date('Y-m-d H:i:s');
            if (is_array($create['op_note'])) {
                $create['op_note'] = json_encode($create['op_note']);
            } else {
                $create['op_note'] = $create['op_note'];
            }

            $create['op_id'] = defined('UID') ? UID : 0;
            $data = $this->create($create);
            return $this->add($data);
        } catch (Exception $ex) {

        }
    }

    /**
     * 更新数据
     * @param  mix $ids 删除的ID数组
     * @return bool
     * @author jhw
     */
    public function deleted_data($ids = 0) {
        try {

            if ($ids && is_array($ids)) {
                $where['id'] = ['in', $ids];
            } elseif ($ids && is_string($ids)) {
                $where['id'] = $ids;
            } else {
                return false;
            }


            $flag = $this->where($where)->delete();
            return $flag;
        } catch (Exception $ex) {

        }
    }

}
