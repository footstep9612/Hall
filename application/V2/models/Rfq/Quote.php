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

    public function info($where, $results) {

        $fields = 'total_purchase,quote_remarks,total_weight,package_volumn,package_mode,'
                . 'payment_mode,trade_terms_bn,payment_period,from_country,to_country,'
                . 'from_port,to_port,trans_mode_bn,bank_interest,period_of_validity,'
                . 'exchange_rate,total_quote_price,total_exw_price,dispatch_place,delivery_addr,'
                . 'logi_quote_flag,certification_fee,gross_profit_rate,premium_rate';
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
            $quoteinfo['payment_period'] = $results['data']['payment_period'];    //回款周期
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
            $quoteinfo['delivery_period'] = $results['data']['delivery_period'];    //交货周期
            $quoteinfo['fund_occupation_rate'] = $results['data']['fund_occupation_rate'];    //占用资金比例
            $quoteinfo['bank_interest'] = $quotedata['bank_interest'];    //银行利息
            $quoteinfo['total_bank_fee'] = $results['data']['total_bank_fee'];    //银行费用
            $quoteinfo['period_of_validity'] = $quotedata['period_of_validity'];    //报价有效期
            $quoteinfo['exchange_rate'] = $quotedata['exchange_rate'];    //汇率
            $quoteinfo['total_logi_fee'] = $results['data']['total_logi_fee'];    //物流合计
            $quoteinfo['total_quote_price'] = $quotedata['total_quote_price'];    //商务报出贸易价格合计
            $quoteinfo['total_exw_price'] = $quotedata['total_exw_price'];    //商务报出EXW价格
            $quoteinfo['final_total_quote_price'] = $results['data']['total_quote_price'];    //市场报出贸易价格合计
            $quoteinfo['final_total_exw_price'] = $results['data']['total_exw_price'];    //市场报出EWX价格
            $quoteinfo['gross_profit_rate'] = $quotedata['gross_profit_rate'];
            //毛利率
            $quoteinfo['premium_rate'] = $quotedata['premium_rate'];    //保险税率
            $quoteinfo['certification_fee'] = $quotedata['certification_fee'];

            return $quoteinfo;
        } else {
            return [];
        }
    }

}
