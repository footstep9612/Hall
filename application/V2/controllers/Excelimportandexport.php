<?php

/*
 * @desc 导入和导出EXCL
 *
 * @author liujf
 * @time 2017-12-21
 */

class ExcelimportandexportController extends PublicController {

    public function init() {
        ini_set('display_errors', 'On');
        error_reporting(E_ERROR | E_STRICT);

        $this->inquiryModel = new InquiryModel();
        $this->inquiryCheckLogModel = new InquiryCheckLogModel();
        $this->inquiryItemModel = new InquiryItemModel();
        $this->quoteItemModel = new QuoteItemModel();
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
        $this->supplierModel = new SupplierModel();
        $this->productModel = new ProductModel();
        $this->goodsModel = new GoodsModel();
        $this->sinosurePolicyModel = new SinosurePolicyModel();

        $this->time = date('Y-m-d H:i:s');

        $this->_getRequestUrl();
        $this->_getFastDFSUrl();
        $this->_getExcelDir();
    }

    /**
     * @desc 导入订单基本信息
     *
     * @author liujf
     * @time 2017-12-22
     */
    public function importOrderBaseInfoAction() {
        $condition = $this->_trim($this->_getPut());

        if ($condition['floder'] == '')
            jsonReturn('', -101, '缺少文件夹参数!');

        $classify = $this->_getTempletAndAttach($condition['floder']);

        // 获取订单模板数据
        $templet = $classify['templet'][0];
        $templetBaseData = $this->_trim(ExcelHelperTrait::ready2import($templet));
        $templetDeliveryData = $this->_trim(ExcelHelperTrait::ready2import($templet, 1));
        $templetPaymentData = $this->_trim(ExcelHelperTrait::ready2import($templet, 2));
        /* $templetAddressData = $this->_trim(ExcelHelperTrait::ready2import($templet, 2));
          $templetPaymentData = $this->_trim(ExcelHelperTrait::ready2import($templet, 3)); */

        /* 批量导入数据 */
        $baseDataIndex = $deliveryDataIndex = $addressDataIndex = $paymentDataIndex = 0;
        $importOrderList = $importBuyerContactList = $importOrderContactList = $importDeliveryList = $importAddressList = $importPaymentList = $executeNoArr = $orderIdMapping = $attachNameMapping = [];
        // 没有客户的订单执行单号
        $noBuyerExecuteNoArr = [];
        foreach ($templetBaseData as $baseInfo) {
            if ($baseDataIndex > 1 && $baseInfo[1] != '') {
                $executeNo = strtoupper($baseInfo[1]);
                $buyerId = $this->buyerModel->where(['buyer_code' => $baseInfo[4], 'deleted_flag' => 'N'])->getField('id');
                // 记录没有客户的订单执行单号
                if (!$buyerId) {
                    $noBuyerExecuteNoArr[] = $executeNo;
                    /* $baseDataIndex++;
                      continue; */
                }
                $orderData = [
                    'po_no' => $baseInfo[0],
                    'execute_no' => $executeNo,
                    'contract_date' => $baseInfo[2] == '' ? null : $this->_getStorageDate($baseInfo[2]),
                    'buyer_id' => $buyerId == '' ? null : $buyerId,
                    'order_agent' => $this->employeeModel->getUserIdByNo($baseInfo[5]) ?: null,
                    'execute_date' => $baseInfo[6] == '' ? null : $this->_getStorageDate($baseInfo[6]),
                    //'agent_id' => $this->employeeModel->getUserIdByNo($baseInfo[10]) ? : null,
                    'amount' => $baseInfo[14] == '' ? null : str_replace(',', '', $baseInfo[14]),
                    'currency_bn' => $baseInfo[15],
                    'trade_terms_bn' => $baseInfo[16],
                    /* 'trans_mode_bn' => $baseInfo[17],
                      'from_country_bn' => $baseInfo[18],
                      'from_port_bn' => $baseInfo[19],
                      'to_country_bn' => $baseInfo[20],
                      'to_port_bn' => $baseInfo[21],
                      'address' => $baseInfo[22], */
                    // 订单状态为进行中
                    'show_status' => 'GOING',
                    'deleted_flag' => 'N',
                    'created_at' => $this->time
                ];
                // 需要导入的订单执行单号和数据
                $executeNoArr[] = $executeNo;
                $importOrderList[] = $orderData;
                /* // 需要导入的采购商联系人数据
                  $buyerContactData = [
                  'execute_no' => $executeNo,
                  'name' => $baseInfo[7],
                  'phone' => $baseInfo[8],
                  'email' => $baseInfo[9],
                  'created_at' => $this->time
                  ];
                  $importBuyerContactList[] = $buyerContactData;
                  // 需要导入的订单联系人数据
                  $orderContactData = [
                  'name' => $baseInfo[11],
                  'company' => '易瑞',
                  'phone' => $baseInfo[12],
                  'email' => $baseInfo[13],
                  'created_at' => $this->time
                  ];
                  $importOrderContactList[] = $orderContactData;
                  // 需要导入的附件名称和执行单号的映射(非必填)
                  if ($baseInfo[23] != '') {
                  $attachNameMapping[$executeNo] = $baseInfo[23];
                  }
                  // 需要导入的交货信息数据(非必填)
                  if ($baseInfo[24] != '') {
                  $deliveryData = json_decode($baseInfo[24], true);
                  $deliveryData['execute_no'] = $executeNo;
                  $deliveryData['created_at'] = $this->time;
                  $importDeliveryList[] = $deliveryData;
                  } */
                // 需要导入的收货人地址数据
                if ($baseInfo[25] != '') {
                    $addressData = [
                        'execute_no' => $executeNo,
                        'address' => $baseInfo[22],
                        'name' => $baseInfo[25],
                        'created_at' => $this->time
                    ];
                    $importAddressList[] = $addressData;
                }
                /* // 需要导入的结算方式数据(非必填)
                  if ($baseInfo[26] != '') {
                  $paymentData = json_decode($baseInfo[26], true);
                  $paymentData['execute_no'] = $executeNo;
                  $paymentData['created_at'] = $this->time;
                  $importPaymentList[] = $paymentData;
                  } */
            }
            $baseDataIndex++;
        }
        /* // 打印没有客户的订单执行单号
          print_r($noBuyerExecuteNoArr);exit; */
        // 需要导入的订单数据列表中加入订单编号
        $listCount = count($importOrderList);
        $orderNoArr = $this->_getOrderNoArr($listCount);
        for ($i = 0; $i < $listCount; $i++) {
            $importOrderList[$i]['order_no'] = $orderNoArr[$i];
        }
        if ($importOrderList) {
            // 导入订单数据
            $importOrderResult = $this->orderModel->addAll($importOrderList);
            if (!$importOrderResult)
                jsonReturn('', -101, '订单数据导入失败!');
            // 订单执行单号和订单ID的映射关系
            foreach ($executeNoArr as $executeNo) {
                $orderIdMapping[$executeNo] = $this->_getOrderIdByExecuteNo($executeNo);
            }
            foreach ($templetDeliveryData as $deliveryInfo) {
                if ($deliveryDataIndex > 1 && $deliveryInfo[0] != '') {
                    $executeNo = strtoupper($deliveryInfo[0]);
                    $orderId = $orderIdMapping[$executeNo];
                    if ($orderId) {
                        // 需要导入的交货信息数据
                        $deliveryData = [
                            'order_id' => $orderId,
                            'describe' => $deliveryInfo[1],
                            'delivery_at' => $deliveryInfo[2] == '' ? null : $this->_getStorageDate($deliveryInfo[2]),
                            'created_at' => $this->time
                        ];
                        $importDeliveryList[] = $deliveryData;
                    }
                }
                $deliveryDataIndex++;
            }
            /* foreach ($templetAddressData as $addressInfo) {
              if ($addressDataIndex > 1 && $addressInfo[0] != '') {
              $executeNo = strtoupper($addressInfo[0]);
              $orderId = $orderIdMapping[$executeNo];
              if ($orderId) {
              // 需要导入的收货人地址数据
              $addressData = [
              'order_id' => $orderId,
              'name' => $addressInfo[1],
              'tel_number' => $addressInfo[2],
              'area_bn' => $addressInfo[3],
              'country' => $addressInfo[4],
              'city' => $addressInfo[5],
              'zipcode' => $addressInfo[6],
              'fax' => $addressInfo[7],
              'address' => $addressInfo[8],
              'email' => $addressInfo[9],
              'created_at' => $this->time
              ];
              $importAddressList[] = $addressData;
              }
              }
              $addressDataIndex++;
              } */
            foreach ($templetPaymentData as $paymentInfo) {
                if ($paymentDataIndex > 1 && $paymentInfo[0] != '') {
                    $executeNo = strtoupper($paymentInfo[0]);
                    $orderId = $orderIdMapping[$executeNo];
                    if ($orderId) {
                        // 需要导入的结算方式数据
                        $paymentData = [
                            'order_id' => $orderId,
                            'name' => $paymentInfo[1],
                            'amount' => $paymentInfo[2] == '' ? null : str_replace(',', '', $paymentInfo[2]),
                            'payment_mode' => $paymentInfo[3],
                            'payment_at' => $paymentInfo[4] == '' ? null : $this->_getStorageDate($paymentInfo[4]),
                            'created_at' => $this->time
                        ];
                        $importPaymentList[] = $paymentData;
                    }
                }
                $paymentDataIndex++;
            }
            /* // 导入采购商联系人数据
              foreach ($importBuyerContactList as &$importBuyerContact) {
              $importBuyerContact['order_id'] = $orderIdMapping[$importBuyerContact['execute_no']];
              unset($importBuyerContact['execute_no']);
              }
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
              foreach ($importOrderContactList as &$importOrderContact) {
              $importOrderContact['order_id'] = $orderIdMapping[$importOrderContact['execute_no']];
              unset($importOrderContact['execute_no']);
              }
              if ($importOrderContactList) {
              $importOrderContactResult = $this->orderContactModel->addAll($importOrderContactList);
              if (!$importOrderContactResult) jsonReturn('', -101, '订单联系人数据导入失败!');
              }
              // 导入交货信息数据
              foreach ($importDeliveryList as &$importDelivery) {
              $importDelivery['order_id'] = $orderIdMapping[$importDelivery['execute_no']];
              unset($importDelivery['execute_no']);
              } */
            if ($importDeliveryList) {
                $importDeliveryResult = $this->orderDeliveryModel->addAll($importDeliveryList);
                if (!$importDeliveryResult)
                    jsonReturn('', -101, '交货信息数据导入失败!');
            }
            // 导入收货人地址数据
            foreach ($importAddressList as &$importAddress) {
                $importAddress['order_id'] = $orderIdMapping[$importAddress['execute_no']];
                unset($importAddress['execute_no']);
            }
            if ($importAddressList) {
                $importAddressResult = $this->orderAddressModel->addAll($importAddressList);
                if (!$importAddressResult)
                    jsonReturn('', -101, '收货人地址数据导入失败!');
            }
            /* // 导入结算方式数据
              foreach ($importPaymentList as &$importPayment) {
              $importPayment['order_id'] = $orderIdMapping[$importPayment['execute_no']];
              unset($importPayment['execute_no']);
              } */
            if ($importPaymentList) {
                $importPaymentResult = $this->orderPaymentModel->addAll($importPaymentList);
                if (!$importPaymentResult)
                    jsonReturn('', -101, '结算方式数据导入失败!');
            }

            /* 批量上传和导入附件 */
            $this->_batchImportOrderAttach($classify['attach'], 'PO', $orderIdMapping, $attachNameMapping);

            /* 导入成功，返回没有客户的订单执行单号(如果存在) */
            $this->jsonReturn($noBuyerExecuteNoArr ? ['execute_no' => $noBuyerExecuteNoArr] : true);
        } else
            jsonReturn('', -101, '没有可导入的数据!');
    }

    /**
     * @desc 导入订单日志信息
     *
     * @author liujf
     * @time 2017-12-26
     */
    public function importOrderLogInfoAction() {
        $condition = $this->_trim($this->_getPut());

        if ($condition['floder'] == '')
            jsonReturn('', -101, '缺少文件夹参数!');

        $classify = $this->_getTempletAndAttach($condition['floder']);
        $logGroup = strtoupper($condition['log_group']);
        $logGroupArr = ['OUTBOUND', 'LOGISTICS', 'DELIVERY', 'COLLECTION', 'CREDIT'];

        if (!in_array($logGroup, $logGroupArr))
            jsonReturn('', -101, '需要正确的日志分组类别!');

        // 获取模板数据
        $templetData = $this->_trim(ExcelHelperTrait::ready2import($classify['templet'][0]));

        /* 批量导入数据 */
        $dataIndex = 0;
        $importOrderLogList = $orderIdMapping = $attachNameMapping = [];
        foreach ($templetData as $data) {
            if ($dataIndex > 1 && $data[0] != '') {
                $executeNo = strtoupper($data[0]);
                $orderId = $this->_getOrderIdByExecuteNo($executeNo);
                if ($orderId) {
                    // 订单执行单号和订单ID的映射关系
                    $orderIdMapping[$executeNo] = $orderId;
                    $where = ['id' => $orderId];
                    $orderLogData = [
                        'order_id' => $orderId,
                        'log_group' => $logGroup,
                        'created_at' => $this->time
                    ];
                    switch ($logGroup) {
                        case 'OUTBOUND' :
                            $orderLogData['out_no'] = $data[1];
                            $orderLogData['content'] = $data[2];
                            $orderLogData['log_at'] = $data[3] == '' ? null : $this->_getStorageDate($data[3]);
                            // 更新订单状态为已出库
                            $this->_setOrderStatus($where, 'OUTGOING');
                            /* // 需要导入的附件名称和执行单号的映射(非必填)
                              if ($data[4] != '') {
                              $attachNameMapping[$data[0]] = $data[4];
                              } */
                            break;
                        case 'LOGISTICS' :
                            $orderLogData['waybill_no'] = $data[1];
                            $orderLogData['content'] = $data[2];
                            $orderLogData['log_at'] = $data[3] == '' ? null : $this->_getStorageDate($data[3]);
                            // 更新订单状态为已发运
                            $this->_setOrderStatus($where, 'DISPATCHED');
                            /* if ($data[4] != '') {
                              $attachNameMapping[$data[0]] = $data[4];
                              } */
                            break;
                        case 'DELIVERY' :
                            $orderLogData['content'] = $data[1];
                            $orderLogData['log_at'] = $data[2] == '' ? null : $this->_getStorageDate($data[2]);
                            /* if ($data[3] != '') {
                              $attachNameMapping[$data[0]] = $data[3];
                              } */
                            break;
                        case 'CREDIT' :
                            $orderLogData['type'] = $this->_getCreditTypeByName($data[1]);
                            $orderLogData['amount'] = $data[2] == '' ? null : str_replace(',', '', $data[2]);
                            $orderLogData['log_at'] = $data[3] == '' ? null : $this->_getStorageDate($data[3]);
                            $orderLogData['content'] = $data[4];
                            break;
                        case 'COLLECTION' :
                            $orderLogData['content'] = $data[1];
                            $orderLogData['amount'] = $data[2] == '' ? null : str_replace(',', '', $data[2]);
                            $orderLogData['log_at'] = $data[3] == '' ? null : $this->_getStorageDate($data[3]);
                            /* // 更新订单状态为已完成
                              $hasDelivery = $this->orderLogModel->where(['order_id' => $orderId, 'deleted_flag' => 'N'])->getField('id');
                              if ($hasDelivery && $data[4] == '是') $this->_setOrderStatus($where, 'COMPLETED'); */
                            // 更新订单的收款状态
                            $this->orderModel->where($where)->setField('pay_status', $this->_getPayStatusByWhether($data[4]));
                    }
                    // 需要导入的订单日志信息数据
                    $importOrderLogList[] = $orderLogData;
                }
            }
            $dataIndex++;
        }
        if ($importOrderLogList) {
            // 导入订单日志信息数据
            $importOrderLogResult = $this->orderLogModel->addAll($importOrderLogList);
            if (!$importOrderLogResult)
                jsonReturn('', -101, '订单日志信息数据导入失败!');

            /* 批量上传和导入附件 */
            //$this->_batchImportOrderAttach($classify['attach'], $logGroup, $orderIdMapping, $attachNameMapping);

            /* 导入成功 */
            $this->jsonReturn(true);
        } else
            jsonReturn('', -101, '没有可导入的数据!');
    }

    /**
     * @desc 导入信保政策信息
     *
     * @author liujf
     * @time 2018-03-26
     */
    public function importSinosurePolicyInfoAction() {
        $condition = $this->_trim($this->_getPut());

        if ($condition['floder'] == '')
            jsonReturn('', -101, '缺少文件夹参数!');

        $classify = $this->_getTempletAndAttach($condition['floder']);

        // 获取信保政策模板数据
        $templet = $classify['templet'][0];
        $templetSinosurePolicyKeruiData = $this->_trim(ExcelHelperTrait::ready2import($templet));
        $templetSinosurePolicyEruiData = $this->_trim(ExcelHelperTrait::ready2import($templet, 1));
        // 数据拼装
        $sinosurePolicyKeruiDataIndex = $sinosurePolicyEruiDataIndex = 0;
        $importSinosurePolicyList = [];
        foreach ($templetSinosurePolicyKeruiData as $sinosurePolicyKeruiInfo) {
            if ($sinosurePolicyKeruiDataIndex > 2) {
                foreach ($sinosurePolicyKeruiInfo as $k => $v) {
                    if ($k == 1) {
                        $countryList = explode(',', $v);
                    } else if ($k > 1) {
                        foreach ($countryList as $country) {
                            $sinosurePolicyKeruiData = [
                                'country_bn' => $country,
                                'company' => '科瑞',
                                'sign_flag' => 'Y',
                                'tax_rate' => $v / 100,
                                'created_at' => $this->time
                            ];
                            $this->_setSinosurePolicyType($k, $sinosurePolicyKeruiData);
                            $this->_setSinosurePolicySettlePeriod($k, $sinosurePolicyKeruiData);
                            $importSinosurePolicyList[] = $sinosurePolicyKeruiData;
                        }
                    }
                }
            }
            $sinosurePolicyKeruiDataIndex++;
        }
        foreach ($templetSinosurePolicyEruiData as $sinosurePolicyEruiInfo) {
            if ($sinosurePolicyEruiDataIndex > 2) {
                foreach ($sinosurePolicyEruiInfo as $k => $v) {
                    if ($k == 1) {
                        $countryList = explode(',', $v);
                    } else if ($k > 1) {
                        foreach ($countryList as $country) {
                            $sinosurePolicyEruiData = [
                                'country_bn' => $country,
                                'company' => '易瑞',
                                'sign_flag' => 'Y',
                                'tax_rate' => $v / 100,
                                'created_at' => $this->time
                            ];
                            $this->_setSinosurePolicyType($k, $sinosurePolicyEruiData);
                            $this->_setSinosurePolicySettlePeriod($k, $sinosurePolicyEruiData);
                            $importSinosurePolicyList[] = $sinosurePolicyEruiData;
                        }
                    }
                }
            }
            $sinosurePolicyEruiDataIndex++;
        }
        if ($importSinosurePolicyList) {
            // 数据导入
            $importSinosurePolicyResult = $this->sinosurePolicyModel->addAll($importSinosurePolicyList);
            $this->jsonReturn($importSinosurePolicyResult);
        } else
            jsonReturn('', -101, '没有可导入的数据!');
    }

    /**
     * @desc 获取模板和附件
     *
     * @param string $floder 获取的目录
     * @return array
     * @author liujf
     * @time 2017-12-24
     */
    private function _getTempletAndAttach($floder) {
        // 导入路径
        $path = $this->_addSlash($this->excelDir ?: $this->_getExcelDir()) . $floder;
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
        $this->_createDir($excelDir);
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
        $files = $data = [];
        $this->_scanDir($path, $files);
        foreach ($files as $file) {
            if ($this->_isTemplet($file)) {
                $data['templet'][] = $file;
            } else {
                $data['attach'][] = $file;
            }
        }
        return $data;
    }

    /**
     * @desc 扫描目录下所有文件
     *
     * @param string $path 扫描路径
     * @param array $files 文件路径
     * @author liujf
     * @time 2017-12-22
     */
    private function _scanDir($path, &$files) {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..')
                    $this->_scanDir($this->_addSlash($path) . $file, $files);
            }
            $dp->close();
        }
        if (is_file($path))
            $files[] = $path;
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
        $name = pathinfo($attach, PATHINFO_BASENAME);
        $dotpos = strrpos($name, '.');
        if (is_int($dotpos))
            $name = substr($name, 0, $dotpos);
        $tmp = $this->_trim(explode('PO', $name));
        $no = $tmp[0] ?: $tmp[1];
        return $this->_trim(trim($no, '-'));
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
    private function _batchImportOrderAttach(&$attachList, $attachGroup, &$orderIdMapping, &$attachNameMapping) {
        $importAttachList = [];
        foreach ($attachList as $attach) {
            $attachExecuteNo = strtoupper($this->_getAttachExecuteNo($attach));
            $orderId = $orderIdMapping[$attachExecuteNo] ?: $this->_getOrderIdByExecuteNo($attachExecuteNo);
            if ($orderId) {
                // 执行附件上传
                $fileInfo = $this->_uploadToFastDFS($attach);
                if ($fileInfo['code'] == '1') {
                    $attachData = [
                        'order_id' => $orderId,
                        'attach_group' => $attachGroup,
                        'attach_name' => $attachNameMapping[$attachExecuteNo] ?: ($fileInfo['name'] ?: $attachGroup),
                        'attach_url' => $fileInfo['url'],
                        'created_at' => $this->time
                    ];
                    $importAttachList[] = $attachData;
                }
            }
        }
        // 导入附件数据
        if ($importAttachList) {
            $importAttachResult = $this->orderAttachModel->addAll($importAttachList);
            if (!$importAttachResult)
                jsonReturn('', -101, '附件数据导入失败!');
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
    private function _uploadToFastDFS($file) {
        // 本地和测试调用接口上传
        if (parse_url($this->requestUrl ?: $this->_getRequestUrl(), PHP_URL_HOST) == '172.18.18.196') {
            // 上传文件的接口地址
            $url = $this->requestUrl . '/V2/Uploadfile/upload';
            // 上传的文件信息
            $data = [
                'tmp_name' => $file,
                'name' => pathinfo($file, PATHINFO_BASENAME),
                'type' => ExcelHelperTrait::getFileType($file)
            ];
            // 执行文件上传
            return postfile($data, $url);
        } else {
            // 线上直接上传
            $result = ExcelHelperTrait::uploadToFileServer($file);
            return $result['fileId'] ? ['code' => '1', 'url' => $result['fileId'], 'name' => $result['file']['name']] : ['code' => '-103', 'message' => 'error'];
        }
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
        if (!is_int($size))
            return false;
        $orderNoArr = [];
        if ($size > 0) {
            $orderNo = $this->_getNewOrderNo();
            $no = intval(substr($orderNo, 8));
            $today = date('Ymd');
            for ($i = 0; $i < $size; $i++) {
                $orderNoArr[] = $this->_jointMark($today, $no);
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
        $orderNo = $this->orderModel->where(['order_no' => ['like', $today . '%']])->order('id DESC')->getField('order_no');
        $no = $orderNo ? intval(substr($orderNo, 8)) + 1 : 1;
        return $this->_jointMark($today, $no);
    }

    /**
     * @desc 根据授信类型名称获取存储值
     *
     * @param string $name 授信类型名称
     * @return string
     * @author  liujf
     * @time 2017-12-29
     */
    private function _getCreditTypeByName($name) {
        switch ($name) {
            case '支出' :
                return 'SPENDING';
            case '还款' :
                return 'REFUND';
            default :
                return '';
        }
    }

    /**
     * @desc 获取是否全部付款的收款状态存储值
     *
     * @param string $whether 是或者否
     * @return string
     * @author  liujf
     * @time 2017-12-29
     */
    private function _getPayStatusByWhether($whether) {
        switch ($whether) {
            case '是' :
                return 'PAY';
            case '否' :
                return 'PARTPAY';
            default :
                return '';
        }
    }

    /**
     * @desc 获取转换后的存储日期
     *
     * @param string $date 转换前的日期
     * @return string
     * @author  liujf
     * @time 2017-12-28
     */
    private function _getStorageDate($date) {
        return date('Y-m-d', strtotime($date));
    }

    /**
     * @desc 获取请求地址
     *
     * @return string
     * @author liujf
     * @time 2018-01-01
     */
    private function _getRequestUrl() {
        return $this->requestUrl = Yaf_Application::app()->getConfig()->myhost;
    }

    /**
     * @desc 获取FastDFS地址
     *
     * @return string
     * @author liujf
     * @time 2018-05-28
     */
    private function _getFastDFSUrl() {
        return $this->fastDFSUrl = Yaf_Application::app()->getConfig()->fastDFSUrl;
    }

    /**
     * @desc 设置订单状态
     *
     * @param string $where 条件参数
     * @param string $status 订单状态值
     * @return mixed
     * @author liujf
     * @time 2018-01-02
     */
    private function _setOrderStatus($where, $status) {
        return $this->orderModel->where($where)->setField('show_status', $status);
    }

    /**
     * @desc 通过订单执行单号获取订单ID
     *
     * @param string $executeNo 订单执行单号
     * @return mixed
     * @author liujf
     * @time 2018-01-17
     */
    private function _getOrderIdByExecuteNo($executeNo) {
        return $this->orderModel->where(['execute_no' => $executeNo, 'deleted_flag' => 'N'])->order('id DESC')->getField('id');
    }

    /**
     * @desc 设置信保政策类型
     *
     * @param int $index 当前数据索引
     * @param array $data 需设置的数据
     * @author liujf
     * @time 2018-03-26
     */
    private function _setSinosurePolicyType($index, &$data) {
        if ($index > 1 && $index < 7) {
            $data['type'] = 'L/C';
        } else if ($index > 6 && $index < 12) {
            $data['type'] = 'D/P';
        } else if ($index > 11 && $index < 17) {
            $data['type'] = 'D/A&OA';
        }
    }

    /**
     * @desc 设置信保政策账期
     *
     * @param int $index 当前数据索引
     * @param array $data 需设置的数据
     * @author liujf
     * @time 2018-03-26
     */
    private function _setSinosurePolicySettlePeriod($index, &$data) {
        switch (($index - 2) % 5) {
            case 0 :
                $data['start_settle_period'] = 0;
                $data['end_settle_period'] = 30;
                break;
            case 1 :
                $data['start_settle_period'] = 31;
                $data['end_settle_period'] = 90;
                break;
            case 2 :
                $data['start_settle_period'] = 91;
                $data['end_settle_period'] = 180;
                break;
            case 3 :
                $data['start_settle_period'] = 181;
                $data['end_settle_period'] = 270;
                break;
            case 4 :
                $data['start_settle_period'] = 271;
                $data['end_settle_period'] = 360;
        }
    }

    /* ----------------------------------------------------------------------导入和导出及文件生成代码界线---------------------------------------------------------------------- */

    /**
     * @desc 导出询单sku数据
     *
     * @author liujf
     * @time 2018-05-28
     */
    public function exportInquirySkuDataAction() {
        $condition = $this->_init();
        $where = [
            'deleted_flag' => 'N',
            'inquiry_id' => $condition['inquiry_id']
        ];
        $inquiryItemList = $this->inquiryItemModel
                ->field('sku, buyer_goods_no, name, name_zh, qty, unit, brand, model, remarks')
                ->where($where)
                ->order('id')
                ->select();
        $date = date('YmdHi');
        $fileName = "inquiry_sku-$date.xlsx";
        $sheetTitle = '询单sku数据';
        $outPath = $this->_addSlash($this->excelDir) . date('YmdH');
        $this->_createDir($outPath);
        $titleList = [
            '序号',
            '平台sku',
            '客户商品号',
            '外文品名（必填）',
            '中文品名（必填）',
            '数量（必填）',
            '单位（必填）',
            '品牌',
            '型号',
            '客户需求描述',
        ];
        $i = 1;
        $outData = [];
        foreach ($inquiryItemList as $inquiryItem) {
            $outData[] = [
                ['value' => $i],
                ['value' => $inquiryItem['sku']],
                ['value' => $inquiryItem['buyer_goods_no']],
                ['value' => $inquiryItem['name']],
                ['value' => $inquiryItem['name_zh']],
                ['value' => $inquiryItem['qty']],
                ['value' => $inquiryItem['unit']],
                ['value' => $inquiryItem['brand']],
                ['value' => $inquiryItem['model']],
                ['value' => $inquiryItem['remarks']],
            ];
            $i++;
        }
        $this->_handleExportExcelFile($fileName, $titleList, $outData, $sheetTitle, $outPath);
    }

    /**
     * @desc 导出报价sku数据
     *
     * @author liujf
     * @time 2018-05-28
     */
    public function exportQuoteSkuDataAction() {
        $condition = $this->_init();

        $quoteitem_model = new Rfq_QuoteItemModel();
        $date = date('YmdHi');
        $fileName = "quote_sku-$date.xlsx";
        $sheetTitle = '报价sku数据';
        $outPath = $this->_addSlash($this->excelDir) . date('YmdH');
        $this->_createDir($outPath);
        $inquiry_model = new InquiryModel();
        $org_id = $inquiry_model->where(['id' => $condition['inquiry_id'], 'deleted_flag' => 'N'])->getField('org_id');
        $is_erui = (new OrgModel())->getIsEruiById($org_id);
        if ($is_erui == 'Y') {
            $titleList = $quoteitem_model->getTitleListByErui();
            $outData = $quoteitem_model->getListByErui($condition);
        } else {
            $titleList = $quoteitem_model->getTitleListByOtherOrg();
            $outData = $quoteitem_model->getListByOtherOrg($condition);
        }
        $this->_handleExportExcelFile($fileName, $titleList, $outData, $sheetTitle, $outPath);
    }

    /**
     * @desc 生成或导出数据文件页面
     *
     * @author liujf
     * @time 2017-12-21
     */
    public function createOrExportExcelAction() {
        $this->getView()->assign("title", "生成或导出数据文件");
        $this->display('createorexport');
    }

    /**
     * @desc 导出报价分析数据
     *
     * @author liujf
     * @time 2017-12-21
     */
    public function exportQuoteAnalysisDataAction() {
        $this->_getPut();
        $where['a.deleted_flag'] = 'N';
        $where['a.status'] = ['neq', 'DRAFT'];
        $lang = 'zh';
        $inquiryStatus = $this->inquiryModel->getInquiryStatus();
        $inquiryList = $this->inquiryModel->alias('a')
                ->field('a.id, a.serial_no, a.created_at, a.buyer_code, b.name AS country_name, c.name AS area_name, d.name AS org_name')
                ->join('erui_dict.country b ON a.country_bn = b.bn AND b.lang = \'' . $lang . '\' AND b.deleted_flag = \'N\'', 'LEFT')
                ->join('erui_operation.market_area c ON a.area_bn = c.bn AND c.lang = \'' . $lang . '\' AND c.deleted_flag = \'N\'', 'LEFT')
                ->join('erui_sys.org d ON a.org_id = d.id', 'LEFT')
                ->where($where)
                ->order('a.id DESC')
                ->select();
        $date = date("Ymd");
        $fileName = "quote_analysis-$date.xlsx";
        $titleList = [
            '流程编码',
            '询价时间',
            '询单所属事业部',
            '客户编码',
            '区域',
            '国家',
            '是否驳回',
            '驳回环节',
            '询价商品名称',
            '市场提交询单时间',
            '询单第一次被分单的时间',
            '询单最终被分单的时间',
            '产品报价最终报出时间',
            '物流分单的时间',
            '物流最终报出价格的时间',
            '市场确认的时间',
        ];
        $outData = [];
        foreach ($inquiryList as $inquiry) {
            $nodeList = $this->inquiryCheckLogModel->field('in_node, out_node, op_note')->where(['inquiry_id' => $inquiry['id'], 'action' => 'REJECT'])->order('id DESC')->select();
            $nodeName = [];
            foreach ($nodeList as $node) {
                $nodeName[] = $inquiryStatus[$node['in_node']] . '-' . $inquiryStatus[$node['out_node']] . '（' . $node['op_note'] . '）';
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
        $this->_exportExcel($fileName, $titleList, $outData);
    }

    /**
     * @desc 导出供应商数据
     *
     * @author liujf
     * @time 2018-02-26
     */
    public function exportSupplierDataAction() {
        $this->_getPut();
        $where['a.deleted_flag'] = 'N';
        $where['a.status'] = 'APPROVED';
        $supplierList = $this->supplierModel->alias('a')
                ->field('a.id, a.name, a.social_credit_code, a.created_at, a.created_by, a.erui_status, a.checked_by, b.material_cat_name3')
                //->join('erui_sys.org b ON a.org_id = b.id', 'LEFT')
                ->join('erui_supplier.supplier_material_cat b ON a.id = b.supplier_id', 'LEFT')
                ->where($where)
                ->order('a.id DESC')
                ->select();
        $date = date("Ymd");
        $fileName = "supplier-$date.xlsx";
        $titleList = [
            '供应商ID',
            '公司名称',
            '营业执照编码',
            '注册时间',
            '创建人',
            '审核状态',
            //'所属事业部',
            '审核人',
            '英语SPU数量',
            '中文SPU数量',
            '英语SKU数量',
            '中文SKU数量',
            '供货范围',
        ];
        $status = [
            'CHECKING' => '待审核',
            'VALID' => '审核通过'
        ];
        $outData = [];
        foreach ($supplierList as $supplier) {
            $outData[] = [
                ['value' => $supplier['id']],
                ['value' => $supplier['name']],
                ['value' => $supplier['social_credit_code']],
                ['value' => $supplier['created_at']],
                ['value' => $this->employeeModel->getUserNameById($supplier['created_by'])],
                ['value' => $status[$supplier['erui_status']]],
                ['value' => $this->employeeModel->getUserNameById($supplier['checked_by'])],
                ['value' => $this->_getSupplierSpuCount($supplier['id'], 'en')],
                ['value' => $this->_getSupplierSpuCount($supplier['id'], 'zh')],
                ['value' => $this->_getSupplierSkuCount($supplier['id'], 'en')],
                ['value' => $this->_getSupplierSkuCount($supplier['id'], 'zh')],
                ['value' => $supplier['material_cat_name3']],
            ];
        }
        $this->_exportExcel($fileName, $titleList, $outData);
    }

    /**
     * @desc 获取供应商SPU数量
     *
     * @param int $supplierId 供应商ID
     * @param string $lang 语言
     * @author liujf
     * @time 2018-02-26
     */
    private function _getSupplierSpuCount($supplierId, $lang) {
        $where = [
            'a.deleted_flag' => 'N',
            'a.lang' => $lang,
            'b.supplier_id' => $supplierId,
            'b.deleted_flag' => 'N',
        ];
        $count = $this->productModel->alias('a')
                ->join('erui_goods.product_supplier b ON a.spu = b.spu', 'LEFT')
                ->where($where)
                ->count('a.id');
        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取供应商SKU数量
     *
     * @param int $supplierId 供应商ID
     * @param string $lang 语言
     * @author liujf
     * @time 2018-02-26
     */
    private function _getSupplierSkuCount($supplierId, $lang) {
        $where = [
            'a.deleted_flag' => 'N',
            'a.lang' => $lang,
            'b.supplier_id' => $supplierId,
            'b.deleted_flag' => 'N',
        ];
        $count = $this->goodsModel->alias('a')
                ->join('erui_goods.goods_supplier b ON a.sku = b.sku', 'LEFT')
                ->where($where)
                ->count('a.id');
        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 生成土猫商品文件集
     *
     * @author liujf
     * @time 2018-01-07
     */
    public function createToolMallFilesAction() {
        $this->_getPut();
        $date = date('YmdHis');
        // 土猫商品文件夹名称
        $toolMallFolder = $this->_utf8ToGbk('土猫商品');
        // 商品图片文件夹名称
        $goodsImgFolder = 'jpg';
        // 商品介绍图片文件夹名称
        $introduceImgFolder = $this->_utf8ToGbk('技术参数 中文');
        // 土猫商品生成目录
        $toolMallDir = $this->_addSlash($this->excelDir ?: $this->_getExcelDir()) . $toolMallFolder;
        $this->_createDir($toolMallDir);
        // 获取模板文件
        $templetFiles = $this->_getTempletFiles($toolMallDir);
        // 失败记录
        $faile = [];
        // 记录数
        $count = 1;
        // 获取模板数据
        foreach ($templetFiles as $templetFile) {
            $templetData = $this->_trim(ExcelHelperTrait::ready2import($templetFile));
            array_shift($templetData);
            $spuDataList = $skuDataList = [];
            $skuDataList[0] = $this->_getFirstSkuData();
            foreach ($templetData as $data) {
                if ($data[0] != '') {
                    // 生成的商品文件夹名称
                    $goodsFolder = $this->_leftSubGbk($this->_utf8ToGbk($this->_jointMark('', $count, 5) . '_' . $date . '_' . $this->_replaceToline($data[0])), 60);
                    // 商品目录
                    $goodsDir = $this->_addSlash($toolMallDir) . $goodsFolder;
                    $this->_createDir($goodsDir);
                    // 商品图片目录
                    $goodsImgDir = $this->_addSlash($goodsDir) . $goodsImgFolder;
                    // 商品介绍图片目录
                    $introduceImgDir = $this->_addSlash($goodsDir) . $introduceImgFolder;
                    // 商品文件
                    $goodsFile = $this->_addSlash($goodsDir) . $goodsFolder . '_' . $this->_utf8ToGbk('中文') . '.xlsx';
                    // 将生成的spu数据
                    $spuDataList[0] = [
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => $data[0]],
                        ['value' => $data[0]],
                        ['value' => ''],
                        ['value' => $data[5]],
                        ['value' => ''],
                        ['value' => $data[7]],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                    ];
                    // 将生成的sku数据
                    $skuDataList[1] = [
                        ['value' => ''],
                        ['value' => '1'],
                        ['value' => ''],
                        ['value' => $data[2]],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => '1'],
                        ['value' => $data[4]],
                        ['value' => '包'],
                        ['value' => '1'],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                    ];

                    /* 下载商品图片 */
                    $goodsImgUrlList = $this->_trim(explode(',', $data[8]));
                    $this->_downloadImg($goodsImgUrlList, $goodsImgDir, $goodsFolder, 'http:');

                    /* 下载商品介绍图片 */
                    preg_match_all('/<img src="([^"]*)"/i', $data[11], $imgMatch);
                    $introduceImgUrlList = $this->_trim($imgMatch[1]);
                    $this->_downloadImg($introduceImgUrlList, $introduceImgDir, $goodsFolder, 'http:');

                    /* 生成模板文件 */
                    $result = $this->_createGoodsToolFile($goodsFile, $spuDataList, $skuDataList);
                    // 记录哪些生成失败
                    if (!$result)
                        $faile[] = $count;
                    $count++;
                }
            }
        }
        // 生成完成，返回失败的记录
        $this->jsonReturn($faile ? ['faile' => $faile] : true);
    }

    /**
     * @desc 生成云防爆商品文件集
     *
     * @author liujf
     * @time 2018-01-08
     */
    public function createYunFangBaoFilesAction() {
        $this->_getPut();
        $date = date('YmdHis');
        // 云防爆商品文件夹名称
        $yunFangBaoFolder = $this->_utf8ToGbk('云防爆商品');
        // 商品图片文件夹名称
        $goodsImgFolder = 'jpg';
        // 云防爆商品生成目录
        $yunFangBaoDir = $this->_addSlash($this->excelDir ?: $this->_getExcelDir()) . $yunFangBaoFolder;
        $this->_createDir($yunFangBaoDir);
        // 获取模板文件
        $templetFiles = $this->_getTempletFiles($yunFangBaoDir);
        // 失败记录
        $faile = [];
        // 记录数
        $count = 1;
        // 获取模板数据
        foreach ($templetFiles as $templetFile) {
            $templetData = $this->_trim(ExcelHelperTrait::ready2import($templetFile));
            array_shift($templetData);
            $spuDataList = $skuDataList = [];
            $skuDataList[0] = $this->_getFirstSkuData();
            foreach ($templetData as $data) {
                if ($data[1] != '') {
                    // 生成的商品文件夹名称
                    $goodsFolder = $this->_leftSubGbk($this->_utf8ToGbk($this->_jointMark('', $count, 5) . '_' . $date . '_' . $this->_replaceToline($data[1])), 60);
                    // 商品目录
                    $goodsDir = $this->_addSlash($yunFangBaoDir) . $goodsFolder;
                    $this->_createDir($goodsDir);
                    // 商品图片目录
                    $goodsImgDir = $this->_addSlash($goodsDir) . $goodsImgFolder;
                    // 商品文件
                    $goodsFile = $this->_addSlash($goodsDir) . $goodsFolder . '_' . $this->_utf8ToGbk('中文') . '.xlsx';
                    // 质保期
                    preg_match('/(质保期[^月年]+(月|年))/', $data[3], $periodMatch);
                    $guaranteePeriod = $periodMatch[1];
                    // 供货周期
                    preg_match('/\|供货周期:(\d+)天\|/', $data[6], $supplyMatch);
                    $supplyCycle = intval($supplyMatch[1]) ?: '';
                    // 将生成的spu数据
                    $spuDataList[0] = [
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => $data[1]],
                        ['value' => $data[1]],
                        ['value' => ''],
                        ['value' => '南阳云防爆'],
                        ['value' => $data[3]],
                        ['value' => $data[6]],
                        ['value' => ''],
                        ['value' => $guaranteePeriod],
                        ['value' => ''],
                    ];
                    // 将生成的sku数据
                    $skuDataList[1] = [
                        ['value' => ''],
                        ['value' => '1'],
                        ['value' => ''],
                        ['value' => $data[5]],
                        ['value' => $data[2]],
                        ['value' => $supplyCycle],
                        ['value' => '1'],
                        ['value' => ''],
                        ['value' => '包'],
                        ['value' => '1'],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                        ['value' => ''],
                    ];

                    /* 下载商品图片 */
                    $goodsImgUrlList = $this->_trim(explode(',', $data[4]));
                    $this->_downloadImg($goodsImgUrlList, $goodsImgDir, $goodsFolder, 'http://image.ex12580.com');

                    /* 生成模板文件 */
                    $result = $this->_createGoodsToolFile($goodsFile, $spuDataList, $skuDataList);
                    // 记录哪些生成失败
                    if (!$result)
                        $faile[] = $count;
                    $count++;
                }
            }
        }
        // 生成完成，返回失败的记录
        $this->jsonReturn($faile ? ['faile' => $faile] : true);
    }

    /**
     * @desc 生成商品数据导入小工具所需的模板文件
     *
     * @param string $file 文件路径
     * @param array $spuDataList 表格标题
     * @param array $skuDataList 表格数据
     * @author liujf
     * @time 2018-01-06
     */
    private function _createGoodsToolFile($file, $spuDataList, $skuDataList) {
        $objPHPExcel = new PHPExcel();
        $spuTitleList = $this->_getSpuTitleList();
        $spuTitleCount = count($spuTitleList);
        $spuWordArr = $this->_getWordArr($spuTitleCount);
        // 设置产品模板标题
        $this->_setExcelTitle($objPHPExcel, $spuTitleList, $spuWordArr);
        // 填充产品模板数据
        $this->_setExcelData($objPHPExcel, $spuDataList, $spuWordArr);
        $objPHPExcel->getActiveSheet()->setTitle('产品模板');
        $skuTitleList = $this->_getSkuTitleList();
        $skuTitleCount = count($skuTitleList);
        $skuWordArr = $this->_getWordArr($skuTitleCount);
        // 创建一个新的sheet空间
        $objPHPExcel->createSheet();
        // 设置商品模板标题
        $this->_setExcelTitle($objPHPExcel, $skuTitleList, $skuWordArr, 1);
        // 填充商品模板数据
        $this->_setExcelData($objPHPExcel, $skuDataList, $skuWordArr, 1);
        $objPHPExcel->getActiveSheet()->setTitle('商品模板');
        // 生成文件
        return $this->_createExcelFile($objPHPExcel, $file);
    }

    /**
     * @desc 获取产品模板标题
     *
     * @return array
     * @author liujf
     * @time 2018-01-07
     */
    private function _getSpuTitleList() {
        return [
            '产品类别',
            '物料编码',
            '产品名称',
            '展示名称',
            '产品组',
            '产品品牌',
            '产品介绍',
            '技术参数',
            '执行标准',
            '质保期',
            '关键字',
        ];
    }

    /**
     * @desc 获取商品模板标题
     *
     * @return array
     * @author liujf
     * @time 2018-01-07
     */
    private function _getSkuTitleList() {
        return [
            '商品信息',
            '序号',
            '订货号',
            '型号',
            '供应商名称',
            '出货周期(天)',
            '最小包装内裸货商品数量',
            '商品裸货单位',
            '最小包装单位',
            '最小订货数量',
            '供应商供货价',
            '币种',
            '物流信息',
            '裸货尺寸长(mm)',
            '裸货尺寸宽(mm)',
            '裸货尺寸高(mm)',
            '最小包装后尺寸长(mm)',
            '最小包装后尺寸宽(mm)',
            '最小包装后尺寸高(mm)',
            '净重(kg)',
            '毛重(kg)',
            '仓储运输包装及其他要求',
            '包装类型',
            '申报要素',
            '中文品名(报关用)',
            '海关编码',
            '成交单位',
            '退税率(%)',
            '监管条件',
            '境内货源地',
            '用途',
        ];
    }

    /**
     * @desc 获取商品模板生成的第一条数据
     *
     * @return array
     * @author liujf
     * @time 2018-01-08
     */
    private function _getFirstSkuData() {
        $tmpArr = [];
        $skuTitleList = $this->_getSkuTitleList();
        $skuTitleList[0] = $skuTitleList[1] = $skuTitleList[12] = '';
        foreach ($skuTitleList as $skuTitle) {
            $tmpArr[] = ['value' => $skuTitle];
        }
        return $tmpArr;
    }

    /**
     * @desc 下载图片
     *
     * @param array $imgUrlList 图片地址列表
     * @param string $saveDir 图片保存目录
     * @param string $imgName 图片名称（不含文件扩展名）
     * @param string $prefix 无http(s)://字符时的地址连接前缀字符
     * @author liujf
     * @time 2018-01-08
     */
    private function _downloadImg($imgUrlList, $saveDir, $imgName, $prefix = '') {
        if (is_array($imgUrlList)) {
            $imgUrlList = $this->_trim($imgUrlList);
            $i = 1;
            foreach ($imgUrlList as $imgUrl) {
                if ($imgUrl != '') {
                    if (!preg_match('/^https?:\/\/.*/i', $imgUrl))
                        $imgUrl = $prefix . $imgUrl;
                    // 保存的图片名称
                    $saveName = $this->_jointMark($imgName, $i, 2, '_') . '.jpg';
                    $i++;
                    $this->_downloadFile($imgUrl, $saveDir, $saveName);
                }
            }
        }
    }

    /**
     * @desc 生成excel文件
     *
     * @param object $objPHPExcel PHPExcel对象
     * @param string $file 文件路径
     * @return bool
     * @author liujf
     * @time 2018-01-06
     */
    private function _createExcelFile(&$objPHPExcel, $file) {
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        try {
            $objWriter->save($file);
            return true;
        } catch (Exception $e) {
            return false;
        };
    }

    /**
     * @desc 获取指定一级目录模板文件
     *
     * @param string $path 目录路径
     * @return array $files 模板文件路径数组
     * @author liujf
     * @time 2018-01-07
     */
    private function _getTempletFiles($path) {
        $files = [];
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    if ($this->_isTemplet($file))
                        $files[] = $this->_addSlash($path) . $file;
                }
            }
            $dp->close();
        }
        return $files;
    }

    /**
     * @desc 判断是否为模板文件
     *
     * @param string $file 文件路径
     * @return bool
     * @author liujf
     * @time 2018-01-07
     */
    private function _isTemplet($file) {
        return preg_match('/^.*templet(_\d+|\d*)\.xls(x)?$/i', $file) ? true : false;
    }

    /**
     * @desc 处理导出的excel文件
     *
     * @param string $fileName 文件名
     * @param array $titleList 表格标题
     * @param array $dataList 表格数据
     * @param string $sheetTitle sheet标题
     * @param string $outPath 输出的文件路径
     * @return mixed
     * @author liujf
     * @time 2018-05-30
     */
    private function _handleExportExcelFile($fileName, $titleList, $dataList, $sheetTitle, $outPath) {
        $file = $this->_exportExcel($fileName, $titleList, $dataList, $sheetTitle, $outPath, 'file');
        if (file_exists($file)) {
            $fileInfo = $this->_uploadToFastDFS($file);
            if ($fileInfo['code'] == '1') {
                unlink($file);
                $this->jsonReturn(['url' => $this->_addSlash($this->fastDFSUrl) . $fileInfo['url'], 'name' => $fileName]);
            }
        }
        $this->jsonReturn(false);
    }

    /**
     * @desc 导出excel
     *
     * @param string $fileName 文件名
     * @param array $titleList 表格标题
     * @param array $dataList 表格数据
     * @param string $sheetTitle sheet标题
     * @param string $outPath 输出的文件路径
     * @param string $outType 输出方式（浏览器输出、文件输出）
     * @return mixed
     * @author liujf
     * @time 2017-12-19
     */
    private function _exportExcel($fileName, $titleList, $dataList, $sheetTitle = 'Worksheet', $outPath = '', $outType = 'browser') {
        $objPHPExcel = new PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()
                ->setCreator('liujf')
                ->setLastModifiedBy('liujf')
                ->setTitle('Office 2007 XLSX Test Document')
                ->setSubject('Office 2007 XLSX Test Document')
                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Test result file');
        $titleCount = count($titleList);
        $wordArr = $this->_getWordArr($titleCount);
        // 设置excel表格的标题
        $this->_setExcelTitle($objPHPExcel, $titleList, $wordArr);
        // 填充excel表格的数据
        $this->_setExcelData($objPHPExcel, $dataList, $wordArr);
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objSheet = $objPHPExcel->setActiveSheetIndex(0);
        // 设置sheet标题
        $objSheet->setTitle($sheetTitle);
        // 设置字体
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        // 设置 固定行列
        //$objSheet->freezePaneByColumnAndRow(2, 2);
        // 设置边框线颜色
        $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => '00000000']]]];
        $tableNo = "A1:{$wordArr[$titleCount - 1]}" . (count($dataList) + 1);
        $objSheet->getStyle($tableNo)->applyFromArray($styleArray);
        // 设置字体变小以适应宽
        $objSheet->getStyle($tableNo)->getAlignment()->setShrinkToFit(true);
        // 设置自动换行
        $objSheet->getStyle($tableNo)->getAlignment()->setWrapText(true);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        if (strtolower(trim($outType)) == 'file') {
            $file = $this->_addSlash($outPath) . $fileName;
            $objWriter->save($file);
            return $file;
        } else {
            header('Content-Type: application/vnd.ms-excel; charset="UTF-8"');
            header('Content-Disposition: attachment; filename=' . urlencode($fileName));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $objWriter->save('php://output');
        }
    }

    /**
     * @desc 设置excel表格的标题
     *
     * @param object $objPHPExcel PHPExcel对象
     * @param array $titleList 表格标题
     * @param array $wordArr 表格对应的词组
     * @param int $pIndex 表格标签页索引
     * @author liujf
     * @time 2018-01-06
     */
    private function _setExcelTitle(&$objPHPExcel, $titleList, $wordArr, $pIndex = 0) {
        $objPHPExcel->setActiveSheetIndex($pIndex);
        $objSheet = $objPHPExcel->getActiveSheet();
        $titleCount = count($titleList);
        $wordArr = is_array($wordArr) && !empty($wordArr) ? $wordArr : $this->_getWordArr($titleCount);
        for ($i = 0; $i < $titleCount; $i++) {
            $objSheet->setCellValue("$wordArr[$i]1", $titleList[$i]);
            $objSheet->getColumnDimension($wordArr[$i])->setAutoSize(true);
        }
    }

    /**
     * @desc 填充excel表格的数据
     *
     * @param object $objPHPExcel PHPExcel对象
     * @param array $dataList 表格数据
     * @param array $wordArr 表格对应的词组
     * @param int $pIndex 表格标签页索引
     * @author liujf
     * @time 2018-01-06
     */
    private function _setExcelData(&$objPHPExcel, $dataList, $wordArr, $pIndex = 0) {
        $objPHPExcel->setActiveSheetIndex($pIndex);
        $objSheet = $objPHPExcel->getActiveSheet();
        $dataCount = count($dataList);
        $typeArr = ['string' => PHPExcel_Cell_DataType::TYPE_STRING, 'int' => PHPExcel_Cell_DataType::TYPE_NUMERIC];
        for ($i = 2; $i <= $dataCount + 1; $i++) {
            $j = 0;
            foreach ($dataList[$i - 2] as $data) {
                $objSheet->setCellValueExplicit("$wordArr[$j]$i", $data['value'], $typeArr[$data['type']] ?: $typeArr['string']);
                if ($data['type'] == 'int')
                    $objSheet->getStyle("$wordArr[$j]$i")->getNumberFormat()->setFormatCode('#,##0.00');
                $j++;
            }
        }
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
        if ($res)
            $next = key($arr);
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
        $next = strtoupper(trim($prev));
        if ($next == '')
            return 'A';
        $letterArr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $flag = true; //是否全是A的标记
        for ($i = strlen($next) - 1; $i >= 0; $i--) {
            if (in_array($next[$i], $letterArr)) {
                $tmp = $this->_getNextVal($letterArr, $next[$i]);
                $next[$i] = $tmp;
                if ($tmp != 'A') {
                    $flag = false;
                    break;
                }
            } else
                return false;
        }
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
        if (!is_int($size))
            return false;
        $wordArr = [];
        $word = '';
        for ($i = 0; $i < $size; $i++) {
            $word = $this->_getNextWord($word);
            $wordArr[] = $word;
        }
        return $wordArr;
    }

    /**
     * @desc 下载远程文件到指定目录
     *
     * @param string $url 远程文件地址
     * @param string $saveDir 文件保存目录
     * @param string $fileName 保存的文件名称
     * @author liujf
     * @time 2018-01-08
     */
    private function _downloadFile($url, $saveDir, $fileName) {
        $saveDir = $this->_trim($saveDir);
        if ($this->_createDir($saveDir)) {
            $saveFile = $this->_addSlash($saveDir) . $fileName;
            $file = fopen($url, 'rb');
            if ($file) {
                $newf = fopen($saveFile, 'wb');
                if ($newf) {
                    $size = 1024 * 8;
                    while (!feof($file))
                        fwrite($newf, fread($file, $size), $size);
                    fclose($newf);
                }
                fclose($file);
            }
        }
    }

    /**
     * @desc 给字符串连接数字
     *
     * @param string $str 需处理的字符串
     * @param string $input 拼接基础字符
     * @param int $length 拼接的字符长度
     * @param string $mark 连接符号
     * @param string $pad 追加字符
     * @param string $type 追加类型
     * @return string
     * @author liujf
     * @time 2018-01-08
     */
    private function _jointMark($str, $input, $length = 4, $mark = '', $pad = '0', $type = STR_PAD_LEFT) {
        return $str . $mark . str_pad($input, $length, $pad, $type);
    }

    /**
     * @desc 加上目录连接斜线
     *
     * @param string $dir 需处理的目录
     * @return string
     * @author liujf
     * @time 2018-01-08
     */
    private function _addSlash($dir) {
        if (!preg_match('/.*[\\\\\/]$/s', $dir))
            $dir .= DS;
        return $dir;
    }

    /**
     * @desc 替换空格和非目录字符为下划线
     *
     * @param string $str 需处理的字符串
     * @return string
     * @author liujf
     * @time 2018-01-07
     */
    private function _replaceToline($str) {
        return preg_replace('/[\\\\\/:*?"<>|\s\r\n]/', '_', $str);
    }

    /**
     * @desc 编码UTF-8转GBK
     *
     * @param string $str 需转换的字符串
     * @param bool $ignore 是否忽略不能转换的字符
     * @return mixed
     * @author liujf
     * @time 2018-01-07
     */
    private function _utf8ToGbk($str, $ignore = false) {
        return iconv('UTF-8', 'GBK//' . ($ignore === true ? 'IGNORE' : 'TRANSLIT'), $str);
    }

    /**
     * @desc GBK编码从左端开始截取字符串,如果长度不够原样返回
     *
     * @param string $str 需截取的字符串
     * @param int $length 长度
     * @return string
     * @author liujf
     * @time 2018-01-09
     */
    private function _leftSubGbk($str, $length) {
        return is_string($str) && is_int($length) && mb_strlen($str, 'GBK') > $length ? mb_substr($str, 0, $length, 'GBK') : $str;
    }

    /**
     * @desc 创建目录
     *
     * @param string $path 目录路径
     * @return bool
     * @author liujf
     * @time 2018-01-07
     */
    private function _createDir($path) {
        return is_dir($path) ? true : mkdir($path, 0777, true);
    }

    /**
     * @desc 去掉数据两侧的空格
     *
     * @param mixed $data
     * @return mixed
     * @author liujf
     * @time 2018-01-11
     */
    private function _trim($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v)
                $data[$k] = $this->_trim($v);
            return $data;
        } else if (is_object($data)) {
            foreach ($data as $k => $v)
                $data->$k = $this->_trim($v);
            return $data;
        } else if (is_string($data)) {
            return trim($data);
        } else {
            return $data;
        }
    }

    /**
     * @desc 调用父类init方法
     *
     * @return array
     * @author liujf
     * @time 2018-05-28
     */
    private function _init() {
        parent::init();
        return $this->put_data;
    }

    /**
     * @desc 安全验证
     *
     * @author liujf
     * @time 2018-01-07
     */
    private function _getPut() {
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
        return json_decode($param['input'], true) ?: $param['input'];
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

}
