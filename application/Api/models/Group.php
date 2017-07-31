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
class GroupModel extends PublicModel {

    //put your code here
    protected $tableName = 'group';
    Protected $autoCheckFields = true;

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
        $data["status"] = 'NORMAL';
        if(!empty($limit)){
            return $this->field('id,parent_id,name,description')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            return $this->field('id,parent_id,name,description')
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
                ->field('id,group_no,parent_id,name,description,status')
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
        if(isset($data['parent_id'])){
            $arr['parent_id'] = $data['parent_id'];
        }
        if(isset($data['group_no'])){
            $arr['group_no'] = $data['group_no'];
        }
        if(isset($data['name'])){
            $arr['name'] = $data['name'];
        }
        if(isset($data['description'])){
            $arr['description'] = $data['description'];
        }
        if(isset($data['status'])){
            $arr['status'] = $data['status'];
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
        if(isset($create['parent_id'])){
            $arr['parent_id'] = $create['parent_id'];
        }else{
            $arr['parent_id'] = 0;
        }
        if(isset($create['name'])){
            $arr['name'] = $create['name'];
        }
        if(isset($create['group_no'])){
            $arr['group_no'] = $create['group_no'];
        }
        if(isset($create['description'])){
            $arr['description'] = $create['description'];
        }
        if(isset($create['status'])){
            $arr['status'] = $create['status'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

}
