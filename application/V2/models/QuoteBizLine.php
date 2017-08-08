<?php

/**
 * 产品线报价
 * Class QuoteBizLineModel
 * @author 买买提
 */
class QuoteBizLineModel extends PublicModel{
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

    public function __construct(){
        parent::__construct();
    }

    /**
     * 根据条件获取所有产品线报价单
     * @param array $param
     *
     * @return array
     */
    public function getQuoteList(array $param){
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
     * @return mixed
     */
    private function getTotal($where)
    {
        return $this->where($where)->count('id');
    }

    /**
     * 划分产品线
     * @param $data
     * @return mixed
     */
    public function partitionBizline($data){

        try{
            if ($this->add($data)){
                return [
                    'code' =>'1',
                    'message'=>'成功!'
                ];
            }
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }

    }

    /**
     * 产品线报价->产品线报价人->提交产品线负责人审核
     * 操作说明:当前报价单状态改为(........)
     * @param $params
     * @return array
     */
    public function submitToBizlineManager($params){

        //TODO 这里可能处理一些逻辑相关的操作

        //更新当前的报价单状态为产品线报价
        try{
            if ($this->where(['quote_id'=>$params['quote_id']])->save(['status'=>'SUBMITED'])){
                return ['code'=>'1','message'=>'提交成功!'];
            }else{
                return ['code'=>'-104','message'=>'提交失败!'];
            }
        }catch (Exception $exception){
            return [
                'code'=> $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }
}
