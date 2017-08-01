<?php

class QuoteItemBizLineModel extends PublicModel
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
    protected $tableName = 'quote_item';

    /**
     * 数据表前缀
     * @var string
     * TODO 这里后期可以设置publicmodel的$tablePrefix属性为空来代替
     */
    protected $tablePrefix = '';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取报价审核信息
     * @param $quote_id 报价单id
     *
     * @return mixed
     */
    public function getVerifyInfo($quote_id)
    {
        $field = [
            'checked_by',//审核人
            'checked_at',//审核时间
        ];

        return $this->where(['quote_id'=>$quote_id])->field($field)->select();
    }
}
