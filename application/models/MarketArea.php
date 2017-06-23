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
class MarketAreaModel extends PublicModel {

    //put your code here
    protected $dbName='erui_dict';
    protected $tableName = 'market_area';
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
            return $this->field('id,lang,bn,parent_bn,name')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            return $this->field('id,lang,bn,parent_bn,name')
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
                ->field('id,lang,bn,name,time_zone,region')
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
        if(isset($data['lang'])){
            $arr['lang'] = $data['lang'];
        }
        if(isset($data['bn'])){
            $arr['bn'] = $data['bn'];
        }
        if(isset($data['name'])){
            $arr['name'] = $data['name'];
        }
        if(isset($data['time_zone'])){
            $arr['time_zone'] = $data['time_zone'];
        }
        if(isset($data['region'])){
            $arr['region'] = $data['region'];
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
        if(isset($create['lang'])){
            $arr['lang'] = $create['lang'];
        }
        if(isset($create['bn'])){
            $arr['bn'] = $create['bn'];
        }
        if(isset($create['name'])){
            $arr['name'] = $create['name'];
        }
        if(isset($create['time_zone'])){
            $arr['time_zone'] = $create['time_zone'];
        }
        if(isset($create['region'])){
            $arr['region'] = $create['region'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

}
