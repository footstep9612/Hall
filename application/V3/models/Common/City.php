<?php

/**
 * Description of PortModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   口岸
 */
class Common_CityModel extends PublicModel {

    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'city'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    private function _getCondition($condition) {
        $where = [];
        if (isset($condition['id']) && $condition['id']) {
            $where['id'] = $condition['id'];
        }
        if (isset($condition['lang']) && $condition['lang']) {
            $where['lang'] = $condition['lang'];
        }
        if (isset($condition['region_bn']) && $condition['region_bn']) {
            $where['region_bn'] = $condition['region_bn'];
        }
        if (isset($condition['country_bn']) && $condition['country_bn']) {
            $where['country_bn'] = $condition['country_bn'];
        }

        if (isset($condition['time_zone']) && $condition['time_zone']) {
            $where['time_zone'] = $condition['time_zone'];
        }
        if (isset($condition['name']) && $condition['name']) {
            $where['name'] = ['like', '%' . $condition['name'] . '%'];
        }

        return $where;
    }

    /*
     * 获取数据
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function getCount($condition) {
        try {

            $data = $this->_getCondition($condition);
            $redis_key = md5(json_encode($data)) . 'COUNT';
            if (redisHashExist('City', $redis_key)) {
                return redisHashGet('City', $redis_key);
            }
            $count = $this->where($data)->count();
            redisHashSet('City', $redis_key, $count);
            return $count;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
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
     * @desc
     */
    public function getList($condition = '') {
        $where = $this->_getCondition($condition);
        list($from, $pagesize) = $this->_getPage($condition);
        $redis_key = md5(json_encode($where) . '_' . $from . '_' . $pagesize);
        if (redisHashExist('City', $redis_key)) {
            return json_decode(redisHashGet('City', $redis_key), true);
        }
        try {
            $field = 'bn,country_bn,name';
            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            redisHashSet('City', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return array();
        }
    }

}
