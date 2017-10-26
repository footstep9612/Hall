<?php

/**
 * 报价人相关操作接口
 * @desc   QuoteController
 * @Author 买买提
 */
class QuoteController extends PublicController{

    private $quoteModel;
    private $quoteItemModel;
    private $inquiryModel;

    private $requestParams = [];

    public function init(){

        parent::init();

        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->inquiryModel = new InquiryModel();

        $this->requestParams = json_decode(file_get_contents("php://input"), true);

    }


    /**
     * 报价信息
     */
    public function infoAction(){

        $request = $this->validateRequests('inquiry_id');
        $condition = ['inquiry_id'=>$request['inquiry_id']];
        $field = 'package_mode,total_weight,package_volumn,period_of_validity,payment_mode,trade_terms_bn,delivery_period,payment_period,fund_occupation_rate,bank_interest,gross_profit_rate,premium_rate,quote_remarks,trans_mode_bn,dispatch_place,delivery_addr,total_bank_fee,exchange_rate,total_purchase,purchase_cur_bn,from_port,to_port,from_country,to_country,logi_quote_flag,total_logi_fee,total_exw_price,total_quote_price';

        $info = $this->quoteModel->getGeneralInfo($condition,$field);

        $exchangeRateModel = new ExchangeRateModel();
        $info['exchange_rate'] = $exchangeRateModel->where(['cur_bn2'=>'CNY','cur_bn1'=>'USD'])->order('created_at DESC')->getField('rate');

        $info['trans_mode_bn'] = $this->inquiryModel->where(['id'=>$request['inquiry_id']])->getField('trans_mode_bn');

        $transMode = new TransModeModel();
        $info['trans_mode_bn'] = $transMode->where(['id' => $info['trans_mode_bn']])->getField('trans_mode');

        $logiInfo = $this->inquiryModel->where(['id'=>$request['inquiry_id']])->field('dispatch_place,destination')->find();

        $info['inquiry_dispatch_place'] = $logiInfo['dispatch_place'];
        $info['inquiry_delivery_addr'] = $logiInfo['destination'];

        $finalQuoteModel = new FinalQuoteModel();
        $finalQuote = $finalQuoteModel->where($condition)->field('total_exw_price,total_quote_price')->find();
        if ($finalQuote){
            $info['final_total_exw_price'] = $finalQuote['total_exw_price'];
            $info['final_total_quote_price'] = $finalQuote['total_quote_price'];
        }
        $this->jsonReturn($info);

    }


    /**
     * 更新报价信息
     */
    public function updateInfoAction(){

        $request = $this->validateRequests('inquiry_id');

        $request = $this->validateNumeric($request);
        $request['biz_quote_by'] = $this->user['id'];
        $request['biz_quote_at'] = date('Y-m-d H:i:s');

        $condition = ['inquiry_id'=>$request['inquiry_id']];
        //这个操作设计到计算
        $result = $this->quoteModel->updateGeneralInfo($condition,$request);

        if (!$result) $this->jsonReturn($result);
        $this->jsonReturn();

    }

    /**
     *退回分单员(事业部分单员)
     */
    public function rejectToBizAction(){

        $request = $this->validateRequests('inquiry_id');
        $condition = ['inquiry_id'=>$request['inquiry_id']];
        $response =  $result = $this->quoteModel->rejectToBiz($condition);
        $this->jsonReturn($response);

    }

    /**
     * 提交物流分单员
     */
    public function sendLogisticsAction(){

        $request = $this->validateRequests('inquiry_id');
        $response = $this->quoteModel->sendLogisticsHandler($request, $this->user);
        $this->jsonReturn($response);

    }

    /**
     * 退回物流报价
     */
    public function rejectLogisticAction(){

        $request = $this->validateRequests('inquiry_id');
        $condition = ['id'=>$request['inquiry_id']];

        $inquiryModel = new InquiryModel();
        $result = $inquiryModel->where($condition)->save([
            'status' => 'LOGI_QUOTING', //物流报价
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->jsonReturn($result);
    }

    /**
     * 提交报价审核
     */
    public function submitQuoteAuditorAction(){

        $request = $this->validateRequests('inquiry_id');

        $this->changeInquiryStatus($request['inquiry_id'],'MARKET_APPROVING');

        $inquiryModel = new InquiryModel();
        $check_org_id = $inquiryModel->getRoleUserId($this->user['group_id'],$inquiryModel::quoteCheckRole);

        $inquiryModel->where(['id'=>$request['inquiry_id']])->save([
            'quote_status' => 'QUOTED',
            'check_org_id' => $check_org_id //事业部审核人
        ]);

        $this->quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->save(['status' => 'BIZ_APPROVING']);

        $finalQuoteModel = new FinalQuoteModel();
        $finalQuoteModel->add($finalQuoteModel->create([
            'inquiry_id' => $request['inquiry_id'],
            'buyer_id' => $this->inquiryModel->where(['id'=>$request['inquiry_id']])->getField('buyer_id'),
            'quote_id' => $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']),
            'created_by' => $this->user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ]));

        $quoteItems = $this->quoteItemModel->where(['inquiry_id'=>$request['inquiry_id']])->field('id,inquiry_id,inquiry_item_id,sku,supplier_id')->select();

        $finalQuoteItemModel = new FinalQuoteItemModel();
        foreach ($quoteItems as $quote=>$item){
            $finalQuoteItemModel->add($finalQuoteItemModel->create([
                'quote_id' => $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']),
                'inquiry_id' => $request['inquiry_id'],
                'inquiry_item_id' => $item['inquiry_item_id'],
                'quote_item_id' => $item['id'],
                'sku' => $item['sku'],
                'supplier_id' => $item['supplier_id'],
                'created_by' => $this->user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]));
        }

        $this->jsonReturn();

    }

    /**
     * 退回报价(审核人)
     */
    public function rejectAction(){

        $request = $this->validateRequests('inquiry_id');
        $response = $this->changeInquiryStatus($request['inquiry_id'],'BIZ_QUOTING');
        $this->jsonReturn($response);

    }

    /**
     * 确认报价(审核人)
     */
    public function confirmAction(){

        $request = $this->validateRequests('inquiry_id');
        $response = $this->changeInquiryStatus($request['inquiry_id'],'MARKET_CONFIRMING');
        $this->jsonReturn($response);

    }

    /**
     * SKU列表
     */
    public function skuAction(){

        $request = $this->validateRequests('inquiry_id');

        $list = $this->quoteItemModel->getList($request);
        if (!$list) $this->jsonReturn(['code'=>'-104','message'=>'没有数据']);

        $supplier = new SupplierModel();

        foreach ($list as $key=>$value){
            $list[$key]['purchase_unit_price'] = sprintf("%.4f", $list[$key]['purchase_unit_price']);
            $list[$key]['supplier_name'] = $supplier->where(['id' => $value['supplier_id']])->getField('name');
        }

        $this->jsonReturn($list);

    }

    /**
     * 保存SKU信息
     */
    public function updateSkuAction(){

        $request = $this->validateRequests();
        $response = $this->quoteItemModel->updateItem($request['data'],$this->user['id']);
        $this->jsonReturn($response);
    }

    public function updateSupplierAction(){

        $request = $this->validateRequests();
        $this->quoteItemModel->updateSupplier($request['data']);
        $this->jsonReturn();
    }

    /**
     * 报价审核人sku列表
     */
    public function finalSkuAction(){

        $request = $this->validateRequests('inquiry_id');

        $finalQuoteItemModel = new FinalQuoteItemModel();
        $list = $finalQuoteItemModel->getFinalSku($request);
        if (!$list) $this->jsonReturn(['code'=>'-104','message'=>'没有数据']);

        foreach ($list as $key=>$value){
            $list[$key]['quote_exw_unit_price'] = sprintf("%.4f", $list[$key]['quote_exw_unit_price']);
            $list[$key]['quote_quote_unit_price'] = sprintf("%.4f", $list[$key]['quote_quote_unit_price']);
            $list[$key]['exw_unit_price'] = sprintf("%.4f", $list[$key]['exw_unit_price']);
            $list[$key]['quote_unit_price'] = sprintf("%.4f", $list[$key]['quote_unit_price']);
        }

        $this->jsonReturn($list);

    }

    public function updateFinalSkuAction(){

        $request = $this->validateRequests();

        $finalQuoteItemModel = new FinalQuoteItemModel();
        $finalQuoteItemModel->updateFinalSku($request['data']);
        $this->jsonReturn();
    }

    /**
     * 删除SKU
     */
    public function delItemAction(){

        $request = $this->validateRequests('id');
        $quoteItemIds = $request['id'];

        //只能删除自己的SKU
        /*
        $quoteItems = $this->quoteItemModel->where('id IN('.$quoteItemIds.')')->getField('created_by',true);
        foreach ($quoteItems as $item){
            if ($item !== $this->user['id']){
                $this->jsonReturn(['code'=>'-104','message'=>'你没有权限删除该SKU!']);
            }
        }
        */

        $response = $this->quoteItemModel->delItem($quoteItemIds);
        $this->jsonReturn($response);

    }

    /**
     * 同步询单和报价SKU
     */
    public function syncSkuAction(){

        $request = $this->validateRequests('inquiry_id');
        $response = $this->quoteItemModel->syncSku($request,$this->user['id']);
        $this->jsonReturn($response);

    }

    private function changeInquiryStatus($id,$status){

        return $this->inquiryModel->where(['id'=>$id])->save([
            'status'=>$status,
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
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

