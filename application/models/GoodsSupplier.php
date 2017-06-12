<?php
/**
 * 供应商model
 */
class GoodsSupplierModel extends ZysModel {
    private $g_table = 'goods_supplier';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /**
     * 创建供应商
     */
    public function SupCreate($data){
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
		@param	$start	int		起始条数	
		@param	$limit	int		每页多少条	
		@return array
	*/
	public function SupList($field, $option, $start, $limit){
		return $this->field($field)->where($option)->limit($start, $limit)->select();
	}
	
	/*
		获取供应商
	*/
	public function SupCount($option){
		return $this->where($option)->count();
	}

    /*
		供应商详情
		@param	$option	array	列表where条件
		@return array
	*/
	public function SupSelOne($option){
		return $this->where($option)->find();
	}

    /*
		供应商逻辑删除
		@param	$option	array	where条件
		@param	$data	array	更新的数据
		@return	bool
	*/
	public function SupUpate($option, $data){
		$status = $this->where($option)->save($data);
		if($status){
			return true;
		}else{
			return false;
		}
	}

}
