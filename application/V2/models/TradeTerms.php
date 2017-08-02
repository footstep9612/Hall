<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TradeTermsModel
 *
 * @author jhw
 * @des 贸易术语
 */
class TradeTermsModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_dict';
    protected $tableName = 'trade_terms';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /*
     * 条件处理
     * @param array $condition 条件
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    function getCondition($condition) {
        $where = [];

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
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */
    public function getList($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'id,terms,description,trans_mode_bn,lang';

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
            return false;
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
    public function getall($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'id,terms,description,trans_mode_bn,lang';

            $result = $this->field($field)
                    ->where($where)
                    ->select();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

}
