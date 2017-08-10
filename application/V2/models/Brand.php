<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Brand
 *
 * @author zhongyg
 */
class BrandModel extends PublicModel {

    //put your code here

    protected $tableName = 'brand';
    protected $dbName = 'erui2_dict'; //数据库名称

    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function __construct() {
        parent::__construct();
    }

    /**
     * 条件解析
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    private function _getcondition($condition, $lang = '') {

        $where = [];
        //  $this->_getValue($where, $condition, 'id', 'string');
        $this->_getValue($where, $condition, 'name', 'like', 'brand');
        $this->_getValue($where, $condition, 'status', 'string', 'status', 'VALID');
        // $this->_getValue($where, $condition, 'manufacturer', 'like', 'brand');
        if ($lang) {
            $where['brand'] = ['like', '%' . $lang . '%'];
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getCount($condition, $lang = '') {
        $where = $this->_getcondition($condition, $lang);

        $redis_key = md5(json_encode($where) . $lang) . '_COUNT';
        if (redisHashExist('Brand', $redis_key)) {
            return redisHashGet('Brand', $redis_key);
        }
        try {
            $count = $this->where($where)
                    ->count('id');

            redisHashSet('Brand', $redis_key, $count);
            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getlist($condition, $lang = '') {
        $where = $this->_getcondition($condition, $lang);
        list($row_start, $pagesize) = $this->_getPage($condition);

        $redis_key = md5(json_encode($where) . $lang . $row_start . $pagesize);
        if (redisHashExist('Brand', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        try {
            $item = $this->where($where)
                    ->field('id,brand,status,created_by,'
                            . 'created_at,updated_by,updated_at')
                    ->order('created_at desc')
                    ->limit($row_start, $pagesize)
                    ->select();
            redisHashSet('Brand', $redis_key, json_encode($item));
            return $item;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function listall($condition, $lang = '') {
        $where = $this->_getcondition($condition, $lang);

        $redis_key = md5(json_encode($where) . $lang);
        if (redisHashExist('Brand', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        try {
            $item = $this->where($where)
                    ->field('id,brand')
                    ->order('created_at desc')
                    ->select();
            redisHashSet('Brand', $redis_key, json_encode($item));
            return $item;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
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
    public function info($id = '', $status = 'VALID') {
        if ($id) {
            $where['id'] = $id;
        } else {
            return [];
        }
        $redis_key = $id;
        if (redisHashExist('Brand', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        $item = $this->where($where)
                ->find();
        redisHashSet('Brand', $redis_key, json_encode($item));
        return$item;
    }

    /**
     * 判断是否存在
     * @param  mix $where 搜索条件
     * @return mix
     * @author zyg
     */
    public function Exist($where) {

        $row = $this->where($where)
                ->field('id')
                ->find();
        return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
    }

    /**
     * 删除数据
     * @param  string $id
     * @param  string $uid 用户ID
     * @return bool
     * @author zyg
     */
    public function delete_data($id = 0) {
        if (!$id) {
            return false;
        } elseif ($id) {
            $where['id'] = $id;
        }
        $flag = $this->where($where)
                ->save(['status' => self::STATUS_DELETED]);

        if ($flag) {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 删除数据
     * @param  string $brand_ids
     * @return bool
     * @author zyg
     */
    public function batchdelete_data($brand_ids = []) {
        if (!$brand_ids) {
            return false;
        } elseif ($brand_ids) {
            $where['id'] = ['in', $brand_ids];
        }
        $this->startTrans();

        $flag = $this->where($where)
                ->save(['status' => self::STATUS_DELETED]);

        if ($flag) {
            $this->commit();

            return true;
        } else {
            $this->rollback();

            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    public function update_data($upcondition = [], $uid = 0) {
        $data['brand'] = $this->_getdata($upcondition);

        if (!$upcondition['id']) {
            return false;
        } else {
            $where['id'] = $upcondition['id'];
        }
        $data['updated_by'] = UID;
        $data['updated_at'] = date('Y-m-d H:i:s');
        try {
            $flag = $this->where($where)->save($data);

            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);

            return false;
        }
    }

    /**
     * 品牌数据组合
     * @param  mix $create 品牌数据
     * @return bool
     * @author zyg
     */
    private function _getdata($create) {

        $data = [
            'style' => $create['style'],
            'label' => $create['label'],
                //   'manufacturer' => $create['manufacturer']
        ];
        $datalist = [];
        foreach ($this->langs as $lang) {
            if (isset($create[$lang]) && isset($create[$lang]['name']) && $create[$lang]['name']) {

                $data['logo'] = $create[$lang]['logo'];
                $data['lang'] = $lang;
                $data['name'] = $create[$lang]['name'];
            }
            $datalist[] = $data;
        }
        return json_encode($datalist, 256);
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = [], $uid = '') {

        $data['brand'] = $this->_getdata($createcondition);
        unset($data['id']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $uid;
        try {
            $flag = $this->add($data);

            if (!$flag) {
                return false;
            }

            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);

            return false;
        }
    }

}
