<?php
/**
 * 供应商model
 */
class OrganizationModel extends ZysModel {
    private $g_table = 'organization';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /**
     * 创建供应商
     */
    public function OrgCreate($data){
		$id = $this->add($data);
		if($id){
			return $id;
		}else{
			return false;
		}
    }

    /*
		供应商列表
		@param	$field	string	查询字段
		@param	$option	array	列表where条件
		@return array
	*/
	public function OrgList($field, $option){
		return $this->field($field)->where($option)->select();
	}
	
	/*
		获取供应商
	*/
	public function OrgCount($option){
		return $this->where($option)->count();
	}

    /*
		供应商详情
		@param	$option	array	列表where条件
		@return array
	*/
	public function OrgSelOne($field, $option){
		return $this->field($field)->where($option)->find();
	}

    /*
		供应商逻辑删除
		@param	$option	array	where条件
		@param	$data	array	更新的数据
		@return	bool
	*/
	public function OrgUpate($option, $data){
		$status = $this->where($option)->save($data);
		if($status){
			return true;
		}else{
			return false;
		}
	}

}
