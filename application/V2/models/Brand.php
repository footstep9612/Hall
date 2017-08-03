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

    public function __construct() {
        parent::__construct();
    }

    public function getcondition($name, $status = 'VALID') {

        $where = [];
        if (!empty($name)) {
            $where['name'] = ['like', '%' . $name . '%'];
        }
        if (!empty($status)) {
            $where['status'] = $status;
        }

        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($name, $status = 'VALID') {
        $where = $this->getcondition($name, $lang);
        try {
            return $this->where($where)
                            ->count('id');
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($name, $status = 'VALID', $current_no = 1, $pagesize = 10) {
        $where = $this->getcondition($name, $status);
        if (intval($current_no) <= 1) {
            $row_start = 0;
        } else {
            $row_start = (intval($current_no) - 1) * $pagesize;
        }
        if ($pagesize < 1) {
            $pagesize = 10;
        }
        return $this->where($where)
                        ->field('id,brand,status,created_by,'
                                . 'created_at,updated_by,updated_at')
                        ->order('id desc')
                        ->limit($row_start . ',' . $pagesize)
                        ->select();
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function listall($name, $status = 'VALID') {
        $where = $this->getcondition($name, $status);
        return $this->where($where)
                        ->field('id,brand')
                        ->order('id desc')
                        ->select();
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
        if ($status) {
            $where['status'] = $status;
        }
        return $this->where($where)
                        ->find();
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
     * @param  string $brand_no
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($id = 0) {
        if (!$id) {
            return false;
        } elseif ($id) {
            $where['id'] = $id;
        }

        $flag = $this->re($where)
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
    public function update_data($upcondition = [], $username = '') {
        $data = $this->create($upcondition);
        if (!$data['id']) {
            return false;
        } else {
            $where['id'] = $data['id'];
        }

        $data['updated_by'] = date('Y-m-d H:i:s');
        $data['updated_at'] = $username;


        $data['name'] = $upcondition['name'];


        $exist_flag = $this->Exist($where);
        $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($data);
        if (!$flag) {

            return false;
        }

        return $flag;
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = [], $username = '') {

        $data = $condition = $this->create($createcondition);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $username;

        $flag = $this->add($data);

        if (!$flag) {

            $this->rollback();
            return false;
        }

        return $flag;
    }

}
