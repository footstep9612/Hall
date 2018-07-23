<?php

/**
 * @desc   系统Excel相关操作处理
 * @Author 买买提
 */
class ExcelmanagerController extends PublicController {

    public function init() {
        parent::init();
    }

    /**
     * 本地Form表单上传测试
     */
    public function uploadAction() {
        $this->getView()->assign("content", "Hello World");
        $this->display('upload');
    }

    /**
     * @desc 获取请求
     * @return mixed
     */
    private function requestParams() {
        return json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params = '') {

        $request = $this->requestParams();
        unset($request['token']);

        //判断筛选字段为空的情况
        if ($params) {
            $params = explode(',', $params);
            foreach ($params as $param) {
                if (empty($request[$param]))
                    $this->jsonReturn(['code' => '-104', 'message' => '缺少参数']);
            }
        }
        return $request;
    }

    /**
     * @desc 下载询单sku导入模板(询单管理->新增询单)
     */
    public function downloadInquirySkuTemplateAction() {
        $this->jsonReturn([
            'code' => 1,
            'message' => L('EXCEL_SUCCESS'),
            'data' => [
                'url' => 'http://file01.erui.com/group1/M00/03/F2/rBFgyFrUD06ASXl7AAAiy1WTkS423.xlsx'
            ]
        ]);
    }

    /**
     * @desc 下载报价sku导入模板接口
     *
     * @author liujf
     * @time 2018-04-16
     */
    public function downloadQuoteSkuTemplateAction() {
        $this->jsonReturn([
            'code' => 1,
            'message' => L('EXCEL_SUCCESS'),
            'data' => [
                'url' => 'http://file01.erui.com/group1/M00/04/40/rBFgyFr6qxiAS5E2AAAwBtKxT0s11.xlsx'
            ]
        ]);
    }

    /**
     * @desc 下载订单sku导入模板接口
     *
     * @author liujf
     * @time 2018-01-11
     */
    public function downloadOrderSkuTemplateAction() {
        $this->jsonReturn([
            'code' => 1,
            'message' => L('EXCEL_SUCCESS'),
            'data' => [
                'url' => 'http://file01.erui.com/group1/M00/02/8F/rBFgyFpcB5WAC85pAAAn8H4MZrE92.xlsx'
            ]
        ]);
    }

    /**
     * @desc 导入sku(询单管理->新增询单)
     */
    public function importSkuAction() {

        $request = $this->validateRequests('inquiry_id,file_url,file_name');

        $remoteFile = $request['file_url'];
        $inquiry_id = $request['inquiry_id'];
        $fileName = $request['file_name'];
        //下载到本地临时文件
        $localFile = ExcelHelperTrait::download2local($remoteFile);
        $data = ExcelHelperTrait::ready2import($localFile);

        $response = $this->importSkuHandler($localFile, $data, $inquiry_id, $fileName);
        $this->jsonReturn($response);
    }

    /**
     * @desc 导入报价sku接口
     *
     * @author liujf
     * @time 2018-04-16
     */
    public function importQuoteSkuAction() {
        $request = $this->validateRequests('inquiry_id,file_url,file_name');
        $inquiryId = $request['inquiry_id'];
        $remoteFile = $request['file_url'];
        $fileName = $request['file_name'];
        //下载到本地临时文件
        $localFile = ExcelHelperTrait::download2local($remoteFile);
        $data = ExcelHelperTrait::ready2import($localFile);
        $response = $this->_importQuoteSkuHandler($localFile, $data, $inquiryId, $fileName);
        $this->jsonReturn($response);
    }

    /**
     * @desc 导入订单sku接口
     *
     * @author liujf
     * @time 2018-01-11
     */
    public function importOrderSkuAction() {
        $request = $this->validateRequests('file_url');
        $remoteFile = $request['file_url'];
        //下载到本地临时文件
        $localFile = ExcelHelperTrait::download2local($remoteFile);
        $data = ExcelHelperTrait::ready2import($localFile);
        $response = $this->_importOrderSkuHandler($localFile, $data);
        $this->jsonReturn($response);
    }

    /**
     * 执行导入操作
     * @param $data
     * @return array
     */
    private function importSkuHandler($localFile, $data, $inquiry_id, $fileName) {

        array_shift($data); //去掉第一行数据(excel文件的标题)
        if (empty($data)) {
            return ['code' => '-104', 'message' => L('EXCEL_NO_DATA')];
        }

        //遍历重组
        foreach ($data as $k => $v) {
            $sku[$k]['inquiry_id'] = $inquiry_id; //询单id
            $sku[$k]['sku'] = $v[1]; //平台sku
            //$sku[$k]['pn'] = $v[1]; //商品供应商PN码
            $sku[$k]['buyer_goods_no'] = $v[2]; //客户商品号
            $sku[$k]['name'] = $v[3]; //外文品名
            $sku[$k]['name_zh'] = $v[4]; //中文品名
            $sku[$k]['qty'] = $v[5]; //数量
            $sku[$k]['unit'] = $v[6]; //单位
            $sku[$k]['brand'] = $v[7]; //品牌
            $sku[$k]['model'] = $v[8]; //型号
            $sku[$k]['remarks'] = $v[9]; //客户需求描述
            $sku[$k]['created_at'] = date('Y-m-d H:i:s'); //添加时间
        }

        //写入数据库
        $inquiryItem = new InquiryItemModel();

        $sku = dataTrim($sku);
        // 总数
        $totalCount = count($sku);
        // 成功数
        $successCount = 0;
        // 失败的数据列
        $failList = [];

        foreach ($sku as $item => $value) {
            $failData = [
                'file_name' => $fileName,
                'sequence_num' => $item + 1,
                'sku_name' => $this->lang == 'zh' ? $value['name_zh'] : $value['name'],
                'status' => L('EXCEL_FAILD')
            ];
            if ($value['name'] == '') {
                $failData['reason'] = L('EXCEL_SKU_NAME_REQUIRED');
            } elseif ($value['name_zh'] == '') {
                $failData['reason'] = L('EXCEL_SKU_NAME_ZH_REQUIRED');
            } elseif (!is_numeric($value['qty'])) {
                $failData['reason'] = L('EXCEL_SKU_QTY_REQUIRED');
            } elseif ($value['unit'] == '') {
                $failData['reason'] = L('EXCEL_SKU_UNIT_REQUIRED');
            } else {
                try {
                    $result = $inquiryItem->add($inquiryItem->create($value));
                    if ($result) {
                        $successCount++;
                        unset($failData);
                    } else {
                        $failData['reason'] = L('EXCEL_UNKNOWN');
                    }
                } catch (Exception $e) {
                    $failData['reason'] = $e->getMessage();
                }
            }
            if (isset($failData)) {
                $failList[] = $failData;
            }
        }
        //删除本地临时文件
        if (is_file($localFile) && file_exists($localFile)) {
            unlink($localFile);
        }

        return [
            'code' => '1',
            'message' => L('EXCEL_SUCCESS'),
            'total_count' => $totalCount,
            'success_count' => $successCount,
            'fail_count' => $totalCount - $successCount,
            'fail_list' => $failList
        ];
    }

    /**
     * @desc 执行报价sku导入操作
     *
     * @param string $localFile
     * @param array $import_data
     * @param int $inquiryId
     * @param string $fileName
     * @return array
     * @author liujf
     * @time 2018-04-16
     */
    private function _importQuoteSkuHandler($localFile, $import_data, $inquiryId, $fileName) {
        $quoteModel = new QuoteModel();
        $inquiryItemModel = new InquiryItemModel();
        $quoteItemModel = new QuoteItemModel();
        $quoteitem_model = new Rfq_QuoteItemModel();

        array_shift($import_data); //去掉第一行数据(excel文件的标题)
        if (empty($import_data)) {
            return ['code' => '-104', 'message' => L('EXCEL_NO_DATA')];
        }
        $inquiry_model = new InquiryModel();
        $org_id = $inquiry_model->where(['id' => $inquiryId, 'deleted_flag' => 'N'])->getField('org_id');
        $is_erui = (new OrgModel())->getIsEruiById($org_id);
        $data = dataTrim($import_data);
        $quoteId = $quoteModel->where(['inquiry_id' => $inquiryId, 'deleted_flag' => 'N'])->getField('id');
        if ($is_erui == 'Y') {
            $sku = $quoteitem_model->getSkusByErui($data, $inquiryId, $quoteId);
        } else {
            $sku = $quoteitem_model->getSkusByOtherOrg($data, $inquiryId, $quoteId);
        }


        //遍历重组
        // 总数
        $totalCount = count($sku);
        // 成功数
        $successCount = 0;
        // 失败的数据列
        $failList = [];
        // 产品分类
        $category = array_merge($inquiryItemModel->isOil, $inquiryItemModel->noOil);
        foreach ($sku as $item => $value) {
            $failData = [
                'file_name' => $fileName,
                'sequence_num' => $item + 1,
                'sku_name' => $this->lang == 'zh' ? $value['name_zh'] : $value['name'],
                'status' => L('EXCEL_FAILD')
            ];

            if ($value['category'] == '' && $is_erui === 'N') {
                $failData['reason'] = L('EXCEL_SKU_CATEGORY_REQUIRED');
            } elseif ($value['material_cat_no'] == '' && $is_erui === 'Y') {
                $failData['reason'] = L('EXCEL_SKU_MATERIAL_CAT_NOT_EXIST');
            } elseif (!is_numeric($value['material_cat_no']) && $is_erui === 'Y') {
                $failData['reason'] = L('EXCEL_SKU_MATERIAL_CAT_NOT_EXIST');
            } elseif ($value['org_id'] == '' && $is_erui === 'Y') {
                $failData['reason'] = L('EXCEL_SKU_ORG_NOT_EXIST');
            } elseif (!is_numeric($value['org_id']) && $is_erui === 'Y') {
                $failData['reason'] = L('EXCEL_SKU_ORG_NOT_EXIST');
            } elseif (!in_array($value['category'], $category) && $is_erui === 'N') {
                $failData['reason'] = L('EXCEL_SKU_CATEGORY_NOT_EXIST');
            } elseif ($value['name'] == '') {
                $failData['reason'] = L('EXCEL_SKU_NAME_REQUIRED');
            } elseif ($value['name_zh'] == '') {
                $failData['reason'] = L('EXCEL_SKU_NAME_ZH_REQUIRED');
            } elseif (!is_numeric($value['qty'])) {
                $failData['reason'] = L('EXCEL_SKU_QTY_REQUIRED');
            } elseif ($value['unit'] == '') {
                $failData['reason'] = L('EXCEL_SKU_UNIT_REQUIRED');
            } elseif ($value['quote_brand'] == '') {
                $failData['reason'] = L('EXCEL_SKU_BRAND_REQUIRED');
            } elseif (!is_numeric($value['purchase_unit_price'])) {
                $failData['reason'] = L('EXCEL_SKU_PURCHASE_UNIT_PRICE_REQUIRED');
            } elseif ($value['purchase_price_cur_bn'] == '') {
                $failData['reason'] = L('EXCEL_SKU_PURCHASE_PRICE_CURRENCY_REQUIRED');
            } elseif (!is_numeric($value['gross_weight_kg'])) {
                $failData['reason'] = L('EXCEL_SKU_GROSS_WEIGHT_REQUIRED');
            } elseif ($value['package_mode'] == '') {
                $failData['reason'] = L('EXCEL_SKU_PACKAGE_MODE_REQUIRED');
            } elseif (!is_numeric($value['package_size'])) {
                $failData['reason'] = L('EXCEL_SKU_PACKAGE_SIZE_REQUIRED');
            } elseif ($value['stock_loc'] == '') {
                $failData['reason'] = L('EXCEL_SKU_STOCK_LOCATION_REQUIRED');
            } elseif ($value['goods_source'] == '') {
                $failData['reason'] = L('EXCEL_SKU_GOODS_SOURCE_REQUIRED');
            } elseif (!is_numeric($value['delivery_days'])) {
                $failData['reason'] = L('EXCEL_SKU_DELIVERY_DAYS_REQUIRED');
            } elseif ($value['period_of_validity'] == '') {
                $failData['reason'] = L('EXCEL_SKU_VALIDITY_PERIOD_REQUIRED');
            } elseif (!is_numeric($value['supplier_id'])) {
                $failData['reason'] = L('EXCEL_SKU_SUPPLIER_NAME_REQUIRED');
            } else {
                $inquiryItemModel->startTrans();
                try {
                    // 新增询单SKU记录
                    $inquiryItemData = [
                        'inquiry_id' => $value['inquiry_id'],
                        'sku' => $value['sku'],
                        'buyer_goods_no' => $value['buyer_goods_no'],
                        'name' => $value['name'],
                        'name_zh' => $value['name_zh'],
                        'qty' => $value['qty'],
                        'unit' => $value['unit'],
                        'brand' => $value['brand'],
                        'model' => $value['model'],
                        'remarks' => $value['remarks'],
                        'created_at' => $value['created_at']
                    ];

                    if ($is_erui == 'Y') {
                        $inquiryItemData['material_cat_no'] = $value['material_cat_no'];
                    } else {
                        $inquiryItemData['category'] = $value['category'];
                    }
                    $inquiryItemId = $inquiryItemModel->add($inquiryItemData);
                    // 新增报价SKU记录
                    $quoteItemData = [
                        'quote_id' => $value['inquiry_id'],
                        'inquiry_id' => $value['inquiry_id'],
                        'inquiry_item_id' => $inquiryItemId,
                        'sku' => $value['sku'],
                        'brand' => $value['quote_brand'],
                        'pn' => $value['pn'],
                        'supplier_id' => $value['supplier_id'],
                        'quote_qty' => $value['qty'],
                        'quote_unit' => $value['unit'],
                        'purchase_unit_price' => $value['purchase_unit_price'],
                        'purchase_price_cur_bn' => $value['purchase_price_cur_bn'],
                        'gross_weight_kg' => $value['gross_weight_kg'],
                        'package_mode' => $value['package_mode'],
                        'package_size' => $value['package_size'],
                        'stock_loc' => $value['stock_loc'],
                        'goods_source' => $value['goods_source'],
                        'delivery_days' => $value['delivery_days'],
                        'period_of_validity' => date('Y-m-d', strtotime($value['period_of_validity'])),
                        'reason_for_no_quote' => $value['reason_for_no_quote'],
                        'created_at' => $value['created_at']
                    ];
                    if ($is_erui == 'Y') {
                        $quoteItemData['org_id'] = intval($value['org_id']);
                    }
                    $quoteItemId = $quoteItemModel->add($quoteItemData);
                    if ($inquiryItemId && $quoteItemId) {
                        $successCount++;
                        unset($failData);
                        $inquiryItemModel->commit();
                    } else {
                        $failData['reason'] = L('EXCEL_UNKNOWN');
                        $inquiryItemModel->rollback();
                    }
                } catch (Exception $e) {
                    $failData['reason'] = $e->getMessage();
                    $inquiryItemModel->rollback();
                }
            }
            if (isset($failData)) {
                $failList[] = $failData;
            }
        }
        //删除本地临时文件
        if (is_file($localFile) && file_exists($localFile)) {
            unlink($localFile);
        }
        return [
            'code' => '1',
            'message' => L('EXCEL_SUCCESS'),
            'total_count' => $totalCount,
            'success_count' => $successCount,
            'fail_count' => $totalCount - $successCount,
            'fail_list' => $failList
        ];
    }

    /**
     * @desc 执行订单sku导入操作
     *
     * @param string $localFile
     * @param array $data
     * @return array
     * @author liujf
     * @time 2018-01-11
     */
    private function _importOrderSkuHandler($localFile, $data) {
        array_shift($data); //去掉第一行数据(excel文件的标题)
        if (empty($data)) {
            return ['code' => '-104', 'message' => L('EXCEL_NO_DATA')];
        }
        //遍历重组
        foreach ($data as $k => $v) {
            if ($v[1] || $v[2] || $v[3] || $v[4] || $v[5] || $v[6] || $v[7] || $v[8] || $v[9]) {
                $sku[$k]['sku'] = $v[1]; //sku编码|订货号
                $sku[$k]['name'] = $v[2]; //外文品名
                $sku[$k]['name_zh'] = $v[3]; //中文品名
                $sku[$k]['buy_number'] = $v[4]; //数量
                $sku[$k]['price'] = $v[5]; //数量
                $sku[$k]['department'] = $v[6]; //数量
                $sku[$k]['nude_cargo_unit'] = $v[7]; //单位
                $sku[$k]['brand'] = $v[8]; //品牌
                $sku[$k]['model'] = $v[9]; //型号
            }
        }
        //删除本地临时文件
        if (file_exists($localFile)) {
            unlink($localFile);
        }
        return $sku;
    }

    /**
     * 下载报价单(询单管理->报价信息)
     */
    public function downQuotationAction() {

        $request = $this->validateRequests('inquiry_id');
        $inquiryAttach = new InquiryAttachModel();
        $condition = ['inquiry_id' => intval($request['inquiry_id']), 'attach_group' => 'FINAL'];
        $ret = $inquiryAttach->getList($condition);
        if ($ret['code'] == 1 && !empty($ret['data']) && !empty($ret['data'][0]['attach_url'])) {
            $this->jsonReturn([
                'code' => '1',
                'message' => L('EXCEL_SUCCESS'),
                'data' => [
                    'url' => $ret['data'][0]['attach_url']
                ]
            ]);
        }

        $data = $this->getFinalQuoteData($request['inquiry_id']);
        //p($data);
        //创建excel表格并填充数据
        $excelFile = $this->createExcelAndInsertData($data);

        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';

        $data['tmp_name'] = $excelFile;
        $data['type'] = 'application/excel';
        $data['name'] = 'excelFile';
        $remoteUrl = $this->postfile($data, $url);

        if (!$remoteUrl) {
            $this->jsonReturn(['code' => '1', 'message' => '失败']);
        }
        //构建打包文件数组
        $fileName = date('YmdHis');
        $files = [
            ['url' => $excelFile, 'name' => $fileName . '.xls']
        ];

        $condition = [
            'inquiry_id' => $request['inquiry_id'],
            'attach_group' => ['in', ['INQUIRY', 'TECHNICAL', 'DEMAND']]
        ];
        $inquiryList = $inquiryAttach->getList($condition);

        if ($inquiryList['code'] == 1) {
            foreach ($inquiryList['data'] as $item) {
                $files[] = ['url' => $fastDFSServer . $item['attach_url'], 'name' => $item['attach_name']];
            }
        }

        //上传至FastDFS
        $zipFile = $fileName . '.zip';
        $fileId = $this->packAndUpload($url, $zipFile, $files);
        //上传失败
        if (empty($fileId) || empty($fileId['url'])) {
            $this->jsonReturn([
                'code' => '-1',
                'message' => '导出失败!',
            ]);
            return;
        }

        //保存数据库
        $data = [
            'inquiry_id' => intval($request['inquiry_id']),
            'attach_group' => 'FINAL',
            'attach_type' => 'application/zip',
            'attach_name' => $zipFile,
            'attach_url' => $fileId['url'],
            'created_by' => intval($this->user['id']),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $inquiryAttach->addData($data);

        //删除本地的临时文件
        @unlink($excelFile);
        $this->jsonReturn([
            'code' => '1',
            'message' => '导出成功!',
            'data' => [
                'url' => $fileId['url']
            ]
        ]);
    }

    public function finalQuotationAction() {

        $request = $this->validateRequests('inquiry_id');

        //更改报价的状态
        $quoteModel = new QuoteModel();
        $quoteModel->where(['inquiry_id' => $request['inquiry_id']])->save(['status' => 'QUOTE_SENT']);
        //更改询单的状态
        $inquiryModel = new InquiryModel();
        $inquiryModel->updateData([
            'id' => $request['inquiry_id'],
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'updated_by' => $this->user['id']
        ]);

        $inquiryAttach = new InquiryAttachModel();
        $condition = ['inquiry_id' => $request['inquiry_id'], 'attach_group' => 'FINAL_EXTERNAL'];
        $ret = $inquiryAttach->getList($condition);
        if ($ret['code'] == 1 && !empty($ret['data']) && !empty($ret['data'][0]['attach_url'])) {
            $this->jsonReturn([
                'code' => '1',
                'message' => L('EXCEL_SUCCESS'),
                'data' => [
                    'url' => $ret['data'][0]['attach_url']
                ]
            ]);
        }

        $data = $this->getCommercialQuoteData($request['inquiry_id']);

        $excelFile = $this->createFinalExcelAndInsertData($data);

        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $data['tmp_name'] = $excelFile;
        $data['type'] = 'application/excel';
        $data['name'] = 'excelFile';
        $remoteUrl = $this->postfile($data, $url);

        if (!$remoteUrl) {
            $this->jsonReturn(['code' => '-104', 'message' => L('EXCEL_FAILD')]);
        }
        //构建打包文件数组
        $fileName = date('YmdHis');
        $files = [['url' => $excelFile, 'name' => $fileName . '.xls']];

        //上传至FastDFS
        $zipFile = $fileName . '.zip';
        $fileId = $this->packAndUpload($url, $zipFile, $files);
        //上传失败
        if (empty($fileId) || empty($fileId['url'])) {
            $this->jsonReturn(['code' => '-1', 'message' => L('EXCEL_FAILD'),]);
            return;
        }

        //保存数据库
        $data = [
            'inquiry_id' => $request['inquiry_id'],
            'attach_group' => 'FINAL_EXTERNAL',
            'attach_type' => 'application/zip',
            'attach_name' => $zipFile,
            'attach_url' => $fileId['url'],
            'created_by' => $this->user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        $inquiryAttach->addData($data);

        //删除本地的临时文件
        @unlink($excelFile);
        $this->jsonReturn([
            'code' => '1',
            'message' => L('EXCEL_SUCCESS'),
            'data' => [
                'url' => $fileId['url']
            ]
        ]);
    }

    /**
     * 获取报价单信息
     * @param $inquiry_id   询单id
     * @return array    报价信息
     */
    private function getCommercialQuoteData($inquiry_id) {

        $quoteModel = new QuoteModel();
        $info = $quoteModel->where(['inquiry_id' => $inquiry_id])->field('quote_no,biz_quote_by,biz_quote_at,period_of_validity')->find();

        $employee = new EmployeeModel();
        $info['quote_info'] = $employee->where(['id' => $info['biz_quote_by']])->field('name,email,mobile')->find();


        $inquiryModel = new InquiryModel();
        $info['inquiey_info'] = $inquiryModel->where(['id' => $inquiry_id])->field('buyer_name,project_name,quote_notes')->find();

        $quoteItemModel = new QuoteItemModel();
        $list = $quoteItemModel->alias('a')
                ->join('erui_rfq.inquiry_item b ON a.inquiry_item_id=b.id', 'LEFT')
                ->join('erui_rfq.final_quote_item c ON a.id=c.quote_item_id', 'LEFT')
                ->field('b.id,b.remarks,a.quote_qty qty,a.purchase_unit_price,c.quote_unit_price')
                ->where(['a.inquiry_id' => $inquiry_id, 'a.deleted_flag' => 'N'])
                ->select();

        $final_total_price = [];
        $final_total_qty = [];
        foreach ($list as $k => $v) {

            $list[$k]['total_quote_unit_price'] = sprintf("%.2f", $v['qty'] * $v['quote_unit_price']);
            $final_total_price[] = $list[$k]['total_quote_unit_price'];
            $final_total_qty[] = $v['qty'];
        }

        $final_total_price = array_sum($final_total_price);
        $final_total_qty = array_sum($final_total_qty);

        $info['total'] = [
            'total_price' => $final_total_price,
            'total_qty' => $final_total_qty
        ];

        $info['list'] = $list;

        return $info;
        //p($info);
    }

    /**
     * 上传文件至FastDFS
     * @param     $data 本地文件信息
     * @param     $url  上传接口地址
     * @param int $timeout  响应时间
     * @return array|mixed
     */
    function postfile($data, $url, $timeout = 30) {
        $cfile = new \CURLFile($data['tmp_name'], $data['type'], $data['name']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['upFile' => $cfile]);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
        $cookies = "eruitoken=" . $GLOBALS['SSO_TOKEN'];
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return [];
        }
        curl_close($ch);
        $cfile = null;
        unset($cfile);
        return json_decode($response, true);
    }

    /**
     * 打包文件并且上传至FastDFS服务器
     * @param string $url 上传地址
     * @param string $filename 压缩包名称
     * @param array $files  需要打包的文件列表
     * @return mixed
     * */
    private function packAndUpload($url, $filename, $files) {
        //创建临时目录
        $tmpdir = $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/" . uniqid() . '/';
        @mkdir($tmpdir, 0777, true);
        if (!is_dir($tmpdir)) {
            return false;
        }

        //复制文件到临时目录
        foreach ($files as $key => $file) {
            $name = $file['name'];
            //如果文件存在则重命名
            if (file_exists($tmpdir . $name)) {
                //循环100次修改文件名
                for ($i = 1; $i < 100; $i++) {
                    $name = preg_replace("/(\.\w+)/i", "($i)$1", $name);
                    if (!file_exists($tmpdir . $name)) {
                        break;
                    }
                }
            }
            //目标文件仍然存在，则写入错误文件
            if (file_exists($tmpdir . $name)) {
                $error_files[] = $file;
            }
            $name = iconv('utf-8', 'gbk', $name);
            $content = @file_get_contents($file['url']);
            @file_put_contents($tmpdir . $name, $content);
        }
        //如果有文件无法复制到本目录
        if (!empty($error_files)) {
            return false;
        }
        //生成压缩文件
        $zip = new ZipArchive();
        $filepath = dirname($tmpdir) . '/' . $filename;
        $res = $zip->open($filepath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        if ($res !== true) {
            return false;
        }

        $files = scandir($tmpdir);
        foreach ($files as $item) {
            if ($item != '.' && $item != '..') {
                $zip->addFile($tmpdir . $item, $item);
            }
        }
        $zip->close();
        //清理临时目录
        foreach ($files as $item) {
            if ($item != '.' && $item != '..') {
                unlink($tmpdir . $item);
            }
        }
        @rmdir($tmpdir);
        //上传至FastDFS
        $data['tmp_name'] = $filepath;
        $data['type'] = 'application/zip';
        $data['name'] = $filename;
        $ret = $this->postfile($data, $url);
        //删除临时压缩文件
        @unlink($filepath);
        return $ret;
    }

    /**
     * 获取报价单信息
     * @param $inquiry_id   询单id
     * @return array    报价信息
     */
    private function getFinalQuoteData($inquiry_id) {

        //询单综合信息 (询价单位 流程编码 项目代码)
        $inquiryModel = new InquiryModel();
        $info = $inquiryModel->where(['id' => $inquiry_id])->field('serial_no,buyer_name,quote_notes,agent_id')->find();

        //报价综合信息 (报价人，电话，邮箱，报价时间)
        $quoteModel = new QuoteModel();
        $finalQuoteInfo = $quoteModel->where(['inquiry_id' => $inquiry_id])->field('biz_quote_by,biz_quote_at')->find();

        $employee = new EmployeeModel();
        $employeeInfo = $employee->where(['id' => intval($finalQuoteInfo['biz_quote_by'])])->field('email,mobile,name')->find();
        $info['agenter'] = $employee->where(['id' => intval($info['agent_id'])])->getField('name');

        //报价人信息
        $info['quoter_email'] = $employeeInfo['email'];
        $info['quoter_mobile'] = $employeeInfo['mobile'];
        $info['quoter_name'] = $employeeInfo['name'];
        //由于此文件仅生成一次，所以记录日期跟当前日期一致
        $info['quote_time'] = $finalQuoteInfo['biz_quote_at'];


        //报价单项(QuoteItem

        $quoteItemModel = new QuoteItemModel();
        $fields = 'a.id,a.inquiry_id,b.name_zh,b.name,b.model,b.remarks,a.remarks quote_remarks,b.qty,b.unit,b.brand,a.exw_unit_price,a.quote_unit_price,a.gross_weight_kg,a.package_size,a.package_mode,a.delivery_days,a.period_of_validity';
        $quoteItems = $quoteItemModel->alias('a')
                ->join('erui_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
                ->field($fields)
                ->where(['a.inquiry_id' => $inquiry_id, 'a.deleted_flag' => 'N'])
                ->order('a.id DESC')
                ->select();

        $quoteModel = new QuoteModel();
        $quoteLogiFeeModel = new QuoteLogiFeeModel();
        $quoteInfo = $quoteModel->where(['inquiry_id' => $inquiry_id])->field('total_weight,package_volumn,payment_mode,delivery_period,trade_terms_bn,trans_mode_bn,dispatch_place,delivery_addr,total_logi_fee,total_bank_fee,total_exw_price,total_insu_fee,total_quote_price,total_insu_fee,quote_remarks,quote_no,quote_cur_bn')->find();
        $quoteLogiFee = $quoteLogiFeeModel->where(['inquiry_id' => $inquiry_id])->field('est_transport_cycle,logi_remarks')->find();
        $quoteInfo['logi_remarks'] = $quoteLogiFee['logi_remarks'];
        $quoteInfo['est_transport_cycle'] = $quoteLogiFee['est_transport_cycle'];

        //综合报价信息
        return $finalQuoteData = [
            'quoter_info' => $info,
            'quote_items' => $quoteItems,
            'quote_info' => $quoteInfo
        ];
    }

    /**
     * 获取用户所在部门数组
     * @param int $uid 用户ID
     * @return array 返回部门数组，从顶级到最低一级
     * */
    private function getDepartmentByUid($uid) {
        if (!is_numeric($uid) || $uid < 1) {
            return [];
        }
        $orgMember = new OrgMemberModel();
        $orgId = $orgMember->where(['employee_id' => intval($uid)])->getField('org_id');
        if ($orgId < 1) {
            return [];
        }
        $org = new OrgModel();
        $list = $org->field('id,parent_id,name')->select();
        $orgs = [];
        foreach ($list as $key => $item) {
            $orgs[$item['id']] = &$list[$key];
        }
        foreach ($orgs as $key => $item) {
            if (isset($orgs[$item['parent_id']])) {
                $orgs[$key]['parent'] = &$orgs[$item['parent_id']];
            } else {
                $orgs[$key]['parent'] = null;
            }
        }
        $depats = [];
        //最大20级，防止死循环
        for ($i = 0; $i < 20; $i++) {
            if (isset($orgs[$orgId])) {
                array_unshift($depats, $orgs[$orgId]['name']);
                $orgId = (int) $orgs[$orgId]['parent_id'];
            } else {
                return $depats;
            }
        }
    }

    private function createFinalExcelAndInsertData($quote) {

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('commercial offer');

        $styleArray = ['borders' => ['outline' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '333333']]]];

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", 'Erui International Electronic Commerce Co.,Ltd')->mergeCells("A1:H1")->getRowDimension(1)->setRowHeight(45);
        $objSheet->setCellValue("B2", '        Tel:+86-400-820-9199             E-mail: eruixsgl@keruigroup.com')->mergeCells("B2:G2");
        $objSheet->setCellValue("B3", '        Fax: +86-0546-8375185           http://www.erui.com')->mergeCells("B3:G3");
        //$objSheet->getStyle("A4:G5")->applyFromArray($styleArray);
        //添加logo
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $objDrawing->setPath('./public/images/logo.png');
        $objDrawing->setHeight(36);
        $objDrawing->setWidth(200);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setOffsetX(130);
        $objDrawing->setOffsetY(13);
        $objDrawing->setRotation(25);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(45);
        $objDrawing->setWorksheet($objSheet);

        $objSheet->getStyle("A1:H1")->getFont()->setSize(16)->setBold(true);
        $objSheet->mergeCells("A4:H4");

        /* 设置A1~R1的文字属性 */
        $objSheet->getCell("A1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objSheet->getColumnDimension("A")->setWidth('9');

        $normal_cols = ["B", "C", "D", "E", "F"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('18');
        endforeach;

        //设置最大列宽度


        $objSheet->setCellValue("A5", "Our Offer : ");
        $objSheet->setCellValue("A6", "Date : ");
        $objSheet->setCellValue("A7", "Contact : ");
        $objSheet->setCellValue("A8", "E-mail : ");
        $objSheet->setCellValue("A9", "Tel : ");

        $objSheet->setCellValue("B5", $quote['quote_no'])->mergeCells("B5:C5");
        $objSheet->setCellValue("B6", $quote['biz_quote_at'])->mergeCells("B6:C6");
        $objSheet->setCellValue("B7", $quote['quote_info']['name'])->mergeCells("B7:C7");
        $objSheet->setCellValue("B8", $quote['quote_info']['email'])->mergeCells("B8:C8");
        $objSheet->setCellValue("B9", $quote['quote_info']['mobile'])->mergeCells("B9:C9");

        $objSheet->setCellValue("D5", "To : ");
        $objSheet->setCellValue("D6", "Your Ref.");
        $objSheet->setCellValue("D7", "Subject : ");
        $objSheet->setCellValue("D8", "Required By : ");

        $objSheet->setCellValue("E5", $quote['inquiey_info']['buyer_name'])->mergeCells("E5:H5");
        $objSheet->setCellValue("E6", "")->mergeCells("E6:H6");
        $objSheet->setCellValue("E7", $quote['inquiey_info']['project_name'])->mergeCells("E7:H7");
        $objSheet->setCellValue("E8", $quote['inquiey_info']['quote_notes'])->mergeCells("E8:H8");

        $objSheet->getStyle('A5:H9')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objSheet->getStyle("A5:H9")->applyFromArray($styleArray);

        $objSheet->mergeCells("D9:H9");
        $objSheet->mergeCells("A10:H10");

        $objSheet->setCellValue("A11", "Item");
        $objSheet->setCellValue("B11", "Description");
        $objSheet->setCellValue("C11", "Reference picture");
        $objSheet->setCellValue("D11", "Qty.");
        $objSheet->setCellValue("E11", "Unit Price(USD)");
        $objSheet->setCellValue("F11", "Total Price(USD)");

        $R_N = ["A11", "B11", "C11", "D11", "E11", "F11"];
        foreach ($R_N as $RN):
            $objSheet->getCell($RN)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $startRow = 12;
        if (!empty($quote['list'])) {
            foreach ($quote['list'] as $k => $v) {

                $objSheet->getRowDimension($startRow)->setRowHeight(35);

                $objSheet->setCellValue("A" . $startRow, $k + 1);
                $objSheet->setCellValue("B" . $startRow, $v['remarks']);
                $objSheet->setCellValue("C" . $startRow, "");
                $objSheet->setCellValue("D" . $startRow, $v['qty']);
                $objSheet->setCellValue("E" . $startRow, $v['quote_unit_price']);
                $objSheet->setCellValue("F" . $startRow, $v['total_quote_unit_price']);

                $objSheet->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("B" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("C" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("E" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("F" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $startRow++;
            }

            $_14 = $startRow + 2;
            $_15 = $startRow + 3;
            $_16 = $startRow + 4;
            $_17 = $startRow + 5;
            $_18 = $startRow + 6;
            $_19 = $startRow + 7;
            $objSheet->setCellValue("A" . $startRow, "Total")->mergeCells("A" . $startRow . ":C" . $startRow)->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objSheet->setCellValue("D" . $startRow, $quote['total']['total_qty'])->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objSheet->setCellValue("F" . $startRow, $quote['total']['total_price'])->getCell("F" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objSheet->setCellValue("A" . $_14, '1. Validity : ' . $quote['period_of_validity'])->mergeCells("A" . $_14 . ":H" . $_14);
            $objSheet->setCellValue("A" . $_15, '2. The above offer is based on the Incoterm;')->mergeCells("A" . $_15 . ":H" . $_15);
            $objSheet->setCellValue("A" . $_16, '3. The delivery time: ')->mergeCells("A" . $_16 . ":H" . $_16);
            $objSheet->setCellValue("A" . $_17, '4. Any deviation about the quantity or specification from our offer may affect the price and the delivery time.')->mergeCells("A" . $_17 . ":H" . $_17);
            $objSheet->setCellValue("A" . $_18, '5. Payment Terms: ')->mergeCells("A" . $_18 . ":H" . $_18);
            $objSheet->setCellValue("A" . $_19, '6. The above qutation price does not include the third party inspection cost or other costs.')->mergeCells("A" . $_19 . ":H" . $_19);
        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "FINAL_" . date('Ymd-His') . '.xls');
    }

    /**
     * 创建excel文件对象
     * @param $quote
     * @return string 文件路径
     */
    private function createExcelAndInsertData($quote) {

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet(); //当前sheet
        $objSheet->setTitle('商务报价单'); //设置报价单标题
        //设置边框
        $styleArray = [
            'borders' => [
                'outline' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '333333'],
                ],
            ],
        ];

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", '易瑞国际电子商务有限公司商务技术部')->mergeCells("A1:R2");
        $objSheet->getStyle("A3:R5")->applyFromArray($styleArray);

        //添加logo
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $objDrawing->setPath('./public/images/logo.png');
        $objDrawing->setHeight(36);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setOffsetX(110);
        $objDrawing->setRotation(25);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(45);
        $objDrawing->setWorksheet($objSheet);

        $objSheet->getStyle("A1:R2")
                ->getFont()
                ->setSize(18)
                ->setBold(true);

        /* 设置A1~R1的文字属性 */
        $objSheet->getCell("A1")
                ->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置全局文字居中
        $objSheet->getDefaultStyle()
                ->getFont()
                ->setName("微软雅黑")
                ->setSize(10);

        $objSheet->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置最小列宽度
        $small_cols = ["A", "G"];
        foreach ($small_cols as $small_col):
            $objSheet->getColumnDimension($small_col)->setWidth('9');
        endforeach;

        //设置中等列宽度
        $normal_cols = ["I", "K", "L", "N", "O", "P", "Q"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('12');
        endforeach;

        //设置最大列宽度
        $big_cols = ["B", "C", "D", "E", "F", "H", "J", "M", "R"];
        foreach ($big_cols as $big_col):
            $objSheet->getColumnDimension($big_col)->setWidth('18');
        endforeach;

        $objSheet->setCellValue("A3", "询单编号 : " . $quote['quoter_info']['serial_no'])->mergeCells("A3:R3");
        $objSheet->setCellValue("A4", "报价人 : " . $quote['quoter_info']['quoter_name'])->mergeCells("A4:E4");
        $objSheet->setCellValue("A5", "电话 : " . $quote['quoter_info']['quoter_mobile'])->mergeCells("A5:E5");
        $objSheet->setCellValue("A6", "邮箱 : " . $quote['quoter_info']['quoter_email'])->mergeCells("A6:E6");

        $objSheet->setCellValue("F4", "询价单位 : " . $quote['quoter_info']['buyer_name'])->mergeCells("F4:R4");
        $objSheet->setCellValue("F5", "业务对接人 : " . $quote['quoter_info']['agenter'])->mergeCells("F5:R5");
        $objSheet->setCellValue("F6", "报价时间 : " . $quote['quoter_info']['quote_time'])->mergeCells("F6:R6");


        $objSheet->setCellValue("A7", '易瑞国际电子商务有限公司商务技术部')
                //单元格合并
                ->mergeCells("A7:R7")
                //设置高度
                ->getRowDimension("6")
                ->setRowHeight(26);

        $objSheet->getCell("A7")
                ->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objSheet->setCellValue("A8", "序号\nitem")->mergeCells("A8:A9");
        $objSheet->setCellValue("B8", "名称\nitem")->mergeCells("B8:B9");
        $objSheet->setCellValue("C8", "外文名称\nitem")->mergeCells("C8:C9");
        $objSheet->setCellValue("D8", "规格\nmodel")->mergeCells("D8:D9");
        $objSheet->setCellValue("E8", "客户需求描述\nRequirementSpecifications")->mergeCells("E8:E9");
        $objSheet->setCellValue("F8", "报价产品描述\nSupplySpecifications")->mergeCells("F8:F9");
        $objSheet->setCellValue("G8", "数量\nQty")->mergeCells("G8:G9");
        $objSheet->setCellValue("H8", "单位\nUnit")->mergeCells("H8:H9");
        $objSheet->setCellValue("I8", "产品品牌\nBrand")->mergeCells("I8:I9");
        $objSheet->setCellValue("J8", "报出EXW单价\nQuote EXW Unit Price")->mergeCells("J8:J9");
        $objSheet->setCellValue("K8", "贸易单价\nTrade Unit Price")->mergeCells("K8:K9");
        $objSheet->setCellValue("L8", "单重\nUnit\nWeight(kg)")->mergeCells("L8:L9");
        $objSheet->setCellValue("M8", "包装体积\nPacking\nSizeL*W*H(mm)")->mergeCells("M8:M9");
        $objSheet->setCellValue("N8", "包装方式\nPacking")->mergeCells("N8:N9");
        $objSheet->setCellValue("O8", "交货期\nDelivery\n(Working Day)")->mergeCells("O8:O9");
        $objSheet->setCellValue("P8", "有效期\nValidity\n(Working Day)")->mergeCells("P8:P9");
        $objSheet->setCellValue("Q8", "备注\nRemark")->mergeCells("Q8:Q9");

        $cols = ["A8", "B8", "C8", "D8", "E8", "F8", "G8", "H8", "I8", "J8", "K8", "L8", "M8", "N8", "O8", "P8", "Q8"];
        foreach ($cols as $col) {
            $objSheet->getStyle($col)
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        //判断quote_item子数组
        if (is_array($quote['quote_items']) && !empty($quote['quote_items'])) {
            $row_num = 10;
            foreach ($quote['quote_items'] as $item) {
                $objSheet->setCellValue("A" . $row_num, $item['id']);
                $objSheet->setCellValue("B" . $row_num, $item['name_zh']);
                $objSheet->setCellValue("C" . $row_num, $item['name']);
                $objSheet->setCellValue("D" . $row_num, $item['model']);
                $objSheet->setCellValue("E" . $row_num, $item['remarks']);
                $objSheet->setCellValue("F" . $row_num, $item['quote_remarks']);
                $objSheet->setCellValue("G" . $row_num, $item['qty']);
                $objSheet->setCellValue("H" . $row_num, $item['unit']);
                $objSheet->setCellValue("I" . $row_num, $item['brand']);
                $objSheet->setCellValue("J" . $row_num, $item['exw_unit_price']);
                $objSheet->setCellValue("K" . $row_num, $item['quote_unit_price']);
                $objSheet->setCellValue("L" . $row_num, $item['gross_weight_kg']);
                $objSheet->setCellValue("M" . $row_num, $item['package_size']);
                $objSheet->setCellValue("N" . $row_num, $item['package_mode']);
                $objSheet->setCellValue("O" . $row_num, $item['delivery_days']);
                $objSheet->setCellValue("P" . $row_num, $item['period_of_validity']);
                $objSheet->setCellValue("Q" . $row_num, $item['quote_remarks']);

                //设置居中
                $cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q"];
                foreach ($cols as $col) {
                    $objSheet->getStyle($col . $row_num)
                            ->getAlignment()
                            ->setWrapText(true)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

                $row_num++;
            }

            $objSheet->getStyle("A7:K" . $row_num)->applyFromArray($styleArray);

            $num10 = $row_num + 1;
            $objSheet->setCellValue("A" . $num10, "")->mergeCells("A" . $num10 . ":R" . $num10);

            $num11 = $row_num + 2;
            $objSheet->setCellValue("B" . $num11, "总重(kg)");
            $objSheet->setCellValue("C" . $num11, $quote['quote_info']['total_weight']);
            $objSheet->setCellValue("D" . $num11, "包装总体积(m³)");
            $objSheet->setCellValue("E" . $num11, $quote['quote_info']['package_volumn']);
            $objSheet->setCellValue("F" . $num11, "付款方式");
            $objSheet->setCellValue("G" . $num11, $quote['quote_info']['payment_mode']);
            $objSheet->setCellValue("H" . $num11, "");
            $objSheet->setCellValue("I" . $num11, "");
            $objSheet->setCellValue("J" . $num11, "EXW交货周期(天)");
            $objSheet->setCellValue("K" . $num11, $quote['quote_info']['delivery_period']);

            $num12 = $row_num + 3;
            $objSheet->setCellValue("B" . $num12, "贸易术语");
            $objSheet->setCellValue("C" . $num12, $quote['quote_info']['trade_terms_bn']);
            $objSheet->setCellValue("D" . $num12, "运输方式");
            $objSheet->setCellValue("E" . $num12, $quote['quote_info']['trans_mode_bn']);
            $objSheet->setCellValue("F" . $num12, "存放地");
            $objSheet->setCellValue("G" . $num12, $quote['quote_info']['dispatch_place']);
            $objSheet->setCellValue("H" . $num12, "目的地");
            $objSheet->setCellValue("I" . $num12, $quote['quote_info']['delivery_addr']);
            $objSheet->setCellValue("J" . $num12, "运输周期(天)");
            $objSheet->setCellValue("K" . $num12, $quote['quote_info']['est_transport_cycle']);

            $num13 = $row_num + 4;
            $objSheet->setCellValue("B" . $num13, "物流合计");
            $objSheet->setCellValue("C" . $num13, $quote['quote_info']['total_logi_fee']);
            $objSheet->setCellValue("D" . $num13, "物流合计币种");
            $objSheet->setCellValue("E" . $num13, "USD");
            $objSheet->setCellValue("F" . $num13, "银行费用");
            $objSheet->setCellValue("G" . $num13, $quote['quote_info']['total_bank_fee']);
            $objSheet->setCellValue("H" . $num13, "银行费用币种");
            $objSheet->setCellValue("I" . $num13, "USD");
            $objSheet->setCellValue("J" . $num13, "");
            $objSheet->setCellValue("K" . $num13, "");

            $num14 = $row_num + 5;
            $objSheet->setCellValue("B" . $num14, "EXW合计");
            $objSheet->setCellValue("C" . $num14, $quote['quote_info']['total_exw_price']);
            $objSheet->setCellValue("D" . $num14, "EXW合计币种");
            $objSheet->setCellValue("E" . $num14, "USD");
            $objSheet->setCellValue("F" . $num14, "出信用保险");
            $objSheet->setCellValue("G" . $num14, $quote['quote_info']['total_insu_fee']);
            $objSheet->setCellValue("H" . $num14, "出信用保险币种");
            $objSheet->setCellValue("I" . $num14, "USD");
            $objSheet->setCellValue("J" . $num14, "");
            $objSheet->setCellValue("K" . $num14, "");

            $num15 = $row_num + 6;
            $objSheet->setCellValue("B" . $num15, "报价合计");
            $objSheet->setCellValue("C" . $num15, $quote['quote_info']['total_quote_price']);
            $objSheet->setCellValue("D" . $num15, "报价合计币种");
            $objSheet->setCellValue("E" . $num15, $quote['quote_info']['quote_cur_bn']);
            $objSheet->setCellValue("F" . $num15, "");
            $objSheet->setCellValue("G" . $num15, "");
            $objSheet->setCellValue("H" . $num15, "");
            $objSheet->setCellValue("I" . $num15, "");
            $objSheet->setCellValue("J" . $num15, "");
            $objSheet->setCellValue("K" . $num15, "");

            $objSheet->getStyle("A" . $num11 . ":K" . $num15)->applyFromArray($styleArray);

            $total_rows = [
                "A" . $num11, "A" . $num12, "A" . $num13, "A" . $num14, "A" . $num15, "B" . $num11, "B" . $num12, "B" . $num13, "B" . $num14, "B" . $num15,
                "C" . $num11, "C" . $num12, "C" . $num13, "C" . $num14, "C" . $num15, "D" . $num11, "D" . $num12, "D" . $num13, "D" . $num14, "D" . $num15,
                "E" . $num11, "E" . $num12, "E" . $num13, "E" . $num14, "E" . $num15, "F" . $num11, "F" . $num12, "F" . $num13, "F" . $num14, "F" . $num15,
                "G" . $num11, "G" . $num12, "G" . $num13, "G" . $num14, "G" . $num15, "H" . $num11, "H" . $num12, "H" . $num13, "H" . $num14, "H" . $num15,
                "I" . $num11, "I" . $num12, "I" . $num13, "I" . $num14, "I" . $num15, "J" . $num11, "J" . $num12, "J" . $num13, "J" . $num14, "J" . $num15,
                "K" . $num11, "K" . $num12, "K" . $num13, "K" . $num14, "K" . $num15,
            ];
            foreach ($total_rows as $total_row) {
                $objSheet->getCell($total_row)->getStyle()
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getStyle($total_row)->applyFromArray($styleArray);
            }

            $num16 = $row_num + 7;
            $num17 = $row_num + 8;
            $objSheet->setCellValue("A" . $num16, '报价备注 : ' . $quote['quoter_info']['quote_notes'])->mergeCells("A" . $num16 . ":K" . $num17);
            $objSheet->getStyle("A" . $num16 . ":K" . $num17)->applyFromArray($styleArray);
            $objSheet->getCell("A" . $num16)
                    ->getStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num18 = $row_num + 9;
            $num19 = $row_num + 10;
            $objSheet->setCellValue("A" . $num18, '物流备注 : ' . $quote['quote_info']['logi_remarks'])->mergeCells("A" . $num18 . ":K" . $num19);
            $objSheet->getStyle("A" . $num18 . ":K" . $num19)->applyFromArray($styleArray);
            $objSheet->getCell("A" . $num18)
                    ->getStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num20 = $row_num + 11;
            $num21 = $row_num + 12;
            $objSheet->setCellValue("A" . $num20, "")->mergeCells("A" . $num20 . ":K" . $num21);
            $objSheet->getStyle("A" . $num20 . ":K" . $num21)->applyFromArray($styleArray);
        }

        //TODO 添加logo
        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "FQ_" . date('Ymd-His') . '.xls');
    }

    /**
     * 导出被驳回的所有询单
     */
    public function exportRejectedAction() {

        set_time_limit(0);

        $this->validateRequests();

        $data = $this->getRejectedInquiry();

        $excelFile = $this->createRejectedFile($data);

        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $data['tmp_name'] = $excelFile;
        $data['type'] = 'application/excel';
        $data['name'] = 'excelFile';
        $remoteUrl = $this->postfile($data, $url);

        if (!$remoteUrl) {
            $this->jsonReturn(['code' => '-104', 'message' => '失败']);
        }
        //构建打包文件数组
        $fileName = date('YmdHis');
        $files = [['url' => $excelFile, 'name' => $fileName . '.xls']];

        //上传至FastDFS
        $zipFile = $fileName . '.zip';
        $fileId = $this->packAndUpload($url, $zipFile, $files);
        //上传失败
        if (empty($fileId) || empty($fileId['url'])) {
            $this->jsonReturn(['code' => '-1', 'message' => '导出失败!',]);
            return;
        }

        //删除本地的临时文件
        @unlink($excelFile);
        $this->jsonReturn([
            'code' => '1',
            'message' => '导出成功!',
            'data' => [
                'url' => $fileId['url']
            ]
        ]);
    }

    /**
     * 获取驳回的信息
     * @return mixed
     */
    private function getRejectedInquiry() {

        $condition = $this->validateRequestParams();

        $inquiryCheckLog = new InquiryCheckLogModel();

        $field = "b.id,b.serial_no,b.agent_id,b.adhoc_request,b.now_agent_id,b.org_id,b.area_bn,b.country_bn,b.created_at inquiry_created_at,a.created_at,a.created_by,a.op_note,a.in_node";
        //$where = "b.deleted_flag='N' AND a.action='REJECT' ";
        $where = [
            'b.deleted_flag' => 'N',
            'a.action' => 'REJECT'
        ];

        //时间
        if (!empty($condition['create_start_time']) && !empty($condition['create_end_time'])) {
            $where['b.created_at'] = [
                ['egt', $condition['create_start_time']],
                ['elt', $condition['create_end_time'] . ' 23:59:59']
            ];
        }

        $data = $inquiryCheckLog->alias('a')->join('erui_rfq.inquiry b ON a.inquiry_id=b.id', 'LEFT')
                ->field($field)
                ->where($where)
                ->select();

        $employee = new EmployeeModel();
        $org = new OrgModel();
        $region = new RegionModel();
        $country = new CountryModel();

        foreach ($data as &$item) {

            $item['agent'] = $employee->where(['id' => $item['agent_id']])->getField('name');
            $item['now_agent'] = $employee->where(['id' => $item['now_agent_id']])->getField('name');
            $item['org_name'] = $org->getNameById($item['org_id'], 'zh');    //增加默认语言 ‘zh’
            $item['region_name'] = $region->where(['bn' => trim($item['area_bn']), 'lang' => 'zh'])->getField('name');
            $item['country_name'] = $country->where(['bn' => trim($item['country_bn']), 'lang' => 'zh'])->getField('name');

            $item['created_by'] = $employee->where(['id' => $item['created_by']])->getField('name');
            $item['in_node'] = $this->setNode($item['in_node']);
        }

        return $data;
    }

    /**
     * 设置环节名称
     * @param $node
     *
     * @return string
     */
    private function setNode($node) {

        switch ($node) {
            case 'DRAFT' : $nodeName = '草稿';
                break;
            case 'REJECT_MARKET' : $nodeName = '驳回市场';
                break;
            case 'BIZ_DISPATCHING' : $nodeName = '事业部分单员';
                break;
            case 'CC_DISPATCHING' : $nodeName = '易瑞客户中心分单员';
                break;
            case 'BIZ_QUOTING' : $nodeName = '事业部报价';
                break;
            case 'LOGI_DISPATCHING' : $nodeName = '物流分单员';
                break;
            case 'LOGI_QUOTING' : $nodeName = '物流报价';
                break;
            case 'LOGI_APPROVING' : $nodeName = '物流审核';
                break;
            case 'BIZ_APPROVING' : $nodeName = '事业部核算';
                break;
            case 'MARKET_APPROVING' : $nodeName = '事业部审核';
                break;
            case 'MARKET_CONFIRMING' : $nodeName = '市场确认';
                break;
            case 'QUOTE_SENT' : $nodeName = '报价单已发出';
                break;
            case 'INQUIRY_CLOSED' : $nodeName = '报价关闭';
                break;
        }

        return $nodeName;
    }

    private function createRejectedFile($data) {

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('');

        $styleArray = ['borders' => ['outline' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '333333']]]];

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", '序号');
        $objSheet->setCellValue("B1", '询单编号');
        $objSheet->setCellValue("C1", '区域');
        $objSheet->setCellValue("D1", '国家');
        $objSheet->setCellValue("E1", '市场经办人');
        $objSheet->setCellValue("F1", '原询单所属事业部');
        $objSheet->setCellValue("G1", '询价时间');
        $objSheet->setCellValue("H1", '驳回环节');
        $objSheet->setCellValue("I1", '询单描述');
        $objSheet->setCellValue("J1", '驳回人');
        $objSheet->setCellValue("K1", '驳回时间');
        $objSheet->setCellValue("L1", '驳回理由');
        $objSheet->setCellValue("M1", '当前办理人');
        $objSheet->setCellValue("N1", '现询单所属事业部');

        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $normal_cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $objSheet->getColumnDimension("A")->setWidth('4');

        $startRow = 2;
        if (!empty($data)) {
            foreach ($data as $k => $v) {

                $objSheet->getRowDimension($startRow)->setRowHeight(30);

                $objSheet->setCellValue("A" . $startRow, $k + 1);
                $objSheet->setCellValue("B" . $startRow, $v['serial_no']);
                $objSheet->setCellValue("C" . $startRow, $v['region_name']);
                $objSheet->setCellValue("D" . $startRow, $v['country_name']);
                $objSheet->setCellValue("E" . $startRow, $v['agent']);
                $objSheet->setCellValue("F" . $startRow, $v['org_name']);
                $objSheet->setCellValue("G" . $startRow, $v['inquiry_created_at']);
                $objSheet->setCellValue("H" . $startRow, $v['in_node']);
                $objSheet->setCellValue("I" . $startRow, $v['adhoc_request']);
                $objSheet->setCellValue("J" . $startRow, $v['created_by']);
                $objSheet->setCellValue("K" . $startRow, $v['created_at']);
                $objSheet->setCellValue("L" . $startRow, $v['op_note']);
                $objSheet->setCellValue("M" . $startRow, $v['now_agent']);
                $objSheet->setCellValue("N" . $startRow, $v['org_name']);

                $objSheet->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("B" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("C" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("E" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("F" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("G" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("H" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("I" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("J" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("K" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("L" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("M" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("N" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $startRow++;
            }
        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "REJECTED_" . date('Ymd-His') . '.xls');
    }

}
