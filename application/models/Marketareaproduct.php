<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Marketareaproduct
 *
 * @author zhongyg
 */
class MarketareaproductModel extends PublicModel {

    //put your code here
    protected $tableName = 'market_area_product';
    protected $dbName = 'erui_dict';

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
        if (isset($condition['market_area_bn'])) {
            $data['market_area_bn'] = $condition['market_area_bn'];
        }
        if (isset($condition['spu'])) {
            $data['spu'] = $condition['spu'];
        }
        if (isset($condition['status'])) {
            $data['status'] = $condition['status'];
        } else {
            $data['status'] = 'VALID';
        }
        if (isset($condition['created_by'])) {
            $data['created_by'] = $condition['created_by'];
        }
        return $data;
    }

    /**
     * 获取列表
     * @param string $name 国家名称;
     * @param string $lang 语言
     * @return array
     * @author jhw
     */
    public function getbnbynameandlang($name, $lang = 'zh') {

        try {
            $data = ['name' => $name, 'lang' => $lang];
            $row = $this->table('erui_dict.country')->field('region')
                    ->where($data)
                    ->find();
            if ($row) {
                return $row['region'];
            } else {
                return 'Asia';
            }
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return 'China';
        }
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
            return $this->where($data)->limit($current_no-1, $pagesize)
                            ->order('created_at desc')->select();
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
            return $this->where(['id' => $id])->find();
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
