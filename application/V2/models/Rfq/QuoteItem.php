<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @desc 报价单明细模型
 * @author 买买提
 */
class Rfq_QuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item';

    public function __construct() {
        parent::__construct();
    }

    public function getListByOtherOrg($condition) {
        $where = [
            'a.deleted_flag' => 'N',
            'a.inquiry_id' => $condition['inquiry_id']
        ];
        $inquiryItemTableName = (new InquiryItemModel())->getTableName();
        $supplierTableName = (new SupplierModel())->getTableName();
        $quoteItemList = $this
                ->alias('a')
                ->field('a.brand AS quote_brand, a.pn, a.purchase_unit_price,'
                        . ' a.purchase_price_cur_bn, a.gross_weight_kg, a.package_mode,'
                        . ' a.package_size, a.stock_loc, a.goods_source, a.delivery_days,'
                        . ' a.period_of_validity, a.reason_for_no_quote, b.sku, b.buyer_goods_no, '
                        . 'b.category, b.name, b.name_zh, b.qty, b.unit, b.brand, b.model,'
                        . ' b.remarks,c.name AS supplier_name')
                ->join($inquiryItemTableName . ' b ON a.inquiry_item_id = b.id AND b.deleted_flag = \'N\'', 'LEFT')
                ->join($supplierTableName . ' c ON a.supplier_id = c.id AND c.deleted_flag = \'N\'', 'LEFT')
                ->where($where)
                ->order('a.id')
                ->select();
        $outData = [];
        $i = 1;
        foreach ($quoteItemList as $quoteItem) {
            $outData[] = [
                ['value' => $i],
                ['value' => $quoteItem['sku']],
                ['value' => $quoteItem['buyer_goods_no']],
                ['value' => $quoteItem['category']],
                ['value' => $quoteItem['name']],
                ['value' => $quoteItem['name_zh']],
                ['value' => $quoteItem['qty']],
                ['value' => $quoteItem['unit']],
                ['value' => $quoteItem['brand']],
                ['value' => $quoteItem['model']],
                ['value' => $quoteItem['remarks']],
                ['value' => $quoteItem['supplier_name']],
                ['value' => $quoteItem['quote_brand']],
                ['value' => $quoteItem['pn']],
                ['value' => $quoteItem['purchase_unit_price']],
                ['value' => $quoteItem['purchase_price_cur_bn']],
                ['value' => $quoteItem['gross_weight_kg']],
                ['value' => $quoteItem['package_mode']],
                ['value' => $quoteItem['package_size']],
                ['value' => $quoteItem['stock_loc']],
                ['value' => $quoteItem['goods_source']],
                ['value' => $quoteItem['delivery_days']],
                ['value' => $quoteItem['period_of_validity']],
                ['value' => $quoteItem['reason_for_no_quote']],
            ];
            $i++;
        }
        return $outData;
    }

    public function getListByErui($condition) {
        $where = [
            'a.deleted_flag' => 'N',
            'a.inquiry_id' => $condition['inquiry_id']
        ];
        $inquiryItemTableName = (new InquiryItemModel())->getTableName();
        $supplierTableName = (new SupplierModel())->getTableName();
        $quoteItemList = $this
                ->alias('a')
                ->field('a.brand AS quote_brand, a.pn, a.purchase_unit_price,'
                        . ' a.purchase_price_cur_bn, a.gross_weight_kg, a.package_mode,'
                        . ' a.package_size, a.stock_loc, a.goods_source, a.delivery_days,'
                        . ' a.period_of_validity, a.reason_for_no_quote, b.sku, b.buyer_goods_no, '
                        . 'a.org_id,b.material_cat_no,b.name, b.name_zh, b.qty, b.unit, b.brand, b.model,'
                        . ' b.remarks,c.name AS supplier_name')
                ->join($inquiryItemTableName . ' b ON a.inquiry_item_id = b.id AND b.deleted_flag = \'N\'', 'LEFT')
                ->join($supplierTableName . ' c ON a.supplier_id = c.id AND c.deleted_flag = \'N\'', 'LEFT')
                ->where($where)
                ->order('a.id')
                ->select();
        (new OrgModel())->setOrgidAndName($quoteItemList);
        (new MaterialCatModel())->setMaterialCatAndNo($quoteItemList, 'zh');
        $outData = [];
        $i = 1;
        foreach ($quoteItemList as $quoteItem) {
            $outData[] = [
                ['value' => $i],
                ['value' => $quoteItem['sku']],
                ['value' => $quoteItem['buyer_goods_no']],
                ['value' => $quoteItem['material_cat_no']],
                ['value' => $quoteItem['org_id']],
                ['value' => $quoteItem['name']],
                ['value' => $quoteItem['name_zh']],
                ['value' => $quoteItem['qty']],
                ['value' => $quoteItem['unit']],
                ['value' => $quoteItem['brand']],
                ['value' => $quoteItem['model']],
                ['value' => $quoteItem['remarks']],
                ['value' => $quoteItem['supplier_name']],
                ['value' => $quoteItem['quote_brand']],
                ['value' => $quoteItem['pn']],
                ['value' => $quoteItem['purchase_unit_price']],
                ['value' => $quoteItem['purchase_price_cur_bn']],
                ['value' => $quoteItem['gross_weight_kg']],
                ['value' => $quoteItem['package_mode']],
                ['value' => $quoteItem['package_size']],
                ['value' => $quoteItem['stock_loc']],
                ['value' => $quoteItem['goods_source']],
                ['value' => $quoteItem['delivery_days']],
                ['value' => $quoteItem['period_of_validity']],
                ['value' => $quoteItem['reason_for_no_quote']],
            ];
            $i++;
        }
        return $outData;
    }

    public function getTitleListByOtherOrg() {
        $titleList = [
            '序号',
            '平台sku',
            '客户商品号',
            '产品分类（必填）',
            '外文品名（必填）',
            '中文品名（必填）',
            '数量（必填）',
            '单位（必填）',
            '品牌',
            '型号',
            '客户需求描述',
            '供应商名称',
            '品牌（必填）',
            'PN码',
            '采购单价（必填）',
            '采购币种（必填）',
            '单件毛重（kg）（必填）',
            '包装方式（必填）',
            '包装体积（m³）（必填）',
            '存放地（必填）',
            '产品来源（必填）',
            '交货期（必填）',
            '报价有效期（必填）',
            '未报价分析',
        ];
        return $titleList;
    }

    public function getSkusByOtherOrg($data, $inquiryId, $quoteId) {
        $sku = [];
        $suppliersModel = new SuppliersModel();
        foreach ($data as $k => $v) {
            $sku[$k]['inquiry_id'] = $inquiryId; //询单id
            $sku[$k]['quote_id'] = $quoteId; //报价单id
            $sku[$k]['sku'] = $v[1]; //平台sku
            $sku[$k]['buyer_goods_no'] = $v[2]; //客户商品号
            $sku[$k]['category'] = $v[3]; //产品分类
            $sku[$k]['name'] = $v[4]; //外文品名
            $sku[$k]['name_zh'] = $v[5]; //中文品名
            $sku[$k]['qty'] = $v[6]; //数量
            $sku[$k]['unit'] = $v[7]; //单位
            $sku[$k]['brand'] = $v[8]; //品牌
            $sku[$k]['model'] = $v[9]; //型号
            $sku[$k]['remarks'] = $v[10]; //客户需求描述
            $sku[$k]['supplier_id'] = $suppliersModel->where(['name' => $v[11], 'deleted_flag' => 'N', 'name is not null and name<>\'\''])->getField('id');
            $sku[$k]['quote_brand'] = $v[12]; //品牌(报价)
            $sku[$k]['pn'] = $v[13]; //商品供应商PN码
            $sku[$k]['purchase_unit_price'] = strpos($v[14], ',') !== false ? str_replace('，', '', str_replace(',', '', $v[14])) : $v[14]; //采购单价
            $sku[$k]['purchase_price_cur_bn'] = $v[15]; //采购币种
            $sku[$k]['gross_weight_kg'] = $v[16]; //毛重
            $sku[$k]['package_mode'] = $v[17]; //包装方式
            $sku[$k]['package_size'] = $v[18]; //包装体积
            $sku[$k]['stock_loc'] = $v[19]; //存放地
            $sku[$k]['goods_source'] = $v[20]; //产品来源
            $sku[$k]['delivery_days'] = $v[21]; //交货周期
            $sku[$k]['period_of_validity'] = $v[22]; //报价有效期
            $sku[$k]['reason_for_no_quote'] = $v[23]; //未报价分析
            $sku[$k]['created_at'] = date('Y-m-d H:i:s'); //添加时间
        }
        return $sku;
    }

    private function _getId($str) {
        if ($str) {
            $match = [];
            preg_match('/.*?-(\d+)$/', $str, $match);

            return !empty($match[1]) ? $match[1] : '';
        } else {
            return '';
        }
    }

    public function getSkusByErui($data, $inquiryId, $quoteId) {
        $sku = [];
        $suppliersModel = new SuppliersModel();
        foreach ($data as $k => $v) {
            $sku[$k]['inquiry_id'] = $inquiryId; //询单id
            $sku[$k]['quote_id'] = $quoteId; //报价单id
            $sku[$k]['sku'] = $v[1]; //平台sku
            $sku[$k]['buyer_goods_no'] = $v[2]; //客户商品号

            $sku[$k]['material_cat_no'] = $this->_getId($v[3]); //产品分类
            $sku[$k]['org_id'] = $this->_getId($v[4]); //外文品名
            $sku[$k]['name'] = $v[5]; //外文品名
            $sku[$k]['name_zh'] = $v[6]; //中文品名
            $sku[$k]['qty'] = $v[7]; //数量
            $sku[$k]['unit'] = $v[8]; //单位
            $sku[$k]['brand'] = $v[9]; //品牌
            $sku[$k]['model'] = $v[10]; //型号
            $sku[$k]['remarks'] = $v[11]; //客户需求描述
            $sku[$k]['supplier_id'] = $suppliersModel->where(['name' => $v[12],
                        'deleted_flag' => 'N', 'name is not null and name<>\'\''])->getField('id');
            $sku[$k]['quote_brand'] = $v[13]; //品牌(报价)
            $sku[$k]['pn'] = $v[14]; //商品供应商PN码
            $sku[$k]['purchase_unit_price'] = strpos($v[15], ',') !== false ? str_replace('，', '', str_replace(',', '', $v[15])) : $v[15]; //采购单价
            $sku[$k]['purchase_price_cur_bn'] = $v[16]; //采购币种
            $sku[$k]['gross_weight_kg'] = $v[17]; //毛重
            $sku[$k]['package_mode'] = $v[18]; //包装方式
            $sku[$k]['package_size'] = $v[19]; //包装体积
            $sku[$k]['stock_loc'] = $v[20]; //存放地
            $sku[$k]['goods_source'] = $v[21]; //产品来源
            $sku[$k]['delivery_days'] = $v[22]; //交货周期
            $sku[$k]['period_of_validity'] = $v[23]; //报价有效期
            $sku[$k]['reason_for_no_quote'] = $v[24]; //未报价分析
            $sku[$k]['created_at'] = date('Y-m-d H:i:s'); //添加时间
        }
        return $sku;
    }

    public function getTitleListByErui() {
        $titleList = [
            '序号',
            '平台sku',
            '客户商品号',
            '物料分类（必填）',
            '所属事业部（必填）',
            '外文品名（必填）',
            '中文品名（必填）',
            '数量（必填）',
            '单位（必填）',
            '品牌',
            '型号',
            '客户需求描述',
            '供应商名称',
            '品牌（必填）',
            'PN码',
            '采购单价（必填）',
            '采购币种（必填）',
            '单件毛重（kg）（必填）',
            '包装方式（必填）',
            '包装体积（m³）（必填）',
            '存放地（必填）',
            '产品来源（必填）',
            '交货期（必填）',
            '报价有效期（必填）',
            '未报价分析',
        ];
        return $titleList;
    }

}
