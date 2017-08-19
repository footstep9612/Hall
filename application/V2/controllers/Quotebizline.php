<?php

/**
 * 产品线报价
 * Class QuotebizlineController
 * @author 买买提
 */
class QuotebizlineController extends PublicController {

    /**
     * 产品线报价单模型
     * @var
     */
    private $_quoteBizLine;

    /**
     * 报价单详情模型
     * @var
     */
    private $_quoteItemBizLine;
    private $_requestParams = [];

    /**
     * 构造方法
     */
    public function init() {
        parent::init();
        $this->_quoteBizLine = new QuoteBizLineModel();
        $this->_quoteItemBizLine = new QuoteItemBizLineModel();
        $this->_requestParams = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params=''){

        $request = $this->_requestParams;
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
     * @desc 产品线报价列表(项目经理)
     * @author 买买提
     */
    public function bizlineListAction(){

        $condition = $this->validateRequests();

        $user = new EmployeeModel();

        if (!empty($condition['agent_name'])) {
            $agent = $user->where(['name' => $condition['agent_name']])->find();
            $condition['agent_id'] = $agent['id'];
        }

        if (!empty($condition['pm_name'])) {
            $pm = $user->where(['name' => $condition['pm_name']])->find();
            $condition['pm_id'] = $pm['id'];
        }

        $quoteBizlineList = QuoteHelper::getPmQuoteBizlineList($condition);
        //$quoteBizlineList = $this->_quoteBizLine->getJoinList($condition);

        foreach ($quoteBizlineList as &$quoteBizline) {
            $quoteBizline['agent_name'] = $user->where(['id'=>$quoteBizline['agent_id']])->getField('name');
            $quoteBizline['pm_name'] = $user->where(['id'=>$quoteBizline['pm_id']])->getField('name');
        }

        if ($quoteBizlineList) {
            $this->jsonReturn([
                'code' => '1',
                'message' => '成功!',
                'count' => QuoteHelper::getPmQuoteBizlineListCount($condition),
                'data' => $quoteBizlineList
            ]);
        } else {
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }
    }

    /**
     * @desc 报价单sku列表(项目经理)
     */
    public function quoteSkuListAction(){

        $request = $this->validateRequests('id');

        $quoteSkuList = QuoteHelper::getQuoteList($request);

        if ($quoteSkuList){
            $this->jsonReturn([
                'code' => '1',
                'message' => '成功!',
                'count' => QuoteHelper::getQuoteTotalCount($request),
                'data' => $quoteSkuList
            ]);
        }else{
            $this->jsonReturn([
                'code' => '-104',
                'message' => '没有数据!',
                'data' => ''
            ]);
        }

    }

    /**
     * @desc 划分产品线(项目经理)
     * @author 买买提
     */
    public function partitionBizlineAction(){

        $request = $this->validateRequests('inquiry_id,buyer_id,serial_no,inquiry_item_id,bizline_id');

        //1.创建一条报价记录(quote)
        $quoteModel = new QuoteModel();
        $quoteModel->startTrans();
        //TODO 这里可以添加是否已经划分了产品线的判断，避免发生重复提交数据的事情
        $inquiry_item_ids = explode(',',$request['inquiry_item_id']);
        $quote_ids = [];//新增的报价id组合
        foreach ($inquiry_item_ids as $inquiry_item_id){
            $quote_ids[] = $quoteModel->add($quoteModel->create([
                'buyer_id' => $request['buyer_id'],
                'inquiry_id' => $request['inquiry_id'],
                'serial_no' => $request['serial_no'],
                'quote_no' => $this->getQuoteNo(),
                'quote_lang' => 'zh',
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }

        //2.创建一条产品线报价记录(quote_bizline)
        $quoteBizlineModel = new QuoteBizLineModel();
        $quoteBizlineModel->startTrans();
        $quoteBizline_ids = [];
        foreach ($quote_ids as $quote_id){
            $quoteBizline_ids[] = $quoteBizlineModel->add($quoteBizlineModel->create([
                'quote_id' => $quote_id,
                'inquiry_id' => $request['inquiry_id'],
                'biz_agent_id' => '275',
                'bizline_id' => $request['bizline_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->user['id'],
            ]));
        }

        //3.选择的询单项(inquiry_item)写入到报价单项(quote_item)
        $inquiryItem = new InquiryItemModel();
        $inquiryItemList = $inquiryItem->where('id IN('.$request['inquiry_item_id'].')')->select();

        $quoteItemModel = new QuoteItemModel();
        $quoteItemModel->startTrans();
        $quoteItemIds = [];
        foreach ($quote_ids as $quote_id){
            foreach ($inquiryItemList as $item){
                $quote_item_ids[] = $quoteItemModel->add($quoteItemModel->create([
                    'quote_id' => $quote_id,
                    'inquiry_id' => $item['inquiry_id'],
                    'inquiry_item_id' => $item['id'],
                    'bizline_id' => $request['bizline_id'],
                    'sku' => $item['sku'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->user['id'],
                ]));
            }
        }

        //4选择的讯单项(inquiry_item)写入到产品线报价单项(quote_item_form)
        $quote_item_form_list = $quoteItemModel->where('id IN('.implode(",",$quote_item_ids).')')->field('quote_id,id,inquiry_item_id,bizline_id,sku')->select();

        $quoteItemFormModel = new QuoteItemFormModel();
        $quote_item_form_ids = [];
        foreach ($quote_item_form_list as $v){
            $quote_item_form_ids[] = $quoteItemFormModel->add($quoteItemFormModel->create([
                'quote_id' => $v['quote_id'],
                'quote_item_id' => $v['id'],
                'inquiry_item_id' => $v['inquiry_item_id'],
                'quote_bizline_id' => $v['bizline_id'],
                'sku' => $v['sku'],
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->user['id']
            ]));
        }

        if ($quote_ids && $quoteBizline_ids && $quote_item_ids && $quote_item_form_ids){
            $quoteModel->commit();
            $quoteBizlineModel->commit();
            $quoteItemModel->commit();
            $quoteItemFormModel->commit();
            $this->jsonReturn(['code'=>'1','message'=>'成功!']);
        }else{
            $quoteModel->rollback();
            $quoteBizlineModel->rollback();
            $quoteItemModel->rollback();
            $quoteItemFormModel->rollback();
            $this->jsonReturn(['code'=>'-104','message'=>'失败!']);
        }

    }

    /**
     * @desc 转交其他人办理(项目经理)
     * 操作说明:转交后，当前人员就不是项目经理了，如果也不是方案中心的人，就不能再查看这个项目了
     */
    public function transmitAction(){

        $request = $this->validateRequests('id,pm_id');

        $response = QuoteBizlineHelper::transmitHandler($request,$this->user['id']);

        $this->jsonReturn($response);

    }

    /**
     * @desc 提交产品线报价(项目经理)
     * @author 买买提
     * 操作说明:当前询单的状态改为产品线报价中
     */
    public function submitToBizlineAction(){

        $request = $this->validateRequests('id');

        $this->jsonReturn(QuoteBizlineHelper::submitToBizline($request));

    }

    /**
     * @desc 产品线报价列表(产品线负责人)
     * @author 买买提
     */
    public function bizlineManagerQuoteListAction()
    {
        $condition = $this->validateRequests();

        $user = new EmployeeModel();

        if (!empty($condition['agent_name'])) {
            $agent = $user->where(['name' => $condition['agent_name']])->find();
            $condition['agent_id'] = $agent['id'];
        }

        if (!empty($condition['pm_name'])) {
            $pm = $user->where(['name' => $condition['pm_name']])->find();
            $condition['pm_id'] = $pm['id'];
        }

        $quoteBizlineList = QuoteHelper::bizlineManagerQuoteList($condition);

        foreach ($quoteBizlineList as &$quoteBizline) {
            $quoteBizline['agent_name'] = $user->where(['id'=>$quoteBizline['agent_id']])->getField('name');
            $quoteBizline['pm_name'] = $user->where(['id'=>$quoteBizline['pm_id']])->getField('name');
        }

        if ($quoteBizlineList) {
            //p($quoteBizlineList);
            $this->jsonReturn([
                'code' => '1',
                'message' => '成功!',
                'count' => QuoteHelper::bizlineManagerQuoteListCount($condition),
                'data' => $quoteBizlineList
            ]);
        } else {
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }

    }

    /**
     * @desc 报价sku列表(产品线负责人)
     */
    public function bizlineManagerQuoteSkuListAction(){

        $request = $this->validateRequests('quote_id');

        $skuList = QuoteHelper::bizlineManagerQuoteSkuList($request);

        if ($skuList){

            $user = new EmployeeModel();

            foreach ($skuList as $key=>$bizlineQuoteSku) {
                $skuList[$key]['created_by'] = $user->where(['id'=>$bizlineQuoteSku['created_by']])->getField('name');
            }

            $this->jsonReturn([
                'code' => '1',
                'message' => '成功!',
                'count' => QuoteHelper::bizlineManagerQuoteSkuListCount($request),
                'data' => $skuList
            ]);
        }

        $this->jsonReturn(['code'=>'-104','message'=>'没有数据!','data'=>'']);

    }

    /**
     * @desc 上传附件(项目经理)
     * @author 买买提
     */
    public function addAttachAction() {

        $request = $this->_requestParams;
        if (empty($request['quote_id']) || empty($request['attach_url'])){
            $this->jsonReturn([
                'code' => '-104',
                'message' => '缺少参数'
            ]);
        }

        $request['created_by'] = $this->user['id'];
        $this->jsonReturn(QuoteBizlineHelper::addAttach($request));
    }

    /**
     * @desc 附件列表
     * @author 买买提
     */
    public function attachListAction() {

        $request = $this->_requestParams;
        if ( empty($request['quote_id']) ){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
        }

        $quoteAttach = new QuoteAttachModel();
        $attachList = $quoteAttach->where(['quote_id' => $this->_requestParams['quote_id']])->order('created_at desc')->select();

        if (!$attachList) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '没有数据',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $attachList
        ]);
    }

    /**
     * @desc 退回产品线重新报价(项目经理)
     * @author 买买提
     */
    public function rejectBizlineAction(){

        if (empty($this->_requestParams['serial_no']) || empty($this->_requestParams['bizline_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $result = $this->_quoteBizLine->rejectBizline($this->_requestParams);
        $this->jsonReturn($result);
    }

    /**
     * @desc 提交物流报价(项目经理)
     * @author 买买提
     */
    public function sentLogisticsAction(){

        if (empty($this->_requestParams['serial_no'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $this->jsonReturn($this->_quoteBizLine->sentLogistics($this->_requestParams));
    }

    /**
     * @desc 退回物流重新报价
     * @author 买买提
     */
    public function rejectLogisticAction(){

        $request = $this->_requestParams;
        if (empty($request['serial_no']) || empty($request['quote_id']) || empty($request['op_note'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }

        //修改项目状态
        $inquiry =  new InquiryModel();
        $inquiryID = $inquiry->where(['serial_no'=>$request['serial_no']])->getField('id');
        if (!$inquiryID){
            $this->jsonReturn(['code'=>'-104','message'=>'没有对应的询单!']);
        }
        $inquiry->startTrans();
        $inquiuryResult = $inquiry->where(['serial_no'=>$request['serial_no']])->save([
            'logi_quote_status' => QuoteBizLineModel::QUOTE_REJECTED
        ]);

        //写审核日志
        $inquiryCheckLog = new InquiryCheckLogModel();
        $inquiryCheckLog->startTrans();
        $checkInfo = [
            'created_by' => !empty($this->user['id']) ? $this->user['id'] : 1,
            'created_at' => date('Y-m-d H:i:s'),
            'inquiry_id' => $inquiryID,
            'quote_id' => $request['quote_id'],
            'category' => 'BIZLINE',
            'action' => 'APPROVING',
            'op_note' => $request['op_note'],
            'op_result' => 'REJECTED'
        ];
        $checklogResult = $inquiryCheckLog->add($checkInfo);

        if ($inquiuryResult && $checklogResult){
            $inquiry->commit();
            $inquiryCheckLog->commit();
            $this->jsonReturn(['code'=>'1','message'=>'退回成功!']);
        }else{
            $inquiry->rollback();
            $inquiryCheckLog->rollback();
            $this->jsonReturn(['code'=>'-104','message'=>'退回失败!']);
        }

    }

    /**
     * @desc 提交市场确认报价
     * @author 买买提
     */
    public function sentMarketAction(){

        $request = $this->_requestParams;
        if (empty($request['serial_no'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $inquiry = Z('erui2_rfq.Inquiry');
        $inquiryResult = $inquiry->where([
            'serial_no' => $request['serial_no']
        ])->save([
            'status' => QuoteBizLineModel::INQUIRY_APPROVING_BY_MARKET,
            'logi_quote_status' => QuoteBizLineModel::QUOTE_APPROVED
        ]);
        //p($inquiryResult);
        if ($inquiryResult){
            $this->jsonReturn(['code'=>'1','message'=>'提交成功!']);
        }else{
            $this->jsonReturn(['code'=>'-104','message'=>'提交失败!']);
        }
    }

    /**
     * @desc 产品线负责人上传附件(产品线负责人)
     * @return array
     */
    public function addBizlineAttachAction(){
        $request = $this->_requestParams;
        if (empty($request['quote_id']) || empty($request['attach_url'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
        }

        $request['created_by'] = $this->user['id'];
        $response = QuoteHelper::addBizlineAttach($request);
        $this->jsonReturn($response);
    }

    /**
     * @desc 指派报价人(产品线负责人)
     * @author 买买提
     */
    public function assignQuoterAction() {
        /*
        | 操作说明
        | 前端需要提交的字段 quote_id报价id bizline_agent_id产品线报价人
        | 把当前报价单的产品线报价人字段改为新选择的id
        |
        */
        $request = $this->validateRequests('quote_id,biz_agent_id');

        $this->jsonReturn($this->_quoteBizLine->assignQuoter($request));

    }

    /**
     * @desc 选择报价(产品线负责人)
     * @author 买买提
     */
    public function selectQuoteAction(){
        echo 12345678;
    }

    /**
     * @desc 退回报价(产品线负责人)
     * @author 买买提
     */
    public function rejectQuoteAction() {
        /*
          |--------------------------------------------------------------------------
          | 产品线报价->退回报价   角色:产品线负责人
          |--------------------------------------------------------------------------
          |
          | 操作说明
          | 退回报价：全部SKU改为 被驳回状态 只有全部SKU都是“已报价”状态，才能退回
          |
         */
        //1.更改当前的报价状态为被退回
        $sendBackQuote = $this->_quoteBizLine->sendback($this->_requestParams['quote_id']);

        //2.更改该报价所属的sku状态为被驳回状态
        $sendBackQuoteSku = $this->_quoteItemBizLine->sendback($this->_requestParams['quote_id']);

        if ($sendBackQuote && $sendBackQuoteSku) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '退回成功!'
            ]);
        }

        $this->jsonReturn([
            'code' => -101,
            'message' => '退回失败！'
        ]);
    }

    /**
     * @desc 提交项目经理审核
     * @author 买买提
     */
    public function sentToManagerAction() {
        /*
        |--------------------------------------------------------------------------
        | 产品线报价->提交项目经理审核   角色:产品线负责人
        |--------------------------------------------------------------------------
        |
        | 操作说明
        | 项目状态:项目经理审核
        | 把当前项目(询单)的状态改为项目经理审核
        */
        if (empty($this->_requestParams['serial_no'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $this->jsonReturn(QuoteBizlineHelper::submitToManager($this->_requestParams));
    }

    /**
     * @desc 选择供应商(产品线报价人)
     * @author 买买提
     */
    public function selectSupplierAction() {
        /*
        |--------------------------------------------------------------------------
        | 产品线报价->选择供应商   角色:产品线报价人
        |--------------------------------------------------------------------------
        | 操作说明
        | 当前用户所在报价小组，对应的供应商
        |
        | 当前用户信息
        | 查找当前用户所在的报价小组(产品线id)  [bizline_group表]
        | 查找产品线对应的供应商列表 [bizline_supplier表]
        |
        */
        //当前用户所在的产品线id
        $bizline_id = 1;

        //产品线对应的供应商
        $bizlineSupplier = new BizlineSupplierModel();
        //TODO 这里后期可能添加搜索功能
        $bizline_suppliers = $bizlineSupplier->getList($bizline_id);

        if ($bizline_suppliers) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '成功!'
            ]);
        }

        $this->jsonReturn([
            'code' => -104,
            'message' => '没有相关记录!'
        ]);
    }

    /**
     * @desc 提交产品线负责人审核(产品线报价人)
     * @author 买买提
     */
    public function sentToBizlineManagerAction() {
        /*
        |--------------------------------------------------------------------------
        | 产品线报价->提交产品线负责人审核   角色:产品线报价人
        |--------------------------------------------------------------------------
        | 操作说明
        | 点击暂存后，不做校验，市场的进度为待提交
        | 当前报价单状态改为待提交  [quote_bizlie表]
        |
        */
        //判断参数是否正确
        if (empty($this->_requestParams['quote_id']) || empty($this->_requestParams['bizline_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        //保存数据及更改状态
        $quoteBizline = new QuoteBizLineModel();
        $this->jsonReturn($quoteBizline->submitToBizlineManager($this->_requestParams));

    }

    /**
     * @desc 获取综合报价信息(项目经理)
     */
    public function quoteGeneralInfoAction(){

        $request = $this->validateRequests(['quote_id']);

        $fields = 'q.id,q.total_weight,q.package_volumn,q.package_mode,q.payment_mode,q.trade_terms_bn,q.payment_period,q.from_country,q.to_country,q.trans_mode_bn,q.delivery_period,q.fund_occupation_rate,q.bank_interest,q.total_bank_fee,q.period_of_validity,q.exchange_rate,q.total_logi_fee,q.total_quote_price,q.total_exw_price,fq.total_quote_price final_total_quote_price,fq.total_exw_price final_total_exw_price';
        $quoteModel = new QuoteModel();
        $result = $quoteModel->alias('q')
                             ->join('erui2_rfq.final_quote fq ON q.id = fq.quote_id','LEFT')
                             ->field($fields)
                             ->where(['q.id'=>$request['quote_id']])
                             ->find();
        if (!$result){
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }
        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!',
            'data' => $result
        ]);

    }

    /**
     * @desc 询单信息(通用)
     * @author 买买提
     */
    public function inquiryInfoAction(){

        $request = $this->_requestParams;
        if (empty($request['serial_no'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
        }

        //获取询单本身信息
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no'=>$request['serial_no']])->field([
            'id','serial_no','status','pm_id'
        ])->find();

        if (!$inquiryInfo){
            return ['code'=>'-104','message'=>'没有询单信息','data'=>''];
        }
        //重组询单信息数组并追加询单明细列表
        $this->jsonReturn([
            'code'=> '1',
            'message' => '成功!',
            'data' => QuoteHelper::restoreInquiryInfo($inquiryInfo)
        ]) ;

    }

    /**
     * @desc 详情页报价信息接口
     */
    public function quoteInfoAction() {

        $data = $this->_quoteBizLine->getQuoteInfo($this->_requestParams['quote_id']);

        if (!$data) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '失败',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);
    }

    /**
     * @desc 暂存
     * @author 买买提
     */
    public function manageAction() {

    }

    /**
     * @desc 报价办理->暂存接口
     * @author 买买提
     */
    public function storageQuoteAction() {
        /*
          |--------------------------------------------------------------------------
          | 报价单信息暂存   角色:产品线负责人
          |--------------------------------------------------------------------------
          |
          | 操作说明
          | 提交暂存后，不做校验，市场的进度为待提交
          |
         */

        $result = $this->_quoteBizLine->storageQuote($this->_requestParams['quote_id']);
        if (!$result) {
            $this->jsonReturn([
                'code' => '-104',
                'message' => '失败!',
            ]);
        }

        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!'
        ]);
    }

    /**
     * @desc 查看审核信息
     * @author 买买提
     */
    public function verifyInfoAction() {

        $quoteItem = new QuoteItemBizLineModel();

        $data = $quoteItem->getVerifyInfo($this->_requestParams['quote_id']);

        if (!$data) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '失败',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);
    }

    /**
     * @desc 暂存(可能要废弃)
     * @author 买买提
     */
    public function quoterStorageAction() {
        /*
          |--------------------------------------------------------------------------
          | 产品线报价->暂存   角色:产品线报价人
          |--------------------------------------------------------------------------
          | 操作说明
          | 点击暂存后，不做校验，市场的进度为待提交
          | 当前报价单状态改为待提交  [quote_bizlie表]
          |
         */
        $quote_id = 1;
        $result = $this->_quoteBizLine->quoterStorage($quote_id);

        //TODO 这里可能添加一些列逻辑

        if ($result) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '成功!'
            ]);
        }

        $this->jsonReturn([
            'code' => -104,
            'message' => '失败!'
        ]);
    }

}
