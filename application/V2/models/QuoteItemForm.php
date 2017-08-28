<?php

/**
 * @desc 报价单产品线平行表
 * @file Class QuoteItemFormModel
 * @author 买买提
 */
class QuoteItemFormModel extends PublicModel{
    /**
     * 数据库名称
     * @var string
     */
    protected $dbName = 'erui2_rfq';

    /**
     * 数据表名称
     * @var string
     */
    protected $tableName = 'quote_item_form';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 选择报价
     * @param $where
     *
     * @return mixed
     */
    public function getList($where){

        $field = 'id,created_by,status,supplier_id,contact_first_name,contact_last_name,contact_phone,purchase_unit_price,period_of_validity';
        //按价格由低到高显示
        return $this->where($where)->field($field)->order('purchase_unit_price ASC')->select();
    }

}