<?php
/**
 * name: Finalquote.php
 * desc: 市场报价单控制器
 * User: 张玉良
 * Date: 2017/8/3
 * Time: 10:55
 */
class FinalquoteController extends PublicController {

    public function init()
    {
        parent::init();
    }

    /*
     * 市场报价单详情
     * Author:张玉良
     */
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $finalquote = new FinalQuoteModel();
        $employee = new EmployeeModel();
        $quoteModel = new QuoteModel();
        $where = $this->put_data;

        //获取市场报价单详细信息
        $quotewhere['inquiry_id'] = $where['id'];
        $results = $finalquote->getInfo($quotewhere);

        if($results['code'] == 1){

            //获取询单基本信息
            $inquirywhere['id'] = $where['id'];
            $inquiryinfo = $inquiry->field('serial_no,pm_id')->where($inquirywhere)->find();

            if(isset($inquiryinfo)){

                //查询项目经理名称
                $rs = $employee->field('name')->where('id='.$inquiryinfo['pm_id'])->find();
                $inquiryinfo['pm_name'] = $rs['name'];

                //当前用户姓名
                $inquiryinfo['user_name'] = $this->user['name'];

                $results['data'] =  array_merge($results['data'],$inquiryinfo);
            }

            //获取综合报价信息

            $fields = 'total_weight,package_volumn,package_mode,payment_mode,trade_terms_bn,payment_period,from_country,to_country,trans_mode_bn,bank_interest,period_of_validity,exchange_rate,total_quote_price,total_exw_price';

            $quotedata = $quoteModel->field($fields)->where('inquiry_id='.$quotewhere['inquiry_id'])->find();

            if(!empty($quotedata)){
                //追加结果
                $quoteinfo['total_weight'] = $quotedata['total_weight'];    //总重
                $quoteinfo['package_volumn'] = $quotedata['package_volumn'];    //包装总体积
                $quoteinfo['package_mode'] = $quotedata['package_mode'];    //包装方式
                $quoteinfo['payment_mode'] = $quotedata['payment_mode'];    //付款方式
                $quoteinfo['trade_terms_bn'] = $quotedata['trade_terms_bn'];    //贸易术语
                $quoteinfo['payment_period'] = $results['data']['payment_period'];    //回款周期
                $quoteinfo['from_country'] = $quotedata['dispatch_place'];    //起始发运地
                $quoteinfo['to_country'] = $quotedata['delivery_addr'];    //目的地
                $quoteinfo['trans_mode_bn'] = $quotedata['trans_mode_bn'];    //运输方式
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

                $results['quotedata'] = $quoteinfo;
            }

            //获取物流报价信息
            $quotetlogifee = new QuoteLogiFeeModel();
            $quoteLogiFee = $quotetlogifee->getJoinDetail($quotewhere);

            if (!empty($quoteLogiFee)) {

                $landFreightUSD = $quoteLogiFee['land_freight'] * $this->_getRateUSD($quoteLogiFee['land_freight_cur']);
                $portSurchargeUSD = $quoteLogiFee['port_surcharge'] * $this->_getRateUSD($quoteLogiFee['port_surcharge_cur']);
                $inspectionFeeUSD = $quoteLogiFee['inspection_fee'] * $this->_getRateUSD($quoteLogiFee['inspection_fee_cur']);
                $interShippingUSD = $quoteLogiFee['inter_shipping'] * $this->_getRateUSD($quoteLogiFee['inter_shipping_cur']);

                $quoteLogiFee['land_freight_usd'] = round($landFreightUSD, 4);
                $quoteLogiFee['port_surcharge_usd'] = round($portSurchargeUSD, 4);
                $quoteLogiFee['inspection_fee_usd'] = round($inspectionFeeUSD, 4);
                $quoteLogiFee['inter_shipping_usd'] = round($interShippingUSD, 4);

                $quoteLogiFee['dest_delivery_fee_usd'] = round($quoteLogiFee['dest_delivery_fee'] * $this->_getRateUSD($quoteLogiFee['dest_delivery_fee_cur']), 4);
                $quoteLogiFee['dest_clearance_fee_usd'] = round($quoteLogiFee['dest_clearance_fee'] * $this->_getRateUSD($quoteLogiFee['dest_clearance_fee_cur']), 4);

                $overlandInsu = $quoteLogiFee['total_exw_price'] * 1.1 * $quoteLogiFee['overland_insu_rate'] / 100;

                $quoteLogiFee['overland_insu'] = round($overlandInsu, 4);
                $quoteLogiFee['shipping_insu'] = round($quoteLogiFee['total_quote_price'] * 1.1 * $quoteLogiFee['shipping_insu_rate'] / 100, 4);

                $tmpTotalFee = $quoteLogiFee['total_exw_price'] + $landFreightUSD + $overlandInsu + $portSurchargeUSD + $inspectionFeeUSD + $interShippingUSD;

                $quoteLogiFee['dest_tariff_fee'] = round($tmpTotalFee * $quoteLogiFee['dest_tariff_rate'] / 100, 4);
                $quoteLogiFee['dest_va_tax_fee'] = round($tmpTotalFee * (1 + $quoteLogiFee['dest_tariff_rate'] / 100) * $quoteLogiFee['dest_va_tax_rate'] / 100, 4);

                $results['logidata'] = $quoteLogiFee;
            }
        }

        $this->jsonReturn($results);
    }

    /**
     * 修改市场报价单
     * Author:张玉良
     */
    public function updateAction(){
        $final = new FinalQuoteModel();

        $data =  $this->put_data;

        //根据修改市场报出EXW单价计算
        $total_exw_price = $total_quote_price = 0;
        if(!empty($data['sku'])){
            foreach($data['sku'] as $val){
                if($val['final_exw_unit_price']>0) {
                    $exw_price = $val['quote_qty'] * $val['final_exw_unit_price'];  //市场报出EXW价格
                    $total_exw_price += $exw_price;     //市场报出EXW价格合计
                }
            }

            //计算
            if($total_exw_price>0){
                $logiwhere['inquiry_id'] = $data['inquiry_id'];
                $quotetlogifee = new QuoteLogiFeeModel();
                $quoteLogiFee = $quotetlogifee->getDetail($logiwhere);

                $logistics = new LogisticsController();
                $logidata['trade_terms_bn'] = $data['trade_terms_bn'];  //贸易术语
                $logidata['total_exw_price'] = $total_exw_price;  //报出EXW合计
                $logidata['premium_rate'] = !empty($data['premium_rate']) ? $data['premium_rate'] : 0;  //保险税率
                $logidata['payment_period'] = $data['payment_period'];  //回款周期
                $logidata['bank_interest'] = $data['bank_interest'];  //银行利息
                $logidata['fund_occupation_rate'] = $data['fund_occupation_rate'];  //资金占用比例
                $logidata['inspection_fee'] = $quoteLogiFee['inspection_fee'];  //商检费
                $logidata['inspection_fee_cur'] = $quoteLogiFee['inspection_fee_cur'];  //商检费币种
                $logidata['land_freight'] = $quoteLogiFee['land_freight'];  //陆运费
                $logidata['land_freight_cur'] = $quoteLogiFee['land_freight_cur'];  //陆运费币种
                $logidata['port_surcharge'] = $quoteLogiFee['port_surcharge'];  //港杂费
                $logidata['port_surcharge_cur'] =$quoteLogiFee['port_surcharge_cur'];  //港杂费币种
                $logidata['inter_shipping'] = $quoteLogiFee['inter_shipping'];  //国际运费
                $logidata['inter_shipping_cur'] = $quoteLogiFee['inter_shipping_cur'];  //国际运费币种
                $logidata['dest_delivery_fee'] = $quoteLogiFee['dest_delivery_fee'];  //目的地配送费
                $logidata['dest_delivery_fee_cur'] = $quoteLogiFee['dest_delivery_fee_cur'];  //目的地配送费币种
                $logidata['dest_clearance_fee'] = $quoteLogiFee['dest_clearance_fee'];  //目的地清关费
                $logidata['dest_clearance_fee_cur'] = $quoteLogiFee['dest_clearance_fee_cur'];  //目的地清关费币种
                $logidata['overland_insu_rate'] = $quoteLogiFee['overland_insu_rate'];  //陆运险率
                $logidata['shipping_insu_rate'] = $quoteLogiFee['shipping_insu_rate'];  //国际运输险率
                $logidata['dest_tariff_rate'] = $quoteLogiFee['dest_tariff_rate'];  //目的地关税税率
                $logidata['dest_va_tax_rate'] = $quoteLogiFee['dest_va_tax_rate'];  //目的地增值税率

                $computedata = $logistics->calcuTotalLogiFee($logidata);

                $total_quote_price = $computedata['total_quote_price']; //市场报出贸易价格合计
            }

            //计算报出冒出贸易单价    quote_unit_price
            $finalitem = new FinalQuoteItemModel();
            $finalitem->startTrans();

            foreach($data['sku'] as $val){
                if($val['final_exw_unit_price']>0){
                    $quote_unit_price = $total_quote_price*$val['final_exw_unit_price']/$total_exw_price;//报出贸易单价

                    $itemdata['id'] = $val['id'];
                    $itemdata['exw_unit_price'] = round($val['final_exw_unit_price'],4);
                    $itemdata['quote_unit_price'] = round($quote_unit_price,4);

                    $itemrs = $this->updateItemAction($itemdata);

                    if($itemrs['code'] != 1){
                        $finalitem->rollback();
                        $this->jsonReturn('','-101','修改报价EXW价格失败！');die;
                    }
                }
            }

            $finaldata['inquiry_id'] = $data['id'];
            $finaldata['payment_period'] = $data['payment_period'];
            $finaldata['delivery_period'] = $data['delivery_period'];
            $finaldata['fund_occupation_rate'] = $data['fund_occupation_rate'];
            if($total_exw_price>0){
                $finaldata['total_exw_price'] =$total_exw_price;   //市场报出EXW价格合计
            }
            if($total_quote_price>0) {
                $finaldata['total_quote_price'] = $total_quote_price;   //市场报出贸易价格合计
            }
            if($computedata['total_logi_fee']>0){
                $finaldata['total_logi_fee'] = $computedata['total_logi_fee'];   //物流费用合计
            }
            if($computedata['total_bank_fee']>0){
                $finaldata['total_bank_fee'] = $computedata['total_bank_fee'];   //银行费用
            }
            if($computedata['total_insu_fee']>0){
                $finaldata['total_insu_fee'] = $computedata['total_insu_fee'];   //出口信用保险费用
            }
            $finaldata['updated_by'] = $this->user['id'];

            $results = $final->updateFinal($finaldata);
            if($results['code'] == 1){
                $finalitem->commit();
                $this->jsonReturn($results);die;
            }else{
                $finalitem->rollback();
                $this->jsonReturn('','-101','修改报价单失败！');die;
            }
        }
    }

    /*
     * 批量修改市场报价单状态
     * Author:张玉良
     */
    public function updateStatusAction(){
        $finalquote = new FinalQuoteModel();
        $inquiry = new InquiryModel();
        $quote = new QuoteModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $finalquote->startTrans();
        $results = $finalquote->updateFinalStatus($data);
        if($results['code'] == 1){
            $inquirywhere['id'] = $data['inquiry_id'];
            $inquirywhere['status'] = $data['status'];
            $inquirydata = $inquiry->updateStatus($inquirywhere);
            if($inquirydata['code'] == 1){
                $quotedata = $quote->updateQuoteStatus($data);
                if($quotedata['code'] == 1){
                    $finalquote->commit();
                }else{
                    $finalquote->rollback();
                    $results['code'] = $quotedata['code'];
                    $results['message'] = $quotedata['message'];
                }
            }else{
                $finalquote->rollback();
                $results['code'] = $inquirydata['code'];
                $results['message'] = $inquirydata['message'];
            }
        }else{
            $finalquote->rollback();
        }
        $this->jsonReturn($results);
    }

    /**
     * 市场报价单SKU列表
     * Author:张玉良
     */
    public function getItemListAction() {
        $finalitem = new FinalQuoteItemModel();
        $data =  $this->put_data;

        $results = $finalitem->getItemList($data);
        $this->jsonReturn($results);
    }

    /**
     * 修改市场报价单SKU
     * Author:张玉良
     */
    public function updateItemAction($condition = []) {
        $finalitem = new FinalQuoteItemModel();

        $condition['updated_by'] = $this->user['id'];

        $results = $finalitem->updateItem($condition);

        return $results;
    }

    /**
     * 获取币种兑换美元汇率
     * Author:张玉良
     */
    private function _getRateUSD($cur) {

        return $this->_getRate($cur,'USD');
    }

    /**
     * 获取币种兑换汇率
     * Author:张玉良
     */
    private function _getRate($cur, $exchangeCur = 'CNY') {

        if (!empty($cur)) {
            if ($cur == $exchangeCur) return 1;

            $exchangeRateModel = new ExchangeRateModel();
            $exchangeRate = $exchangeRateModel->field('rate')->where(['cur_bn1' => $cur, 'cur_bn2' => $exchangeCur])->order('created_at DESC')->find();

            return $exchangeRate['rate'];
        } else {
            return false;
        }

    }
}