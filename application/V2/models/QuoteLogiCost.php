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
     * @return mixed
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
	 * @return mixed
     * @author liujf 
     * @time 2018-04-24
	 */
	public function delRecord($condition = []) {
	    $where = $this->getWhere($condition);
		return $this->where($where)->delete();
	}
	
	/**
	 * @desc 获取物流费用历史价格列表
	 *
	 * @param array $condition
	 * -----------------------------------------
	 * from_country 起运国简称
	 * trade_terms_bn 贸易术语简称
	 * trans_mode_id 运输方式ID
	 * unit 计费单位
	 * qty 数量
	 * type 费用类型
	 * -----------------------------------------
	 * @param int $listCount 条数
	 * @return mixed
	 * @author liujf
	 * @time 2018-04-25
	 */
	public function getHistoricalPriceList($condition = [], $listCount = 3) {
	    //$lang = defined(LANG_SET) ? LANG_SET : 'zh';
	    $listCount = intval($listCount);
	    $quoteModel = new QuoteModel();
	    $employeeModel = new EmployeeModel();
	    $quoteTableName = $quoteModel->getTableName();
	    $employeeTableName = $employeeModel->getTableName();
	    $where = [
	        'b.from_country' => [['neq', ''], ['eq', $condition['from_country']]],
	        'b.trade_terms_bn' => [['neq', ''], ['eq', $condition['trade_terms_bn']]],
	        'b.trans_mode_bn' => [['neq', ''], ['eq', $condition['trans_mode_id']]],
	        'a.unit' => [['neq', ''], ['eq', $condition['unit']]],
	        'a.qty' => [['neq', ''], ['eq', $condition['qty']]],
	        'a.type' => [['neq', ''], ['eq', $condition['type']]],
	    ];
	    return $this->alias('a')
                    	    ->join($quoteTableName . ' b ON a.quote_id = b.id AND b.deleted_flag = \'N\'', 'LEFT')
                    	    ->join($employeeTableName . ' c ON a.created_by = c.id AND c.deleted_flag = \'N\'', 'LEFT')
                    	    //->field('a.price, a.cur_bn, a.created_at, c.' . ($lang == 'zh' ? 'name' : 'name_en') . ' AS created_name')
                    	    ->field('a.price, a.cur_bn, a.created_at, c.name AS created_name')
                    	    ->where($where)
                    	    ->page(1, $listCount)
                    	    ->order('a.id DESC')
                    	    ->select();
	}
}
