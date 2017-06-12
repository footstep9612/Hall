<?php
/*
	评论回贴Model
*/
class PostModel extends ZysModel {
	private $g_table = 'post';

	public function __construct() {
		parent::__construct($this->g_table);
	}

	/*
		获取评论数
	*/
	public function PostCount($option){
		return $this->where($option)->count();
	}
	
	/*
		列表
		@param	$field	string	查询字段
		@param	$option	array	列表where条件
		@param	$start	int		起始条数	
		@param	$limit	int		每页多少条	
		@return array
	*/
	public function PostList($field, $option, $start, $limit){

		return $this->field($field)->where($option)->limit($start, $limit)->select();

	}
	/*
		更新
	*/
	public function PostUp($option, $data){
		$status = $this->where($option)->save($data);
		if($status){
			return true;
		}else{
			return false;
		}
    }
}
