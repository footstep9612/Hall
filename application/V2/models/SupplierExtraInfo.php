<?php
/*
 * @desc 供应商其他信息模型
 * 
 * @author liujf 
 * @time 2017-11-10
 */
class SupplierExtraInfoModel extends PublicModel {

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_extra_info';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2017-11-10
     */
     public function getWhere($condition = []) {
         
         $where['deleted_flag'] = 'N';
         
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
     * @time 2017-11-10
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
     * @time 2017-11-10
     */
    public function getList($condition = [], $field = '*') {
        
    	$where = $this->getWhere($condition);
    	
    	$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
    	$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    	
    	return $this->field($field)->where($where)->page($currentPage, $pageSize)->order('id DESC')->select();
    }   
    
	/**
     * @desc 获取详情
 	 * 
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf 
     * @time 2017-11-10
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
     * @time 2017-11-10 
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
     * @time 2017-11-10
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
     * @time 2017-11-10
	 */
	public function delRecord($condition = []) {

		if (!empty($condition['supplier_id'])) {
			$where['supplier_id'] = ['in', explode(',', $condition['supplier_id'])];
		} else {
			return false;
		}

		return $this->where($where)->save(['deleted_flag' => 'Y']);
	}
}
