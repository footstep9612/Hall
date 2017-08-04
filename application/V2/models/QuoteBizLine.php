<?php

/**
 * 产品线报价
 * Class QuoteBizLineModel
 * @author 买买提
 */
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
     * 根据条件获取所有产品线报价单
     * @param array $param
     *
     * @return array
     */
    public function getQuoteList(array $param)
    {
        $where = $this->filterParam($param);

        //$fields = [];

        //$total = $this->getTotal($where);

        return $this->where($where)->select();
    }

    /**
     * 根据条件获取报价信息
     * @param array $param
     *
     * @return mixed
     */
    public function getQuoteInfo($quote_id)
    {
        return $this->where(['quote_id'=>$quote_id])->find();
    }

    /**
     * 产品线负责人暂存报价信息
     * @param $quote_id 报价单id
     *
     * @return bool
     */
    public function storageQuote($quote_id)
    {
        /*
        |--------------------------------------------------------------------------
        | TODO 这里状态对应值整理好了以后再具体实现逻辑
        |--------------------------------------------------------------------------
        */
        return $this->where(['quote_id'=>$quote_id])->save(['status'=>'SUBMIT']);
    }

    /**
     * 产品线负责人退回产品线报价人重新报价
     * @param $quote_id 报价id
     *
     * @return bool
     */
    public function sendback($quote_id)
    {
        return $this->where(['quote_id'=>$quote_id])->save(['status'=>'SUBMIT']);
    }

    /**
     * 产品线报价人暂存报价
     * @param $quote_id 报价id
     *
     * @return bool
     */
    public function quoterStorage($quote_id)
    {
        //TODO 这里添加保存数据的逻辑才行
        return $this->where(['quote_id'=>$quote_id])->save(['status'=>'SUBMIT']);
    }

    /**
     * 过滤条件
     * @param array $param
     *
     * @return array
     */
    private function filterParam(array $param)
    {
        $data = [];
        if (isset($param['quote_id'])){
            //报价单id
            $data['quote_id'] = $param['quote_id'];
        }

        return $data;
    }

    /**
     * 根据条件获取总数
     * @param $where
     *
     * @return mixed
     */
    private function getTotal($where)
    {
        return $this->where($where)->count('id');
    }
}
