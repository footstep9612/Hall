<?php

/**
 * @desc 报价单明细模型
 * @author 买买提
 */
class QuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 删除报价单项(一个或多个)
     * @param string $ids
     * @return bool True|False
     * @author mmt、liujf
     */
    public function delItem($ids) {
        return $this->where(['inquiry_item_id' => ['in', explode(',', $ids) ?: ['-1']]])->save(['deleted_flag' => 'Y']);
    }

    /**
     * @desc 获取记录总数
     *
     * @param array $request
     * @return int
     * @author liujf
     * @time 2018-04-13
     */
    public function getCount($request) {
        $count = $this->getSqlJoint($request)->count('a.id');
        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取采购总价
     *
     * @param array $request
     * @return int
     * @author liujf
     * @time 2018-06-27
     */
    public function getTotalPurchasePrice($request) {
        $re = $this->getSqlJoint($request)->field('sum(a.purchase_unit_price * b.qty ) as totalPurchasePrice')->find();

        return isset($re['totalPurchasePrice']) ? $re['totalPurchasePrice'] : 0;
    }

    /**
     * 获取sku列表
     * @param $request 条件
     * @return mixed 数据
     * @author mmt、liujf
     */
    public function getList($request) {
        $currentPage = empty($request['currentPage']) ? 1 : $request['currentPage'];
        $pageSize = empty($request['pageSize']) ? 10 : $request['pageSize'];
        $fields = 'a.id,b.id inquiry_item_id,b.sku,b.buyer_goods_no,'
                . 'b.name,b.name_zh,b.qty,b.unit,b.brand inquiry_brand,b.model,'
                . 'b.remarks,b.category,a.supplier_id,a.brand,'
                . 'a.purchase_unit_price,b.qty*a.purchase_unit_price AS total_purchase_price,'
                . 'a.purchase_price_cur_bn,a.gross_weight_kg,'
                . 'a.package_mode,a.package_size,a.stock_loc,a.goods_source,'
                . 'a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.pn,'
                . 'c.attach_name,c.attach_url,b.material_cat_no,a.org_id'; //
        return $this->getSqlJoint($request)
                        ->field($fields)
                        ->page($currentPage, $pageSize)
                        ->order('a.id ASC')
                        ->select();
    }

    /**
     * @desc 获取组装sql后的对象
     *
     * @param array $request
     * @return object
     * @author liujf
     * @time 2018-04-13
     */
    public function getSqlJoint($request) {
        $inquiryItemModel = new InquiryItemModel();
        $inquiryItemAttachModel = new InquiryItemAttachModel();
        $inquiryItemTableName = $inquiryItemModel->getTableName();
        $inquiryItemAttachTableName = $inquiryItemAttachModel->getTableName();
        $where['a.inquiry_id'] = $request['inquiry_id'];
        $where['a.deleted_flag'] = 'N';
        return $this->alias('a')
                        ->join($inquiryItemTableName . ' b ON a.inquiry_item_id = b.id', 'LEFT')
                        ->join($inquiryItemAttachTableName . ' c ON a.inquiry_item_id = c.inquiry_item_id', 'LEFT')
                        ->where($where);
    }

    public function updateSupplier($data) {
        foreach ($data as $key => $value) {
            if (empty($value['period_of_validity'])) {
                $value['period_of_validity'] = null;
            }
            $value['updated_at'] = date('Y-m-d H:i:s');
            try {
                $this->save($this->create($value));
            } catch (Exception $exception) {
                return [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ];
            }
        }
        return true;
    }

    /**
     * 更新SKU信息
     * @param $data 数据对象
     * @param $user 当前用户
     *
     * @return array|bool
     */
    public function updateItem($data, $user) {

        foreach ($data as $key => $value) {

            $value['updated_at'] = date('Y-m-d H:i:s');
            $value['updated_by'] = $user;

            //如果输填写了未报价分析原因
            if (!empty($value['reason_for_no_quote'])) {
                try {
                    $this->save($this->create($value));
                } catch (Exception $exception) {
                    return [
                        'code' => $exception->getCode(),
                        'message' => $exception->getMessage()
                    ];
                }
            } else {
                /**
                 * 如果是选择了供应商，一下信息是必填字段
                 * 报价产品描述，采购单价，采购币种，毛重，包装体积，包装方式，产品来源，存放地，交货期(天)，报价有效期
                 */
                //采购单价
                if (empty($value['purchase_unit_price'])) {
                    return ['code' => '-104', 'message' => '采购单价必填'];
                }
                if (!is_numeric($value['purchase_unit_price'])) {
                    return ['code' => '-104', 'message' => '采购单价必须是数字'];
                }
                //采购币种
                if (empty($value['purchase_price_cur_bn'])) {
                    return ['code' => '-104', 'message' => '采购币种必选'];
                }
                //毛重
//                if (empty($value['gross_weight_kg'])) {
//                    return ['code' => '-104', 'message' => '毛重必填'];
//                }
//                if (!empty($value['gross_weight_kg']) && !is_numeric($value['gross_weight_kg'])) {
//                    return ['code' => '-104', 'message' => '毛重必须是数字'];
//                }
                //包装体积
//                if (empty($value['package_size'])) {
//                    return ['code' => '-104', 'message' => '包装体积必填'];
//                }
//                if (!empty($value['package_size']) && !is_numeric($value['package_size'])) {
//                    return ['code' => '-104', 'message' => '包装体积必须是数字'];
//                }
                //包装方式
                if (empty($value['package_mode'])) {
                    return ['code' => '-104', 'message' => '包装方式必填'];
                }
                //产品来源
                if (empty($value['goods_source'])) {
                    return ['code' => '-104', 'message' => '产品来源必填'];
                }
                //存放地
                if (empty($value['stock_loc'])) {
                    return ['code' => '-104', 'message' => '存放地必填'];
                }
                //交货期(天)，报价有效期
                if (empty($value['delivery_days'])) {
                    return ['code' => '-104', 'message' => '交货期必填'];
                }
                if (!is_numeric($value['delivery_days'])) {
                    return ['code' => '-104', 'message' => '交货期必须是数字'];
                }
                //报价有效期
//                if (empty($value['period_of_validity'])) {
//                    return ['code' => '-104', 'message' => '报价有效期必填'];
//                }
                $value['period_of_validity'] = strtotime($value['period_of_validity']) ? $value['period_of_validity'] : null;
                $value['package_size'] = is_numeric($value['package_size']) ? $value['package_size'] : null;
                $value['gross_weight_kg'] = is_numeric($value['gross_weight_kg']) ? $value['gross_weight_kg'] : null;
                //报价有效期


                $value['status'] = 'QUOTED';
                $value['quote_qty'] = $value['qty'];
                $value['quote_unit'] = $value['unit'];

                $this->save($this->create($value));
            }
        }
        return [
            'code' => 1,
            'message' => L('QUOTE_SUCCESS')
        ];
    }

    /**
     * 保存SKU信息，不加任何必填校验
     * @param $data 数据对象
     * @param $user 当前用户
     * @param $currentPage 当前页
     * @param $pageSize 每页条数
     *
     * @return array|bool
     * @author mmt、liujf
     */
    public function updateItemBatch($data, $user, $currentPage, $pageSize, $is_erui = 'N') {
        $inquiryItemModel = new InquiryItemModel();
        $suppliersModel = new SuppliersModel();
        $materialcat_model = new MaterialCatModel();
        $data = dataTrim($data);
        $supplierFailList = [];
        $i = 0;
        $currentPage = intval($currentPage) ?: 1;
        $pageSize = intval($pageSize) ?: 10;
        $row = ($currentPage - 1) * $pageSize;
        $this->startTrans();



        foreach ($data as $key => $value) {
            $row++;

            if ($is_erui == 'N' && $value['id']) {

                $inquiry_id = $this->where(['id' => $value['id'], 'deleted_flag' => 'N'])->getField('inquiry_id');
                $org_id = (new InquiryModel())->where(['id' => $inquiry_id, 'deleted_flag' => 'N'])->getField('org_id');
                $is_erui = (new OrgModel())->getIsEruiById($org_id);
            }

            // 校验必填字段，如果有未填项且主键id为空就跳过，否则删除该记录
            if ($value['name'] == '' || $value['name_zh'] == '' || $value['qty'] == '' || $value['unit'] == '' || $value['brand'] == '' || $value['purchase_unit_price'] == '' || $value['purchase_price_cur_bn'] == '' || $value['package_mode'] == '' || $value['stock_loc'] == '' || $value['goods_source'] == '' || $value['delivery_days'] == '' || (empty($value['category']) && $is_erui == 'N')) {
                if ($value['id'] == '') {
                    continue;
                } else {
                    $inquiryItemResult = $inquiryItemModel->deleteData(['id' => $value['inquiry_item_id']]);
                    $quoteItemResult = $this->delItem($value['inquiry_item_id']);
                }
            } else {

                if (!empty($value['supplier_name'])) {
                    $supplierId = $suppliersModel
                            ->where(['name' => $value['supplier_name'],
                                'deleted_flag' => 'N'])
                            ->getField('id');
                } else {
                    $supplierId = 0;
                }

                if (!is_numeric($supplierId)) {
                    // 匹配供应商失败列表
                    $supplierFailList[] = $row;
                    continue;
                } else {
                    $value['supplier_id'] = $supplierId;
                }
                if (!is_numeric($value['purchase_unit_price'])) {
                    if ($i > 0) {
                        $this->rollback();
                    }
                    return ['code' => '-104', 'message' => L('QUOTE_PUP_NUMBER')];
                }

                $value['period_of_validity'] = strtotime($value['period_of_validity']) ? $value['period_of_validity'] : null;
                $value['package_size'] = is_numeric($value['package_size']) ? $value['package_size'] : null;
                $value['gross_weight_kg'] = is_numeric($value['gross_weight_kg']) ? $value['gross_weight_kg'] : null;
//                if (!is_numeric($value['gross_weight_kg'])) {
//                    if ($i > 0) {
//                        $this->rollback();
//                    }
//                    return ['code' => '-104', 'message' => L('QUOTE_GW_NUMBER')];
//                }
//                if (!is_numeric($value['package_size'])) {
//                    if ($i > 0) {
//                        $this->rollback();
//                    }
//                    return ['code' => '-104', 'message' => L('QUOTE_PS_NUMBER')];
//                }
                if (!is_numeric($value['delivery_days'])) {
                    if ($i > 0) {
                        $this->rollback();
                    }
                    return ['code' => '-104', 'message' => L('QUOTE_DD_NUMBER')];
                }
                if (!is_numeric($value['qty'])) {
                    if ($i > 0) {
                        $this->rollback();
                    }
                    return ['code' => '-104', 'message' => L('QUOTE_QQ_NUMBER')];
                }
                $time = date('Y-m-d H:i:s');
                if (empty($value['org_id'])) {
                    $value['org_id'] = 0;
                } elseif (!empty($value['org_id']) && is_numeric($value['org_id'])) {
                    $value['org_id'] = intval($value['org_id']);
                } elseif (!empty($value['org_id']) && is_string($value['org_id'])) {
                    preg_match('/.*?-(\d+)$/', $value['org_id'], $org_id);
                    unset($value['org_id']);
                    $value['org_id'] = isset($org_id[1]) ? $org_id[1] : 0;
                } else {
                    $value['org_id'] = intval($value['org_id']);
                }


                if (empty($value['material_cat_no'])) {
                    $value['material_cat_no'] = '';
                } elseif (!empty($value['material_cat_no']) && is_numeric($value['material_cat_no'])) {
                    $value['material_cat_no'] = trim($value['material_cat_no']);
                } elseif (!empty($value['material_cat_no']) && is_string($value['material_cat_no'])) {
                    $material_cat_no = [];
                    preg_match('/(.*?)-(\d+)$/', $value['material_cat_no'], $material_cat_no);
                    $value['material_cat_no'] = isset($material_cat_no[2]) ? $material_cat_no[2] : '';
                    if (empty($value['material_cat_no']) && !empty($material_cat_no[1])) {
                        $value['material_cat_no'] = $materialcat_model->getCatNoByRealName($material_cat_no[1]);
                    }
                } elseif (!empty($value['material_cat_no']) && is_array($value['material_cat_no'])) {
                    preg_match('/.*?-(\d+)$/', $value['material_cat_no'][count($value['material_cat_no']) - 1], $material_cat_no);
                    unset($value['material_cat_no']);
                    $value['material_cat_no'] = isset($material_cat_no[1]) ? $material_cat_no[1] : '';
                }
                $inquiryItemData = $quoteItemData = $value;
                unset($inquiryItemData['id'], $quoteItemData['id']);
                $inquiryItemData['brand'] = $value['inquiry_brand'];



                $quoteItemData['quote_qty'] = $value['qty'];
                $quoteItemData['quote_unit'] = $value['unit'];
                $quoteItemData = $this->create($quoteItemData);

                if ($value['id'] == '') {
                    $inquiryItemData['created_by'] = $user;
                    $inquiryItemResult = $inquiryItemModel->addData($inquiryItemData);

                    $quoteItemData['inquiry_item_id'] = $inquiryItemResult['insert_id'];
                    $quoteItemData['created_by'] = $user;
                    $quoteItemData['created_at'] = $time;
                    $quoteItemResult = $this->add($quoteItemData);
                } else {
                    $inquiryItemData['id'] = $value['inquiry_item_id'];
                    $inquiryItemData['updated_by'] = $user;
                    $inquiryItemResult = $inquiryItemModel->updateData($inquiryItemData);

                    $quoteItemData['id'] = $value['id'];
                    $quoteItemData['updated_by'] = $user;
                    $quoteItemData['updated_at'] = $time;
                    $quoteItemResult = $this->save($quoteItemData);
                }
            }
            if ($inquiryItemResult['code'] != 1 || !$quoteItemResult) {
                $this->rollback();
                return ['code' => '-101', 'message' => L('FAIL')];
            }
            $i++;
        }
        $this->commit();
        return ['code' => '1', 'message' => L('SUCCESS'), 'supplier_fail_list' => implode(',', $supplierFailList)];
    }

    public function syncSku($request, $user) {

        $quoteModel = new QuoteModel();
        $inquiryItemModel = new InquiryItemModel();
        //查询所有已经添加过的，后面判断是添加还是修改
        $quoteItems = $this->where(['inquiry_id' => $request['inquiry_id'], 'deleted_flag' => 'N'])->getField('inquiry_item_id', true);
        $quoteId = $quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']);

        $inquiryItems = $inquiryItemModel->where(['inquiry_id' => $request['inquiry_id'], 'deleted_flag' => 'N'])->select();

        foreach ($inquiryItems as $inquiry => $item) {
            //判断是添加还是修改
            if (!in_array($item['id'], $quoteItems)) {
                $this->add($this->create([
                            'quote_id' => $quoteId,
                            'inquiry_id' => $item['inquiry_id'],
                            'inquiry_item_id' => $item['id'],
                            'sku' => $item['sku'],
                            'quote_qty' => $item['qty'],
                            'quote_unit' => $item['unit'],
                            'created_by' => $user,
                            'created_at' => date('Y-m-d H:i:s')
                ]));
            }
        }
    }

    /**
     * @desc 获取报价审核人SKU记录总数
     *
     * @param array $request
     * @return int
     * @author liujf
     * @time 2018-04-20
     */
    public function getFinalCount($request) {
        $count = $this->getFinalSqlJoint($request)->count('a.id');
        return $count > 0 ? $count : 0;
    }

    /**
     * 获取SKU关联信息
     * author:张玉良、刘俊飞
     */
    public function getQuoteFinalSku($request) {
        $currentPage = empty($request['currentPage']) ? 1 : $request['currentPage'];
        $pageSize = empty($request['pageSize']) ? 10 : $request['pageSize'];
        $fields = 'c.id,c.sku,b.buyer_goods_no,b.name,b.name_zh,b.qty,b.unit,b.brand,'
                . 'b.model,b.remarks,b.category,a.exw_unit_price,'
                . 'a.quote_unit_price,c.exw_unit_price final_exw_unit_price,'
                . 'c.quote_unit_price final_quote_unit_price,a.gross_weight_kg,'
                . 'a.package_mode,a.package_size,a.delivery_days,a.period_of_validity,'
                . 'a.goods_source,a.stock_loc,a.reason_for_no_quote,b.material_cat_no,a.org_id';
        $this->getFinalSqlJoint($request)
                ->field($fields)
                ->order('a.id ASC');
        if (!empty($request['currentPage'])) {
            $this->page($currentPage, $pageSize);
        }
        $data = $this->select();

        return$data;
    }

    /**
     * @desc 获取报价审核人SKU组装sql后的对象
     *
     * @param array $request
     * @return object
     * @author liujf
     * @time 2018-04-20
     */
    public function getFinalSqlJoint($request) {
        $inquiryItemModel = new InquiryItemModel();
        $finalQuoteItemModel = new FinalQuoteItemModel();
        $inquiryItemTableName = $inquiryItemModel->getTableName();
        $finalQuoteItemTableName = $finalQuoteItemModel->getTableName();
        $where['a.inquiry_id'] = $request['inquiry_id'];
        $where['a.deleted_flag'] = 'N';
        return $this->alias('a')
                        ->join($inquiryItemTableName . ' b ON b.id = a.inquiry_item_id', 'LEFT')
                        ->join($finalQuoteItemTableName . ' c ON c.quote_item_id = a.id', 'LEFT')
                        ->where($where);
    }

    /**
     * @desc 根据报价单ID删除SKU记录
     *
     * @param int $quoteId
     * @return mixed
     * @author liujf
     * @time 2018-04-19
     */
    public function delByQuoteId($quoteId) {
        return $this->where(['quote_id' => $quoteId])->setField('deleted_flag', 'Y');
    }

    /**
     * @desc 根据询单ID删除SKU记录
     *
     * @param int $inquiryId
     * @return mixed
     * @author liujf
     * @time 2018-04-09
     */
    public function delQuoteByInquiryId($inquiryId) {
        $flag = $this->where(['inquiry_id' => $inquiryId])->setField('deleted_flag', 'Y');

        return $flag;
    }

}
