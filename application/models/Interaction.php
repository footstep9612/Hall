<?php
/*
	点赞转发Model
*/
class InteractionModel extends ZysModel {
	private $g_table = 'user_interaction';

	public function __construct() {
		parent::__construct($this->g_table);
	}
	
	/*
		任务列表
		@param	$field	string	查询字段
		@param	$option	array	列表where条件
		@param	$start	int		起始条数	
		@param	$limit	int		每页多少条	
		@return array
	*/
	public function InterList($field, $option, $start, $limit){
		return $this->field($field)->where($option)->limit($start, $limit)->select();
	}
	/*
		获取条数
	*/
	public function InterCount($option){
		return $this->where($option)->count();
	}
}
