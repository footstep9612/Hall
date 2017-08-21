<?php

/**
 * 支付方式
 * User: linkai
 * Date: 2017/6/30
 * Time: 21:33
 */
class PaymentModeModel extends PublicModel {

    protected $dbName = 'erui2_dict'; //数据库名称
    protected $tableName = 'payment_mode'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取支付方式
     * @param string $lang
     * @return array|mixed
     */
    public function getPaymentmode($lang = '') {
        $condition = array();
        if (!empty($lang)) {
            $condition['lang'] = $lang;
        }

        if (redisHashExist('Paymentmode', md5(json_encode($condition)))) {
            return json_decode(redisHashGet('Paymentmode', md5(json_encode($condition))), true);
        }

        $field = 'lang,bn,name';
        $result = $this->field($field)->where($condition)->order('bn')->select();
        if ($result) {
            redisHashSet('Paymentmode', md5(json_encode($condition)), json_encode($result));
            return $result;
        }
    }

}
