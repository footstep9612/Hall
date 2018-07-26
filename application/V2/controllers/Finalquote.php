<?php

/**
 * name: Finalquote.php
 * desc: 市场报价单控制器
 * User: 张玉良
 * Date: 2017/8/3
 * Time: 10:55
 */
class FinalquoteController extends PublicController {

    public function init() {
        parent::init();
    }

    /*
     * 市场报价单详情
     * Author:张玉良
     */

    public function getInfoAction() {

        $finalquote = new FinalQuoteModel();

        $where = $this->put_data;

        //获取市场报价单详细信息
        $quotewhere['inquiry_id'] = $where['id'];
        $results = $finalquote->getInfo($quotewhere);
        unset($finalquote);
        if ($results['code'] == 1) {
            //获取询单基本信息
            $inquiry_model = new Rfq_InquiryModel();
            $inquiryinfo = $inquiry_model->info($where);
            unset($inquiry_model);
            if (isset($inquiryinfo)) {
                //当前用户姓名
                $inquiryinfo['user_name'] = $this->user['name'];
                //更改询单状态
                $results['data']['status'] = $inquiryinfo['inquiry_status'];
                $results['data'] = array_merge($results['data'], $inquiryinfo);
            }
            //获取综合报价信息
            $quoteModel = new Rfq_QuoteModel();
            $quoteinfo = $quoteModel->info($where);

            unset($quoteModel);
            if ($quoteinfo) {

                $results['quotedata'] = $quoteinfo;
            }

            //获取物流报价信息
            $quotetlogifee_model = new Rfq_QuoteLogiFeeModel();
            $quoteLogiFee = $quotetlogifee_model->info($quotewhere);
            unset($quotetlogifee_model);
            if ($quoteLogiFee) {
                $results['logidata'] = $quoteLogiFee;
            }
        }

        $this->jsonReturn($results);
    }

    /**
     * 修改市场报价单
     * Author:张玉良
     */
    public function updateAction() {
        $final = new FinalQuoteModel();

        $data = $this->put_data;

        //根据修改市场报出EXW单价计算
        $total_exw_price = $total_quote_price = 0;
        if (!empty($data['sku'])) {
            foreach ($data['sku'] as $val) {
                if ($val['final_exw_unit_price'] > 0) {
                    $exw_price = $val['quote_qty'] * $val['final_exw_unit_price'];  //市场报出EXW价格
                    $total_exw_price += $exw_price;     //市场报出EXW价格合计
                }
            }

            //计算
            if ($total_exw_price > 0) {
                $logiwhere['inquiry_id'] = $data['id'];
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
                $logidata['port_surcharge_cur'] = $quoteLogiFee['port_surcharge_cur'];  //港杂费币种
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
                $logidata['certification_fee'] = $data['certification_fee'];
                $logidata['certification_fee_cur'] = 'CNY';

                $computedata = $logistics->calcuTotalLogiFee($logidata);

                $total_quote_price = $computedata['total_quote_price']; //市场报出贸易价格合计
            }

            //计算报出冒出贸易单价    quote_unit_price
            $finalitem = new FinalQuoteItemModel();
            $finalitem->startTrans();

            foreach ($data['sku'] as $val) {
                if ($val['final_exw_unit_price'] > 0) {
                    $quote_unit_price = $total_quote_price * $val['final_exw_unit_price'] / $total_exw_price; //报出贸易单价

                    $itemdata['id'] = $val['id'];
                    $itemdata['exw_unit_price'] = round($val['final_exw_unit_price'], 8);
                    $itemdata['quote_unit_price'] = round($quote_unit_price, 8);

                    $itemrs = $this->updateItemAction($itemdata);

                    if ($itemrs['code'] != 1) {
                        $finalitem->rollback();
                        $this->jsonReturn('', '-101', L('FINAL_QUOTE_UPDATE_EXW_FAIL'));
                        die;
                    }
                }
            }

            $finaldata['inquiry_id'] = $data['id'];
            $finaldata['payment_period'] = $data['payment_period'];     //回款周期
            $finaldata['delivery_period'] = $data['delivery_period'];
            $finaldata['fund_occupation_rate'] = $data['fund_occupation_rate']; //赊销比例
            if ($total_exw_price > 0) {
                $finaldata['total_exw_price'] = $total_exw_price;   //市场报出EXW价格合计
            }
            if ($total_quote_price > 0) {
                $finaldata['total_quote_price'] = $total_quote_price;   //市场报出贸易价格合计
            }
            if ($computedata['total_logi_fee'] > 0) {
                $finaldata['total_logi_fee'] = $computedata['total_logi_fee'];   //物流费用合计
            }
            if ($computedata['total_bank_fee'] > 0) {
                $finaldata['total_bank_fee'] = $computedata['total_bank_fee'];   //银行费用
            }
            if ($computedata['total_insu_fee'] > 0) {
                $finaldata['total_insu_fee'] = $computedata['total_insu_fee'];   //出口信用保险费用
            }
            $finaldata['updated_by'] = $this->user['id'];

            $results = $final->updateFinal($finaldata);
            if ($results['code'] == 1) {
                $finalitem->commit();
                $this->jsonReturn($results);
                die;
            } else {
                $finalitem->rollback();
                $this->jsonReturn('', '-101', L('FAIL'));
                die;
            }
        }
    }

    /*
     * 批量修改市场报价单状态
     * Author:张玉良
     */

    public function updateStatusAction() {
        $finalquote = new FinalQuoteModel();
        $inquiry = new InquiryModel();
        $quote = new QuoteModel();
        $data = $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $finalquote->startTrans();
        $results = $finalquote->updateFinalStatus($data);
        if ($results['code'] == 1) {
            $inquirywhere['id'] = $data['inquiry_id'];
            $inquirywhere['status'] = $data['status'];
            $inquirydata = $inquiry->updateStatus($inquirywhere);
            if ($inquirydata['code'] == 1) {
                $quotedata = $quote->updateQuoteStatus($data);
                if ($quotedata['code'] == 1) {
                    $finalquote->commit();
                } else {
                    $finalquote->rollback();
                    $results['code'] = $quotedata['code'];
                    $results['message'] = $quotedata['message'];
                }
            } else {
                $finalquote->rollback();
                $results['code'] = $inquirydata['code'];
                $results['message'] = $inquirydata['message'];
            }
        } else {
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
        $data = $this->put_data;

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

}
