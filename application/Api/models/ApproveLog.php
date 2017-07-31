<?php
/**
 * @desc 审核日志模型
 * @author liujf 2017-07-01
 */
class ApproveLogModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'approve_log';
    
    public function __construct() {
        parent::__construct();
    }
    
	/**
     * @desc 获取查询条件
 	 * @author liujf 2017-07-01
     * @param $condition array
     * @return $where array
     */
    public function getWhere($condition) {
    	$where = array();

		if (!empty($condition['id'])) {
			$where['id'] = $condition['id'];
		}
    	
    	if (!empty($condition['inquiry_no'])) {
            $where['inquiry_no'] = $condition['inquiry_no'];
        }
        
    	if (!empty($condition['type'])) {
            $where['type'] = $condition['type'];
        }
        
    	if (!empty($condition['belong'])) {
            $where['belong'] = $condition['belong'];
        }
        
    	if (!empty($condition['approver_id'])) {
            $where['approver_id'] = $condition['approver_id'];
        }
        
    	if (!empty($condition['approver'])) {
            $where['approver'] = $condition['approver'];
        }
        
    	if (!empty($condition['status'])) {
            $where['status'] = $condition['status'];
        }
        
    	if(!empty($condition['start_time']) && !empty($condition['end_time'])){
            $where['created_at'] = array(
                array('egt', date('Y-m-d H:i:s', strtotime($condition['start_time']))),
                array('elt', date('Y-m-d H:i:s', strtotime($condition['end_time'])))
            );
        }
    	
    	return $where;
    }
    
	/**
     * @desc 获取记录总数
 	 * @author liujf 2017-07-01
     * @param array $condition 
     * @return int $count
     */
    public function getCount($condition) {
    	$where = $this->getWhere($condition);

    	$count = $this->where($where)->count('id');

    	return $count > 0 ? $count : 0;
    }
    
	/**
     * @desc 获取列表
 	 * @author liujf 2017-07-01
     * @param array $condition
     * @return array
     */
    public function getList($condition) {
    	$where = $this->getWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		return $this->where($where)->page($condition['currentPage'], $condition['pageSize'])->select();
    	} else {
    		return $this->where($where)->page(1, 10)->select();
    	}
    }
    
	/**
	 * @desc 添加数据
	 * @author liujf 2017-07-01
	 * @param array $condition
	 * @return array
	 */
	public function addData($condition) {
		
		$data = $this->create($condition);

		return $this->add($data);
	}

	/**
	 * @desc 获取详情
	 * @author liujf 2017-07-01
	 * @param array $condition
	 * @return array
	 */
	public function getDetail($condition) {

		$where = $this->getWhere($condition);

		return $this->where($where)->find();
	}
	

	/**
	 * @desc 修改数据
	 * @author liujf 2017-07-01
	 * @param array $where , $condition
	 * @return array
	 */
	public function updateData($condition, $data) {

		$where = $this->getWhere($condition);

		$inData = $this->create($data);

		return $this->where($where)->save($inData);
	}

	/**
	 * @desc 删除数据
	 * @author liujf 2017-07-01
	 * @param array $condition
	 * @return array
	 */
	public function delData($condition) {
		
		$where = $this->getWhere($condition);

		return $this->where($where)->delete();
	}
}
