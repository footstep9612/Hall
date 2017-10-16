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
            //,'false' as check
            return $this->field("id,fn,fn_group,show_name,url,remarks,sort,parent_id,grant_flag,created_by,created_at")
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            //,'false' as `check`
            return $this->field("id,fn,fn_group,show_name,url,remarks,sort,parent_id,grant_flag,created_by,created_at")
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
                ->field('id,fn,fn_group,show_name,url,sort,remarks,parent_id,grant_flag,created_by,created_at')
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
        $arr = $this->create($data);
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
        $data = $this->create($create);
        $data['created_at'] = date("Y-m-d H:i:s");
        return $this->add($data);
    }

}
