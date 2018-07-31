<?php

/**
 * @desc   QuoteModel
 * @Author 买买提
 */
class Rfq_QuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote';

    const INQUIRY_DRAFT = 'DRAFT';             //新建询单
    const INQUIRY_BIZ_DISPATCHING = 'BIZ_DISPATCHING';   //事业部分单员
    const INQUIRY_CC_DISPATCHING = 'CC_DISPATCHING';    //易瑞客户中心
    const INQUIRY_BIZ_QUOTING = 'BIZ_QUOTING';       //事业部报价
    const INQUIRY_LOGI_DISPATCHING = 'LOGI_DISPATCHING';  //物流分单员
    const INQUIRY_LOGI_QUOTING = 'LOGI_QUOTING';      //物流报价
    const INQUIRY_LOGI_APPROVING = 'LOGI_APPROVING';    //物流审核
    const INQUIRY_BIZ_APPROVING = 'BIZ_APPROVING';     //事业部核算
    const INQUIRY_MARKET_APPROVING = 'MARKET_APPROVING';  //事业部审核
    const INQUIRY_MARKET_CONFIRMING = 'MARKET_CONFIRMING'; //市场确认
    const INQUIRY_QUOTE_SENT = 'QUOTE_SENT';        //报价单已发出
    const INQUIRY_INQUIRY_CLOSED = 'INQUIRY_CLOSED';    //报价关闭
    const QUOTE_NOT_QUOTED = 'NOT_QUOTED'; //未报价
    const QUOTE_ONGOING = 'ONGOING';    //报价中
    const QUOTE_QUOTED = 'QUOTED';     //已报价
    const QUOTE_COMPLETED = 'COMPLETED';  //已完成

    public function __construct() {
        parent::__construct();
    }

    public function getQuoteIdByInQuiryId($inquiry_id) {
        return $this->where(['inquiry_id' => $inquiry_id])->getField('id');
    }

    public function info($where, $results) {

        $fields = 'total_purchase,quote_remarks,total_weight,package_volumn,package_mode,'
                . 'payment_mode,trade_terms_bn,payment_period,from_country,to_country,'
                . 'from_port,to_port,trans_mode_bn,bank_interest,period_of_validity,'
                . 'exchange_rate,total_quote_price,total_exw_price,dispatch_place,delivery_addr,'
                . 'logi_quote_flag,certification_fee,gross_profit_rate,premium_rate,'
                . 'total_logi_fee,total_exw_price,total_quote_price,total_bank_fee,delivery_period,fund_occupation_rate,payment_period';
        $quotewhere = [];
        $quotewhere['inquiry_id'] = $where['id'];
        $quotedata = $this
                        ->field($fields)
                        ->where($quotewhere)->find();
        if (empty($quotedata['package_volumn'])) {
            $quotedata['package_volumn'] = (new QuoteLogiQwvModel())->GetTotal($quotewhere['inquiry_id']);
        }
        if (!empty($quotedata)) {
            $transModeModel = new TransModeModel();
            $countryModel = new CountryModel();
            $portModel = new PortModel();
            //追加结果
            $quoteinfo['logi_quote_flag'] = $quotedata['logi_quote_flag'];  //是否需要物流报价
            $quoteinfo['total_weight'] = $quotedata['total_weight'];    //总重
            $quoteinfo['package_volumn'] = $quotedata['package_volumn'];    //包装总体积
            $quoteinfo['total_purchase'] = $quotedata['total_purchase'];    //采购总价
            $quoteinfo['package_mode'] = $quotedata['package_mode'];    //包装方式
            $quoteinfo['payment_mode'] = $quotedata['payment_mode'];    //付款方式
            $quoteinfo['quote_remarks'] = $quotedata['quote_remarks'];    //报价备注
            $quoteinfo['trade_terms_bn'] = $quotedata['trade_terms_bn'];    //贸易术语

            $quoteinfo['dispatch_place'] = $quotedata['dispatch_place'];    //起始发运地
            $quoteinfo['delivery_addr'] = $quotedata['delivery_addr'];    //交货地点
            $quoteinfo['from_country'] = $quotedata['from_country'];    //起运国
            $quoteinfo['from_country_name'] = $countryModel->getCountryNameByBn($quotedata['from_country'], $this->lang);
            $quoteinfo['to_country'] = $quotedata['to_country'];    //目的国
            $quoteinfo['to_country_name'] = $countryModel->getCountryNameByBn($quotedata['to_country'], $this->lang);
            $quoteinfo['from_port'] = $quotedata['from_port'];    //起运港
            $quoteinfo['from_port_name'] = $portModel->getPortNameByBn($quotedata['from_country'], $quotedata['from_port'], $this->lang);
            $quoteinfo['to_port'] = $quotedata['to_port'];    //目的港
            $quoteinfo['to_port_name'] = $portModel->getPortNameByBn($quotedata['to_country'], $quotedata['to_port'], $this->lang);
            $quoteinfo['trans_mode_bn'] = $quotedata['trans_mode_bn'];    //运输方式
            $quoteinfo['trans_mode_name'] = $transModeModel->getTransModeByBn($quotedata['trans_mode_bn'], $this->lang);

            $quoteinfo['bank_interest'] = $quotedata['bank_interest'];    //银行利息
            //银行费用
            $quoteinfo['period_of_validity'] = $quotedata['period_of_validity'];    //报价有效期
            $quoteinfo['exchange_rate'] = $quotedata['exchange_rate'];    //汇率

            $quoteinfo['total_quote_price'] = $quotedata['total_quote_price'];    //商务报出贸易价格合计
            $quoteinfo['total_exw_price'] = $quotedata['total_exw_price'];    //商务报出EXW价格

            $quoteinfo['gross_profit_rate'] = $quotedata['gross_profit_rate'];
            //毛利率
            $quoteinfo['premium_rate'] = $quotedata['premium_rate'];    //保险税率
            $quoteinfo['certification_fee'] = $quotedata['certification_fee'];


            if (in_array($results['status'], ['INQUIRY_CONFIRM', 'INQUIRY_CLOSE', 'MARKET_CONFIRMING', 'MARKET_APPROVING', 'QUOTE_SENT'])) {
                $quoteinfo['total_logi_fee'] = $results['total_logi_fee'];    //物流合计
                $quoteinfo['total_bank_fee'] = $results['total_bank_fee'];
                $quoteinfo['delivery_period'] = $results['delivery_period'];    //交货周期
                $quoteinfo['fund_occupation_rate'] = $results['fund_occupation_rate'];    //占用资金比例
                $quoteinfo['final_total_quote_price'] = $results['total_quote_price'];    //市场报出贸易价格合计
                $quoteinfo['final_total_exw_price'] = $results['total_exw_price'];    //市场报出EWX价格
                $quoteinfo['payment_period'] = $results['payment_period'];    //回款周期
            } else {
                $quoteinfo['total_logi_fee'] = $quotedata['total_logi_fee'];    //物流合计
                $quoteinfo['total_bank_fee'] = $quotedata['total_bank_fee'];
                $quoteinfo['delivery_period'] = $quotedata['delivery_period'];    //交货周期
                $quoteinfo['fund_occupation_rate'] = $quotedata['fund_occupation_rate'];    //占用资金比例
                $quoteinfo['total_quote_price'] = $quoteinfo['final_total_quote_price'] = null;    //市场报出贸易价格合计

                $quoteinfo['final_total_exw_price'] = $quotedata['total_exw_price'];    //市场报出EWX价格
                $quoteinfo['payment_period'] = $quotedata['payment_period'];    //回款周期
            }
            return $quoteinfo;
        } else {
            return [];
        }
    }

    public function Detail($inquiry_id) {

        return $this->where(['inquiry_id' => $inquiry_id, 'deleted_flag' => 'N'])->find();
    }

    public function updateGeneralInfo(array $condition, $data) {

        try {
            $this->startTrans();
            $falg = $this->where($condition)
                    ->save($this->create($data));

            if ($falg === false) {
                $this->rollback();

                return [
                    'code' => -1,
                    'message' => '新建报价失败!'
                ];
            }
            $error = '';
            //处理计算相关逻辑
            $flag = $this->calculate($condition, $error);

            if ($flag === false) {
                $this->rollback();

                return [
                    'code' => -1,
                    'message' => '处理计算相关逻辑失败!' . $error
                ];
            }
            $this->commit();
            return [
                'code' => 1,
                'message' => L('QUOTE_SUCCESS')
            ];
        } catch (Exception $exception) {

            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 处理所有计算相关逻辑
     * @param $condition    条件
     * @return bool
     */
    private function calculate($condition, &$error = null) {

        $quoteItemModel = new QuoteItemModel();
        $exchangeRateModel = new ExchangeRateModel();

        $where = $condition;
        $where['deleted_flag'] = 'N';

        /*
          |--------------------------------------------------------------------------
          | 计算商务报出EXW单价         计算公式 : EXW单价=采购单价*毛利率/汇率
          |--------------------------------------------------------------------------
         */
        $quoteInfo = $this->where($condition)
                ->field('id,gross_profit_rate,exchange_rate')
                ->find();
        $gross_profit_rate = $quoteInfo['gross_profit_rate']; //毛利率

        $quoteItemIds = $quoteItemModel
                ->where($where)
                ->field('id,purchase_unit_price,purchase_price_cur_bn,reason_for_no_quote')
                ->select();


        if (!empty($quoteItemIds)) {
            foreach ($quoteItemIds as $key => $value) {


                if (empty($value['reason_for_no_quote']) && !empty($value['purchase_unit_price'])) {

                    if (!in_array($value['purchase_price_cur_bn'], ['CNY', 'USD', 'EUR'])) {
                        $error = '报价商品币种选择错误,请重新选择!';
                        return false;
                    }

                    $exchange_rate = $exchangeRateModel->getRateToUSD($value['purchase_price_cur_bn'], $error);


                    if (empty($exchange_rate)) {
                        $error = $value['purchase_price_cur_bn'] . '兑USD汇率不存在';
                        return false;
                    } else {
                        $exw_unit_price = $value['purchase_unit_price'] * (($gross_profit_rate / 100) + 1) * $exchange_rate;
                    }
                    //毛利率改为：$gross_profit_rate->(($gross_profit_rate/100)+1)
                    $exw_unit_price = sprintf("%.8f", $exw_unit_price);

                    $flag = $quoteItemModel->where(['id' => $value['id']])->save([
                        'exw_unit_price' => $exw_unit_price
                    ]);
                    if ($flag === false) {
                        $error = '计算报出EXW价格失败!';
                        return false;
                    }
                }
            }
        }

        /*
          |--------------------------------------------------------------------------
          | 计算商务报出EXW总价        计算公式 : EXW总价=EXW单价*条数*数量
          |--------------------------------------------------------------------------
         */
        $quoteItemExwUnitPrices = $quoteItemModel
                ->where($where)
                ->field('exw_unit_price,quote_qty,gross_weight_kg')
                ->select();
        if (!empty($quoteItemExwUnitPrices)) {
            $total_exw_price_arr = [];
            foreach ($quoteItemExwUnitPrices as $price) {
                $total_exw_price_arr[] = $price['exw_unit_price'] * $price['quote_qty'];
            }
            $total_exw_price = array_sum($total_exw_price_arr);

            $total_gross_weight_kg_arr = [];
            foreach ($quoteItemExwUnitPrices as $price) {
                $total_gross_weight_kg_arr[] = $price['gross_weight_kg'] * $price['quote_qty'];
            }
            $total_gross_weight_kg = array_sum($total_gross_weight_kg_arr);
        } else {
            $total_exw_price = 0;
            $total_gross_weight_kg = 0;
        }
        $flag = $this->where($condition)->save([
            //总重
            'total_weight' => $total_gross_weight_kg,
            //exw合计
            'total_exw_price' => $total_exw_price
        ]);

        if ($flag === false) {
            $error = '计算商务报出EXW价格合计 和 总重出错!';
            return false;
        }
        /*
          |--------------------------------------------------------------------------
          | 采购合计          计算公式 : 采购总价=采购单价*条数
          |--------------------------------------------------------------------------
         */
        $totalPurchase = [];
        $quoteItemsData = $quoteItemModel->where($where)->field('purchase_unit_price,purchase_price_cur_bn,quote_qty')->select();

        foreach ($quoteItemsData as $quote => $item) {
            if (!in_array($item['purchase_price_cur_bn'], ['CNY', 'USD', 'EUR'])) {
                $error = '币种错误!';
                return false;
            }
            $exchange_rate = $exchangeRateModel->getRateToUSD($item['purchase_price_cur_bn'], $error);
            if (empty($exchange_rate)) {
                $error = $item['purchase_price_cur_bn'] . '兑USD汇率不存在';
                return false;
            } else {
                $totalPurchase[] = round($item['purchase_unit_price'] * $item['quote_qty'] * $exchange_rate, 16);
            }
        }


        return $this->where($condition)->save(['total_purchase' => array_sum($totalPurchase), 'purchase_cur_bn' => 'USD']);
    }

}
