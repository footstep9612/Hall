<?php

/**
 * 临时商品库模型
 * Class TemporaryGoodsModel
 * @author 买买提
 */
class TemporaryGoodsModel extends PublicModel
{
    /**
     * @var string
     */
    protected $dbName = 'erui_rfq';

    /**
     * @var string
     */
    protected $tableName = 'temporary_goods';

    /**
     * 默认查询条件
     * @var array
     */
    protected $defaultCondition = ['deleted_flag' => 'N'];

    public function sync()
    {
        //从询报价sku同步到临时商品库
    }

    public function getList(array $condition = [])
    {

    }

    /**
     * 设置分页
     * @param array $condition
     * @return array
     */
    protected function setPage(array $condition = [])
    {
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        return [$currentPage, $pageSize];
    }
}