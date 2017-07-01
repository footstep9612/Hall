<?php
/**
 * @desc 报价单明细模型
 * @author liujf 2017-06-17
 */
class QuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item';
    protected $joinFinal = 'erui_rfq.t_final_quote_item b ON a.id = b.id';
    protected $fieldJoin = 'a.*, b.quote_unit_price AS final_quote_unit_price';
    
    public function __construct() {
        parent::__construct();
    }
    
	/**
     * @desc 获取查询条件
 	 * @author liujf 2017-06-27
     * @param $condition array
     * @return $where array
     */
    public function getWhere($condition) {
    	$where = array();
    	
    	if (!empty($condition['quote_no'])) {
            $where['quote_no'] = $condition['quote_no'];
        }
    	
    	return $where;
    }
    
	/**
     * @desc 获取关联查询条件
 	 * @author liujf 2017-06-30
     * @param array $condition
     * @return array
     */
     public function getJoinWhere($condition) {
     	$where = array();
     	
     	if(!empty($condition['quote_no'])) {
    		$where['a.quote_no'] = $condition['quote_no'];
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
     * @desc 获取关联记录总数
 	 * @author liujf 2017-06-30
     * @param array $condition 
     * @return int $count
     */
    public function getJoinCount($condition) {
    	
    	$where = $this->getJoinWhere($condition);
    	
    	$count = $this->alias('a')
    				  ->join($this->joinFinal, 'LEFT')
    				  ->field($this->fieldJoin)
    				  ->where($where)
    				  ->count('a.id');
    	
    	return $count > 0 ? $count : 0;
    }

	/**
     * @desc 获取报价单项目列表
 	 * @author liujf 2017-06-17
     * @param array $condition
     * @return array
     */
    public function getItemList($condition) {
    	$where = $this->getWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		return $this->where($where)->page($condition['currentPage'], $condition['pageSize'])->select();
    	} else {
    		return $this->where($where)->select();
    	}
    }
    
	/**
     * @desc 获取关联询价SKU列表
 	 * @author liujf 2017-06-30
     * @param array $condition
     * @return array
     */
    public function getJoinList($condition) {
    	
    	$where = $this->getJoinWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		
    		return $this->alias('a')
	    				 ->join($this->joinFinal, 'LEFT')
	    				 ->field($this->fieldJoin)
	    				 ->where($where)
	    				 ->page($condition['currentPage'], $condition['pageSize'])
	    				 ->select();
    	} else {
    		return $this->alias('a')
    					->join($this->joinFinal, 'LEFT')
    					->field($this->fieldJoin)
    					->where($where)
    					->select();
    	}
    }

	/**
	 * @desc 添加报价单SKU详情
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function addItem($condition) {
		$data = $this->create($condition);
		$data['status'] = !empty($condition['status'])?$condition['status']:'ONGOING';
		$data['created_at'] = time();

		return $this->add($data);
	}

	/**
	 * @desc 获取报价单SKU详情
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function getDetail($condition) {

		$where = $this->getWhere($condition);

		return $this->where($where)->find();
	}
	
	/**
     * @desc 获取关联询价SKU详情
 	 * @author liujf 2017-06-30
     * @param array $condition
     * @return array
     */
    public function getJoinDetail($condition) {
    	
    	$where = $this->getJoinWhere($condition);
    	
    	if (empty($where)) return false;
    	
    	return $this->alias('a')
    				->join($this->joinFinal, 'LEFT')
    				->field($this->fieldJoin)
    				->where($where)
    				->find();
    }

	/**
	 * @desc 修改报价单SKU
	 * @author zhangyuliang 2017-06-29
	 * @param array $where , $condition
	 * @return array
	 */
	public function updateItem($where = [], $condition = []) {

		if(empty($where['id'])){
			return false;
		}

		$data = $this->create($condition);

		return $this->where($where)->save($data);
	}

	/**
	 * @desc 删除报价单SKU
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function delItem($condition = []) {

		if(!empty($condition['quote_no'])) {
			$where['where'] = $condition['quote_no'];
		}else{
			return false;
		}

		return $this->where($where)->save(['status' => 'DELETED']);
	}
}
