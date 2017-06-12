<?php
/**
 * 话题标签关联表
 */
class RsLabelsTopicsModel extends ZysModel {
    private $g_table = 'rs_labels_topics';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /**
     * 创建对应关系
     */
    public function dataCreate($data){
		$id = $this->add($data);
		if($id){
			return $id;
		}else{
			return false;
		}
    }
    /*
		查找
		@param	$option	array	列表where条件
		@return array
	*/
	public function dataFindOne($option){
		return $this->where($option)->find();
	}

    /*
		更新的数据
		@param	$option	array	where条件
		@param	$data	array	更新的数据
		@return	bool
	*/
	public function dataUpdate($option, $data){
		$status = $this->where($option)->save($data);
		if($status){
			return true;
		}else{
			return false;
		}
	}

}
