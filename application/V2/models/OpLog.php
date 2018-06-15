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
class OpLogModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_sys';
    protected $tableName = 'op_log';

    public function __construct() {
        parent::__construct();
    }

    protected function _before_update(&$data, $options) {

    }

    // 更新成功后的回调方法
    protected function _after_update($data, $options) {

    }

    // 插入数据前的回调方法
    protected function _before_insert(&$data, $options) {

    }

    // 插入成功后的回调方法
    protected function _after_insert($data, $options) {

    }

    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data) {

    }

    // 删除数据前的回调方法
    protected function _before_delete($options) {

    }

    // 删除成功后的回调方法
    protected function _after_delete($data, $options) {

    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = [], $uid = 0) {
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
            //  $this->deleted_data();
            return $this->add($data);
        } catch (Exception $ex) {

        }
    }

    private function index_exit() {

        $sql = 'SELECT INDEX_NAME FROM information_schema.statistics WHERE table_schema=\'erui_sys\' AND table_name = \'op_log\' AND index_name = \'index_created_at\'';
        $d = $this->query($sql);
        if (!$d) {
            $sql_addIndex = '  ALTER TABLE `' . $this->tableName . '` ADD INDEX index_created_at (`created_at` ) ';
            $flag = $this->execute($sql_addIndex);
        }
    }

    /**
     * 更新数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function deleted_data() {
        try {
            $this->index_exit();
            $this->where(['created_at' => ['lt', date('Y-m-d H:i:s', strtotime(' -1 month'))]])->delete();
            $this->where(['created_at' => '0000-00-00 00:00:00'])->delete();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * 更新数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function update_data($create = [], $id = 0, $uid = 0) {
        try {
            $create['op_id'] = defined('UID') ? UID : 0;
            $create['created_at'] = $create['op_at'] = date('Y-m-d H:i:s');
            if (is_array($create['op_note'])) {
                $create['op_note'] = json_encode($create['op_note'], 256);
            } else {
                $create['op_note'] = $create['op_note'];
            }

            $create['op_id'] = defined('UID') ? UID : 0;
            $data = $this->where(['id' => $id])->save($create);
            return $data;
        } catch (Exception $ex) {

        }
    }

}
