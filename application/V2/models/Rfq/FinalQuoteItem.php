<?php

/**
 * @desc 最终报价单明细模型
 * @author 张玉良
 */
class Rfq_FinalQuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote_item';
    protected $joinTable1 = 'erui_rfq.quote_item b ON a.quote_item_id = b.id';
    protected $joinTable2 = 'erui_rfq.inquiry_item c ON a.inquiry_item_id = c.id';
    protected $joinTable3 = 'erui_rfq.inquiry_item_attach d ON a.inquiry_item_id = d.inquiry_item_id';
    protected $joinField = 'a.id,a.inquiry_id,a.quote_id,a.sku,a.supplier_id,a.exw_unit_price as final_exw_unit_price,a.quote_unit_price as final_quote_unit_price,
	                                           c.qty as quote_qty,c.unit as quote_unit,b.brand,b.exw_unit_price,b.quote_unit_price,b.net_weight_kg,b.gross_weight_kg,b.remarks as final_remarks,
	                                           b.package_mode,b.package_size,b.delivery_days,b.period_of_validity,b.goods_source,b.stock_loc,b.reason_for_no_quote,b.pn,
	                                           c.buyer_goods_no,c.name,c.name_zh,c.model,c.remarks,c.remarks_zh,d.attach_name,d.attach_url';
    protected $finalSkuFields = 'a.id,a.sku,
                                                       b.buyer_goods_no,b.name,b.name_zh,b.qty,b.unit,b.brand,b.model,b.remarks,b.category,
                                                       c.exw_unit_price,c.quote_unit_price,
                                                       a.exw_unit_price final_exw_unit_price,a.quote_unit_price final_quote_unit_price,
                                                       c.gross_weight_kg,c.package_mode,c.package_size,c.delivery_days,c.period_of_validity,c.goods_source,c.stock_loc,c.reason_for_no_quote';

    public function __construct() {
        parent::__construct();
    }

}
