<?php
/**
 * @desc 报价单模型
 * @author liujf 2017-06-17
 */
class QuoteModel extends PublicModel {

    protected $dbName = 'erui_db_ddl_rfq';
    protected $tableName = 'quote';
    
    public function __construct() {
        parent::__construct();
    }
    
	/**
     * @desc 获取报价单总体信息
 	 * @author liujf 2017-06-17
     * @param $inquiry_no string
     * @return array
     */
    public function getInfo($inquiry_no = '') {
    	$field = 'total_exw_price,total_exw_cur,total_logi_fee,total_logi_cur,total_bank_fee,
    			  total_insurance_fee,total_quote_price,total_quote_cur,payment_mode,trade_terms,
    			  trans_mode_brief_name,origin_place,destination,exw_delivery_period,est_transport_cycle,logi_notes';
    	
    	$where = array('inquiry_no' => $inquiry_no);
        return $this->where($where)->field($field)->find();
    }   

}
