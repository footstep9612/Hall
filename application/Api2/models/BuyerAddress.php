<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class BuyerAddressModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_buyer';
    protected $tableName = 'buyer_address';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取用户信息
     * @param  array  $data
     * @return array
     * @author jhw
     */
    public function info($data) {
        if (!empty($data['buyer_id'])) {
            $row = $this->where(['buyer_id' => $data['buyer_id']])
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 新增/更新数据
     * @author klp
     */
    public function createInfo($token, $input) {
        if (!isset($input))
            return false;
        $this->startTrans();
        try {
            foreach ($input as $key => $item) {
                $arr = ['zh', 'en', 'ru', 'es'];
                if (in_array($key, $arr)) {
                    $checkout = $this->checkParam($item);
                    $data = [
                        'lang' => $key,
                        'customer_id' => $token['customer_id'],
                        'tel_country_code' => isset($checkout['tel_country_code']) ? $checkout['tel_country_code'] : '',
                        'official_email' => isset($checkout['official_email']) ? $checkout['official_email'] : '',
                        'zipcode' => isset($checkout['zipcode']) ? $checkout['zipcode'] : '',
                        'address' => isset($checkout['address']) ? $checkout['address'] : '',
                        'longitude' => isset($checkout['longitude']) ? $checkout['longitude'] : '',
                        'latitude' => isset($checkout['latitude']) ? $checkout['latitude'] : '',
                        'tel_area_code' => isset($checkout['tel_area_code']) ? $checkout['tel_area_code'] : '',
                        'tel_local_number' => isset($checkout['tel_local_number']) ? $checkout['tel_local_number'] : '',
                        'tel_ext_number' => isset($checkout['tel_ext_number']) ? $checkout['tel_ext_number'] : '',
                    ];
                    //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                    $result = $this->field('customer_id')->where(['customer_id' => $token['customer_id'], 'lang' => $key])->find();
                    if ($result) {
                        $this->where(['customer_id' => $token['customer_id'], 'lang' => $key])->save($data);
                    } else {
                        $data['created_by'] = $token['user_name'];
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $this->add($data);
                    }
                }
            }
            $this->commit();
            return $token['customer_id'];
        } catch (\Kafka\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * 参数校验-门户
     * @author klp
     */
    private function checkParam($param = []) {
        if (empty($param))
            return false;
        //待补充
        return $param;
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['buyer_id'])) {
            $arr['buyer_id'] = $create['buyer_id'];
        }
        if (isset($create['lang'])) {
            $arr['lang'] = $create['lang'];
        }
        if (isset($create['address'])) {
            $arr['address'] = $create['address'];
        }
        if (isset($create['zipcode'])) {
            $arr['zipcode'] = $create['zipcode'];
        }
        if (isset($create['longitude'])) {
            $arr['longitude'] = $create['longitude'];
        }
        if (isset($create['latitude'])) {
            $arr['latitude'] = $create['latitude'];
        }
        if (isset($create['tel_country_code'])) {
            $arr['tel_country_code'] = md5($create['tel_country_code']);
        }
        if (isset($create['tel_area_code'])) {
            $arr['tel_area_code'] = $create['tel_area_code'];
        }
        if (isset($create['tel_local_number'])) {
            $arr['tel_local_number'] = $create['tel_local_number'];
        }
        if (isset($create['tel_ext_number'])) {
            $arr['tel_ext_number'] = $create['tel_ext_number'];
        }
        if (isset($create['official_email'])) {
            $arr['official_email'] = $create['official_email'];
        }
        $arr['created_at'] = date("Y-m-d H:i:s");
        try {
            $data = $this->create($arr);
            return $this->add($data);
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition, $where) {
        if (isset($condition['address'])) {
            $data['address'] = $condition['address'];
        }
        if (isset($condition['zipcode'])) {
            $data['zipcode'] = $condition['zipcode'];
        }
        if (isset($condition['tel_country_code'])) {
            $data['tel_country_code'] = $condition['tel_country_code'];
        }
        if (isset($condition['tel_area_code'])) {
            $data['tel_area_code'] = $condition['tel_area_code'];
        }
        if (isset($condition['tel_ext_number'])) {
            $data['tel_ext_number'] = $condition['tel_ext_number'];
        }
        if (isset($condition['official_email'])) {
            $data['official_email'] = $condition['official_email'];
        }
        $resCheck = $this->field('address,zipcode')->where(['buyer_id' => $where['buyer_id']])->find();
        if($resCheck){
            if (isset($data['address'])) {
                if($data['address'] == $resCheck['address']){
                    unset($data['address']);
                }
            }
            if (isset($data['zipcode'])) {
                if($data['zipcode'] == $resCheck['zipcode']){
                    unset($data['zipcode']);
                }
            }
        }
        if(empty($data)){
            return true;
        }
        $res =  $this->where(['buyer_id'=>$where['buyer_id']])->save($data);
        if($res){
            return true;
        }
        return false;
    }

}
