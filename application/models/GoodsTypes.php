<?php
/**
 * 商品型号 model
 */
class GoodsTypesModel extends ZysModel
{
    private $g_table = 'goods_types';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /*
		型号详情
		@param	$option	array	列表where条件
		@return array
	*/
	public function TypesList($field, $option){
		return $this->field($field)->where($option)->select();
	}

}
