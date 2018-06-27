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
class SupplierContactModel extends PublicModel
{

    //put your code here
    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_contact';

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }


    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['supplier_id'])){
            $arr['supplier_id'] = $create['supplier_id'];
        }
        if(isset($create['first_name'])){
            $arr['first_name'] = $create['first_name'];
        }
        if(isset($create['last_name'])){
            $arr['last_name'] = $create['last_name'];
        }
        if(isset($create['gender'])){
            $arr['gender'] = $create['gender'];
        }
        if(isset($create['title'])){
            $arr['title'] = $create['title'];
        }
        if(isset($create['phone'])){
            $arr['phone'] = $create['phone'];
        }
        if(isset($create['email'])){
            $arr['email'] = $create['email'];
        }
        if(isset($create['remarks'])){
            $arr['remarks'] = $create['remarks'];
        }
        if(isset($create['created_by'])){
            $arr['created_by'] =$create['created_by'];
        }
        $arr['created_at'] =date("Y-m-d H:i:s");
        try{
            $data = $this->create($arr);
            return $this->add($data);
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }

    }
    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition,$where){
        if(isset($condition['first_name'])){
            $arr['first_name'] = $condition['first_name'];
        }
        if(isset($condition['last_name'])){
            $arr['last_name'] = $condition['last_name'];
        }
        if(isset($condition['gender'])){
            $arr['gender'] = $condition['gender'];
        }
        if(isset($condition['title'])){
            $arr['title'] = $condition['title'];
        }
        if(isset($condition['phone'])){
            $arr['phone'] = $condition['phone'];
        }
        if(isset($condition['email'])){
            $arr['email'] = $condition['email'];
        }
        if(isset($condition['remarks'])){
            $arr['remarks'] = $condition['remarks'];
        }
        if (!empty($where)&&isset($arr)) {
            $info = $this->where($where)->find();
            if(!$info){
                $arr['supplier_id']=$where['supplier_id'];
                $this->create_data($arr);
            }else{
                return $this->where($where)->save($arr);
            }
        } else {
            return false;
        }
    }
    
    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-11
     */
    public function getWhere($condition = []) {
        $where = [];
        
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        }
         
        if (!empty($condition['supplier_id'])) {
            $where['supplier_id'] = $condition['supplier_id'];
        }
         
        return $where;
    }
    
    /**
     * @desc 获取记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-11-11
     */
    public function getCount($condition = []) {
    
        $where = $this->getWhere($condition);
         
        $count = $this->where($where)->count('id');
         
        return $count > 0 ? $count : 0;
    }
    
    /**
     * @desc 获取列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-11-11
     */
    public function getList($condition = [], $field = '*') {
    
        $where = $this->getWhere($condition);
         
        //$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        //$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
         
        return $this->field($field)
                            ->where($where)
                            //->page($currentPage, $pageSize)
                            ->order('id DESC')
                            ->select();
    }
    
    /**
     * @desc 获取详情
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-11-11
     */
    public function getDetail($condition = [], $field = '*') {
         
        $where = $this->getWhere($condition);
         
        return $this->field($field)->where($where)->order('id DESC')->find();
    }
    
    /**
     * @desc 添加记录
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-11-11
     */
    public function addRecord($condition = []) {
    
        $data = $this->create($condition);
    
        return $this->add($data);
    }
    
    /**
     * @desc 修改信息
     *
     * @param array $where , $condition
     * @return bool
     * @author liujf
     * @time 2017-11-11
     */
    public function updateInfo($where = [], $condition = []) {
    
        $data = $this->create($condition);
    
        return $this->where($where)->save($data);
    }
    
    /**
     * @desc 删除记录
     *
     * @param array $condition
     * @return bool
     * @author liujf
     * @time 2017-08-08
     */
    public function delRecord($condition = []) {
    
        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        } else {
            return false;
        }
    
        return $this->where($where)->delete();
    }
    
}
