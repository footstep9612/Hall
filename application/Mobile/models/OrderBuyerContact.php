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
class OrderBuyerContactModel extends PublicModel {

    //put your code here
    protected $tableName = 'order_buyer_contact';
    protected $dbName = 'erui_order'; //数据库名称

    //状态
    //pay_status status show_status

    public function __construct() {
        parent::__construct();
    }


    /**
     * 数据字典
     * @var array
     * @author link 2017-12-20
     */
    private $_field = [
        'order_id',    //订单id
        'name',    //联系人姓名
        'company',    //公司名称
        'phone',    //电话
        'email',    //邮箱
        'remarks',    //备注
        'created_at',    //创建时间
        'created_by',    //创建人
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
     * 添加
     * @var $data
     * @author link 2017-12-20
     */
    public function addInfo($data){
        if(!isset($data['order_id'])){
            jsonReturn('订单联系人添加，orer_id不能为空');
        }
        try{
            $data = $this->_getData($data);
            $data['created_at'] = date('Y-m-d H:i:s',time());
            $result = $this->add($this->create($data));
            return $result ? $result : false;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【OrderBuyerContactModel】 add:' . $e , Log::ERR);
            return false;
        }
    }




















    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function info($order_id) {

        return $this->where(['order_id' => $order_id])->order('created_at desc')->find();
    }

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function getlistByOrderids($order_ids) {

        $data = $this->field('max(created_at), company ,order_id')
                ->where(['order_id' => ['in', $order_ids]])
                ->order('created_at desc')
                ->group('order_id')
                ->select();
        $deliverys = [];
        if ($data) {
            foreach ($data as $item) {
                $deliverys[$item['order_id']] = $item['company'];
            }
        }

        return $deliverys;
    }

}
