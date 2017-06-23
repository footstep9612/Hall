<?php

/**
 * 询价单明细表模型
 * Class Qoute_itemModel
 * @author maimaiti
 */
class QouteItemModel extends PublicModel
{
    protected $dbName = 'erui_db_ddl_rfq';
    protected $tableName = 'quote_item';

    /**
     * 获取sku询价单列表
     * @param $fields   array 筛选字段
     * @return array
     */
    public function get_quote_item_list($fields)
    {
        return $this->field($fields)->select();
    }
}
