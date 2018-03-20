<?php
/*
 * @desc 信保政策模型
 * 
 * @author liujf 
 * @time 2018-03-19
 */
class SinosurePolicyModel extends PublicModel {

    protected $dbName = 'erui_config';
    protected $tableName = 'sinosure_policy';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2018-03-19
     */
     public function getWhere($condition = []) {
         $where = [];
         if (!empty($condition['country_bn'])) {
             $where['country_bn'] = $condition['country_bn'];
         }
         if (!empty($condition['type'])) {
             $where['type'] = $condition['type'];
         }
         if (!empty($condition['company'])) {
             $where['company'] = ['like', '%' . $condition['company'] . '%'];
         }
         return $where;
     }
     
	/**
     * @desc 获取记录总数
 	 * 
     * @param array $condition 
     * @return int $count
     * @author liujf 
     * @time 2018-03-19
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
     * @time 2018-03-19
     */
    public function getList($condition = [], $field = '*') {
    	$where = $this->getWhere($condition);
    	//$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
    	//$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    	return $this->field($field)
    	                    ->where($where)
    	                    //->page($currentPage, $pageSize)
    	                    ->order('id DESC')
    	                    ->select();
    }
    
    /**
     * @desc 获取分组列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2018-03-20
     */
    public function getGroupList($condition = [], $group = '', $field = '*') {
        $where = $this->getWhere($condition);
        //$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        //$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        return $this->field($field)
                            ->where($where)
                            //->page($currentPage, $pageSize)
                            ->group($group)
                            ->order('id DESC')
                            ->select();
    }
    
    /**
     * @desc 获取根据国家分组的记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2018-03-19
     */
    public function getCountGroupByCountry($condition = []) {
        $where = $this->getWhere($condition);
        $count = $this->field('country_bn')->where($where)->group('country_bn')->select();
        return count($count);
    }
    
    /**
     * @desc 获取根据国家分组的列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2018-03-20
     */
    public function getListGroupByCountry($condition = [], $field = '*') {
        $where = $this->getWhere($condition);
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        return $this->field($field)
                            ->where($where)
                            ->page($currentPage, $pageSize)
                            ->group('country_bn')
                            ->order('id DESC')
                            ->select();
    }
    
	/**
     * @desc 获取详情
 	 * 
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf 
     * @time 2018-03-19
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
     * @time 2018-03-19 
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
     * @time 2018-03-19
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
     * @time 2018-03-19
	 */
	public function delRecord($condition = []) {
		$where = $this->getWhere($condition);
		return $this->where($where)->delete();
	}
}
