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
     * @desc 项目经理报价sku列表(新增)
     */
    public function pmQuoteListAction(){

        $request = $this->validateRequests('inquiry_id');

        $quoteBizline =  new QuoteBizLineModel();
        $response = $quoteBizline->getPmQuoteList($request);

        if (!$response){
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }

        $bizline = new BizlineModel();
        $user = new EmployeeModel();

        foreach ($response as $k=>$v){
            $response[$k]['bizline_name'] = $bizline->where(['id'=>$v['bizline_id']])->getField('name');
            $response[$k]['bizline_agent_name'] = $user->where(['id'=>$v['bizline_agent_id']])->getField('name');
        }

        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!',
            'total' => $quoteBizline->getPmQuoteListCount($request),
            'data' => $response
        ]);
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
        $quoteResult = $quoteModel->add($quoteModel->create([
            'buyer_id' => $request['buyer_id'],
            'inquiry_id' => $request['inquiry_id'],
            'serial_no' => $request['serial_no'],
            'quote_no' => $this->getQuoteNo(),
            'quote_lang' => 'zh',
            'created_at' => date('Y-m-d H:i:s')
        ]));


        //判断是否已经划分了产品线

        //2.创建一条产品线报价记录(quote_bizline)
        $quoteBizlineModel = new QuoteBizLineModel();
        $quoteBizlineModel->startTrans();
        $quoteBizlineResult = $quoteBizlineModel->add($quoteBizlineModel->create([
            'quote_id' => $quoteResult,
            'inquiry_id' => $request['inquiry_id'],
            'bizline_id' => $request['bizline_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->user['id'],
        ]));

        //3.选择的询单项(inquiry_item)写入到报价单项(quote_item)
        $inquiryItem = new InquiryItemModel();
        $inquiryItemList = $inquiryItem->where('id IN('.$request['inquiry_item_id'].')')->select();

        $quoteItemModel = new QuoteItemModel();
        $quoteItemModel->startTrans();

        $quote_item_ids = [];
        foreach ($inquiryItemList as $item){
            $quote_item_ids[] = $quoteItemModel->add($quoteItemModel->create([
                'quote_id' => $quoteResult,
                'inquiry_id' => $item['inquiry_id'],
                'inquiry_item_id' => $item['id'],
                'bizline_id' => $request['bizline_id'],
                'sku' => $item['sku'],
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->user['id'],
            ]));
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

        if ($quoteResult && $quoteBizlineResult && $quote_item_ids && $quote_item_form_ids){
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
            $supplier = new SupplierModel();

            foreach ($skuList as $key=>$bizlineQuoteSku) {
                $skuList[$key]['supplier_name'] = $supplier->where(['id'=>$bizlineQuoteSku['supplier_id']])->getField('name');
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
     */
    public function rejectBizlineAction(){

        $request = $this->validateRequests('inquiry_id');

        $quote = new QuoteModel();
        $request['quote_id'] = $quote->where(['inquiry_id'=>$request['inquiry_id']])->getField('id');

        $quoteitem = new QuoteItemModel();
        $quotebizline = new QuoteBizLineModel();
        $quoteitemform = new QuoteItemFormModel();

        //查找报价单全部SKU id
        $ids = $quoteitem->where('quote_id='.$request['quote_id'])->getField('id',true);

        $itemformwhere['quote_item_id'] = array('in',$ids);
        //事物开始
        $quoteitemform->startTrans();
        $upitemform = $quoteitemform->where($itemformwhere)->save(['status' => 'REJECTED']);

        if($upitemform){
            $upquotetatus = $quote->where('id='.$request['quote_id'])->save(['status' => 'BZ_QUOTE_REJECTED']);//修改报价单状态
            $upbizlinestatus = $quotebizline->where('quote_id='.$request['quote_id'])->save(['status' => 'REJECTED']);//修改产品线报价状态

            if($upquotetatus && $upbizlinestatus){
                $quoteitemform->commit();
                $result['code'] = '1';
                $result['message'] = '成功!';
            }else{
                $quoteitemform->rollback();
                $result['code'] = '-101';
                $result['message'] = '返回产品线失败!';
            }
        }else{
            $quoteitemform->rollback();
            $result['code'] = '-101';
            $result['message'] = '返回产品线失败!';
        }

        $this->jsonReturn($result);
    }
    /**
     * @desc 提交物流报价(项目经理)
     * @author 买买提
     */
    public function sentLogisticsAction(){

        $request = $this->validateRequests('inquiry_id');
        $quoteBizline = new QuoteBizLineModel();
        $response = $quoteBizline->sentLogistics($request,$this->user['id']);
        $this->jsonReturn($response);

    }

    /**
     * @desc 退回物流重新报价
     * @author 买买提
     */
    public function rejectLogisticAction(){

        $request = $this->validateRequests('serial_no,quote_id,op_note');

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

        $request = $this->validateRequests('inquiry_id');

        $inquiry = new InquiryModel();
        $inquiry->startTrans();
        $inquiryResult = $inquiry->where([
            'id' => $request['inquiry_id']
        ])->save([
            'status' => QuoteBizLineModel::INQUIRY_APPROVED_BY_PM,
            'logi_quote_status' => QuoteBizLineModel::QUOTE_APPROVED
        ]);

        if ($inquiryResult){

            //1.创建final_quote记录
            $quote = new QuoteModel();
            $quoteData = $quote->where(['inquiry_id'=>$request['inquiry_id']])->find();

            $finalQuote = new FinalQuoteModel();
            $finalQuote->startTrans();
            $finalQuoteResult = $finalQuote->add($finalQuote->create([
                'buyer_id' => $quoteData['buyer_id'],
                'inquiry_id' => $quoteData['inquiry_id'],
                'quote_id' => $quoteData['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]));

            //2.创建final_quote_item记录
            $quoteItem = new QuoteItemModel();
            $quoteItemData = $quoteItem->where(['inquiry_id'=>$request['inquiry_id']])->select();

            $finalQuoteItem = new FinalQuoteItemModel();
            $finalQuoteItem->startTrans();
            $finalQuoteIds = [];
            foreach ($quoteItemData as $value){
                $finalQuoteIds[] = $finalQuoteItem->add($finalQuoteItem->create([
                    'quote_id' => $value['quote_id'],
                    'inquiry_id' => $value['inquiry_id'],
                    'inquiry_item_id' => $value['inquiry_item_id'],
                    'quote_item_id' => $value['id'],
                    'sku' => $value['sku'],
                    'supplier_id' => $value['supplier_id'],
                    'total_logi_fee' => $value['total_logi_fee'],
                    'total_logi_fee_cur_bn' => $value['total_logi_fee_cur_bn'],
                    'total_bank_fee' => $value['total_bank_fee'],
                    'total_bank_fee_cur_bn' => $value['total_bank_fee_cur_bn'],
                    'total_insu_fee' => $value['total_insu_fee'],
                    'total_insu_fee_cur_bn' => $value['total_insu_fee_cur_bn'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]));
            }
            if ($finalQuoteResult && $finalQuoteIds){
                $inquiry->commit();
                $finalQuote->commit();
                $finalQuoteItem->commit();
                $this->jsonReturn(['code'=>'1','message'=>'提交成功!']);
            }else{
                $inquiry->rollback();
                $finalQuote->rollback();
                $finalQuoteItem->rollback();
                $this->jsonReturn(['code'=>'-104','message'=>'回归失败!']);
            }
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
    public function bizlineManagerRejectQuoteAction() {

        $request = $this->validateRequests('inquiry_id,quote_id,op_note');

        $quoteBizline = new QuoteBizLineModel();
        $response = $quoteBizline->bizlineManagerRejectQuote($request);

        $this->jsonReturn($response);

    }

    /**
     * @desc 提交项目经理审核(产品线负责人)
     */
    public function sentToManagerAction() {

        $request = $this->validateRequests('quote_id,serial_no');
        $response = QuoteBizlineHelper::submitToManager($request);
        $this->jsonReturn($response);
    }

    /**
     * @desc 提交产品线负责人审核(产品线报价人)
     */
    public function sentToBizlineManagerAction() {

        $request = $this->validateRequests('quote_id');

        //保存数据及更改状态
        $quoteBizline = new QuoteBizLineModel();

        $this->jsonReturn($quoteBizline->submitToBizlineManager($request));

    }

    /**
     * @desc 获取综合报价信息(项目经理)
     */
    public function quoteGeneralInfoAction(){

        $request = $this->validateRequests('inquiry_id');

        $fields = 'q.id,q.total_weight,q.package_volumn,q.package_mode,q.payment_mode,q.trade_terms_bn,q.payment_period,q.from_country,q.to_country,q.from_port,q.to_port,q.trans_mode_bn,q.delivery_period,q.fund_occupation_rate,q.bank_interest,q.total_bank_fee,q.period_of_validity,q.exchange_rate,q.total_logi_fee,q.total_quote_price,q.total_exw_price,fq.total_quote_price final_total_quote_price,fq.total_exw_price final_total_exw_price';
        $quoteModel = new QuoteModel();
        $result = $quoteModel->alias('q')
                             ->join('erui2_rfq.final_quote fq ON q.id = fq.quote_id','LEFT')
                             ->field($fields)
                             ->where(['q.inquiry_id'=>$request['inquiry_id']])
                             ->find();
        if (!$result){
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }

        $employee = new EmployeeModel();
        $inquiry = new InquiryModel();
        $pm_id = $inquiry->where(['id'=>$request['inquiry_id']])->getField('pm_id');
        $result['pm_name'] =  $employee->where(['id'=>$pm_id])->getField('name');

        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!',
            'data' => $result
        ]);

    }

    /**
     * @desc 保存报价综合信息(项目经理)
     */
    public function saveQuoteGeneralInfoAction(){

        $request = $this->validateRequests('inquiry_id');
        //p($request);
        $quoteModel = new QuoteModel();
        try{
            if ($quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->save($quoteModel->create($request))){
                $this->jsonReturn(['code'=>'1','message'=>'保存成功!']);
            }else{
                //p($quoteModel->getLastSql());
                $this->jsonReturn(['code'=>'-104','message'=>'失败!']);
            }
        }catch (Exception $exception){
            $this->jsonReturn([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ]);
        }

    }

    /**
     * @desc 询单信息
     * @fields 询单流程编码 询单报价状态 询单项目经理
     */
    public function inquiryInfoAction(){

        $request = $this->validateRequests('serial_no');

        //获取询单本身信息
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no'=>$request['serial_no']])->field([
            'id','serial_no','goods_quote_status','pm_id','agent_id'
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
     * @desc 暂存(产品线报价人)
     */
    public function storageQuoteAction() {

        $request = $this->_requestParams['data'];

        $quoteBizline = new QuoteBizLineModel();
        $response = $quoteBizline->storageQuote($request,$this->user['id']);

        $this->jsonReturn($response);

    }

    /**
     * @desc 暂存(产品线负责人)
     */
    public function managerStoreQuoteAction(){

        $request = $this->_requestParams['data'];
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
