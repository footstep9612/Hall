<?php

/**
 * Description of PortModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   口岸
 */
class Common_TradetermModel extends PublicModel {

    protected $dbName = 'erui_dict';
    protected $tableName = 'trade_terms';

    public function __construct() {
        parent::__construct();
    }

    /*
     * 条件处理
     * @param array $condition 条件
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];

        $this->_getValue($where, $condition, 'id', 'string');
        $this->_getValue($where, $condition, 'lang', 'string');
        $this->_getValue($where, $condition, 'terms', 'string');
        $this->_getValue($where, $condition, 'description', 'like');
        $this->_getValue($where, $condition, 'trans_mode_bn', 'string');
        $this->_getValue($where, $condition, 'status', 'string');
        if ($where['status']) {
            $where['status'] = 'VALID';
        }
        return $where;
    }

    /*
     * 获取数据
     * @author  zhongyg
     * @param array $condition 条件
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    public function getCount($condition) {
        try {
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where)) . '_COUNT';
            if (redisHashExist('TradeTerms', $redis_key)) {
                return redisHashGet('TradeTerms', $redis_key);
            }
            $count = $this->where($where)->count();
            redisHashSet('TradeTerms', $redis_key, $count);
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
     * @desc   贸易术语
     */
    public function getList($condition = '') {
        $where = $this->_getCondition($condition);
        try {
            $field = 'id,terms,trans_mode_bn';

            list($from, $pagesize) = $this->_getPage($condition);
            $redis_key = md5(json_encode($where) . $field . '_' . $from . '_' . $pagesize);
            if (redisHashExist('TradeTerms', $redis_key)) {
                return json_decode(redisHashGet('TradeTerms', $redis_key), true);
            }
            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            if ($result) {
                redisHashSet('TradeTerms', $redis_key, json_encode($result));
            }
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
