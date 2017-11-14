<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class SupplierAgentModel extends PublicModel {
    //put your code here
    protected $tableName = 'supplier_agent';
    protected $dbName = 'erui_supplier'; //数据库名称
    protected $g_table = 'erui_supplier.supplier_agent';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效,通过
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_TEST = 'TEST'; //待报审；
    const STATUS_CHECKING = 'STATUS_CHECKING'; //审核；
    const STATUS_DELETED = 'DELETED'; //删除；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        return $this->where($condition)
            ->field('id,supplier_id,org_id,role,created_by,created_at')
            ->order('id desc')
            ->select();
    }

    public function create_data($create = [])
    {
        if(!empty($create['id'])) {
            $data['id'] = $create['id'];
        }else{
            return false;
        }
        if(!empty($create['org_ids'])) {
            $data['org_ids'] = $create['org_ids'];
        }else{
            return false;
        }
        $create['created_at'] = date('Y-m-d H:i:s');
        $org_arr = explode(',',$data['org_ids']);
        $this->where(['supplier_id' => $data['id']])->delete();
        for($i=0;$i<count($org_arr);$i++){
            $arr['org_id']=$org_arr[$i];
            $arr['supplier_id']=$data['id'];
            $arr['created_at']=$create['created_at'];
            $arr['created_by']=$create['created_by'];
            $datajson = $this->create($arr);
            $res = $this->add($datajson);
        }
        return true;
    }
    
    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-14
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
     * @time 2017-11-14
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
     * @time 2017-11-14
     */
    public function getAgentList($condition = [], $field = '*') {
    
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
     * @time 2017-11-14
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
     * @time 2017-11-14
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
     * @time 2017-11-14
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
     * @time 2017-11-14
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
