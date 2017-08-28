<?php

/**
 * @desc 报价单产品线平行表
 * @file Class QuoteItemFormModel
 * @author 买买提
 */
class QuoteItemFormModel extends PublicModel{
    /**
     * 数据库名称
     * @var string
     */
    protected $dbName = 'erui2_rfq';

    /**
     * 数据表名称
     * @var string
     */
    protected $tableName = 'quote_item_form';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 选择报价
     * @param $where
     *
     * @return mixed
     */
    public function getList($where){

        $field = 'id,created_by,brand,goods_desc,net_weight_kg,gross_weight_kg,package_size,package_mode,delivery_days,goods_source,stock_loc,status,supplier_id,contact_first_name,contact_last_name,contact_phone,purchase_unit_price,period_of_validity';
        //按价格由低到高显示
        return $this->where($where)->field($field)->order('purchase_unit_price ASC')->select();
    }


    /**
     * 获取报价单项对应的sku列表
     * @param array $condition
     *
     * @return mixed
     */
    public function getSkuList(array $condition){

        $where = ['a.quote_item_id' => $condition['quote_item_id'] ];
        $field = 'a.id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,a.brand,a.supplier_id,a.goods_desc,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.status,a.created_by,c.bizline_id';

        $data = $this->alias('a')
                    ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
                    ->join('erui2_rfq.quote_bizline c ON a.quote_id = c.quote_id')
                    ->field($field)
                    ->where($where)
                    ->order('a.id DESC')
                    ->select();
        //p($data);
        return $data;
    }

    public function getSkuListCount(array $condition){

        $where = ['a.quote_item_id' => $condition['quote_item_id'] ];
        $field = 'a.id,b.sku,b.buyer_goods_no,b.name,b.name_zh,b.model,b.remarks,b.remarks_zh,b.qty,b.unit,a.brand,a.supplier_id,a.goods_desc,a.purchase_unit_price,a.purchase_price_cur_bn,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.goods_source,a.stock_loc,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.status,a.created_by';

        $count = $this->alias('a')
            ->join('erui2_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
            ->field($field)
            ->where($where)
            ->count('a.id');

        return $count > 0 ? $count : 0;
    }
}