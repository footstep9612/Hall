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
        //询单(项目)状态
        switch ($inquiry['status']){
            case 'DRAFT': $inquiry['status'] = '起草'; break;
            case 'APPROVING_BY_SC': $inquiry['status'] = '方案中心审核中'; break;
            case 'APPROVED_BY_SC': $inquiry['status'] = '方案中心已确认'; break;
            case 'QUOTING_BY_BIZLINE': $inquiry['status'] = '产品线报价中'; break;
            case 'QUOTED_BY_BIZLINE': $inquiry['status'] = '产品负责人已确认'; break;
            case 'BZ_QUOTE_REJECTED': $inquiry['status'] = '项目经理驳回产品报价'; break;
            case 'QUOTING_BY_LOGI': $inquiry['status'] = '物流报价中'; break;
            case 'QUOTED_BY_LOGI': $inquiry['status'] = '物流审核人已确认'; break;
            case 'LOGI_QUOTE_REJECTED': $inquiry['status'] = '项目经理驳回物流报价'; break;
            case 'APPROVED_BY_PM': $inquiry['status'] = '项目经理已确认'; break;
            case 'APPROVING_BY_MARKET': $inquiry['status'] = '市场主管审核中'; break;
            case 'APPROVED_BY_MARKET': $inquiry['status'] = '市场主管已审核'; break;
            case 'QUOTE_SENT': $inquiry['status'] = '报价单已发出'; break;
            case 'INQUIRY_CLOSED': $inquiry['status'] = '报价关闭'; break;
        }

        return $inquiry;
    }

    public static function getQuoteList($where){

        $quoteItem = new QuoteItemModel();

        /*===============================
        关联询单(inquiry)表获取一下字段
        inquiry_no  客户询单号
        adhoc_request   客户需求描述

        关联询单明细(inquiry_item)表获取一下字段
        sku  sku
        model   型号
        name   外文品名
        name_zh   中文品名
        remarks   客户需求描述
        remarks_zh   客户需求描述
        brand   品牌
        qty   数量
        unit   单位
        ===============================*/
        $fields = ['a.id','a.bizline_id','d.name bizline_name','a.sku','b.inquiry_no','b.serial_no','b.adhoc_request','c.name','c.name_zh','c.model','c.remarks','c.remarks_zh','c.qty','c.unit','c.brand'];

        $data = $quoteItem->alias('a')
                        ->join('erui2_rfq.inquiry b ON a.inquiry_id = b.id','LEFT')
                        ->join('erui2_rfq.inquiry_item c ON a.inquiry_item_id = c.id','LEFT')
                        ->join('erui2_operation.bizline d ON a.bizline_id = d.id','LEFT')
                        ->field($fields)
                        ->where($where)
                        ->order('a.id DESC')
                        ->select();
        p($data);
    }

    /**
     * @desc 根据条件获取总数(负责人)
     * @param $where 条件
     * @return int 总数
     */
    public static function getQuoteTotalCount($where)
    {
        $quoteItem = new QuoteItemModel();
        $fields = ['a.id','d.name bizline_name','c.sku','b.inquiry_no','b.adhoc_request','c.name','c.name_zh','c.model','c.remarks','c.remarks_zh','c.qty','c.unit','c.brand'];

        $count = $quoteItem->alias('a')
            ->join('erui2_rfq.inquiry b ON a.inquiry_id = b.id','LEFT')
            ->join('erui2_rfq.inquiry_item c ON a.inquiry_id = c.inquiry_id','LEFT')
            ->join('erui2_operation.bizline d ON a.bizline_id = d.id','LEFT')
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

        $quoteModel = new QuoteModel();
        return  $quoteModel->alias('a')
            ->join('erui2_rfq.inquiry b ON a.inquiry_id = b.id', 'LEFT')
            ->field('a.id, b.serial_no, b.country_bn, b.buyer_name, b.agent_id, b.pm_id, b.inquiry_time, b.status, a.period_of_validity')
            ->where($where)
            ->page($currentPage, $pageSize)
            ->order('a.id DESC')
            ->select();

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
            $where['b.status'] = $condition['status'];
        }
        //国家
        if(!empty($condition['country_bn'])) {
            $where['b.country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }
        //流程编码
        if(!empty($condition['serial_no'])) {
            $where['b.inquiry_no'] = ['like', '%' . $condition['inquiry_no'] . '%'];
        }
        //客户名称
        if(!empty($condition['buyer_name'])) {
            $where['b.buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }
        //市场经办人
        if (!empty($condition['agent_id'])) {
            $where['b.agent_id'] = $condition['agent_id'];
        }
        //项目经理
        if (!empty($condition['pm_id'])) {
            $where['b.pm_id'] = $condition['pm_id'];
        }
        //询价时间
        if(!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])){
            $where['b.inquiry_time'] = [
                ['egt', $condition['start_inquiry_time']],
                ['elt', $condition['end_inquiry_time'] . ' 23:59:59']
            ];
        }

        $where['a.deleted_flag'] = 'N';

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

        $quoteModel = new QuoteModel();
        $count = $quoteModel->alias('a')
            ->join('erui2_rfq.inquiry b ON a.inquiry_id = b.id', 'LEFT')
            ->field('a.id, b.serial_no, b.country_bn, b.buyer_name, b.agent_id, b.pm_id, b.inquiry_time, b.status, a.period_of_validity')
            ->where($where)
            ->count('a.id');

        return $count > 0 ? $count : 0;
    }
}
