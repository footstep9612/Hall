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
}
