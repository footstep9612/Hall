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
}