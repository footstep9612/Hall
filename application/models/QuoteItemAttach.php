<?php
/**
 * @desc 报价单明细附件模型
 * @author liujf 2017-06-17
 */
class QuoteItemAttachModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item_attach';
    
    public function __construct() {
        parent::__construct();
    }
    
	/**
     * @desc 获取查询附件where条件
 	 * @author liujf 2017-06-17
     * @param $condition array
     * @return $where array
     */
    public function getAttachWhere($condition = array()) {
    	$where = array();
    	
    	if (isset($condition['quote_no'])) {
            $where['quote_no'] = $condition['quote_no'];
        }
    	
    	if (isset($condition['attach_type'])) {
            $where['attach_type'] = $condition['attach_type'];
        }
    	
    	return $where;
    }

	/**
     * @desc 获取报价单明细附件
 	 * @author liujf 2017-06-17
     * @param $condition array
     * @return array
     */
    public function getAttachList($condition = array()) {
    	
    	$where = $this->getAttachWhere($condition);
    	
        return $this->where($where)->select();
    }

}
