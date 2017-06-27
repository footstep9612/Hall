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
     	if(isset($condition['quote_no'])) {
    		$where['quote_no'] = $condition['quote_no'];
    	}
    	
     	if(isset($condition['quote_status'])) {
    		$where['quote_status'] = $condition['quote_status'];
    	}
     }
     
    /**
     * @desc 获取报价单列表
 	 * @author liujf 2017-06-27
     * @param array $condition
     * @return array
     */
    public function getList($condition) {
    	
    	$where = $this->getWhere($condition);
    	
        return $this->where($where)->select();
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
