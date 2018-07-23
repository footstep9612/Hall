<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 * @author  zhongyg
 * @date    2017-9-12 13:08:12
 * @version V2.0
 * @desc
 */
class OrderController extends PublicController {

    public function init() {
        //$this->token = false;
        parent::init();
    }

    /**
     * 订单添加
     * @example:
     * createAction($data[
     *      'country_bn',
     *      'lang',
     *      'skuAry' =>[sku=>数量，sku=>数量....]
     *      'infoAry'=>[],
     *      'addrAry'=>[],
     *      'contactAry'=>[]
     * ])
     * @author link 2017-12-20
     */
    public function createAction(){
        $input = $this->getPut();
        if(!isset($input['special_id']) || empty($input['special_id'])){
            jsonReturn('', MSG::ERROR_PARAM, 'special_id not null');
        }
        if(!isset($input['country_bn']) || empty($input['country_bn'])){
            jsonReturn('', MSG::ERROR_PARAM, 'country_bn not null');
        }
        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', MSG::ERROR_PARAM, 'lang not null');
        }
        if(!isset($input['skuAry']) || empty($input['skuAry'])){
            jsonReturn('', MSG::ERROR_PARAM, 'skuAry not null');
        }
        if(!isset($input['infoAry']) || empty($input['infoAry'])){
            jsonReturn('', MSG::ERROR_PARAM, 'infoAry not null');
        }
        if(!isset($input['addrAry']) || empty($input['addrAry'])){
            jsonReturn('', MSG::ERROR_PARAM, 'addrAry not null');
        }
        $input['buyer_id'] = $this->user['buyer_id'];
        $order_moder = new OrderModel();
        $orderNo = $order_moder->addOrder($input);
        jsonReturn($orderNo,$orderNo ? MSG::MSG_SUCCESS : MSG::MSG_FAILED);
    }


    /* 获取订单列表
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    //put your code here
    public function listAction() {

        $condition = $this->getPut();
        $order_moder = new OrderModel();
        $condition['buyer_id'] = $this->user['buyer_id'];
        $data = $order_moder->getList($condition);
        $count = $order_moder->getCount($condition);
        if ($data) {
            $this->_setinfos($data);
            $this->setvalue('count', intval($count));
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setvalue('count', 0);
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function infoAction() {
        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $oder_moder = new OrderModel();
        $info = $oder_moder->info($order_id);
        if ($info) {

            if ($info['buyer_id'] != $this->user['buyer_id']) {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn(null);
            }
            $this->_setinfo($info);
            $info['show_status_text'] = $oder_moder->getShowStatus($info['show_status']);
            $info['pay_status_text'] = $oder_moder->getPayStatus($info['pay_status']);
            $delivery_model = new OrderDeliveryModel();
            $delivery_at = $delivery_model->getlastdelivery_at($order_id);

            $info['delivery_at'] = $delivery_at;
            if ($delivery_at) {
                $info['delivery_left'] = ceil((strtotime($delivery_at) - time()) / 86400);
            } else {
                $info['delivery_left'] = null;
            }
            $this->_setBuyerName($info);
            $this->_setOrderCurrency($info); //获取订单的单位
            $this->_setOrderAttachOther($info, $order_id); //获取附件
            $this->_setOrderAttachPo($info, $order_id); //获取附件
            $this->_setOrderBuyerContact($info, $order_id); //获取采购商信息
            $this->_setOrderContact($info, $order_id); //获取供应商信息
            $this->jsonReturn($info);
        } elseif ($info === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

    /* 订单收货人信息
     * @param int $order_id // 订单ID
     * @desc   交收信息
     */
    public function ListAdressAction() {

        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }

        $order_address_model = new OrderAddressModel();
        $order_address = $order_address_model->info($order_id);


        if ($order_address) {

            $this->jsonReturn($order_address);
        } elseif ($order_address === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

    /* 订单商品信息
    * @param int $order_id // 订单ID
    * @desc   商品信息
    */
    public function ListGoodsAction() {

        $order_no = $this->getPut('order_no');
        if (!$order_no) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $condition['order_no'] = $order_no;
        $order_goods_model = new OrderGoodsModel();
        $order_goods = $order_goods_model->getList($condition);
        $order_count = $order_goods_model->getCount($condition);

        if ($order_goods) {
            /*foreach($order_goods as $item) {
                $this->_setOrderCurrency($item); //获取商品的单位
            }*/
            $this->_setinfos($order_goods);
            $this->setvalue('count', intval($order_count));
            $this->jsonReturn($order_goods);
        } elseif ($order_goods === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

    /* 获取订单列表
    * @desc   订单
    */

    //put your code here
    public function ListCommentAction() {

        $condition = $this->getPut(); //查询条件

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

    //订单评论
    public function AddCommentAction() {
        $condition = $this->getPut(); //查询条件

        if (!isset($condition['order_id']) || empty($condition['order_id'])) {
            $this->setCode(MSG::MSG_PARAM_ERROR);
            $this->setMessage('order_id can not empty');

            $this->jsonReturn(null);
        }
        if (!isset($condition['content']) || empty($condition['content'])) {
            $this->setCode(MSG::MSG_PARAM_ERROR);
            $this->setMessage('Content can not empty');
            $this->jsonReturn(null);
        }

        $order_model = new OrderModel();
        $info = $order_model->info($condition['order_id']);

        if ($info['show_status'] !== OrderModel::SHOW_STATUS_COMPLETED) {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('Un Finished Order Can Not Evaluation!');
            $this->jsonReturn(null);
        }
        $oder_comment_moder = new OrderCommentModel();
        $result = $oder_comment_moder->add_data($condition);
        if ($result) {
            $this->jsonReturn($result);
        } else {

            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /* 订单交收信息列表
     * @param int $order_id // 订单ID
     * @desc   交收信息
     */
    public function ListDeliveryAction() {

        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $delivery_model = new OrderDeliveryModel();
        $deliverys = $delivery_model->getlist($order_id);
        if ($deliverys) {
            $this->jsonReturn($deliverys);
        } elseif ($deliverys === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

    /* 订单结算方式列表
    * @param int $order_id // 订单ID
    */
    public function ListPaymentAction() {

        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->jsonReturn(null);
        }
        $payment_model = new OrderPaymentModel();
        $payments = $payment_model->getlist($order_id);
        if ($payments) {

            $this->jsonReturn($payments);
        } elseif ($payments === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }


    /* 获取订单工作流列表 订单执行日志调用
    * @param int $order_id // 订单ID
    * @desc   订单
    */

    public function ListLogAction() {

        $order_id = $this->getPut('order_id');
        if (!$order_id) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        }
        $workflow_model = new OrderLogModel();
        $workflows = $workflow_model->getlist($order_id);

        if ($workflows) {

            $this->jsonReturn($workflows);
        } elseif ($workflows === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
    }

    private function _setinfo(&$info) {

        $country_bns[] = $info['from_country_bn'];
        $country_bns[] = $info['to_country_bn'];
        $port_bns[] = $info['from_port_bn'];
        $port_bns[] = $info['to_port_bn'];
        $trans_mode_bns[] = $info['trans_mode_bn'];
        $country_model = new CountryModel();
        $countrys = $country_model->getCountryByBns($country_bns, 'en');
        $port_model = new PortModel();
        $ports = $port_model->getPortByBns($port_bns, 'en');
        $trans_mode_model = new TransModeModel();
        $trans_modes = $trans_mode_model->getTransModeByBns($trans_mode_bns, 'en');
        if (isset($countrys[$info['from_country_bn']]) && $countrys[$info['from_country_bn']]) {
            $info['from_country'] = $countrys[$info['from_country_bn']];
        } else {
            $info['from_country'] = '';
        }
        if (isset($countrys[$info['to_country_bn']]) && $countrys[$info['to_country_bn']]) {
            $info['to_country'] = $countrys[$info['to_country_bn']];
        } else {
            $info['to_country'] = '';
        }
        if (isset($ports[$info['from_port_bn']]) && $ports[$info['from_port_bn']]) {
            $info['from_port'] = $ports[$info['from_port_bn']];
        } else {
            $info['from_port'] = '';
        }
        if (isset($ports[$info['to_port_bn']]) && $ports[$info['to_port_bn']]) {
            $info['to_port'] = $ports[$info['to_port_bn']];
        } else {
            $info['to_port'] = '';
        }
        if (isset($trans_modes[$info['trans_mode_bn']]) && $trans_modes[$info['trans_mode_bn']]) {
            $info['trans_mode'] = $trans_modes[$info['trans_mode_bn']];
        } else {
            $info['trans_mode'] = '';
        }
    }

    /* 获取采购商信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderBuyerContact(&$info, $order_id) {
        $order_buyer_contact_model = new OrderBuyerContactModel();
        $order_buyer_contact = $order_buyer_contact_model->info($order_id);
        if ($order_buyer_contact) {
            $info['order_buyer_contact'] = $order_buyer_contact;
        } else {
            $info['order_buyer_contact'] = null;
        }
    }

    /* 获取采购商信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setBuyerName(&$info) {
        if ($info['buyer_id']) {
            $buyer_model = new BuyerAccountModel();
            $order_buyer_contact = $buyer_model->getBuyerNamesByBuyerids([$info['buyer_id']]);
            if (isset($order_buyer_contact[$info['buyer_id']])) {
                $info['show_name'] = $order_buyer_contact[$info['buyer_id']];
                $info['user_name'] = $order_buyer_contact[$info['user_name']];
            } else {
                $info['show_name'] = null;
                $info['user_name'] = null;
            }
        } else {
            $info['show_name'] = '';
            $info['user_name'] = '';
        }
    }

    /* 获取采购商信息
   * @param int $order_id // 订单ID
   * @author  zhongyg
   * @date    2017-8-1 16:50:09
   * @version V2.0
   * @desc   订单
   */

    private function _setOrderCurrency(&$info) {
        if ($info['currency_bn']) {
            $currency_model = new CurrencyModel();
            $order_currency = $currency_model->field('bn,symbol,name')->where(['bn'=>$info['currency_bn']])->find();
            if (isset($order_currency['symbol'])) {
                $info['symbol'] = $order_currency['symbol'];
            } else {
                $info['symbol'] = null;
            }
        } else {
            $info['symbol'] = '';
        }
    }

    /* 获取供应商信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderContact(&$info, $order_id) {
        $order_contact_model = new OrderContactModel();
        $order_contact = $order_contact_model->info($order_id);

        if ($order_contact) {
            $info['order_contact'] = $order_contact;
        } else {
            $info['order_contact'] = null;
        }
    }

    /* 获取附件列表信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderAttachPo(&$info, $order_id) {
        $order_attach_model = new OrderAttachModel();
        $order_attachs = $order_attach_model->getlist($order_id, 'PO');
        if ($order_attachs) {
            $info['po'] = $order_attachs[0];
        } else {
            $info['po'] = null;
        }
    }

    /* 获取附件列表信息
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _setOrderAttachOther(&$info, $order_id) {
        $order_attach_model = new OrderAttachModel();
        $order_attachs = $order_attach_model->getlist($order_id, ['OTHERS', 'OUTBOUND',
            'LOGISTICS',
            'DELIVERY',
            'COLLECTION',
            'CREDIT',
        ]);

        if ($order_attachs) {
            $info['others'] = $order_attachs;
        } else {
            $info['others'] = null;
        }
    }


    private function _setinfos(&$list) {
        $orderids = $buyerids = [];
        $country_bns = [];
        $port_bns = [];
        $trans_mode_bns = [];
        foreach ($list as $order) {
            $buyerids[] = $order['buyer_id'];
            $orderids[] = $order['id'];
            $country_bns[] = $order['from_country_bn'];
            $country_bns[] = $order['to_country_bn'];
            $port_bns[] = $order['from_port_bn'];
            $port_bns[] = $order['to_port_bn'];
            $trans_mode_bns[] = $order['trans_mode_bn'];
        }
        $order_moder = new OrderModel();
        $delivery_model = new OrderDeliveryModel();
        $delivery_ats = $delivery_model->getlistByOrderids($orderids);

        $contact_model = new OrderContactModel();
        $contacts = $contact_model->getlistByOrderids($orderids);
        $buyer_model = new OrderBuyerContactModel();
        $buyers = $buyer_model->getlistByOrderids($orderids);
        $country_model = new CountryModel();
        $countrys = $country_model->getCountryByBns($country_bns, 'en');
        $port_model = new PortModel();
        $ports = $port_model->getPortByBns($port_bns, 'en');
        $trans_mode_model = new TransModeModel();
        $trans_modes = $trans_mode_model->getTransModeByBns($trans_mode_bns, 'en');

        foreach ($list as $key => $val) {
            if (isset($delivery_ats[$val['id']]) && $delivery_ats[$val['id']]) {
                $val['delivery_at'] = $delivery_ats[$val['id']];
            } else {
                $val['delivery_at'] = '';
            }
            if (isset($buyers[$val['id']]) && $buyers[$val['id']]) {
                $val['buyer'] = $buyers[$val['id']];
            } else {
                $val['buyer'] = '';
            }
            if (isset($countrys[$val['from_country_bn']]) && $countrys[$val['from_country_bn']]) {
                $val['from_country'] = $countrys[$val['from_country_bn']];
            } else {
                $val['from_country'] = '';
            }
            if (isset($countrys[$val['to_country_bn']]) && $countrys[$val['to_country_bn']]) {
                $val['to_country'] = $countrys[$val['to_country_bn']];
            } else {
                $val['to_country'] = '';
            }
            if (isset($ports[$val['from_port_bn']]) && $ports[$val['from_port_bn']]) {
                $val['from_port'] = $ports[$val['from_port_bn']];
            } else {
                $val['from_port'] = '';
            }
            if (isset($ports[$val['to_port_bn']]) && $ports[$val['to_port_bn']]) {
                $val['to_port'] = $ports[$val['to_port_bn']];
            } else {
                $val['to_port'] = '';
            }
            if (isset($trans_modes[$val['trans_mode_bn']]) && $trans_modes[$val['trans_mode_bn']]) {
                $val['trans_mode'] = $trans_modes[$val['trans_mode_bn']];
            } else {
                $val['trans_mode'] = '';
            }
            if (isset($contacts[$val['id']]) && $contacts[$val['id']]) {
                $val['supplier'] = $contacts[$val['id']];
            } else {
                $val['supplier'] = '';
            }
            $val['show_status_text'] = $order_moder->getShowStatus($val['show_status']);
            $val['pay_status_text'] = $order_moder->getPayStatus($val['pay_status']);
            $list[$key] = $val;
        }
    }
    //发送邮件
    public function sendAction(){
        $data = $this->getPut();
        $lang = empty($data['lang']) ? 'en' : $data['lang'];
        $country_bn = empty($data['country_bn']) ? '' : strtolower($data['country_bn']);
        if($data['order_no']){
            $ordr_model = new OrderModel();
            $result = $ordr_model->field('id,created_at,show_status,pay_status,currency_bn,amount,remark,expected_receipt_date')
                                 ->where(['order_no'=>$data['order_no'],'deleted_flag'=>'N'])
                                 ->find();
            $config_obj = Yaf_Registry::get("config");
            $config_shop = $config_obj->shop->toArray();
            $config_email = $config_obj->email->toArray();
            if($result['id']){
                $order_address_model = new OrderAddressModel();
                $res = $order_address_model->info($result['id']);
                $arrEmail = [
                    'order_no'=> $data['order_no'],
                    'created_at'=> date('Y-m-d',strtotime($result['created_at'])),
                    'status'=> $ordr_model->getShowStatus($result['show_status']),
                    'pay_status'=> $ordr_model->getPayStatus($result['show_status']),
                    'currency_bn'=> $result['currency_bn'],
                    'amount'=> number_format($result['amount'],'2','.',','),
                    'remark'=> $result['remark'],
                    'expected_receipt_date'=> $result['expected_receipt_date'],
                    'name'=> $res['name'],
                    'phone'=> $res['phone'],
                    'zipcode'=> $res['zipcode'],
                    'url'=> $config_shop['url'],
                    'time'=> date('Y-m-d H:i:s',time()),
                ];
            }
            //客户(默认英文)
            $arrEmail['preInfo'] = ShopMsg::getMessage('2001-1', $lang);   //客户(默认英文)
            $arrEmail['info'] = ShopMsg::getMessage('2001-2', $lang);   //客户(默认英文)
            $email = $res['email'];
            $this->orderEmail($email,$arrEmail,$lang,$config_email['url']);
            //我方(加拿大人员)
            $order_address = COMMON_CONF_PATH  . DS . 'order_email.'.CONFBDP.'.php';
            $canada_email = include_once($order_address);
            if(isset($canada_email[$country_bn])){
                $arrEmail['preInfo'] = ShopMsg::getMessage('2002-1', $lang);   //我方(加拿大人员)
                $arrEmail['info'] = ShopMsg::getMessage('2002-2', $lang);   //我方(加拿大人员)
                $email_arr = $canada_email[$country_bn];
                $this->orderEmail($email_arr,$arrEmail,$lang,$config_email['url']);
            }
        }
    }
    //订单生产发送邮件
    function orderEmail($email,$arrEmail, $lang, $emailUrl, $title= 'Erui.com') {
        $body = $this->getView()->render('order/order_email_'.$lang.'.html', $arrEmail);
        $data = [
            "title"        => $title,
            "content"      => $body,
            "groupSending" => 0,
            "useType"      => "Order"
        ];
        if(is_array($email)) {
            $arr = implode(',',$email);
            $data["to"] = "[$arr]";
        }elseif(is_string($email)){
            $data["to"] = "[$email]";
        }
        PostData($emailUrl, $data, true);
    }

}
