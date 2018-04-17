<?php
/*
 * @desc SKU历史报价模型
 * 
 * @author liujf 
 * @time 2018-04-11
 */
class HistoricalSkuQuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'historical_sku_quote';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2018-04-12
     */
     public function getWhere($condition = []) {
        // 是否必选之一的参数
        $required = false;
        // 采购币种
        if (!empty($condition['purchase_price_cur_bn'])) {
            $where['c.purchase_price_cur_bn'] =  ['in', explode(',', $condition['purchase_price_cur_bn']) ? : ['-1']];
        }
        // 商品SKU
        if (!empty($condition['sku'])) {
            $where['_complex']['d.sku'] = $condition['sku'];
            $required = true;
        }
        // 商品供应商PN码
        if (!empty($condition['pn'])) {
            $where['_complex']['c.pn'] = $condition['pn'];
            $required = true;
        }
        // 中文品名
        if (!empty($condition['name_zh'])) {
            $where['_complex']['b.name_zh'] = $condition['name_zh'];
            $required = true;
        }
        // 品牌和型号
        if (!empty($condition['brand']) && !empty($condition['model'])) { 
            $where['_complex'][] =  [[
                'c.brand' => $condition['brand'],
                'b.model' => $condition['model']
            ]];
            $required = true;
        }
        if ($required && $where['_complex']) {
            $where['_complex']['_logic'] = 'or';
        } else {
            $where['a.id'] = '-1';
        }
        return $where;
     }
     
	/**
     * @desc 获取记录总数
 	 * 
     * @param array $condition 
     * @return int
     * @author liujf 
     * @time 2018-04-12
     */
    public function getCount($condition = []) {
    	$ids = $this->getSqlJoint($condition)->getField('a.id', true);
    	return count($ids);
    }
    
    /**
     * @desc 获取列表
 	 * 
     * @param array $condition
     * @return array
     * @author liujf 
     * @time 2018-04-12
     */
    public function getList($condition = []) {
        // 语言
        $lang = defined(LANG_SET) ? LANG_SET : 'zh';
        // 分页
    	$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
    	$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    	// 排序
    	$orderReferFields = ['purchase_unit_price', 'delivery_days', 'matching_percent'];
    	$orderReferType = ['ASC', 'DESC'];
    	if (!empty($condition['order_by'])) {
    	    $orderArr = [];
    	    $orderFields = explode(',', $condition['order_by']);
    	    foreach ($orderFields as $v) {
    	        $tmpArr = explode(':', $v);
    	        $orderField = trim($tmpArr[0]);
    	        $orderType = strtoupper(trim($tmpArr[1]));
    	        if (in_array($orderField, $orderReferFields)) {
    	            $orderArr[] =$orderField . ' ' . (in_array($orderType, $orderReferType) ? $orderType : 'ASC');
    	        }
    	    }
    	    $order = implode(',', $orderArr);
    	}
    	$order = ($order ? $order . ',' : '') . 'a.id DESC';
    	return $this->getSqlJoint($condition)
                            ->field('a.created_at,
                                          b.name, b.name_zh, b.model,
                                          c.pn, c.brand, c.quote_qty, c.period_of_validity, c.delivery_days, c.stock_loc, c.purchase_unit_price, c.purchase_price_cur_bn,
                                          d.sku, d.supplier_id,
                                          e.name'. ($lang == 'zh' ? '' : '_en') . ' AS supplier_name,
                                          CASE
                                          WHEN (d.sku <> \'\' AND d.sku = \'' . $condition['sku'] . '\')
                                                        OR (b.pn <> \'\' AND b.pn = \'' . $condition['pn'] . '\')
                                                        OR ((b.name <> \'\' AND b.name = \'' . $condition['name'] . '\') AND (b.name_zh <> \'\' AND b.name_zh = \'' . $condition['name_zh'] . '\')) AND (b.brand <> \'\' AND b.brand = \'' . $condition['brand'] . '\') AND (b.model <> \'\' AND b.model = \'' . $condition['model'] . '\') THEN 100
                                          WHEN (b.name_zh <> \'\' AND b.name_zh = \'' . $condition['name_zh'] . '\') AND (b.model <> \'\' AND b.model = \'' . $condition['model'] . '\') THEN 90
                                          WHEN b.name_zh <> \'\' AND b.name_zh = \'' . $condition['name_zh'] . '\' THEN 80
                                          WHEN (b.brand <> \'\' AND b.brand = \'' . $condition['brand'] . '\') AND (b.model <> \'\' AND b.model = \'' . $condition['model'] . '\') THEN 70
                                          END AS matching_percent')
    	                    ->page($currentPage, $pageSize)
    	                    ->order($order)
    	                    ->select();
    }
    
    /**
     * @desc 获取组装sql后的对象
     *
     * @param array $condition
     * @return object
     * @author liujf
     * @time 2018-04-12
     */
    public function getSqlJoint($condition = []) {
        $inquiryItemModel = new InquiryItemModel();
        $quoteItemModel = new QuoteItemModel();
        $finalQuoteItemModel = new FinalQuoteItemModel();
        $suppliersModel = new SuppliersModel();
        // 获取表名
        $inquiryItemTableName = $inquiryItemModel->getTableName();
        $quoteItemTableName = $quoteItemModel->getTableName();
        $finalQuoteItemTableName = $finalQuoteItemModel->getTableName();
        $suppliersTableName = $suppliersModel->getTableName();
        $where = $this->getWhere($condition);
        return $this->alias('a')
                            ->join($inquiryItemTableName . ' b ON a.inquiry_item_id = b.id AND b.deleted_flag = \'N\'', 'LEFT')
                            ->join($quoteItemTableName . ' c ON a.quote_item_id = c.id AND c.deleted_flag = \'N\'', 'LEFT')
                            ->join($finalQuoteItemTableName . ' d ON a.inquiry_item_id = d.inquiry_item_id AND d.deleted_flag = \'N\'', 'LEFT')
                            ->join($suppliersTableName . ' e ON d.supplier_id = e.id AND e.deleted_flag = \'N\'', 'LEFT')
                            ->where($where)
    	                    ->group('a.id');
    }
    
}