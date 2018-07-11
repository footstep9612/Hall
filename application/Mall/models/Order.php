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
class OrderModel extends PublicModel {

    //put your code here
    protected $tableName = 'order';
    protected $dbName = 'erui_order'; //数据库名称

    const SHOW_STATUS_UNCONFIRM = 'UNCONFIRM'; // 订单展示状态CONFIRM待确认
    const SHOW_STATUS_GOING = 'GOING'; // 订单展示状态  GOING.进行中
    const SHOW_STATUS_COMPLETED = 'COMPLETED'; // 订单展示状态 COMPLETED.已完成
    const PAY_STATUS_UNCONFIRM = 'UNPAY'; //支付状态 UNPAY未付款
    const PAY_STATUS_GOING = 'PARTPAY'; //支付状态 PARTPAY部分付款
    const PAY_STATUS_COMPLETED = 'PAY'; //支付状态  PAY已付款

    //状态
//pay_status status show_status

    public function __construct() {
        parent::__construct();
    }

    /**
     * 数据字典
     * @var $data
     * @author link 2017-12-20
     */
    private $_field = [
        'order_no',    //订单编号
        'buyer_id',    //采购商id
        'order_contact_id',    //订单（采购商）联系人
        'buyer_contact_id',
        'amount',    //订单金额
        'currency_bn',    //币种
        'trade_terms_bn',    //贸易条款简码
        'trans_mode_bn',    //运输方式简码
        'to_country_bn',    //目的国
        'to_port_bn',    //目的港口
        'address',    //地址'
        'created_by',    //创建人    这里可能不需要填写
        'created_at',    //创建时间
        'expected_receipt_date',    //期望收货日期
        'remark',    //订单备注
        'source',    //源
    ];

    /**
     * 格式化数据
     * @var $data
     * @author link 2017-12-20
     */
    private function _getData($data){
        if(empty($data)){
            return [];
        }
        foreach($data as $key =>$value){
            if(!in_array($key,$this->_field)){
                unset($data[$key]);
            }
            if(empty($value)){
                $data[$key] = null;
            }
        }
        return $data;
    }

    /**
     * 生成订单编号
     * @return bool|string
     * @author link 2017-12-20
     */
    public function createOrderNo(){
        $orderNo = '';
        try {
            $orderNoInfo = $this->field( 'order_no' )->order( 'order_no DESC' )->find();
            if($orderNoInfo){
                $orderNo = substr($orderNoInfo['order_no'],0,8).str_pad(substr($orderNoInfo['order_no'],8)+1, 4, '0', STR_PAD_LEFT);
            }
            return $orderNo;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【OrderModel】 createOrderNo:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 添加
     * @param array $data['country_bn','buyer_id','lang','skuAry'=[sku:number],'infoAry'=>[],'addrAry'=>[],'contactAry'=>[]]
     * @return bool|mixed
     */
    public function addOrder($data){
        if(!isset($data['infoAry'])){
            jsonReturn('', MSG::MSG_FAILED, '订单信息不能为空');
        }

        $orerNo = $this->createOrderNo();
        if(!$orerNo){
            jsonReturn('',MSG::MSG_FAILED, '生成订单编号失败');
        }

        $this->startTrans();
        try{
            $data['infoAry']['order_no'] = $orerNo;
            $data['infoAry']['buyer_id'] = $data['buyer_id'];
            $data['infoAry']['source'] = 'Mall';
            $dataInfo = $this->_getData($data['infoAry']);
            $dataInfo['deleted_flag'] = 'N';
            $dataInfo['created_at'] = date('Y-m-d H:i:s',time());
            $result = $this->add($this->create($dataInfo));
            if($result){
                //添加订单商品信息
                if(isset($data['skuAry']) && !empty($data['skuAry'])){
                    if(!$this->addGoods($orerNo,$data['skuAry'],$data['special_id'],$data['country_bn'],$data['lang'],$data['buyer_id'])){
                        $this->rollback();
                        jsonReturn('', MSG::MSG_FAILED, '订单商品添加失败');
                    }
                }

                //添加订单地址信息
                if(isset($data['addrAry']) && !empty($data['addrAry'])){
                    $data['addrAry']['order_id'] = $result;
                    $oaModel = new OrderAddressModel();
                    if(!$oaModel->addInfo($data['addrAry'])){
                        $this->rollback();
                        jsonReturn('', MSG::MSG_FAILED, '订单地址添加失败');
                    }
                }
                //添加订单联系人信息
                if(isset($data['contactAry']) && !empty($data['contactAry'])){
                    $data['contactAry']['order_id'] = $result;
                    $ocModel = new OrderBuyerContactModel();
                    if(!$ocModel->addInfo($data['contactAry'])){
                        $this->rollback();
                        jsonReturn('', MSG::MSG_FAILED, '订单联系人添加失败');
                    }
                }
                $this->commit();
                return $orerNo;
            }
            return false;
        }catch (Exception $e){
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【OrderModel】 createOrder:' . $e , Log::ERR);
            return false;
        }
    }


    /**
     * 订单商品
     * @author link
     * @param $order_no
     * @param $data
     * @param $special_id
     * @param $country_bn
     * @param $lang
     * @param $buyer_id
     * @return bool
     */
    public function addGoods($order_no,$data,$special_id,$country_bn,$lang,$buyer_id){
        if(empty($data)){
            return false;
        }
        $productModel = new ProductModel();
        $goodsModel = new GoodsModel();
        $gattachModel = new GoodsAttachModel();
        $ogModel = new OrderGoodsModel();
        $data_insert = [];
        $amount = 0;
        $currency_bn = '';
        $skuAry = [];
        $sku_stock = [];
        foreach($data as $sku => $number){
            $priceInfo = [];
            $skuAry[] = $sku;
            $number = intval($number);
            $data_temp = [];
            //获取库存信息
            $stockInfo = $productModel->getSkuStockBySku($sku,$country_bn,$lang);
            if($stockInfo){
                $stock = ($stockInfo && isset($stockInfo[$sku])) ? $stockInfo[$sku]['stock'] : 0;
                if($number>$stock){
                    jsonReturn('', 1099, '库存不足');
                }
            }else{
                jsonReturn('', 1044, '已经下架');
            }
            //获取价格信息
            $promotion_price = '';
            if (isset($stockInfo[$sku]['price_strategy_type']) && $stockInfo[$sku]['price_strategy_type']!='' && (($stockInfo[$sku]['strategy_validity_start']< date('Y-m-d H:i:s',time()) || $stockInfo[$sku]['strategy_validity_start']==null) && ($stockInfo[$sku]['strategy_validity_end']> date('Y-m-d H:i:s',time()) || $stockInfo[$sku]['strategy_validity_end']==null) )) {
                $psdM = new PriceStrategyDiscountModel();
                $promotion_price = $psdM->getSkuPriceByCount($sku,'STOCK',$special_id,$number);
            }
            $promotion_price = $promotion_price ? $promotion_price : ($stockInfo[$sku]['price'] ? $stockInfo[$sku]['price'] : null);

            $currency_bn = $stockInfo[$sku]['price_cur_bn'] ? $stockInfo[$sku]['price_cur_bn'] : null;

            //获取商品基本信息
            $goodsInfo = $goodsModel->getInfoBySku($sku,$lang);

            //商品附件图
            $condition_attach = ['sku'=>$sku, 'attach_type'=>'BIG_IMAGE', 'status'=>'VALID'];
            $attach = $gattachModel->getAttach($condition_attach);
            $data_temp = [
                'order_no' => $order_no,
                'sku' => $sku,
                'name' => $goodsInfo[$sku]['show_name'] ? $goodsInfo[$sku]['show_name'] : ($goodsInfo[$sku]['name'] ? $goodsInfo[$sku]['name'] : ($goodsInfo[$sku]['spu_show_name'] ? $goodsInfo[$sku]['spu_show_name'] :($goodsInfo[$sku]['spu_name'] ? $goodsInfo[$sku]['spu_name'] : ''))),    //商品名称
                'spec_attrs' => $goodsInfo[$sku]['spec_attrs'],    //规格属性
                'symbol' => $stockInfo[$sku]['price_symbol'] ? $stockInfo[$sku]['price_symbol'] : null,
                'price' => $promotion_price,
                'buy_number' => $number,
                'lang' => $lang,    //语言
                'model' => $goodsInfo[$sku]['model'] ? $goodsInfo[$sku]['model'] : null,    //型号
                'min_pack_naked_qty' => $goodsInfo[$sku]['min_pack_naked_qty'] ? $goodsInfo[$sku]['min_pack_naked_qty'] : null,    //最小包装内裸货商品数量
                'nude_cargo_unit' => $goodsInfo[$sku]['nude_cargo_unit'],    //商品裸货单位
                'min_pack_unit' => $goodsInfo[$sku]['min_pack_unit'],    //最小包装单位
                'thumb'=> $attach ? json_encode($attach): null,    //商品图
                'buyer_id' => $buyer_id,    //采购商id
                'created_at' => date('Y-m-d H:i:s',time())
            ];
            $amount = $amount + $data_temp['price'] * $data_temp['buy_number'];
            $data_insert[] = $data_temp;
        }
        if(!empty($data_insert)){
            $result = $ogModel->addAll($data_insert);

            //清购物车
            if($skuAry){
                $scModel = new ShoppingCarModel();
                $scModel->clear($skuAry, $buyer_id, 1);
            }

            //修改库存
            $stockModel = new StockModel();
            foreach($data_insert as $r){
                $stockWhere = [
                    'sku'=>$r['sku'],
                    'country_bn'=> $country_bn,
                    'lang' => $r['lang']
                ];
                $stockUpData = [
                    'stock' => ['exp', 'stock'.'-'.$r['buy_number']]
                ];
                $upstatue = $stockModel->where($stockWhere)->save($stockUpData);
                if(!$upstatue){
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Order】stock更新失败'.$country_bn.'-'.$lang.'-'.$r['sku'].'库存应减'.$r['buy_number'] , Log::ERR);
                }
            }

            //更新订单金额
            $orderModel = new OrderModel();
            $result_order = $orderModel->where(['order_no'=>$order_no])->save(['amount'=>$amount, 'currency_bn'=>$currency_bn]);
            return ($result && $result_order) ? true : false;
        }
        return false;
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function info($order_id, $lang = 'en') {
        $field = 'id,order_no,po_no,execute_no,contract_date,buyer_id,address,status,show_status,pay_status,amount,trade_terms_bn,currency_bn';
        $field .= ',trans_mode_bn,from_country_bn,to_country_bn,from_port_bn,to_port_bn,quality,distributed,comment_flag,remark,created_at';
        return $this->field($field)
                        ->where(['id' => $order_id])->find();
    }

    /* 查询条件
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    private function _getCondition($condition) {
        $where = [];
        $where['deleted_flag'] = 'N';
        $this->_getValue($where, $condition, 'order_no'); //平台订单号
        $this->_getValue($where, $condition, 'po_no'); //po编号
        $this->_getValue($where, $condition, 'execute_no'); //执行编号
        if (isset($condition['status']) && $condition['status']) {
            switch ($condition['status']) {
                case 'to_be_confirmed':
                    $where['show_status'] = 'UNCONFIRM';
                    break;
                case 'proceeding':
                    $where['show_status'] = 'GOING';
                    break;
                case 'finished':
                    $where['show_status'] = 'COMPLETED';
                    break;
            }
        }
        if (isset($condition['pay_status']) && $condition['pay_status']) {
            switch ($condition['pay_status']) {
                case 'unpaid':
                    $where['pay_status'] = 'UNPAY';
                    break;
                case 'part_paid':
                    $where['pay_status'] = 'PARTPAY';
                    break;
                case 'payment_completed':
                    $where['pay_status'] = 'PAY';
                    break;
            }
        }

        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //创建时间new
            $where['created_at'] = array(
                array('egt', date('Y-m-d 0:0:0',strtotime($condition['start_time']))),
                array('elt', date('Y-m-d 23:59:59',strtotime($condition['end_time'])))
            );
        }
        //$this->_getValue($where, $condition, 'contract_date', 'between'); //签约日期
        if (isset($condition['term']) && $condition['term']) {    //贸易术语
            $where['trade_terms_bn'] = $condition['term'];
        }
        if (isset($condition['buyer_id']) && $condition['buyer_id']) {
            $where['buyer_id'] = $condition['buyer_id'];
        } else {
            jsonReturn(null,-1,'用户ID不能为空!');
        }
        if (isset($condition['buyername']) && $condition['buyername']) {

            $buyermodel = new BuyerModel();
            $buyerids = $buyermodel->getBuyeridsByBuyerName($condition['buyername']);
            if ($buyerids) {
                $where['buyer_id'] = ['in', $buyerids];
            }
        }
        return $where;
    }

    /* 获取订单列表
     * @param array $condition // 查询条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getList($condition, $lang = 'en') {

        $where = $this->_getCondition($condition);
        $condition['current_no'] = $condition['currentPage'];

        list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,order_no,po_no,execute_no,contract_date,buyer_id,address,status,show_status,pay_status,amount,trade_terms_bn,currency_bn';
        $field .= ',trans_mode_bn,from_country_bn,to_country_bn,from_port_bn,to_port_bn,created_at';
        return $this->field($field)
                    ->where($where)
                    ->limit($start_no, $pagesize)
                    ->order('id desc')
                    ->select();
    }

    /* 获取订单数量
     * @param array $condition // 查询条件
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
    }

    /* 获取订单状态
     * @param int $show_status // 订单展示状态CONFIRM待确认 GOING.进行中  COMPLETED.已完成
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getShowStatus($show_status) {
        switch ($show_status) {

            case 'UNCONFIRM':
                return 'To be confirmed';

            case 'GOING':
                return 'Proceeding';

            case 'COMPLETED':
                return 'Finished';

            default :return'To be confirmed';
        }
    }

    /* 获取订单付款状态
     * @param int $status // 状态 支付状态 UNPAY未付款 PARTPAY部分付款  PAY已付款
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getPayStatus($pay_status) {
        switch ($pay_status) {
            case 'UNPAY':
                return 'Unpaid';

            case 'PARTPAY':
                return 'Part paid';

            case 'PAY':
                return 'Payment completed';

            default :return'Unpaid';
        }
    }



}
