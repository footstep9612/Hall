<?php
/**
 * @desc 报价单明细模型
 * @author 买买提
 */
class QuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 删除报价单项(一个或多个)
     * @param $where 条件
     * @return bool True|False
     */
    public function delItem($where){
        return $this->where('id IN('.$where.')')->save(['deleted_flag'=>'Y']);
    }

}
