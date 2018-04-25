<?php
/*
 * @desc 物流费用明细模型
 * 
 * @author liujf 
 * @time 2018-04-24
 */
class QuoteLogiCostModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_logi_cost';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2018-04-24
     */
     public function getWhere($condition = []) {
         $where = [];
         if (!empty($condition['inquiry_id'])) {
             $where['inquiry_id'] = $condition['inquiry_id'];
         }
         if (!empty($condition['quote_id'])) {
             $where['quote_id'] = $condition['quote_id'];
         }
         if (!empty($condition['unit'])) {
             $where['unit'] = $condition['unit'];
         }
         if (!empty($condition['qty'])) {
             $where['qty'] = $condition['qty'];
         }
         if (!empty($condition['type'])) {
             $where['type'] = $condition['type'];
         }
         return $where ? : ['id' => '-1'];
     }
     
	/**
     * @desc 获取记录总数
 	 * 
     * @param array $condition 
     * @return int $count
     * @author liujf 
     * @time 2018-04-24
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
     * @time 2018-04-24
     */
    public function getList($condition = [], $field = '*') {
    	$where = $this->getWhere($condition);
    	//$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
    	//$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    	return $this->field($field)
                        	->where($where)
                        	//->page($currentPage, $pageSize)
                        	->order('id')
                        	->select();
    }   
    
    /**
     * @desc 添加记录
     * 
     * @param array $condition
     * @return mixed
     * @author liujf 
     * @time 2018-04-24 
     */
    public function addRecord($condition = []) {
        $data = $this->create($condition);
        return $this->add($data);
    }

	/**
	 * @desc 删除记录
	 * 
	 * @param array $condition
	 * @return bool
     * @author liujf 
     * @time 2018-04-24
	 */
	public function delRecord($condition = []) {
	    $where = $this->getWhere($condition);
		return $this->where($where)->delete();
	}
}
