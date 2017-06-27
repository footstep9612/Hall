<?php
/**
 * @desc 报价单模型
 * @author liujf 2017-06-17
 */
class QuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
     public function getWhere($condition) {
     	if(isset($condition['quote_no'])) {
    		$where['quote_no'] = $condition['quote_no'];
    	}
    	
     	if(isset($condition['biz_quote_status'])) {
    		$where['biz_quote_status'] = $condition['biz_quote_status'];
    	}
    	
     	if(isset($condition['logi_quote_status'])) {
    		$where['logi_quote_status'] = $condition['logi_quote_status'];
    	}
    	
     }
    
    
    /**
     * @desc 获取报价单列表
 	 * @author liujf 2017-06-17
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
