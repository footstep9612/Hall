<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HotKeywords
 * @author  zhongyg
 * @date    2017-8-1 16:52:02
 * @version V2.0
 * @desc   搜索热词
 */
class HotKeywordsModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_operation';
    protected $tableName = 'hot_keywords';
    protected $redis_name = 'HotKeywords';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    protected function _getcondition(&$condition = []) {
        $data = [];
        $this->_getValue($data, $condition, 'keywords', 'string');
        $this->_getValue($data, $condition, 'keywords', 'string');
        $this->_getValue($data, $condition, 'show_cat_no', 'string');
        $this->_getValue($data, $condition, 'search_time', 'range');
        $this->_getValue($data, $condition, 'created_at', 'range');
        $this->_getValue($data, $condition, 'created_by', 'string');
        $this->_getValue($data, $condition, 'updated_at', 'range');
        $this->_getValue($data, $condition, 'updated_by', 'string');
        return $data;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($condition = []) {
        $data = $this->_getcondition($condition);
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
        $data = $this->_getcondition($condition);
        try {
            list($current_no, $pagesize) = $this->_getPage($condition);

            $redis_key = md5(json_encode($data)) . $current_no . $pagesize;
            if (redisHashExist($this->redis_name, $redis_key)) {
                return json_decode(redisHashGet($this->redis_name, $redis_key), true);
            }
            $list = $this->where($data)->limit($current_no, $pagesize)
                            ->order('search_count desc,search_time desc')->select();
            redisHashSet($this->redis_name, $redis_key, json_encode($list), 3600);
            return $list;
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
            $redis_key = $id;
            if (redisHashExist($this->redis_name, $redis_key)) {
                return json_decode(redisHashGet($this->redis_name, $redis_key), true);
            }

            $item = $this->where(['id' => $id])->find();
            redisHashSet($this->redis_name, $redis_key, json_encode($item), 3600);
            return $item;
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    public function exist($condition) {
        try {
            $where = $this->_getcondition($condition);
            return $this->_exist($where);
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
            return $this->where(['id' => $id])->save($data);
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
