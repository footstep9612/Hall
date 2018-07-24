<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Rfq_QuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote';

    public function __construct() {
        parent::__construct();
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setLogiQuoteFlag(&$arr) {
        if ($arr) {

            $inquiry_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['id']) && $val['id']) {
                    $inquiry_ids[] = $val['id'];
                }
            }
            $logi_quote_flags = [];
            $quotes = $this->where(['inquiry_id' => ['in', $inquiry_ids]])
                            ->field('inquiry_id,logi_quote_flag')->select();
            foreach ($quotes as $quote) {
                $logi_quote_flags[$quote['inquiry_id']] = $quote['logi_quote_flag'];
            }
            foreach ($arr as $key => $val) {
                if ($val['id'] && isset($logi_quote_flags[$val['id']])) {
                    $val['logi_quote_flag'] = $logi_quote_flags[$val['id']];
                } else {
                    $val['logi_quote_flag'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

}
