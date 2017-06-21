<?php
/**
 * @desc 报价单明细模型
 * @author liujf 2017-06-17
 */
class QuoteItemModel extends PublicModel {

    protected $dbName = 'erui_db_ddl_rfq';
    protected $tableName = 'quote_item';
    
    public function __construct() {
        parent::__construct();
    }

	/**
     * @desc 获取报价单明细
 	 * @author liujf 2017-06-17
     * @param $quote_no string
     * @return array
     */
    public function getDetail($quote_no = '') {
    	$field = 'total_quote_price,period_of_validity,quote_model,quote_spec,quote_quantity,quote_unit,quote_brand,exw_unit_price,
    			  quote_exw_unit_price,quote_unit_price,trade_unit_price,delivery_period,period_of_validity,
    			  rebate_rate,unit_weight,weight_unit,package_size,size_unit,package_mode,quote_notes,goods_notes';
    	
    	$where = array('quote_no' => $quote_no);
        return $this->where($where)->field($field)->find();
    }

}
