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
    protected $dbName='erui_db_ddl_config';
    protected $tableName = 'exchange_rate';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data,$limit,$order='id desc') {
        if(!empty($limit)){
            return $this->field('id,effective_date,currency1,currency2,rate')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            return $this->field('id,effective_date,currency1,currency2,rate')
                ->where($data)
                ->order($order)
                ->select();
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
        if(!empty($where['id'])){
            $row = $this->where($where)
                ->field('id,effective_date,currency1,currency2,rate')
                ->find();
            return $row;
        }else{
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
        if(!empty($where['id'])){
            return $this->where($where)
                ->save(['status' => 'DELETED']);
        }else{
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
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
            return false;
        }
    }



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

}
