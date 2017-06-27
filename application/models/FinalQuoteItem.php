<?php
/**
 * @desc 最终报价单明细模型
 * @author liujf 2017-06-21
 */
class FinalQuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote_item';
    
    public function __construct() {
        parent::__construct();
    }

	/**
     * @desc 获取报价单项目列表
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getItemList($condition) {
    	if(isset($condition['quote_no'])) {
    		$where['quote_no'] = $condition['quote_no'];
    	}
    	
        return $this->where($where)->select();
    }

}
