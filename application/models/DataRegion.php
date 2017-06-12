<?php
/**
 * 供应商model
 */
class DataRegionModel extends ZysModel {
    private $g_table = 'data_region';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /*
		列表
		@param	$field	string	查询字段
		@param	$option	array	列表where条件
		@return array
	*/
	public function RegionList($field, $option){
		return $this->field($field)->where($option)->select();
	}

}
