<?php
/*
	回复Model
*/
class CommentsModel extends ZysModel {
	private $g_table = 'comments_on_post';

	public function __construct() {
		parent::__construct($this->g_table);
	}
	
	/*
		列表
		@param	$field	string	查询字段
		@param	$option	array	列表where条件
		@return array
	*/
	public function ComList($field, $option){

		return $this->field($field)->where($option)->select();

	}
	/*
		更新
	*/
	public function ComUp($option, $data){
		$status = $this->where($option)->save($data);
		if($status){
			return true;
		}else{
			return false;
		}
    }
}
