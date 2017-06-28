<?php
/**
 * @desc 商品历史报价模型
 * @author liujf 2017-06-27
 */
class GoodsPriceHisModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'goods_price_his';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * @author liujf 2017-06-27
     * @param array $condition
     * @return array
     */
     public function getWhere($condition) {
     	$where = array();
     	
     	if(isset($condition['sku'])) {
    		$where['sku'] = $condition['sku'];
    	}
    	
    	return $where;
     }
    
    
    /**
     * @desc 获取商品历史报价列表
 	 * @author liujf 2017-06-27
     * @param array $condition
     * @return array
     */
    public function getList($condition) {
    	
    	$where = $this->getWhere($condition);
    	
        return $this->where($where)->select();
    }   
    
	/**
     * @desc 获取商品历史报价详情
 	 * @author liujf 2017-06-27
     * @param array $condition
     * @return array
     */
    public function getDetail($condition) {
    	
    	$where = $this->getWhere($condition);
    	
        return $this->where($where)->find();
    }   

}
