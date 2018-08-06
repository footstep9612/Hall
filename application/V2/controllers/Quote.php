<?php

/**
 * 报价人相关操作接口
 * @desc   QuoteController
 * @Author 买买提
 */
class QuoteController extends PublicController {

    private $quoteModel;
    private $quoteItemModel;
    private $inquiryModel;
    private $inquiryItemModel;
    private $requestParams = [];

    public function init() {

        parent::init();

        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->inquiryModel = new InquiryModel();
        $this->inquiryItemModel = new InquiryItemModel();
        $this->finalQuoteItemModel = new FinalQuoteItemModel();
        $this->historicalSkuQuoteModel = new HistoricalSkuQuoteModel();

        $this->requestParams = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 报价信息
     * @author mmt、liujf
     */
    public function infoAction() {

        $request = $this->validateRequestParams('inquiry_id');
        $condition = ['inquiry_id' => $request['inquiry_id']];
        $field = 'id,package_mode,total_weight,package_volumn,period_of_validity,payment_mode,trade_terms_bn,delivery_period,payment_period,fund_occupation_rate,bank_interest,gross_profit_rate,certification_fee,premium_rate,quote_remarks,trans_mode_bn,dispatch_place,delivery_addr,total_bank_fee,exchange_rate,total_purchase,purchase_cur_bn,from_port,to_port,from_country,to_country,logi_quote_flag,total_logi_fee,total_exw_price,total_quote_price';

        $info = $this->quoteModel->getGeneralInfo($condition, $field);

        $exchangeRateModel = new ExchangeRateModel();
        $transModeModel = new TransModeModel();
        $countryModel = new CountryModel();
        $portModel = new PortModel();
        $info['exchange_rate'] = $exchangeRateModel->where(['cur_bn2' => 'CNY', 'cur_bn1' => 'USD'])->order('created_at DESC')->getField('rate');
        $info['exchange_rate'] = $info['exchange_rate'] ?: L('NOTHING');

        $info['inquiry_trans_mode_bn'] = $this->inquiryModel->where(['id' => $request['inquiry_id']])->getField('trans_mode_bn');
        $info['inquiry_trans_mode_bn'] = $info['inquiry_trans_mode_bn'] ?: L('NOTHING');

        $logiInfo = $this->inquiryModel->where(['id' => $request['inquiry_id']])->field('dispatch_place,destination,inflow_time,org_id,status')->find();

        // 起运国
        $info['from_country_name'] = $countryModel->getCountryNameByBn($info['from_country'], $this->lang) ?: L('NOTHING');
        // 目的国
        $info['to_country_name'] = $countryModel->getCountryNameByBn($info['to_country'], $this->lang) ?: L('NOTHING');
        // 起运港
        $info['from_port_name'] = $portModel->getPortNameByBn($info['from_country'], $info['from_port'], $this->lang) ?: L('NOTHING');
        // 目的港
        $info['to_port_name'] = $portModel->getPortNameByBn($info['to_country'], $info['to_port'], $this->lang) ?: L('NOTHING');
        $info['trans_mode_bn'] = $info['trans_mode_bn'] ?: L('NOTHING');
        $info['trans_mode_name'] = $transModeModel->getTransModeByBn($info['trans_mode_bn'], $this->lang) ?: L('NOTHING');
        $info['dispatch_place'] = $info['dispatch_place'] ?: L('NOTHING');
        $info['inquiry_dispatch_place'] = $logiInfo['dispatch_place'];
        $info['inquiry_dispatch_place'] = $info['inquiry_dispatch_place'] ?: L('NOTHING');
        $info['inquiry_delivery_addr'] = $logiInfo['destination'];
        $info['inquiry_delivery_addr'] = $info['inquiry_delivery_addr'] ?: L('NOTHING');
        $info['total_bank_fee'] = $info['total_bank_fee'] ?: L('NOTHING');
        $info['total_exw_price'] = $info['total_exw_price'] ?: L('NOTHING');
        $info['inflow_time'] = $logiInfo['inflow_time'];
        $info['org_id'] = $logiInfo['org_id'];
        $info['status'] = $logiInfo['status'];

        if (empty($info['package_volumn'])) {
            $info['package_volumn'] = (new QuoteLogiQwvModel())->GetTotal($request['inquiry_id']);
        }
        $finalQuoteModel = new FinalQuoteModel();
        $finalQuote = $finalQuoteModel->where($condition)->field('total_exw_price,total_quote_price')->find();
        if ($finalQuote) {
            $info['final_total_exw_price'] = $finalQuote['total_exw_price'];
            $info['final_total_quote_price'] = $finalQuote['total_quote_price'];
        }
        $this->jsonReturn([
            'code' => 1,
            'message' => L('QUOTE_SUCCESS'),
            'data' => $info
        ]);
    }

    /**
     * 更新报价信息
     * @author mmt、liujf
     */
    public function updateInfoAction() {

        $val_request = $this->validateRequests('inquiry_id');

        $request = $this->validateNumeric($val_request);
        unset($val_request);
        $request['biz_quote_by'] = $this->user['id'];
        $request['biz_quote_at'] = date('Y-m-d H:i:s');

        if ($request['trans_mode_bn'] == L('NOTHING')) {
            unset($request['trans_mode_bn']);
        }
        if ($request['total_bank_fee'] == L('NOTHING')) {
            unset($request['total_bank_fee']);
        }
        if ($request['total_exw_price'] == L('NOTHING')) {
            unset($request['total_exw_price']);
        }
        if ($request['dispatch_place'] == L('NOTHING')) {
            unset($request['dispatch_place']);
        }


        if (isset($request['premium_rate']) && ($request['premium_rate'] >= 1 || $request['premium_rate'] < 0)) {
            $this->jsonReturn([
                'code' => -1,
                'message' => '保险税率必须小于1且大于等于零!'
            ]);
        }


        $condition = ['inquiry_id' => $request['inquiry_id']];


        unset($request['total_bank_fee']);

        //这个操作设计到计算
        $result = $this->quoteModel->updateGeneralInfo($condition, $request);


        if ($result['code']) {
            $this->jsonReturn($result);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => L('QUOTE_SUCCESS')
        ]);
    }

    /**
     * 退回分单员(事业部分单员)
     */
    public function rejectToBizAction() {

        $inquiryModel = new InquiryModel();

        $request = $this->validateRequests('inquiry_id');
        $op_note = !empty($request['op_note']) ? $request['op_note'] : '';

        $in_node = !empty($request['in_node']) ? $request['in_node'] : null;
        $condition = ['inquiry_id' => $request['inquiry_id'], 'op_note' => $op_note, 'in_node' => $in_node];
        $org_id = $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('org_id', true);
        $condition['now_agent_id'] = $inquiryModel->getInquiryIssueUserId($request['inquiry_id'], $org_id, ['in', [$inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::quoteIssueAuxiliaryRole]], ['in', [$inquiryModel::inquiryIssueRole, $inquiryModel::quoteIssueMainRole]], ['in', ['ub', 'eub', 'erui']]);
        $response = $result = $this->quoteModel->rejectToBiz($condition, $this->user);

        $this->jsonReturn($response);
    }

    /**
     * 提交物流分单员
     */
    public function sendLogisticsAction() {

        $request = $this->validateRequests('inquiry_id');
        $res = $this->quoteModel->validate($request['inquiry_id']);
        if ($res['code'] != 1) {
            $this->jsonReturn($res);
        }

        $response = $this->quoteModel->sendLogisticsHandler($request, $this->user);

        $this->jsonReturn($response);
    }

    /**
     * 退回物流报价
     */
    public function rejectLogisticAction() {

        $request = $this->validateRequests('inquiry_id');
        $inquiryModel = new InquiryModel();
        $now_agent_id = $inquiryModel->where(['id' => $request['inquiry_id']])->getField('logi_agent_id');
        $result = $inquiryModel->updateData([
            'id' => $request['inquiry_id'],
            'now_agent_id' => $now_agent_id,
            'inflow_time' => date('Y-m-d H:i:s', time()),
            'status' => 'LOGI_QUOTING',
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s', time())
        ]);
        $this->rollback($this->inquiryModel, null, $result);
        $op_note = !empty($request['op_note']) ? $request['op_note'] : '';
        $in_node = !empty($request['in_node']) ? $request['in_node'] : null;
        $this->rollback($this->inquiryModel, Rfq_CheckLogModel::addCheckLog($request['inquiry_id'], 'LOGI_QUOTING', $this->user, $in_node, 'REJECT', $op_note), null, Rfq_CheckLogModel::$mError);

        $this->jsonReturn($result);
    }

    /**
     * 提交报价审核
     */
    public function submitQuoteAuditorAction() {
        $condition = $this->put_data;
        $request = $this->validateRequests('inquiry_id');

        $inquiryModel = new InquiryModel();
        $check_org_id = $condition['check_org_id']; //$inquiryModel->getRoleUserId($this->user['group_id'],$inquiryModel::quoteCheckRole);

        $inquiryModel->updateData([
            'id' => $request['inquiry_id'],
            'now_agent_id' => $check_org_id,
            'inflow_time' => date('Y-m-d H:i:s', time()),
            'check_org_id' => $check_org_id, //事业部审核人
            'status' => 'MARKET_APPROVING',
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s', time())
        ]);

        $this->quoteModel->where(['inquiry_id' => $request['inquiry_id']])->save(['status' => 'BIZ_APPROVING']);

        $finalQuoteModel = new FinalQuoteModel();
        $quoteModel = new QuoteModel();
        $finalQuoteItemModel = new FinalQuoteItemModel();
        //验证数据
        $quoteInfo = $quoteModel->where(['inquiry_id' => $request['inquiry_id']])
                        ->field('id,payment_period,fund_occupation_rate,delivery_period,'
                                . 'total_purchase,total_logi_fee,total_bank_fee,total_exw_price,'
                                . 'total_quote_price,total_insu_fee')->find();

        //判断是否存在数据，如果是退回报价更新数据，如果不是就插入一条数据
        $final = $finalQuoteModel->field('id')->where('inquiry_id=' . $request['inquiry_id'])->find();

        if (empty($final)) {
            $finalQuoteModel->add($finalQuoteModel->create([
                        'inquiry_id' => $request['inquiry_id'],
                        'buyer_id' => $this->inquiryModel->where(['id' => $request['inquiry_id']])->getField('buyer_id'),
                        'quote_id' => $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']),
                        'payment_period' => $quoteInfo['payment_period'],
                        'fund_occupation_rate' => $quoteInfo['fund_occupation_rate'],
                        'delivery_period' => $quoteInfo['delivery_period'],
                        'total_purchase' => $quoteInfo['total_purchase'],
                        'total_logi_fee' => $quoteInfo['total_logi_fee'],
                        'total_bank_fee' => $quoteInfo['total_bank_fee'],
                        'total_exw_price' => $quoteInfo['total_exw_price'],
                        'total_quote_price' => $quoteInfo['total_quote_price'],
                        'total_insu_fee' => $quoteInfo['total_insu_fee'],
                        'created_by' => $this->user['id'],
                        'created_at' => date('Y-m-d H:i:s')
            ]));
        } else {
            $finalQuoteModel->where('inquiry_id=' . $request['inquiry_id'])->save($finalQuoteModel->create([
                        'inquiry_id' => $request['inquiry_id'],
                        'buyer_id' => $this->inquiryModel->where(['id' => $request['inquiry_id']])->getField('buyer_id'),
                        'quote_id' => $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']),
                        'payment_period' => $quoteInfo['payment_period'],
                        'fund_occupation_rate' => $quoteInfo['fund_occupation_rate'],
                        'delivery_period' => $quoteInfo['delivery_period'],
                        'total_purchase' => $quoteInfo['total_purchase'],
                        'total_logi_fee' => $quoteInfo['total_logi_fee'],
                        'total_bank_fee' => $quoteInfo['total_bank_fee'],
                        'total_exw_price' => $quoteInfo['total_exw_price'],
                        'total_quote_price' => $quoteInfo['total_quote_price'],
                        'total_insu_fee' => $quoteInfo['total_insu_fee'],
                        'created_by' => $this->user['id'],
                        'created_at' => date('Y-m-d H:i:s')
            ]));
        }

        $quoteItems = $this->quoteItemModel->where(['inquiry_id' => $request['inquiry_id'], 'deleted_flag' => 'N'])->field('id,inquiry_id,inquiry_item_id,sku,supplier_id,quote_unit_price,exw_unit_price')->select();

        $finalItems = $finalQuoteItemModel->where(['inquiry_id' => $request['inquiry_id'], 'deleted_flag' => 'N'])->getField('quote_item_id', true);
        $quote_id = $this->quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']);

        foreach ($quoteItems as $item) {
            if (!in_array($item['id'], $finalItems)) {
                $finalQuoteItemModel->add($finalQuoteItemModel->create([
                            'quote_id' => $quote_id,
                            'inquiry_id' => $request['inquiry_id'],
                            'inquiry_item_id' => $item['inquiry_item_id'],
                            'quote_item_id' => $item['id'],
                            'sku' => $item['sku'],
                            'supplier_id' => $item['supplier_id'],
                            'quote_unit_price' => $item['quote_unit_price'],
                            'exw_unit_price' => $item['exw_unit_price'],
                            'created_by' => $this->user['id'],
                            'created_at' => date('Y-m-d H:i:s'),
                ]));
            } else {
                $finalQuoteItemModel->where(['quote_item_id' => $item['id']])->save($finalQuoteItemModel->create([
                            'quote_id' => $quote_id,
                            'inquiry_id' => $request['inquiry_id'],
                            'inquiry_item_id' => $item['inquiry_item_id'],
                            'quote_item_id' => $item['id'],
                            'sku' => $item['sku'],
                            'supplier_id' => $item['supplier_id'],
                            'quote_unit_price' => $item['quote_unit_price'],
                            'exw_unit_price' => $item['exw_unit_price'],
                            'created_by' => $this->user['id'],
                            'created_at' => date('Y-m-d H:i:s'),
                ]));
            }
        }



        $this->rollback($this->inquiryModel, Rfq_CheckLogModel::addCheckLog($request['inquiry_id'], 'MARKET_APPROVING', $this->user), null, Rfq_CheckLogModel::$mError);

        $this->inquiryModel->commit();
        $this->jsonReturn(['code' => 1, 'message' => L('QUOTE_SUCCESS')]);
    }

    /**
     * 退回报价(审核人)
     */
    public function rejectAction() {

        $request = $this->validateRequests('inquiry_id');

        //更新当前办理人
        $inquiry = new InquiryModel();
        $now_agent_id = $inquiry->where(['id' => $request['inquiry_id']])->getField('quote_id');

        //是不是需要物流报价标识区分
        $quoteModel = new QuoteModel();
        $logi_quote_flag = $quoteModel->where(['inquiry_id' => $request['inquiry_id']])->getField('logi_quote_flag');

        if ($logi_quote_flag == "Y") {
            $status = "BIZ_QUOTING";
        } else {
            $status = "BIZ_APPROVING";
        }

        $response = $this->inquiryModel->updateData([
            'id' => $request['inquiry_id'],
            'now_agent_id' => $now_agent_id,
            'inflow_time' => date('Y-m-d H:i:s', time()),
            'status' => $status,
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s', time())
        ]);
        $this->rollback($this->inquiryModel, null, $response);
        $op_note = !empty($request['op_note']) ? $request['op_note'] : '';
        $in_node = !empty($request['in_node']) ? $request['in_node'] : null;
        $flag = Rfq_CheckLogModel::addCheckLog($request['inquiry_id'], $status, $this->user, $in_node, 'REJECT', $op_note);
        $this->rollback($this->inquiryModel, $flag);

        $this->jsonReturn($response);
    }

    /**
     * 确认报价(审核人)
     * @author mmt、liujf
     */
    public function confirmAction() {

        $request = $this->validateRequests('inquiry_id');

        //更新当前办理人
        $now_agent_id = $this->inquiryModel
                ->where(['id' => $request['inquiry_id']])
                ->getField('agent_id');

        $this->inquiryModel->startTrans();

        $res1 = $this->inquiryModel->updateData([
            'id' => $request['inquiry_id'],
            'now_agent_id' => $now_agent_id,
            'inflow_time' => date('Y-m-d H:i:s', time()),
            'status' => 'MARKET_CONFIRMING',
            'quote_status' => 'QUOTED',
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s', time())
        ]);

        // 记录历史报价
        $list = $this->finalQuoteItemModel
                ->field('quote_id, inquiry_id, inquiry_item_id, quote_item_id')
                ->where(['inquiry_id' => $request['inquiry_id'], 'deleted_flag' => 'N'])
                ->select();
        foreach ($list as &$item) {
            $item['created_by'] = $this->user['id'];
            $item['created_at'] = date('Y-m-d H:i:s');
        }
        $res2 = $this->historicalSkuQuoteModel->addAll($list);

        if ($res1 && $res2) {
            $this->inquiryModel->commit();
            $this->setCode('1');
            $this->setMessage(L('SUCCESS'));
            $this->jsonReturn(true);
        } else {
            $this->inquiryModel->rollback();
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
    }

    /**
     * 确认报价(审核人)
     * @author mmt、liujf
     */
    public function quotetosubmitAction() {

        error_reporting(E_ALL);
        $request = $this->validateRequests('inquiry_id');

        $condition = ['inquiry_id' => $request['inquiry_id']];
        unset($request['total_bank_fee']);
        //这个操作设计到计算
        $inquiryModel = new Rfq_InquiryModel();
        $quoteModel = new Rfq_QuoteModel();
        $quote = $quoteModel->Detail($request['inquiry_id']);
        if (empty($quote)) {
            $this->jsonReturn([
                'code' => -1,
                'message' => '报价不存在!'
            ]);
        } elseif (isset($request['premium_rate']) && ($request['premium_rate'] >= 1 || $request['premium_rate'] < 0)) {
            $this->jsonReturn([
                'code' => -1,
                'message' => '保险税率必须小于1且大于等于零!'
            ]);
        }
        $this->inquiryModel->startTrans();
        $inquiry_id = $request['inquiry_id'];

        $result = $quoteModel->updateGeneralInfo($condition, $request);
        $this->rollback($this->inquiryModel, null, $result);

        $res1 = $inquiryModel->submit($inquiry_id);
        $this->rollback($this->inquiryModel, null, $res1);


        $flag1 = $quoteModel->where(['inquiry_id' => $inquiry_id])->save(['status' => 'BIZ_APPROVING']);
        $this->rollback($this->inquiryModel, $flag1);


        $quote_logi_fee_Model = new Rfq_QuoteLogiFeeModel();
        $flag2 = $quote_logi_fee_Model->submit($inquiry_id);
        $this->rollback($this->inquiryModel, $flag2);

        $FinalQuoteModel = new Rfq_FinalQuoteModel();
        $res2 = $FinalQuoteModel->submit($inquiry_id);
        $this->rollback($this->inquiryModel, $res2);

        $falg = Rfq_CheckLogModel::AddQuoteToSubmitLog($inquiry_id, $this->user);
        $this->rollback($this->inquiryModel, $falg, null, Rfq_CheckLogModel::$mError);

        $this->inquiryModel->commit();
        $this->setCode('1');
        $this->setMessage(L('SUCCESS'));
        $this->jsonReturn(true);
    }

    /**
     * @desc 事业部审核退回事业部报价
     *
     * @author liujf
     * @time 2018-05-29
     */
    public function rejectQuotingAction() {
        $request = $this->validateRequests('inquiry_id');
        $quoteId = $this->inquiryModel->where(['id' => $request['inquiry_id']])->getField('quote_id');
        $data = [
            'id' => $request['inquiry_id'],
            'now_agent_id' => $quoteId,
            'status' => 'REJECT_QUOTING',
            'updated_by' => $this->user['id']
        ];
        $res = $this->inquiryModel->updateData($data);

        $op_note = !empty($request['op_note']) ? $request['op_note'] : '';
        $in_node = !empty($request['in_node']) ? $request['in_node'] : null;
        $flag = Rfq_CheckLogModel::addCheckLog($request['inquiry_id'], 'REJECT_QUOTING', $this->user, $in_node, 'REJECT', $op_note);
        $this->rollback($this->inquiryModel, $flag);


        $this->inquiryModel->commit();
        $this->jsonReturn($res);
    }

    /**
     * SKU列表
     * @author mmt、liujf
     */
    public function skuAction() {

        $request = $this->validateRequests('inquiry_id');
        $list = $this->quoteItemModel->getList($request);
        $inquiry_model = new InquiryModel();
        $org_id = $inquiry_model->where(['id' => $request['inquiry_id'], 'deleted_flag' => 'N'])->getField('org_id');

        $is_erui = (new OrgModel())->getIsEruiById($org_id);


        empty($list) ? $this->jsonReturn(['code' => -104,
                            'message' => L('QUOTE_NO_DATA'),
                            'is_erui' => $is_erui,
                            'org_id' => $org_id,
                        ]) : null;



        ( new SupplierModel())->setSupplier($list);
        $this->_setOrgName($list);
        $this->_setMaterialCatName($list);


        foreach ($list as $key => $value) {
            $list[$key]['purchase_unit_price'] = sprintf("%.4f", $list[$key]['purchase_unit_price']);
            // 参考历史报价数量
            $condition = [
                'sku' => $value['sku'],
                'pn' => $value['pn'],
                'name' => $value['name'],
                'name_zh' => $value['name_zh'],
                'brand' => $value['brand'],
                'model' => $value['model']
            ];
            $list[$key]['historical_quote_count'] = $this->historicalSkuQuoteModel->getCount($condition);
        }
        $total_purchase_price = $this->quoteItemModel->getTotalPurchasePrice($request);
        $this->jsonReturn([
            'code' => 1,
            'is_erui' => $is_erui,
            'org_id' => $org_id,
            'message' => L('QUOTE_SUCCESS'),
            'count' => $this->quoteItemModel->getCount($request),
            'total_purchase_price' => $total_purchase_price,
            'data' => $list
        ]);
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
                if (isset($val['org_id']) && $val['org_id']) {
                    $org_ids[] = $val['org_id'];
                }
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
                if ($val['org_id'] && isset($orgnames[$val['org_id']])) {
                    $val['org_name'] = $orgnames[$val['org_id']];
                } else {
                    $val['org_name'] = '';
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

    private function _setMaterialCatName(&$arr) {
        if ($arr) {
            $material_cat_model = new MaterialCatModel();
            $material_cat_nos = [];
            foreach ($arr as $key => $val) {
                if (isset($val['material_cat_no']) && $val['material_cat_no']) {
                    $material_cat_nos[] = $val['material_cat_no'];
                }
            }
            $material_cat_names = [];
            if ($material_cat_nos) {
                $material_cats = $material_cat_model->where(['cat_no' => ['in', $material_cat_nos],
                            'deleted_flag' => 'N',
                            'lang' => $this->lang
                        ])
                        ->field('cat_no,name')
                        ->select();
                foreach ($material_cats as $material_cat) {
                    $material_cat_names[$material_cat['cat_no']] = $material_cat['name'];
                }
            }
            foreach ($arr as $key => $val) {
                if (!empty($val['material_cat_no']) && isset($material_cat_names[$val['material_cat_no']])) {
                    $val['material_cat_name'] = $material_cat_names[$val['material_cat_no']];
                } else {
                    $val['material_cat_name'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /**
     * 保存SKU信息，加校验
     */
    public function updateSkuAction() {

        $request = $this->validateRequests();
        //验证必填项是否填写
        $checkitem = $this->checkSkuFieldsAction($request['data']);
        if ($checkitem['code'] == 1) {
            $response = $this->quoteItemModel->updateItem($request['data'], $this->user['id']);
            $this->jsonReturn($response);
        } else {
            $this->jsonReturn($checkitem);
        }
    }

    /**
     * 批量保存SKU信息，不加校验
     */
    public function updateSkuBatchAction() {
        $request = $this->validateRequests();
        $org_id = (new InquiryModel())->where(['id' => $request['inquiry_id'], 'deleted_flag' => 'N'])->getField('org_id');
        $is_erui = (new OrgModel())->getIsEruiById($org_id);
        $response = $this->quoteItemModel->updateItemBatch($request['data'], $this->user['id'], $request['currentPage'], $request['pageSize'], $is_erui);
        $this->jsonReturn($response);
    }

    public function updateSupplierAction() {

        $request = $this->validateRequests();
        $return = $this->quoteItemModel->updateSupplier($request['data']);
        $this->jsonReturn($return);
    }

    /**
     * 报价审核人sku列表
     * @author mmt、liujf
     */
    public function finalSkuAction() {

        $request = $this->validateRequests('inquiry_id');
        $list = $this->quoteItemModel->getQuoteFinalSku($request);
        $org_id = (new InquiryModel())->where(['id' => $request['inquiry_id'], 'deleted_flag' => 'N'])->getField('org_id');
        $is_erui = (new OrgModel())->getIsEruiById($org_id);

        if (!$list) {
            $this->jsonReturn(['code' => '-104',
                'message' => L('QUOTE_NO_DATA'),
                'is_erui' => $is_erui,
                'org_id' => $org_id,]);
        }


        $this->_setOrgName($list);
        $this->_setMaterialCatName($list);
        foreach ($list as $key => $value) {
            $value['exw_unit_price'] = sprintf("%.4f", $value['exw_unit_price']);
            $value['quote_unit_price'] = sprintf("%.4f", $value['quote_unit_price']);
            $value['final_exw_unit_price'] = sprintf("%.4f", $value['final_exw_unit_price']);
            $value['final_quote_unit_price'] = sprintf("%.4f", $value['final_quote_unit_price']);
            $list[$key] = $value;
        }

        $this->jsonReturn([
            'code' => 1,
            'is_erui' => $is_erui,
            'org_id' => $org_id,
            'message' => L('QUOTE_SUCCESS'),
            'count' => $this->quoteItemModel->getFinalCount($request),
            'data' => $list
        ]);
    }

    public function updateFinalSkuAction() {

        $request = $this->validateRequests();

        $finalQuoteItemModel = new FinalQuoteItemModel();
        $finalQuoteItemModel->updateFinalSku($request['data']);
        $this->jsonReturn();
    }

    /**
     * 删除SKU
     */
    public function delItemAction() {

        $request = $this->validateRequests('id');
        $quoteItemIds = $request['id'];

        $response = $this->quoteItemModel->delItem($quoteItemIds);
        $this->jsonReturn($response);
    }

    /**
     * 删除所有相关的SKU
     */
    public function delItemAllAction() {
        $quoteItemLogiModel = new QuoteItemLogiModel();
        $finalQuoteItemModel = new FinalQuoteItemModel();

        $request = $this->validateRequests('id');
        $inquiryItemIds = explode(',', $request['id']) ?: ['-1'];

        //先删除询单SKU，在删除报价单SKU，最后删除物流和市场报价单SKU
        $this->inquiryItemModel->startTrans();
        $results = $this->inquiryItemModel->deleteData($request);    //删除询单SKU
        if ($results['code'] == 1) {
            //判断报价单SKU表是否存在数据，有就删除
            $quoteItemIds = $this->quoteItemModel->where(['inquiry_item_id' => ['in', $inquiryItemIds], 'deleted_flag' => 'N'])->getField('id', true);
            if ($quoteItemIds) {
                $resquote = $this->quoteItemModel->delItem($request['id']);    //删除报价单SKU
                if ($resquote) {
                    //判断物流SKU表是否存在数据，有就删除
                    $logiItemIds = $quoteItemLogiModel
                                    ->where(['quote_item_id' => ['in', $quoteItemIds], 'deleted_flag' => 'N'])->getField('id', true);
                    if ($logiItemIds) {
                        $logiItemId['r_id'] = implode(',', $logiItemIds);
                        $reslogi = $quoteItemLogiModel->delRecord($logiItemId);
                        $this->rollback($this->inquiryItemModel, $reslogi, null);
                    }

                    //判断物流SKU表是否存在数据，有就删除
                    $finalItemIds = $finalQuoteItemModel->where(['quote_item_id' => ['in', $quoteItemIds], 'deleted_flag' => 'N'])->getField('id', true);
                    if ($finalItemIds) {
                        $finalItemId['id'] = implode(',', $finalItemIds);
                        $resfinal = $finalQuoteItemModel->delItem($finalItemId);
                        $this->rollback($this->inquiryItemModel, null, $resfinal);
                    }
                    $this->inquiryItemModel->commit();
                    $this->jsonReturn($results);
                } else {
                    $this->inquiryItemModel->rollback();
                    $results['code'] = '-101';
                    $results['message'] = L('QUOTE_DELETE_FAIL');
                    $this->jsonReturn($results);
                }
            } else {
                $this->inquiryItemModel->commit();
                $this->jsonReturn($results);
            }
        } else {
            $this->inquiryItemModel->rollback();
            $this->jsonReturn($results);
        }
    }

    /**
     * @desc 删除指定报价单的所有SKU
     *
     * @author liujf
     * @time 2018-04-19
     */
    public function delQuoteItemAction() {
        $request = $this->validateRequests('quote_id');
        $inquiryId = $this->quoteModel->where(['id' => $request['quote_id'], 'deleted_flag' => 'N'])->getField('inquiry_id');
        $this->inquiryItemModel->startTrans();
        $res1 = $this->inquiryItemModel->delByInquiryId($inquiryId);
        $res2 = $this->quoteItemModel->delByQuoteId($request['quote_id']);
        if ($res1 !== false && $res2 !== false) {
            $this->inquiryItemModel->commit();
            $res = true;
        } else {
            $this->inquiryItemModel->rollback();
            $res = false;
        }
        if ($res) {
            $this->setCode('1');
            $this->setMessage(L('SUCCESS'));
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
    }

    /**
     * 同步询单和报价SKU
     */
    public function syncSkuAction() {

        $request = $this->validateRequests('inquiry_id');
        $response = $this->quoteItemModel->syncSku($request, $this->user['id']);
        $this->jsonReturn($response);
    }

    private function changeInquiryStatus($id, $status) {

        return $this->inquiryModel->updateStatus([
                    'id' => $id,
                    'status' => $status,
                    'updated_by' => $this->user['id']
        ]);
    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params = '') {

        $request = $this->requestParams;
        unset($request['token']);

        //判断筛选字段为空的情况
        if ($params) {
            $params = explode(',', $params);
            foreach ($params as $param) {
                if (empty($request[$param])) {
                    $this->jsonReturn(['code' => '-104', 'message' => L('MISSING_PARAMETER')]);
                }
            }
        }

        return $request;
    }

    /**
     * 验证必填和数字属性的字段
     * @param $request
     * @return mixed
     */
    private function validateNumeric($request) {

        //总重
        if (!empty($request['total_weight']) && !is_numeric($request['total_weight'])) {
            $this->jsonReturn(['code' => '-104', 'message' => L('QUOTE_TOTAL_WEIGHT_NUMBER')]);
        }
        //包装总体积
        if (!empty($request['package_volumn']) && !is_numeric($request['package_volumn'])) {
            $this->jsonReturn(['code' => '-104', 'message' => L('QUOTE_PACKAGE_VOLUMN_NUMBER')]);
        }
        //回款周期
        if (!empty($request['payment_period']) && !is_numeric($request['payment_period'])) {
            $this->jsonReturn(['code' => '-104', 'message' => L('QUOTE_PAYMENT_PRIOD_NUMBER')]);
        }
        //交货周期
        if (!empty($request['delivery_period']) && !is_numeric($request['delivery_period'])) {
            $this->jsonReturn(['code' => '-104', 'message' => L('QUOTE_DELIVERY_PRIOD_NUMBER')]);
        }
        //资金占用比例
        if (!empty($request['fund_occupation_rate']) && !is_numeric($request['fund_occupation_rate'])) {
            $this->jsonReturn(['code' => '-104', 'message' => L('QUOTE_FUND_OCCUPATION_RATE_NUMBER')]);
        }
        //银行利息
        if (!empty($request['bank_interest']) && !is_numeric($request['bank_interest'])) {
            $this->jsonReturn(['code' => '-104', 'message' => L('QUOTE_BANK_INTEREST_NUMBER')]);
        }
        //毛利率
        if (!empty($request['gross_profit_rate']) && !is_numeric($request['gross_profit_rate'])) {
            $this->jsonReturn(['code' => '-104', 'message' => L('QUOTE_GROSS_PROFIT_RATE_NUMBER')]);
        }
        return $request;
    }

    /**
     * 验证报价单SKU必填和数字字段
     * @param array $data
     *
     * @return array
     */
    public function checkSkuFieldsAction($data = []) {

        foreach ($data as $value) {
            if (empty($value['reason_for_no_quote'])) {
                //供应商活着未报价分析
//                if (empty($value['supplier_id'])) {
//                    return ['code' => '-104', 'message' => L('QUOTE_SUPPLIER_REQUIRED')];
//                }
                //品牌
                if (empty($value['brand'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_BRAND_REQUIRED')];
                }
                //采购单价
                if (empty($value['purchase_unit_price'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_PUP_REQUIRED')];
                }
                if (!is_numeric($value['purchase_unit_price'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_PUP_NUMBER')];
                } else if ($value['purchase_unit_price'] <= 0) {
                    return ['code' => '-104', 'message' => L('QUOTE_PUP_NOT_ZERO')];
                }
                //采购币种
                if (empty($value['purchase_price_cur_bn'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_PPC_REQUIRED')];
                }
                //毛重
//                if (empty($value['gross_weight_kg'])) {
//                    return ['code' => '-104', 'message' => L('QUOTE_GW_REQUIRED')];
//                }
                if (!empty($value['gross_weight_kg']) && !is_numeric($value['gross_weight_kg'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_GW_NUMBER')];
                }
                //包装体积
//                if (empty($value['package_size'])) {
//                    return ['code' => '-104', 'message' => L('QUOTE_PS_REQUIRED')];
//                }
                if (!empty($value['package_size']) && !is_numeric($value['package_size'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_PS_NUMBER')];
                }
                //包装方式
                if (empty($value['package_mode'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_PM_REQUIRED')];
                }
                //产品来源
                if (empty($value['goods_source'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_GS_REQUIRED')];
                }
                //存放地
                if (empty($value['stock_loc'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_SL_REQUIRED')];
                }
                //交货期(天)，报价有效期
                if (empty($value['delivery_days'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_DD_REQUIRED')];
                }
                if (!is_numeric($value['delivery_days'])) {
                    return ['code' => '-104', 'message' => L('QUOTE_DD_NUMBER')];
                }
                //报价有效期
//                if (empty($value['period_of_validity'])) {
//                    return ['code' => '-104', 'message' => L('QUOTE_POF_REQUIRED')];
//                }
            }
        }

        return ['code' => 1, 'message' => L('QUOTE_VALIDATION')];
    }

    private function rollback(&$inquiry, $flag, $results = null, $error = null) {
        if (!empty($results) && isset($results['code']) && $results['code'] != 1) {
            $inquiry->rollback();
            $this->jsonReturn($results);
        } elseif ($results === false) {
            $inquiry->rollback();
            $this->setCode('-101');
            $this->setMessage(L('FAIL') . $error);
            $this->jsonReturn();
        } elseif ($flag === false) {
            $inquiry->rollback();
            $this->setCode('-101');
            $this->setMessage(L('FAIL') . $error);
            $this->jsonReturn();
        }
    }

}
