<?php

/**
 * Description of LogiPeriodModel
 * @author  zhongyg
 * @date    2017-8-2 13:07:21
 * @version V2.0
 * @desc   贸易条款对应物流时效
 */
class LogiPeriodModel extends PublicModel {

    protected $dbName = 'erui_config'; //数据库名称
    protected $tableName = 'logi_period';

    const STATUS_VALID = 'VALID';

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    private function _getCondition($condition) {
        $where = [];

        getValue($where, $condition, 'id', 'string'); //id
        getValue($where, $condition, 'lang', 'string'); //语言
        getValue($where, $condition, 'logi_no', 'string'); //承运项编号
        getValue($where, $condition, 'trade_terms_bn', 'string'); //贸易术语简称
        getValue($where, $condition, 'trans_mode_bn', 'string'); //运输方式简称
        getValue($where, $condition, 'warehouse', 'string'); //仓库
        getValue($where, $condition, 'from_country', 'string'); //起运国
        getValue($where, $condition, 'from_port', 'string'); //起运口岸
        getValue($where, $condition, 'to_country', 'string'); //目的国
        getValue($where, $condition, 'to_port', 'string'); //目的口岸
        getValue($where, $condition, 'clearance_loc', 'string'); //清关地
        getValue($where, $condition, 'delivery_addr', 'string'); //目的地
        getValue($where, $condition, 'transfer_flag', 'bool'); //中转标志
        getValue($where, $condition, 'status', 'string'); //状态
        if (!$where['status']) {
            $where['status'] = 'VALID';
        }
        getValue($where, $condition, 'created_by', 'string'); //创建人
        getValue($where, $condition, 'created_at', 'string'); //创建时间
        getValue($where, $condition, 'updated_by', 'string'); //修改人
        getValue($where, $condition, 'updated_at', 'string'); //修改时间
        getValue($where, $condition, 'checked_by', 'string'); //审核人
        getValue($where, $condition, 'checked_at', 'string'); //审核时间
        getValue($where, $condition, 'deleted_flag', 'bool'); //运输方式简称

        return $where;
    }

    /**
     * Description of 获取总数
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function getCount($condition) {
        try {
            $data = $this->_getCondition($condition);
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
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function getListbycondition($condition = '', $type = true) {
        $where = $this->_getCondition($condition);
        if ($type) {
            list($from, $pagesize) = $this->_getPage($condition);
            $redis_key = md5(json_encode($condition) . $from . $pagesize . $type);
        } else {
            $redis_key = md5(json_encode($condition) . $type);
        }

        if (redisHashExist('LogiPeriod', $redis_key)) {
            return json_decode(redisHashGet('LogiPeriod', $redis_key, true));
        }
        try {
            $field = 'id,lang,logi_no,trade_terms_bn,trans_mode_bn,warehouse,from_country,'
                    . 'from_port,to_country,clearance_loc,to_port,packing_period_min,'
                    . 'packing_period_max,collecting_period_min,collecting_period_max,'
                    . 'declare_period_min,declare_period_max,loading_period_min,'
                    . 'loading_period_max,int_trans_period_min,int_trans_period_max,'
                    . 'remarks,period_min,period_max,status,created_by,created_at';
            $this->field($field);
            if ($type) {
                $this->limit($from, $pagesize);
            }
            $result = $this->where($where)->select();
            redisHashSet('LogiPeriod', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function getList($lang = '', $to_country = '', $from_country = '', $warehouse = '') {
        if (empty($lang) || empty($to_country)) {
            return array();
        }

        $countryModel = new CountryModel();
        $cityModel = new CityModel();
//库中中国状态暂为无效
        $from_country = $from_country ? $from_country : $countryModel->getCountryByBn('China', $lang);
//city库中暂无东营,暂时写死以为效果
        $warehouse = $warehouse ? $warehouse : $cityModel->getCityByBn('Dongying', $lang);

        $condition = array(
            'status' => self::STATUS_VALID,
            'lang' => $lang,
            'to_country' => $to_country,
            'from_country' => $from_country,
            'warehouse' => $warehouse
        );
        if (redisHashExist('LogiPeriod', md5(json_encode($condition)))) {
            return json_decode(redisHashGet('LogiPeriod', md5(json_encode($condition))), true);
        }
        try {
            $field = 'id,lang,logi_no,trade_terms_bn,trans_mode_bn,warehouse,from_country,'
                    . 'from_port,to_country,clearance_loc,to_port,packing_period_min,'
                    . 'packing_period_max,collecting_period_min,collecting_period_max,'
                    . 'declare_period_min,declare_period_max,loading_period_min,'
                    . 'loading_period_max,int_trans_period_min,int_trans_period_max,'
                    . 'remarks,period_min,period_max,description';
            $result = $this->field($field)->where($condition)->select();
            $data = array();
            if ($result) {
                foreach ($result as $item) {
                    $data[$item['trade_terms']][] = $item;
                }
                redisHashSet('LogiPeriod', md5(json_encode($condition)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 根据条件获取物流时效信息
     * @param string $field 要获取的字段
     * @param array $where 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function getInfo($field, $where) {
        if (empty($field) || empty($where))
            return array();

        if (redisHashExist('LogiPeriod', md5(json_encode($where)))) {
            return json_decode(redisHashGet('LogiPeriod', md5(json_encode($where))), true);
        }
        try {
            $result = $this->field($field)->where($where)->select();
            $data = array();
            if ($result) {
                $data = $result;
                redisHashSet('LogiPeriod', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

}
