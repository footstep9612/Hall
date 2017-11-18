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
        $this->_getValue($where, $condition, 'supplier_no', 'string', 'supplier_no');
        $this->_getValue($where, $condition, 'supplier_name', 'like', 'name');
        // $this->_getValue($where, $condition, 'created_at', 'between', 'i.created_at');
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getList($condition) {
        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
        ];
        $this->_getCondition($condition, $where);
        list($offset, $length) = $this->_getPage($condition);
        $created_at_start = !empty($condition['created_at_start']) ? $condition['created_at_start'] : null;
        $created_at_end = !empty($condition['created_at_end']) ? $condition['created_at_end'] : null;
        $data = $this
                ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->limit($offset, $length)
                ->select();
        $ret = [];

        foreach ($data as $item) {
            $this->getAreaCountBySupplierId($item['supplier_id'], $created_at_start, $created_at_end, $item);
            $ret[] = $item;
        }
        return $ret;
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
                ->field('count(\'id\') as area_count,area_bn')
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
            'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
        ];
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
        $final_where = ['supplier_id' => ['gt', 0],
            'deleted_flag' => 'N',
            'status' => 'VALID',
        ];
        if ($supplier_id) {
            $final_where['supplier_id'] = $supplier_id;
        }
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where($final_where)->group('inquiry_id')->select();
        $inquiry_ids = [];


        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        if (empty($inquiry_ids)) {
            return 0;
        }
        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'id' => ['in', $inquiry_ids],
            'area_bn' => ['in', $this->areas]
        ];
        $inquiry_model = new InquiryModel();
        $count = $inquiry_model
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
    public function Inquiryexport() {
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
        $field = 'i.serial_no,';
        $field .= '(select country.`name` from ' . $country_table . ' as country where country.bn=i.country_bn and country.lang=\'zh\' group by country.bn) as country_name ,'; //国家名称
        $field .= '(select ma.`name` from ' . $country_table . ' c left join ' . $market_area_country_table . ' mac'
                . ' on mac.country_bn=c.bn '
                . ' left join ' . $market_area_table . ' ma on ma.bn=mac.market_area_bn '
                . 'where c.bn=i.country_bn and ma.lang=\'zh\' group by ma.bn) as market_area_name ,'; //营销区域名称
        $field .= '(select `name` from ' . $org_table . ' where i.org_id=id ) as org_name,'; //事业部
        $field .= 'i.buyer_code,it.remarks,it.name_zh,it.name,it.model,it.qty,it.unit,';
        $field .= 'if(i.kerui_flag=\'Y\',\'是\',\'否\') as keruiflag,';
        $field .= 'if(i.bid_flag=\'Y\',\'是\',\'否\') as bidflag ,';
        $field .= 'i.inflow_time,i.quote_deadline,';


        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_item_model = new InquiryitemModel();
        $inquiry_item_table = $inquiry_item_model->getTableName(); //询单项明细表
        /*         * *************-----------询单项明细结束------------------- */

        /*         * *************-----------询单项明细开始------------------- */
        $inquiry_check_log_model = new InquiryCheckLogModel();
        $inquiry_check_log_table = $inquiry_check_log_model->getTableName(); //询单项明细表
        $inquiry_check_log_sql = '(select max(out_at) from ' . $inquiry_check_log_table . ' where inquiry_id=i.id';
        $field .= $inquiry_check_log_sql . ' and out_node=\'BIZ_QUOTING\' group by inquiry_id) as bq_time,'; //事业部报价日期
        $field .= $inquiry_check_log_sql . ' and out_node=\'LOGI_DISPATCHING\' group by inquiry_id) as ld_time,'; //物流接收日期
        $field .= $inquiry_check_log_sql . ' and out_node=\'LOGI_APPROVING\' group by inquiry_id) as la_time,'; //物流报出日期
        $field .= $inquiry_check_log_sql . ' and out_node=\'QUOTE_SENT\' group by inquiry_id) as qs_time,'; //报出日期
        /*         * *************-----------询单项明细结束------------------- */
        $field .= '(UNIX_TIMESTAMP(i.updated_at)-UNIX_TIMESTAMP(i.created_at))/86400 as quoted_time,'; //报价用时

        $employee_sql = '(select `name` from ' . $employee_table . ' where deleted_flag=\'N\' ';
        $field .= $employee_sql . ' AND id=i.agent_id)as agent_name,'; //市场负责人
        $field .= $employee_sql . ' AND id=i.quote_id)as quote_name,'; //商务技术部报价人
        $field .= $employee_sql . ' AND id=i.check_org_id)as check_org_name,'; //事业部负责人
        $field .= ' qt.brand,qt.quote_unit,qt.purchase_unit_price,qt.purchase_unit_price*qt.quote_qty as total,'; //total厂家总价（元）
        $field .= ' fqt.quote_unit_price,fqt.total_quote_price,(fqt.total_quote_price+fqt.total_logi_fee+fqt.total_bank_fee+fqt.total_insu_fee) as total_quoted_price,'; //报价总金额（美金）
        $field .= 'qt.gross_weight_kg,(qt.gross_weight_kg*qt.quote_qty) as total_kg,qt.package_size,qt.package_mode,qt.quote_qty,';
        $field .= 'qt.delivery_days,qt.period_of_validity,i.trade_terms_bn,';
        $field .= '(case i.status WHEN \'BIZ_DISPATCHING\' THEN \'事业部分单员\' '
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

        $inquiry_model = new InquiryModel();
        $list = $inquiry_model->alias('i')
                ->join($inquiry_item_table . ' as it on it.deleted_flag=\'N\' and it.inquiry_id=i.id', 'left')
                ->join($quote_item_table . ' as qt on qt.deleted_flag=\'N\' and qt.inquiry_id=i.id and qt.sku=it.sku', 'left')
                ->join($final_quote_item_table . ' as fqt on fqt.deleted_flag=\'N\' and fqt.inquiry_id=i.id and fqt.sku=it.sku', 'left')
                ->field($field)
                ->where(['i.deleted_flag' => 'N', 'i.status' => ['neq', 'DRAFT']])
                ->select();

        return $this->_createXls($list);
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
            'F' => ['buyer_code', '客户名称或代码'],
            'G' => ['remarks', '客户及项目背景描述'],
            'H' => ['name_zh', '品名中文'],
            'I' => ['name', '品名外文'],
            'J' => ['model', '规格'],
            'K' => [null, '图号'],
            'L' => ['qty', '数量'],
            'M' => ['unit', '单位'],
            'N' => [null, '油气or非油气'],
            'O' => [null, '平台产品分类'],
            'P' => [null, '产品分类'],
            'Q' => ['keruiflag', '是否科瑞设备用配件'],
            'R' => ['bidflag', '是否投标'],
            'S' => ['inflow_time', '转入日期'],
            'T' => ['quote_deadline', '需用日期'],
            'U' => [null, '澄清完成日期'],
            'V' => ['bq_time', '事业部报出日期'],
            'W' => ['ld_time', '物流接收日期'],
            'X' => ['la_time', '物流报出日期'],
            'Y' => ['qs_time', '报出日期'],
            'Z' => ['quoted_time', '报价用时'],
            'AA' => ['agent_name', '市场负责人'],
            'AB' => ['quote_name', '商务技术部报价人'],
            'AC' => ['check_org_name', '事业部负责人'],
            'AD' => ['brand', '产品品牌'],
            'AE' => ['quote_unit', '报价单位'],
            'AF' => [null, '供应商报价人'],
            'AG' => [null, '报价人联系方式'],
            'AH' => ['purchase_unit_price', '厂家单价（元）'],
            'AI' => ['total', '厂家总价（元）'],
            'AJ' => [null, '利润率'],
            'AK' => ['quote_unit_price', '报价单价（元）'],
            'AL' => ['total_quote_price', '报价总价（元）'],
            'AM' => ['total_quoted_price', '报价总金额（美金）'],
            'AN' => ['gross_weight_kg', '单重(kg)'],
            'AO' => ['total_kg', '总重(kg)'],
            'AP' => ['package_size', '包装体积(mm)'],
            'AQ' => ['package_mode', '包装方式'],
            'AR' => ['delivery_days', '交货期（天）'],
            'AS' => ['period_of_validity', '有效期（天）'],
            'AT' => ['trade_terms_bn', '贸易术语'],
            'AU' => ['istatus', '最新进度及解决方案'],
            'AV' => ['iquote_status', '报价后状态'],
            'AW' => ['quote_notes', '备注'],
            'AX' => [null, '报价超48小时原因类型'],
            'AY' => [null, '报价超48小时分析'],
            'AZ' => [null, '成单或失单'],
            'BA' => [null, '失单原因类型'],
            'BB' => [null, '失单原因分析'],
        ];
    }

    private function _createXls($list) {
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
        $keys = $this->_getKeys();
        $objSheet->setCellValue('A1', '序号');
        foreach ($keys as $rowname => $key) {
            $objSheet->setCellValue($rowname . '1', $key[1]);
        }
        foreach ($list as $j => $item) {
            $objSheet->setCellValue('A' . ($j + 2), ($j + 1));
            foreach ($keys as $rowname => $key) {

                if ($key && isset($item)) {
                    $objSheet->setCellValue($rowname . ($j + 2), $item[$key[0]]);
                } else {
                    $objSheet->setCellValue($rowname . ($j + 2), '');
                }
            }
        }
        $objSheet->freezePaneByColumnAndRow(2, 1);
        $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '00000000'),],],];
        $objSheet->getStyle('A1:BB' . ($j + 2))->applyFromArray($styleArray);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $file = $dirName . DS . '导出的询报价单' . date('YmdHi') . '.xls';
        $objWriter->save($file);

        if (file_exists($file)) {
            //把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $file;
            $data['type'] = 'application/xls';
            $data['name'] = '导出的询报价单' . date('YmdHi') . '.xls';
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

}
