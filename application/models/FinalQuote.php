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
     * @desc 获取报价单详情
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getDetail($condition) {
    	
    	if(isset($condition['inquiry_no'])) {
    		$where['inquiry_no'] = $condition['inquiry_no'];
    	}
    	
        return $this->where($where)->find();
    }   

}
