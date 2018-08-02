<?php

/**
 * name: FinalQuote.php
 * desc: 市场报价单控制器
 * User: 张玉良
 * Date: 2017/8/4
 * Time: 10:45
 */
class Rfq_FinalQuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote';

    public function __construct() {
        parent::__construct();
    }

    public function submit($inquiry_id) {
        $quoteModel = new Rfq_QuoteModel();
        $inquiryModel = new Rfq_InquiryModel();
        $finalQuoteItemModel = new FinalQuoteItemModel();
        //验证数据
        $quoteInfo = $quoteModel->where(['inquiry_id' => $inquiry_id])
                        ->field('id,payment_period,fund_occupation_rate,delivery_period,'
                                . 'total_purchase,total_logi_fee,total_bank_fee,total_exw_price,'
                                . 'total_quote_price,total_insu_fee')->find();


        //判断是否存在数据，如果是退回报价更新数据，如果不是就插入一条数据
        $final = $this->field('id')->where('inquiry_id=' . $inquiry_id)->find();

        if (empty($final)) {
            $flag = $this->add($this->create([
                        'inquiry_id' => $inquiry_id,
                        'buyer_id' => $inquiryModel->where(['id' => $inquiry_id])->getField('buyer_id'),
                        'quote_id' => $quoteModel->getQuoteIdByInQuiryId($inquiry_id),
                        'payment_period' => $quoteInfo['payment_period'],
                        'fund_occupation_rate' => $quoteInfo['fund_occupation_rate'],
                        'delivery_period' => $quoteInfo['delivery_period'],
                        'total_purchase' => $quoteInfo['total_purchase'],
                        'total_logi_fee' => $quoteInfo['total_logi_fee'],
                        'total_bank_fee' => $quoteInfo['total_bank_fee'],
                        'total_exw_price' => $quoteInfo['total_exw_price'],
                        'total_quote_price' => $quoteInfo['total_quote_price'],
                        'total_insu_fee' => $quoteInfo['total_insu_fee'],
                        'created_by' => defined(UID) ? UID : 0,
                        'created_at' => date('Y-m-d H:i:s')
            ]));
            if (!$flag) {
                return false;
            }
        } else {
            $flag = $this->where('inquiry_id=' . $inquiry_id)->save($this->create([
                        'inquiry_id' => $inquiry_id,
                        'buyer_id' => $inquiryModel->where(['id' => $inquiry_id])->getField('buyer_id'),
                        'quote_id' => $quoteModel->getQuoteIdByInQuiryId($inquiry_id),
                        'payment_period' => $quoteInfo['payment_period'],
                        'fund_occupation_rate' => $quoteInfo['fund_occupation_rate'],
                        'delivery_period' => $quoteInfo['delivery_period'],
                        'total_purchase' => $quoteInfo['total_purchase'],
                        'total_logi_fee' => $quoteInfo['total_logi_fee'],
                        'total_bank_fee' => $quoteInfo['total_bank_fee'],
                        'total_exw_price' => $quoteInfo['total_exw_price'],
                        'total_quote_price' => $quoteInfo['total_quote_price'],
                        'total_insu_fee' => $quoteInfo['total_insu_fee'],
                        'created_by' => defined(UID) ? UID : 0,
                        'created_at' => date('Y-m-d H:i:s')
            ]));

            if (!$flag) {

                return false;
            }
        }
        $quoteItemModel = new Rfq_QuoteItemModel();
        $quoteItems = $quoteItemModel->where(['inquiry_id' => $inquiry_id, 'deleted_flag' => 'N'])->field('id,inquiry_id,inquiry_item_id,sku,supplier_id,quote_unit_price,exw_unit_price')->select();

        $finalItems = $finalQuoteItemModel->where(['inquiry_id' => $inquiry_id, 'deleted_flag' => 'N'])
                ->getField('quote_item_id', true);
        $quote_id = $quoteModel->getQuoteIdByInQuiryId($inquiry_id);

        foreach ($quoteItems as $quote => $item) {
            if (!in_array($item['id'], $finalItems)) {
                $flag = $finalQuoteItemModel->add($finalQuoteItemModel->create([
                            'quote_id' => $quote_id,
                            'inquiry_id' => $inquiry_id,
                            'inquiry_item_id' => $item['inquiry_item_id'],
                            'quote_item_id' => $item['id'],
                            'sku' => $item['sku'],
                            'supplier_id' => $item['supplier_id'],
                            'quote_unit_price' => $item['quote_unit_price'],
                            'exw_unit_price' => $item['exw_unit_price'],
                            'created_by' => defined(UID) ? UID : 0,
                            'created_at' => date('Y-m-d H:i:s'),
                ]));
                if ($flag === false) {
                    return false;
                }
            } else {
                $flag = $finalQuoteItemModel->where(['quote_item_id' => $item['id']])->save($finalQuoteItemModel->create([
                            'quote_id' => $quote_id,
                            'inquiry_id' => $inquiry_id,
                            'inquiry_item_id' => $item['inquiry_item_id'],
                            'quote_item_id' => $item['id'],
                            'sku' => $item['sku'],
                            'supplier_id' => $item['supplier_id'],
                            'quote_unit_price' => $item['quote_unit_price'],
                            'exw_unit_price' => $item['exw_unit_price'],
                            'created_by' => defined(UID) ? UID : 0,
                            'created_at' => date('Y-m-d H:i:s'),
                ]));

                if ($flag === false) {
                    return false;
                }
            }
        }
    }

}
