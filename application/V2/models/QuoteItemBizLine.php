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

    /*
    |--------------------------------------------------------------------------
    | 产品线报价->产品线负责人退回产品线报价人重新报价   角色:产品线负责人
    |--------------------------------------------------------------------------
    |
    | 操作说明
    | 查找所有该报价单说书的sku状态改为被驳回状态
    |
    */
    public function sendback($quote_id)
    {
        $data = $this->where(['quote_id'=>$quote_id])->field(['id','quote_id','status'])->select();

        foreach ($data as $k=>$v){
            $this->where(['quote_id'=>$v['quote_id']])->save(['status'=>QuoteBizLineModel::STATUS_RETURN]);
        }

        return true;
    }
}
