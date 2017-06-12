<?php
/**
 * 商品型号 model
 */
class GoodsCatePropertyModel extends ZysModel
{
    private $g_table = 'goods_categories_property';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /*
		参数详情
		@param	$option	array	列表where条件
		@return array
	*/
	public function PropertyList($field, $option){
		return $this->field($field)->where($option)->select();
	}

}
