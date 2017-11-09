<?php
/*
 * @desc 部门成员模型
 * 
 * @author liujf 
 * @time 2017-08-29
 */
class OrgMemberModel extends PublicModel {

    protected $dbName = 'erui_sys';
    protected $tableName = 'org_member';
			    
    public function __construct() {
        parent::__construct();
    }
    
/**
     * @desc 获取查询条件
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2017-08-29
     */
     public function getWhere($condition = []) {
         
     	$where = [];
     	
     	if(!empty($condition['org_id'])) {
     	    $where['org_id'] = ['in', $condition['org_id']];
     	}
     	
     	if(!empty($condition['employee_id'])) {
     	    $where['employee_id'] = $condition['employee_id'];
     	}
    	
    	return $where;
    	
     }
     
	/**
     * @desc 获取记录总数
 	 * 
     * @param array $condition 
     * @return int $count
     * @author liujf 
     * @time 2017-08-29
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
     * @time 2017-08-29
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
     * @desc 获取详情
 	 * 
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf 
     * @time 2017-08-29
     */
    public function getDetail($condition = [], $field = '*') {
    	
    	$where = $this->getWhere($condition);
    	
        return $this->field($field)->where($where)->find();
    }
    
    /**
     * @desc 添加记录
     * 
     * @param array $condition
     * @return mixed
     * @author liujf 
     * @time 2017-08-29 
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
     * @time 2017-08-29
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
     * @time 2017-08-29
	 */
	public function delRecord($condition = []) {

		if (!empty($condition['r_id'])) {
			$where['id'] = ['in', explode(',', $condition['r_id'])];
		} else {
			return false;
		}

		return $this->where($where)->delete();
	}

	/*
	 * @param array $condition
     * @return array
     * @author zhangyuliang
     * @time 2017-11-02
	 */
	public function getOrgUserlist($condition = []){
		if(empty($condition['org_id'])) {
			return ['code'=>'-104','message'=>'部门ID必填'];
		}else{
			$where['a.org_id'] = $condition['org_id'];
		}
		if(empty($condition['role_no'])){
			return ['code'=>'-104','message'=>'角色编码必填'];
		}else{
			$where['c.role_no'] = $condition['role_no'];
		}
		if(!empty($condition['user_no'])){
			$where['d.user_no'] = array('like',$condition['user_no']);
		}
		if(!empty($condition['username'])){
			$where['d.name'] = array('like',$condition['username']);
		}

		$page = !empty($condition['currentPage'])?$condition['currentPage']:1;
		$pagesize = !empty($condition['pageSize'])?$condition['pageSize']:10;

		$where['d.status'] = 'NORMAL';

		try {
			$fields = 'd.id,d.user_no,d.name,c.name as role_name';
			$list = $this->alias('a')
					->join('erui_sys.role_member b ON a.employee_id = b.employee_id','left')
					->join('erui_sys.role c ON b.role_id = c.id','left')
					->join('erui_sys.employee d ON a.employee_id = d.id','left')
					->field($fields)
					->where($where)
					->page($page, $pagesize)
					->order('a.id DESC')
					->select();
			$count = $this->alias('a')
					->join('erui_sys.role_member b ON a.employee_id = b.employee_id','left')
					->join('erui_sys.role c ON b.role_id = c.id','left')
					->join('erui_sys.employee d ON a.employee_id = d.id','left')
					->where($where)
					->count('a.id');

			if($list){
				$results['code'] = '1';
				$results['message'] = '成功！';
				$results['count'] = $count;
				$results['data'] = $list;
			}else{
				$results['code'] = '-101';
				$results['message'] = '没有找到相关信息!';
			}
			return $results;
		} catch (Exception $e) {
			$results['code'] = $e->getCode();
			$results['message'] = $e->getMessage();
			return $results;
		}
	}
}
