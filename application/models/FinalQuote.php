<?php
/**
 * @desc 最终报价单模型
 * @author liujf 2017-06-21
 */
class FinalQuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote';
    
    public function __construct() {
        parent::__construct();
    }
    
	/**
     * @desc 获取查询条件
 	 * @author liujf 2017-06-27
     * @param array $condition
     * @return array
     */
     public function getWhere($condition) {
     	$where = array();
     	
     	if(!empty($condition['quote_no'])) {
    		$where['quote_no'] = $condition['quote_no'];
    	}
    	
     	if(!empty($condition['quote_status'])) {
    		$where['quote_status'] = $condition['quote_status'];
    	}
    	
    	return $where;
     }
     
    /**
     * @desc 获取记录总数
 	 * @author liujf 2017-06-27
     * @param array $condition 
     * @return int $count
     */
    public function getCount($condition) {
    	$where = $this->getWhere($condition);
    	
    	$count = $this->where($where)->count('id');
    	
    	return $count > 0 ? $count : 0;
    }
    
    /**
     * @desc 获取报价单列表
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getList($condition) {
    	
    	$where = $this->getWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		return $this->where($where)->page($condition['currentPage'], $condition['pageSize'])->select();
    	} else {
    		return $this->where($where)->select();
    	}
    }   
    
	/**
     * @desc 获取报价单详情
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getDetail($condition) {
    	
    	$where = $this->getWhere($condition);
    	
        return $this->where($where)->find();
    }   

}
