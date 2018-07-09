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
class System_RoleAccessPermModel extends PublicModel {

    //put your code here
    protected $tableName = 'role_access_perm';
    Protected $autoCheckFields = true;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'rap.id desc') {
        $sql = 'SELECT rap.id,rap.role_id,r.name as role_name,rap.url_perm_id,up.description as url_perm_name,rap.perm_flag ';
        $sql .= ' FROM ' . $this->tableName . 'as ug';
        $sql .= ' LEFT JOIN t_url_perm AS up ON up.`id` = rap.`url_perm_id`';
        $sql .= ' LEFT JOIN t_role AS r ON r.`id` = rap.`role_id`';
        if (!empty($data['role_id'])) {
            $sql .= ' WHERE rap.`role_id` = ' . $data['role_id'];
        }
        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit['page'] . ',' . $limit['num'];
        }
        $sql .= ' ORDER BY ' . $order;
        $res = $this->query($sql);
        return $res;
    }

//    /**
//     * 获取列表
//     * @param  int  $id
//     * @return array
//     * @author jhw
//     */
//    public function detail($id = '') {
//        $where['id'] = $id;
//        if(!empty($where['id'])){
//            $row = $this->where($where)
//                ->field('id,parent_id,name,description,status')
//                ->find();
//            return $row;
//        }else{
//            return false;
//        }
//    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            return $this->where($where)
                            ->dete(['status' => 'DELETED']);
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {
        if (isset($data['id'])) {
            $arr['id'] = $data['id'];
        }
        if (!empty($where)) {
            return $this->where($where)->save($arr);
        } else {
            return false;
        }
    }

    public function update_datas($data) {
        if ($data['role_id']) {
            $this->where(['role_id' => $data['role_id']])->delete();
            if ($data['url_perm_ids']) {
                $url_perm_id_arr = explode(',', $data['url_perm_ids']);
                $count = count($url_perm_id_arr);
                for ($i = 0; $i < $count; $i++) {
                    $this->create_data(['role_id' => $data['role_id'], 'func_perm_id' => $url_perm_id_arr[$i]]);
                }
            }
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['role_id'])) {
            $arr['role_id'] = $create['role_id'];
        }
        if (isset($create['func_perm_id'])) {
            $arr['func_perm_id'] = $create['func_perm_id'];
        }
        if (isset($create['perm_flag'])) {
            $arr['perm_flag'] = $create['perm_flag'];
        }
        $arr['created_at'] = date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }

}
