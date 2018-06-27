<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/28
 * Time: 14:34
 * @desc 落地配
 */
class DestDeliveryLogiModel extends PublicModel {

    protected $dbName = 'erui_config'; //数据库名称
    protected $tableName = 'dest_delivery_logi';

    const STATUS_VALID = 'VALID';    //有效的

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据落地国家跟语言获取信息
     * @param string $country
     * @param string $lang
     * @return array|mixed|string
     */
    public function getList($country = '', $lang = '') {

        if (redisHashExist('DestDeliveryLogi', md5($country . '_' . $lang))) {
//            return json_decode(redisHashGet('DestDeliveryLogi', md5($country . '_' . $lang)), true);
        }
        try {
            $condition = array(
                'country' => $country,
                'lang' => $lang,
                'status' => self::STATUS_VALID
            );
            $field = 'lang,logi_no,trans_mode_bn,country,from_port,to_loc,remarks,'
                    . 'clearance_days_min,clearance_days_max,delivery_time_min,delivery_time_max';
            $result = $this->field($field)->where($condition)->select();

            if ($result) {
                redisHashSet('DestDeliveryLogi', md5($country . '_' . $lang), json_encode($result));
                return $result;
            }
            return array();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
