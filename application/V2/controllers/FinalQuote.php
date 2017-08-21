<?php
/**
 * name: FinalQuote.php
 * desc: 市场报价单控制器
 * User: 张玉良
 * Date: 2017/8/3
 * Time: 10:55
 */
class FinalQuoteController extends PublicController {

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
            $quotebizline = new QuotebizlineController();
            $quotedata = $quotebizline->quoteGeneralInfoAction();

            if($quotedata['code']==1){
                //追加结果
                $quoteinfo['total_weight'] = $quotedata['data']['total_weight'];    //总重
                $quoteinfo['package_volumn'] = $quotedata['data']['package_volumn'];    //包装总体积
                $quoteinfo['package_mode'] = $quotedata['data']['package_mode'];    //包装方式
                $quoteinfo['payment_mode'] = $quotedata['data']['payment_mode'];    //付款方式
                $quoteinfo['trade_terms_bn'] = $quotedata['data']['trade_terms_bn'];    //贸易术语
                $quoteinfo['payment_period'] = $results['data']['payment_period'];    //回款周期
                $quoteinfo['from_country'] = $quotedata['data']['from_country'];    //起始发运地
                $quoteinfo['to_country'] = $quotedata['data']['to_country'];    //目的地
                $quoteinfo['trans_mode_bn'] = $quotedata['data']['trans_mode_bn'];    //运输方式
                $quoteinfo['delivery_period'] = $results['data']['delivery_period'];    //交货周期
                $quoteinfo['fund_occupation_rate'] = $results['data']['fund_occupation_rate'];    //占用资金比例
                $quoteinfo['bank_interest'] = $quotedata['data']['bank_interest'];    //银行利息
                $quoteinfo['total_bank_fee'] = $results['data']['total_bank_fee'];    //银行费用
                $quoteinfo['period_of_validity'] = $quotedata['data']['period_of_validity'];    //报价有效期
                $quoteinfo['exchange_rate'] = $quotedata['data']['exchange_rate'];    //汇率
                $quoteinfo['total_logi_fee'] = $results['data']['total_logi_fee'];    //物流合计
                $quoteinfo['total_quote_price'] = $quotedata['data']['total_quote_price'];    //商务报出贸易价格合计
                $quoteinfo['total_exw_price'] = $quotedata['data']['total_exw_price'];    //商务报出EXW价格
                $quoteinfo['final_total_quote_price'] = $results['data']['total_quote_price'];    //市场报出贸易价格合计
                $quoteinfo['final_total_exw_price'] = $results['data']['total_exw_price'];    //市场报出EWX价格

                $results['quotedata'] = $quoteinfo;
            }

            //获取物流报价信息
            $logistics = new LogisticsController();
            $logidata = $logistics->getQuoteLogiFeeDetailAction();

            if($logidata['code']==1){
                //追加结果
                $results['logidata'] = $logidata['data'];
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
                $exw_price = $val['quote_qty']*$val['exw_unit_price'];  //市场报出EXW价格
                $total_exw_price += $exw_price;     //市场报出EXW价格合计
            }

            //计算
            if($total_exw_price>0){
                $logistics = new LogisticsController();
                $logidata['trade_terms_bn'] = $data['trade_terms_bn'];  //贸易术语
                $logidata['total_exw_price'] = $total_exw_price;  //报出EXW合计
                $logidata['premium_rate'] = $data['premium_rate'];  //保险税率
                $logidata['payment_period'] = $data['payment_period'];  //回款周期
                $logidata['bank_interest'] = $data['bank_interest'];  //银行利息
                $logidata['fund_occupation_rate'] = $data['fund_occupation_rate'];  //资金占用比例
                $logidata['inspection_fee'] = $data['inspection_fee'];  //商检费
                $logidata['inspection_fee_cur'] = 'USD';  //商检费币种
                $logidata['land_freight'] = $data['land_freight'];  //陆运费
                $logidata['land_freight_cur'] = 'USD';  //陆运费币种
                $logidata['port_surcharge'] = $data['port_surcharge'];  //港杂费
                $logidata['port_surcharge_cur'] = 'USD';  //港杂费币种
                $logidata['inter_shipping'] = $data['inter_shipping'];  //国际运费
                $logidata['inter_shipping_cur'] = 'USD';  //国际运费币种
                $logidata['dest_delivery_fee'] = $data['dest_delivery_fee'];  //目的地配送费
                $logidata['dest_delivery_fee_cur'] = 'USD';  //目的地配送费币种
                $logidata['dest_clearance_fee'] = $data['dest_clearance_fee'];  //目的地清关费
                $logidata['dest_clearance_fee_cur'] = 'USD';  //目的地清关费币种
                $logidata['overland_insu_rate'] = $data['overland_insu_rate'];  //陆运险率
                $logidata['shipping_insu_rate'] = $data['shipping_insu_rate'];  //国际运输险率
                $logidata['dest_tariff_rate'] = $data['dest_tariff_rate'];  //目的地关税税率
                $logidata['dest_va_tax_rate'] = $data['dest_va_tax_rate'];  //目的地增值税率

                $computedata = $logistics->calcuTotalLogiFee($logidata);

                $total_quote_price = $computedata['total_quote_price']; //市场报出贸易价格合计
            }

            //计算报出冒出贸易单价    quote_unit_price
            $finalitem = new FinalQuoteItemModel();
            $finalitem->startTrans();
            foreach($data['sku'] as $val){
                $exw_price = $val['quote_qty']*$val['exw_unit_price'];  //市场报出EXW价格
                $quote_unit_price = $total_quote_price*$exw_price/$total_exw_price;//报出贸易单价

                $itemdata['id'] = $val['id'];
                $itemdata['exw_unit_price'] = $val['exw_unit_price'];
                $itemdata['quote_unit_price'] = $quote_unit_price;

                $itemrs = $this->updateItemAction($itemdata);

                if($itemrs['code'] != 1){
                    $finalitem->rollback();
                    $this->jsonReturn('','-101','修改报价EXW价格失败！');die;
                }
            }
            $finalitem->commit();
        }



        //把修改更新到市场报价单表
        $finaldata['id'] = $data['id']; //市场报价单ID
        $finaldata['payment_period'] =$data['payment_period'];    //回款周期
        $finaldata['delivery_period'] =$data['delivery_period'];   //交货周期
        $finaldata['fund_occupation_rate'] =$data['fund_occupation_rate'];  //占用资金比例
        if($total_exw_price>0){
            $finaldata['total_exw_price'] =$total_exw_price;   //市场报出EXW价格合计
        }
        if($total_quote_price>0) {
            $finaldata['total_quote_price'] = $total_quote_price;   //市场报出贸易价格合计
        }
        $finaldata['updated_by'] =$this->user['id'];

        $results = $final->updateFinal($finaldata);
        $this->jsonReturn($results);
    }

    /*
     * 批量修改市场报价单状态
     * Author:张玉良
     */
    public function updateStatusAction(){
        $finalquote = new FinalQuoteModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $finalquote->updateFinalStatus($data);
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
    public function updateItemAction() {
        $finalitem = new FinalQuoteItemModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $finalitem->updateItem($data);
        $this->jsonReturn($results);
    }

}