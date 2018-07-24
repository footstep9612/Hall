<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class Buyer_BuyerModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.buyer';

//    protected $autoCheckFields = false;
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

    public function setBuyerNo(&$arr) {
        if ($arr) {

            $buyer_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['buyer_id']) && $val['buyer_id']) {
                    $buyer_ids[] = $val['buyer_id'];
                }
            }
            $buyer_nos = [];
            if ($buyer_ids) {
                $buyers = $this->field('id,buyer_no')->where(['id' => ['in', $buyer_ids]])->select();
                foreach ($buyers as $buyer) {
                    $buyer_nos[$buyer['id']] = $buyer['buyer_no'];
                }
            }
            foreach ($arr as $key => $val) {

                if ($val['buyer_id'] && isset($buyer_nos[$val['buyer_id']])) {
                    $val['buyer_no'] = $buyer_nos[$val['buyer_id']];
                } else {
                    $val['buyer_no'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /**
     * @desc 根据用户姓名获取ID
     *
     * @param string $buyer_no
     * @return array
     * @author liujf
     * @time 2017-11-29
     */
    public function getBuyerIdByBuyerNo($buyer_no) {

        return $this->where(['buyer_no' => ['like', '%' . trim($buyer_no) . '%'], 'deleted_flag' => 'N'])->getField('id', true);
    }

}
