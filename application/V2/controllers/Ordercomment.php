<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ordercomment
 * @author  zhongyg
 * @date    2017-9-12 13:08:12
 * @version V2.0
 * @desc
 */
class OrdercommentController extends PublicController {

    public function init() {
        parent::init();
    }

    /* 获取订单列表
     *
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    //put your code here
    public function ListAction() {

        $condition = $this->getPut(); //查询条件
        $condition['order_id'] = 1;
        if (!isset($condition['order_id']) || empty($condition['order_id'])) {
            $this->setCode(MSG::MSG_PARAM_ERROR);
            $this->setMessage('订单ID不能为空');

            $this->jsonReturn(null);
        }

        $oder_comment_moder = new OrderCommentModel();
        $data = $oder_comment_moder->getList($condition);
        $order_model = new OrderModel();
        $order = $order_model->info($condition['order_id']);
        if ($data) {
            $buyerids = [];
            foreach ($data as $key => $comment) {
                if ($comment['comment_group'] === 'B') {
                    $buyerids[] = $comment['created_by'];
                } elseif ($comment['comment_group'] === 'E') {
                    $comment['created_by_name'] = 'Erui';
                }
                $data[$key] = $comment;
            }
            if ($buyerids) {
                $buyer_model = new BuyerAccountModel();
                $buyernames = $buyer_model->getBuyerNamesByBuyerids($buyerids);
            }
            foreach ($data as $key => $val) {
                if ($val['created_by'] && isset($buyernames[$val['created_by']]) && $val['comment_group'] === 'B') {
                    $val['created_by_name'] = $buyernames[$val['created_by']];
                } elseif ($val['comment_group'] === 'B') {
                    $val['created_by_name'] = '';
                }
                $data[$key] = $val;
            }

            if (isset($order['quality']) && $order['quality']) {

                $this->setvalue('quality', $order['quality']);
            } else {
                $this->setvalue('quality', 5);
            }
            if (isset($order['distributed']) && $order['distributed']) {

                $this->setvalue('distributed', $order['distributed']);
            } else {
                $this->setvalue('distributed', 5);
            }
            $this->jsonReturn($data);
        } elseif ($data === null) {

            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

    public function AddAction() {
        $condition = $this->getPut(); //查询条件

        if (!isset($condition['order_id']) || empty($condition['order_id'])) {
            $this->setCode(MSG::MSG_PARAM_ERROR);
            $this->setMessage('订单ID不能为空');

            $this->jsonReturn(null);
        }
        if (!isset($condition['content']) || empty($condition['content'])) {
            $this->setCode(MSG::MSG_PARAM_ERROR);
            $this->setMessage('内容不能为空');
            $this->jsonReturn(null);
        }
        $order_model = new OrderModel();
        $info = $order_model->info($condition['order_id']);
        if ($info['show_status'] !== OrderModel::SHOW_STATUS_COMPLETED) {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('订单完成,不能回复订单!');
            $this->jsonReturn(null);
        }
        $oder_comment_moder = new OrderCommentModel();
        $result = $oder_comment_moder->add_data($condition);
        if ($result) {
            $this->jsonReturn();
        } else {

            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
