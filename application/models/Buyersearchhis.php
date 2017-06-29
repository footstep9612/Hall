<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Buyersearchhis
 *
 * @author zhongyg
 */
class BuyersearchhisModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer_search_his';
    protected $dbName = 'erui_beavior';

    public function __construct($str = '') {
        parent::__construct($str);
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    protected function getcondition($condition = []) {
        $data = [];
        if (isset($condition['keywords'])) {
            $data['keywords'] = $condition['keywords'];
        }
        if (isset($condition['user_email'])) {
            $data['user_email'] = $condition['user_email'];
        }

        if (isset($condition['search_time_start']) && isset($condition['search_time_end'])) {
            $data['search_time'] = ['between', $condition['search_time_start'], $condition['search_time_end']];
        } elseif (isset($condition['search_time_start'])) {

            $data['search_time'] = ['egt', $condition['search_time_start']];
        } elseif (isset($condition['search_time_end'])) {

            $data['search_time'] = ['elt', $condition['search_time_end']];
        }
        if (isset($condition['search_count_start']) && isset($condition['search_count_end'])) {
            $data['search_count'] = ['between', $condition['search_count_start'],
                $condition['search_count_end']];
        } elseif (isset($condition['search_count_start'])) {

            $data['search_count'] = ['egt', $condition['search_count_start']];
        } elseif (isset($condition['search_count_end'])) {

            $data['search_count'] = ['elt', $condition['search_count_end']];
        }
        return $data;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($condition = []) {
        $data = $this->getcondition($condition);
        try {
            return $this->where($data)->count('id');
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return 0;
        }
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = []) {
        $data = $this->getcondition($condition);
        try {

            $pagesize = 10;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }
            return $this->where($data)->limit($current_no, $pagesize)
                            ->order('search_count desc,search_time desc')->select();
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($id = '') {


        try {
            return $this->where(['id' => $id])->limit($current_no, $pagesize)->find();
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    public function exist($condition) {
        try {
            $data = $this->create($data);
            $row = $this->where($data)->find();
            return empty($row) ? false : $row['id'];
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 删除数据
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($id = '') {

        try {
            return $this->where(['id' => $id])->delete();
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update_data($upcondition = []) {

        $data = $this->create($upcondition);
        $id = $upcondition['id'];
        try {
            return $this->where(['id' => $id])->create($data);
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {
        $data = $this->create($createcondition);
        try {
            return $this->add($data);
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return false;
        }
    }

}
