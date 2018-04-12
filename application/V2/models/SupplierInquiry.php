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

        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'area_bn' => ['in', $this->areas],
            'id' => ['in', $inquiry_ids]
        ];
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
        $where = ['supplier_id' => $supplier_id,
            'deleted_flag' => 'N',
            'status' => 'VALID',
        ];
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
        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'DRAFT', 'APPROVING', 'INVALID']]
        ];
        $map['name'] = ['neq', ''];
        $map[] = '`name` is not null';
        $map['_logic'] = 'and';
        $where['_complex'] = $map;
        $count = $this
                // ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 询单数量
     * @return mix
     * @author zyg
     */
    public function getInquiryCount($supplier_id = null) {

        $final_quote_item_model = new FinalQuoteItemModel();
        $supplier_model = new SupplierModel();
        $supplier_table = $supplier_model->getTableName();
        $final_where = ['fqi.supplier_id' => ['gt', 0],
            'fqi.deleted_flag' => 'N',
            'fqi.status' => 'VALID',
            's.id' => ['gt', 0],
            's.deleted_flag' => 'N',
        ];
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
        $where = [
            'i.deleted_flag' => 'N',
            'i.status' => 'QUOTE_SENT',
            'i.quote_status' => 'COMPLETED',
            'i.id' => ['in', $inquiry_ids],
            'mac.market_area_bn' => ['in', $this->areas],
        ];
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
        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'id' => ['in', $inquiry_ids],
            'area_bn' => ['in', $this->areas]
        ];
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
    public function Inquiryexport($condition) {
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

        /*         * **报价单** */
        $final_quote_model = new FinalQuoteModel();
        $final_quote_table = $final_quote_model->getTableName(); //最终报价单明细

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
        $field .= 'i.proxy_no,i.buyer_code,i.project_basic_info,it.name_zh,it.name,it.model,it.qty,it.unit,';
        $field .= 'if(i.proxy_flag=\'Y\',\'是\',\'否\') as proxy_flag,';
        $field .= 'if(i.kerui_flag=\'Y\',\'是\',\'否\') as keruiflag,';
        $field .= 'if(i.bid_flag=\'Y\',\'是\',\'否\') as bidflag ,';
        $field .= 'i.quote_deadline,qt.supplier_id,qt.purchase_price_cur_bn,';
        $field .= '(select q.gross_profit_rate from ' . $quote_table . ' q where q.inquiry_id=i.id) as gross_profit_rate,'; //毛利率
        $field .= '(select q.exchange_rate from ' . $quote_table . ' q where q.inquiry_id=i.id) as exchange_rate,'; //汇率



        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_item_model = new InquiryItemModel();
        $inquiry_item_table = $inquiry_item_model->getTableName(); //询单项明细表
        /*         * *************-----------询单项明细结束------------------- */

        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_check_log_model = new InquiryCheckLogModel();
        $inquiry_check_log_table = $inquiry_check_log_model->getTableName(); //询单项明细表
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
        $field .= 'i.created_at,it.category,qt.reason_for_no_quote,qt.inquiry_id,'; //报价用时 为qs_time-created_at 或当前时间-created_at;

        $employee_sql = '(select `name` from ' . $employee_table . ' where deleted_flag=\'N\' ';
        $field .= $employee_sql . ' AND id=i.agent_id)as agent_name,'; //市场负责人
        $field .= $employee_sql . ' AND id=i.quote_id)as quote_name,'; //商务技术部报价人
        $field .= $employee_sql . ' AND id=i.check_org_id)as check_org_name,'; //事业部负责人
        $field .= $employee_sql . ' AND id=i.obtain_id)as obtain_name,'; //获取人
        $field .= $employee_sql . ' AND id=i.created_by)as created_by_name,'; //询单创建人


        $field .= ' qt.brand,qt.quote_unit,qt.purchase_unit_price,qt.purchase_unit_price*qt.quote_qty as total,'; //total厂家总价（元）
        $field .= ' fqt.quote_unit_price,fqt.total_quote_price,(fqt.total_quote_price+fqt.total_logi_fee+fqt.total_bank_fee+fqt.total_insu_fee) as total_quoted_price,'; //报价总金额（美金）
        $field .= 'qt.gross_weight_kg,(qt.gross_weight_kg*qt.quote_qty) as total_kg,qt.package_size,qt.package_mode,qt.quote_qty,';
        $field .= 'qt.delivery_days,qt.period_of_validity,i.trade_terms_bn,';
        $field .= '(case i.status WHEN \'BIZ_DISPATCHING\' THEN \'事业部分单员\' '
                . 'WHEN \'CLARIFY\' THEN \'项目澄清\' '
                . 'WHEN \'REJECT_MARKET\' THEN \'驳回市场\' '
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
                . ' END) as istatus,';

        $field .= '(case i.quote_status WHEN \'NOT_QUOTED\' THEN \'未报价\' '
                . 'WHEN \'ONGOING\' THEN \'报价中\' '
                . 'WHEN \'QUOTED\' THEN \'已报价\' '
                . 'WHEN \'COMPLETED\' THEN \'已完成\' '
                . ' END) as iquote_status,i.quote_notes';
        /*         * ****报价单明细** */
        $quote_item_model = new QuoteItemModel();
        $quote_item_table = $quote_item_model->getTableName(); //报价单明细表
        /*         * ****报价单明细** */

        /*         * **最终报价单明细** */
        $final_quote_item_model = new FinalQuoteItemModel();
        $final_quote_item_table = $final_quote_item_model->getTableName(); //最终报价单明细

        /*         * **最终报价单明细** */

        $where = ['i.deleted_flag' => 'N',
            'i.status' => ['neq', 'DRAFT'],
        ];
        if (!empty($condition['created_at_start']) && !empty($condition['created_at_end'])) {
            $created_at_start = trim($condition['created_at_start']);
            $created_at_end = trim($condition['created_at_end']);
            $where['i.created_at'] = ['between', $created_at_start . ',' . $created_at_end];
        } elseif (!empty($condition['created_at_start'])) {

            $created_at_start = trim($condition['created_at_start']);
            $where['i.created_at'] = ['egt', $created_at_start];
        } elseif (!empty($condition['created_at_end'])) {
            $created_at_end = trim($condition['created_at_end']);
            $where['i.created_at'] = ['elt', $created_at_end];
        }
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
        $this->_setConstPrice($list);
        $this->_setMaterialCat($list, 'zh');
        $this->_setCalculatePrice($list);
        $this->_setBizDespatching($list);
        $this->_setOilFlag($list);
        $this->_setObtainInfo($list);
        //$this->_setClarificationTime($list);
        $this->_setClarifyTime($list);
        $this->_setQuoteSpendTime($list);
        return $this->_createXls($list);
    }

    /**
     * 导出询单列表
     * @return mix
     * @author zyg
     */
    public function InquiryToatolexport($condition) {
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
        $field = 'i.serial_no,i.id as inquiry_id,i.project_name as name_zh,';
        $field .= '(case i.buyer_oil WHEN \'Y\' THEN \'是\' '
                . 'WHEN \'N\' THEN \'否\' '
                . 'else  \'否\' END ) '
                . ' as buyer_oil,';
        // oil_flag
        $field .= '(select country.`name` from ' . $country_table . ' as country where country.bn=i.country_bn and country.lang=\'zh\' group by country.bn) as country_name ,'; //国家名称
        $field .= '(select ma.`name` from ' . $country_table . ' c left join ' . $market_area_country_table . ' mac'
                . ' on mac.country_bn=c.bn '
                . ' left join ' . $market_area_table . ' ma on ma.bn=mac.market_area_bn '
                . 'where c.bn=i.country_bn and ma.lang=\'zh\' group by ma.bn) as market_area_name ,'; //营销区域名称
        $field .= '(select `name` from ' . $org_table . ' where i.org_id=id ) as org_name,'; //事业部
        $field .= 'i.proxy_no,i.buyer_code,i.project_basic_info,';
        $field .= 'if(i.proxy_flag=\'Y\',\'是\',\'否\') as proxy_flag,';
        $field .= 'if(i.kerui_flag=\'Y\',\'是\',\'否\') as keruiflag,';
        $field .= 'if(i.bid_flag=\'Y\',\'是\',\'否\') as bidflag ,';
        $field .= 'i.quote_deadline,obtain_id,';

        $inquiry_item_model = new InquiryItemModel();
        $inquiry_item_table = $inquiry_item_model->getTableName();
        $field .= '1 as qty,\'批\' as unit,';

        $field .= '(select q.gross_profit_rate from ' . $quote_table . ' q where q.inquiry_id=i.id) as gross_profit_rate,'; //毛利率
        $field .= '(select q.exchange_rate from ' . $quote_table . ' q where q.inquiry_id=i.id) as exchange_rate,'; //汇率
        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_check_log_model = new InquiryCheckLogModel();
        $inquiry_check_log_table = $inquiry_check_log_model->getTableName(); //询单项明细表
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
        $field .= 'i.created_at,'; //报价用时 为qs_time-created_at 或当前时间-created_at;
        $employee_sql = '(select `name` from ' . $employee_table . ' where deleted_flag=\'N\' ';
        $field .= $employee_sql . ' AND id=i.agent_id)as agent_name,'; //市场负责人
        $field .= $employee_sql . ' AND id=i.quote_id)as quote_name,'; //商务技术部报价人
        $field .= $employee_sql . ' AND id=i.check_org_id)as check_org_name,'; //事业部负责人
        $field .= $employee_sql . ' AND id=i.created_by)as created_by_name,'; //询单创建人

        $field .= 'i.trade_terms_bn,';
        $field .= '(case i.status WHEN \'BIZ_DISPATCHING\' THEN \'事业部分单员\' '
                . 'WHEN \'CC_DISPATCHING\' THEN \'易瑞客户中心\' '
                . 'WHEN \'CLARIFY\' THEN \'项目澄清\' '
                . 'WHEN \'REJECT_MARKET\' THEN \'驳回市场\' '
                . 'WHEN \'BIZ_QUOTING\' THEN \'事业部报价\' '
                . 'WHEN \'LOGI_DISPATCHING\' THEN \'物流分单员\' '
                . 'WHEN \'LOGI_QUOTING\' THEN \'物流报价\' '
                . 'WHEN \'LOGI_APPROVING\' THEN \'物流审核\' '
                . 'WHEN \'BIZ_APPROVING\' THEN \'事业部核算\' '
                . 'WHEN \'MARKET_APPROVING\' THEN \'市场主管审核\' '
                . 'WHEN \'MARKET_CONFIRMING\' THEN \'市场确认\' '
                . 'WHEN \'QUOTE_SENT\' THEN \'报价单已发出\' '
                . 'WHEN \'INQUIRY_CLOSED\' THEN \'报价关闭\' '
                . ' END) as istatus,';
        $field .= '(case i.quote_status WHEN \'NOT_QUOTED\' THEN \'未报价\' '
                . 'WHEN \'ONGOING\' THEN \'报价中\' '
                . 'WHEN \'QUOTED\' THEN \'已报价\' '
                . 'WHEN \'COMPLETED\' THEN \'已完成\' '
                . ' END) as iquote_status,i.quote_notes';


        $where = ['i.deleted_flag' => 'N',
            'i.status' => ['neq', 'DRAFT'],
        ];
        if (!empty($condition['created_at_start']) && !empty($condition['created_at_end'])) {
            $created_at_start = trim($condition['created_at_start']);
            $created_at_end = trim($condition['created_at_end']);
            $where['i.created_at'] = ['between', $created_at_start . ',' . $created_at_end];
        } elseif (!empty($condition['created_at_start'])) {
            $created_at_start = trim($condition['created_at_start']);
            $where['i.created_at'] = ['egt', $created_at_start];
        } elseif (!empty($condition['created_at_end'])) {
            $created_at_end = trim($condition['created_at_end']);
            $where['i.created_at'] = ['elt', $created_at_end];
        }
        $inquiry_model = new InquiryModel();
        $list = $inquiry_model->alias('i')
                ->field($field)
                ->where($where)
                ->select();

        $this->_setquoted_time($list);
        $this->_setBizDespatching($list);
        $this->_setTotalPrice($list);
        $this->_setObtainInfo($list);
        $this->_setTotalOilFlag($list);
        //$this->_setClarificationTime($list);
        $this->_setClarifyTime($list);
        $this->_setQuoteSpendTime($list);

        // $this->_setTotalCalculatePrice($list);
        return $this->_createXls($list, '导出总行询单数据');
    }

    /*
     * 对应表
     *
     */

    private function _getKeys() {
        return [
            'B' => ['serial_no', '报价单号'],
            'C' => ['country_name', '询价单位'],
            'D' => ['market_area_name', '所属地区部'],
            'E' => ['org_name', '事业部'],
            'F' => ['ie_erui', '是否走易瑞'],
            'G' => ['buyer_code', '客户名称或代码'],
            'H' => ['proxy_flag', '是否代理商获取'],
            'I' => ['proxy_no', '代理商代码'],
            'J' => ['project_basic_info', '客户及项目背景描述'],
            'K' => ['name_zh', '品名中文'],
            'L' => ['name', '品名外文'],
            'M' => ['product_name', '产品名称'],
            'N' => ['supplier_name', '供应商'],
            'O' => ['model', '规格'],
            'P' => [null, '图号'],
            'Q' => ['qty', '数量'],
            'R' => ['unit', '单位'],
            'S' => ['buyer_oil', '是否油气客户'],
            'T' => ['oil_flag', '油气or非油气'],
            'U' => ['material_cat_name', '平台产品分类'],
            'V' => ['category', '产品分类'],
            'W' => ['keruiflag', '是否科瑞设备用配件'],
            'X' => ['bidflag', '是否投标'],
            'Y' => ['inflow_time', '转入日期'],
            'Z' => ['quote_deadline', '需用日期'],
            'AA' => ['max_inflow_time_out', '最后一次流入事业部分单员时间'], //最后一次流入事业部分单员时间
            'AB' => ['max_inflow_time', '澄清完成日期'],
            'AC' => ['bq_time', '事业部报出日期'],
            'AD' => ['ld_time', '物流接收日期'],
            'AE' => ['la_time', '物流报出日期'],
            'AF' => ['qs_time', '报出日期'],
            'AG' => ['biz_dispatching_clarification_time', '事业部分单员发起的澄清用时（小时）'], //事业部分单员发起的澄清用时（小时）
            'AH' => ['biz_quoting_clarification_time', '事业部报价人发起的澄清用时（小时）'], //事业部报价人发起的澄清用时（小时）
            'AI' => ['logi_dispatching_clarification_time', '物流分单员发起的澄清用时（小时）'], //物流分单员发起的澄清用时（小时）
            'AJ' => ['logi_quoting_clarification_time', '物流报价人发起的澄清用时（小时）'], //物流报价人发起的澄清用时（小时）
            'AK' => ['logi_approving_clarification_time', '物流审核发起的澄清用时（小时）'], //物流审核发起的澄清用时（小时）
            'AL' => ['biz_approving_clarification_time', '事业部核算发起的澄清用时（小时）'], //事业部核算发起的澄清用时（小时）
            'AM' => ['market_approving_clarification_time', '事业部审核发起的澄清用时（小时）'], //事业部审核发起的澄清用时（小时）
            'AN' => ['clarification_time', '项目澄清时间(小时)'],
            'AO' => ['quoted_time', '最终报价用时(小时)'],
            'AP' => ['whole_quoted_time', '整体报价时间(小时)'],
            'AQ' => ['biz_quoted_time', '商务技术报价用时(小时)'],
            'AR' => ['logi_quoted_time', '物流报价时间(小时)'],
            'AS' => ['obtain_org_name', '获单主体单位)'],
            'AT' => ['obtain_name', '获取人)'],
            'AU' => ['created_by_name', '询单创建人'],
            'AV' => ['agent_name', '市场负责人'],
            'AW' => ['biz_despatching', '事业部分单人'],
            'AX' => ['quote_name', '商务技术部报价人'],
            'AY' => ['check_org_name', '事业部负责人'],
            'AZ' => ['brand', '产品品牌'],
            'BA' => ['supplier_name', '报价单位'],
            'BB' => [null, '供应商报价人'],
            'BC' => [null, '报价人联系方式'],
            'BD' => ['purchase_unit_price', '厂家单价（元）'],
            'BE' => ['purchase_price_cur_bn', '币种'],
            'BF' => ['total', '厂家总价（元）'],
            'BG' => ['purchase_price_cur_bn', '币种'],
            'BH' => ['gross_profit_rate', '利润率'],
            'BI' => ['quote_unit_price', '报价单价（元）'],
            'BJ' => ['purchase_price_cur_bn', '币种'],
            'BK' => ['total_quote_price', '报价总价（元）'],
            'BL' => ['purchase_price_cur_bn', '币种'],
            'BM' => ['total_quoted_price_usd', '报价总金额（美金）'],
            'BN' => ['gross_weight_kg', '单重(kg)'],
            'BO' => ['total_kg', '总重(kg)'],
            'BP' => ['package_size', '包装体积(mm)'],
            'BQ' => ['package_mode', '包装方式'],
            'BR' => ['delivery_days', '交货期（天）'],
            'BS' => ['period_of_validity', '有效期（天）'],
            'BT' => ['trade_terms_bn', '贸易术语'],
            'BU' => ['istatus', '最新进度及解决方案'],
            'BV' => ['iquote_status', '报价后状态'],
            'BW' => ['quote_notes', '备注'],
            'BX' => ['reason_for_no_quote', '未报价分析'],
//            'BA' => [null, '报价超48小时原因类型'],
//            'BB' => [null, '报价超48小时分析'],
//            'BC' => [null, '成单或失单'],
//            'BD' => [null, '失单原因类型'],
//            'BE' => [null, '失单原因分析'],
        ];
    }

    /*
     * 对应表
     *
     */

    private function _getTotalKeys() {
        return [
            'B' => ['serial_no', '报价单号'],
            'C' => ['country_name', '询价单位'],
            'D' => ['market_area_name', '所属地区部'],
            'E' => ['org_name', '事业部'],
            'F' => ['ie_erui', '是否走易瑞'],
            'G' => ['buyer_code', '客户名称或代码'],
            'H' => ['proxy_flag', '是否代理商获取'],
            'I' => ['proxy_no', '代理商代码'],
            'J' => ['project_basic_info', '客户及项目背景描述'],
            'K' => ['name_zh', '品名中文'],
            'L' => ['name', '品名外文'],
            'M' => ['product_name', '产品名称'],
            'N' => ['supplier_name', '供应商'],
            'O' => ['model', '规格'],
            'P' => [null, '图号'],
            'Q' => ['qty', '数量'],
            'R' => ['unit', '单位'],
            'S' => ['buyer_oil', '是否油气客户'],
            'T' => ['oil_flag', '油气/非油气'],
            'U' => [null, '平台产品分类'],
            'V' => ['category', '产品分类'],
            'W' => ['keruiflag', '是否科瑞设备用配件'],
            'X' => ['bidflag', '是否投标'],
            'Y' => ['inflow_time', '转入日期'],
            'Z' => ['quote_deadline', '需用日期'],
            'AA' => ['max_inflow_time_out', '最后一次流入事业部分单员时间'], //最后一次流入事业部分单员时间
            'AB' => ['max_inflow_time', '澄清完成日期'],
            'AC' => ['bq_time', '事业部报出日期'],
            'AD' => ['ld_time', '物流接收日期'],
            'AE' => ['la_time', '物流报出日期'],
            'AF' => ['qs_time', '报出日期'],
            'AG' => ['biz_dispatching_clarification_time', '事业部分单员发起的澄清用时（小时）'], //事业部分单员发起的澄清用时（小时）
            'AH' => ['biz_quoting_clarification_time', '事业部报价人发起的澄清用时（小时）'], //事业部报价人发起的澄清用时（小时）
            'AI' => ['logi_dispatching_clarification_time', '物流分单员发起的澄清用时（小时）'], //物流分单员发起的澄清用时（小时）
            'AJ' => ['logi_quoting_clarification_time', '物流报价人发起的澄清用时（小时）'], //物流报价人发起的澄清用时（小时）
            'AK' => ['logi_approving_clarification_time', '物流审核发起的澄清用时（小时）'], //物流审核发起的澄清用时（小时）
            'AL' => ['biz_approving_clarification_time', '事业部核算发起的澄清用时（小时）'], //事业部核算发起的澄清用时（小时）
            'AM' => ['market_approving_clarification_time', '事业部审核发起的澄清用时（小时）'], //事业部审核发起的澄清用时（小时）
            'AN' => ['clarification_time', '项目澄清时间(小时)'],
            'AO' => ['quoted_time', '最终报价用时(小时)'],
            'AP' => ['whole_quoted_time', '整体报价时间(小时)'],
            'AQ' => ['biz_quoted_time', '商务技术报价用时(小时)'],
            'AR' => ['logi_quoted_time', '物流报价时间(小时)'],
            'AS' => ['obtain_org_name', '获单主体单位)'],
            'AT' => ['obtain_name', '获取人)'],
            'AU' => ['created_by_name', '询单创建人'],
            'AV' => ['agent_name', '市场负责人'],
            'AW' => ['biz_despatching', '事业部分单人'],
            'AX' => ['quote_name', '商务技术部报价人'],
            'AY' => ['check_org_name', '事业部负责人'],
            'AZ' => ['brand', '产品品牌'],
            'BA' => ['supplier_name', '报价单位'],
            'BB' => [null, '供应商报价人'],
            'BC' => [null, '报价人联系方式'],
            'BD' => ['purchase_unit_price', '厂家单价（元）'],
            'BE' => ['purchase_price_cur_bn', '币种'],
            'BF' => ['total', '厂家总价（元）'],
            'BG' => ['purchase_price_cur_bn', '币种'],
            'BH' => ['gross_profit_rate', '利润率'],
            'BI' => ['quote_unit_price', '报价单价（元）'],
            'BJ' => ['purchase_price_cur_bn', '币种'],
            'BK' => ['total_quote_price', '报价总价（元）'],
            'BL' => ['purchase_price_cur_bn', '币种'],
            'BM' => ['total_quoted_price_usd', '报价总金额（美金）'],
            'BN' => ['gross_weight_kg', '单重(kg)'],
            'BO' => ['total_kg', '总重(kg)'],
            'BP' => ['package_size', '包装体积(mm)'],
            'BQ' => ['package_mode', '包装方式'],
            'BR' => ['delivery_days', '交货期（天）'],
            'BS' => ['period_of_validity', '有效期（天）'],
            'BT' => ['trade_terms_bn', '贸易术语'],
            'BU' => ['istatus', '最新进度及解决方案'],
            'BV' => ['iquote_status', '报价后状态'],
            'BW' => ['quote_notes', '备注'],
            'BX' => ['reason_for_no_quote', '未报价分析'],
        ];
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
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
        $objPHPExcel = new PHPExcel();

        $objSheet = $objPHPExcel->setActiveSheetIndex(0);    //当前sheet
        $objSheet->setTitle('询报价单表');
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        if ($name == '导出总行询单数据') {
            $keys = $this->_getTotalKeys();
        } else {
            $keys = $this->_getKeys();
        }
        $objSheet->setCellValue('A1', '序号');
        foreach ($keys as $rowname => $key) {
            $objSheet->setCellValue($rowname . '1', $key[1]);
        }
        foreach ($list as $j => $item) {
            $objSheet->setCellValue('A' . ($j + 2), ($j + 1));
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
        $objSheet->getStyle('A1:BZ' . ($j + 2))->applyFromArray($styleArray);
        $objSheet->getStyle('A1:BZ' . ($j + 2))->getAlignment()->setShrinkToFit(true); //字体变小以适应宽
        $objSheet->getStyle('A1:BZ' . ($j + 2))->getAlignment()->setWrapText(true); //自动换行
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
                return array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
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
                ->where(['icl.inquiry_id' => ['in', $inquiry_ids], 'out_node' => 'BIZ_DISPATCHING'])
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
                $list[$key]['material_cat_no'] = $product_names[$item['sku']]['material_cat_no'];
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
            if ($item['obtain_id'] && isset($users[$item['obtain_id']])) {
                $list[$key]['obtain_name'] = $users[$item['obtain_id']];
            } else {
                $list[$key]['obtain_name'] = '';
            }

            if ($item['obtain_id'] && isset($orgs[$item['obtain_id']])) {
                $list[$key]['obtain_org_name'] = $orgs[$item['obtain_id']];
            } else {
                $list[$key]['obtain_org_name'] = '';
            }
        }
    }

    /*
     * 项目澄清时间
     * @author  zhongyg
     * @param array $list // 询单总行信息
     * @return
     * @date    2018-02-011 11:45:09
     * @version V2.0
     * @desc  项目澄清时间
     */

    private function _setClarificationTime(&$list) {
        $inquiry_ids = [];
        foreach ($list as $item) {
            if ($item['inquiry_id']) {
                $inquiry_ids[] = $item['inquiry_id'];
            }
        }
        $inquiryCheckLogModel = new InquiryCheckLogModel();
        $inquiryCheckLogTable = $inquiryCheckLogModel->getTableName();
        $where['icl.inquiry_id'] = ['in', $inquiry_ids];
        $where['icl.action'] = 'CLARIFY';
        $where['icl.in_node'] = ['in', ['BIZ_QUOTING', 'LOGI_DISPATCHING', 'LOGI_QUOTING', 'BIZ_APPROVING', 'MARKET_APPROVING']];
        $where['icl.out_node'] = 'CLARIFY';
        $where[] = 'iclog.out_node is not null and iclog.out_node<>\'\'';
        $ClarificationTimes = $inquiryCheckLogModel
                ->field('icl.inquiry_id,icl.in_node,sum(UNIX_TIMESTAMP(iclog.out_at)-UNIX_TIMESTAMP(icl.into_at)) as clarification_time')
                ->alias('icl')
                ->join($inquiryCheckLogTable . ' as iclog on icl.inquiry_id=iclog.inquiry_id'
                        . ' and icl.in_node=iclog.out_node and iclog.in_node=\'CLARIFY\'')
                ->where($where)
                ->group('icl.inquiry_id,icl.in_node')
                ->select();
        $clarification_times = [];
        foreach ($ClarificationTimes as $ClarificationTime) {

            $clarification_times[$ClarificationTime['inquiry_id']][$ClarificationTime['in_node']] = $ClarificationTime['clarification_time'] / 3600;
            $clarification_times[$ClarificationTime['inquiry_id']]['TOTAL'] = intval($clarification_times[$ClarificationTime['inquiry_id']]['TOTAL']) + $ClarificationTime['clarification_time'];
        }
        foreach ($list as $key => $item) {
            if ($item['inquiry_id'] && isset($clarification_times[$item['inquiry_id']])) {
                //总澄清用时
                $list[$key]['clarification_time'] = number_format($clarification_times[$item['inquiry_id']]['TOTAL'] / 3600, 2, '.', ',');
                //事业部报价人发起的澄清用时（小时）
                if (isset($clarification_times[$item['inquiry_id']]['BIZ_QUOTING'])) {
                    $list[$key]['biz_quoting_clarification_time'] = number_format($clarification_times[$item['inquiry_id']]['BIZ_QUOTING'], 2, '.', ',');
                } else {
                    $list[$key]['biz_quoting_clarification_time'] = '';
                }
                //物流分单员发起的澄清用时（小时）
                if (isset($clarification_times[$item['inquiry_id']]['LOGI_DISPATCHING'])) {
                    $list[$key]['logi_dispatching_clarification_time'] = number_format($clarification_times[$item['inquiry_id']]['LOGI_DISPATCHING'], 2, '.', ',');
                } else {
                    $list[$key]['logi_dispatching_clarification_time'] = '';
                }
                //物流报价人发起的澄清用时（小时）
                if (isset($clarification_times[$item['inquiry_id']]['LOGI_QUOTING'])) {
                    $list[$key]['logi_quoting_clarification_time'] = number_format($clarification_times[$item['inquiry_id']]['LOGI_QUOTING'], 2, '.', ',');
                } else {
                    $list[$key]['logi_quoting_clarification_time'] = '';
                }
                //事业部核算发起的澄清用时（小时）
                if (isset($clarification_times[$item['inquiry_id']]['BIZ_APPROVING'])) {
                    $list[$key]['biz_approving_clarification_time'] = number_format($clarification_times[$item['inquiry_id']]['BIZ_APPROVING'], 2, '.', ',');
                } else {
                    $list[$key]['biz_approving_clarification_time'] = '';
                }
                //事业部审核发起的澄清用时（小时）
                if (isset($clarification_times[$item['inquiry_id']]['MARKET_APPROVING'])) {
                    $list[$key]['market_approving_clarification_time'] = number_format($clarification_times[$item['inquiry_id']]['MARKET_APPROVING'], 2, '.', ',');
                } else {
                    $list[$key]['market_approving_clarification_time'] = '';
                }
            } else {
                $list[$key]['biz_quoting_clarification_time'] = '';
                $list[$key]['logi_dispatching_clarification_time'] = '';
                $list[$key]['logi_quoting_clarification_time'] = '';
                $list[$key]['biz_approving_clarification_time'] = '';
                $list[$key]['market_approving_clarification_time'] = '';
                $list[$key]['clarification_time'] = '';
            }
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
            'BIZ_QUOTING' => 'biz_quoting_clarification_time',
            'LOGI_DISPATCHING' => 'logi_dispatching_clarification_time',
            'LOGI_QUOTING' => 'logi_quoting_clarification_time',
            'LOGI_APPROVING' => 'logi_approving_clarification_time',
            'BIZ_APPROVING' => 'biz_approving_clarification_time',
            'MARKET_APPROVING' => 'market_approving_clarification_time'
        ];
        foreach ($clarifyMapping as $k => $v) {
            $clarifyNode[] = $k;
        }
        foreach ($list as &$item) {
            $where['inquiry_id'] = $item['inquiry_id'];
            foreach ($clarifyMapping as $v) {
                // 项目澄清时间初始化
                $item[$v] = '';
            }
            $item['clarification_time'] = '';
            // 最后一次流入事业部分单员的日志ID
            $lastBizDispatchingID = $inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'BIZ_DISPATCHING']))->order('id DESC')->getField('id');
            // 最后一次流入客户中心的日志ID
            $lastEruiDispatchingID = $inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'CC_DISPATCHING']))->order('id DESC')->getField('id');
            // 项目澄清时间的参考ID
            $referenceID = $lastEruiDispatchingID > $lastBizDispatchingID ? $lastEruiDispatchingID : $lastBizDispatchingID;
            if ($referenceID) {
                // 各环节的项目澄清时间列表
                $clarifyList = $inquiryCheckLogModel->field('out_node, (UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) AS clarify_time')->where(array_merge($where, ['id' => ['gt', $referenceID], 'in_node' => 'CLARIFY', 'out_node' => ['in', array_diff($clarifyNode, ['BIZ_DISPATCHING'])]]))->order('id ASC')->select();
                foreach ($clarifyList as $clarify) {
                    // 计算各环节的项目澄清时间（事业部分单员除外）
                    $item[$clarifyMapping[$clarify['out_node']]] += $clarify['clarify_time'];
                }
                // 最后一次事业部分单员的项目澄清时间
                $item[$clarifyMapping['BIZ_DISPATCHING']] = $inquiryCheckLogModel->field('(UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) AS clarify_time')->where(array_merge($where, ['in_node' => 'CLARIFY', 'out_node' => 'BIZ_DISPATCHING']))->order('id DESC')->find()['clarify_time'];
                // 如果最后一条日志为项目澄清且没有流出，根据当前时间计算项目澄清时间
                $lastLog = $inquiryCheckLogModel->field('in_node, out_node, UNIX_TIMESTAMP(out_at) AS out_time')->where($where)->order('id DESC')->find();
                $lastClarifyTime = '';
                if ($lastLog['out_node'] == 'CLARIFY' && in_array($lastLog['in_node'], $clarifyNode)) {
                    $lastClarifyTime = $nowTime - $lastLog['out_time'];
                    $item[$clarifyMapping[$lastLog['in_node']]] += $lastClarifyTime;
                    if ($lastLog['in_node'] == 'BIZ_DISPATCHING') {
                        $item[$clarifyMapping['BIZ_DISPATCHING']] = $lastClarifyTime;
                    }
                }
                foreach ($clarifyMapping as $v) {
                    if ($item[$v] > 0) {
                        // 项目澄清时间换算成小时
                        $item[$v] = number_format($item[$v] / 3600, 2);
                    }
                }
                // 总的项目澄清时间
                $item['clarification_time'] = $inquiryCheckLogModel->field('SUM(UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) AS clarify_time')->where(array_merge($where, ['in_node' => 'CLARIFY']))->find()['clarify_time'] + $lastClarifyTime;
                if ($item['clarification_time'] > 0) {
                    $item['clarification_time'] = number_format($item['clarification_time'] / 3600, 2);
                }
            }
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
        foreach ($list as &$item) {
            $where['inquiry_id'] = $item['inquiry_id'];
            // 第一次流入事业部分单员的时间
            $firstBizDispatchingTime = $inquiryCheckLogModel->field('MIN(into_at) AS first_time')->where(array_merge($where, ['in_node' => 'BIZ_DISPATCHING']))->find()['first_time'];
            // 最后一次流出事业部审核的时间
            $lastMarketApprovingTime = $inquiryCheckLogModel->field('MAX(out_at) AS last_time')->where(array_merge($where, ['in_node' => 'MARKET_APPROVING']))->find()['last_time'];
            // 第一次流入物流分单员的时间
            $firstLogiDispatchingTime = $inquiryCheckLogModel->field('MIN(into_at) AS first_time')->where(array_merge($where, ['in_node' => 'LOGI_DISPATCHING']))->find()['first_time'];
            // 最后一次流出物流报价的时间
            $lastlogiQuotingTime = $inquiryCheckLogModel->field('MAX(out_at) AS last_time')->where(array_merge($where, ['in_node' => 'LOGI_QUOTING']))->find()['last_time'];
            // 物流审核耗时
            $logiApprovingSpend = $inquiryCheckLogModel->field('ROUND(SUM(UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) / 3600, 2) AS spend')->where(array_merge($where, ['in_node' => 'LOGI_APPROVING']))->find()['spend'];
            // 整体报价耗时
            $item['whole_quoted_time'] = number_format((($lastMarketApprovingTime ? strtotime($lastMarketApprovingTime) : $nowTime) - ($firstBizDispatchingTime ? strtotime($firstBizDispatchingTime) : strtotime($item['created_at'])))  / 3600, 2);
            // 商务技术报价耗时
            $item['biz_quoted_time'] = number_format(str_replace(',', '', $item['whole_quoted_time']) - str_replace(',', '', $item['clarification_time']) - $logiApprovingSpend, 2);
            // 物流报价耗时
            $item['logi_quoted_time'] = $lastlogiQuotingTime ? number_format((strtotime($lastlogiQuotingTime) - strtotime($firstLogiDispatchingTime)) / 3600, 2) : 0;
        }
    }

    /*
     * Description of 获取价格属性
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setConstPrice(&$list) {
        if ($list) {

            $skus = [];
            foreach ($list as $key => $val) {
                $skus[] = $val['sku'];
            }

            $goods_cost_price_model = new GoodsCostPriceModel();
            $stockcostprices = $goods_cost_price_model->getCostPricesBySkus($skus);

            foreach ($list as $key => $val) {

                if ($val['sku'] && isset($stockcostprices[$val['sku']])) {
                    if (isset($stockcostprices[$val['sku']])) {
                        $price = '';
                        foreach ($stockcostprices[$val['sku']] as $stockcostprice) {
                            if ($stockcostprice['price'] && $stockcostprice['max_price']) {
                                $price = $stockcostprice['price'] . '-' . $stockcostprice['max_price'];
                            } elseif ($stockcostprice['price']) {
                                $price = $stockcostprice['price'];
                            } else {
                                $price = '';
                            }
                        }
                        $val['costprices'] = $price;
                    }
                } else {
                    $val['costprices'] = '';
                }
                $list[$key] = $val;
            }
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
        $exchange_rate_model = new ExchangeRateModel();

        foreach ($list as $key => $item) {
            $gross_profit_rate = $item['gross_profit_rate'] / 100 + 1;
            $list[$key]['quote_unit_price'] = $item['quote_unit_price'] > 0 ? $item['quote_unit_price'] : $gross_profit_rate * $item['purchase_unit_price'];
            $list[$key]['total_quote_price'] = $item['total_quote_price'] > 0 ? $item['total_quote_price'] : $gross_profit_rate * $item['total'];

            if ($item['purchase_price_cur_bn'] == 'USD') {
                $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'];
            } else {
                if ($item['exchange_rate'] && $item['exchange_rate'] > 1) {
                    $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] / $item['exchange_rate'];
                } elseif ($item['exchange_rate']) {
                    $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] * $item['exchange_rate'];
                } else {
                    $exchange_rate = $exchange_rate_model
                            ->where(['cur_bn1' => $item['purchase_price_cur_bn'],
                                'cur_bn2' => 'USD',
                                'effective_date' => ['egt', date('Y-m')],
                            ])
                            ->order('created_at DESC')
                            ->getField('rate');

                    if (!$exchange_rate) {
                        $exchange_rate_change = $exchange_rate_model->where([
                                    'cur_bn2' => $item['purchase_price_cur_bn'],
                                    'cur_bn1' => 'USD',
                                    'effective_date' => ['egt', date('Y-m')],
                                ])->order('created_at DESC')->getField('rate');
                        $exchange_rate = $exchange_rate_change > 0 ? 1 / $exchange_rate_change : null;
                    }
                    if ($exchange_rate) {
                        $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] * $exchange_rate;
                    }
                }
            }
            // 只在市场确认、报价单已发出、报价关闭环节显示报价金额
            if (!in_array($item['istatus'], ['市场确认', '报价单已发出', '报价关闭'])) {
                $list[$key]['quote_unit_price'] = $list[$key]['total_quote_price'] = $list[$key]['total_quoted_price_usd'] = '';
            }
        }
    }

    private function _setTotalCalculatePrice(&$list) {
        $exchange_rate_model = new ExchangeRateModel();

        foreach ($list as $key => $item) {
            if ($item['purchase_price_cur_bn'] && $item['total_quote_price']) {

                if ($item['purchase_price_cur_bn'] == 'USD') {
                    $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'];
                } else {
                    if ($item['exchange_rate'] && $item['exchange_rate'] > 1) {
                        $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] / $item['exchange_rate'];
                    } elseif ($item['exchange_rate']) {
                        $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] * $item['exchange_rate'];
                    } else {
                        $exchange_rate = $exchange_rate_model
                                ->where(['cur_bn1' => $item['purchase_price_cur_bn'],
                                    'cur_bn2' => 'USD',
                                    'effective_date' => ['egt', date('Y-m')],
                                ])
                                ->order('created_at DESC')
                                ->getField('rate');

                        if (!$exchange_rate) {
                            $exchange_rate_change = $exchange_rate_model->where([
                                        'cur_bn2' => $item['purchase_price_cur_bn'],
                                        'cur_bn1' => 'USD',
                                        'effective_date' => ['egt', date('Y-m')],
                                    ])->order('created_at DESC')->getField('rate');
                            $exchange_rate = $exchange_rate_change > 0 ? 1 / $exchange_rate_change : null;
                        }
                        if ($exchange_rate) {
                            $list[$key]['total_quoted_price_usd'] = $list[$key]['total_quote_price'] * $exchange_rate;
                        }
                    }
                }
            }
            // 只在市场确认、报价单已发出、报价关闭环节显示报价金额
            if (!in_array($item['istatus'], ['市场确认', '报价单已发出', '报价关闭'])) {
                $list[$key]['quote_unit_price'] = $list[$key]['total_quote_price'] = $list[$key]['total_quoted_price_usd'] = '';
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
            $oil_flags = [
                '石油专用管材',
                '钻修井设备',
                '固井酸化压裂设备',
                '采油集输设备',
                '石油专用工具',
                '石油专用仪器仪表',
                '油田化学材料',];
            $not_oil_flags = [
                '通用机械设备',
                '劳动防护用品',
                '消防、医疗产品',
                '电力电工设备',
                '橡塑产品',
                '钢材',
                '包装物',
                '杂品',];

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

    /*
     * Description of 获取物料分类名称
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setTotalOilFlag(&$arr) {

        $oilflag = ['石油专用管材', '钻修井设备', '固井酸化压裂设备', '采油集输设备', '石油专用工具', '石油专用仪器仪表', '油田化学材料'];
        $notoilflags = ['通用机械设备', '劳动防护用品', '消防、医疗产品', '电力电工设备', '橡塑产品', '钢材', '包装物', '杂品'];
        if ($arr) {
            foreach ($arr as $item) {
                $inquiry_ids[] = $item['inquiry_id'];
            }
            $where = ['deleted_flag' => 'N',
                'category is not null and category<>\'\''
            ];
            $inquiry_item_model = new InquiryItemModel();
            if ($inquiry_ids) {
                $where['inquiry_id'] = ['in', $inquiry_ids];
                $where['category'] = ['in', $oilflag];
                $oilinquiry_items = $inquiry_item_model->field('inquiry_id,category ,count(`id`) as num')
                        ->where($where)
                        ->group('inquiry_id')
                        ->select();
            } else {
                $oilinquiry_items = [];
            }

            if ($inquiry_ids) {
                $where['inquiry_id'] = ['in', $inquiry_ids];
                $where['category'] = ['in', $notoilflags];
                $notoilinquiry_items = $inquiry_item_model->field('inquiry_id,category,count(`id`) as num ')
                        ->where($where)
                        ->group('inquiry_id')
                        ->select();
            } else {
                $notoilinquiry_items = [];
            }
            $oils = [];
            foreach ($oilinquiry_items as $inquiry_item) {
                $oils[$inquiry_item['inquiry_id']] = $inquiry_item;
            }
            foreach ($notoilinquiry_items as $inquiry_item) {
                $notoils[$inquiry_item['inquiry_id']] = $inquiry_item;
            }

            foreach ($arr as $key => $val) {
                if (isset($oils[$val['inquiry_id']]['num']) && isset($notoils[$val['inquiry_id']]['num']) && $oils[$val['inquiry_id']]['num'] >= $notoils[$val['inquiry_id']]['num']) {
                    $val['oil_flag'] = '油气';
                } elseif (isset($oils[$val['inquiry_id']]['num']) && isset($notoils[$val['inquiry_id']]['num']) && $oils[$val['inquiry_id']]['num'] < $notoils[$val['inquiry_id']]['num']) {
                    $val['oil_flag'] = '非油气';
                } elseif (isset($oils[$val['inquiry_id']]['num']) && !isset($notoils[$val['inquiry_id']]['num'])) {
                    $val['oil_flag'] = '油气';
                } elseif (!isset($oils[$val['inquiry_id']]['num']) && isset($notoils[$val['inquiry_id']]['num'])) {
                    $val['oil_flag'] = '非油气';
                } else {
                    $val['oil_flag'] = '';
                }
                if ($val['oil_flag'] === '油气' && isset($oils[$val['inquiry_id']]['category'])) {
                    $val['category'] = $oils[$val['inquiry_id']]['category'];
                } elseif ($val['oil_flag'] === '非油气' && isset($notoils[$val['inquiry_id']]['category'])) {
                    $val['category'] = $notoils[$val['inquiry_id']]['category'];
                } else {
                    $val['category'] = '';
                }
                $arr[$key] = $val;
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

    private function _setTotalPrice(&$arr) {
        if ($arr) {
            foreach ($arr as $item) {
                if ($item['inquiry_id']) {
                    $inquiry_ids[] = $item['inquiry_id'];
                }
            }
            $where = ['deleted_flag' => 'N',
            ];
            if ($inquiry_ids) {
                $where['inquiry_id'] = ['in', $inquiry_ids];
            } else {
                return;
            }
            $quote_model = new QuoteModel();
            $final_quote_model = new FinalQuoteModel();
            $quotes = $quote_model->field('inquiry_id,total_logi_fee,total_quote_price,total_weight,package_volumn,package_mode,delivery_period,period_of_validity')
                    ->where($where)
                    ->select();
            $final_quotes = $final_quote_model->field('inquiry_id,total_logi_fee,total_quote_price')
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
            foreach ($arr as $key => $val) {
                if (isset($final_quoteprices[$val['inquiry_id']]['total_quote_price'])) {
                    $val['total_quote_price'] = $final_quoteprices[$val['inquiry_id']]['total_quote_price'];
                    $val['purchase_price_cur_bn'] = 'USD';
                    $val['total_quoted_price_usd'] = $final_quoteprices[$val['inquiry_id']]['total_quote_price'];
                } elseif (isset($quoteprices[$val['inquiry_id']]['total_quote_price'])) {
                    $val['total_quote_price'] = $quoteprices[$val['inquiry_id']]['total_quote_price'];
                    $val['purchase_price_cur_bn'] = 'USD';
                    $val['total_quoted_price_usd'] = $quoteprices[$val['inquiry_id']]['total_quote_price'];
                } else {
                    $val['total_quote_price'] = '';
                    $val['purchase_price_cur_bn'] = '';
                    $val['total_quoted_price_usd'] = '';
                }

                if (isset($quoteprices[$val['inquiry_id']]['total_weight'])) {
                    $val['total_kg'] = $quoteprices[$val['inquiry_id']]['total_weight'];
                    $val['package_size'] = isset($quoteprices[$val['inquiry_id']]['package_volumn']) ? $quoteprices[$val['inquiry_id']]['package_volumn'] : '';
                    $val['package_mode'] = $quoteprices[$val['inquiry_id']]['package_mode'];
                    $val['delivery_days'] = $quoteprices[$val['inquiry_id']]['delivery_period'];
                    $val['period_of_validity'] = $quoteprices[$val['inquiry_id']]['period_of_validity'];
                } else {
                    $val['total_weight'] = '';
                    $val['package_size'] = '';
                    $val['package_mode'] = '';
                    $val['delivery_days'] = '';
                    $val['period_of_validity'] = '';
                }

                $arr[$key] = $val;
                // 只在市场确认、报价单已发出、报价关闭环节显示报价金额
                if (!in_array($val['istatus'], ['市场确认', '报价单已发出', '报价关闭'])) {
                    $arr[$key]['quote_unit_price'] = $arr[$key]['total_quote_price'] = $arr[$key]['total_quoted_price_usd'] = '';
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

    private function _setMaterialCat(&$arr, $lang) {
        if ($arr) {
            $material_cat_model = new MaterialCatModel();
            $catnos = [];
            foreach ($arr as $key => $val) {
                $catnos[] = $val['material_cat_no'];
            }
            $catnames = $material_cat_model->getNameByCatNos($catnos, $lang);
            foreach ($arr as $key => $val) {
                if ($val['category'] && isset($catnames[$val['material_cat_no']])) {
                    $val['material_cat_name'] = $catnames[$val['material_cat_no']];
                } else {
                    $val['material_cat_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
