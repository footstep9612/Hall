<?php

/**
 * 产品线报价
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
     * 用户权限验证
     * @return array
     */
    public function validateAuth(){

        if (!isset($this->user['group_id']) && empty($this->user['group_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'没有权限']);
        }
        $groupid = $this->user['group_id'];
        $bizlinegroup = new BizlineGroupModel();

        $data = $bizlinegroup->where('group_id IN('.implode(",",$groupid).') and group_role in("SKU_QUOTER","BIZLINE_MANAGER") ')->select();
        if (!$data){
            $this->jsonReturn(['code'=>'-104','message'=>'没有权限']);
        }

        $bizlineids = [];
        $grouprole = [];
        foreach ($data as $key=>$val){
            $bizlineids[] = $val['bizline_id'];

            if($val['group_role']=='SKU_QUOTER'){
                $grouprole['SKU_QUOTER'] = 'SKU_QUOTER';
            }

            if($val['group_role']=='BIZLINE_MANAGER'){
                $grouprole['BIZLINE_MANAGER'] = 'BIZLINE_MANAGER';
            }

        }

        $bizlineids = array_unique($bizlineids);

        $results['bizlineid'] = $bizlineids;
        $results['grouprole'] = $grouprole;

        return $results;

    }

    /**
     * 产品线报价列表(项目经理)
     */
    public function bizlineListAction(){

        $condition = $this->validateRequests();

        $user = new EmployeeModel();
        if (!empty($condition['agent_name'])) {
            $agent = $user->where(['name' => $condition['agent_name']])->find();
            $condition['agent_id'] = $agent['id'];
        }

        //项目经理 列表
        $condition['pm_id'] = $this->user['id'];

        $quoteBizlineList = QuoteHelper::getPmQuoteBizlineList($condition);

        $country = new CountryModel();

        foreach ($quoteBizlineList as &$quoteBizline) {
            $quoteBizline['agent_name'] = $user->where(['id'=>$quoteBizline['agent_id']])->getField('name');
            $quoteBizline['pm_name'] = $user->where(['id'=>$quoteBizline['pm_id']])->getField('name');
            //国家
            if (!empty($quoteBizline['country_bn'])) {
                $countryInfo = $country->field('name')->where("lang='zh' and bn='" . $quoteBizline['country_bn'] . "'")->find();
                $quoteBizline['country_bn'] = $countryInfo['name'];
            }
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
     * 报价单sku列表(项目经理)
     */
    public function quoteSkuListAction(){

        $request = $this->validateRequests('id');

        $quoteSkuList = QuoteHelper::getQuoteList($request);

        if ($quoteSkuList){
            $quoteitemform = new QuoteItemFormModel();
            //关联产品线，查询已经关联的产品线名称
            foreach($quoteSkuList  as $key=>$val){
                $bizline = $quoteitemform->alias('a')
                    ->field('c.id,c.name')
                    ->join('erui2_rfq.quote_bizline b ON a.quote_bizline_id = b.id')
                    ->join('erui2_operation.bizline c ON b.bizline_id = c.id')
                    ->where('a.inquiry_item_id = '.$val['id'])
                    ->find();

                if(isset($bizline)){
                    $quoteSkuList[$key]['bizline_id'] = $bizline['id'];
                    $quoteSkuList[$key]['bizline_name'] = $bizline['name'];
                }
            }

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
     * 项目经理报价sku列表(新增)
     */
    public function pmQuoteListAction(){

        $request = $this->validateRequests('inquiry_id');

        $quoteBizline =  new QuoteBizLineModel();
        $response = $quoteBizline->getPmQuoteList($request);
        //p($response);
        if (!$response){
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }

        $bizline = new BizlineModel();
        $user = new EmployeeModel();
       $supplier = new SupplierModel();

        foreach ($response as $k=>$v){
            $response[$k]['bizline_name'] = $bizline->where(['id'=>$v['bizline_id']])->getField('name');
            $response[$k]['bizline_agent_name'] = $user->where(['id'=>$v['bizline_agent_id']])->getField('name');
            $response[$k]['supplier_name'] = $supplier->where(['id'=>$v['supplier_id']])->getField('name');
            $response[$k]['quoter_name'] = $user->where(['id'=>$v['created_by']])->getField('name');
        }
        //p($response);
        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!',
            'total' => $quoteBizline->getPmQuoteListCount($request),
            'data' => $response
        ]);
    }

    /**
     * 划分产品线(项目经理)
     */
    public function partitionBizlineAction(){

        $request = $this->validateRequests('inquiry_id,buyer_id,serial_no,inquiry_item_id,bizline_id');

        //1.创建一条报价记录(quote)
        $quoteModel = new QuoteModel();
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['id'=>$request['inquiry_id']])->find();
        $quoteModel->startTrans();

        $isEx = $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->getField('id');

        if (!$isEx){
            $quoteResult = $quoteModel->add($quoteModel->create([
                'buyer_id' => $request['buyer_id'],
                'inquiry_id' => $request['inquiry_id'],
                'serial_no' => $request['serial_no'],
                'quote_no' => $this->getQuoteNo(),
                'quote_lang' => 'zh',
                'created_by' => $this->user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]));
        }else{
            $quoteResult = $isEx;
        }
        // 判断产品线报价记录是否存在
        $quoteBizlineModel = new QuoteBizLineModel();
        $quoteBizlineInfo = $quoteBizlineModel->where(['inquiry_id' => $request['inquiry_id'], 'bizline_id' => $request['bizline_id']])->find();
        
        if ($quoteBizlineInfo) {
            $quoteBizlineId = $quoteBizlineInfo['id'];
        } else {
            //2.创建一条产品线报价记录(quote_bizline)
            $quoteBizlineModel->startTrans();
            $quoteBizlineResult = $quoteBizlineModel->add($quoteBizlineModel->create([
                'quote_id' => $quoteResult,
                'inquiry_id' => $request['inquiry_id'],
                'bizline_id' => $request['bizline_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->user['id'],
            ]));
            
            $quoteBizlineId = $quoteBizlineResult;
        }

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
                'quote_unit' => $item['unit'],
                'quote_qty' => $item['qty'],
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }

        //4选择的讯单项(inquiry_item)写入到产品线报价单项(quote_item_form)
        $quote_item_form_list = $quoteItemModel->where('id IN('.implode(",",$quote_item_ids).')')->field('quote_id,id,inquiry_item_id,bizline_id,sku')->select();

        $quoteItemFormModel = new QuoteItemFormModel();

        //判断是否重复
        $inquiry_item_ids = explode(',',$request['inquiry_item_id']);
        foreach ($inquiry_item_ids as $inquiry_item_id){
            $isHave = $quoteItemFormModel->where(['inquiry_item_id'=>$inquiry_item_id])->find();
            if ($isHave){
                //回归事务
                $quoteModel->rollback();
                $quoteBizlineModel->rollback();
                $quoteItemModel->rollback();

                $this->jsonReturn([
                    'code' => '-104',
                    'message' => '已经划分了产品线!'
                ]);
            }
        }

        $quote_item_form_ids = [];
        foreach ($quote_item_form_list as $v){
            $quote_item_form_ids[] = $quoteItemFormModel->add($quoteItemFormModel->create([
                'quote_id' => $v['quote_id'],
                'quote_item_id' => $v['id'],
                'inquiry_item_id' => $v['inquiry_item_id'],
                'quote_bizline_id' => $quoteBizlineId,
                'sku' => $v['sku'],
                'created_at' => date('Y-m-d H:i:s'),
            ]));
        }

        if (isset($quoteBizlineResult)) {
            if ($quoteResult && $quoteBizlineResult && $quote_item_ids && $quote_item_form_ids){
                $quoteModel->commit();
                $quoteBizlineModel->commit();
                $quoteItemModel->commit();
                $quoteItemFormModel->commit();
                $this->jsonReturn(['code'=>'1','message'=>'划分产品线成功!']);
            }else{
                $quoteModel->rollback();
                $quoteBizlineModel->rollback();
                $quoteItemModel->rollback();
                $quoteItemFormModel->rollback();
                $this->jsonReturn(['code'=>'-104','message'=>'划分产品线失败!']);
            }
        } else {
            if ($quoteResult && $quote_item_ids && $quote_item_form_ids){
                $quoteModel->commit();
                $quoteItemModel->commit();
                $quoteItemFormModel->commit();
                $this->jsonReturn(['code'=>'1','message'=>'划分产品线成功!']);
            }else{
                $quoteModel->rollback();
                $quoteItemModel->rollback();
                $quoteItemFormModel->rollback();
                $this->jsonReturn(['code'=>'-104','message'=>'划分产品线失败!']);
            }
        }

    }

    /**
     * 转交其他人办理(项目经理)
     * 操作说明:转交后，当前人员就不是项目经理了，如果也不是方案中心的人，就不能再查看这个项目了
     */
    public function transmitAction(){

        $request = $this->validateRequests('id,pm_id');
        $response = QuoteBizlineHelper::transmitHandler($request,$this->user['id']);
        $this->jsonReturn($response);

    }

    /**
     * 提交产品线报价(项目经理)
     * 操作说明:当前询单的状态改为产品线报价中
     */
    public function submitToBizlineAction(){

        $request = $this->validateRequests('id');
        $this->jsonReturn(QuoteBizlineHelper::submitToBizline($request));

    }

    /**
     * 产品线报价列表(产品线负责人)
     */
    public function bizlineManagerQuoteListAction(){

        $condition = $this->validateRequests();

        $auth = $this->validateAuth();

        foreach ($auth['bizlineid'] as $val){
            $condition['bizline_id'][] = $val;
        }

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

        $country = new CountryModel();
        foreach ($quoteBizlineList as &$quoteBizline) {
            $quoteBizline['agent_name'] = $user->where(['id'=>$quoteBizline['agent_id']])->getField('name');
            $quoteBizline['pm_name'] = $user->where(['id'=>$quoteBizline['pm_id']])->getField('name');
            //国家
            if (!empty($quoteBizline['country_bn'])) {
                $countryInfo = $country->field('name')->where("lang='zh' and bn='" . $quoteBizline['country_bn'] . "'")->find();
                $quoteBizline['country_bn'] = $countryInfo['name'];
            }
        }

        if ($quoteBizlineList) {
            $this->jsonReturn([
                'code' => '1',
                'message' => '成功!',
                'count' => QuoteHelper::bizlineManagerQuoteListCount($condition),
                'role' => $auth['grouprole'],
                'data' => $quoteBizlineList
            ]);
        } else {
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }

    }

    /**
     * 报价sku列表(产品线负责人)
     */
    public function bizlineManagerQuoteSkuListAction(){

        $request = $this->validateRequests('quote_id,quote_bizline_id,bizline_id');
        $skuList = QuoteHelper::bizlineManagerQuoteSkuList($request);

        if ($skuList){
            $user = new EmployeeModel();
            $supplier = new SupplierModel();
            $quoteItemForm = new QuoteItemFormModel();
            foreach ($skuList as $key=>$bizlineQuoteSku) {
                $skuList[$key]['supplier_name'] = $supplier->where(['id'=>$bizlineQuoteSku['supplier_id']])->getField('name');
                $skuList[$key]['created_by'] = $user->where(['id'=>$bizlineQuoteSku['bizline_agent_id']])->getField('name');
                //已经报价供应商数量(也就是说quote_item_form对应的记录)
                $skuList[$key]['supplier_count'] = $quoteItemForm->where(['quote_item_id'=>$bizlineQuoteSku['id'],'status'=>'QUOTED'])->count('id');
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
     * 报价人sku列表
     */
    public function bizlineQuoterSkuListAction(){

        $request = $this->validateRequests('quote_bizline_id');

        $quoteItemForm = new QuoteItemFormModel();
        $quoterSkuList = $quoteItemForm->getSkuList($request,$this->user['id']);

        if (!$quoterSkuList){
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }else{
            $list = [];
            $sku = [];
            foreach ($quoterSkuList as $k=>$v){
                if ($v['status'] =='NOT_QUOTED'){
                    if(!in_array($v['sku'],$sku)){
                        $list[] = $v;
                    }
                }else{
                    $sku[] = $v['sku'];
                    $list[] = $v;
                }
            }
            $quoterSkuList = $list;
        }

        $user = new EmployeeModel();
        $supplier = new SupplierModel();

        foreach ($quoterSkuList as $key=>$value){
            $quoterSkuList[$key]['created_by'] = $user->where(['id'=>$value['updated_by']])->getField('name');
            $quoterSkuList[$key]['supplier_name'] = $supplier->where(['id'=>$value['supplier_id']])->getField('name');
        }

        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!',
            'count' => $quoteItemForm->getSkuListCount($request,$this->user['id']),
            'data' => $quoterSkuList
        ]);
    }

    /**
     * 上传附件(项目经理)
     */
    public function addAttachAction() {

        $request = $this->validateRequests('quote_id,attach_url');
        $request['created_by'] = $this->user['id'];
        $this->jsonReturn(QuoteBizlineHelper::addAttach($request));

    }

    /**
     * 附件列表
     */
    public function attachListAction() {

        $request = $this->validateRequests('quote_id');

        $quoteAttach = new QuoteAttachModel();
        $attachList = $quoteAttach->where(['quote_id' => $request['quote_id']])->order('created_at desc')->select();

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
     * 退回产品线重新报价(项目经理)
     */
    public function rejectBizlineAction(){

        $request = $this->validateRequests('inquiry_id,op_note');

        $quote = new QuoteModel();
        $request['quote_id'] = $quote->where(['inquiry_id'=>$request['inquiry_id']])->getField('id');

        $quoteitem = new QuoteItemModel();
        $quotebizline = new QuoteBizLineModel();
        $quoteitemform = new QuoteItemFormModel();

        //查找报价单全部SKU id
        $ids = $quoteitem->where('quote_id='.$request['quote_id'])->getField('id',true);
        //p($ids);
        $itemformwhere['quote_item_id'] = array('in',$ids);
        //事物开始
        $quoteitemform->startTrans();
        $upitemform = $quoteitemform->where($itemformwhere)->save(['status' => 'REJECTED']);

        $inquiry = new InquiryModel();

        if($upitemform){

            $upquotetatus = $quote->where('id='.$request['quote_id'])->save(['status' => 'BZ_QUOTE_REJECTED']);//修改报价单状态
            $upbizlinestatus = $quotebizline->where('quote_id='.$request['quote_id'])->save(['status' => 'REJECTED']);//修改产品线报价状态
            $inquiryStatus = $inquiry->where(['id'=>$request['inquiry_id']])->save(['status'=>'BZ_QUOTE_REJECTED']);
            //写入审核日志
            $inquiryCheckLog = new InquiryCheckLogModel();
            $inquiryCheckLogResult = $inquiryCheckLog->add($inquiryCheckLog->create([
                'op_id' => $this->user['id'],
                'inquiry_id' => $request['inquiry_id'],
                'quote_id' => $request['quote_id'],
                'category' => 'PM',
                'action' => 'APPROVING',
                'op_note' => $request['op_note'],
                'op_result' => 'REJECTED',
                'created_by' => $this->user['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]));

            if($upquotetatus && $upbizlinestatus && $inquiryStatus && $inquiryCheckLogResult){
                $quoteitemform->commit();
                $result['code'] = '1';
                $result['message'] = '成功!';
            }else{
                $quoteitemform->rollback();
                $result['code'] = '-101';
                $result['message'] = '退回产品线失败!';
            }
        }else{
            $quoteitemform->rollback();
            $result['code'] = '-101';
            $result['message'] = '返回产品线失败!';
        }

        $this->jsonReturn($result);
    }

    /**
     * 提交物流报价(项目经理)
     */
    public function sentLogisticsAction(){

        $request = $this->validateRequests('inquiry_id');
        $quoteBizline = new QuoteBizLineModel();
        $response = $quoteBizline->sentLogistics($request,$this->user['id']);
        $this->jsonReturn($response);

    }

    /**
     * 退回物流重新报价
     */
    public function rejectLogisticAction(){

        $request = $this->validateRequests('inquiry_id,op_note');

        //修改项目状态
        $inquiry =  new InquiryModel();
        $inquiry->startTrans();
        $inquiryResult = $inquiry->where(['id'=>$request['inquiry_id']])->save([
            'status' => 'LOGI_QUOTE_REJECTED',
            'logi_quote_status' => 'REJECTED'
        ]);

        //修改报价的状态
        $quoteModel = new QuoteModel();
        $quoteModel->startTrans();
        $quoteResult = $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->save([
            'status' => 'LOGI_QUOTE_REJECTED'
        ]);

        //修改物流表的状态
        $quoteLogiFee = new QuoteLogiFeeModel();
        $quoteLogiFee->startTrans();
        $quoteLogiFeeResult = $quoteLogiFee->where(['inquiry_id'=>$request['inquiry_id']])->save([
            'status' => 'REJECTED'
        ]);

        //写审核日志
        $inquiryCheckLog = new InquiryCheckLogModel();
        $inquiryCheckLog->startTrans();
        $checkInfo = [
            'op_id' => $this->user['id'],
            'created_by' => $this->user['id'],
            'created_at' => date('Y-m-d H:i:s'),
            'inquiry_id' => $request['inquiry_id'],
            'quote_id' => $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->getField('id'),
            'category' => 'PM',
            'action' => 'APPROVING',
            'op_note' => $request['op_note'],
            'op_result' => 'REJECTED'
        ];

        $checklogResult = $inquiryCheckLog->add($inquiryCheckLog->create($checkInfo));

        if ($inquiryResult && $quoteResult && $checklogResult && $quoteLogiFeeResult){
            $inquiry->commit();
            $quoteModel->commit();
            $inquiryCheckLog->commit();
            $quoteLogiFee->commit();
            $this->jsonReturn(['code'=>'1','message'=>'退回成功!']);
        }else{
            $inquiry->rollback();
            $quoteModel->rollback();
            $inquiryCheckLog->rollback();
            $quoteLogiFee->rollback();
            $this->jsonReturn(['code'=>'-104','message'=>'退回失败!']);
        }

    }

    /**
     * 提交市场确认报价(项目经理)
     */
    public function sentMarketAction(){

        $request = $this->validateRequests('inquiry_id');

        $inquiry = new InquiryModel();
        $inquiry->startTrans();
        $inquiryResult = $inquiry->where([
            'id' => $request['inquiry_id']
        ])->save([
            'status' => QuoteBizLineModel::INQUIRY_APPROVED_BY_PM,
            //'logi_quote_status' => QuoteBizLineModel::QUOTE_APPROVED
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
                'created_at' => date('Y-m-d H:i:s'),
                'payment_period' => $quoteData['payment_period'],
                'delivery_period' => $quoteData['delivery_period'],
                'fund_occupation_rate' => $quoteData['fund_occupation_rate'],
                'total_purchase' => $quoteData['total_purchase'],
                'purchase_cur_bn' => $quoteData['purchase_cur_bn'],
                'total_logi_fee' => $quoteData['total_logi_fee'],
                'total_exw_price' => $quoteData['total_exw_price'],
                'total_quote_price' => $quoteData['total_quote_price'],
                'total_bank_fee' => $quoteData['total_bank_fee'],
                'total_insu_fee' => $quoteData['total_insu_fee'],
                'status' => 'APPROVED_BY_PM'
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
     * 产品线负责人上传附件(产品线负责人)
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
     * 指派报价人(产品线负责人)
     */
    public function assignQuoterAction() {

        //TODO 更改参数quote_item_form_id为quote_item_id
        $request = $this->validateRequests('quote_id,quote_item_id,biz_agent_id,bizline_id');

        $quoteItemModel = new QuoteItemModel();
        $quoteItemFormModel = new QuoteItemFormModel();
        $quoteBizline = new QuoteBizLineModel();

        $quoteItemInfo = $quoteItemModel->where(['quote_id'=>$request['quote_id'],'bizline_id'=>$request['bizline_id']])->find();
        $quoteBizlineInfo = $quoteBizline->where(['quote_id'=>$request['quote_id'],'bizline_id'=>$request['bizline_id']])->find();

        //当前报价单项没有报价人则把当前报价单项指派给新选择的人
        $quote_item_form_ids = explode(',',$request['quote_item_id']);

        try{
            foreach ($quote_item_form_ids as $quoteFormItem){

                $isAssigned = $quoteItemFormModel->where([
                    'quote_id' => $request['quote_id'],
                    'quote_item_id' => $quoteFormItem,
                    'sku' => $quoteItemInfo['sku'],
                    'updated_by' => $request['biz_agent_id'],
                ])->count();

                //是否已被指派
                if ($isAssigned){
                    $this->jsonReturn(['code' => '-104','message' => '不能重复指派!']);
                }


                $quoteItemFormModel->add($quoteItemFormModel->create([
                    'quote_id' => $request['quote_id'],
                    'quote_item_id' => $quoteFormItem,
                    'inquiry_item_id' => $quoteItemInfo['inquiry_item_id'],
                    'quote_bizline_id' => $quoteBizlineInfo['id'],
                    'sku' => $quoteItemInfo['sku'],
                    'created_by' => $request['biz_agent_id'],
                    'updated_by' => $request['biz_agent_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]));

            }

            $this->jsonReturn(['code'=>'1','message'=>'指派成功!']);

        }catch (Exception $exception){
            $this->jsonReturn([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);
        }

    }

    /**
     * 选择报价(产品线负责人)
     */
    public function selectQuoteAction(){

        $request = $this->validateRequests('quote_item_id');

        $quoteItemForm = new QuoteItemFormModel();
        $response = $quoteItemForm->getList($request);

        if (!$response){
            $this->jsonReturn(['code'=>'-104','message'=>'暂没有数据!']);
        }

        $user = new EmployeeModel();
        $supplier = new SupplierModel();
        $quoteItem = new QuoteItemModel();

        //可以选的供应商id
        $supplier_id = $quoteItem->where(['id'=>$request['quote_item_id']])->getField('supplier_id');

        foreach ($response as $key=>$value){
            if (!empty($value['updated_by'])){
                $response[$key]['created_by'] = $user->where(['id'=>$value['updated_by']])->getField('name');
                //是否被指派
                $response[$key]['is_assign'] = 'Y';
            }else{
                $response[$key]['is_assign'] = 'N';
            }

            $response[$key]['supplier_name'] = $supplier->where(['id'=>$value['supplier_id']])->getField('name');

            $response[$key]['is_selected'] = $supplier_id==$value['supplier_id'] ? 'Y' : 'N';

        }

        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!',
            'data' => $response
        ]);
    }

    /**
     * 退回报价(产品线负责人)
     */
    public function bizlineManagerRejectQuoteAction() {

        $request = $this->validateRequests('inquiry_id,quote_id,op_note');
        $request['user_id'] = $this->user['id'];

        $quoteBizline = new QuoteBizLineModel();
        $response = $quoteBizline->bizlineManagerRejectQuote($request);
        $this->jsonReturn($response);

    }

    /**
     * 提交项目经理审核(产品线负责人)
     */
    public function sentToManagerAction() {

        $request = $this->validateRequests('quote_id,quote_bizline_id,serial_no');
        $response = QuoteBizlineHelper::submitToManager($request);
        $this->jsonReturn($response);

    }

    /**
     * 提交产品线负责人审核(产品线报价人)
     */
    public function sentToBizlineManagerAction() {

        $request = $this->validateRequests('quote_bizline_id');

        $quoteBizline = new QuoteBizLineModel();
        $response = $quoteBizline->submitToBizlineManager($request);
        $this->jsonReturn($response);

    }

    /**
     * 获取综合报价信息(项目经理)
     */
    public function quoteGeneralInfoAction(){

        $request = $this->validateRequests('inquiry_id');

        $fields = 'q.id,q.total_weight,a.trans_mode_bn,q.premium_rate,q.package_volumn,a.dispatch_place,a.delivery_addr,q.dispatch_place logi_dispatch_place,q.delivery_addr logi_delivery_addr,q.gross_profit_rate,q.total_purchase,q.purchase_cur_bn,q.package_mode,q.payment_mode,q.trade_terms_bn,q.payment_period,q.from_country,q.to_country,q.from_port,q.to_port,q.delivery_period,q.fund_occupation_rate,q.bank_interest,q.total_bank_fee,q.period_of_validity,q.exchange_rate,q.total_logi_fee,q.total_quote_price,q.total_exw_price,fq.total_quote_price final_total_quote_price,fq.total_exw_price final_total_exw_price';
        $quoteModel = new QuoteModel();
        $result = $quoteModel->alias('q')
                             ->join('erui2_rfq.inquiry a ON q.inquiry_id = a.id','LEFT')
                             ->join('erui2_rfq.final_quote fq ON q.id = fq.quote_id','LEFT')
                             ->field($fields)
                             ->where(['q.inquiry_id'=>$request['inquiry_id']])
                             ->find();
        if (!$result){
            $this->jsonReturn(['code'=>'-104','message'=>'没有数据!']);
        }

        $employee = new EmployeeModel();
        $inquiry = new InquiryModel();
        $exchange_rate_model = new ExchangeRateModel();

        $pm_id = $inquiry->where(['id'=>$request['inquiry_id']])->getField('pm_id');
        $result['pm_name'] =  $employee->where(['id'=>$pm_id])->getField('name');
        $result['exchange_rate'] = $exchange_rate_model->where(['cur_bn1'=>'CNY','cur_bn2'=>'USD'])->getField('rate');
        //运输方式
        $transMode = new TransModeModel();
        $result['trans_mode_bn'] = $transMode->where(['id'=>$result['trans_mode_bn']])->getField('trans_mode');

        $this->jsonReturn([
            'code' => '1',
            'message' => '成功!',
            'data' => $result
        ]);

    }

    /**
     * 保存报价综合信息(项目经理)
     */
    public function saveQuoteGeneralInfoAction(){

        $request = $this->validateRequests('inquiry_id');
        unset($request['id']);//过滤前端发送的多余id字段

        //总重
        if (!empty($request['total_weight']) && !is_numeric($request['total_weight'])){
            $this->jsonReturn(['code'=>'-104','message'=>'总重必须是数字']);
        }
        //包装总体积
        if (!empty($request['package_volumn']) && !is_numeric($request['package_volumn'])){
            $this->jsonReturn(['code'=>'-104','message'=>'包装总体积必须是数字']);
        }
        //回款周期
        if (!empty($request['payment_period']) && !is_numeric($request['payment_period'])){
            $this->jsonReturn(['code'=>'-104','message'=>'回款周期必须是数字']);
        }
        //交货周期
        if (!empty($request['delivery_period']) && !is_numeric($request['delivery_period'])){
            $this->jsonReturn(['code'=>'-104','message'=>'交货周期必须是数字']);
        }
        //资金占用比例
        if (!empty($request['fund_occupation_rate']) && !is_numeric($request['fund_occupation_rate'])){
            $this->jsonReturn(['code'=>'-104','message'=>'资金占用比例必须是数字']);
        }
        //银行利息
        if (!empty($request['bank_interest']) && !is_numeric($request['bank_interest'])){
            $this->jsonReturn(['code'=>'-104','message'=>'银行利息必须是数字']);
        }
        //毛利率
        if (!empty($request['gross_profit_rate']) && !is_numeric($request['gross_profit_rate'])){
            $this->jsonReturn(['code'=>'-104','message'=>'毛利率必须是数字']);
        }

        $request['created_at'] = date('Y-m-d H:i:s');
        $quoteModel = new QuoteModel();
        try{
            $result = $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->save($quoteModel->create($request));
            if ($result){

                //计算商务报出EXW单价
                $this->calculateExwUnitPrice($request['inquiry_id']);

                //计算商务报出EXW总报价&采购合计
                $this->calculateTotaleExwPrice($request['inquiry_id']);

                $this->jsonReturn(['code'=>'1','message'=>'保存成功!']);

            }else{
                $this->jsonReturn(['code'=>'1','message'=>'保存成功!']);
            }
        }catch (Exception $exception){
            $this->jsonReturn([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ]);
        }

    }

    /**
     * 采购合计(美元)
     * @param $inquiry_id
     *
     * @return float|int
     */
    private function calculateTotalPurchase($inquiry_id){

        $quoteItemModel = new QuoteItemModel();
        $exchangeRateModel = new ExchangeRateModel();

        $totalPurchase = [];
        $quoteItemsData = $quoteItemModel->where(['inquiry_id'=>$inquiry_id])->field('purchase_unit_price,purchase_price_cur_bn,quote_qty')->select();

        foreach ($quoteItemsData as $quote=>$item){
            switch ($item['purchase_price_cur_bn']){
                case 'EUR' :
                    $rate = $exchangeRateModel->where(['cur_bn1'=>'EUR','cur_bn2'=>'USD'])->getField('rate');
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'] * $rate;
                    break;
                case 'USD' :
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'];
                    break;
                case 'CNY' :
                    $rate = $exchangeRateModel->where(['cur_bn1'=>'CNY','cur_bn2'=>'USD'])->getField('rate');
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'] * $rate;
                    break;
            }
        }

        return array_sum($totalPurchase);
    }

    /**
     * 计算商务报出EXW合计
     * @param $inquiry_id 询单id
     * @return bool
     */
    private function calculateTotaleExwPrice($inquiry_id){

        $quoteItemModel = new QuoteItemModel();
        //商务报出EXW合计total_exw_price
        $quoteItemExwUnitPrices = $quoteItemModel->where(['inquiry_id'=>$inquiry_id])->field('exw_unit_price,quote_qty')->select();
        $total_exw_price = [];
        foreach ($quoteItemExwUnitPrices as $price){
            $total_exw_price[] = $price['exw_unit_price'] * $price['quote_qty'];
        }
        $total_exw_price = array_sum($total_exw_price);

        //采购总价total_purchase
        $quoteModel = new QuoteModel();
        return $quoteModel->where(['inquiry_id'=>$inquiry_id])->save([
            //exw合计
            'total_exw_price' =>$total_exw_price,
            //采购总价
            'total_purchase' =>$this->calculateTotalPurchase($inquiry_id)
        ]);

    }

    /**
     * 计算商务报出EXW单价
     * @param $inquiry_id 当前询单
     */
    private function calculateExwUnitPrice($inquiry_id){

        $quoteModel = new QuoteModel();
        $quoteInfo = $quoteModel->where(['inquiry_id'=>$inquiry_id])->field('id,gross_profit_rate,exchange_rate')->find();
        //毛利率
        $gross_profit_rate = $quoteInfo['gross_profit_rate'];

        $quoteItemModel = new QuoteItemModel();
        $exchangeRateModel = new ExchangeRateModel();

        $quoteItemIds = $quoteItemModel->where(['quote_id'=>$quoteInfo['id']])->field('id,purchase_unit_price,purchase_price_cur_bn,reason_for_no_quote')->select();
        if (!empty($quoteItemIds)){
            foreach ($quoteItemIds as $key=>$value){

                    if (empty($value['reason_for_no_quote']) && !empty($value['purchase_unit_price'])){
                        /**
                         * EXW单价=采购单价*毛利率/汇率
                         */

                        //汇率
                        $exchange_rate = $exchangeRateModel->where(['cur_bn1'=>$value['purchase_price_cur_bn'],'cur_bn2'=>'USD'])->getField('rate');
                        $exw_unit_price = $value['purchase_unit_price'] *  $gross_profit_rate / $exchange_rate ;
                        $exw_unit_price = sprintf("%.4f", $exw_unit_price);
                        $quoteItemModel->where(['id'=>$value['id']])->save([
                            'exw_unit_price' => $exw_unit_price
                        ]);

                    }
            }

        }


    }

    /**
     * 询单信息
     * 询单流程编码 询单报价状态 询单项目经理
     */
    public function inquiryInfoAction(){

        $request = $this->validateRequests('serial_no');
        //获取询单本身信息
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no'=>$request['serial_no']])->field([
            'id','serial_no','status','pm_id','agent_id'
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
     * 详情页报价信息接口
     */
    public function quoteInfoAction() {

        $request = $this->validateRequests('quote_id');
        $data = $this->_quoteBizLine->getQuoteInfo($request['quote_id']);

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
     * 暂存(产品线报价人)
     */
    public function storageQuoteAction() {

        $request = $this->_requestParams['data'];

        $quoteBizline = new QuoteBizLineModel();
        $response = $quoteBizline->storageQuote($request,$this->user['id']);
        $this->jsonReturn($response);

    }

    /**
     * 暂存(产品线负责人)
     */
    public function pmStorageQuoteAction(){

        $where = $this->validateRequests();
        $request = $where['data'];

        $user = new EmployeeModel();
        $quoteItem = new QuoteItemModel();
        try{
            $result = $quoteItem->where(['id'=>$request['id']])->save($quoteItem->create([
                'supplier_id' => $request['supplier_id'],
                'brand' => $request['brand'],
                'purchase_unit_price' => $request['purchase_unit_price'],
                'purchase_price_cur_bn' => $request['purchase_price_cur_bn'],
                'remarks' => $request['goods_desc'],
                'net_weight_kg' => $request['net_weight_kg'],
                'gross_weight_kg' => $request['gross_weight_kg'],
                'package_size' => $request['package_size'],
                'package_mode' => $request['package_mode'],
                'goods_source' => $request['goods_source'],
                'stock_loc' => $request['stock_loc'],
                'delivery_days' => $request['delivery_days'],
                'period_of_validity' => $request['period_of_validity'],
                'reason_for_no_quote' => $request['reason_for_no_quote'],
                'bizline_agent_id' => $user->where(['name'=>$request['created_by']])->getField('id'),
            ]));

            if ($result){
                $this->jsonReturn(['code'=>'1','messsage'=>'成功!']);
            }else{
                $this->jsonReturn(['code'=>'-104','messsage'=>'已经选择过了!']);
            }
        }catch(Exception $exception){
            $this->jsonReturn([
                'code' =>$exception->getCode(),
                'message' => $exception->getMessage()
            ]);
        }

    }

}
