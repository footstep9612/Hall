<?php

/*
 * @desc 物流报价控制器
 *
 * @author liujf
 * @time 2017-08-02
 */

class LogisticsController extends PublicController {

    public function init() {
        parent::init();
        $this->put_data = dataTrim($this->put_data);

        $this->inquiryModel = new InquiryModel();
        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->quoteLogiFeeModel = new QuoteLogiFeeModel();
        $this->quoteItemLogiModel = new QuoteItemLogiModel();
        //$this->userModel = new UserModel();
        $this->inquiryCheckLogModel = new InquiryCheckLogModel();
        $this->quoteLogiQwvModel = new QuoteLogiQwvModel();
        $this->marketAreaTeamModel = new MarketAreaTeamModel();
        $this->orgMemberModel = new OrgMemberModel();
        $this->historicalSkuQuoteModel = new HistoricalSkuQuoteModel();
        $this->quoteLogiCostModel = new QuoteLogiCostModel();
        $this->transModeModel = new TransModeModel();
        $this->countryModel = new CountryModel();
        $this->portModel = new PortModel();
        $this->boxTypeModel = new BoxTypeModel();

        $this->time = date('Y-m-d H:i:s');
    }

    /**
     * @desc 获取报价单项物流报价列表接口
     *
     * @author liujf
     * @time 2017-08-02
     */
    public function getQuoteItemLogiListAction() {
        $condition = $this->put_data;

        if (empty($condition['inquiry_id'])) {
            $this->jsonReturn(false);
        }
        $data = dataTrim($this->quoteItemLogiModel->getJoinList($condition));

        foreach ($data as &$item) {
            $skuInfo = $this->historicalSkuQuoteModel->getLogiSkuQuote($item['from_country'], $item['sku']);
            $item['tax_no'] = $item['tax_no'] ?: $skuInfo['tax_no'];
            $item['rebate_rate'] = $item['rebate_rate'] ?: $skuInfo['rebate_rate'];
            $item['export_tariff_rate'] = $item['export_tariff_rate'] ?: $skuInfo['export_tariff_rate'];
        }

        $this->_handleList($this->quoteItemLogiModel, $data, $condition, true);
    }

    /**
     * @desc 获取报价单项物流报价接口
     *
     * @author liujf
     * @time 2017-08-02
     */
    public function getQuoteItemLogiDetailAction() {
        $condition = $this->put_data;

        if (!empty($condition['r_id'])) {
            $condition['id'] = $condition['r_id'];
            unset($condition['r_id']);
            $res = $this->quoteItemLogiModel->getJoinDetail($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 修改报价单项物流报价信息接口
     *
     * @author liujf
     * @time 2017-08-08
     */
    public function updateQuoteItemLogiInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['items'])) {
            $this->quoteItemLogiModel->startTrans();

            foreach ($condition['items'] as $item) {
                $where['id'] = $item['id'];
                $itemData['tax_no'] = $item['tax_no'];
                $itemData['rebate_rate'] = isDecimal($item['rebate_rate']) ? $item['rebate_rate'] : null;
                $itemData['export_tariff_rate'] = isDecimal($item['export_tariff_rate']) ? $item['export_tariff_rate'] : null;
                $itemData['supervised_criteria'] = $item['supervised_criteria'];
                $itemData['updated_by'] = $this->user['id'];
                $itemData['updated_at'] = $this->time;

                $res = $this->quoteItemLogiModel->updateInfo($where, $itemData);

                if (!$res) {
                    $this->quoteItemLogiModel->rollback();
                    $this->jsonReturn($res);
                }
            }
            $this->quoteItemLogiModel->commit();
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取报价单物流费用详情接口
     *
     * @author liujf
     * @time 2017-08-03
     */
    public function getQuoteLogiFeeDetailAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_id']) || !empty($condition['inquiry_id'])) {

            $quoteLogiFee = $this->quoteLogiFeeModel->getJoinDetail($condition);

            if ($quoteLogiFee) {
                $quoteLogiFee['land_freight_usd'] = round($quoteLogiFee['land_freight'] * $this->_getRateUSD($quoteLogiFee['land_freight_cur']), 8);
                $quoteLogiFee['port_surcharge_usd'] = round($quoteLogiFee['port_surcharge'] * $this->_getRateUSD($quoteLogiFee['port_surcharge_cur']), 8);
                $quoteLogiFee['inspection_fee_usd'] = round($quoteLogiFee['inspection_fee'] * $this->_getRateUSD($quoteLogiFee['inspection_fee_cur']), 8);
                $quoteLogiFee['inter_shipping_usd'] = round($quoteLogiFee['inter_shipping'] * $this->_getRateUSD($quoteLogiFee['inter_shipping_cur']), 8);

                $quoteLogiFee['dest_delivery_fee_usd'] = round($quoteLogiFee['dest_delivery_fee'] * $this->_getRateUSD($quoteLogiFee['dest_delivery_fee_cur']), 8);
                $quoteLogiFee['dest_clearance_fee_usd'] = round($quoteLogiFee['dest_clearance_fee'] * $this->_getRateUSD($quoteLogiFee['dest_clearance_fee_cur']), 8);

                $overlandInsuFee = $this->_getOverlandInsuFee($quoteLogiFee['total_exw_price'], $quoteLogiFee['overland_insu_rate']);
                $quoteLogiFee['overland_insu'] = $overlandInsuFee['CNY'];
                $shippingInsuFee = $this->_getShippingInsuFee($quoteLogiFee['total_exw_price'], $quoteLogiFee['shipping_insu_rate']);
                $quoteLogiFee['shipping_insu'] = $shippingInsuFee['CNY'];

                $tmpTotalFee = $quoteLogiFee['total_exw_price'] + $quoteLogiFee['land_freight_usd'] + $overlandInsuFee['USD'] + $quoteLogiFee['port_surcharge_usd'] + $quoteLogiFee['inspection_fee_usd'] + $quoteLogiFee['inter_shipping_usd'];

                $quoteLogiFee['dest_tariff_fee'] = round($tmpTotalFee * $quoteLogiFee['dest_tariff_rate'] / 100, 8);
                $quoteLogiFee['dest_va_tax_fee'] = round($tmpTotalFee * (1 + $quoteLogiFee['dest_tariff_rate'] / 100) * $quoteLogiFee['dest_va_tax_rate'] / 100, 8);

                $quoteLogiFee['trans_mode_bn'] = $quoteLogiFee['trans_mode_bn'] ?: L('NOTHING');
                $quoteLogiFee['trans_mode_name'] = $this->transModeModel->getTransModeByBn($quoteLogiFee['trans_mode_bn'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['package_mode'] = $quoteLogiFee['package_mode'] ?: L('NOTHING');
                $quoteLogiFee['dispatch_place'] = $quoteLogiFee['dispatch_place'] ?: L('NOTHING');
                $quoteLogiFee['from_country'] = $quoteLogiFee['from_country'] ?: L('NOTHING');
                $quoteLogiFee['from_country_name'] = $this->countryModel->getCountryNameByBn($quoteLogiFee['from_country'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['to_country'] = $quoteLogiFee['to_country'] ?: L('NOTHING');
                $quoteLogiFee['to_country_name'] = $this->countryModel->getCountryNameByBn($quoteLogiFee['to_country'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['from_port'] = $quoteLogiFee['from_port'] ?: L('NOTHING');
                $quoteLogiFee['from_port_name'] = $this->portModel->getPortNameByBn($quoteLogiFee['from_country'], $quoteLogiFee['from_port'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['to_port'] = $quoteLogiFee['to_port'] ?: L('NOTHING');
                $quoteLogiFee['to_port_name'] = $this->portModel->getPortNameByBn($quoteLogiFee['to_country'], $quoteLogiFee['to_port'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['delivery_addr'] = $quoteLogiFee['delivery_addr'] ?: L('NOTHING');
                $quoteLogiFee['logi_trans_mode_bn'] = $quoteLogiFee['logi_trans_mode_bn'] ?: L('NOTHING');
                $quoteLogiFee['logi_trans_mode_name'] = $this->transModeModel->getTransModeByBn($quoteLogiFee['logi_trans_mode_bn'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['logi_from_port'] = $quoteLogiFee['logi_from_port'] ?: L('NOTHING');
                $quoteLogiFee['logi_from_port_name'] = $this->portModel->getPortNameByBn($quoteLogiFee['from_country'], $quoteLogiFee['logi_from_port'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['logi_to_port'] = $quoteLogiFee['logi_to_port'] ?: L('NOTHING');
                $quoteLogiFee['logi_to_port_name'] = $this->portModel->getPortNameByBn($quoteLogiFee['to_country'], $quoteLogiFee['logi_to_port'], $this->lang) ?: L('NOTHING');
                $quoteLogiFee['logi_box_type_bn'] = $quoteLogiFee['logi_box_type_bn'] ?: L('NOTHING');
                $quoteLogiFee['logi_box_type_name'] = $this->boxTypeModel->getBoxTypeNameByBn($quoteLogiFee['logi_box_type_bn'], $this->lang) ?: L('NOTHING');

                // 港杂费和国际运费
                $logiCostList = $this->quoteLogiCostModel->getList(['inquiry_id' => $quoteLogiFee['inquiry_id']], 'unit, qty, price, cur_bn, type');
                $quoteLogiFee['port_surcharge_items'] = $quoteLogiFee['inter_shipping_items'] = [];
                foreach ($logiCostList as $logiCost) {
                    $costType = $logiCost['type'];
                    $logiCost['show'] = false;
                    unset($logiCost['type']);
                    if ($costType == 'port_surcharge') {
                        $quoteLogiFee['port_surcharge_items'][] = $logiCost;
                    } elseif ($costType == 'inter_shipping') {
                        $quoteLogiFee['inter_shipping_items'][] = $logiCost;
                    }
                }
            }
            $this->jsonReturn($quoteLogiFee);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 修改报价单物流费用信息接口
     *
     * @author liujf
     * @time 2017-08-10
     */
    public function updateQuoteLogiFeeInfoAction() {
        $condition = $this->put_data;

        if (empty($condition['inquiry_id'])) {
            $this->jsonReturn(false);
        }
        $data = $condition;
        unset($data['from_port']);
        unset($data['to_port']);
        unset($data['trans_mode_bn']);
        unset($data['box_type_bn']);
        unset($data['quote_remarks']);
        $where['inquiry_id'] = $condition['inquiry_id'];
        $quote = $this->quoteModel->where($where)->find();
        $data['premium_rate'] = $quote['premium_rate'];
        $data['trade_terms_bn'] = $quote['trade_terms_bn'];
        $data['payment_period'] = $quote['payment_period'];
        $data['fund_occupation_rate'] = $quote['fund_occupation_rate'];
        $data['bank_interest'] = $quote['bank_interest'];
        $data['total_exw_price'] = $quote['total_exw_price'];
        $data['certification_fee'] = $quote['certification_fee'];
        $data['certification_fee_cur'] = $quote['certification_fee_cur'];
        $data['port_surcharge_cur'] = $data['inter_shipping_cur'] = 'USD';

        //计算并保存港杂费和国际运费数据
        if (!in_array($data['trade_terms_bn'], ['EXW', 'FCA', 'FAS'])) {
            if (empty($data['port_surcharge_items'])) {
                jsonReturn('', -101, L('MISSING_PARAMETER_PORT_SURCHARGE_ITEMS'));
            } else {
                $portSurchargeList = $interShippingList = [];
                $data['port_surcharge'] = $data['inter_shipping'] = 0;
                foreach ($data['port_surcharge_items'] as $portSurchargeItem) {

                    $portSurchargeItem['unit'] == '' ? jsonReturn('', -101, L('MISSING_PARAMETER_PORT_SURCHARGE_UNIT')) : null;

                    !isDecimal($portSurchargeItem['qty']) ? jsonReturn('', -101, L('MISSING_PARAMETER_PORT_SURCHARGE_QTY')) : null;

                    !isDecimal($portSurchargeItem['price']) ? jsonReturn('', -101, L('MISSING_PARAMETER_PORT_SURCHARGE_PRICE')) : null;

                    $portSurchargeItem['cur_bn'] == '' ? jsonReturn('', -101, L('MISSING_PARAMETER_PORT_SURCHARGE_CUR')) : null;
                    $portSurchargeData = [];
                    $portSurchargeData['unit'] = $portSurchargeItem['unit'];
                    $portSurchargeData['qty'] = $portSurchargeItem['qty'];
                    $portSurchargeData['price'] = $portSurchargeItem['price'];
                    $portSurchargeData['cur_bn'] = $portSurchargeItem['cur_bn'];
                    $portSurchargeData['inquiry_id'] = $quote['inquiry_id'];
                    $portSurchargeData['quote_id'] = $quote['id'];
                    $portSurchargeData['type'] = 'port_surcharge';
                    $portSurchargeData['created_by'] = $this->user['id'];
                    $portSurchargeData['created_at'] = $this->time;
                    $portSurchargeList[] = $portSurchargeData;
                    $data['port_surcharge'] += round($portSurchargeItem['price'] * $portSurchargeItem['qty'] * $this->_getRateUSD($portSurchargeItem['cur_bn']), 8);
                }
                if ($data['trade_terms_bn'] != 'FOB') {
                    if (empty($data['inter_shipping_items'])) {
                        jsonReturn('', -101, L('MISSING_PARAMETER_INTER_SHIPPING_ITEMS'));
                    }
                    foreach ($data['inter_shipping_items'] as $interShippingItem) {

                        $interShippingItem['unit'] == '' ? jsonReturn('', -101, L('MISSING_PARAMETER_INTER_SHIPPING_UNIT')) : null;
                        !isDecimal($interShippingItem['qty']) ? jsonReturn('', -101, L('MISSING_PARAMETER_INTER_SHIPPING_QTY')) : null;
                        !isDecimal($interShippingItem['price']) ? jsonReturn('', -101, L('MISSING_PARAMETER_INTER_SHIPPING_PRICE')) : null;
                        $interShippingItem['cur_bn'] == '' ? jsonReturn('', -101, L('MISSING_PARAMETER_INTER_SHIPPING_CUR')) : null;
                        $interShippingData = [];
                        $interShippingData['unit'] = $interShippingItem['unit'];
                        $interShippingData['qty'] = $interShippingItem['qty'];
                        $interShippingData['price'] = $interShippingItem['price'];
                        $interShippingData['cur_bn'] = $interShippingItem['cur_bn'];

                        $interShippingData['inquiry_id'] = $quote['inquiry_id'];
                        $interShippingData['quote_id'] = $quote['id'];
                        $interShippingData['type'] = 'inter_shipping';
                        $interShippingData['created_by'] = $this->user['id'];
                        $interShippingData['created_at'] = $this->time;
                        $interShippingList[] = $interShippingData;
                        $data['inter_shipping'] += round($interShippingItem['price'] * $interShippingItem['qty'] * $this->_getRateUSD($interShippingItem['cur_bn']), 8);
                    }
                }
                $logiCostList = array_merge($portSurchargeList, $interShippingList);
            }
        }

        $res_data = $this->calcuTotalLogiFee($data);

        // 去掉暂无的数据
        $res_data['logi_from_port'] = $res_data['logi_from_port'] == L('NOTHING') ? null : $res_data['logi_from_port'];
        $res_data['logi_to_port'] = $res_data['logi_to_port'] == L('NOTHING') ? null : $res_data['logi_to_port'];
        $res_data['logi_trans_mode_bn'] = $res_data['logi_trans_mode_bn'] == L('NOTHING') ? null : $res_data['logi_trans_mode_bn'];
        $res_data['logi_box_type_bn'] = $res_data['logi_box_type_bn'] == L('NOTHING') ? null : $res_data['logi_box_type_bn'];

        $res_data['updated_by'] = $this->user['id'];
        $res_data['updated_at'] = $this->time;

        $this->quoteLogiFeeModel->startTrans();
        $res1 = $this->quoteLogiFeeModel->updateInfo($where, $res_data);

        $quoteData['quote_remarks'] = $condition['quote_remarks'];
        $quoteData['total_logi_fee'] = $res_data['total_logi_fee'];
        $quoteData['total_quote_price'] = $res_data['total_quote_price'];
        $quoteData['total_bank_fee'] = $res_data['total_bank_fee'];
        $quoteData['total_insu_fee'] = $res_data['total_insu_fee'];
        $quoteData['updated_by'] = $this->user['id'];
        $quoteData['updated_at'] = $this->time;
        $res2 = $this->quoteModel->where($where)->save($quoteData);

        $res3 = $this->_updateQuoteUnitPrice($condition['inquiry_id'], $res_data);

        $res4 = true;
        if (isset($logiCostList)) {
            $this->quoteLogiCostModel->delRecord($where);
            $res4 = $this->quoteLogiCostModel->addAll($logiCostList);
        }

        if ($res1 && $res2 && $res3 && $res4) {
            $this->quoteLogiFeeModel->commit();
            $res = true;
        } else {
            $this->quoteLogiFeeModel->rollback();
            $res = false;
        }

        $this->jsonReturn($res);
    }

    /**
     * @desc 获取物流费用历史价格列表接口
     *
     * @author liujf
     * @time 2018-04-25
     */
    public function getQuoteLogiCostHistoricalPriceListAction() {
        $condition = $this->put_data;
        $quote = $this->quoteModel->field('from_country, trade_terms_bn, trans_mode_bn')->where(['inquiry_id' => $condition['inquiry_id']])->find();
        $condition['from_country'] = $quote['from_country'];
        $condition['trade_terms_bn'] = $quote['trade_terms_bn'];
        $historicalPriceList = $this->quoteLogiCostModel->getHistoricalPriceList($condition);
        $this->jsonReturn($historicalPriceList);
    }

    /**
     * @desc 获取物流报价件重尺列表接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function getQuoteLogiQwvListAction() {
        $condition = $this->put_data;

        if (empty($condition['inquiry_id'])) {
            $this->jsonReturn(false);
        }

        $data = $this->quoteLogiQwvModel->getList($condition);

        $this->_handleList($this->quoteLogiQwvModel, $data, $condition);
    }

    /**
     * @desc 新增物流报价件重尺记录接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function addQuoteLogiQwvRecordAction() {
        $condition = $this->put_data;

        if (empty($condition['inquiry_id'])) {
            $this->jsonReturn(false);
        }

        $length = isDecimal($condition['length']) ? $condition['length'] : null;
        $width = isDecimal($condition['width']) ? $condition['width'] : null;
        $height = isDecimal($condition['height']) ? $condition['height'] : null;
        $volumn = $length * $width * $height;

        $qwvData = [
            'inquiry_id' => $condition['inquiry_id'],
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'volumn' => $volumn > 0 ? $volumn : 0,
            'gross_weight' => isDecimal($condition['gross_weight']) ? $condition['gross_weight'] : null,
            'quantity' => isDecimal($condition['quantity']) ? $condition['quantity'] : null,
            'created_by' => $this->user['id'],
            'created_at' => $this->time,
            'updated_by' => $this->user['id'],
            'updated_at' => $this->time
        ];

        $this->quoteLogiQwvModel->startTrans();
        // $count = $this->quoteLogiQwvModel->getCount(['inquiry_id' => $condition['inquiry_id']]);
        // 新增多行
        $row = intval($condition['row']) > 1 ? intval($condition['row']) : 1;

        $this->quoteLogiQwvModel->getcount();

        $flag = true;
        $data['ids'] = [];
        $nulldata = [
            'inquiry_id' => $condition['inquiry_id'],
            'created_by' => $this->user['id'],
            'created_at' => $this->time,
            'updated_by' => $this->user['id'],
            'updated_at' => $this->time
        ];

        if (!empty($condition['row']) && $volumn) {
            $res = $this->quoteLogiQwvModel->addRecord($qwvData);
            if ($res) {
                $data['ids'][] = $res;
            } else {
                $this->quoteLogiQwvModel->rollback();
                $this->jsonReturn(false);
            }
        } elseif ($volumn) {
            $res = $this->quoteLogiQwvModel->addRecord($qwvData);
            if ($res) {
                $data['ids'][] = $res;
            } else {
                $this->quoteLogiQwvModel->rollback();
                $this->jsonReturn(false);
            }
        }
        if (!empty($condition['row'])) {
            for ($i = 0; $i < $row; $i++) {
                $res = $this->quoteLogiQwvModel->addRecord($nulldata);
                if ($res) {
                    $data['ids'][] = $res;
                } else {
                    $this->quoteLogiQwvModel->rollback();
                    $this->jsonReturn(false);
                }
            }
        }

        if ($flag) {
            $qwvData = [
                'inquiry_id' => $condition['inquiry_id'],
                'created_by' => $this->user['id'],
                'created_at' => $this->time,
                'updated_by' => $this->user['id'],
                'updated_at' => $this->time
            ];
            $this->quoteLogiQwvModel->commit();
            $this->jsonReturn($data);
        } else {
            $this->quoteLogiQwvModel->rollback();
            $this->jsonReturn($flag);
        }
    }

    /**
     * @desc 修改物流报价件重尺信息接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function updateQuoteLogiQwvInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['r_id'])) {
            $where['id'] = $condition['r_id'];
            unset($condition['r_id']);

            $length = isDecimal($condition['length']) ? $condition['length'] : null;
            $width = isDecimal($condition['width']) ? $condition['width'] : null;
            $height = isDecimal($condition['height']) ? $condition['height'] : null;
            $volumn = $length * $width * $height;

            $qwvData = [
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'volumn' => $volumn > 0 ? $volumn : 0,
                'gross_weight' => isDecimal($condition['gross_weight']) ? $condition['gross_weight'] : null,
                'quantity' => isDecimal($condition['quantity']) ? $condition['quantity'] : null,
                'updated_by' => $this->user['id'],
                'updated_at' => $this->time
            ];

            $res = $this->quoteLogiQwvModel->updateInfo($where, $qwvData);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 批量修改物流报价件重尺信息接口
     *
     * @author liujf
     * @time 2017-08-21
     */
    public function batchUpdateQuoteLogiQwvInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['items'])) {

            $flag = true;
            $data = [];

            foreach ($condition['items'] as $item) {
                $where['id'] = $item['id'];
                unset($item['id']);

                $length = isDecimal($item['length']) ? $item['length'] : null;
                $width = isDecimal($item['width']) ? $item['width'] : null;
                $height = isDecimal($item['height']) ? $item['height'] : null;
                $volumn = $length * $width * $height;

                $qwvData = [
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'volumn' => $volumn > 0 ? $volumn : 0,
                    'gross_weight' => isDecimal($item['gross_weight']) ? $item['gross_weight'] : null,
                    'quantity' => isDecimal($item['quantity']) ? $item['quantity'] : null,
                    'updated_by' => $this->user['id'],
                    'updated_at' => $this->time
                ];

                $res = $this->quoteLogiQwvModel->updateInfo($where, $qwvData);

                if (!$res) {
                    $data[] = $where['id'];
                    $flag = false;
                }
            }

            if ($flag) {
                $this->jsonReturn($flag);
            } else {
                $this->setCode('-101');
                $this->setMessage(L('FAIL'));
                parent::jsonReturn($data);
            }
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 删除物流报价件重尺记录接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function delQuoteLogiQwvRecordAction() {
        $condition = $this->put_data;

        if (!empty($condition['r_id'])) {
            $res = $this->quoteLogiQwvModel->delRecord($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 分配物流报价人接口
     *
     * @author liujf
     * @time 2017-08-03
     */
    public function assignLogiAgentAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id']) && !empty($condition['logi_agent_id'])) {

            $this->inquiryModel->startTrans();

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $condition['logi_agent_id'],
                'logi_agent_id' => $condition['logi_agent_id'],
                'status' => 'LOGI_QUOTING',
                'updated_by' => $this->user['id']
            ];

            $res1 = $this->inquiryModel->updateData($data);

            // 更改报价单状态
            $quoteData = [
                'status' => 'LOGI_QUOTING',
                'updated_by' => $this->user['id'],
                'updated_at' => $this->time
            ];
            $res2 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save($quoteData);
            $flag = Rfq_CheckLogModel::addCheckLog($data['id'], $data['status'], $this->user);
            if ($res1['code'] == 1 && $res2 !== false && $flag !== false) {
                $this->inquiryModel->commit();
                $res = true;
            } else {
                $this->inquiryModel->rollback();
                $res = false;
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 提交物流审核接口
     *
     * @author liujf
     * @time 2017-08-08
     */
    public function submitLogiCheckAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {

            $logiCheckId = $condition['logi_check_id']; //$this->inquiryModel->getRoleUserId($this->user['group_id'], $inquiryModel::logiCheckRole, 'lg');

            $this->inquiryModel->startTrans();

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $logiCheckId,
                'logi_check_id' => $logiCheckId,
                'status' => 'LOGI_APPROVING',
                'updated_by' => $this->user['id']
            ];

            $res1 = $this->inquiryModel->updateData($data);

            // 更改报价单状态
            $quoteData = [
                'status' => 'LOGI_APPROVING',
                'updated_by' => $this->user['id'],
                'updated_at' => $this->time
            ];
            $res2 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save($quoteData);
            $flag = Rfq_CheckLogModel::addCheckLog($data['id'], $data['status'], $this->user);
            if ($res1['code'] == 1 && $res2 !== false && $flag !== false) {
                $this->inquiryModel->commit();
                $res = true;
            } else {
                $this->inquiryModel->rollback();
                $res = false;
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 通过物流审核接口
     *
     * @author liujf
     * @time 2017-10-23
     */
    public function passLogiCheckAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $this->inquiryModel->startTrans();

            $quoteId = $this->inquiryModel->where(['id' => $condition['inquiry_id']])->getField('quote_id');

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $quoteId,
                'status' => 'BIZ_APPROVING',
                'updated_by' => $this->user['id']
            ];

            $res1 = $this->inquiryModel->updateData($data);

            // 更改报价单状态
            $quoteData = [
                'status' => 'BIZ_APPROVING',
                'updated_by' => $this->user['id'],
                'updated_at' => $this->time
            ];
            $res2 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save($quoteData);
            $flag = Rfq_CheckLogModel::addCheckLog($data['id'], $data['status'], $this->user);
            if ($res1['code'] == 1 && $res2 !== false && $flag !== false) {
                $this->inquiryModel->commit();
                $res = true;
            } else {
                $this->inquiryModel->rollback();
                $res = false;
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 物流退回接口
     *
     * @author liujf
     * @time 2017-10-21
     */
    public function rejectLogiAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id']) && !empty($condition['current_node'])) {
            $this->inquiryModel->startTrans();

            $where = ['inquiry_id' => $condition['inquiry_id']];
            $inquiry = $this->inquiryModel->field('quote_id, logi_agent_id')->where(['id' => $condition['inquiry_id']])->find();

            $data = [
                'id' => $condition['inquiry_id'],
                'updated_by' => $this->user['id']
            ];

            switch ($condition['current_node']) {
                case 'issue' :
                    $status = 'BIZ_QUOTING';
                    $data['now_agent_id'] = $inquiry['quote_id'];
                    // 清除物流报价SKU
                    $logiRes = $this->quoteItemLogiModel->where($where)->delete();


                    break;
                case 'quote' :
                    $status = 'LOGI_DISPATCHING';
                    $inquiryModel = $this->inquiryModel;
                    $data['now_agent_id'] = $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], $this->user['group_id'], $inquiryModel::logiIssueAuxiliaryRole, $inquiryModel::logiIssueMainRole, ['in', ['lg', 'elg']]);
                    break;
                case 'check' :
                    $status = 'LOGI_QUOTING';
                    $data['now_agent_id'] = $inquiry['logi_agent_id'];
            }

            $data['status'] = $status;

            // 更改询单状态
            $res1 = $this->inquiryModel->updateData($data);


            // 更改报价单状态
            $quoteData = [
                'status' => $status,
                'updated_by' => $this->user['id'],
                'updated_at' => $this->time
            ];
            $res2 = $this->quoteModel->where($where)->save($quoteData);
            $op_note = !empty($condition['op_note']) ? $condition['op_note'] : '';
            $in_node = !empty($condition['in_node']) ? $condition['in_node'] : null;
            $flag = Rfq_CheckLogModel::addCheckLog($condition['inquiry_id'], $status, $this->user, $in_node, 'REJECT', $op_note);

            if ($res1['code'] == 1 && $res2 !== false && $flag !== false) {
                if ($condition['current_node'] == 'issue') {
                    if ($logiRes !== false) {
                        $res = true;
                    } else {
                        $res = false;
                    }
                } else {
                    $res = true;
                }
            } else {
                $res = false;
            }
            if ($res) {
                $this->inquiryModel->commit();
            } else {
                $this->inquiryModel->rollback();
            }
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 从事业部审核退回事业部报价回到事业部审核接口
     *
     * @author liujf
     * @time 2018-05-29
     */
    public function rejectQuotingToMarketAppovingAction() {

        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiry = $this->inquiryModel->where(['id' => $condition['inquiry_id']])->find();
//            if ($inquiry['status'] != 'REJECT_QUOTING') {
//                jsonReturn('', -101, L('INQUIRY_STATUS_ERROR'));
//            }

            $this->inquiryModel->startTrans();
            $inquiryData = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $inquiry['check_org_id'],
                'status' => 'MARKET_APPROVING',
                'updated_by' => $this->user['id']
            ];
            $res = $this->inquiryModel->updateData($inquiryData);
            $this->rollback($this->inquiryModel, null, $res);

            $result = $this->quoteModel->GeneralInfo(['inquiry_id' => $condition['inquiry_id']]);

            $this->rollback($this->inquiryModel, null, $result);
            $flag1 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save(['status' => 'MARKET_APPROVING']);
            $this->rollback($this->inquiryModel, $flag1);


            $quote_logi_fee_Model = new Rfq_QuoteLogiFeeModel();
            $flag2 = $quote_logi_fee_Model->submit($condition['inquiry_id']);

            $this->rollback($this->inquiryModel, $flag2);
            $FinalQuoteModel = new Rfq_FinalQuoteModel();
            $res2 = $FinalQuoteModel->submit($condition['inquiry_id']);
            $this->rollback($this->inquiryModel, $res2);
            $flag3 = $FinalQuoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save(['status' => 'MARKET_APPROVING']);
            $this->rollback($this->inquiryModel, $flag3);
            $flag4 = Rfq_CheckLogModel::addCheckLog($inquiryData['id'], $inquiryData['status'], $this->user);
            $this->rollback($this->inquiryModel, $flag4);

            $this->inquiryModel->commit();
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取陆运险费用接口
     *
     * @author liujf
     * @time 2017-09-21
     */
    public function getOverlandInsuFeeAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $totalExwPrice = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->getField('total_exw_price');

            $overlandInsuFee = $this->_getOverlandInsuFee($totalExwPrice, $condition['overland_insu_rate']);
            $res['overland_insu'] = $overlandInsuFee['CNY'];

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取国际运输险费用接口
     *
     * @author liujf
     * @time 2017-09-21
     */
    public function getShippingInsuFeeAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $totalExwPrice = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->getField('total_exw_price');

            $shippingInsuFee = $this->_getShippingInsuFee($totalExwPrice, $condition['shipping_insu_rate']);
            $res['shipping_insu'] = $shippingInsuFee['CNY'];

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 计算物流合计
     *
     * @param array $condition
     * --------------------------------------------------------------
     *     trade_terms_bn 贸易术语简称
     *     total_exw_price 报出EXW合计
     *     premium_rate 保险税率
     *     payment_period 回款周期(天)
     *     bank_interest 银行利息
     *     fund_occupation_rate 资金占用比例
     *     certification_fee 商品检测费（如第三方检验费、认证费等）
     *     certification_fee_cur 商品检测费（如第三方检验费、认证费等）币种
     *     inspection_fee 商检费
     *     inspection_fee_cur 商检费币种
     *     land_freight 陆运费
     *     land_freight_cur 陆运费币种
     *     port_surcharge 港杂费
     *     port_surcharge_cur 港杂费币种
     *     inter_shipping 国际运费
     *     inter_shipping_cur 国际运费币种
     *     dest_delivery_fee 目的地配送费
     *     dest_delivery_fee_cur 目的地配送费币种
     *     dest_clearance_fee 目的地清关费
     *     dest_clearance_fee_cur 目的地清关费币种
     *     overland_insu_rate 陆运险率
     *     shipping_insu_rate 国际运输险率
     *     dest_tariff_rate 目的地关税税率
     *     dest_va_tax_rate 目的地增值税率
     * --------------------------------------------------------------
     * @return mixed
     * @author liujf
     * @time 2017-08-18
     */
    public function calcuTotalLogiFee($condition = []) {
        if (empty($condition['trade_terms_bn'])) {
            return false;
        } else {
            $trade = $condition['trade_terms_bn'];
        }

        $data = $condition;

        $data['inter_shipping'] = $data['port_surcharge'] = $data['land_freight'] = 0;
        $data['overland_insu_rate'] = $data['dest_clearance_fee'] = $data['dest_delivery_fee'] = 0;
        $data['dest_va_tax_rate'] = $data['dest_tariff_rate'] = $data['shipping_insu_rate'] = 0;
        $data['certification_fee'] = $condition['certification_fee'] > 0 ? $condition['certification_fee'] : 0;
        $data['inspection_fee'] = $condition['inspection_fee'] > 0 ? $condition['inspection_fee'] : 0;

        $this->_HandelData($data, $trade, $condition);

        $certificationFeeUSD = round($data['certification_fee'] * $this->_getRateUSD($data['certification_fee_cur']), 8);
        $inspectionFeeUSD = round($data['inspection_fee'] * $this->_getRateUSD($data['inspection_fee_cur']), 8);
        $landFreightUSD = round($data['land_freight'] * $this->_getRateUSD($data['land_freight_cur']), 8);
        $overlandInsuFee = $this->_getOverlandInsuFee($data['total_exw_price'], $data['overland_insu_rate']);
        $overlandInsuUSD = $overlandInsuFee['USD'];
        $portSurchargeUSD = round($data['port_surcharge'] * $this->_getRateUSD($data['port_surcharge_cur']), 8);
        $interShippingUSD = round($data['inter_shipping'] * $this->_getRateUSD($data['inter_shipping_cur']), 8);
        $destDeliveryFeeUSD = round($data['dest_delivery_fee'] * $this->_getRateUSD($data['dest_delivery_fee_cur']), 8);
        $destClearanceFeeUSD = round($data['dest_clearance_fee'] * $this->_getRateUSD($data['dest_clearance_fee_cur']), 8);
        $sumUSD = $data['total_exw_price'] + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $inspectionFeeUSD + $interShippingUSD;
        $destTariffUSD = round($sumUSD * $data['dest_tariff_rate'] / 100, 8);
        $destVaTaxUSD = round($sumUSD * (1 + $data['dest_tariff_rate'] / 100) * $data['dest_va_tax_rate'] / 100, 8);

        $tmpRate1 = 1 - $data['premium_rate'] - round($data['payment_period'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 360, 8);
        $tmpRate2 = $tmpRate1 - 1.1 * $data['shipping_insu_rate'] / 100;

        $totalQuotePrice = $this->_getTotal_QuotePrice($trade, $tmpRate1, $tmpRate2, $data, $certificationFeeUSD, $inspectionFeeUSD, $landFreightUSD, $overlandInsuUSD, $portSurchargeUSD, $interShippingUSD, $destClearanceFeeUSD, $destDeliveryFeeUSD);

        $shippingInsuFee = $this->_getShippingInsuFee($data['total_exw_price'], $data['shipping_insu_rate']);
        $shippingInsuUSD = $shippingInsuFee['USD'];
        $totalBankFeeUSD = round($totalQuotePrice * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_period'] / 360, 8);
        $totalInsuFeeUSD = round($totalQuotePrice * $data['premium_rate'], 8);

        $data['overland_insu'] = $overlandInsuUSD;
        $data['shipping_insu'] = $shippingInsuUSD;
        $data['dest_tariff_fee'] = $destTariffUSD;
        $data['dest_va_tax_fee'] = $destVaTaxUSD;
        // 物流费用合计
        $data['total_logi_fee'] = round($inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $shippingInsuUSD + $destDeliveryFeeUSD + $destClearanceFeeUSD + $destTariffUSD + $destVaTaxUSD, 8);

        $data['shipping_charge_cny'] = round(($data['inspection_fee_cur'] == 'CNY' ? $data['inspection_fee'] : 0) + ($data['land_freight_cur'] == 'CNY' ? $data['land_freight'] : 0) + ($data['port_surcharge_cur'] == 'CNY' ? $data['port_surcharge'] : 0) + ($data['inter_shipping_cur'] == 'CNY' ? $data['inter_shipping'] : 0) + ($data['dest_delivery_fee_cur'] == 'CNY' ? $data['dest_delivery_fee'] : 0), 8);
        $data['shipping_charge_ncny'] = round(($data['inspection_fee_cur'] == 'USD' ? $data['inspection_fee'] : 0) + ($data['land_freight_cur'] == 'USD' ? $data['land_freight'] : 0) + ($data['port_surcharge_cur'] == 'USD' ? $data['port_surcharge'] : 0) + ($data['inter_shipping_cur'] == 'USD' ? $data['inter_shipping'] : 0) + ($data['dest_delivery_fee_cur'] == 'USD' ? $data['dest_delivery_fee'] : 0), 8);
        $data['total_quote_price'] = $totalQuotePrice + $shippingInsuFee['USD'];
        $data['total_bank_fee'] = $totalBankFeeUSD;
        $data['total_insu_fee'] = $totalInsuFeeUSD;

        return $data;
    }

    private function _HandelData(&$data, $trade, $condition) {
        switch ($trade) {
            case 'EXW' :
                break;
            case 'FCA':
            case 'FAS' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                break;
            case 'FOB' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                break;
            case 'CPT' :
            case 'CFR' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                break;
            case 'CIF' :
            case 'CIP' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                break;
            case 'DAP':
            case 'DAT' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
                break;
            case 'DDP':
            case '快递' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
                $data['dest_clearance_fee'] = $condition['dest_clearance_fee'] > 0 ? $condition['dest_clearance_fee'] : 0;
                $data['dest_tariff_rate'] = $condition['dest_tariff_rate'] > 0 ? $condition['dest_tariff_rate'] : 0;
                $data['dest_va_tax_rate'] = $condition['dest_va_tax_rate'] > 0 ? $condition['dest_va_tax_rate'] : 0;
        }
    }

    private function _getTotal_QuotePrice($trade, $tmpRate1, $tmpRate2, $data, $certificationFeeUSD, $inspectionFeeUSD, $landFreightUSD, $overlandInsuUSD, $portSurchargeUSD = 0, $interShippingUSD = 0, $destClearanceFeeUSD = 0, $destDeliveryFeeUSD = 0) {

        switch ($trade) {
            case 'EXW' :
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD) / $tmpRate1, 8) : 0;
                break;
            case 'FCA' :
            case 'FAS':
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD) / $tmpRate1, 8) : 0;
                break;
            case 'FOB' :
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD) / $tmpRate1, 8) : 0;
                break;
            case 'CPT' :
            case 'CFR' :
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) / $tmpRate1, 8) : 0;
                break;
            case 'CIF' :
            case 'CIP' :
                $tmpCaFee = $data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD;

                $totalQuotePrice = $tmpRate2 > 0 ? $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2) : 0;
                break;
            case 'DAP' :
            case 'DAT' :
                $tmpCaFee = $data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $destDeliveryFeeUSD;
                $totalQuotePrice = $tmpRate2 > 0 ? $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2, $trade, $tmpRate1) : 0;
                break;
            case 'DDP' :
            case '快递' :

                $tmpCaFee = ($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) * (1 + $data['dest_tariff_rate'] / 100) * (1 + $data['dest_va_tax_rate'] / 100) + $destDeliveryFeeUSD + $destClearanceFeeUSD;
                $totalQuotePrice = $tmpRate2 > 0 ? $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2, $trade, $tmpRate1) : 0;
        }
        return $totalQuotePrice;
    }

    /**
     * @desc 获取报出价格合计
     *
     * @param float $calcuFee, $shippingInsuRate, $calcuRate, $extRate
     * @param string $trade
     * @return float
     * @author liujf
     * @time 2017-08-10
     */
    private function _getTotalQuotePrice($calcuFee, $shippingInsuRate, $calcuRate, $trade = 'CIF', $extRate = 1) {
        $tmpIfFee = round($calcuFee * 1.1 * $shippingInsuRate / 100 / $calcuRate, 8);

        if ($tmpIfFee >= 8 || $tmpIfFee == 0) {
            $totalQuotePrice = round($calcuFee / $calcuRate, 8);
        } else {
            $tmpRate = $trade == 'DAP' || $trade == 'DAT' || $trade == 'DDP' || $trade == '快递' ? $extRate : $calcuRate;
            $totalQuotePrice = round(($calcuFee + 8) / $tmpRate, 8);
        }

        return $totalQuotePrice;
    }

    /**
     * @desc 获取陆运险费用
     *
     * @param float $totalExwPrice exw价格合计
     * @param float $overlandInsuRate 陆运险率
     * @return float
     * @author liujf
     * @time 2017-09-20
     */
    private function _getOverlandInsuFee($totalExwPrice = 0, $overlandInsuRate = 0) {
        // 美元兑人民币汇率
        $rate = $this->_getRateUSD('CNY');

        $tmpPrice = $totalExwPrice * $overlandInsuRate / 100;

        $overlandInsuCNY = round($tmpPrice / $rate, 8);

        if ($overlandInsuCNY > 0 && $overlandInsuCNY < 50) {
            $overlandInsuUSD = round($rate > 0 ? 50 * $rate : 0, 8);
            $overlandInsuCNY = 50;
        } else if ($overlandInsuCNY >= 50) {
            $overlandInsuUSD = round($tmpPrice, 8);
        } else {
            $overlandInsuCNY = 0;
            $overlandInsuUSD = 0;
        }

        return ['USD' => $overlandInsuUSD, 'CNY' => $overlandInsuCNY];
    }

    /**
     * @desc 获取国际运输险费用
     *
     * @param float $totalExwPrice exw价格合计
     * @param float $shippingInsuRate 国际运输险率
     * @return float
     * @author liujf
     * @time 2017-09-20
     */
    private function _getShippingInsuFee($totalExwPrice = 0, $shippingInsuRate = 0) {
        // 美元兑人民币汇率
        $rate = $this->_getRateUSD('CNY');

        $tmpPrice = $totalExwPrice * 1.1 * $shippingInsuRate / 100;

        $shippingInsuCNY = round($tmpPrice / $rate, 8);

        if ($shippingInsuCNY > 0 && $shippingInsuCNY < 50) {
            $shippingInsuUSD = round($rate > 0 ? 50 * $rate : 0, 8);
            $shippingInsuCNY = 50;
        } else if ($shippingInsuCNY >= 50) {
            $shippingInsuUSD = round($tmpPrice, 8);
        } else {
            $shippingInsuCNY = 0;
            $shippingInsuUSD = 0;
        }

        return ['USD' => $shippingInsuUSD, 'CNY' => $shippingInsuCNY];
    }

    /**
     * @desc 获取人民币兑换汇率
     *
     * @param string $cur 币种
     * @return float
     * @author liujf
     * @time 2017-08-03
     */
    private function _getRateCNY($cur) {
        if (empty($cur)) {
            return 1;
        } else {
            return $this->_getRate($cur, 'CNY');
        }
    }

    /**
     * @desc 获取美元兑换汇率
     *
     * @param string $cur 币种
     * @return float
     * @author liujf
     * @time 2017-08-03
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
     * @time 2017-08-03
     */
    private function _getRate($holdCur, $exchangeCur = 'CNY') {
        if (!empty($holdCur)) {
            if ($holdCur == $exchangeCur) {
                return 1;
            }
            $exchangeRateModel = new ExchangeRateModel();
            $exchangeRate = $exchangeRateModel->field('rate')->where(['cur_bn1' => $holdCur, 'cur_bn2' => $exchangeCur])->order('created_at DESC')->find();

            return $exchangeRate['rate'];
        } else {
            return false;
        }
    }

    /**
     * @desc 更新商务报出贸易单价
     *
     * @param int $inquiryId 询单ID
     * @param array $data
     * @return bool
     * @author liujf
     * @time 2018-05-29
     */
    private function _updateQuoteUnitPrice($inquiryId, $data) {
        $quoteItemList = $this->quoteItemModel->where(['inquiry_id' => $inquiryId])->select();
        foreach ($quoteItemList as $quoteItem) {
            $quoteUnitPrice = $data['total_exw_price'] > 0 ? round($data['total_quote_price'] * $quoteItem['exw_unit_price'] / $data['total_exw_price'], 8) : 0;
            $res = $this->quoteItemModel->where(['id' => $quoteItem['id']])->save(['quote_unit_price' => $quoteUnitPrice, 'updated_by' => $this->user['id'], 'updated_at' => $this->time]);
            if (!$res) {
                return false;
            }
        }
        return true;
    }

    /**
     * @desc 获取用户所在指定组织的id集合串
     *
     * @param string $employeeId 员工id
     * @param array $findFields 查找的组织字段
     * @param string $outField 输出的组织字段
     * @return string 组织id集合串
     * @author liujf
     * @time 2017-08-31
     */
    /* private function _getOrgIds($employeeId = '-1', $findFields = ['logi_quote_org_id'], $outField = 'logi_check_org_id') {

      $orgMemberList = $this->orgMemberModel->getList(['employee_id' => $employeeId]);

      $orgArr = [];

      foreach ($orgMemberList as $orgMember) {
      $orgArr[] = $orgMember['org_id'];
      }

      $where = [];
      $condition = $orgArr ? ['in', $orgArr] : '-1';

      foreach ($findFields as $findField) {
      $where['_complex'][$findField] = $condition;
      }

      if ($where) $where['_complex']['_logic'] = 'or';

      $marketAreaTeamList = $this->marketAreaTeamModel->where($where)->select();

      $appointOrgArr = [];

      foreach ($marketAreaTeamList as $marketAreaTeam) {
      $appointOrgArr[] = $marketAreaTeam[$outField];
      }

      return implode(',', $appointOrgArr);
      } */

    /**
     * @desc 对获取列表数据的处理
     *
     * @author liujf
     * @time 2017-08-02
     */
    private function _handleList($model, $data = [], $condition = [], $join = false) {
        if ($data) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $data;
            $res['count'] = $join ? $model->getJoinCount($condition) : $model->getCount($condition);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 重写jsonReturn方法
     *
     * @author liujf
     * @time 2017-08-02
     */
    public function jsonReturn($data = [], $type = 'JSON') {
        if ($data) {
            $this->setCode('1');
            $this->setMessage(L('SUCCESS'));
            parent::jsonReturn($data, $type);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            parent::jsonReturn();
        }
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
