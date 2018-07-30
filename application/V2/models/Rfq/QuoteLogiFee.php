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

            $quoteLogiFee['land_freight_usd'] = round($quoteLogiFee['land_freight'] / $this->_getRateUSD($quoteLogiFee['land_freight_cur']), 8);
            $quoteLogiFee['port_surcharge_usd'] = round($quoteLogiFee['port_surcharge'] / $this->_getRateUSD($quoteLogiFee['port_surcharge_cur']), 8);
            $quoteLogiFee['inspection_fee_usd'] = round($quoteLogiFee['inspection_fee'] / $this->_getRateUSD($quoteLogiFee['inspection_fee_cur']), 8);
            $quoteLogiFee['inter_shipping_usd'] = round($quoteLogiFee['inter_shipping'] / $this->_getRateUSD($quoteLogiFee['inter_shipping_cur']), 8);

            $quoteLogiFee['dest_delivery_fee_usd'] = round($quoteLogiFee['dest_delivery_fee'] / $this->_getRateUSD($quoteLogiFee['dest_delivery_fee_cur']), 8);
            $quoteLogiFee['dest_clearance_fee_usd'] = round($quoteLogiFee['dest_clearance_fee'] / $this->_getRateUSD($quoteLogiFee['dest_clearance_fee_cur']), 8);

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
            return $this->_getRate('CNY', $cur);
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
            return $this->_getRate('USD', $cur);
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
            if ($holdCur == $exchangeCur)
                return 1;

            $exchangeRateModel = new ExchangeRateModel();
            $exchangeRate = $exchangeRateModel->field('rate')->where(['cur_bn1' => $holdCur, 'cur_bn2' => $exchangeCur])->order('created_at DESC')->find();

            return $exchangeRate['rate'];
        } else {
            return false;
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

        $overlandInsuCNY = round($tmpPrice * $rate, 8);

        if ($overlandInsuCNY > 0 && $overlandInsuCNY < 50) {
            $overlandInsuUSD = round($rate > 0 ? 50 / $rate : 0, 8);
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

        $shippingInsuCNY = round($tmpPrice * $rate, 8);

        if ($shippingInsuCNY > 0 && $shippingInsuCNY < 50) {
            $shippingInsuUSD = round($rate > 0 ? 50 / $rate : 0, 8);
            $shippingInsuCNY = 50;
        } else if ($shippingInsuCNY >= 50) {
            $shippingInsuUSD = round($tmpPrice, 8);
        } else {
            $shippingInsuCNY = 0;
            $shippingInsuUSD = 0;
        }

        return ['USD' => $shippingInsuUSD, 'CNY' => $shippingInsuCNY];
    }

}
