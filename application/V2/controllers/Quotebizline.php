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
//        parent::init();
        $this->_quoteBizLine = new QuoteBizLineModel();
        $this->_quoteItemBizLine = new QuoteItemBizLineModel();
        $this->_requestParams = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * @desc 划分产品线(项目经理)
     * @author 买买提
     */
    public function partitionBizlineAction(){

        $request = $this->_requestParams;

        if (empty($request['quote_id']) || empty($request['serial_no']) || empty($request['bizline_id']) || empty($request['created_by'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }

        $this->jsonReturn($this->_quoteBizLine->setPartitionBizline($request));
    }

    /**
     * 产品线报价->项目经理->转交其他人办理
     * 操作说明:转交后，当前人员就不是项目经理了，如果也不是方案中心的人，就不能再查看这个项目了
     */
    public function transmitAction(){

        if (empty($this->_requestParams['serial_no']) || empty($this->_requestParams['ori_pm_id']) || empty($this->_requestParams['pm_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $response = QuoteBizlineHelper::transmitHandler($this->_requestParams);
        $this->jsonReturn($response);

    }

    /**
     * @desc 提交产品线报价(项目经理)
     * @author 买买提
     * 操作说明:当前询单的状态改为产品线报价中
     */
    public function submitToBizlineAction(){

        if (empty($this->_requestParams['serial_no'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $this->jsonReturn(QuoteBizlineHelper::submitToBizline($this->_requestParams));

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
    public function addBizlineAttach(){
        return ['code'=>'1','message'=>'上传成功!'];
    }

    /**
     * @desc 指派报价人
     * @author 买买提
     */
    public function assignQuoterAction() {
        /*
        |--------------------------------------------------------------------------
        | 产品线报价->指派报价人   角色:产品线负责人
        |--------------------------------------------------------------------------
        |
        | 操作说明
        | 前端需要提交的字段 quote_id报价id bizline_agent_id产品线报价人
        | 把当前报价单的产品线报价人字段改为新选择的id
        |
        */
        if( empty($this->_requestParams['quote_id']) || empty($this->_requestParams['biz_agent_id']) ){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
        }
        $this->jsonReturn($this->_quoteBizLine->assignQuoter($this->_requestParams));

    }

    /**
     * @desc 选择报价(产品线负责人)
     * @author 买买提
     */
    public function selectQuoteAction(){

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
     * @desc 报价信息(列表)
     * @author 买买提
     */
    public function quoteListAction(){

        $request = $this->_requestParams;
        if (empty($request['inquiry_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }

        $response = QuoteHelper::quoteListHandler($request['inquiry_id'],'QUOTER');
        if (!$response){
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!','data'=>'']);
        }

       $this->jsonReturn([
           'code' => '1',
           'message' => '成功!',
           'data' => $response
       ]);

    }

    /**
     * @desc 产品线报价->询单列表(角色:项目经理)
     * @author 买买提
     */
    public function listAction(){
        //p($this->getUserInfo()[id]);
        $filterParams = QuoteBizlineHelper::filterListParams($this->_requestParams,'PM');
        $this->jsonReturn(QuoteBizlineHelper::getQuotelineInquiryList($filterParams));
    }

    /**
     * @desc 产品线报价->列表(角色:产品线相关人员)
     * @author 买买提
     */
    public function listBizlineAction(){
        $this->jsonReturn($this->listBizlineHandler());
    }
    /**
     * @desc 产品线报价->列表(角色:产品线相关人员)
     * @author 买买提
     * 说明:产品线相关人员：可以查看自己负责的询单
     * 数据库操作说明:查找当前用户id跟inquiry表中pm_id字段值相等的items
     */
    private function listBizlineHandler(){
        $filterParams = QuoteBizlineHelper::filterListParams($this->_requestParams,'BIZLINE');
        return QuoteBizlineHelper::getQuotelineInquiryList($filterParams);
    }

    /**
     * @desc 详情页询单信息接口(只读)
     * @author 买买提
     * 说明:项目经理,产品线相关人员通用
     * 数据库操作:inquiry询单表 inquiry_item询单明细表
     */
    public function inquiryInfoAction(){

        $request = $this->_requestParams;
        //获取询单本身信息
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no'=>$request['serial_no']])->field(QuoteBizlineHelper::getInquiryInfoFields())->find();

        if (!$inquiryInfo){
            return ['code'=>'-104','message'=>'没有询单信息','data'=>''];
        }

        //重组询单信息数组并追加询单明细列表
        $this->jsonReturn(QuoteBizlineHelper::restoreInqiryInfo($inquiryInfo)) ;

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
     * @desc 附件列表
     * @author 买买提
     */
    public function attachAction() {

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
