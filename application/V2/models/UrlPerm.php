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
    protected $tableName = 'func_perm';
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
    public function getlist($data,$limit,$order='sort desc') {
        if(!empty($limit)){
            return $this->field("id,fn,url,remarks,sort,parent_id,grant_flag,created_by,created_at,'false' as check")
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            return $this->field("id,fn,url,remarks,sort,parent_id,grant_flag,created_by,created_at,'false' as `check`")
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
                ->field('id,fn,url,sort,remarks,parent_id,grant_flag,created_by,created_at')
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
                ->delete();
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
        if(isset($data['remarks'])){
            $arr['remarks'] = $data['remarks'];
        }
        if(isset($data['fn'])){
            $arr['fn'] = $data['fn'];
        }
        if(isset($create['sort'])){
            $arr['sort'] = $create['sort'];
        }
        if(isset($data['parent_id'])){
            $arr['parent_id'] = $data['parent_id'];
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
        if(isset($create['grant_flag'])){
            $arr['grant_flag'] = $create['grant_flag'];
        }
        if(isset($create['fn'])){
            $arr['fn'] = $create['fn'];
        }
        if(isset($create['sort'])){
            $arr['sort'] = $create['sort'];
        }
        if(isset($create['remarks'])){
            $arr['remarks'] = $create['remarks'];
        }
        if(isset($create['created_at'])){
            $arr['created_at'] = date("Y-m-d H:i:s");
        }
        if(isset($create['created_by'])){
            $arr['created_by'] = $create['created_by'];
        }
        $arr['created_at'] = date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }

}
