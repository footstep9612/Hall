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

    /*
     * 询单(项目)状态
     */
    const INQUIRY_DRAFT = 'DRAFT';//起草
    const INQUIRY_APPROVING_BY_SC = 'APPROVING_BY_SC';//方案中心审核中
    const INQUIRY_APPROVED_BY_SC = 'APPROVED_BY_SC';//方案中心已确认
    const INQUIRY_QUOTING_BY_BIZLINE = 'QUOTING_BY_BIZLINE';//产品线报价中
    const INQUIRY_QUOTED_BY_BIZLINE = 'QUOTED_BY_BIZLINE';//产品负责人已确认
    const INQUIRY_BZ_QUOTE_REJECTED = 'BZ_QUOTE_REJECTED';//项目经理驳回产品报价
    const INQUIRY_QUOTING_BY_LOGI = 'QUOTING_BY_LOGI';//物流报价中
    const INQUIRY_QUOTED_BY_LOGI = 'QUOTED_BY_LOGI';//物流审核人已确认
    const INQUIRY_LOGI_QUOTE_REJECTED = 'LOGI_QUOTE_REJECTED';//项目经理驳回物流报价
    const INQUIRY_APPROVED_BY_PM = 'APPROVED_BY_PM';//项目经理已确认
    const INQUIRY_APPROVING_BY_MARKET = 'APPROVING_BY_MARKET';//市场主管审核中
    const INQUIRY_APPROVED_BY_MARKET = 'APPROVED_BY_MARKET';//市场主管已审核
    const INQUIRY_QUOTE_SENT = 'QUOTE_SENT';//报价单已发出
    const INQUIRY_INQUIRY_CLOSED = 'INQUIRY_CLOSED';//报价关闭

    /*
     * 报价状态
     */
    const QUOTE_NOT_QUOTED = 'NOT_QUOTED';//未报价
    const QUOTE_QUOTED = 'QUOTED';//已报价
    const QUOTE_APPROVED = 'APPROVED';//已审核
    const QUOTE_REJECTED = 'REJECTED';//被驳回


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
     * @param $quote_id 报价单id
     * @return mixed 获取的结果
     */
    public function getQuoteInfo($quote_id)
    {
        return $this->where(['quote_id'=>$quote_id])->find();
    }

    /**
     * 产品线负责人暂存报价信息
     * @param $quote_id 报价单id
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
        return $this->where(['quote_id'=>$quote_id])->save(['status'=>self::STATUS_RETURN]);
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
     * 产品线报价->产品线报价人->提交产品线负责人审核
     * 操作说明:当前报价单状态改为(........)
     * @param $params
     * @return array
     */
    public function submitToBizlineManager($params){

        //TODO 这里可能处理一些逻辑相关的操作

        //更新当前的报价单状态为产品线报价
        try{
            if ($this->where(['quote_id'=>$params['quote_id']])->save(['status'=>self::STATUS_QUOTED])){
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


    /**
     * 划分产品线
     * @param $param
     * @return array
     */
    public function setPartitionBizline($param)
    {
        //先查找询单相关的字段 inquiry_id biz_agent_id
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no'=>$param['serial_no']])
                                    ->field(['id','agent_id'])
                                    ->find();
        //判断一个quote_id是一个或者是多个
        $quote = explode(',',$param['quote_id']);
        $data = [
            'inquiry_id'=>$inquiryInfo['id'],
            'biz_agent_id'=>$inquiryInfo['agent_id'],
            'bizline_id'=>$param['bizline_id'],
            'created_by'=>$param['created_by'],
            'created_at'=>date('Y-m-d H:i:s')
        ];
        foreach ($quote as $k=>$v){
            $data['quote_id'] = $v;
            $this->add($data);
        }
        return ['code'=>'1','message'=>'成功!'];
    }

    /**
     * 产品线负责人指派报价人
     * @param $request 请求参数
     * @return array 返回结果
     */
    public function assignQuoter($request){
        try{
            if ($this->where(['quote_id'=>$request['quote_id']])->save(['biz_agent_id'=>$request['biz_agent_id']])){
                return ['code'=>'1','message'=>'指派成功!'];
            }else{
                return ['code'=>'-104','message'=>'指派失败!'];
            }
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }
}
