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
     	$where = array();

     	if(!empty($condition['quote_no'])) {
    		$where['quote_no'] = $condition['quote_no'];
    	}
    	
     	if(!empty($condition['biz_quote_status'])) {
    		$where['biz_quote_status'] = array('in', $condition['biz_quote_status']);
    	}
    	
     	if(!empty($condition['logi_quote_status'])) {
    		$where['logi_quote_status'] = $condition['logi_quote_status'];
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

	/**
	 * @desc 修改报价单
	 * @author zhangyuliang 2017-06-29
	 * @param array $where , $condition
	 * @return array
	 */
	public function updateQuote($where = [], $condition = []) {

		if(empty($where['quote_no'])){
			return false;
		}

		$data = $this->create($condition);

		return $this->where($where)->save($data);
	}

	/**
	 * @desc 删除报价单
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function delQuote($condition = []) {

		if(!empty($condition['quote_no'])) {
			$where['where'] = $condition['quote_no'];
		}else{
			return false;
		}

		return $this->where($where)->save(['quote_status' => 'DELETED']);
	}
}
