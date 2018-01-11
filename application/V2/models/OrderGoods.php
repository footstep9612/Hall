<?php
/*
 * @desc 订单商品模型
 * 
 * @author liujf 
 * @time 2018-01-10
 */
class OrderGoodsModel extends PublicModel {

    protected $dbName = 'erui_order';
    protected $tableName = 'order_goods';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2018-01-10
     */
     public function getWhere($condition = []) {
         $where['deleted_flag'] = 'N';
         if (!empty($condition['id'])) {
             $where['id'] = $condition['id'];
         }
         if (!empty($condition['order_id'])) {
             $where['order_id'] = $condition['order_id'];
         }
         return $where;
     }
     
	/**
     * @desc 获取记录总数
 	 * 
     * @param array $condition 
     * @return int $count
     * @author liujf 
     * @time 2018-01-10
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
     * @time 2018-01-10
     */
    public function getList($condition = [], $field = '*') {
    	$where = $this->getWhere($condition);
    	//$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
    	//$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    	return $this->field($field)
    	                    ->where($where)
    	                    //->page($currentPage, $pageSize)
    	                    ->order('updated_at DESC')
    	                    ->select();
    }   
    
	/**
     * @desc 获取详情
 	 * 
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf 
     * @time 2018-01-10
     */
    public function getDetail($condition = [], $field = '*') {
    	$where = $this->getWhere($condition);
        return $this->field($field)->where($where)->order('id DESC')->find();
    }
    
    /**
     * @desc 添加记录
     * 
     * @param array $condition
     * @return mixed
     * @author liujf 
     * @time 2018-01-10 
     */
    public function addRecord($condition = []) {
        $data = $this->create($condition);
        return $this->add($data);
    }

	/**
	 * @desc 修改信息
	 * 
	 * @param array $where , $condition
	 * @return bool
     * @author liujf 
     * @time 2018-01-10
	 */
	public function updateInfo($where = [], $condition = []) {
		$data = $this->create($condition);
		return $this->where($where)->save($data);
	}

	/**
	 * @desc 删除记录
	 * 
	 * @param array $condition
	 * @return bool
     * @author liujf 
     * @time 2018-01-10
	 */
	public function delRecord($condition = []) {
		if (!empty($condition['id'])) {
			$where['id'] = ['in', explode(',', $condition['id'])];
		} else {
			return false;
		}
		return $this->where($where)->save(['deleted_flag' => 'Y']);
	}
}
