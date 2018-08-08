<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SupplierInquiry
 * @author  zhongyg
 * @date    2017-11-7 13:52:20
 * @version V2.0
 * @desc
 */
class SupplierInquiryModel extends PublicModel {

//put your code here
    protected $tableName = 'supplier';
    protected $dbName = 'erui_supplier'; //数据库名称
    protected $areas = ['Middle East', 'South America', 'North America', 'Africa', 'Pan Russian', 'Asia-Pacific', 'Europe'];
    protected $RateUSD = [];

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition, &$where) {
        if (!empty($condition['supplier_no'])) {
            $supplier_no = $this->escapeString($condition['supplier_no']);
            $where .= ' AND tmp.supplier_no=\'' . $supplier_no . '\'';
        }
        if (!empty($condition['supplier_name'])) {
            $supplier_name = $this->escapeString($condition['supplier_name']);
            $where .= ' AND tmp.supplier_name like \'%' . $supplier_name . '%\'';
        }
        if (!empty($condition['created_at_start']) && !empty($condition['created_at_end'])) {
            $created_at_start = $this->escapeString($condition['created_at_start']);
            $created_at_end = $this->escapeString($condition['created_at_end']);
            $where .= ' AND tmp.created_at between \'' . $created_at_start . '\''
                    . ' AND \'' . $created_at_end . '\'';
        } elseif (!empty($condition['created_at_start'])) {
            $created_at_start = $this->escapeString($condition['created_at_start']);
            $where .= ' AND tmp.created_at > \'' . $created_at_start . '\'';
        } elseif (!empty($condition['created_at_end'])) {
            $created_at_end = $this->escapeString($condition['created_at_end']);
            $where .= ' AND tmp.created_at < \'' . $created_at_end . '\'';
        }
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getList($condition) {
        $where = '';

        $this->_getCondition($condition, $where);
        list($offset, $length) = $this->_getPage($condition);

        $inquiry_model = new InquiryModel();
        $inquiry_table = $inquiry_model->getTableName();
        $final_quote_item_model = new FinalQuoteItemModel();
        $final_quote_item_table = $final_quote_item_model->getTableName();

        $marketareacountry_model = new MarketAreaCountryModel();

        $marketareacountry_table = $marketareacountry_model->getTableName();
        $field = 'supplier_no,supplier_name,supplier_id,';
        foreach ($this->areas as $area_bn) {
            $new_area_bn = str_replace(' ', '-', trim($area_bn));
            $field .= 'sum(if(tmp.area_bn=\'' . $this->escapeString($area_bn) . '\',1,0)) as \'' . $new_area_bn . '\',';
        }
        $field .= 'sum(tmp.area_bn is not null) as \'total\' ';

        $supplier_table = $this->getTableName();
        $areas = '\'Middle East\',\'South America\',\'North America\',\'Africa\',\'Pan Russian\',\'Asia-Pacific\',\'Europe\'';
        $sql = 'select ' . $field . ' from (SELECT s.supplier_no,s.name as supplier_name,s.id as supplier_id,fqi.inquiry_id,mac.market_area_bn as area_bn,s.created_at FROM '
                . $supplier_table . ' s left JOIN ' . $final_quote_item_table . ' fqi on fqi.supplier_id=s.id and fqi.deleted_flag=\'N\' and fqi.`status`=\'VALID\' '
                . ' left JOIN ' . $inquiry_table . ' i on i.id =fqi.inquiry_id '
                . ' AND i.deleted_flag = \'N\' AND i.status = \'QUOTE_SENT\' '
                . ' AND i.quote_status = \'COMPLETED\' '
                . ' left join ' . $marketareacountry_table . ' mac on mac.country_bn=i.country_bn  '
                . ' WHERE s.deleted_flag = \'N\' '
                . ' AND  s.`status` in (\'APPROVED\', \'VALID\', \'DRAFT\', \'APPROVING\',\'INVALID\') '
                . ' and  mac.market_area_bn  IN (' . $areas . ')'
                . ' GROUP BY fqi.inquiry_id,mac.market_area_bn,fqi.supplier_id  ) tmp WHERE 1=1 ' . $where
                . ' group by  supplier_id order by total desc ';

        $data = $this->query($sql . ' limit ' . $offset . ' ,' . $length);


        $count = $this->query('select count(*) as num from (' . $sql . ') t');

        return [$data, isset($count[0]['num']) ? intval($count[0]['num']) : 0];
    }

    /**
     * 供应商数量
     * @return mix
     * @author zyg
     */
    public function getCount($condition) {

        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
        ];
        $this->_getCondition($condition, $where);
        $count = $this
// ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getAreaCountBySupplierId($supplier_id, $created_at_start, $created_at_end, &$item) {

        $inquiry_ids = $this->getInquiryIdsSupplierId($supplier_id);
        $item['total'] = 0;
        foreach ($this->areas as $area) {

            $areabn = str_replace(' ', '-', trim($area));
            $item[$areabn] = 0;
        }

        if (empty($inquiry_ids)) {
            return null;
        }

        $where = ['deleted_flag' => 'N', 'status' => 'QUOTE_SENT', 'quote_status' => 'COMPLETED', 'area_bn' => ['in', $this->areas], 'id' => ['in', $inquiry_ids]];
        if ($created_at_start && $created_at_end) {
            $where['created_at'] = ['between', $created_at_start . ',' . $created_at_end];
        } elseif ($created_at_start) {
            $where['created_at'] = ['egt', $created_at_start];
        } elseif ($created_at_end) {
            $where['created_at'] = ['elt', $created_at_end];
        }
        $inquiry_model = new InquiryModel();

        $areacounts = $inquiry_model
                ->field('count(\'id\') as area_count,area_bn ')
                ->where($where)
                ->group('area_bn')
                ->select();

        foreach ($areacounts as $areacount) {
            $area_bn = str_replace(' ', '-', trim($areacount['area_bn']));
            $item[$area_bn] = $areacount['area_count'];
            $item['total'] += $areacount['area_count'];
        }
    }

    /**
     * 获取供应商询单ID
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function getInquiryIdsSupplierId($supplier_id) {
        $final_quote_item_model = new FinalQuoteItemModel();
        $where = ['supplier_id' => $supplier_id, 'deleted_flag' => 'N', 'status' => 'VALID',];
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where($where)->group('inquiry_id')->select();
        $inquiry_ids = [];
        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        return $inquiry_ids;
    }

    /**
     * 供应商数量
     * @return mix
     * @author zyg
     */
    public function getSupplierCount() {
        $where = ['deleted_flag' => 'N', 'status' => ['in', ['APPROVED', 'DRAFT', 'APPROVING', 'INVALID']]];
        $map['name'] = ['neq', ''];
        $map[] = '`name` is not null';
        $map['_logic'] = 'and';
        $where['_complex'] = $map;
        $count = $this
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 询单数量
     * @return mix
     * @author zyg
     */
    public function getInquiryCount(array $condition, $supplier_id = null) {

        $final_quote_item_model = new FinalQuoteItemModel();
        $supplier_model = new SupplierModel();
        $supplier_table = $supplier_model->getTableName();
        $final_where = ['fqi.supplier_id' => ['gt', 0], 'fqi.deleted_flag' => 'N', 'fqi.status' => 'VALID', 's.id' => ['gt', 0], 's.deleted_flag' => 'N',];
        if ($supplier_id) {
            $final_where['fqi.supplier_id'] = $supplier_id;
        }
        $inquiryids = $final_quote_item_model
                        ->alias('fqi')
                        ->join($supplier_table . ' s on s.id=fqi.supplier_id')
                        ->field('fqi.inquiry_id')
                        ->where($final_where)->group('fqi.inquiry_id')->select();
        $inquiry_ids = [];


        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        if (empty($inquiry_ids)) {
            return 0;
        }
        $marketareacountry_model = new MarketAreaCountryModel();

        $marketareacountry_table = $marketareacountry_model->getTableName();
        $where = ['i.deleted_flag' => 'N', 'i.status' => 'QUOTE_SENT', 'i.quote_status' => 'COMPLETED',
            'i.id' => ['in', $inquiry_ids],];

//按区域筛选
        if (!empty($condition['area_bn'])) {
            $where['mac.market_area_bn'] = ['in', [trim($condition['area_bn'])]];
        } else {
            $where['mac.market_area_bn'] = ['in', $this->areas];
        }

        $inquiry_model = new InquiryModel();
        $count = $inquiry_model
                ->alias('i')
                ->join($marketareacountry_table . ' mac on  mac.country_bn=i.country_bn  ')
                ->where($where)
                ->count();

        return $count > 0 ? $count : 0;
    }

    /**
     * 询单列表
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function getInquirysBySupplierId($supplier_id, $condition) {
        list($offset, $length) = $this->_getPage($condition);
        $final_quote_item_model = new FinalQuoteItemModel();
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where(['supplier_id' => $supplier_id,
                            'deleted_flag' => 'N',
                            'status' => 'VALID',
                        ])->group('inquiry_id')->select();
        $inquiry_ids = [];
        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }


        if (empty($inquiry_ids)) {
            return null;
        }
        $where = ['deleted_flag' => 'N', 'status' => 'QUOTE_SENT', 'quote_status' => 'COMPLETED', 'id' => ['in', $inquiry_ids],];

//按区域筛选
        if (!empty($condition['area_bn'])) {
            $where['area_bn'] = ['in', [trim($condition['area_bn'])]];
        } else {
            $where['area_bn'] = ['in', $this->areas];
        }

        $inquiry_model = new InquiryModel();
        $list = $inquiry_model
                ->field('id as inquiry_id,inquiry_no,serial_no,created_at')
                ->where($where)
                ->order('created_at ASC')
                ->limit($offset, $length)
                ->select();

        return $list;
    }

    /**
     * 询单明细
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function Info($supplier_id) {

        $info = $this
                ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where(['id' => $supplier_id])
                ->find();

        return $info;
    }

    /**
     * 导出询单列表
     * @return mix
     * @author zyg
     */
    public function Inquiryexport($where) {
        $country_model = new CountryModel();
        $country_table = $country_model->getTableName(); //国家表
        $market_area_country_model = new MarketAreaCountryModel();
        $market_area_country_table = $market_area_country_model->getTableName(); //国家区域关系表
        $market_area_model = new MarketAreaModel();
        $market_area_table = $market_area_model->getTableName();   //营销区域表
        $employee_model = new EmployeeModel();
        $employee_table = $employee_model->getTableName(); //管理员表
        $org_model = new OrgModel(); //
        $org_table = $org_model->getTableName(); //组织表

        /*         * **报价单** */
        $quote_model = new QuoteModel();
        $quote_table = $quote_model->getTableName(); //最终报价单明细



        /*         * **报价单** */
        $field = 'i.serial_no,qt.sku,';
        $field .= '(case i.buyer_oil WHEN \'Y\' THEN \'是\' '
                . 'WHEN \'N\' THEN \'否\' '
                . 'else  \'否\' END ) '
                . ' as buyer_oil,';
        $field .= '(select country.`name` from ' . $country_table . ' as country where country.bn=i.country_bn and country.lang=\'zh\' group by country.bn) as country_name ,'; //国家名称
        $field .= '(select ma.`name` from ' . $country_table . ' c left join ' . $market_area_country_table . ' mac'
                . ' on mac.country_bn=c.bn '
                . ' left join ' . $market_area_table . ' ma on ma.bn=mac.market_area_bn '
                . 'where c.bn=i.country_bn and ma.lang=\'zh\' group by ma.bn) as market_area_name ,'; //营销区域名称
        $field .= '(select `name` from ' . $org_table . ' where i.org_id=id ) as org_name,'; //事业部
        $field .= 'if((select id from ' . $org_table . ' where i.org_id=id and org_node = \'erui\' and deleted_flag = \'N\') > 0, \'Y\', \'N\') as org_is_erui,'; //事业部是否易瑞
        $field .= 'i.proxy_no,i.buyer_code,i.project_name,i.project_basic_info,it.name_zh,it.name,it.model,it.qty,it.unit,';
        $field .= 'if(i.proxy_flag=\'Y\',\'是\',\'否\') as proxy_flag,';
        $field .= 'if(i.kerui_flag=\'Y\',\'是\',\'否\') as keruiflag,';
        $field .= 'if(i.bid_flag=\'Y\',\'是\',\'否\') as bidflag ,';
        $field .= 'i.quote_deadline,i.obtain_id,qt.supplier_id,qt.purchase_price_cur_bn,it.material_cat_no,qt.org_id as qt_org_id,';
        $field .= '(select q.gross_profit_rate from ' . $quote_table . ' q where q.inquiry_id=i.id) as gross_profit_rate,'; //毛利率
        $field .= '(select q.exchange_rate from ' . $quote_table . ' q where q.inquiry_id=i.id) as exchange_rate,'; //汇率



        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_item_model = new InquiryItemModel();
        $inquiry_item_table = $inquiry_item_model->getTableName(); //询单项明细表
        /*         * *************-----------询单项明细结束------------------- */

        /*         * *************-----------询单项明细开始------------------- */

        $inquiry_check_log_table = ( new InquiryCheckLogModel())->getTableName(); //询单项明细表
        $inquiry_check_log_sql = '(select max(out_at) from ' . $inquiry_check_log_table . ' where inquiry_id=i.id';

        $inquiry_check_minlog_sql = '(select min(out_at) from ' . $inquiry_check_log_table . ' where inquiry_id=i.id';
        $inquiry_check_in_log_sql = '(select min(into_at) from ' . $inquiry_check_log_table . ' where inquiry_id=i.id';
        $inquiry_check_max_in_log_sql = '(select max(out_at) from ' . $inquiry_check_log_table . ' where inquiry_id=i.id';


        $field .= $inquiry_check_in_log_sql . ' and in_node=\'BIZ_DISPATCHING\' group by inquiry_id) as inflow_time,'; //转入日期
        $field .= $inquiry_check_minlog_sql . ' and out_node=\'BIZ_DISPATCHING\' group by inquiry_id) as inflow_time_out,'; //转入日期

        $field .= $inquiry_check_max_in_log_sql . ' and in_node=\'CLARIFY\' group by inquiry_id) as max_inflow_time,'; //澄清日期
        $field .= $inquiry_check_log_sql . ' and out_node in(\'BIZ_DISPATCHING\',\'CC_DISPATCHING\' ) group by inquiry_id) as max_inflow_time_out,'; //最后一次流入事业部分单员时间

        $field .= $inquiry_check_log_sql . ' and in_node=\'BIZ_QUOTING\' group by inquiry_id) as bq_time,'; //事业部报价日期
        $field .= $inquiry_check_log_sql . ' and out_node=\'LOGI_DISPATCHING\' group by inquiry_id) as ld_time,'; //物流接收日期
        $field .= $inquiry_check_log_sql . ' and in_node=\'LOGI_QUOTING\' group by inquiry_id) as la_time,'; //物流报出日期
        $field .= $inquiry_check_log_sql . ' and in_node=\'MARKET_APPROVING\' group by inquiry_id) as qs_time,'; //报出日期
        /*         * *************-----------询单项明细结束------------------- */
        $field .= 'i.created_at,it.category,qt.reason_for_no_quote,i.id as inquiry_id,'; //报价用时 为qs_time-created_at 或当前时间-created_at;

        $employee_sql = '(select `name` from ' . $employee_table . ' where deleted_flag=\'N\' ';
        $field .= $employee_sql . ' AND id=i.agent_id)as agent_name,'; //市场负责人
        $field .= $employee_sql . ' AND id=i.quote_id)as quote_name,'; //商务技术部报价人
        $field .= $employee_sql . ' AND id=i.check_org_id)as check_org_name,'; //事业部负责人
        $field .= $employee_sql . ' AND id=i.created_by)as created_by_name,'; //询单创建人


        $field .= ' qt.brand,qt.quote_unit,qt.purchase_unit_price,qt.purchase_unit_price*qt.quote_qty as total,'; //total厂家总价（元）
        $field .= ' fqt.quote_unit_price,fqt.total_quote_price,fqt.total_exw_price,fqt.exw_unit_price,fqt.exw_cur_bn,(fqt.total_quote_price+fqt.total_logi_fee+fqt.total_bank_fee+fqt.total_insu_fee) as total_quoted_price,'; //报价总金额（美金）
        $field .= 'qt.gross_weight_kg,(qt.gross_weight_kg*qt.quote_qty) as total_kg,qt.package_size,qt.package_mode,qt.quote_qty,';
        $field .= 'qt.delivery_days,qt.period_of_validity,i.trade_terms_bn,qt.total_exw_price as qt_total_exw_price,qt.exw_unit_price as qt_exw_unit_price,qt.exw_cur_bn as qt_exw_cur_bn,';
        $field .= '(case i.status WHEN \'BIZ_DISPATCHING\' THEN \'事业部分单员\' '
                . 'WHEN \'CLARIFY\' THEN \'项目澄清\' '
                . 'WHEN \'REJECT_MARKET\' THEN \'驳回市场\' '
                . 'WHEN \'REJECT_CLOSE\' THEN \'驳回市场关闭\' '
                . 'WHEN \'CC_DISPATCHING\' THEN \'易瑞客户中心\' '
                . 'WHEN \'BIZ_QUOTING\' THEN \'事业部报价\' '
                . 'WHEN \'LOGI_DISPATCHING\' THEN \'物流分单员\' '
                . 'WHEN \'LOGI_QUOTING\' THEN \'物流报价\' '
                . 'WHEN \'LOGI_APPROVING\' THEN \'物流审核\' '
                . 'WHEN \'BIZ_APPROVING\' THEN \'事业部核算\' '
                . 'WHEN \'MARKET_APPROVING\' THEN \'市场主管审核\' '
                . 'WHEN \'MARKET_CONFIRMING\' THEN \'市场确认\' '
                . 'WHEN \'QUOTE_SENT\' THEN \'报价单已发出\' '
                . 'WHEN \'INQUIRY_CLOSED\' THEN \'报价关闭\' '
                . 'WHEN \'INQUIRY_CONFIRM\' THEN \'询单确认\' '
                . ' END) as istatus,';

        $field .= '(case i.quote_status WHEN \'NOT_QUOTED\' THEN \'未报价\' '
                . 'WHEN \'ONGOING\' THEN \'报价中\' '
                . 'WHEN \'QUOTED\' THEN \'已报价\' '
                . 'WHEN \'COMPLETED\' THEN \'已完成\' '
                . ' END) as iquote_status,i.quote_notes';
        /*         * ****报价单明细** */

        $quote_item_table = (new QuoteItemModel())->getTableName(); //报价单明细表
        /*         * ****报价单明细** */

        /*         * **最终报价单明细** */

        $final_quote_item_table = (new FinalQuoteItemModel())->getTableName(); //最终报价单明细

        /*         * **最终报价单明细** */


        $inquiry_model = new InquiryModel();

        $list = $inquiry_model->alias('i')
                ->join($inquiry_item_table . ' as it on it.deleted_flag=\'N\' and it.inquiry_id=i.id', 'left')
                ->join($quote_item_table . ' as qt on qt.deleted_flag=\'N\' and qt.inquiry_id=i.id and qt.inquiry_item_id=it.id', 'left')
                ->join($final_quote_item_table . ' as fqt on fqt.deleted_flag=\'N\' and fqt.inquiry_id=i.id and fqt.inquiry_item_id=it.id and fqt.quote_item_id=qt.id', 'left')
                ->field($field)
                ->where($where)
                ->select();


        $this->_setSupplierName($list);
        $this->_setquoted_time($list);
        $this->_setProductName($list);


        (new MaterialCatModel())->setMaterialCat($list, 'zh');
        (new OrgModel())->setOrgName($list, 'qt_org_id', 'qt_org_name');

        $this->_setCalculatePrice($list);
        $this->_setBizDespatching($list);
        $this->_setOilFlag($list);
        $this->_setObtainInfo($list);
        $this->_setClarifyTime($list);
        $this->_setQuoteSpendTime($list);
        $this->_resetListData($list);

        return $this->_createXls($list);
    }

    /*
     * 对应表
     *
     */

    private function _getKeys() {
        return json_decode('{"A":["sequence_no","序号"],"B":["serial_no","报价单号"],"C":["country_name","询价单位"],"D":["market_area_name","所属地区部"],"E":["org_name","事业部"],"F":["ie_erui","是否走易瑞"],"G":["buyer_code","客户名称或代码"],"H":["proxy_flag","是否代理商获取"],"I":["proxy_no","代理商代码"],"J":["project_basic_info","客户及项目背景描述"],"K":["name_zh","品名中文"],"L":["name","品名外文"],"M":["product_name","产品名称"],"N":["supplier_name","供应商"],"O":["model","规格"],"P":[null,"图号"],"Q":["qty","数量"],"R":["unit","单位"],"S":["buyer_oil","是否油气客户"],"T":["oil_flag","油气\/非油气"],"U":[null,"平台产品分类"],"V":["category","产品分类"],"W":["material_cat_name","物料分类"],"X":["qt_org_name","产品所属事业部"],"Y":["keruiflag","是否科瑞设备用配件"],"Z":["bidflag","是否投标"],"AA":["created_at","新建询单日期"],"AB":["inflow_time","转入日期"],"AC":["quote_deadline","需用日期"],"AD":["max_inflow_time_out","最后一次流入事业部分单员时间"],"AE":["max_inflow_time","澄清完成日期"],"AF":["bq_time","事业部报出日期"],"AG":["ld_time","物流接收日期"],"AH":["la_time","物流报出日期"],"AI":["qs_time","报出日期"],"AJ":["cc_dispatching_clarification_time","易瑞分单员发起的澄清用时（小时）"],"AK":["biz_dispatching_clarification_time","事业部分单员发起的澄清用时（小时）"],"AL":["biz_quoting_clarification_time","事业部报价人发起的澄清用时（小时）"],"AM":["logi_dispatching_clarification_time","物流分单员发起的澄清用时（小时）"],"AN":["logi_quoting_clarification_time","物流报价人发起的澄清用时（小时）"],"AO":["logi_approving_clarification_time","物流审核发起的澄清用时（小时）"],"AP":["biz_approving_clarification_time","事业部核算发起的澄清用时（小时）"],"AQ":["market_approving_clarification_time","事业部审核发起的澄清用时（小时）"],"AR":["clarification_time","项目澄清时间(小时)"],"AS":["real_quoted_time","真实报价用时(去处澄清时间](小时)"],"AT":["whole_quoted_time","整体报价时间(小时)"],"AU":["cc_quoted_time","易瑞商务技术报价用时(小时)"],"AV":["biz_quoted_time","事业部商务技术报价用时(小时)"],"AW":["logi_quoted_time","物流报价时间(小时)"],"AX":["obtain_org_name","获单主体单位"],"AY":["obtain_name","获取人"],"AZ":["created_by_name","询单创建人"],"BA":["agent_name","市场负责人"],"BB":["biz_despatching","事业部分单人"],"BC":["quote_name","商务技术部报价人"],"BD":["check_org_name","事业部负责人"],"BE":["brand","产品品牌"],"BF":["supplier_name","报价单位"],"BG":[null,"供应商报价人"],"BH":[null,"报价人联系方式"],"BI":["purchase_unit_price","厂家单价"],"BJ":["purchase_price_cur_bn","币种"],"BK":["total","厂家总价"],"BL":["purchase_price_cur_bn","币种"],"BM":["gross_profit_rate","利润率"],"BN":["quote_unit_price","报价单价"],"BO":["quote_price_cur_bn","币种"],"BP":["total_exw_price","商务报出EXW价格合计"],"BQ":["total_quote_price_cur_bn","币种"],"BR":["total_quoted_price_usd","商务技术报价"],"BS":["gross_weight_kg","单重(kg]"],"BT":["total_kg","总重(kg)"],"BU":["package_size","包装体积(mm)"],"BV":["package_mode","包装方式"],"BW":["delivery_days","交货期（天）"],"BX":["period_of_validity","有效期（天）"],"BY":["trade_terms_bn","贸易术语"],"BZ":["istatus","最新进度及解决方案"],"CA":["iquote_status","报价后状态"],"CB":["quote_notes","备注"],"CC":["reason_for_no_quote","未报价分析"]}', true);
    }

    private function _createXls($list, $name = '导出的询报价单') {
        $tmpDir = MYPATH . DS . 'public' . DS . 'tmp' . DS;
        rmdir($tmpDir);
        $dirName = $tmpDir . date('YmdH', time());
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }
        PHPExcel_Settings::setCacheStorageMethod(
                PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array('memoryCacheSize' => '512MB'));
        $objPHPExcel = new PHPExcel();

        $objSheet = $objPHPExcel->setActiveSheetIndex(0);    //当前sheet
        $objSheet->setTitle('询报价单表');
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        $keys = $this->_getKeys();
        foreach ($keys as $rowname => $key) {
            $objSheet->setCellValue($rowname . '1', $key[1]);
        }
        foreach ($list as $j => $item) {
            foreach ($keys as $rowname => $key) {
                if ($key && isset($item)) {
                    $value = isset($item[$key[0]]) ? $item[$key[0]] : null;
                    if (strpos($value, '=') === 0) {
                        $value = "'" . $value;
                    }
                    $objSheet->setCellValue($rowname . ($j + 2), $value);
                } else {
                    $objSheet->setCellValue($rowname . ($j + 2), '');
                }
            }
        }
        $objSheet->freezePaneByColumnAndRow(2, 2);
        $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '00000000'),],],];
        $objSheet->getStyle('A1:CA' . ($j + 2))->applyFromArray($styleArray);
        $objSheet->getStyle('A1:CA' . ($j + 2))->getAlignment()->setShrinkToFit(true); //字体变小以适应宽
        $objSheet->getStyle('A1:CA' . ($j + 2))->getAlignment()->setWrapText(true); //自动换行
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $file = $dirName . DS . $name . date('YmdHi') . '.xls';
        $objWriter->save($file);
        if (file_exists($file)) {
//把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $file;
            $data['type'] = 'application/xls';
            $data['name'] = $name . date('YmdHi') . '.xls';
            $fileId = postfile($data, $url);
            if ($fileId) {
                unlink($file);
                return array('url' => rtrim($fastDFSServer, '/') . '/' . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $file . ' 上传到FastDFS失败', Log::ERR);
            return false;
        }
        return false;
    }

//产品名称、规格、价格、供应商等信息

    private function _setSupplierName(&$list) {
        $supplier_ids = [];
        foreach ($list as $item) {
            if ($item['supplier_id']) {
                $supplier_ids[] = $item['supplier_id'];
            }
        }
        $suppliers_model = new SuppliersModel();
        $supplier_names = $suppliers_model->getSupplierNameByIds($supplier_ids);

        foreach ($list as $key => $item) {
            if ($item['supplier_id'] && isset($supplier_names[$item['supplier_id']])) {
                $list[$key]['supplier_name'] = $supplier_names[$item['supplier_id']]['name'];
            }
        }
    }

    private function _setBizDespatching(&$list) {
        $inquiry_ids = [];

        foreach ($list as $item) {
            if ($item['inquiry_id']) {
                $inquiry_ids[] = $item['inquiry_id'];
            }
        }
        $inquiry_check_log_model = new InquiryCheckLogModel();

        $employee_model = new EmployeeModel();
        $employee_table = $employee_model->getTableName(); //管理员表
        $biz_despatchings = $inquiry_check_log_model->alias('icl')
                ->field('icl.inquiry_id,group_concat(DISTINCT `e`.`name`) as biz_despatching')
                ->join($employee_table . ' e on e.id=icl.agent_id')
                ->where(['icl.inquiry_id' => ['in', $inquiry_ids ?: ['-1']], 'out_node' => 'BIZ_DISPATCHING'])
                ->group('icl.inquiry_id')
                ->select();
        $bizdespatchings = [];
        if ($biz_despatchings) {
            foreach ($biz_despatchings as $biz_despatching) {
                if ($biz_despatching['inquiry_id']) {
                    $bizdespatchings[$biz_despatching['inquiry_id']] = $biz_despatching['biz_despatching'];
                }
            }
        }
        foreach ($list as $key => $item) {
            if ($item['inquiry_id'] && isset($bizdespatchings[$item['inquiry_id']])) {
                $list[$key]['biz_despatching'] = $bizdespatchings[$item['inquiry_id']];
            }
        }
    }

    private function _setProductName(&$list) {
        $skus = [];

        foreach ($list as $item) {
            if (!empty($item['sku'])) {
                $skus[] = $item['sku'];
            }
        }
        $goods_model = new GoodsModel();
        $product_names = $goods_model->getProductNamesAndMaterialCatNoBySkus($skus);

        foreach ($list as $key => $item) {
            if (!empty($item['sku']) && isset($product_names[$item['sku']])) {
                $list[$key]['product_name'] = $product_names[$item['sku']]['product_name'];
                // $list[$key]['material_cat_no'] = $product_names[$item['sku']]['material_cat_no'];
            }
        }
    }

    /*
     * 获取人 获取单位
     * @author  zhongyg
     * @param array $list // 询单总行信息
     * @return
     * @date    2018-02-011 11:45:09
     * @version V2.0
     * @desc  获取人信息处理
     */

    private function _setObtainInfo(&$list) {
        $obtain_ids = [];
        foreach ($list as $item) {
            if ($item['obtain_id']) {
                $obtain_ids[] = $item['obtain_id'];
            }
        }
        $employee_model = new EmployeeModel();
        $org_member_model = new OrgMemberModel();
        $users = $employee_model->getNamesByids($obtain_ids);
        $orgs = $org_member_model->getOrgNamesByemployeeids($obtain_ids);

        foreach ($list as $key => $item) {
            $item['obtain_id'] && isset($users[$item['obtain_id']]) ? $list[$key]['obtain_name'] = $users[$item['obtain_id']] : $list[$key]['obtain_name'] = '';
            $item['obtain_id'] && isset($orgs[$item['obtain_id']]) ? $list[$key]['obtain_org_name'] = $orgs[$item['obtain_id']] : $list[$key]['obtain_org_name'] = '';
        }
    }

    /**
     * @desc 设置项目澄清时间
     *
     * @param array $list  询单列表信息
     * @author liujf
     * @time 2018-02-09
     */
    private function _setClarifyTime(&$list) {
        $inquiryCheckLogModel = new InquiryCheckLogModel();

        $nowTime = time();
        $clarifyMapping = [
            'BIZ_DISPATCHING' => 'biz_dispatching_clarification_time',
            'CC_DISPATCHING' => 'cc_dispatching_clarification_time',
            'BIZ_QUOTING' => 'biz_quoting_clarification_time',
            'LOGI_DISPATCHING' => 'logi_dispatching_clarification_time',
            'LOGI_QUOTING' => 'logi_quoting_clarification_time',
            'LOGI_APPROVING' => 'logi_approving_clarification_time',
            'BIZ_APPROVING' => 'biz_approving_clarification_time',
            'MARKET_APPROVING' => 'market_approving_clarification_time'
        ];
        $clarifyNode = array_keys($clarifyMapping);

        $inquiry_ids = [];
        foreach ($list as $item) {
            if (!empty($item['inquiry_id']) && !in_array($item['inquiry_id'], $inquiry_ids)) {
                $inquiry_ids[] = $item['inquiry_id'];
            }
        }
        $clarification = [];

        // 各环节的项目澄清时间列表
        $clarifys = $inquiryCheckLogModel
                ->field('out_node, (UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) AS clarify_time,inquiry_id')
                ->where(['in_node' => 'CLARIFY',
                    'out_node' => ['in', $clarifyNode],
                    'inquiry_id' => ['in', !empty($inquiry_ids) ? $inquiry_ids : ['-1']]
                ])
                ->order('id ASC')
                ->select();


        $clarifyList = [];
        foreach ($clarifys as $clarify) {
            $clarifyList[$clarify['inquiry_id']][] = $clarify;
        }
        $node_datas = $inquiryCheckLogModel
                ->field('max(id) as max_id')
                ->where(['inquiry_id' => ['in', !empty($inquiry_ids) ? $inquiry_ids : ['-1']]])
                ->group('inquiry_id')
                ->order('id DESC')
                ->select();
        $node_ids = [];
        foreach ($node_datas as $node_data) {
            $node_ids[] = $node_data['max_id'];
        }
        $nodeDatas = [];
        $nodes = $inquiryCheckLogModel
                ->field('inquiry_id,in_node, out_node, UNIX_TIMESTAMP(out_at) AS out_time')
                ->where(['id' => ['in', !empty($node_ids) ? $node_ids : ['-1']]])
                ->group('inquiry_id')
                ->order('id DESC')
                ->select();
        foreach ($nodes as $nodedata) {
            $nodeDatas[$nodedata['inquiry_id']][] = $nodedata;
        }



        foreach ($inquiry_ids as $inquiry_id) {
            $item = [];

            foreach ($clarifyMapping as $v) {
// 项目澄清时间初始化
                $item[$v] = '';
            }
            $item['clarification_time'] = '';

            if (!empty($clarifyList[$inquiry_id])) {
                foreach ($clarifyList[$inquiry_id] as $clarify) {// 计算各环节的项目澄清时间
                    if (!empty($item[$clarifyMapping[$clarify['out_node']]])) {
                        $item[$clarifyMapping[$clarify['out_node']]] += $clarify['clarify_time'];
                    } else {
                        $item[$clarifyMapping[$clarify['out_node']]] = $clarify['clarify_time'];
                    }
                }
            }
            $nodeData = !empty($nodeDatas[$inquiry_id]) ? $nodeDatas[$inquiry_id] : [];

            $lastClarifyTime = '';
            if (!empty($nodeData['out_node']) && $nodeData['out_node'] == 'CLARIFY' && in_array($nodeData['in_node'], $clarifyNode)) {
                $lastClarifyTime = $nowTime - $nodeData['out_time'];
                $item[$clarifyMapping[$nodeData['in_node']]] += $lastClarifyTime;
            }
            foreach ($clarifyMapping as $v) {
                if (!empty($item[$v]) && $item[$v] > 0) {
                    $item['clarification_time'] = isset($item['clarification_time']) ? ($item['clarification_time'] + $item[$v]) : $item[$v];
                    $item[$v] = number_format($item[$v] / 3600, 2);
                }
            }


// 总的项目澄清时间
            if ($item['clarification_time'] > 0) {
                $item['clarification_time'] = number_format($item['clarification_time'] / 3600, 2);
            }
//}
            $clarification[$inquiry_id] = $item;
        }



        foreach ($list as $key => $item) {

            if (!empty($item['inquiry_id']) && !empty($clarification[$item['inquiry_id']])) {
                foreach ($clarifyMapping as $v) {
                    $item[$v] = !empty($clarification[$item['inquiry_id']][$v]) ? $clarification[$item['inquiry_id']][$v] : '';
                }

                $item['clarification_time'] = !empty($clarification[$item['inquiry_id']]['clarification_time']) ? $clarification[$item['inquiry_id']]['clarification_time'] : 0;
            } elseif (!empty($item['inquiry_id'])) {
                foreach ($clarifyMapping as $v) {
                    $item[$v] = '';
                }
                $item['clarification_time'] = '';
            }
            $list[$key] = $item;
        }
    }

    /**
     * @desc 设置报价相关用时
     *
     * @param array $list  询单列表信息
     * @author liujf
     * @time 2018-04-03
     */
    private function _setQuoteSpendTime(&$list) {
        $inquiryCheckLogModel = new InquiryCheckLogModel();

        $nowTime = time();
        $quoteMapping = [
            'BIZ_DISPATCHING' => 'biz_dispatching_quoted_time',
            'CC_DISPATCHING' => 'cc_dispatching_quoted_time',
            'BIZ_QUOTING' => 'biz_quoting_quoted_time',
            'LOGI_DISPATCHING' => 'logi_dispatching_quoted_time',
            'LOGI_QUOTING' => 'logi_quoting_quoted_time',
            'LOGI_APPROVING' => 'logi_approving_quoted_time',
            'BIZ_APPROVING' => 'biz_approving_quoted_time',
            'MARKET_APPROVING' => 'market_approving_quoted_time'
        ];
        $quoteNode = array_keys($quoteMapping);

        $inquiry_ids = [];
        $real_items = [];
        foreach ($list as $item) {
            if (!empty($item['inquiry_id']) && !in_array($item['inquiry_id'], $inquiry_ids)) {
                $inquiry_ids[] = $item['inquiry_id'];
                $real_items [$item['inquiry_id']] = [
                    'qs_time' => $item['qs_time'],
                    'org_is_erui' => $item['org_is_erui'],
                    'inflow_time' => $item['inflow_time'],
                ];
            }
        }

        $nodes = $inquiryCheckLogModel->field('inquiry_id,in_node, (UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) AS quote_time')
                ->where(['inquiry_id' => ['in', !empty($inquiry_ids) ? $inquiry_ids : ['-1']],
                    'in_node' => ['in', $quoteNode]])
                ->select();
        $spendList = [];
        foreach ($nodes as $nodedata) {
            $spendList[$nodedata['inquiry_id']][] = $nodedata;
        }
        $quoted_times = [];

        foreach ($inquiry_ids as $inquiry_id) {
            $item = [];

            $quoteTime = [];
            foreach ($quoteMapping as $v) {
// 报价用时初始化
                $item[$v] = '';
            }
// 各环节的报价用时列表

            foreach ($spendList[$inquiry_id] as $spend) {
// 计算各环节的报价用时
                if (!empty($quoteTime[$quoteMapping[$spend['in_node']]])) {
                    $quoteTime[$quoteMapping[$spend['in_node']]] += $spend['quote_time'];
                } else {
                    $quoteTime[$quoteMapping[$spend['in_node']]] = $spend['quote_time'];
                }
            }


// 物流报价用时
            $logiSpend = intval($quoteTime['logi_dispatching_quoted_time']) + intval($quoteTime['logi_quoting_quoted_time']) + intval($quoteTime['logi_approving_quoted_time']);
            $item['logi_quoted_time'] = number_format($logiSpend / 3600, 2);
            $tmpDispatchingSpend = intval($quoteTime['biz_quoting_quoted_time']) + intval($quoteTime['biz_approving_quoted_time']) + intval($quoteTime['market_approving_quoted_time']);
            if ($real_items[$inquiry_id]['org_is_erui'] == 'Y') {
// 易瑞商务技术报价用时
                $ccSpend = $quoteTime['cc_dispatching_quoted_time'] + $quoteTime['biz_dispatching_quoted_time'] + $tmpDispatchingSpend;
                $item['cc_quoted_time'] = number_format($ccSpend / 3600, 2);
// 事业部商务技术报价用时
                $item['biz_quoted_time'] = 0;
                $realSpend = $ccSpend + $logiSpend;
            } else {
                $item['cc_quoted_time'] = 0;
                $bizSpend = $quoteTime['biz_dispatching_quoted_time'] + $tmpDispatchingSpend;
                $item['biz_quoted_time'] = number_format($bizSpend / 3600, 2);
                $realSpend = $bizSpend + $logiSpend;
            }
// 真实报价用时
            $item['real_quoted_time'] = $logiSpend > 0 ? number_format($realSpend / 3600, 2) : '';
// 整体报价用时
            $qsSpend = strtotime($real_items[$inquiry_id]['qs_time']);
            $wholeSpend = ($qsSpend > 0 ? $qsSpend : $nowTime) - strtotime($real_items[$inquiry_id]['inflow_time']);
            $item['whole_quoted_time'] = number_format($wholeSpend / 3600, 2);
            $quoted_times[$inquiry_id] = $item;
        }

        $inquiry_quoted_time_keys = [
            'whole_quoted_time',
            'real_quoted_time',
            'biz_quoted_time',
            'logi_quoted_time',
            'cc_quoted_time'];
        foreach ($list as $key => $item) {
            if (!empty($item['inquiry_id']) && !empty($quoted_times[$item['inquiry_id']])) {

                foreach ($inquiry_quoted_time_keys as $v) {

                    $item[$v] = !empty($quoted_times[$item['inquiry_id']][$v]) ? $quoted_times[$item['inquiry_id']][$v] : '';
                }
            } else {
                foreach ($inquiry_quoted_time_keys as $v) {
                    $item[$v] = 0;
                }
            }



            $list[$key] = $item;
        }
    }

    /**
     * @desc 数据汇总并重新组织数据
     *
     * @param array $list  询单列表信息
     * @author liujf
     * @time 2018-05-16
     */
    private function _resetListData(&$list) {


        $inquiryItemModel = new InquiryItemModel();
        $tmpList = $newList = $serialNoList = [];
        $i = 0;
        $inquiry_ids = [];
        foreach ($list as $item) {
            !empty($item['inquiry_id']) && !in_array($item['inquiry_id'], $inquiry_ids) ? $inquiry_ids[] = $item['inquiry_id'] : null;
        }

        if ($inquiry_ids) {
            $category_lists = $inquiryItemModel
                    ->field('COUNT(id) AS count, category,inquiry_id')
                    ->where(['inquiry_id' => ['in', $inquiry_ids], 'category' => ['neq', ''], 'deleted_flag' => 'N'])
                    ->group('inquiry_id,category')->order('count DESC')
                    ->select();

            $categorys = [];
            foreach ($category_lists as $category) {
                if (empty($categorys[$category['inquiry_id']])) {
                    $categorys[$category['inquiry_id']] = $category['category'];
                }
            }
            $where = ['deleted_flag' => 'N', 'inquiry_id' => ['in', $inquiry_ids]];
            $quote_model = new QuoteModel();
            $final_quote_model = new FinalQuoteModel();
            $quotes = $quote_model->field('inquiry_id,total_logi_fee,total_quote_price,total_weight,total_exw_price,package_volumn,package_mode,delivery_period,period_of_validity')
                    ->where($where)
                    ->select();
            $final_quotes = $final_quote_model->field('inquiry_id,total_logi_fee,total_quote_price,total_exw_price')
                    ->where($where)
                    ->select();
            $quoteprices = [];
            $final_quoteprices = [];
            foreach ($quotes as $quote) {
                $quoteprices[$quote['inquiry_id']] = $quote;
            }
            foreach ($final_quotes as $final_quote) {
                $final_quoteprices[$final_quote['inquiry_id']] = $final_quote;
            }
        }

        foreach ($list as $item) {
            $serialNo = $item['inquiry_id'];
            if (empty($tmpList[$serialNo])) {
                $tmpList[$serialNo] = $item;
                $tmpList[$serialNo]['name_zh'] = $item['project_name'];
                $tmpList[$serialNo]['name'] = '';
                $tmpList[$serialNo]['supplier_name'] = '';
                $tmpList[$serialNo]['model'] = '';
                $tmpList[$serialNo]['qty'] = '1';
                $tmpList[$serialNo]['unit'] = '批';
                $tmpList[$serialNo]['category'] = isset($categorys[$item['inquiry_id']]) ? $categorys[$item['inquiry_id']] : '';
                $tmpList[$serialNo]['brand'] = '';
                $tmpList[$serialNo]['purchase_unit_price'] = '';
                $tmpList[$serialNo]['total'] = '';
                $tmpList[$serialNo]['quote_unit_price'] = '';
                $tmpList[$serialNo]['quote_price_cur_bn'] = 'USD';
                $tmpList[$serialNo]['total_quote_price'] = '';
                $tmpList[$serialNo]['total_quoted_price_usd'] = '';
                $tmpList[$serialNo]['gross_weight_kg'] = '';
                $tmpList[$serialNo]['total_kg'] = $item['total_kg'];
                $tmpList[$serialNo]['package_size'] = $item['package_size'];
                $tmpList[$serialNo]['package_mode'] = '';
                $tmpList[$serialNo]['material_cat_name'] = '';
                $tmpList[$serialNo]['qt_org_name'] = '';
            } else {

                $tmpList[$serialNo]['total_kg'] += $item['total_kg'];
                $tmpList[$serialNo]['package_size'] += $item['package_size'];
                $tmpList[$serialNo]['package_mode'] = '';
            }
        }

        foreach ($list as $item) {
            $serialNo = $item['inquiry_id'];
            if (!in_array($serialNo, $serialNoList)) {
                $tmpList[$serialNo]['sequence_no'] = ++$i;

                if (!in_array($item['istatus'], ['市场确认', '报价单已发出', '报价关闭', '询单确认'])) {
                    $tmpList[$serialNo]['quote_unit_price'] = '';
                    $tmpList[$serialNo]['total_quote_price'] = '';
                    $tmpList[$serialNo]['total_quoted_price_usd'] = '';
                } else {
                    if (isset($final_quoteprices[$serialNo]['total_quote_price'])) {
                        $tmpList[$serialNo]['total_quoted_price_usd'] = $final_quoteprices[$serialNo]['total_quote_price'];
                        $tmpList[$serialNo]['total_quote_price_cur_bn'] = 'USD';
                    } elseif (isset($quoteprices[$serialNo]['total_quote_price'])) {

                        $tmpList[$serialNo]['total_quoted_price_usd'] = $quoteprices[$serialNo]['total_quote_price'];
                        $tmpList[$serialNo]['total_quote_price_cur_bn'] = 'USD';
                    } else {

                        $tmpList[$serialNo]['total_quoted_price_usd'] = '';
                        $tmpList[$serialNo]['total_quote_price_cur_bn'] = 'USD';
                    }
                    if (isset($final_quoteprices[$serialNo]['total_exw_price'])) {
                        $tmpList[$serialNo]['total_exw_price'] = $final_quoteprices[$serialNo]['total_exw_price'];
                        $tmpList[$serialNo]['quote_price_cur_bn'] = 'USD';
                    } elseif (isset($quoteprices[$serialNo]['total_exw_price'])) {
                        $tmpList[$serialNo]['total_exw_price'] = $quoteprices[$serialNo]['total_exw_price'];
                        $tmpList[$serialNo]['quote_price_cur_bn'] = 'USD';
                    } else {
                        $tmpList[$serialNo]['total_exw_price'] = '';
                        $tmpList[$serialNo]['quote_price_cur_bn'] = 'USD';
                    }
                }

                $newList[] = $tmpList[$serialNo];
                $serialNoList[] = $serialNo;
            }
            $item['total_quote_price_cur_bn'] = $item['quote_price_cur_bn'];

            $newList[] = $item;
        }
        $list = $newList;
    }

    /**
     * @desc 获取美元兑换汇率
     *
     * @param string $cur 币种
     * @return float
     * @author liujf
     * @time 2018-05-16
     */
    private function _getRateUSD($cur) {


        if (empty($cur)) {
            return 1;
        } elseif (!empty($this->RateUSD[$cur])) {

            return $this->RateUSD[$cur];
        } else {
            $Rate = $this->_getRate($cur, 'USD');

            $this->RateUSD[$cur] = $Rate;
            return $Rate;
        }
    }

    /**
     * @desc 获取币种兑换汇率
     *
     * @param string $holdCur 持有币种
     * @param string $exchangeCur 兑换币种
     * @return float
     * @author liujf
     * @time 2018-05-16
     */
    private function _getRate($holdCur, $exchangeCur = 'CNY') {
        if (!empty($holdCur)) {
            if ($holdCur == $exchangeCur) {
                return 1;
            }
            $exchangeRateModel = new ExchangeRateModel();
            $exchangeRate = $exchangeRateModel->field('rate')
                            ->where(['cur_bn1' => $holdCur, 'cur_bn2' => $exchangeCur])
                            ->order('created_at DESC')->find();
            if (empty($exchangeRate)) {
                $exchangeRate = $exchangeRateModel->field('rate')
                                ->where(['cur_bn2' => $holdCur, 'cur_bn1' => $exchangeCur])
                                ->order('created_at DESC')->find();
                return 1 / $exchangeRate['rate'];
            }

            return $exchangeRate['rate'];
        } else {
            return 1;
        }
    }

    private function date_diff($datetime1, $datetime2) {
        $date_time2 = strtotime($datetime2);
        $date_time1 = strtotime($datetime1);
        $interval = ($date_time1 - $date_time2) / 3600;
        return $interval;
    }

    private function _setquoted_time(&$list) {
        foreach ($list as $key => $item) {
            $list[$key]['inflow_time'] = !empty($item['inflow_time']) ? $item['inflow_time'] : $item['inflow_time_out'];
//$list[$key]['max_inflow_time'] = !empty($item['max_inflow_time']) ? $item['max_inflow_time'] : $item['max_inflow_time_out'];
            if ($item['qs_time']) {
                $list[$key]['quoted_time'] = $this->date_diff($item['qs_time'], $item['created_at']);
            } else {
                $list[$key]['quoted_time'] = $this->date_diff(date('Y-m-d H:i:s'), $item['created_at']);
            }
        }
    }

    /*
     *     'AL' => ['purchase_unit_price', '厂家单价（元）'],
      'AM' => ['total', '厂家总价（元）'],
     */

    private function _setCalculatePrice(&$list) {
        $exchange_rates = [];
        foreach ($list as $key => $item) {
// 只在市场确认、报价单已发出、报价关闭环节显示报价金额

            if (!in_array($item['istatus'], ['市场确认', '报价单已发出', '报价关闭', '询单确认'])) {
                $list[$key]['total_exw_price'] = $list[$key]['quote_unit_price'] = $list[$key]['total_quote_price'] = $list[$key]['total_quoted_price_usd'] = '';
            } else {



                if (!empty($item['total_exw_price'])) {

                    $list[$key]['total_exw_price'] = $item['total_exw_price'];
                } elseif (empty($item['total_exw_price']) && $item['exw_unit_price'] > 0) {
                    $list[$key]['total_exw_price'] = $item['exw_unit_price'] * $item['quote_qty'];
                } elseif (!empty($item['qt_total_exw_price'])) {

                    $list[$key]['total_exw_price'] = $item['qt_total_exw_price'];
                } elseif (empty($item['qt_total_exw_price']) && $item['qt_exw_unit_price'] > 0) {

                    $list[$key]['total_exw_price'] = $item['qt_exw_unit_price'] * $item['quote_qty'];
                } elseif (!empty($exchange_rates[$item['purchase_price_cur_bn']])) {

                    $list[$key]['total_exw_price'] = $gross_profit_rate * $item['purchase_unit_price'] * $item['quote_qty'] * $exchange_rates[$item['purchase_price_cur_bn']];
                } else {

                    $exchange_rates[$item['purchase_price_cur_bn']] = $this->_getRateUSD($item['purchase_price_cur_bn']);
                    $list[$key]['total_exw_price'] = $gross_profit_rate * $item['purchase_unit_price'] * $item['quote_qty'] * $exchange_rates[$item['purchase_price_cur_bn']];
                }

                $gross_profit_rate = $item['gross_profit_rate'] / 100 + 1;
                if ($item['total_quote_price'] > 0) {
                    $list[$key]['quote_price_cur_bn'] = 'USD';
                    $list[$key]['total_quoted_price_usd'] = $item['total_quote_price'];
                    continue;
                } elseif ($item['quote_unit_price'] > 0) {
                    $list[$key]['quote_price_cur_bn'] = 'USD';
                    $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quoted_price'] = $item['quote_unit_price'] * $item['quote_qty'];
                    continue;
                } else {
                    $list[$key]['quote_unit_price'] = $gross_profit_rate * $item['purchase_unit_price'];
                    $list[$key]['quote_price_cur_bn'] = $item['purchase_price_cur_bn'];
                    $list[$key]['total_quote_price'] = $gross_profit_rate * $item['purchase_unit_price'] * $item['quote_qty'];
                }
                if ($item['purchase_price_cur_bn'] == 'USD') {
                    $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'];
                } else {
                    if ($item['exchange_rate'] && $item['exchange_rate'] > 1) {
                        $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] / $item['exchange_rate'];
                    } elseif ($item['exchange_rate']) {
                        $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] * $item['exchange_rate'];
                    } elseif (!empty($exchange_rates[$item['purchase_price_cur_bn']])) {
                        $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] * $exchange_rates[$item['purchase_price_cur_bn']];
                    } else {
                        $exchange_rates[$item['purchase_price_cur_bn']] = $this->_getRateUSD($item['purchase_price_cur_bn']);
                        $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] * $exchange_rates[$item['purchase_price_cur_bn']];
                    }
                }
            }
        }
    }

    /*
     * Description of 获取物料分类名称
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setOilFlag(&$arr) {
        if ($arr) {
            $oil_flags = ['石油专用管材', '钻修井设备', '固井酸化压裂设备', '采油集输设备', '石油专用工具', '石油专用仪器仪表', '油田化学材料',];
            $not_oil_flags = ['通用机械设备', '劳动防护用品', '消防、医疗产品', '电力电工设备', '橡塑产品', '钢材', '包装物', '杂品',];

            foreach ($arr as $key => $val) {
                if ($val['category'] && in_array($val['category'], $oil_flags)) {
                    $val['oil_flag'] = '油气';
                } elseif ($val['category'] && in_array($val['category'], $not_oil_flags)) {
                    $val['oil_flag'] = '非油气';
                } else {
                    $val['oil_flag'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /**
     * @desc 获取供应商和总数
     * @param array $attributes
     * @author 买买提
     * @time 2018-04-19
     * @return array
     */
    public function suppliersWithFilterAndTotals(array $attributes) {
        $where = $this->setSuppliersFilterConditions($attributes);
        $fields = 'id supplier_id,name supplier_name,supplier_no';

        $currentPage = empty($attributes['current_no']) ? 1 : $attributes['current_no'];
        $pageSize = empty($attributes['pageSize']) ? 10 : $attributes['pageSize'];
        return [
            (new SuppliersModel)->where($where)->field($fields)->page($currentPage, $pageSize)->order('id DESC')->select(),
            $this->totalSuppliersWithFilter($attributes)
        ];
    }

    /**
     * @desc 获取总条数
     * @param array $attributes
     * @author 买买提
     * @time 2018-04-19
     * @return mixed
     * @throws Exception
     */
    public function totalSuppliersWithFilter(array $attributes = null) {
        return (new SuppliersModel)->where($this->setSuppliersFilterConditions($attributes))->count();
    }

    /**
     * @desc 设置查询条件
     * @param array $condition
     * @author 买买提
     * @time 2018-04-19
     * @return array
     */
    private function setSuppliersFilterConditions(array $condition = null) {

        $where = ['deleted_flag' => 'N', 'status' => ['neq', 'DRAFT'],];

        !empty($condition['id']) ? $where['id'] = $condition['id'] : '';
        !empty($condition['name']) ? $where['name'] = ['like', '%' . $condition['name'] . '%'] : '';
        !empty($condition['name']) ? $where['supplier_name'] = ['like', '%' . $condition['supplier_name'] . '%'] : '';
        !empty($condition['supplier_no']) ? $where['supplier_no'] = ['like', '%' . $condition['supplier_no'] . '%'] : '';
        if (!empty($condition['create_start_time']) && !empty($condition['create_end_time'])) {
            $where['created_at'] = [['egt', $condition['create_start_time']], ['elt', $condition['create_end_time'] . ' 23:59:59']];
        }

        return $where;
    }

    public function setInquiryStatics(array $suppliers = null) {
        if (is_null($suppliers)) {
            return null;
        }

//['Middle East', 'South America', 'North America', 'Africa', 'Pan Russian', 'Asia-Pacific', 'Europe']
        foreach ($suppliers as &$supplier) {
            $supplier['Middle-East'] = $this->areaInquiryStaticsBy($supplier['supplier_id'], 'Middle East');
            $supplier['South-America'] = $this->areaInquiryStaticsBy($supplier['supplier_id'], 'South America');
            $supplier['North-America'] = $this->areaInquiryStaticsBy($supplier['supplier_id'], 'North America');
            $supplier['Africa'] = $this->areaInquiryStaticsBy($supplier['supplier_id'], 'Africa');
            $supplier['Europe'] = $this->areaInquiryStaticsBy($supplier['supplier_id'], 'Europe');
            $supplier['Pan-Russian'] = $this->areaInquiryStaticsBy($supplier['supplier_id'], 'Pan Russian');
            $supplier['Asia-Pacific'] = $this->areaInquiryStaticsBy($supplier['supplier_id'], 'Asia-Pacific');
            $supplier['total'] = $supplier['Middle-East'] + $supplier['South-America'] + $supplier['North-America'] + $supplier['Africa'] + $supplier['Europe'] + $supplier['Pan-Russian'] + $supplier['Asia-Pacific'];
        }

        return $suppliers;
    }

    public function areaInquiryStaticsBy($supplier, $area = '') {
        $supplierIds = (new TemporarySupplierRelationModel)->regularSupplierWithTemporarySupplierIdsBy($supplier);

        $where = ['i.deleted_flag' => 'N', 'i.status' => 'QUOTE_SENT', 'i.quote_status' => 'COMPLETED', 'fqi.supplier_id' => ['IN', implode(',', $supplierIds)], 'mac.market_area_bn' => !empty($area) ? trim($area) : ['IN', $this->areas]];

        $inquiry = new InquiryModel();
        $supplier_inquiry = $inquiry->alias('i')
                ->join('erui_rfq.final_quote_item fqi ON i.id=fqi.inquiry_id')
                ->join('erui_operation.market_area_country mac ON i.country_bn=mac.country_bn')
                ->where($where)
                ->count("DISTINCT(i.id)");
        return $supplier_inquiry;
    }

    public function areaInquiryDataBy(array $condition = null) {

        $supplierIds = (new TemporarySupplierRelationModel)->regularSupplierWithTemporarySupplierIdsBy($condition['supplier_id']);

        $where = ['i.deleted_flag' => 'N', 'i.status' => 'QUOTE_SENT', 'i.quote_status' => 'COMPLETED', 'fqi.supplier_id' => ['IN', implode(',', $supplierIds)], 'mac.market_area_bn' => !empty($condition['area_bn']) ? trim($condition['area_bn']) : ['IN', $this->areas]];

        $currentPage = empty($condition['current_no']) ? 1 : $condition['current_no'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $inquiry = new InquiryModel();


        $supplier_inquiry = $inquiry->alias('i')
                ->join('erui_rfq.final_quote_item fqi ON i.id=fqi.inquiry_id')
                ->join('erui_operation.market_area_country mac ON i.country_bn=mac.country_bn')
                ->where($where)
                ->field('i.id inquiry_id,i.inquiry_no,i.serial_no,i.created_at')
                ->group('i.id')
                ->page($currentPage, $pageSize)
                ->select();

        return $supplier_inquiry;
    }

}
