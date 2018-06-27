<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuyerSource
 * @author  zhongyg
 * @date    2018-4-22 10:28:07
 * @version V2.0
 * @desc
 */
class BuyerSourceModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer_source';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.buyer_source';

    public function __construct() {
        parent::__construct();
    }

    public function getBuyerIdByToken($token) {

        return $this->where(['token' => $token])->getField('buyer_id');
    }

    public function create_data($buyer_id, $introduction_source) {
        $data['buyer_id'] = $buyer_id;
        $data['source'] = $introduction_source;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['token'] = md5('created_at=' . $data['created_at'] . '&buyer_id=' . $buyer_id . '&source=' . $introduction_source);

        return [$this->add($data), $data['token']];
    }

    public function update_sendmail($buyer_id) {
        $where['buyer_id'] = $buyer_id;
        return $this->where($where)->save(['is_send_mail' => 'Y']);
    }

    public function update_questionnaire($buyer_id) {
        $where['buyer_id'] = $buyer_id;
        return $this->where($where)->save(['is_questionnaire' => 'Y']);
    }

}
