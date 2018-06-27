<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MarketAreaModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   营销区域
 */
class MarketAreaModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_operation';
    protected $tableName = 'market_area';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    private function _getCondition($condition) {
        $data = [];
        $data['zh.lang'] = 'zh';
        //$this->_getValue($data, $condition, 'lang', 'string');
        $this->_getValue($data, $condition, 'bn', 'string', 'zh.bn');
        $this->_getValue($data, $condition, 'parent_bn', 'string', 'zh.parent_bn');
        $this->_getValue($data, $condition, 'name', 'like', 'zh.name');
        $this->_getValue($data, $condition, 'status', 'string', 'zh.status');
        if (!$data['zh.status']) {
            $data['zh.status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'url', 'like', 'zh.url');

        return $data;
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @param string $order 排序
     * @param bool $type 是否分页
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function getlist($condition, $order = 'zh.id desc') {
        try {
            $data = $this->_getCondition($condition);
            $redis_key = md5(json_encode($data));
            if (redisHashExist('Market_Area', $redis_key)) {
                return json_decode(redisHashGet('Market_Area', $redis_key), true);
            }
            $result = $this->alias('zh')
                            ->join('erui_operation.market_area as en on '
                                    . 'en.bn=zh.bn and en.lang=\'en\' and en.`status` = \'VALID\' ', 'inner')
                            ->field('zh.bn,zh.parent_bn,zh.name as zh_name,zh.url,en.name as en_name ')
                            ->where($data)->order($order)->select();
            redisHashSet('Market_Area', $redis_key, json_encode($result));

            return $result;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return [];
        }
    }

    /**
     * Description of 获取总数
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            $redis_key = md5(json_encode($data)) . '_COUNT';
            if (redisHashExist('Market_Area', $redis_key)) {
                return redisHashGet('Market_Area', $redis_key);
            }
            $count = $this->where($data)->count();

            redisHashSet('Market_Area', $redis_key, $count);

            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
    }

    /**
     * Description of 详情
     * @param string $bn 区域简码
     * @param string $lang 语言 默认英文
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function info($bn = '', $lang = 'en') {
        $where['bn'] = $bn;
        $where['lang'] = $lang;
        $redis_key = md5(json_encode($where));
        if (redisHashExist('Market_Area', $redis_key)) {
            return json_decode(redisHashGet('Market_Area', $redis_key), true);
        }
        if (!empty($where)) {
            $row = $this->where($where)
                    ->field('id,lang,bn,name,url')
                    ->find();
            redisHashSet('Market_Area', $redis_key, json_encode($row));
            return $row;
        } else {
            return false;
        }
    }

}
