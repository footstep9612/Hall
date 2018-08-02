<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ExportModel extends PublicModel {

    protected $tableName = 'inquiry';
    protected $dbName = 'erui_rfq';

    public function __construct() {

        parent::__construct();
    }

    /**
     * 导出询单列表
     * @return mix
     * @author zyg
     */
    public function Rfq($condition) {
        $where = ['i.deleted_flag' => 'N',
            'i.status' => ['neq', 'DRAFT'],
        ];
        if (!empty($condition['created_at_start']) && !empty($condition['created_at_end'])) {
            $created_at_start = trim($condition['created_at_start']);
            $created_at_end = date('Y-m-d H:i:s', strtotime(trim($condition['created_at_end'])) + 86399);
            $where['i.created_at'] = ['between', $created_at_start . ',' . $created_at_end];
        } elseif (!empty($condition['created_at_start'])) {
            $created_at_start = trim($condition['created_at_start']);
            $where['i.created_at'] = ['egt', $created_at_start];
        } elseif (!empty($condition['created_at_end'])) {
            $created_at_end = date('Y-m-d H:i:s', strtotime(trim($condition['created_at_end'])) + 86399);
            $where['i.created_at'] = ['elt', $created_at_end];
        }
        if (!empty($condition['country_bn'])) {
            $where['i.country_bn'] = ['in', explode(',', $condition['country_bn']) ?: ['-1']];
        }
        $field = 'i.id as inquiry_id,i.serial_no,i.project_name as name_zh,'
                . 'i.buyer_oil,i.country_bn,i.org_id,i.proxy_no,i.buyer_code,i.project_basic_info,'
                . 'if(i.proxy_flag=\'Y\',\'是\',\'否\') as proxy_flag,'
                . 'if(i.kerui_flag=\'Y\',\'是\',\'否\') as keruiflag,'
                . 'if(i.bid_flag=\'Y\',\'是\',\'否\') as bidflag ,'
                . 'i.quote_deadline,i.obtain_id,'
                . '1 as qty,\'批\' as unit,i.agent_id,i.quote_id,i.check_org_id,i.created_by,'
                . 'i.trade_terms_bn,i.quote_status,i.quote_notes,i.created_at';

        $list = $this->alias('i')
                ->field($field)
                ->where($where)
                ->select();

        $this->_setUserName($list, ['agent_name' => 'agent_id',
            'quote_name' => 'quote_id',
            'created_by_name' => 'created_by']);
        (new CountryModel())->setCountry($list, $this->lang);
        (new MarketAreaCountryModel())->setAreaBn($list);
        (new MarketAreaModel())->setArea($list);
        $this->_setOrgName($list);
        $this->_setIsErui($list);
        $this->_setCheckLog($list);
        $this->_setQuoteRate($list);
        $this->_setItems($list);

        $this->_setSupplierName($list);
        $this->_setquoted_time($list);
        $this->_setProductName($list);
        $this->_setConstPrice($list);
        $this->_setMaterialCat($list, 'zh');
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
        return [
            'A' => ['sequence_no', '序号'],
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
            'AG' => ['cc_dispatching_clarification_time', '易瑞分单员发起的澄清用时（小时）'], //易瑞分单员发起的澄清用时（小时）
            'AH' => ['biz_dispatching_clarification_time', '事业部分单员发起的澄清用时（小时）'], //事业部分单员发起的澄清用时（小时）
            'AI' => ['biz_quoting_clarification_time', '事业部报价人发起的澄清用时（小时）'], //事业部报价人发起的澄清用时（小时）
            'AJ' => ['logi_dispatching_clarification_time', '物流分单员发起的澄清用时（小时）'], //物流分单员发起的澄清用时（小时）
            'AK' => ['logi_quoting_clarification_time', '物流报价人发起的澄清用时（小时）'], //物流报价人发起的澄清用时（小时）
            'AL' => ['logi_approving_clarification_time', '物流审核发起的澄清用时（小时）'], //物流审核发起的澄清用时（小时）
            'AM' => ['biz_approving_clarification_time', '事业部核算发起的澄清用时（小时）'], //事业部核算发起的澄清用时（小时）
            'AN' => ['market_approving_clarification_time', '事业部审核发起的澄清用时（小时）'], //事业部审核发起的澄清用时（小时）
            'AO' => ['clarification_time', '项目澄清时间(小时)'],
            'AP' => ['real_quoted_time', '真实报价用时(去处澄清时间)(小时)'],
            'AQ' => ['whole_quoted_time', '整体报价时间(小时)'],
            'AR' => ['cc_quoted_time', '易瑞商务技术报价用时(小时)'],
            'AS' => ['biz_quoted_time', '事业部商务技术报价用时(小时)'],
            'AT' => ['logi_quoted_time', '物流报价时间(小时)'],
            'AU' => ['obtain_org_name', '获单主体单位)'],
            'AV' => ['obtain_name', '获取人)'],
            'AW' => ['created_by_name', '询单创建人'],
            'AX' => ['agent_name', '市场负责人'],
            'AY' => ['biz_despatching', '事业部分单人'],
            'AZ' => ['quote_name', '商务技术部报价人'],
            'BA' => ['check_org_name', '事业部负责人'],
            'BB' => ['brand', '产品品牌'],
            'BC' => ['supplier_name', '报价单位'],
            'BD' => [null, '供应商报价人'],
            'BE' => [null, '报价人联系方式'],
            'BF' => ['purchase_unit_price', '厂家单价（元）'],
            'BG' => ['purchase_price_cur_bn', '币种'],
            'BH' => ['total', '厂家总价（元）'],
            'BI' => ['purchase_price_cur_bn', '币种'],
            'BJ' => ['gross_profit_rate', '利润率'],
            'BK' => ['quote_unit_price', '报价单价（元）'],
            'BL' => ['quote_price_cur_bn', '币种'],
            'BM' => ['total_quote_price', '报价总价（元）'],
            'BN' => ['quote_price_cur_bn', '币种'],
            'BO' => ['total_quoted_price_usd', '报价总金额（美金）'],
            'BP' => ['gross_weight_kg', '单重(kg)'],
            'BQ' => ['total_kg', '总重(kg)'],
            'BR' => ['package_size', '包装体积(mm)'],
            'BS' => ['package_mode', '包装方式'],
            'BT' => ['delivery_days', '交货期（天）'],
            'BU' => ['period_of_validity', '有效期（天）'],
            'BV' => ['trade_terms_bn', '贸易术语'],
            'BW' => ['istatus', '最新进度及解决方案'],
            'BX' => ['iquote_status', '报价后状态'],
            'BY' => ['quote_notes', '备注'],
            'BZ' => ['reason_for_no_quote', '未报价分析'],
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
        PHPExcel_Settings::setCacheStorageMethod(
                PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array('memoryCacheSize' => '512MB'));
        $objPHPExcel = new PHPExcel();

        $objSheet = $objPHPExcel->setActiveSheetIndex(0);    //当前sheet
        $objSheet->setTitle('询报价单表');
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        if ($name == '导出总行询单数据') {
            $keys = $this->_getTotalKeys();
        } else {
            $keys = $this->_getKeys();
        }

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
        $objSheet->getStyle('A1:BZ' . ($j + 2))->applyFromArray($styleArray);
        $objSheet->getStyle('A1:BZ' . ($j + 2))->getAlignment()->setShrinkToFit(true); //字体变小以适应宽
        $objSheet->getStyle('A1:BZ' . ($j + 2))->getAlignment()->setWrapText(true); //自动换行
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $file = $dirName . DS . $name . date('YmdHi') . '.xls';
        $objWriter->save($file);
        if (file_exists($file)) {

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
        foreach ($clarifyMapping as $k => $v) {
            $clarifyNode[] = $k;
        }
        foreach ($list as &$item) {
            $where['inquiry_id'] = $item['inquiry_id'];
            foreach ($clarifyMapping as $v) {

                $item[$v] = '';
            }
            $item['clarification_time'] = '';

            $clarifyList = $inquiryCheckLogModel->field('out_node, (UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) AS clarify_time')->where(array_merge($where, [/* 'id' => ['gt', $referenceID], */ 'in_node' => 'CLARIFY', 'out_node' => ['in', /* array_diff($clarifyNode, ['BIZ_DISPATCHING', 'CC_DISPATCHING']) */ $clarifyNode]]))->order('id ASC')->select();
            foreach ($clarifyList as $clarify) {

                $item[$clarifyMapping[$clarify['out_node']]] += $clarify['clarify_time'];
            }
            $nodeData = $inquiryCheckLogModel->field('in_node, out_node, UNIX_TIMESTAMP(out_at) AS out_time'
                    )->where($where)->order('id DESC')->find();

            $lastClarifyTime = '';
            if ($nodeData['out_node'] == 'CLARIFY' && in_array($nodeData['in_node'], $clarifyNode)) {
                $lastClarifyTime = $nowTime - $nodeData['out_time'];
                $item[$clarifyMapping[$nodeData['in_node']]] += $lastClarifyTime;
            }
            foreach ($clarifyMapping as $v) {
                if ($item[$v] > 0) {
                    $item['clarification_time'] += $item[$v];

                    $item[$v] = number_format($item[$v] / 3600, 2);
                }
            }


            if ($item['clarification_time'] > 0) {
                $item['clarification_time'] = number_format($item['clarification_time'] / 3600, 2);
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
        foreach ($quoteMapping as $k => $v) {
            $quoteNode[] = $k;
        }
        foreach ($list as &$item) {
            $quoteTime = [];
            foreach ($quoteMapping as $v) {
// 报价用时初始化
                $item[$v] = '';
            }
// 各环节的报价用时列表
            $spendList = $inquiryCheckLogModel->field('in_node, (UNIX_TIMESTAMP(out_at) - UNIX_TIMESTAMP(into_at)) AS quote_time')->where([/* 'action' => ['neq', 'CLARIFY'], */ 'inquiry_id' => $item['inquiry_id'], 'in_node' => ['in', $quoteNode]])->select();
            foreach ($spendList as $spend) {
// 计算各环节的报价用时
                $quoteTime[$quoteMapping[$spend['in_node']]] += $spend['quote_time'];
            }
// 物流报价用时
            $logiSpend = $quoteTime['logi_dispatching_quoted_time'] + $quoteTime['logi_quoting_quoted_time'] + $quoteTime['logi_approving_quoted_time'];
            $item['logi_quoted_time'] = number_format($logiSpend / 3600, 2);
            $tmpDispatchingSpend = $quoteTime['biz_quoting_quoted_time'] + $quoteTime['biz_approving_quoted_time'] + $quoteTime['market_approving_quoted_time'];
            if ($item['org_is_erui'] == 'Y') {
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
            $qsSpend = strtotime($item['qs_time']);
            $wholeSpend = ($qsSpend > 0 ? $qsSpend : $nowTime) - strtotime($item['inflow_time']);
            $item['whole_quoted_time'] = number_format($wholeSpend / 3600, 2);
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
        foreach ($list as $item) {
            $serialNo = $item['serial_no'];
            $tmpData = $tmpList[$serialNo];
            $tmpList[$serialNo] = $item;
            $tmpList[$serialNo]['name_zh'] = $item['project_name'];
            $tmpList[$serialNo]['name'] = '';
            $tmpList[$serialNo]['supplier_name'] = '';
            $tmpList[$serialNo]['model'] = '';
            $tmpList[$serialNo]['qty'] = '1';
            $tmpList[$serialNo]['unit'] = '批';
            $tmpList[$serialNo]['category'] = $inquiryItemModel->field('COUNT(id) AS count, category')->where(['inquiry_id' => $item['inquiry_id'], 'category' => ['neq', ''], 'deleted_flag' => 'N'])->group('category')->order('count DESC')->find()['category'];
            $tmpList[$serialNo]['brand'] = '';
            $tmpList[$serialNo]['purchase_unit_price'] = '';
            $tmpList[$serialNo]['total'] = '';
            $tmpList[$serialNo]['quote_unit_price'] = '';
            $tmpList[$serialNo]['quote_price_cur_bn'] = 'USD';
            $tmpList[$serialNo]['total_quote_price'] = $tmpData['total_quote_price'] + round($item['total_quote_price'] / $this->_getRateUSD($item['purchase_price_cur_bn']), 2);
            $tmpList[$serialNo]['total_quoted_price_usd'] += $tmpData['total_quoted_price_usd'];
            $tmpList[$serialNo]['gross_weight_kg'] = '';
            $tmpList[$serialNo]['total_kg'] += $tmpData['total_kg'];
            $tmpList[$serialNo]['package_size'] += $tmpData['package_size'];
            $tmpList[$serialNo]['package_mode'] = '';
        }
        foreach ($list as $item) {
            $serialNo = $item['serial_no'];
            if (!in_array($serialNo, $serialNoList)) {
                $tmpList[$serialNo]['sequence_no'] = ++$i;
                $newList[] = $tmpList[$serialNo];
                $serialNoList[] = $serialNo;
            }
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
        } else {
            return $this->_getRate($cur, 'USD');
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

            return $exchangeRate['rate'];
        } else {
            return false;
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
            if ($item['qs_time']) {
                $list[$key]['quoted_time'] = $this->date_diff($item['qs_time'], $item['created_at']);
            } else {
                $list[$key]['quoted_time'] = $this->date_diff(date('Y-m-d H:i:s'), $item['created_at']);
            }
        }
    }

    private function _setCalculatePrice(&$list) {
        $exchange_rate_model = new ExchangeRateModel();

        foreach ($list as $key => $item) {
// 只在市场确认、报价单已发出、报价关闭环节显示报价金额
            if (!in_array($item['istatus'], ['市场确认', '报价单已发出', '报价关闭'])) {
                $list[$key]['quote_unit_price'] = $list[$key]['total_quote_price'] = $list[$key]['total_quoted_price_usd'] = '';
            } else {
                $list[$key]['quote_price_cur_bn'] = $item['purchase_price_cur_bn'];
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

    private function _setUserName(&$arr, $fileds) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if (isset($val[$filed]) && $val[$filed]) {
                        $userids[] = $val[$filed];
                    }
                }
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed_key => $filed) {
                    if ($val[$filed] && isset($usernames[$val[$filed]])) {
                        $val[$filed_key] = $usernames[$val[$filed]];
                    } else {
                        $val[$filed_key] = '';
                    }
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setOrgName(&$arr) {
        if ($arr) {
            $org_model = new OrgModel();
            $org_ids = [];
            foreach ($arr as $key => $val) {
                !empty($val['org_id']) ? $org_ids[] = $val['check_org_id'] : '';
                !empty($val['check_org_id']) ? $org_ids[] = $val['check_org_id'] : '';
            }
            $orgnames = [];
            if ($org_ids) {
                $orgs = $org_model->where(['id' => ['in', $org_ids], 'deleted_flag' => 'N'])
                                ->field('id,name')->select();
                foreach ($orgs as $org) {
                    $orgnames[$org['id']] = $org['name'];
                }
            }
            foreach ($arr as $key => $val) {
                $val['org_name'] = $val['org_id'] && isset($orgnames[$val['org_id']]) ? $orgnames[$val['org_id']] : '';
                $val['check_org_name'] = $val['check_org_id'] && isset($orgnames[$val['check_org_id']]) ? $orgnames[$val['check_org_id']] : '';
                $val['buyer_oil'] = $val['buyer_oil'] == 'Y' ? '是' : '否';
                $val['proxy_flag'] = $val['proxy_flag'] == 'Y' ? '是' : '否';
                $val['keruiflag'] = $val['kerui_flag'] == 'Y' ? '是' : '否';
                $val['bidflag'] = $val['bid_flag'] == 'Y' ? '是' : '否';
                switch ($val['quote_status']) {
                    case 'NOT_QUOTED':$val['iquote_status'] = '未报价';
                        break;
                    case 'ONGOING':$val['iquote_status'] = '报价中';
                        break;
                    case 'QUOTED':$val['iquote_status'] = '已报价';
                        break;
                    case 'COMPLETED':$val['iquote_status'] = '已完成';
                        break;
                    default :$val['iquote_status'] = '未报价';
                        break;
                }
                switch ($val['status']) {
                    case 'BIZ_DISPATCHING':$val['istatus'] = '事业部分单员';
                        break;
                    case 'CLARIFY':$val['istatus'] = '项目澄清';
                        break;
                    case 'REJECT_MARKET':$val['istatus'] = '驳回市场';
                        break;
                    case 'REJECT_CLOSE':$val['istatus'] = '驳回市场关闭';
                        break;
                    case 'CC_DISPATCHING':$val['istatus'] = '易瑞客户中心';
                        break;
                    case 'BIZ_QUOTING':$val['istatus'] = '事业部报价';
                        break;
                    case 'LOGI_DISPATCHING':$val['istatus'] = '物流分单员';
                        break;
                    case 'LOGI_QUOTING':$val['istatus'] = '物流报价';
                        break;
                    case 'LOGI_APPROVING':$val['istatus'] = '物流审核';
                        break;
                    case 'BIZ_APPROVING':$val['istatus'] = '事业部核算';
                        break;
                    case 'MARKET_APPROVING':$val['istatus'] = '市场主管审核';
                        break;
                    case 'MARKET_CONFIRMING':$val['istatus'] = '市场确认';
                        break;
                    case 'QUOTE_SENT':$val['istatus'] = '报价单已发出';
                        break;
                    case 'INQUIRY_CLOSED':$val['istatus'] = '报价关闭';
                        break;
                    default :$val['istatus'] = '错误';
                        break;
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 事业部是否易瑞
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     *
     */

    private function _setIsErui(&$arr) {
        $org_model = new OrgModel();
        $org_ids = [];
        foreach ($arr as $val) {
            if (!empty($val['org_id'])) {
                $org_ids[] = $val['org_id'];
            }
        }
        $orgnames = [];
        if ($org_ids) {
            $orgs = $org_model->where(['id' => ['in', $org_ids],
                                'org_node' => 'erui',
                                'deleted_flag' => 'N'])
                            ->field('id,name')->select();
            foreach ($orgs as $org) {
                $orgnames[$org['id']] = $org['name'];
            }

            foreach ($arr as $key => $val) {
                if (!empty($orgnames[$val['org_id']])) {
                    $val['org_is_erui'] = 'Y';
                } else {
                    $val['org_is_erui'] = 'N';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 询单项明细开始
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     *
     */

    private function _setCheckLog(&$arr) {
        $inquiry_check_log_model = new InquiryCheckLogModel();

        $inquiry_ids = [];
        foreach ($arr as $val) {
            if (!empty($val['id'])) {
                $inquiry_ids[] = $val['id'];
            }
        }

        if ($inquiry_ids) {
            $field = 'inquiry_id,'
                    . 'min(if(in_node=\'BIZ_DISPATCHING\',into_at,0)) as inflow_time,'
                    . 'min(if(out_node=\'BIZ_DISPATCHING\',out_at,0)) as inflow_time_out,'
                    . 'max(if(in_node=\'CLARIFY\',out_at,0)) as max_inflow_time,'
                    . 'max(max(if(out_node in(\'BIZ_DISPATCHING\' ,\'CC_DISPATCHING\') ,out_at,0)) as max_inflow_time_out,'
                    . 'max(if(in_node=\'BIZ_QUOTING\' ,out_at,0)) as bq_time,'
                    . 'max(if(out_node=\'LOGI_DISPATCHING\' ,out_at,0)) as ld_time,'
                    . 'max(if(in_node=\'LOGI_QUOTING\' ,out_at,0)) as la_time,'
                    . 'max(if(in_node=\'MARKET_APPROVING\' ,out_at,0)) as qs_time';
            $inflow_times = $inquiry_check_log_model->where(['inquiry_id' => ['in', $inquiry_ids]])
                    ->field($field)
                    ->group('inquiry_id')
                    ->select();
            $times = [];
            foreach ($inflow_times as $inflow_time) {
                $times[$inflow_time['inquiry_id']] = $inflow_time;
            }

            foreach ($arr as $key => $val) {
                if (!empty($times[$val['id']])) {

                    $val['inflow_time'] = $times[$val['id']]['inflow_time'];
                    $val['inflow_time_out'] = $times[$val['id']]['inflow_time_out'];
                    $val['max_inflow_time'] = $times[$val['id']]['max_inflow_time'];
                    $val['max_inflow_time_out'] = $times[$val['id']]['max_inflow_time_out'];
                    $val['bq_time'] = $times[$val['id']]['bq_time'];
                    $val['ld_time'] = $times[$val['id']]['ld_time'];
                    $val['la_time'] = $times[$val['id']]['la_time'];
                    $val['qs_time'] = $times[$val['id']]['qs_time'];
                } else {
                    $val['inflow_time'] = '';
                    $val['inflow_time_out'] = '';
                    $val['max_inflow_time'] = '';
                    $val['max_inflow_time_out'] = '';
                    $val['bq_time'] = '';
                    $val['ld_time'] = '';
                    $val['la_time'] = '';
                    $val['qs_time'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 询单项明细开始
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     *
     */

    private function _setQuoteRate(&$arr) {
        $quote_model = new QuoteModel();

        $inquiry_ids = [];
        foreach ($arr as $val) {
            if (!empty($val['id'])) {
                $inquiry_ids[] = $val['id'];
            }
        }

        if ($inquiry_ids) {
            $field = 'inquiry_id,gross_profit_rate,exchange_rate';
            $inflow_times = $quote_model->where(['inquiry_id' => ['in', $inquiry_ids]])
                    ->field($field)
                    ->select();
            $times = [];
            foreach ($inflow_times as $inflow_time) {
                $times[$inflow_time['inquiry_id']] = $inflow_time;
            }

            foreach ($arr as $key => $val) {
                if (!empty($times[$val['id']])) {

                    $val['gross_profit_rate'] = $times[$val['id']]['gross_profit_rate'];
                    $val['exchange_rate'] = $times[$val['id']]['exchange_rate'];
                } else {
                    $val['gross_profit_rate'] = '';
                    $val['exchange_rate'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 询单项明细开始
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     *
     */

    private function _setItems(&$arr) {
        $inquiry_item_model = new InquiryItemModel();
        $quote_item_model = new QuoteItemModel();
        $final_quote_item_model = new FinalQuoteItemModel();
        $inquiry_ids = [];
        foreach ($arr as $val) {
            if (!empty($val['id'])) {
                $inquiry_ids[] = $val['id'];
            }
        }

        if ($inquiry_ids) {
            $field = 'name_zh,name,model,qty,category,unit,inquiry_id,id';
            $items = $inquiry_item_model
                    ->where(['inquiry_id' => ['in', $inquiry_ids], 'deleted_flag' => 'N'])
                    ->field($field)
                    ->select();
            $quoteitems = $quote_item_model
                    ->where(['inquiry_id' => ['in', $inquiry_ids], 'deleted_flag' => 'N'])
                    ->field('sku,supplier_id,purchase_price_cur_bn,reason_for_no_quote,inquiry_item_id,
brand,quote_unit,purchase_unit_price,purchase_unit_price*quote_qty as total,
gross_weight_kg,(gross_weight_kg*quote_qty) as total_kg,package_size,package_mode,quote_qty,
delivery_days,period_of_validity')
                    ->select();
            $final_quoteitems = $final_quote_item_model
                    ->where(['inquiry_id' => ['in', $inquiry_ids], 'deleted_flag' => 'N'])
                    ->field('inquiry_item_id,quote_unprice,total_quote_price,(total_quote_price+total_logi_fee+total_bank_fee+total_insu_fee) as total_quoted_price')
                    ->select();
            $final_quote_items = $quote_items = $inquiry_items = [];
            foreach ($items as $item) {
                $inquiry_items[$item['inquiry_id']][$item['id']] = $item;
            }
            foreach ($quoteitems as $item) {
                $quote_items[$item['inquiry_item_id']] = $item;
            }
            foreach ($final_quoteitems as $item) {
                $final_quote_items[$item['inquiry_item_id']] = $item;
            }
            foreach ($arr as $key => $val) {
                if (!empty($inquiry_items[$val['id']])) {

                    foreach ($inquiry_items[$val['id']] as $inquiry_item) {

                        $inquiry_item['quote'] = !empty($quote_items[$inquiry_item['id']]) ? $quote_items[$inquiry_item['id']] : [];
                        $inquiry_item['final_quote'] = !empty($final_quote_items[$inquiry_item['id']]) ? $final_quote_items[$inquiry_item['id']] : [];

                        $val['items'][] = $inquiry_item;
                    }
                } else {
                    $val['items'] = [];
                }
                $arr[$key] = $val;
            }
        }
    }

}
