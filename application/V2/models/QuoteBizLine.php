<?php

class QuoteBizLineModel extends PublicModel
{

    /**
     * 数据库名称
     * @var string
     */
    protected $dbName = 'erui2_rfq';

    /**
     * 数据表名称
     * @var string
     */
    protected $tableName = 'quote_bizline';

    /**
     * 根据条件获取所有产品线报价单
     * @param array $param
     *
     * @return array
     */
    public function getQuoteList(array $param)
    {
        return $data = [
            'code' => 1,
            'message' => 'success',
            'data' => $param
        ];
    }
}
