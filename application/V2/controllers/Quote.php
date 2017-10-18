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
        $field = 'package_mode,total_weight,package_volumn,period_of_validity,payment_mode,trade_terms_bn,delivery_period,payment_period,fund_occupation_rate,bank_interest,gross_profit_rate,premium_rate,quote_remarks,trans_mode_bn,dispatch_place,delivery_addr,total_bank_fee,exchange_rate,total_purchase,purchase_cur_bn,from_port,to_port,from_country,to_country,logi_quote_flag';

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

        $request = $this->validateNumeric($request);

        $condition = ['inquiry_id'=>$request['inquiry_id']];
        //这个操作设计到计算
        $result = $this->quoteModel->updateGeneralInfo($condition,$request);

        if (!$result) $this->jsonReturn($result);
        $this->jsonReturn(['code' => '1', 'message' => '成功!']);

    }

    /**
     *退回分单员(事业部分单员)
     */
    public function rejectToBiz(){

        $request = $this->validateRequests('inquiry_id');
        $condition = ['inquiry_id'=>$request['inquiry_id']];
        $response =  $result = $this->quoteModel->rejectToBiz($condition);
        $this->jsonReturn($response);
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

    /**
     * 验证必填和数字属性的字段
     * @param $request
     * @return mixed
     */
    private function validateNumeric($request){

        //总重
        if (!empty($request['total_weight']) && !is_numeric($request['total_weight'])) {
            $this->jsonReturn(['code' => '-104', 'message' => '总重必须是数字']);
        }
        //包装总体积
        if (!empty($request['package_volumn']) && !is_numeric($request['package_volumn'])) {
            $this->jsonReturn(['code' => '-104', 'message' => '包装总体积必须是数字']);
        }
        //回款周期
        if (!empty($request['payment_period']) && !is_numeric($request['payment_period'])) {
            $this->jsonReturn(['code' => '-104', 'message' => '回款周期必须是数字']);
        }
        //交货周期
        if (!empty($request['delivery_period']) && !is_numeric($request['delivery_period'])) {
            $this->jsonReturn(['code' => '-104', 'message' => '交货周期必须是数字']);
        }
        //资金占用比例
        if (!empty($request['fund_occupation_rate']) && !is_numeric($request['fund_occupation_rate'])) {
            $this->jsonReturn(['code' => '-104', 'message' => '资金占用比例必须是数字']);
        }
        //银行利息
        if (!empty($request['bank_interest']) && !is_numeric($request['bank_interest'])) {
            $this->jsonReturn(['code' => '-104', 'message' => '银行利息必须是数字']);
        }
        //毛利率
        if (!empty($request['gross_profit_rate']) && !is_numeric($request['gross_profit_rate'])) {
            $this->jsonReturn(['code' => '-104', 'message' => '毛利率必须是数字']);
        }
        return $request;
    }

}

