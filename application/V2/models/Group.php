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
    protected $dbName = 'erui2_sys'; //数据库名称
    protected $tableName = 'org';
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
        $data["org.deleted_flag"] = 'N';
        if(!empty($limit)){
            return    $this->field('org.id,org.sort,org.membership,org.parent_id,org.org,org.name,org.remarks,org.created_by,org.created_at,org.deleted_flag,group_concat(`em`.`name`) as employee_name')
                            ->join('`erui2_sys`.`org_member` om on om.org_id=org.id', 'left')
                            ->join('`erui2_sys`.`employee` em on em.id=`om`.`employee_id`', 'left')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->group('org.id')
                            ->order($order)
                            ->select();
        }else{
           $res = $this->field('org.id,org.sort,org.membership,org.parent_id,org.org,org.name,org.remarks,org.created_by,org.created_at,org.deleted_flag,group_concat(`em`.`name`) as employee_name')
                ->join('`erui2_sys`.`org_member` om on om.org_id=org.id', 'left')
                ->join('`erui2_sys`.`employee` em on em.id=`om`.`employee_id`', 'left')
                ->where($data)
                ->group('org.id')
                ->order($order)
                ->select();
            return $res;
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
                ->field('id,membership,sort,parent_id,org,name,remarks,created_by,created_at,deleted_flag')
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
                ->save(['deleted_flag' => 'N']);
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
        if(isset($data['membership'])){
            $arr['membership'] = $data['membership'];
        }
        if(isset($data['name'])){
            $arr['name'] = $data['name'];
        }
        if(isset($data['remarks'])){
            $arr['remarks'] = $data['remarks'];
        }
        if(isset($data['deleted_flag'])){
            $arr['deleted_flag'] = $data['deleted_flag'];
        }
        if(isset($create['sort'])){
            $arr['sort'] = $create['sort'];
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
        if(isset($create['membership'])){
            $arr['membership'] = $create['membership'];
        }
        if(isset($create['org'])){
            $arr['org'] = $create['org'];
        }
        if(isset($create['name_en'])){
            $arr['name_en'] = $create['name_en'];
        }
        if(isset($create['status'])){
            $arr['status'] = $create['status'];
        }
        if(isset($create['sort'])){
            $arr['sort'] = $create['sort'];
        }
        if(isset($create['created_by'])){
            $arr['created_by'] = $create['created_by'];
        }
        if(isset($arr)){
            $arr['created_at'] = date("Y-m-d H:i:s");
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

}
