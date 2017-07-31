<?php

/**
 * @desc    产品线报价详情模型
 * @author    买买提
 */
class ProductLineQuoteItemModel extends PublicModel
{
    protected $dbName = 'erui_rfq' ; //数据库名称
    protected $tableName = 'inquiry_item' ; //数据表名称

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @desc    获取询单sku列表
     * @param    string    $serial_no   询单号
     * @author    买买提
     * @return    mixed
     */
    public function getSkuList($serial_no)
    {
        return $this->where(['serial_no'=>$serial_no])->select();
    }
}
