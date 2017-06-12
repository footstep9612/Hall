<?php
/**
 * 商品品牌 model
 */
class GoodsBrandModel extends ZysModel
{
    private $g_table = 'goods_brand';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /*
		品牌详情
		@param	$option	array	列表where条件
		@return array
	*/
	public function BrandList($field, $option){
		return $this->field($field)->where($option)->select();
	}

}
