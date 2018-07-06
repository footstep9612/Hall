<?php

/**
 * Description of PortModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   口岸
 */
class Common_PortModel extends PublicModel {

    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'port'; //数据表表名

    public function __construct() {
        parent::__construct();
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
     * @desc   口岸
     */
    public function getList($condition = '') {
        $where = $this->_getCondition($condition);
        list($from, $pagesize) = $this->_getPage($condition);
        $redis_key = md5(json_encode($where) . '_' . $from . '_' . $pagesize);
        if (redisHashExist('Port', $redis_key)) {
            return json_decode(redisHashGet('Port', $redis_key), true);
        }
        try {
            $field = 'bn,country_bn,name';
            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            if ($result) {
                redisHashSet('Port', $redis_key, json_encode($result));
            }
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
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
        $where = ['deleted_flag' => 'N'];

        if (isset($condition['lang']) && $condition['lang']) {
            $where['lang'] = $condition['lang'];
        }
        if (isset($condition['bn']) && $condition['bn']) {
            $where['bn'] = $condition['bn'];
        }
        if (isset($condition['country_bn']) && $condition['country_bn']) {
            $where['country_bn'] = $condition['country_bn'];
        }
        if (isset($condition['port_type']) && $condition['port_type']) {
            $where['port_type'] = $condition['port_type'];
        }
        if (isset($condition['trans_mode']) && $condition['trans_mode']) {
            $where['trans_mode'] = str_replace("+", " ", $condition['trans_mode']);
        }
        $this->_getValue($where, $condition, 'status', 'string', 'VALID');
        if (isset($condition['name']) && $condition['name']) {
            $where['name'] = ['like', '%' . $condition['name'] . '%'];
        }

        return $where;
    }

    /**
     * Description of 获取总数
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */
    public function getCount($condition) {
        try {
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where)) . '_COUNT';
            if (redisHashExist('Port', $redis_key)) {
                return redisHashGet('Port', $redis_key);
            }
            $count = $this->where($where)->count();
            redisHashSet('Port', $redis_key, $count);
            return $count;
        } catch (Exception $ex) {

            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

}
