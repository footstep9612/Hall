<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class UrlPermModel extends PublicModel {

    //put your code here
    protected $tableName = 'url_perm';
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
        if(!empty($limit)){
            return $this->field('id,url,description,parent_id,status')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            return $this->field('id,url,description,parent_id,status')
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
                ->field('id,url,parent_id,description,parent_id,status')
                ->find();
            return $row;
        }else{
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
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
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data,$where) {
        if(isset($data['url'])){
            $arr['url'] = $data['url'];
        }
        if(isset($data['description'])){
            $arr['description'] = $data['description'];
        }
        if(isset($data['parent_id'])){
            $arr['parent_id'] = $data['parent_id'];
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
        if(isset($create['url'])){
            $arr['url'] = $create['url'];
        }
        if(isset($create['parent_id'])){
            $arr['parent_id'] = $create['parent_id'];
        }
        if(isset($create['status'])){
            $arr['status'] = $create['status'];
        }
        if(isset($create['description'])){
            $arr['description'] = $create['description'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

}
