<?php
/*
 * @desc 导入和导出EXCL
 *
 * @author liujf
 * @time 2017-12-21
 */
class ExcelimportandexportController extends PublicController {

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        
        $this->inquiryModel = new InquiryModel();
        $this->inquiryCheckLogModel = new InquiryCheckLogModel();
        $this->inquiryItemModel = new InquiryItemModel();
        $this->buyerModel = new BuyerModel();
        $this->employeeModel = new EmployeeModel();
        $this->orderBuyerContactModel = new OrderBuyerContactModel();
        $this->orderModel = new OrderModel();
        $this->orderContactModel = new OrderContactModel();
        $this->orderDeliveryModel = new OrderDeliveryModel();
        $this->orderAddressModel = new OrderAddressModel();
        $this->orderPaymentModel = new OrderPaymentModel();
        $this->orderAttachModel = new OrderAttachModel();
        $this->orderLogModel = new OrderLogModel();
        
        $this->time = date('Y-m-d H:i:s');
        
        $this->_getExcelDir();
    }
    
    /**
     * @desc 导入订单基本信息
     *
     * @author liujf
     * @time 2017-12-22
     */
    public function importOrderBaseInfoAction() {
        $condition = $this->_trim($this->getPut());
        
        if ($condition['floder'] == '') jsonReturn('', -101, '缺少文件夹参数!');
        
        $classify = $this->_getTempletAndAttach($condition['floder']);
        
        // 获取模板数据
        $templetData = ExcelHelperTrait::ready2import($classify['templet'][0]);
        
        /* 批量导入数据*/
        $dataIndex = 0;
        $importOrderList = $importBuyerContactList = $importOrderContactList = $importDeliveryList = $importAddressList = $importPaymentList = $executeNoArr = $orderIdMapping = $importAttachList = $attachNameMapping = [];
        // 没有客户的订单执行单号
        $noBuyerExecuteNoArr = [];
        foreach ($templetData as $data) {
            if ($dataIndex > 1 && !empty($data)) {
                $data = $this->_trim($data);
                $buyerId = $this->buyerModel->where(['name' => $data[3], 'buyer_code' => $data[4], 'deleted_flag' => 'N'])->getField('id');
                // 没有客户就不插入该数据，记录订单执行单号
                if (!$buyerId) {
                    $noBuyerExecuteNoArr[] = $data[1];
                    $dataIndex++;
                    continue;
                }
                $orderData = [
                    'po_no' => $data[0],
                    'execute_no' => $data[1],
                    'contract_date' => $data[2],
                    'buyer_id' => $buyerId,
                    'order_agent' => $this->employeeModel->getUserIdByNo($data[5]),
                    'execute_date' => $data[6],
                    'agent_id' => $this->employeeModel->getUserIdByNo($data[10]),
                    'amount' => $data[14],
                    'trade_terms_bn' => $data[15],
                    'trans_mode_bn' => $data[16],
                    'from_country_bn' => $data[17],
                    'from_port_bn' => $data[18],
                    'to_country_bn' => $data[19],
                    'to_port_bn' => $data[20],
                    'address' => $data[21],
                    'deleted_flag' => 'N',
                    'created_at' => $this->time
                ];
                // 需要导入的订单执行单号和数据
                $executeNoArr[] = $data[1];
                $importOrderList[] = $orderData;
                // 需要导入的采购商联系人数据
                $buyerContactData = [
                    'execute_no' => $data[1],
                    'name' => $data[7],
                    'phone' => $data[8], 
                    'email' => $data[9],
                    'created_at' => $this->time
                ];
                $importBuyerContactList[] = $buyerContactData;
                // 需要导入的订单联系人数据
                $orderContactData = [
                    'name' => $data[11],
                    'company' => '易瑞',
                    'phone' => $data[12],
                    'email' => $data[13],
                    'created_at' => $this->time
                ];
                $importOrderContactList[] = $orderContactData;
                // 需要导入的附件名称和执行单号的映射(非必填)
                if ($data[22] != '') {
                    $attachNameMapping[$data[1]] = $data[22];
                }
                // 需要导入的交货信息数据(非必填)
                if ($data[23] != '') {
                    $deliveryData = json_decode($data[23], true);
                    $deliveryData['execute_no'] = $data[1];
                    $deliveryData['created_at'] = $this->time;
                    $importDeliveryList[] = $deliveryData;
                }
                // 需要导入的收货人地址数据
                $addressData = json_decode($data[24], true);
                $addressData['created_at'] = $this->time;
                $importAddressList[] = $addressData;
                // 需要导入的结算方式数据(非必填)
                if ($data[25] != '') {
                    $paymentData = json_decode($data[25], true);
                    $paymentData['execute_no'] = $data[1];
                    $paymentData['created_at'] = $this->time;
                    $importPaymentList[] = $paymentData;
                }
            }
            $dataIndex++;
        }
        // 需要导入的订单数据列表中加入订单编号
        $listCount = count($importOrderList);
        $orderNoArr = $this->_getOrderNoArr($listCount);
        for ($i = 0; $i < $listCount; $i++) {
            $importOrderList[$i]['order_no'] = $orderNoArr[$i];
        }
        if ($importOrderList) {
            // 导入订单数据
            $importOrderResult = $this->orderModel->addAll($importOrderList);
            if (!$importOrderResult) jsonReturn('', -101, '订单数据导入失败!');
            // 订单执行单号和订单ID的映射关系
            foreach ($executeNoArr as $executeNo) {
                $orderIdMapping[$executeNo] = $this->orderModel->where(['execute_no' => $executeNo, 'deleted_flag' => 'N'])->order('id DESC')->getField('id');
            }
            $j = 0;
            foreach ($importBuyerContactList as &$importBuyerContact) {
                $importBuyerContact['order_id'] = $importAddressList[$j]['order_id'] = $importOrderContactList[$j]['order_id'] = $orderIdMapping[$importBuyerContact['execute_no']];
                unset($importBuyerContact['execute_no']);
                $j++;
            }
            // 导入采购商联系人数据
            if ($importBuyerContactList) {
                $importBuyerContactResult = $this->orderBuyerContactModel->addAll($importBuyerContactList);
                if (!$importBuyerContactResult) jsonReturn('', -101, '采购商联系人数据导入失败!');
            }
            // 更新订单表中的采购商联系人ID
            foreach ($importBuyerContactList as $importBuyerContact) {
                $buyerContactId = $this->orderBuyerContactModel->where(['order_id' => $importBuyerContact['order_id']])->getField('id');
                $this->orderModel->where(['id' => $importBuyerContact['order_id']])->save(['order_contact_id' => $buyerContactId, 'buyer_contact_id' => $buyerContactId]);
            }
            // 导入订单联系人数据
            if ($importOrderContactList) {
                $importOrderContactResult = $this->orderContactModel->addAll($importOrderContactList);
                if (!$importOrderContactResult) jsonReturn('', -101, '订单联系人数据导入失败!');
            }
            // 导入交货信息数据
            foreach ($importDeliveryList as &$importDelivery) {
                $importDelivery['order_id'] = $orderIdMapping[$importDelivery['execute_no']];
                unset($importDelivery['execute_no']);
            }
            if ($importDeliveryList) {
                $importDeliveryResult = $this->orderDeliveryModel->addAll($importDeliveryList);
                if (!$importDeliveryResult) jsonReturn('', -101, '交货信息数据导入失败!');
            }
            // 导入收货人地址数据
            if ($importAddressList) {
                $importAddressResult = $this->orderAddressModel->addAll($importAddressList);
                if (!$importAddressResult) jsonReturn('', -101, '收货人地址数据导入失败!');
            }
            // 导入结算方式数据
            foreach ($importPaymentList as &$importPayment) {
                $importPayment['order_id'] = $orderIdMapping[$importPayment['execute_no']];
                unset($importPayment['execute_no']);
            }
            if ($importPaymentList) {
                $importPaymentResult = $this->orderPaymentModel->addAll($importPaymentList);
                if (!$importPaymentResult) jsonReturn('', -101, '结算方式数据导入失败!');
            }
            
            /* 批量上传和导入附件*/
            $this->_batchImportOrderAttach($classify['attach'], 'PO', $orderIdMapping, $attachNameMapping);
            
            /* 导入成功，返回没有客户的订单执行单号(如果存在)*/
            $this->jsonReturn($noBuyerExecuteNoArr ? ['execute_no' => $noBuyerExecuteNoArr] : true);
            
        } else jsonReturn('', -101, '没有可导入的数据!');
    }
    
    /**
     * @desc 导入订单日志信息
     *
     * @author liujf
     * @time 2017-12-26
     */
    public function importOrderLogInfoAction() {
        $condition = $this->_trim($this->getPut());
    
        if ($condition['floder'] == '') jsonReturn('', -101, '缺少文件夹参数!');
    
        $classify = $this->_getTempletAndAttach($condition['floder']);
        $logGroup = strtoupper($condition['log_group']);
        $logGroupArr = ['OUTBOUND', 'LOGISTICS', 'DELIVERY', 'COLLECTION', 'CREDIT'];
        
        if (!in_array($logGroup, $logGroupArr)) jsonReturn('', -101, '需要正确的日志分组类别!');
    
        // 获取模板数据
        $templetData = ExcelHelperTrait::ready2import($classify['templet'][0]);
    
        /* 批量导入数据*/
        $dataIndex = 0;
        $importOrderLogList = $orderIdMapping = $importAttachList = $attachNameMapping = [];
        foreach ($templetData as $data) {
            if ($dataIndex > 1 && !empty($data)) {
                $data = $this->_trim($data);
                $orderId = $this->orderModel->where(['execute_no' => $data[0], 'deleted_flag' => 'N'])->order('id DESC')->getField('id');
                // 订单执行单号和订单ID的映射关系
                $orderIdMapping[$data[0]] = $orderId;
                $orderLogData = [
                    'order_id' => $orderId,
                    'log_group' => $logGroup,
                    'created_at' => $this->time
                ];
                switch ($logGroup) {
                    case 'OUTBOUND' :
                        $orderLogData['out_no'] = $data[1];
                        $orderLogData['content'] = $data[2];
                        $orderLogData['log_at'] = $data[3];
                        // 需要导入的附件名称和执行单号的映射(非必填)
                        if ($data[4] != '') {
                            $attachNameMapping[$data[0]] = $data[4];
                        }
                        break;
                    case 'LOGISTICS' :
                        $orderLogData['waybill_no'] = $data[1];
                        $orderLogData['content'] = $data[2];
                        $orderLogData['log_at'] = $data[3];
                        if ($data[4] != '') {
                            $attachNameMapping[$data[0]] = $data[4];
                        }
                        break;
                    case 'DELIVERY' :
                        $orderLogData['content'] = $data[1];
                        $orderLogData['log_at'] = $data[2];
                        if ($data[3] != '') {
                            $attachNameMapping[$data[0]] = $data[3];
                        }
                        break;
                    case 'CREDIT' :
                        $orderLogData['type'] = $data[1];
                        $orderLogData['amount'] = $data[2];
                        $orderLogData['log_at'] = $data[3];
                        break;
                    case 'COLLECTION' :
                        $orderLogData['content'] = $data[1];
                        $orderLogData['amount'] = $data[2];
                        $orderLogData['log_at'] = $data[3];
                        // 更新订单的收款状态
                        $this->orderModel->where(['id' => $orderId])->setField('pay_status', $data[4]);
                }
                // 需要导入的订单日志信息数据
                $importOrderLogList[] = $orderLogData;
            }
            $dataIndex++;
        }
        if ($importOrderLogList) {
            // 导入订单日志信息数据
            $importOrderLogResult = $this->orderLogModel->addAll($importOrderLogList);
            if (!$importOrderLogResult) jsonReturn('', -101, '订单日志信息数据导入失败!');
    
            /* 批量上传和导入附件*/
            $this->_batchImportOrderAttach($classify['attach'], $logGroup, $orderIdMapping, $attachNameMapping);
    
            /* 导入成功*/
            $this->jsonReturn(true);
            
        } else jsonReturn('', -101, '没有可导入的数据!');
    }
    
    public function getPut() {
        $key = '9b2a37b7b606c14d43db538487a148c7';
        $input = json_decode(file_get_contents("php://input"), true);
        $post = $this->getPost();
        $param = $input ? $input : $post;
        $sign = md5($key . $param['input']);
        if ($param['sign'] != $sign) {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('验证失败!');
            parent::jsonReturn();
        }
        return json_decode($param['input'], true) ? : $param['input'];
    }
    
    /**
     * @desc 获取模板和附件
     *
     * @param string $floder 需要导入的目录
     * @return array
     * @author liujf
     * @time 2017-12-24
     */
    private function _getTempletAndAttach($floder) {
        // 导入路径
        $path = ($this->excelDir ? : $this->getExcelDir()) . DS . $floder;
        return $this->_fileClassify($path);
    }
    
    /**
     * @desc 获取excel文件存放目录
     *
     * @return string
     * @author liujf
     * @time 2017-12-22
     */
    private function _getExcelDir() {
        $excelDir = MYPATH . DS . 'public' . DS . 'tmp';
        if (!is_dir($excelDir)) mkdir($excelDir, 0777, true);
        return $this->excelDir = $excelDir;
    }
    
    /**
     * @desc 对需要导入目录下的文件进行分类
     *
     * @param string $path 目录路径
     * @return array
     * @author liujf
     * @time 2017-12-22
     */
    private function _fileClassify($path) {
        $flies = $data = [];
        $this->_scanDir($path, $flies);
        foreach ($flies as $flie) {
            if (preg_match('/^.*templet(\d+)?\.xls(x)?$/i', $flie)) {
                $data['templet'][] = $flie;
            } else if (!preg_match('/^.*\.txt$/i', $flie)) {
                $data['attach'][] = $flie;
            }
        }
        return $data;
    }
    
    /**
     * @desc 扫描目录下所有文件
     *
     * @param string $path 扫描路径
     * @param array $flies 文件路径
     * @author liujf
     * @time 2017-12-22
     */
    private function _scanDir($path, &$flies) {
        if (is_dir($path)) {
            $dp = dir($path);
            while($file = $dp->read()) {
                if($file != '.' && $file != '..') $this->_scanDir($path . DS . $file, $flies);
            }
            $dp->close();
        }
        if(is_file($path)) $flies[] = $path;
    }
    
    /**
     * @desc 获取附件的执行单号
     *
     * @param string $attach 附件路径
     * @return string
     * @author liujf
     * @time 2017-12-26
     */
    private function _getAttachExecuteNo($attach) {
        $ds = DS;
        $pattern = "/.*\\{$ds}([^{$ds}-]+)-[^{$ds}-]+$/i";
        preg_match($pattern, $attach, $match);
        return $match[1];
    }
    
    /**
     * @desc 批量上传和导入订单相关附件
     *
     * @param array $attachList 需要导入的附件集合
     * @param string $attachGroup 附件分组
     * @param array $orderIdMapping 订单ID和执行单号的映射关系
     * @param array $attachNameMapping 订单相关附件名称和执行单号的映射关系
     * @author liujf
     * @time 2017-12-26
     */
    private  function _batchImportOrderAttach(&$attachList, $attachGroup, &$orderIdMapping, &$attachNameMapping) {
        foreach ($attachList as $attach) {
            // 执行附件上传
            $fileInfo = $this->_upload2FastDFS($attach);
            if ($fileInfo['code'] == 1) {
                $attachExecuteNo = $this->_getAttachExecuteNo($attach);
                $attachData = [
                    'order_id' => $orderIdMapping[$attachExecuteNo],
                    'attach_group' => $attachGroup,
                    'attach_name' => $attachNameMapping[$attachExecuteNo] ? : $attachGroup,
                    'attach_url' => $fileInfo['url'],
                    'created_at' => $this->time
                ];
                $importAttachList[] = $attachData;
            }
        }
        // 导入附件数据
        if ($importAttachList) {
            $importAttachResult = $this->orderAttachModel->addAll($importAttachList);
            if (!$importAttachResult) jsonReturn('', -101, '附件数据导入失败!');
        }
    }
    
    /**
     * @desc 上传文件到FastDFS
     *
     * @param string $file 文件路径
     * @return array
     * @author liujf
     * @time 2017-12-27
     */
    private function _upload2FastDFS($file) {
        $server = Yaf_Application::app()->getConfig()->myhost;
        // 上传文件的接口地址
        $url = $server . '/V2/Uploadfile/upload';
        // 上传的文件信息
        $data = [
            'tmp_name' => $file,
            'name' => pathinfo($file, PATHINFO_BASENAME),
            'type' => ExcelHelperTrait::getFileType($file)
        ];
        // 执行文件上传
        return postfile($data, $url);
    }
    
    /**
     * @desc 获取需要导入的订单编号组
     *
     * @param int $size 订单数量
     * @return mixed
     * @author liujf
     * @time 2017-12-24
     */
    private function _getOrderNoArr($size) {
        if (!is_int($size)) return false;
        $orderNoArr = [];
        if ($size > 0) {
            $orderNo = $this->_getNewOrderNo();
            $no = intval(substr($orderNo, 8));
            $today = date('Ymd');
            for ($i = 0; $i < $size; $i++) {
                $orderNoArr[] = $today . str_pad($no, 4, '0', STR_PAD_LEFT);
                $no++;
            }
        }
        return $orderNoArr;
    }
    
    /**
     * @desc 获取最新的订单编号
     *
     * @return string
     * @author  liujf
     * @time 2017-12-23
     */
    private function _getNewOrderNo() {
        $today = date('Ymd');
        $orderNo = $this->orderModel->where(['order_no' => ['like', $today . '%']])->order('id desc')->getField('order_no');
        if (empty($orderNo)) {
            return $today . '0001';
        }
        $no = intval(substr($orderNo, 8)) + 1;
        return $today . str_pad($no, 4, '0', STR_PAD_LEFT);
    }

    /*----------------------------------------------------------------------导入和导出代码界线----------------------------------------------------------------------*/
    
    /**
     * @desc 导出excel文件页面
     *
     * @author liujf
     * @time 2017-12-21
     */
    public function exportExcelAction() {
        $this->getView()->assign("title", "数据导出");
        $this->display('export');
    }
    
    /**
     * @desc 导出报价分析数据
     *
     * @author liujf
     * @time 2017-12-21
     */
    public function inquiryExportDataAction() {
        $this->getPut();
    
        $where['a.deleted_flag'] = 'N';
        $where['a.status'] = ['neq', 'DRAFT'];
        $lang = 'zh';
    
        $inquiryList = $this->inquiryModel->alias('a')
                                                                     ->field('a.id, a.serial_no, a.created_at, a.buyer_code, b.name AS country_name, c.name AS area_name, d.name AS org_name')
                                                                     ->join('erui_dict.country b ON a.country_bn = b.bn AND b.lang = \'' . $lang . '\' AND b.deleted_flag = \'N\'', 'LEFT')
                                                                     ->join('erui_operation.market_area c ON a.area_bn = c.bn AND c.lang = \'' . $lang . '\' AND c.deleted_flag = \'N\'', 'LEFT')
                                                                     ->join('erui_sys.org d ON a.org_id = d.id', 'LEFT')
                                                                     ->where($where)
                                                                     ->order('a.id DESC')
                                                                     ->select();
    
        $date = date("Ymd");
        $filename = "quote_analysis-$date.xlsx";
    
        $titleList = [
            "流程编码",
            "询价时间",
            "询单所属事业部",
            "客户编码",
            "区域",
            "国家",
            "是否驳回",
            "驳回环节",
            "询价商品名称",
            "市场提交询单时间",
            "询单第一次被分单的时间",
            "询单最终被分单的时间",
            "产品报价最终报出时间",
            "物流分单的时间",
            "物流最终报出价格的时间",
            "市场确认的时间",
        ];
    
        $outData = [];
    
        foreach ($inquiryList as $inquiry) {
            $nodeList = $this->inquiryCheckLogModel->field('in_node, out_node, op_note')->where(['inquiry_id' => $inquiry['id'], 'action' => 'REJECT'])->order('id DESC')->select();
            $nodeName = [];
            foreach ($nodeList as $node) {
                $nodeName[] = $this->inquiryModel->inquiryStatus[$node['in_node']] . '-' . $this->inquiryModel->inquiryStatus[$node['out_node']] . '（' . $node['op_note'] . '）';
            }
            $where = ['inquiry_id' => $inquiry['id']];
            $skuName = $this->inquiryItemModel->where(array_merge($where, ['deleted_flag' => 'N']))->getField('name_zh', true);
            $where['action'] = 'CREATE';
            $submitTime = $this->inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'DRAFT']))->order('id DESC')->getField('out_at');
            $issueFirstTime = $this->inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'BIZ_DISPATCHING']))->order('id ASC')->getField('out_at');
            $issueLastTime = $this->inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'BIZ_DISPATCHING']))->order('id DESC')->getField('out_at');
            $quoteTime = $this->inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'BIZ_QUOTING']))->order('id DESC')->getField('out_at');
            $logiIssueTime = $this->inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'LOGI_DISPATCHING']))->order('id DESC')->getField('out_at');
            $logiQuoteTime = $this->inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'LOGI_QUOTING']))->order('id DESC')->getField('out_at');
            $marketTime = $this->inquiryCheckLogModel->where(array_merge($where, ['in_node' => 'MARKET_CONFIRMING']))->order('id DESC')->getField('out_at');
    
            $outData[] = [
                ['value' => $inquiry['serial_no'], 'type' => 'string'],
                ['value' => $inquiry['created_at'], 'type' => 'string'],
                ['value' => $inquiry['org_name'], 'type' => 'string'],
                ['value' => $inquiry['buyer_code'], 'type' => 'string'],
                ['value' => $inquiry['area_name'], 'type' => 'string'],
                ['value' => $inquiry['country_name'], 'type' => 'string'],
                ['value' => $nodeList ? '是' : '否', 'type' => 'string'],
                ['value' => implode(',', $nodeName), 'type' => 'string'],
                ['value' => implode(',', $skuName), 'type' => 'string'],
                ['value' => $submitTime, 'type' => 'string'],
                ['value' => $issueFirstTime, 'type' => 'string'],
                ['value' => $issueLastTime, 'type' => 'string'],
                ['value' => $quoteTime, 'type' => 'string'],
                ['value' => $logiIssueTime, 'type' => 'string'],
                ['value' => $logiQuoteTime, 'type' => 'string'],
                ['value' => $marketTime, 'type' => 'string'],
            ];
        }
    
        $this->_exportExcel($filename, $titleList, $outData);
    }
    
    

    /**
     * @desc 重写jsonReturn方法
     *
     * @author liujf
     * @time 2017-11-10
     */
    public function jsonReturn($data = [], $type = 'JSON') {
        if ($data) {
            $this->setCode('1');
            $this->setMessage('成功!');
            parent::jsonReturn($data, $type);
        } else {
            $this->setCode('-101');
            $this->setMessage('失败!');
            parent::jsonReturn();
        }
    }
    
    /**
     * @desc 导出excel
     *
     * @param string $filename 文件名
     * @param array $titleArr 表格标题
     * @param array $dataArr 表格数据
     * @author liujf
     * @time 2017-12-19
     */
    private function _exportExcel($filename, $titleArr, $dataArr) {
        $titleCount = count($titleArr);
        $dataCount = count($dataArr);
        $wordArr = $this->_getWordArr($titleCount);
        $objPHPExcel = new PHPExcel();
    
        // Set document properties
        $objPHPExcel->getProperties()
                                ->setCreator("liujf")
                                ->setLastModifiedBy("liujf")
                                ->setTitle("Office 2007 XLSX Test Document")
                                ->setSubject("Office 2007 XLSX Test Document")
                                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                                ->setKeywords("office 2007 openxml php")
                                ->setCategory("Test result file");
    
        for($i = 0; $i < $titleCount; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("$wordArr[$i]1", $titleArr[$i]);
            $objPHPExcel->getActiveSheet()->getColumnDimension($wordArr[$i])->setAutoSize(true);
        }
    
        $typeArr = array(
            'string' => PHPExcel_Cell_DataType::TYPE_STRING,
            'int' => PHPExcel_Cell_DataType::TYPE_NUMERIC
        );
        //填充表格信息
        for ($i = 2; $i <= $dataCount + 1; $i++) {
            $j = 0;
            foreach ($dataArr[$i - 2] as $key=>$value) {
                if($typeArr[$value['type']]) {
                    //显示指定内容类型
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("$wordArr[$j]$i", $value['value'], $typeArr[$value['type']]);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("$wordArr[$j]$i", $value['value']);
                }
                if($value['type'] == 'int') $objPHPExcel->getActiveSheet()->getStyle("$wordArr[$j]$i")->getNumberFormat()->setFormatCode('#,##0.00');
                $j++;
            }
        }
    
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
    
        header('Content-Type: application/vnd.ms-excel; charset="UTF-8"');
        header('Content-Disposition: attachment; filename=' . urlencode($filename));
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
    
    /**
     * @desc 获取数组中下一个元素值
     *
     * @param array $arr 数组
     * @param string $val 当前元素
     * @return string
     * @author liujf
     * @time 2017-12-20
     */
    private function _getNextVal(&$arr, $val) {
        $next = 0;
        reset($arr);
        do {
            $tmp = key($arr);
            $res = next($arr);
        } while ($arr[$tmp] != $val && $res);
        if ($res) $next = key($arr);
        return $arr[$next];
    }
    
    /**
     * @desc 获取下一个字词
     *
     * @param string $prev 字词
     * @return mixed
     * @author liujf
     * @time 2017-12-20
     */
    private function _getNextWord($prev = '') {
        $prev = strtoupper(trim($prev));
        if ($prev == '') return 'A';
        $letterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $flag = true; //是否全是A的标记
        $tmpArr = str_split($prev);
        for ($i = count($tmpArr) - 1; $i >= 0; $i--) {
            if (in_array($tmpArr[$i], $letterArr)) {
                $tmp = $this->_getNextVal($letterArr, $tmpArr[$i]);
                $tmpArr[$i] = $tmp;
                if ($tmp != 'A') {
                    $flag = false;
                    break;
                }
            } else {
                return false;
            }
        }
        $next = implode($tmpArr);
        return $flag ? $next . 'A' : $next;
    }
    
    /**
     * @desc 获取字词数组
     *
     * @param int $size 数组大小
     * @return mixed
     * @author liujf
     * @time 2017-12-20
     */
    private function _getWordArr($size = 1) {
        if (!is_int($size)) return false;
        $wordArr = [];
        $word = '';
        for ($i = 0; $i < $size; $i++) {
            $word = $this->_getNextWord($word);
            $wordArr[] = $word;
        }
        return $wordArr;
    }
    
    /**
     * @desc 去掉参数数据两侧的空格
     *
     * @author liujf
     * @time 2017-12-23
     */
    private function _trim($condition = []) {
        if (is_string($condition)) return trim($condition);
        foreach ($condition as $k => $v) {
            if (is_array($v)) {
                $condition[$k] = $this->_trim($v);
            } else {
                $condition[$k] = trim($v);
            }
        }
        return $condition;
    }

}
