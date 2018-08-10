<?php

/*
 * @desc 报价单物流费用模型
 *
 * @author liujf
 * @time 2017-08-02
 */

class Rfq_QuoteLogiFeeModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_logi_fee';
    protected $joinTable1 = 'erui_rfq.quote b ON a.quote_id = b.id';
    protected $joinTable2 = 'erui_rfq.inquiry c ON a.inquiry_id = c.id';
    protected $joinTable3 = 'erui_sys.employee d ON c.logi_agent_id = d.id';
    protected $joinTable4 = 'erui_dict.country e ON c.country_bn = e.bn AND e.lang = \'zh\'';
    protected $joinField = 'a.*, b.premium_rate AS quote_premium_rate, b.trade_terms_bn, b.from_country, b.from_port, b.trans_mode_bn, b.to_country, b.to_port, b.package_mode, b.box_type_bn, b.delivery_addr, b.dispatch_place, b.quote_remarks, b.total_logi_fee, b.total_insu_fee, b.total_exw_price, b.total_quote_price, c.serial_no, c.status AS inquiry_status, c.org_id, c.logi_org_id, c.inflow_time, d.name';
    protected $joinField_ = 'a.*, b.period_of_validity, c.serial_no, c.buyer_name, c.agent_id, e.name AS country_name';

    public function __construct() {
        parent::__construct();
    }

    public function __destruct() {

    }

    public function info($where) {
        $quotewhere = [];
        $quotewhere['inquiry_id'] = $where['id'];
        $quoteLogiFee = $this->getJoinDetail($quotewhere);
        if (!empty($quoteLogiFee)) {
            $transModeModel = new TransModeModel();
            $countryModel = new CountryModel();
            $portModel = new PortModel();
            $boxTypeModel = new BoxTypeModel();
            $quoteLogiFee['from_country_name'] = $countryModel->getCountryNameByBn($quoteLogiFee['from_country'], $this->lang);
            $quoteLogiFee['to_country_name'] = $countryModel->getCountryNameByBn($quoteLogiFee['to_country'], $this->lang);
            $quoteLogiFee['from_port_name'] = $portModel->getPortNameByBn($quoteLogiFee['from_country'], $quoteLogiFee['from_port'], $this->lang);
            $quoteLogiFee['to_port_name'] = $portModel->getPortNameByBn($quoteLogiFee['to_country'], $quoteLogiFee['to_port'], $this->lang);
            $quoteLogiFee['trans_mode_name'] = $transModeModel->getTransModeByBn($quoteLogiFee['trans_mode_bn'], $this->lang);
            $quoteLogiFee['logi_trans_mode_name'] = $transModeModel->getTransModeByBn($quoteLogiFee['logi_trans_mode_bn'], $this->lang);
            $quoteLogiFee['logi_from_port_name'] = $portModel->getPortNameByBn($quoteLogiFee['from_country'], $quoteLogiFee['logi_from_port'], $this->lang);
            $quoteLogiFee['logi_to_port_name'] = $portModel->getPortNameByBn($quoteLogiFee['to_country'], $quoteLogiFee['logi_to_port'], $this->lang);
            $quoteLogiFee['logi_box_type_name'] = $boxTypeModel->getBoxTypeNameByBn($quoteLogiFee['logi_box_type_bn'], $this->lang);

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

            return $quoteLogiFee;
        } else {
            return [];
        }
    }

    /**
     * @desc 获取关联详情
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-02
     */
    public function getJoinDetail($condition = []) {

        $where = $this->getJoinWhere($condition);

        return $this->alias('a')
                        ->join($this->joinTable1, 'LEFT')
                        ->join($this->joinTable2, 'LEFT')
                        ->join($this->joinTable3, 'LEFT')
                        ->field($this->joinField)
                        ->where($where)
                        ->find();
    }

    /**
     * @desc 获取关联查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-02
     */
    public function getJoinWhere($condition = []) {

        $where = [];

        if (!empty($condition['quote_id'])) {
            $where['a.quote_id'] = $condition['quote_id'];
        }

        if (!empty($condition['inquiry_id'])) {
            $where['a.inquiry_id'] = $condition['inquiry_id'];
        }

        if (!empty($condition['status'])) {
            $where['a.status'] = $condition['status'];
        }

        if (!empty($condition['country_bn'])) {
            $where['c.country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }

        if (!empty($condition['serial_no'])) {
            $where['c.serial_no'] = ['like', '%' . $condition['serial_no'] . '%'];
        }

        if (!empty($condition['buyer_name'])) {
            $where['c.buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }

        if (!empty($condition['agent_id'])) {
            $where['c.agent_id'] = $condition['agent_id'];
        }

        if (!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])) {
            $where['c.created_at'] = [
                ['egt', $condition['start_inquiry_time']],
                ['elt', $condition['end_inquiry_time'] . ' 23:59:59']
            ];
        }

        if (!empty($condition['market_agent_id'])) {
            if (empty($condition['agent_id'])) {
                $quoter['c.agent_id'] = ['in', $condition['market_agent_id']];
            } else {
                $quoter['c.agent_id'] = [['eq', $condition['agent_id']], ['in', $condition['market_agent_id']], 'and'];
            }
            $quoter['a.status'] = ['neq', 'QUOTED'];

            $checker['a.checked_by'] = $condition['user_id'];
            $checker['a.status'] = 'QUOTED';

            $map[] = $quoter;
            $map[] = $checker;
            $map['_logic'] = 'or';
            $where[] = $map;
        }


        $where['a.deleted_flag'] = 'N';

        return $where;
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
            $rate_model = new ExchangeRateModel();
            return $rate_model->getRate($cur, 'CNY');
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
            $rate_model = new ExchangeRateModel();
            return $rate_model->getRate($cur, 'USD');
        }
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
     * @desc 获取详情
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-18
     */
    public function getDetail($condition = []) {

        $where = $this->getWhere($condition);

        return $this->where($where)->find();
    }

    public function submit($inquiry_id) {


        $where['inquiry_id'] = $inquiry_id;
        $quoteModel = new Rfq_QuoteModel();
        $quoteLogiCostModel = new QuoteLogiCostModel();


        $quote = $quoteModel->where($where)->find();
        $data = $this->where($where)->find();

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
            $data['port_surcharge_items'] = $quoteLogiCostModel
                    ->getList(['inquiry_id' => $inquiry_id, 'type' => 'port_surcharge'], 'price,qty,cur_bn');
            if (empty($data['port_surcharge_items'])) {
                return false;
            } else {


                $data['port_surcharge'] = $data['inter_shipping'] = 0;

                $data['inter_shipping_items'] = $quoteLogiCostModel
                        ->getList(['inquiry_id' => $inquiry_id, 'type' => 'inter_shipping'], 'price,qty,cur_bn');
                foreach ($data['port_surcharge_items'] as $portSurchargeItem) {

                    $data['port_surcharge'] += round($portSurchargeItem['price'] * $portSurchargeItem['qty'] * $this->_getRateUSD($portSurchargeItem['cur_bn']), 8);
                }
                if ($data['trade_terms_bn'] != 'FOB') {
                    if (empty($data['inter_shipping_items'])) {
                        return false;
                    } else {
                        foreach ($data['inter_shipping_items'] as $interShippingItem) {
                            $data['inter_shipping'] += round($interShippingItem['price'] * $interShippingItem['qty'] * $this->_getRateUSD($interShippingItem['cur_bn']), 8);
                        }
                    }
                }
            }
        }

        $data = $this->calcuTotalLogiFee($data);
// 去掉暂无的数据
        $data['logi_from_port'] = $data['logi_from_port'] == L('NOTHING') ? null : $data['logi_from_port'];
        $data['logi_to_port'] = $data['logi_to_port'] == L('NOTHING') ? null : $data['logi_to_port'];
        $data['logi_trans_mode_bn'] = $data['logi_trans_mode_bn'] == L('NOTHING') ? null : $data['logi_trans_mode_bn'];
        $data['logi_box_type_bn'] = $data['logi_box_type_bn'] == L('NOTHING') ? null : $data['logi_box_type_bn'];

        $data['updated_by'] = defined(UID) ? UID : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');


        $quoteData['quote_remarks'] = $quote['quote_remarks'];
        $quoteData['total_logi_fee'] = $data['total_logi_fee'];
        $quoteData['total_quote_price'] = $data['total_quote_price'];
        $quoteData['total_bank_fee'] = $data['total_bank_fee'];
        $quoteData['total_insu_fee'] = $data['total_insu_fee'];
        $quoteData['updated_by'] = defined(UID) ? UID : 0;
        $quoteData['updated_at'] = date('Y-m-d H:i:s');
        $res2 = $quoteModel->where($where)->save($quoteData);

        unset($quoteModel, $quoteLogiCostModel);
        $res3 = $this->_updateQuoteUnitPrice($inquiry_id, $data);



        if ($res2 !== false && $res3) {
            return true;
        } else {
            return false;
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

        $data['land_freight'] = 0;
        $data['port_surcharge'] = 0;
        $data['inter_shipping'] = 0;
        $data['dest_delivery_fee'] = 0;
        $data['dest_clearance_fee'] = 0;
        $data['overland_insu_rate'] = 0;
        $data['shipping_insu_rate'] = 0;
        $data['dest_tariff_rate'] = 0;
        $data['dest_va_tax_rate'] = 0;

        $data['certification_fee'] = isset($condition['certification_fee']) && $condition['certification_fee'] > 0 ? $condition['certification_fee'] : 0;
        $data['inspection_fee'] = isset($condition['inspection_fee']) && $condition['inspection_fee'] > 0 ? $condition['inspection_fee'] : 0;

        switch (true) {
            case $trade == 'EXW' :
                break;
            case $trade == 'FCA' || $trade == 'FAS' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                break;
            case $trade == 'FOB' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                break;
            case $trade == 'CPT' || $trade == 'CFR' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                break;
            case $trade == 'CIF' || $trade == 'CIP' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                break;
            case $trade == 'DAP' || $trade == 'DAT' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
                break;
            case $trade == 'DDP' || $trade == '快递' :
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

        switch (true) {
            case $trade == 'EXW' :
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD) / $tmpRate1, 8) : 0;
                break;
            case $trade == 'FCA' || $trade == 'FAS' :
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD) / $tmpRate1, 8) : 0;
                break;
            case $trade == 'FOB' :
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD) / $tmpRate1, 8) : 0;
                break;
            case $trade == 'CPT' || $trade == 'CFR' :
                $totalQuotePrice = $tmpRate1 > 0 ? round(($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) / $tmpRate1, 8) : 0;
                break;
            case $trade == 'CIF' || $trade == 'CIP' :
                $tmpCaFee = $data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD;
                $totalQuotePrice = $tmpRate2 > 0 ? $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2) : 0;
                break;
            case $trade == 'DAP' || $trade == 'DAT' :
                $tmpCaFee = $data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $destDeliveryFeeUSD;
                $totalQuotePrice = $tmpRate2 > 0 ? $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2, $trade, $tmpRate1) : 0;
                break;
            case $trade == 'DDP' || $trade == '快递' :
                $tmpCaFee = ($data['total_exw_price'] + $certificationFeeUSD + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) * (1 + $data['dest_tariff_rate'] / 100) * (1 + $data['dest_va_tax_rate'] / 100) + $destDeliveryFeeUSD + $destClearanceFeeUSD;
                $totalQuotePrice = $tmpRate2 > 0 ? $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2, $trade, $tmpRate1) : 0;
        }


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

        $data['total_quote_price'] = $totalQuotePrice + $shippingInsuUSD;
        $data['total_bank_fee'] = $totalBankFeeUSD;
        $data['total_insu_fee'] = $totalInsuFeeUSD;

        return $data;
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
        $quoteItemModel = new Rfq_QuoteItemModel();

        $quoteItemList = $quoteItemModel->where(['inquiry_id' => $inquiryId])->select();
        foreach ($quoteItemList as $quoteItem) {
            $quoteUnitPrice = $data['total_exw_price'] > 0 ? round($data['total_quote_price'] * $quoteItem['exw_unit_price'] / $data['total_exw_price'], 8) : 0;
            $res = $quoteItemModel->where(['id' => $quoteItem['id']])->save(['quote_unit_price' => $quoteUnitPrice, 'updated_by' => $this->user['id'], 'updated_at' => $this->time]);
            if ($res === false) {
                return false;
            }
        }
        return true;
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

}
