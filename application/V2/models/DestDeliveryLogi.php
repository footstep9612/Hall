<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/28
 * Time: 14:34
 * @desc 落地配
 */
class DestDeliveryLogiModel extends PublicModel {

    protected $dbName = 'erui2_config'; //数据库名称
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
            return json_decode(redisHashGet('DestDeliveryLogi', md5($country . '_' . $lang)), true);
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
            }
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return '';
        }
    }

    /*
     * id,lang,logi_no,trans_mode_bn,country,from_port,to_loc,clearance_days_min,clearance_days_max,delivery_time_min,delivery_time_max,remarks,status,created_by,created_at
     */

    function getCondition($condition) {
        $where = [];
        if (isset($condition['id']) && $condition['id']) {
            $where['id'] = $condition['id'];
        }
        if (isset($condition['lang']) && $condition['lang']) {
            $where['lang'] = $condition['lang'];
        }
        if (isset($condition['logi_no']) && $condition['logi_no']) {
            $where['logi_no'] = $condition['logi_no'];
        }
        if (isset($condition['trans_mode_bn']) && $condition['trans_mode_bn']) {
            $where['trans_mode_bn'] = $condition['trans_mode_bn'];
        }
        if (isset($condition['country']) && $condition['country']) {
            $where['country'] = $condition['country'];
        }
        if (isset($condition['from_port']) && $condition['from_port']) {
            $where['from_port'] = $condition['from_port'];
        }
        if (isset($condition['to_loc']) && $condition['to_loc']) {
            $where['to_loc'] = $condition['to_loc'];
        }
        if (isset($condition['clearance_loc']) && $condition['clearance_loc']) {
            $where['clearance_loc'] = $condition['clearance_loc'];
        }
        if (isset($condition['status']) && $condition['status']) {
            $where['status'] = $condition['status'];
        }

        if (isset($condition['created_by']) && $condition['created_by']) {
            $where['created_by'] = $condition['created_by'];
        }
        return $where;
    }

    /*
     * 获取数据
     */

    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            return $this->where($data)->count();
        } catch (Exception $ex) {

            return 0;
        }
    }

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     */
    public function getListbycondition($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'id,lang,logi_no,trans_mode_bn,country,from_port,to_loc,clearance_days_min,'
                    . 'clearance_days_max,delivery_time_min,delivery_time_max,'
                    . 'remarks,status,created_by,created_at';

            $pagesize = 10;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }
            $from = ($current_no - 1) * $pagesize;
            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            return $result;
        } catch (Exception $e) {
            return array();
        }
    }

}
