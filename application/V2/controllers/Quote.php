<?php

/**
 * 报价人相关操作接口
 * @desc   QuoteController
 * @Author 买买提
 */
class QuoteController extends PublicController{

    private $quoteModel;

    private $requestParams = [];

    public function init(){

        //parent::init();

        $this->quoteModel = new QuoteModel();

        $this->requestParams = json_decode(file_get_contents("php://input"), true);

    }


    /**
     * 报价信息
     */
    public function infoAction(){

        $request = $this->validateRequests('inquiry_id');
        $condition = ['inquiry_id'=>$request['inquiry_id']];
        $field = 'package_mode,total_weight,package_volumn,period_of_validity,payment_mode,trade_terms_bn,delivery_period,payment_period,fund_occupation_rate,bank_interest,gross_profit_rate,premium_rate,trans_mode_bn,dispatch_place,delivery_addr,total_bank_fee,exchange_rate,total_purchase,purchase_cur_bn,from_port,to_port,from_country,to_country,logi_quote_flag';

        $info = $this->quoteModel->getGeneralInfo($condition,$field);
        $this->jsonReturn(
            [
                'code' => '1',
                'message' => '成功!',
                'data' => $info
            ]
        );

    }

    /**
     * 更新报价信息
     */
    public function updateInfoAction(){

        $request = $this->validateRequests('inquiry_id');
        $condition = ['inquiry_id'=>$request['inquiry_id']];

        $result = $this->quoteModel->updateGeneralInfo($condition,$request);

        if (!$result) $this->jsonReturn($result);

        $this->jsonReturn(
            [
                'code' => '1',
                'message' => '成功!'
            ]
        );
    }

    public function rejectToBiz(){

    }

    public function sendToLogi(){

    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params=''){

        $request = $this->requestParams;
        unset($request['token']);

        //判断筛选字段为空的情况
        if ($params){
            $params = explode(',',$params);
            foreach ($params as $param){
                if (empty($request[$param])) $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
            }
        }

        return $request;

    }

}

