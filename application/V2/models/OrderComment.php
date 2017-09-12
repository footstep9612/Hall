<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 * @author  zhongyg
 * @date    2017-9-12 13:09:26
 * @version V2.0
 * @desc
 */
class OrderCommentModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_comment';
    protected $dbName = 'erui2_order'; //数据库名称

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {

        $where = [];

        $this->_getValue($where, $condition, 'order_id'); //平台订单号
    }

    /* 获取订单列表
     * @param array $condition // 查询条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getList($condition) {

        $where = $this->_getCondition($condition);

        return $this->field('id,order_id,content,comment_group,is_read,created_at, created_by')
                        ->where($where)
                        ->order('created_at asc')
                        ->select();
    }

}
