<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class ExchangeRateModel extends PublicModel {

    //put your code here
<<<<<<< HEAD
    protected $dbName = 'erui_config';
    protected $tableName = 'exchange_rate';

=======
    protected $dbName='erui_config';
    protected $tableName = 'exchange_rate';
>>>>>>> leo
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
<<<<<<< HEAD
    public function getlist($data, $limit, $order = 'id desc') {
        if (!empty($limit)) {
=======
    public function getlist($data,$limit,$order='id desc') {
        if(!empty($limit)){
>>>>>>> leo
            return $this->field('id,effective_date,currency1,currency2,rate')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
<<<<<<< HEAD
        } else {
            return $this->field('id,effective_date,currency1,currency2,rate')
                            ->where($data)
                            ->order($order)
                            ->select();
=======
        }else{
            return $this->field('id,effective_date,currency1,currency2,rate')
                ->where($data)
                ->order($order)
                ->select();
>>>>>>> leo
        }
    }

    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
<<<<<<< HEAD
        if (!empty($where['id'])) {
            $row = $this->where($where)
                    ->field('id,effective_date,currency1,currency2,rate')
                    ->find();
            return $row;
        } else {
=======
        if(!empty($where['id'])){
            $row = $this->where($where)
                ->field('id,effective_date,currency1,currency2,rate')
                ->find();
            return $row;
        }else{
>>>>>>> leo
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
<<<<<<< HEAD
        if (!empty($where['id'])) {
            return $this->where($where)
                            ->save(['status' => 'DELETED', 'deleted_flag' => 'Y']);
        } else {
=======
        if(!empty($where['id'])){
            return $this->where($where)
                ->save(['status' => 'DELETED']);
        }else{
>>>>>>> leo
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
<<<<<<< HEAD
    public function update_data($data, $where) {
        if (isset($data['effective_date'])) {
            $arr['effective_date'] = $data['effective_date'];
        }
        if (isset($data['currency1'])) {
            $arr['currency1'] = $data['currency1'];
        }
        if (isset($data['currency2'])) {
            $arr['currency2'] = $data['currency2'];
        }
        if (isset($data['rate'])) {
            $arr['rate'] = $data['rate'];
        }
        if (!empty($where)) {
            return $this->where($where)->save($arr);
        } else {
=======
    public function update_data($data,$where) {
        if(isset($data['effective_date'])){
            $arr['effective_date'] = $data['effective_date'];
        }
        if(isset($data['currency1'])){
            $arr['currency1'] = $data['currency1'];
        }
        if(isset($data['currency2'])){
            $arr['currency2'] = $data['currency2'];
        }
        if(isset($data['rate'])){
            $arr['rate'] = $data['rate'];
        }
        if(!empty($where)){
            return $this->where($where)->save($arr);
        }else{
>>>>>>> leo
            return false;
        }
    }

<<<<<<< HEAD
    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['effective_date'])) {
            $arr['effective_date'] = $create['effective_date'];
        }
        if (isset($create['currency1'])) {
            $arr['currency1'] = $create['currency1'];
        }
        if (isset($create['currency2'])) {
            $arr['currency2'] = $create['currency2'];
        }
        if (isset($create['rate'])) {
            $arr['rate'] = $create['rate'];
        }
        $arr['create_at'] = date('Y-m-d H:i:s');
        $arr['create_by'] = UID;
        $data = $this->create($arr);
        return $this->add($data);
    }

    /*
     * 条件
     */

    function getCondition($condition) {
        $where = [];
        if (isset($condition['effective_date']) && $condition['effective_date']) {
            $where['effective_date'] = $condition['effective_date'];
        }
        if (isset($condition['currency1']) && $condition['currency1']) {
            $where['currency1'] = $condition['currency1'];
        }
        if (isset($condition['currency2']) && $condition['currency2']) {
            $where['currency2'] = $condition['currency2'];
        }

        return $where;
    }

    /*
     * 获取数据
     */

    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            return $this->where($data)->count();
        } catch (Exception $ex) {

            return 0;
        }
    }

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     */
    public function getListbycondition($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'id,effective_date,currency1,currency2,rate,create_by,create_at';

            $pagesize = 50;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }
            $from = ($current_no - 1) * $pagesize;
            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            return $result;
        } catch (Exception $e) {
            return array();
        }
    }
=======


    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['effective_date'])){
            $arr['effective_date'] = $create['effective_date'];
        }
        if(isset($create['currency1'])){
            $arr['currency1'] = $create['currency1'];
        }
        if(isset($create['currency2'])){
            $arr['currency2'] = $create['currency2'];
        }
        if(isset($create['rate'])){
            $arr['rate'] = $create['rate'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }
>>>>>>> leo

}
