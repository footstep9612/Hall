<?php

/**
 * @desc 报价相关工具类
 * @file Trait QuoteHelper
 * @author 买买提
 */
trait QuoteHelper{


    /**
     * @desc 报价列表(信息)
     * @param string  $inquiry_id 流程编码
     * @param string $rol 角色(产品线报价人,产品线负责人) 默认为报价人
     * @return array 返回结果
     */
    public static function quoteListHandler($inquiry_id,$rol='QUOTER'){

        //询单(项目)信息 [inquiry表]
        $inquiry = new InquiryModel();
        $inquiryInfo = $inquiry->where(['id'=>$inquiry_id])->field([
            'serial_no','status','pm_id'
        ])->find();
        $inquiryInfo['pm_name'] = Z('erui2_sys.Employee')->where(['id'=>$inquiryInfo['pm_id']])->getField('name');
        unset($inquiryInfo['pm_id']);

        //询单明细信息 [inquiry_item表]
        $inquiryItem = new InquiryItemModel();
        $inquiryItemInfo = $inquiryItem->where(['inquiry_id'=>$inquiry_id])->field([
            'id','inquiry_id','sku','buyer_goods_no','name','name_zh','model','remarks','remarks_zh','qty','unit','brand'
        ])->find();

        //报价列表
        $quoteItem = new QuoteItemModel();
        $quoteItemList = $quoteItem->where(['inquiry_id'=>$inquiry_id])->field([
           'supplier_id',//供应商名称
            'brand',//品牌
            'purchase_unit_price',//采购单价
            'purchase_price_cur_bn',//采购币种
            'net_weight_kg',//净重
            'gross_weight_kg',//毛重
            'package_size',//包装体积
            'package_mode',//包装方式
            'goods_source',//产品来源
            'stock_loc',//存放地
            'delivery_days',//交货期(天)
            'period_of_validity',//报价有效期
            'reason_for_no_quote',//未报价分析
            'status'//报价状态
        ])->select();

        foreach ($quoteItemList as $item=>$value){
            $quoteItemList[$item]['supplier_name'] = Z('erui2_supplier.Supplier')->where(['id'=>$value['supplier_id']])->getField('name');
            $quoteItemList[$item]['sku'] = $inquiryItemInfo['sku'];
            $quoteItemList[$item]['buyer_goods_no'] = $inquiryItemInfo['buyer_goods_no'];
            $quoteItemList[$item]['name'] = $inquiryItemInfo['name'];
            $quoteItemList[$item]['name_zh'] = $inquiryItemInfo['name_zh'];
            $quoteItemList[$item]['model'] = $inquiryItemInfo['model'];
            $quoteItemList[$item]['remarks'] = $inquiryItemInfo['remarks'];
            $quoteItemList[$item]['remarks_zh'] = $inquiryItemInfo['remarks_zh'];
            $quoteItemList[$item]['qty'] = $inquiryItemInfo['qty'];
            $quoteItemList[$item]['unit'] = $inquiryItemInfo['unit'];
        }

        $response = $inquiryInfo;
        $response['list'] = $quoteItemList;

        return $response;
    }

    /**
     * @desc 上传附件(产品线负责人)
     * @param $request
     * @return array
     */
    public static function addBizlineAttach($request){

        $quoteAttach = new QuoteAttachModel();
        //声明附件分组
        $request['attach_group'] = '产品线附件';
        $request['created_at'] = date('Y-m-d H:i:s');
        try{
            if ($quoteAttach->add($quoteAttach->create($request))){
                return ['code'=>'1','message'=>'上传成功!'];
            }else{
                return ['code'=>'-104','message'=>'上传失败!'];
            }
        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    public static function restoreInquiryInfo(array $inquiry){
        //市场经办人
        $employeeModel = new EmployeeModel();
        $agent = $employeeModel->where(['id'=>$inquiry['agent_id']])->getField('name');
        if ($agent){
            $inquiry['agent_name'] = $agent;
            unset($inquiry['agent_id']);
        }
        //项目经理
        $productManager = $employeeModel->where(['id'=>$inquiry['pm_id']])->getField('name');
        if ($productManager){
            $inquiry['pm_name'] = $productManager;
            unset($inquiry['pm_id']);
        }

        return $inquiry;
    }

    /**
     * @desc 报价列表(产品线负责人)
     * @param $condition
     * @return mixed
     */
    public static function bizlineManagerQuoteList($condition){

        $where = self::bizlineManagerQuoteListCondition($condition);
        //p($where);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $quoteBizlineModel = new QuoteBizLineModel();
        $field = 'inq.id inquiry_id,inq.serial_no,inq.country_bn,inq.buyer_name,inq.agent_id,inq.pm_id,inq.inquiry_time,inq.status,inq.quote_deadline,qb.id,qb.quote_id,qb.bizline_id,qb.status quote_status';
        return  $quoteBizlineModel->alias('qb')
            ->join('erui2_rfq.inquiry inq ON qb.inquiry_id = inq.id')
            ->field($field)
            ->where($where)
            ->page($currentPage, $pageSize)
            ->order('id DESC')
            ->select();
        //p($quoteBizlineModel->getLastSql());
        //p($data);
    }

    public static function bizlineManagerQuoteListCondition(array $condition){

        $where = [];

        if(!empty($condition['bizline_id'])) {
            $where['qb.bizline_id'] = array('in',implode(",",$condition['bizline_id']));
        }
        //p($where);
        //项目状态
        if(!empty($condition['status'])) {
            $where['qb.status'] = $condition['status'];
        }
        //国家
        if(!empty($condition['country_bn'])) {
            $where['country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }
        //流程编码
        if(!empty($condition['serial_no'])) {
            $where['serial_no'] = ['like', '%' . $condition['serial_no'] . '%'];
        }
        //客户名称
        if(!empty($condition['buyer_name'])) {
            $where['buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }
        //市场经办人
        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = $condition['agent_id'];
        }
        //项目经理
        if (!empty($condition['pm_id'])) {
            $where['pm_id'] = $condition['pm_id'];
        }
        //询价时间
        if(!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])){
            $where['inquiry_time'] = [
                ['gt', $condition['start_inquiry_time']],
                ['lt', $condition['end_inquiry_time']]
            ];
        }

        $where['inq.deleted_flag'] = 'N';

        return $where;

    }

    /**
     * @desc 获取报价总数(产品线负责人)
     * @param $where 条件
     * @return int 总数
     */
    public static function bizlineManagerQuoteListCount($where){

        $quoteBizlineModel = new QuoteBizLineModel();

        $where = self::bizlineManagerQuoteListCondition($where);

        $field = 'inq.serial_no,inq.country_bn,inq.buyer_name,inq.agent_id,inq.pm_id,inq.inquiry_time,inq.status,inq.quote_deadline,qb.id,qb.quote_id,qb.status quote_status';
        $count =  $quoteBizlineModel->alias('qb')
            ->join('erui2_rfq.inquiry inq ON qb.inquiry_id = inq.id')
            ->field($field)
            ->where($where)
            ->count('qb.id');

        return $count > 0 ? $count : 0;
    }


    public static function bizlineManagerQuoteSkuList($condition){

        $where['a.quote_id'] = $condition['quote_id'];
        $where['qb.bizline_id'] = $condition['bizline_id'];

        //原理的错误逻辑
//        $quoteItemFormModel = new QuoteItemFormModel();
//
//        $fields = 'a.id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,a.brand,a.supplier_id,a.goods_desc,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.status,a.created_by,qb.bizline_id';
//        $data = $quoteItemFormModel->alias('a')
//            ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
//            ->join('erui2_rfq.quote_bizline qb ON a.quote_id = qb.quote_id')
//            ->field($fields)
//            ->where($where)
//            ->order('a.id DESC')
//            ->select();
//        p($data);

        //改正
        $quoteItemModel = new QuoteItemModel();

        $fields = 'a.id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,a.brand,a.supplier_id,a.remarks goods_remarks,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,qb.status,a.created_by,qb.bizline_id';
        $data = $quoteItemModel->alias('a')
            ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
            ->join('erui2_rfq.quote_bizline qb ON a.quote_id = qb.quote_id')
            ->field($fields)
            ->where($where)
            ->order('a.id DESC')
            ->select();
        return $data;
        //p($data);
    }

    public static function bizlineManagerQuoteSkuListCount($request){

        $where['a.quote_id'] = $request['quote_id'];
        $where['qb.bizline_id'] = $request['bizline_id'];
        //以前的错误代码
//        $quoteItemFormModel = new QuoteItemFormModel();
//
//        $fields = 'a.id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,b.brand,a.supplier_id,a.goods_desc,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.status';
//        $count = $quoteItemFormModel->alias('a')
//            ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
//            ->field($fields)
//            ->where($where)
//            ->count('a.id');
        $quoteItemModel = new QuoteItemModel();

        $fields = 'a.id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,a.brand,a.supplier_id,a.remarks goods_remarks,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.status,a.created_by,qb.bizline_id';
        $count = $quoteItemModel->alias('a')
            ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
            ->join('erui2_rfq.quote_bizline qb ON a.quote_id = qb.quote_id')
            ->field($fields)
            ->where($where)
            ->count('a.id');
        return $count > 0 ? $count : 0 ;
    }

    public static function getQuoteList($condition){

        $where['inq.id'] = $condition['id'];

        $quoteItem = new InquiryItemModel();

        $fields = ['a.id','a.sku','inq.inquiry_no','a.buyer_goods_no','a.name','a.name_zh','a.model','a.remarks','a.remarks_zh','a.qty','a.unit','a.brand'];

        return  $quoteItem->alias('a')
                        ->join('erui2_rfq.inquiry inq ON a.inquiry_id = inq.id','LEFT')
                        ->field($fields)
                        ->where($where)
                        ->order('a.id DESC')
                        ->select();
        //p($data);
    }

    private static $mqslFields = [
            'a.id',
            'a.sku',
            'b.inquiry_no',
            'c.name',
            'c.name_zh',
            'c.model',
            'c.remarks',
            'c.remarks_zh',
            'c.qty',
            'c.unit',
            'c.brand',
            'a.purchase_unit_price',
            'a.purchase_price_cur_bn',
            'a.quote_qty',
            'a.supplier_id',
            's.name supplier_name',
            'a.remarks quote_remarks',
            'a.net_weight_kg',
            'a.gross_weight_kg',
            'a.package_mode',
            'a.package_size',
            'a.delivery_days',
            'a.period_of_validity',
            'a.goods_source',
            'a.stock_loc',
            'a.status',
            'a.reason_for_no_quote',
            'a.bizline_agent_id'
    ];

    public static function getManagerQuoteSkuList($condition){

        $quoteItem = new QuoteItemModel();

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $where = self::getManagerQuoteSkuListCondition($condition);

        return $quoteItem->alias('a')
            ->join('erui2_rfq.inquiry b ON a.inquiry_id = b.id','LEFT')
            ->join('erui2_rfq.inquiry_item c ON a.inquiry_item_id = c.id','LEFT')
            ->join('erui2_supplier.supplier s ON a.supplier_id = s.id','LEFT')
            ->field(self::$mqslFields)
            ->where($where)
            ->page($currentPage, $pageSize)
            ->order('a.id DESC')
            ->select();

    }


    /**
     * @desc 根据条件获取总数(负责人)
     * @param $where 条件
     * @return int 总数
     */
    public static function getQuoteTotalCount($condition){

        $where['inq.id'] = $condition['id'];

        $quoteItem = new InquiryItemModel();

        $fields = ['a.id','a.sku','inq.inquiry_no','inq.serial_no','a.name','a.name_zh','a.model','a.remarks','a.remarks_zh','a.qty','a.unit','a.brand'];

        return  $quoteItem->alias('a')
            ->join('erui2_rfq.inquiry inq ON a.inquiry_id = inq.id','LEFT')
            ->field($fields)
            ->where($where)
            ->count('a.id');

        return $count > 0 ? $count : 0;

    }


    /**
     * @desc 根据筛选条件获取报价列表(项目经理)
     * @param array $condition 条件
     * @return array 结果
     */
    public static function getPmQuoteBizlineList(array $condition){

        $where = self::getPmQuoteBizlineListCondition($condition);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $inquiry = new InquiryModel();
        return $inquiry->field('id, serial_no, country_bn, buyer_id, buyer_name, agent_id, pm_id, inquiry_time, status, quote_deadline')
            ->where($where)
            ->page($currentPage, $pageSize)
            ->order('id DESC')
            ->select();
        //p($inquiry->getLastSql());
        //p($data);
    }

    /**
     * @desc 形成报价列表筛选数组(项目经理)
     * @param array $condition 条件
     * @return array 匹配后的条件
     */
    public static function getPmQuoteBizlineListCondition(array $condition=[]){

        $where = [];
        //项目状态
        if(!empty($condition['status'])) {
            $where['status'] = $condition['status'];
        }
        //国家
        if(!empty($condition['country_bn'])) {
            $where['country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }
        //流程编码
        if(!empty($condition['serial_no'])) {
            $where['serial_no'] = ['like', '%' . $condition['serial_no'] . '%'];
        }
        //客户名称
        if(!empty($condition['buyer_name'])) {
            $where['buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }
        //市场经办人
        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = $condition['agent_id'];
        }
        //项目经理
        if (!empty($condition['pm_id'])) {
            $where['pm_id'] = $condition['pm_id'];
        }
        //询价时间
        if(!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])){
            $where['inquiry_time'] = [
                ['egt', $condition['start_inquiry_time']],
                ['elt', $condition['end_inquiry_time'] . ' 23:59:59']
            ];
        }

        $where['deleted_flag'] = 'N';

        return $where;
    }

    /**
     * @desc 根据条件获取记录总数
     * @param array $condition 条件
     * @return int 总数
     */
    public static function getPmQuoteBizlineListCount(array $condition)
    {
        $where = self::getPmQuoteBizlineListCondition($condition);

        $inquiry = new InquiryModel();
        $count = $inquiry->field('id, serial_no, country_bn, buyer_id, buyer_name, agent_id, pm_id, inquiry_time, status, quote_deadline')
            ->where($where)
            ->count('id');

        return $count > 0 ? $count : 0;
    }

    public static function partitionBizline(array $request){

        //1.创建一条报价记录(quote)
        //2.创建一条产品线报价记录(quote_bizline)
        //3.选择的询单项(inquiry_item)写入到报价单项(quote_item)
        //4选择的讯单项(inquiry_item)写入到产品线报价单项(quote_item_form)
    }
}
