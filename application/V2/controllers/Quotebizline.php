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
        //parent::init();

        $this->_quoteBizLine = new QuoteBizLineModel();

        $this->_quoteItemBizLine = new QuoteItemBizLineModel();

        $this->_requestParams = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * @desc 产品线报价列表接口
     */
    public function listAction() {

        $inquryList = $this->getListHandler($this->_requestParams);
        if ($inquryList['code'] !='1'){
            $this->jsonReturn($inquryList);
        }
        //显示市场经办人，项目经理
        $response = $this->restoreListHandler($inquryList);
        $this->jsonReturn($response);
    }

    /**
     * 根据条件获取数据
     * @param $request 前段提交的条件
     * @return array 获取的数据
     */
    private function getListHandler($request){

        $where = $this->getListCondition($request);

        $page = !empty($request['currentPage']) ? $request['currentPage'] : 1;
        $pageSize = !empty($request['pageSize']) ? $request['pageSize'] : 10;

        $inquiry = new InquiryModel();
        try{
            $total = $inquiry->getCount($where);
            $field = ['id','serial_no','country_bn','buyer_name','created_at','status','quote_deadline','agent_id','pm_id'];
            $list = $inquiry->where($where)->page($page,$pageSize)->field($field)->order('updated_at desc')->select();
            if (!$list){
                return ['code'=>'-104','message'=>'没有数据！','data'=>''];
            }
            return [
                'code' => '1',
                'message' => '成功！',
                'total' => $total,
                'data' => $list
            ];
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 重组数组
     * @param $list
     * @return mixed
     */
    private function restoreListHandler($list){
        foreach ($list['data'] as $item=>$value) {
            //经办人
            if(!empty($value['agent_id'])){
                $employee = Z('Employee')->where(['id'=>$value['agent_id']])->field('name')->find();
                $list['data'][$item]['agent_name'] = $employee['name'];
            }
            //项目经理
            if(!empty($val['pm_id'])){
                $productManager = Z('Employee')->where(['id'=>$value['pm_id']])->field('name')->find();
                $list['data'][$item]['pm_name'] = $productManager['name'];
            }
        }
        return $list;
    }

    /**
     * 重组查询条件
     * @param $request 查询条件
     * @return array 重组后的条件
     */
    private function getListCondition($request){

        $where = [];
        //Z函数实例化一个不存在模型文件的模型
        $employee = Z('Employee');

        //市场经办人
        if (!empty($request['agent_name'])){
            $agenter = $employee->field('id')->where(['name'=>$request['agent_name']])->find();
            if ($agenter){
                $where['agent_id'] = intval($agenter['id']);
            }
        }
        //项目经理
        if (!empty($request['pm_name'])){
            $projectManager = $employee->field('id')->where(['name'=>$request['pm_name']])->find();
            if ($projectManager){
                $where['pm_id'] = $projectManager['id'];
            }
        }
        //项目状态
        if (!empty($request['status'])) {
            $where['status'] = $request['status'];
        }
        //国家
        if (!empty($request['country_bn'])) {
            $where['country_bn'] = $request['country_bn'];
        }
        //流程编码
        if (!empty($request['serial_no'])) {
            $where['serial_no'] = $request['serial_no'];
        }
        //客户名称
        if (!empty($request['buyer_name'])) {
            $where['buyer_name'] = $request['buyer_name'];
        }
        //询价时间
        if (!empty($request['start_time']) && !empty($request['end_time'])) {
            $where['created_at'] = array(
                array('gt',date('Y-m-d H:i:s',$request['start_time'])),
                array('lt',date('Y-m-d H:i:s',$request['end_time']))
            );
        }
        //删除状态
        $where['deleted_flag'] = !empty($request['deleted_flag']) ? $request['deleted_flag'] : 'N';

        return $where;
    }
    /**
     * @desc 详情页询单信息接口(只读)
     */
    public function inquiryInfoAction() {

        $response = $this->inquryInfoHandler($this->_requestParams);
        $this->jsonReturn($response);

    }

    private function inquryInfoHandler($request){
        //获取询单本身信息
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no'=>$request['serial_no']])->field(QuoteBizlineHelper::getInquiryInfoFields())->find();

        if (!$inquiryInfo){
            return ['code'=>'-104','message'=>'没有询单信息','data'=>''];
        }
        //重组询单信息数组
        $inquiry = QuoteBizlineHelper::restoreInqiryInfo($inquiryInfo);

        return $response = [
            'code' => '1',
            'message' => '成功!',
            'data' => $inquiry
        ];
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
     * @desc 报价办理接口
     */
    public function manageAction() {
          /*
          |--------------------------------------------------------------------------
          | 报价办理接口（读取询单信息）
          |--------------------------------------------------------------------------
          |
          | 操作说明
          | 提交暂存后，不做校验，市场的进度为待提交
          |
         */

    }

    private function manageHandler(){

    }

    /**
     * @desc 报价办理->暂存接口
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
                'code' => -104,
                'message' => '失败!',
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功!'
        ]);
    }

    /**
     * @desc 报价办理->查看审核信息接口
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

    /*
      |--------------------------------------------------------------------------
      | 产品线报价->附件信息(上传附件)   角色:产品线负责人
      |--------------------------------------------------------------------------
      | 说明：
      | 1、当前环节且本人上传，可删除
      | 2、A环节，上传了附件，提交出去后，再流转回来，不能删除之前上传的附件
      | 3、附件排序：按时间顺序正序排列
      | 4.点击附件名称可以下载附件
      |
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

    /*
      |--------------------------------------------------------------------------
      | 产品线报价->附件信息(上传附件)   角色:产品线负责人
      |--------------------------------------------------------------------------
      | 说明：
      | 1、当前环节且本人上传，可删除
      | 2、A环节，上传了附件，提交出去后，再流转回来，不能删除之前上传的附件
      | 3、附件排序：按时间顺序正序排列
      | 4.点击附件名称可以下载附件
      |
     */

    public function addAttach() {
        $requestData = $this->_requestParams;

        $quoteAttach = new QuoteAttachModel();
        $result = $quoteAttach->add([
            'quote_id' => $requestData['quote_id'],
            'attach_group' => isset($requestData['attach_group']) ? $requestData['attach_group'] : '',
            'attach_type' => isset($requestData['attach_type']) ? $requestData['attach_type'] : '',
            'attach_name' => isset($requestData['attach_name']) ? $requestData['attach_name'] : '',
            'attach_url' => $requestData['attach_url'],
            'status' => 'VALID',
            //TODO 这里获取当前用户？
            'created_by' => $requestData['created_by'],
        ]);

        if ($result) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '成功'
            ]);
        }
    }

    /*
      |--------------------------------------------------------------------------
      | 产品线报价->退回报价   角色:产品线负责人
      |--------------------------------------------------------------------------
      |
      | 操作说明
      | 退回报价：全部SKU改为 被驳回状态 只有全部SKU都是“已报价”状态，才能退回
      |
     */

    public function sendbackAction() {
        //1.更改当前的报价状态为被退回
        $sendBackQuote = $this->_quoteBizLine->sendback($this->_requestParams['quote_id']);

        //2.更改该报价所属的sku状态为被驳回状态
        $sendBackQuoteSku = $this->_quoteItemBizLine->sendback($this->_requestParams['quote_id']);

        if ($sendBackQuote && $sendBackQuoteSku) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '成功'
            ]);
        }

        $this->jsonReturn([
            'code' => -101,
            'message' => '失败！'
        ]);
    }

    /*
      |--------------------------------------------------------------------------
      | 产品线报价->指派报价人   角色:产品线负责人
      |--------------------------------------------------------------------------
      |
      | 操作说明
      | 产品线报价
      |
     */

    public function assignAction() {
        echo 23456789;
    }

    /*
      |--------------------------------------------------------------------------
      | 产品线报价->提交项目经理审核   角色:产品线负责人
      |--------------------------------------------------------------------------
      |
      | 操作说明
      | 项目状态:项目经理审核
      | 把当前项目(询单)的状态改为项目经理审核
     */

    public function submitToManagerAction() {
        $inquiry = new InquiryModel();

        $result = $inquiry->where(['inquiry_no' => $this->_requestParams['inquiry_no']])->save([
            'status' => 'DRAFT',
            'goods_quote_status' => 'NOT_QUOTED'
        ]);

        if ($result) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '提交成功!'
            ]);
        }

        $this->jsonReturn([
            'code' => -101,
            'message' => '提交失败!'
        ]);
    }

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

    /**
     * 选择供应商
     */
    public function supplierAction() {
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

    /*
      |--------------------------------------------------------------------------
      | 产品线报价->暂存   角色:产品线报价人
      |--------------------------------------------------------------------------
      | 操作说明
      | 点击暂存后，不做校验，市场的进度为待提交
      | 当前报价单状态改为待提交  [quote_bizlie表]
      |
     */

    public function quoterStorageAction() {
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

    /*
      |--------------------------------------------------------------------------
      | 产品线报价->提交产品线负责人审核   角色:产品线报价人
      |--------------------------------------------------------------------------
      | 操作说明
      | 点击暂存后，不做校验，市场的进度为待提交
      | 当前报价单状态改为待提交  [quote_bizlie表]
      |
     */

    public function submitToBizlineManagerAction() {

        //判断参数是否正确
        if (empty($this->_requestParams['quote_id']) || empty($this->_requestParams['bizline_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }

        //保存数据及更改状态
        $quoteBizline = new QuoteBizLineModel();
        $this->jsonReturn($quoteBizline->submitToBizlineManager($this->_requestParams));

    }

    /**
     * 产品线报价->项目经理->划分产品线
     */
    public function partitionBizlineAction(){
        $response = $this->partitionBizlineHandler($this->_requestParams);
        $this->jsonReturn($response);
    }

    /**
     * 执行产品线报价划分产品线业务
     * @param $param 参数
     * @return array 结果
     */
    private function partitionBizlineHandler($param){
        //重组参数，并准备插入到quote_bizline表
        $data = QuoteBizlineHelper::setPartitionBizlineFields($param);
        //插入数据
        return $this->_quoteBizLine->partitionBizline($data);
    }


    /**
     * 产品线报价->项目经理->转交其他人办理
     * 操作说明:转交后，当前人员就不是项目经理了，如果也不是方案中心的人，就不能再查看这个项目了
     */
    public function transmitAction(){

        if (empty($this->_requestParams['inquiry_id']) || empty($this->_requestParams['pm_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $response = QuoteBizlineHelper::transmitHandler($this->_requestParams);
        $this->jsonReturn($response);

    }

    /**
     * 产品线报价->项目经理->提交产品线报价
     * 操作说明:当前询单的状态改为产品线报价
     */
    public function submitToBizlineAction(){

        if (empty($this->_requestParams['inquiry_ids'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $response = QuoteBizlineHelper::submitToBizline($this->_requestParams);
        $this->jsonReturn($response);

    }

    /**
     * 产品线报价->项目经理->退回产品线重新报价
     * 操作说明:当前询单的状态改为(退回)---待定
     */
    public function sendbackToBizlineAction(){

        if (empty($this->_requestParams['inquiry_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }

        $this->jsonReturn(QuoteBizlineHelper::sendbackToBizline($this->_requestParams));

    }


    /**
     * 产品线报价->项目经理->提交物流报价
     */
    public function submitToLogiAction()
    {

        if (empty($this->_requestParams['inquiry_id'])){
            $this->jsonReturn(['code'=>'-104','message'=>'缺少参数!']);
        }
        $this->jsonReturn(QuoteBizlineHelper::submitToLogi($this->_requestParams));

    }

}
