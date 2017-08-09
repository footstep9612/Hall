<?php

trait QuoteBizlineHelper{


    /**
     * 产品线报价列表通用筛选条件
     * @param $request
     * @return array
     */
    public static function filterListParams($request,$type='PM'){

        $where = [];

        //市场经办人
        if (!empty($request['agent_name'])){
            //Z函数实例化一个不存在模型文件的模型
            $employee = Z('Employee');
            $agenter = $employee->field('id')->where(['name'=>$request['agent_name']])->find();
            if ($agenter){
                $where['agent_id'] = intval($agenter['id']);
            }
        }
        //项目经理
        if (!empty($request['pm_name'])){
            //Z函数实例化一个不存在模型文件的模型
            $employee = Z('Employee');
            $projectManager = $employee->field('id')->where(['name'=>$request['pm_name']])->find();
            if ($projectManager){
                $where['pm_id'] = $projectManager['id'];
            }
        }

        switch ($type){
            case 'PM' : $where['pm_id'] = $request['pm_id'] ; break ;
            case 'BIZLINE' : $where['agent_id'] = $request['agent_id'] ; break ;
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
        return $where;

    }

    /**
     * 根据条件获取询单信息
     * @param $filterParams 筛选字段
     * @return mixed 返回结果
     */
    public static function getQuotelineInquiryList($filterParams){

        $inquiry = new InquiryModel();
        if (!$total = $inquiry->getCount($filterParams)){
            return ['code'=>'-104','message'=>'没有数据!','data'=>''];
        }

        //设置分页
        $page = !empty($request['currentPage']) ? $request['currentPage'] : 1;
        $pageSize = !empty($request['pageSize']) ? $request['pageSize'] : 10;

        //最终列出的字段
        $field = ['id','serial_no','country_bn','buyer_name','created_at','status','quote_deadline','agent_id','pm_id'];

       $list = $inquiry->where($filterParams)->page($page,$pageSize)->field($field)->order('updated_at desc')->select();

        /**
         * 重组数据
         * 把项目经理id,市场经办人id换成人名
         */
        $responseData = self::restoreQuotelineInquiryList($list);

        return [
            'code' => '1',
            'message' => '成功!',
            'total' => $total,
            'data' => $responseData
        ];

    }

    public static function restoreQuotelineInquiryList($list){
        foreach ($list as $item=>$value) {
            //经办人
            if(!empty($value['agent_id'])){
                $employee = Z('Employee')->where(['id'=>$value['agent_id']])->field('name')->find();
                $list[$item]['agent_name'] = $employee['name'];
                unset( $list[$item]['agent_id']);
            }
            //项目经理
            if(!empty($value['pm_id'])){
                $productManager = Z('Employee')->where(['id'=>$value['pm_id']])->field('name')->find();
                $list[$item]['pm_name'] = $productManager['name'];
                unset( $list[$item]['pm_id']);
            }
        }
        return $list;
    }






    static public function getInquiryInfoFields()
    {
        return [
            'id',//询单id
            'serial_no',//流程编码
            'status',//项目状态
            'agent_id',//当前经办人,这个字段跟employee表关联获取名字
            'buyer_id',//客户编码 跟采购商表关联获取相关字段信息
            'buyer_name',//客户名称
            //所属地区
            'country_bn',//国家
            'pm_id',//项目经理 这个字段跟employee表关联获取名字
            'created_by',//询单创建人
            'inquiry_no',//项目代码
            'project_name',//项目名称
            'quote_deadline',//预计报价时间
            'bid_flag',//是否投标
            'kerui_flag',//科瑞设备所用配件
            'payment_mode',//付款方式
            'trade_terms_bn',//贸易术语
            'trans_mode_bn',//运输方式
            'cur_bn',//报价币种
            'from_country',//起运国
            'from_port',//起运港
            'dispatch_place',//发运起始地
            'to_country',//目的国
            'to_port',//目的港
            'project_basic_info',//项目背景描述
            'quote_notes',//报价备注
            'adhoc_request'//客户检验要求
        ];
    }

    static public function restoreInqiryInfo(array $inquiry){
        //市场经办人
        $agent = Z('Employee')->where(['id'=>$inquiry['agent_id']])->getField('name');
        if ($agent){
            $inquiry['agent_name'] = $agent;
            unset($inquiry['agent_id']);
        }
        //项目经理
        $productManager = Z('Employee')->where(['id'=>$inquiry['pm_id']])->getField('name');
        if ($productManager){
            $inquiry['pm_name'] = $productManager;
            unset($inquiry['pm_id']);
        }

        //获取询单明细
        $inquiryItem = new InquiryItemModel();
        $inquiryList = $inquiryItem->where(['inquiry_id'=>$inquiry['id']])->select();

        $inquiry['list'] = !is_null($inquiryList) ? $inquiryList : [] ;
        return [
            'code' => '1',
            'message' =>'成功!',
            'data' => $inquiry
        ];

    }

    /**
     * 重组划分产品线数据
     * @param $param    条件
     * @return array    重组后的结构
     */
    static public function setPartitionBizlineFields($param){

        //先查找询单相关的字段 inquiry_id biz_agent_id
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no'=>$param['serial_no']])
                                    ->field(['id','agent_id'])
                                    ->find();

        //判断一个quote_id是一个或者是多个
        $quote = explode(',',$param['quote_id']);

        //给产品线关联表插入数据 quote_bizline
        $quoteBizline = new QuoteBizLineModel();

        $data = [];
        $data['quote_id'] = $param['quote_id'];
        $data['inquiry_id'] = $param['inquiry_id'];
        $data['bizline_id'] = $param['bizline_id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        //$data['biz_agent_id'] 需要去inquiry表读取agent_id字段
        $inquiryModel = new InquiryModel();
        $data['biz_agent_id'] = $inquiryModel->where(['id'=>$param['inquiry_id']])->getField('agent_id');

        return $data;
    }

    /**
     * 项目经理转交其他人办理
     * 操作说明：根据新选择的项目经理替换掉原来的项目经理
     * @param $param
     * @return mixed
     */
    static public function transmitHandler(array $param){
        $inquiry = new InquiryModel();
        try{
            $result = $inquiry->where(['serial_no'=>$param['serial_no']])
                              ->save([
                                  'pm_id'=>$param['pm_id'],//现项目经理
                                  'ori_pm_id'=>$param['ori_pm_id']//原项目经理
                              ]);
            if ($result){
                return [
                    'code' => '1',
                    'message' => '转交成功!'
                ];
            }else{
                return [
                    'code' => '-104',
                    'message' => '转交失败!'
                ];
            }
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 产品线报价->项目经理->提交产品线报价
     * @param $param 请求数据
     * @return array 返回数据
     */
    static public function submitToBizline($param){

        $inquiryModel = new InquiryModel();

        try{
            if ($inquiryModel->where(['serial_no'=>$param['serial_no']])->save(['status'=>'QUOTING_BY_BIZLINE'])){
                return ['code'=>'1','message'=>'成功!'];
            }else{
                return ['code'=>'-104','message'=>'失败!'];
            }

        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 产品线报价->项目经理->退回产品线重新报价
     * @param $param 请求参数
     * @return array 结果
     */
    static public function sendbackToBizline($param){

        //TODO 这里处理一些其他逻辑待定
        //self::sendbackToBizlineDetail();

        $inquiry_id = $param['inquiry_id'];
        $inquiryModel = new InquiryModel();

        try{
            $result = $inquiryModel->where(['id'=>$inquiry_id])->save(['status'=>'BIZLINE_QUOTE']);
            if (!$result){
                return ['code'=>'-101','message'=>'操作失败!'];
            }
            return ['code'=>'1','message'=>'操作成功!'];
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 产品线报价->项目经理->退回产品线重新报价时候的其他逻辑
     * @param $data 参数
     * @return mixed 结果
     */
    static public function sendbackToBizlineDetail($data){
        return $data;
    }

    /**
     * 产品线负责人->指派报价人
     * @param $request 请求的数据
     * @return array 结果
     */
    static public function assignQuoter($request){

        $quote_item = new QuoteItemModel();
        try{
            if ($quote_item->where(['quote_id'=>$request['quote_id']])->save(['bizline_agent_id'=>$request['bizline_agent_id']])){
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

    //产品此案负责人->提交项目经理审核
    //数据库操作 inquriy表中的status改为QUOTED_BY_BIZLINE  goods_quote_status字段值改为QUOTED
    public static function submitToManager($request){
        $inquiry = new InquiryModel();
        try{
            $result = $inquiry->where(['serial_no'=>$request['serial_no']])->save([
                'status'=>'QUOTED_BY_BIZLINE',//询单(项目)的状态
                'goods_quote_status'=>'QUOTED'//当前报价的状态
            ]);
            if ($result){
                return ['code'=>'1','message'=>'提交成功!'];
            }else{
                return ['code'=>'-104','message'=>'失败!'];
            }
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 产品线报价->项目经理->提交物流报价
     * @param $param
     * @return array
     */
    public static function submitToLogi($param)
    {
        //更改项目(询单)状态status为QUOTING_BY_LOGI(物流报价中)
        $inquiry = new InquiryModel();
        try{
            $result = $inquiry->where(['serial_no'=>$request['serial_no']])->save(['status'=>'QUOTED_BY_BIZLINE']);
            if ($result){
                return ['code'=>'1','message'=>'提交成功!'];
            }else{
                return ['code'=>'-104','message'=>'失败!'];
            }
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }
}