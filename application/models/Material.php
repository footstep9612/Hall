<?php
/**
 * **************************************
 * class MaterialModel
 * 商品model类
 * **************************************
 * Created by PhpStorm.
 * User: wen
 * Date: 2017/5/20
 * Time: 21:30
 */

class MaterialModel extends ZysModel
{
    private $g_table = 'goods';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /**
     * 商品总数
     * @param $where array  查询字段
     * @return int
     * @author Wen
     */

    public function SelCount( $where = null )
    {
        return $this->where($where)->count();
    }

    /**
     * 商品素材列表
     * @param $field    string  查询字段
     * @param $where    array   条件
     * @param $start    int     起始位置
     * @param $limit    int     每页条数
     * @return array
     * @author Wen
     */

    public function SelList( $field = null, $where, $start = null, $limit = null )
    {
        return $this->field($field)->where($where)->order('updated_at DESC')->limit($start, $limit)->select();
    }


	//获取商品参数类型
	public function businessTypeVal($businessType, $lang = 'zh'){
		switch ($businessType)
		{
			case 11:
			  $businessTypeval = $lang == 'zh' ? "租赁" : "Seeking rent";
			  break;  
			case 12:
			  $businessTypeval = $lang == 'zh' ? "购买" : "Buying";
			  break;
			case 13:
			  $businessTypeval = $lang == 'zh' ? "购买/租赁" : "Buying / Seeking rent";
			  break;
			case 21:
			  $businessTypeval = $lang == 'zh' ? "出租" : "Rent";
			  break;
			case 22:
			  $businessTypeval = $lang == 'zh' ? "出售" : "Sell";
			  break;
			case 23:
			  $businessTypeval = $lang == 'zh' ? "出售/出租" : "Sell / Rent";
			  break;
			default:
			  $businessTypeval = "";
		}
		return $businessTypeval;
	}

    /**
     * 修改一条数据
     * @param $where    array
     * @param $data     array
     * @return bool
     * @author Wen
     */

    public function UpdOne( $where, $data )
    {
        return $this->where($where)->save($data);
    }

    /**
     * 获取一条数据
     * @param $where
     * @return mixed
     */

    public function SelOne( $where )
    {
        return $this->where( $where )->find();
    }

}