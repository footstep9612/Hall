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
    private $inquiryItemModel;

    private $requestParams = [];

    public function init(){

        parent::init();

        $this->quoteModel     = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->inquiryModel   = new InquiryModel();
        $this->inquiryItemModel = new InquiryItemModel();

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
        $info['exchange_rate'] = $info['exchange_rate'] ? : '暂无';

        $info['trans_mode_bn'] = $this->inquiryModel->where(['id'=>$request['inquiry_id']])->getField('trans_mode_bn');

        $transMode = new TransModeModel();
        $info['trans_mode_bn'] = $transMode->where(['id' => $info['trans_mode_bn']])->getField('trans_mode');
        $info['trans_mode_bn'] = $info['trans_mode_bn'] ? : '暂无';

        $logiInfo = $this->inquiryModel->where(['id'=>$request['inquiry_id']])->field('dispatch_place,destination,inflow_time,org_id,status')->find();

        $info['inquiry_dispatch_place'] = $logiInfo['dispatch_place'];
        $info['inquiry_dispatch_place'] = $info['inquiry_dispatch_place'] ? : '暂无';
        $info['inquiry_delivery_addr']  = $logiInfo['destination'];
        $info['inquiry_delivery_addr'] = $info['inquiry_delivery_addr'] ? : '暂无';
        $info['total_bank_fee'] = $info['total_bank_fee'] ? : '暂无';
        $info['total_exw_price'] = $info['total_exw_price'] ? : '暂无';
        $info['inflow_time'] = $logiInfo['inflow_time'];
        $info['org_id']  = $logiInfo['org_id'];
        $info['status']  = $logiInfo['status'];

        $finalQuoteModel = new FinalQuoteModel();
        $finalQuote = $finalQuoteModel->where($condition)->field('total_exw_price,total_quote_price')->find();
        if ($finalQuote){
            $info['final_total_exw_price']   = $finalQuote['total_exw_price'];
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

        if($request['trans_mode_bn'] == '暂无'){
            unset($request['trans_mode_bn']);
        }
        if($request['total_bank_fee'] == '暂无'){
            unset($request['total_bank_fee']);
        }
        if($request['total_exw_price'] == '暂无'){
            unset($request['total_exw_price']);
        }
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

        $inquiryModel = new InquiryModel();

        $request   = $this->validateRequests('inquiry_id');
        $condition = ['inquiry_id'=>$request['inquiry_id']];
        $country = $inquiryModel->getInquiryCountry($request['inquiry_id']);
        $org_id = $inquiryModel->where(['id'=>$condition['inquiry_id']])->getField('org_id',true);
        $condition['now_agent_id'] = $inquiryModel->getCountryIssueUserId($country, $org_id, ['in', [$inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::quoteIssueAuxiliaryRole]], ['in', [$inquiryModel::inquiryIssueRole, $inquiryModel::quoteIssueMainRole]], ['in', ['ub', 'erui']]);
        $response  = $result = $this->quoteModel->rejectToBiz($condition, $this->user);

        //发送短信通知
        $employee = new EmployeeModel();
        $this->sendSms($employee->getMobileByUserId($condition['now_agent_id']),"REJECT",$employee->getUserNameById($condition['now_agent_id']),$inquiryModel->getSerialNoById($request['inquiry_id']),$this->user['name'],"BIZ_QUOTING","BIZ_DISPATCHING");

        $this->jsonReturn($response);

    }

    /**
     * 提交物流分单员
     */
    public function sendLogisticsAction(){

        $request  = $this->validateRequests('inquiry_id');

        $response = $this->quoteModel->sendLogisticsHandler($request, $this->user);

        //发送短信通知
        $inquiryModel = new InquiryModel();
        $info = $inquiryModel->where(['id'=>$request['inquiry_id']])->field('now_agent_id,serial_no')->find();

        $employee = new EmployeeModel();
        $this->sendSms($employee->getMobileByUserId($info['now_agent_id']),"SUBMIT",$employee->getUserNameById($info['now_agent_id']),$info['serial_no'],$this->user['name'],"BIZ_QUOTING","LOGI_DISPATCHING");

        $this->jsonReturn($response);

    }

    /**
     * 退回物流报价
     */
    public function rejectLogisticAction(){

        $request = $this->validateRequests('inquiry_id');
        $inquiryModel = new InquiryModel();
        $now_agent_id = $inquiryModel->where(['id'=>$request['inquiry_id']])->getField('logi_agent_id');
        $result = $inquiryModel->updateData([
            'id'=>$request['inquiry_id'],
            'now_agent_id'=>$now_agent_id,
            'inflow_time'   => date('Y-m-d H:i:s',time()),
            'status' =>'LOGI_QUOTING',
            'updated_by' => $this->user['id'],
            'updated_at'   => date('Y-m-d H:i:s',time())
        ]);

        //发送短信通知
        $employee = new EmployeeModel();
        $this->sendSms($employee->getMobileByUserId($now_agent_id),"REJECT",$employee->getUserNameById($now_agent_id),$inquiryModel->getSerialNoById($request['inquiry_id']),$this->user['name'],"BIZ_APPROVING","LOGI_QUOTING");

        $this->jsonReturn($result);
    }

    /**
     * 提交报价审核
     */
    public function submitQuoteAuditorAction(){
        $condition = $this->put_data;
        $request = $this->validateRequests('inquiry_id');

        $inquiryModel = new InquiryModel();
        $check_org_id = $condition['check_org_id'];//$inquiryModel->getRoleUserId($this->user['group_id'],$inquiryModel::quoteCheckRole);

        $inquiryModel->updateData([
            'id'=>$request['inquiry_id'],
            'quote_status' => 'QUOTED',
            'now_agent_id' => $check_org_id,
            'inflow_time'   => date('Y-m-d H:i:s',time()),
            'check_org_id' => $check_org_id, //事业部审核人
            'status' => 'MARKET_APPROVING',
            'updated_by' => $this->user['id'],
            'updated_at'   =>date('Y-m-d H:i:s',time())
        ]);

        $this->quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->save(['status' => 'BIZ_APPROVING']);

        $finalQuoteModel = new FinalQuoteModel();
        $quoteModel = new QuoteModel();
        $finalQuoteItemModel = new FinalQuoteItemModel();
        //验证数据
        $quoteInfo  = $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->field('id,payment_period,fund_occupation_rate,delivery_period,total_purchase,total_logi_fee,total_bank_fee,total_exw_price,total_quote_price,total_insu_fee')->find();

        //判断是否存在数据，如果是退回报价更新数据，如果不是就插入一条数据
        $final = $finalQuoteModel->field('id')->where('inquiry_id='.$request['inquiry_id'])->find();

        if(empty($final)){
            $finalQuoteModel->add($finalQuoteModel->create([
                'inquiry_id'           => $request['inquiry_id'],
                'buyer_id'             => $this->inquiryModel->where(['id'=>$request['inquiry_id']])->getField('buyer_id'),
                'quote_id'             => $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']),
                'payment_period'       => $quoteInfo['payment_period'],
                'fund_occupation_rate' => $quoteInfo['fund_occupation_rate'],
                'delivery_period'      => $quoteInfo['delivery_period'],
                'total_purchase'       => $quoteInfo['total_purchase'],
                'total_logi_fee'       => $quoteInfo['total_logi_fee'],
                'total_bank_fee'       => $quoteInfo['total_bank_fee'],
                'total_exw_price'      => $quoteInfo['total_exw_price'],
                'total_quote_price'    => $quoteInfo['total_quote_price'],
                'total_insu_fee'       => $quoteInfo['total_insu_fee'],
                'created_by'           => $this->user['id'],
                'created_at'           => date('Y-m-d H:i:s')
            ]));
        }else{
            $finalQuoteModel->where('inquiry_id='.$request['inquiry_id'])->save($finalQuoteModel->create([
                'inquiry_id'           => $request['inquiry_id'],
                'buyer_id'             => $this->inquiryModel->where(['id'=>$request['inquiry_id']])->getField('buyer_id'),
                'quote_id'             => $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']),
                'payment_period'       => $quoteInfo['payment_period'],
                'fund_occupation_rate' => $quoteInfo['fund_occupation_rate'],
                'delivery_period'      => $quoteInfo['delivery_period'],
                'total_purchase'       => $quoteInfo['total_purchase'],
                'total_logi_fee'       => $quoteInfo['total_logi_fee'],
                'total_bank_fee'       => $quoteInfo['total_bank_fee'],
                'total_exw_price'      => $quoteInfo['total_exw_price'],
                'total_quote_price'    => $quoteInfo['total_quote_price'],
                'total_insu_fee'       => $quoteInfo['total_insu_fee'],
                'created_by'           => $this->user['id'],
                'created_at'           => date('Y-m-d H:i:s')
            ]));
        }

        $quoteItems = $this->quoteItemModel->where(['inquiry_id'=>$request['inquiry_id'],'deleted_flag'=>'N'])->field('id,inquiry_id,inquiry_item_id,sku,supplier_id,quote_unit_price,exw_unit_price')->select();

        $finalItems = $finalQuoteItemModel->where(['inquiry_id'=>$request['inquiry_id'],'deleted_flag'=>'N'])->getField('quote_item_id',true);
        $quote_id = $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']);

        foreach ($quoteItems as $quote=>$item){
            if(!in_array($item['id'],$finalItems)){
                $finalQuoteItemModel->add($finalQuoteItemModel->create([
                    'quote_id'         => $quote_id,
                    'inquiry_id'       => $request['inquiry_id'],
                    'inquiry_item_id'  => $item['inquiry_item_id'],
                    'quote_item_id'    => $item['id'],
                    'sku'              => $item['sku'],
                    'supplier_id'      => $item['supplier_id'],
                    'quote_unit_price' => $item['quote_unit_price'],
                    'exw_unit_price'   => $item['exw_unit_price'],
                    'created_by'       => $this->user['id'],
                    'created_at'       => date('Y-m-d H:i:s'),
                ]));
            }else{
                $finalQuoteItemModel->where('id='.$item['id'])->save($finalQuoteItemModel->create([
                    'quote_id'         => $quote_id,
                    'inquiry_id'       => $request['inquiry_id'],
                    'inquiry_item_id'  => $item['inquiry_item_id'],
                    'quote_item_id'    => $item['id'],
                    'sku'              => $item['sku'],
                    'supplier_id'      => $item['supplier_id'],
                    'quote_unit_price' => $item['quote_unit_price'],
                    'exw_unit_price'   => $item['exw_unit_price'],
                    'created_by'       => $this->user['id'],
                    'created_at'       => date('Y-m-d H:i:s'),
                ]));
            }

        }

        //发送短信通知
        $employee = new EmployeeModel();
        $this->sendSms($employee->getMobileByUserId($check_org_id),"SUBMIT",$employee->getUserNameById($check_org_id),$inquiryModel->getSerialNoById($request['inquiry_id']),$this->user['name'],"BIZ_APPROVING","MARKET_APPROVING");


        $this->jsonReturn();

    }

    /**
     * 退回报价(审核人)
     */
    public function rejectAction(){

        $request = $this->validateRequests('inquiry_id');

        //更新当前办理人
        $inquiry = new InquiryModel();
        $now_agent_id = $inquiry->where(['id' => $request['inquiry_id']])->getField('quote_id');

        $response = $this->inquiryModel->updateData([
            'id'            => $request['inquiry_id'],
            'now_agent_id'  => $now_agent_id,
            'inflow_time'   => date('Y-m-d H:i:s',time()),
            'status'        => 'BIZ_APPROVING',
            'updated_by'    => $this->user['id'],
            'updated_at'   =>date('Y-m-d H:i:s',time())
        ]);

        $this->jsonReturn($response);

    }

    /**
     * 确认报价(审核人)
     */
    public function confirmAction(){

        $request = $this->validateRequests('inquiry_id');

        //更新当前办理人
        $now_agent_id = $this->inquiryModel->where(['id'=>$request['inquiry_id']])->getField('agent_id');

        $response = $this->inquiryModel->updateData([
            'id'           => $request['inquiry_id'],
            'now_agent_id' => $now_agent_id,
            'inflow_time'   => date('Y-m-d H:i:s',time()),
            'status'       => 'MARKET_CONFIRMING',
            'updated_by'   => $this->user['id'],
            'updated_at'   =>date('Y-m-d H:i:s',time())
        ]);

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
            $list[$key]['supplier_name']       = $supplier->where(['id' => $value['supplier_id']])->getField('name');
        }

        $this->jsonReturn($list);

    }

    /**
     * 保存SKU信息，加校验
     */
    public function updateSkuAction(){

        $request = $this->validateRequests();
        //验证必填项是否填写
        $checkitem = $this->checkSkuFieldsAction($request['data']);
        if($checkitem['code'] == '1'){
            $response = $this->quoteItemModel->updateItem($request['data'],$this->user['id']);
            $this->jsonReturn($response);
        }else{
            $this->jsonReturn($checkitem);
        }
    }

    /**
     * 批量保存SKU信息，不加校验
     */
    public function updateSkuBatchAction(){
        $request = $this->validateRequests();

        $response = $this->quoteItemModel->updateItemBatch($request['data'],$this->user['id']);
        $this->jsonReturn($response);
    }

    public function updateSupplierAction(){

        $request = $this->validateRequests();
        $return = $this->quoteItemModel->updateSupplier($request['data']);
        $this->jsonReturn($return);
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
            $list[$key]['exw_unit_price'] = sprintf("%.4f", $list[$key]['exw_unit_price']);
            $list[$key]['quote_unit_price'] = sprintf("%.4f", $list[$key]['quote_unit_price']);
            $list[$key]['final_exw_unit_price'] = sprintf("%.4f", $list[$key]['final_exw_unit_price']);
            $list[$key]['final_quote_unit_price'] = sprintf("%.4f", $list[$key]['final_quote_unit_price']);
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
     * 删除所有相关的SKU
     */
    public function delItemAllAction(){
        $quoteItemLogiModel = new QuoteItemLogiModel();
        $finalQuoteItemModel = new FinalQuoteItemModel();

        $request = $this->validateRequests('id');
        $inquiryItemIds = $request['id'];

        //先删除询单SKU，在删除报价单SKU，最后删除物流和市场报价单SKU
        $this->inquiryItemModel->startTrans();
        $results = $this->inquiryItemModel->deleteData($request);    //删除询单SKU
        if($results['code'] == 1){
            //判断报价单SKU表是否存在数据，有就删除
            $quoteItemIds = $this->quoteItemModel->where('inquiry_item_id IN('.$inquiryItemIds.')')->getField('id',true);
            if($quoteItemIds){
                $resquote = $this->quoteItemModel->delItem($inquiryItemIds);    //删除报价单SKU
                if($resquote){
                    $quoteItemId = implode(',',$quoteItemIds);

                    //判断物流SKU表是否存在数据，有就删除
                    $logiItemIds = $quoteItemLogiModel->where('quote_item_id IN('.$quoteItemId.')')->getField('id',true);
                    if($logiItemIds){
                        $logiItemId['r_id'] = implode(',',$logiItemIds);
                        $reslogi = $quoteItemLogiModel->delRecord($logiItemId);
                        if(!$reslogi){
                            $this->inquiryItemModel->rollback();
                            $results['code'] = '-101';
                            $results['messaage'] = '删除失败!';
                            $this->jsonReturn($results);
                        }
                    }

                    //判断物流SKU表是否存在数据，有就删除
                    $finalItemIds = $finalQuoteItemModel->where('quote_item_id IN('.$quoteItemId.')')->getField('id',true);
                    if($finalItemIds){
                        $finalItemId['id'] = implode(',',$finalItemIds);
                        $resfinal = $finalQuoteItemModel->delItem($finalItemId);
                        if(!$resfinal){
                            $this->inquiryItemModel->rollback();
                            $results['code'] = '-101';
                            $results['messaage'] = '删除失败!';
                            $this->jsonReturn($results);
                        }
                    }
                    $this->inquiryItemModel->commit();
                    $this->jsonReturn($results);
                }else{
                    $this->inquiryItemModel->rollback();
                    $results['code'] = '-101';
                    $results['messaage'] = '删除失败!';
                    $this->jsonReturn($results);
                }
            }else{
                $this->inquiryItemModel->commit();
                $this->jsonReturn($results);
            }
        }else{
            $this->inquiryItemModel->rollback();
            $this->jsonReturn($results);
        }
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

        return $this->inquiryModel->updateStatus([
            'id' => $id,
            'status' => $status,
            'updated_by' => $this->user['id']
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

    /**
     * 验证报价单SKU必填和数字字段
     * @param $request
     * @return mixed
     */
    public function checkSkuFieldsAction($data=[]){

        foreach($data as $key=>$value){
            if(empty($value['reason_for_no_quote'])){
                //供应商活着未报价分析
                if(empty($value['supplier_id'])){
                    return ['code'=>'-104','message'=>'供应商未选择'];
                }
                //品牌
                if (empty($value['brand'])){
                    return ['code'=>'-104','message'=>'品牌必填'];
                }
                //采购单价
                if (empty($value['purchase_unit_price'])){
                    return ['code'=>'-104','message'=>'采购单价必填'];
                }
                if (!is_numeric($value['purchase_unit_price'])){
                    return ['code'=>'-104','message'=>'采购单价必须是数字'];
                }
                //采购币种
                if (empty($value['purchase_price_cur_bn'])){
                    return ['code'=>'-104','message'=>'采购币种必选'];
                }
                //毛重
                if (empty($value['gross_weight_kg'])){
                    return ['code'=>'-104','message'=>'毛重必填'];
                }
                if (!is_numeric($value['gross_weight_kg'])){
                    return ['code'=>'-104','message'=>'毛重必须是数字'];
                }
                //包装体积
                if (empty($value['package_size'])){
                    return ['code'=>'-104','message'=>'包装体积必填'];
                }
                if (!is_numeric($value['package_size'])){
                    return ['code'=>'-104','message'=>'包装体积必须是数字'];
                }
                //包装方式
                if (empty($value['package_mode'])){
                    return ['code'=>'-104','message'=>'包装方式必填'];
                }
                //产品来源
                if (empty($value['goods_source'])){
                    return ['code'=>'-104','message'=>'产品来源必填'];
                }
                //存放地
                if (empty($value['stock_loc'])){
                    return ['code'=>'-104','message'=>'存放地必填'];
                }
                //交货期(天)，报价有效期
                if (empty($value['delivery_days'])){
                    return ['code'=>'-104','message'=>'交货期必填'];
                }
                if (!is_numeric($value['delivery_days'])){
                    return ['code'=>'-104','message'=>'交货期必须是数字'];
                }
                //报价有效期
                if (empty($value['period_of_validity'])){
                    return ['code'=>'-104','message'=>'报价有效期必填'];
                }
            }
        }

        return ['code'=>'1','message'=>'验证通过'];
    }
}

