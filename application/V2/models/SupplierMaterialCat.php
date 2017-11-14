<?php
/*
 * @desc 供应商与物料分类映射模型
 * 
 * @author liujf 
 * @time 2017-11-11
 */
class SupplierMaterialCatModel extends PublicModel {

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_material_cat';
    protected $joinTable1 = 'erui_goods.material_cat b ON a.material_cat_no1 = b.cat_no AND b.lang = \'zh\'';
    protected $joinTable2 = 'erui_goods.material_cat c ON a.material_cat_no2 = c.cat_no AND c.lang = \'zh\'';
    protected $joinField = 'a.*, b.name AS material_cat_name1, c.name AS material_cat_name2';
			    
    public function __construct() {
        parent::__construct();
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
      * @desc 获取关联查询条件
      *
      * @param array $condition
      * @return array
      * @author liujf
      * @time 2017-11-14
      */
     public function getJoinWhere($condition = []) {
     
         $where = [];
     
         if (!empty($condition['id'])) {
             $where['a.id'] = $condition['id'];
         }
     
        if (!empty($condition['supplier_id'])) {
             $where['a.supplier_id'] = $condition['supplier_id'];
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
     * @desc 获取关联记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-11-14
     */
    public function getJoinCount($condition = []) {
    
        $where = $this->getJoinWhere($condition);
    
        $count = $this->alias('a')
                                 ->join($this->joinTable1, 'LEFT')
                                 ->join($this->joinTable2, 'LEFT')
                                 ->where($where)
                                 ->count('a.id');
    
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
     * @desc 获取关联列表
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-14
     */
    public function getJoinList($condition = []) {
    
        $where = $this->getJoinWhere($condition);
    
        //$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        //$pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    
        return $this->alias('a')
                            ->join($this->joinTable1, 'LEFT')
                            ->join($this->joinTable2, 'LEFT')
                            ->field($this->joinField)
                            ->where($where)
                            //->page($currentPage, $pageSize)
                            ->order('a.id DESC')
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
     * @time 2017-11-11
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
