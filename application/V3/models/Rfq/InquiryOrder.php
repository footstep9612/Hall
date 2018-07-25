<?php

/**
 * name: InquiryAttach
 * desc: 询价单附件表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:14
 */
class Rfq_InquiryOrderModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_order'; //数据表表名

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

    public function setContractNo(&$arr) {
        if ($arr) {

            $inquiry_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['id']) && $val['id']) {
                    $inquiry_ids[] = $val['id'];
                }
            }
            $inquiry_orders = $this->where(['inquiry_id' => ['in', $inquiry_ids]])
                            ->field('inquiry_id,contract_no')->select();
            $contract_nos = [];
            foreach ($inquiry_orders as $inquiry_order) {
                $contract_nos[$inquiry_order['inquiry_id']] = $inquiry_order['logi_quote_flag'];
            }
            foreach ($arr as $key => $val) {
                if ($val['id'] && isset($contract_nos[$val['id']])) {
                    $val['contract_no'] = $contract_nos[$val['id']];
                } else {
                    $val['contract_no'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

}
