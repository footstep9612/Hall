<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zhongyg
 * @desc 汇率列表
 */
class ExchangeRateModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_config';
    protected $tableName = 'exchange_rate';
    protected $RateUSD = [];

    public function __construct() {
        parent::__construct();
    }

    /*
     * 自动完成
     */

    protected $_auto = array(
        array('created_at', 'getDate', 1, 'callback'),
    );
    /*
     * 自动表单验证
     */
    protected $_validate = array(
        array('cur_bn1', 'require', '币种不能为空'),
        array('cur_bn2', 'require', '承兑币种不能为空'),
        array('effective_date', 'require', '生效日期不能为空'),
        array('created_at', 'require', '创建时间不能为空'),
    );

    /*
     * 获取当前时间
     */

    function getDate() {
        return date('Y-m-d H:i:s');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'id desc') {
        $data['deleted_flag'] = 'N';
        if (!empty($limit)) {
            return $this->field('id,effective_date,cur_bn1,cur_bn2,rate,created_by,created_at')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field('id,effective_date,cur_bn1,cur_bn2,rate,created_by,created_at')
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            try {
                $row = $this->where($where)
                        ->field('id,effective_date,cur_bn1,cur_bn2,rate,created_by,created_at')
                        ->find();
            } catch (Exception $ex) {
                LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
                LOG::write($ex->getMessage(), LOG::ERR);
                return false;
            }
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        if (is_array($id)) {
            $where['id'] = ['in', $id];
        } elseif ($id) {
            $where['id'] = $id;
        }

        if (!empty($id)) {
            try {
                return $this->where($where)->save(['deleted_flag' => 'Y']);
            } catch (Exception $ex) {
                LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
                LOG::write($ex->getMessage(), LOG::ERR);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {
        if (isset($data['effective_date'])) {
            $arr['effective_date'] = $data['effective_date'];
        }
        if (isset($data['cur_bn1'])) {
            $arr['cur_bn1'] = $data['cur_bn1'];
        }
        if (isset($data['cur_bn2'])) {
            $arr['cur_bn2'] = $data['cur_bn2'];
        }
        if (isset($data['rate'])) {
            $arr['rate'] = $data['rate'];
        }

        $arr['deleted_flag'] = 'N';


        if (!empty($where)) {
            try {
                return $this->where($where)->save($arr);
            } catch (Exception $ex) {
                LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
                LOG::write($ex->getMessage(), LOG::ERR);

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = [], $uid = '') {

        if (isset($create['effective_date'])) {
            $arr['effective_date'] = $create['effective_date'];
        }
        if (isset($create['cur_bn1'])) {
            $arr['cur_bn1'] = $create['cur_bn1'];
        }
        if (isset($create['cur_bn2'])) {
            $arr['cur_bn2'] = $create['cur_bn2'];
        }
        if (isset($create['rate'])) {
            $arr['rate'] = $create['rate'];
        }
        $arr['created_at'] = date('Y-m-d H:i:s');
        $arr['created_by'] = defined('UID') ? UID : 0;
        $data = $this->create($arr);

        try {
            return $this->add($data);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 条件
     */

    function _getCondition($condition) {
        $where = [];
        if (isset($condition['effective_date']) && $condition['effective_date']) {
            $where['effective_date'] = $condition['effective_date'];
        }
        if (isset($condition['cur_bn1']) && $condition['cur_bn1']) {
            $where['cur_bn1'] = $condition['cur_bn1'];
        }
        if (isset($condition['cur_bn2']) && $condition['cur_bn2']) {
            $where['cur_bn2'] = $condition['cur_bn2'];
        }
        $where['deleted_flag'] = 'N';
        return $where;
    }

    /*
     * 获取数据
     */

    public function getCount($condition) {
        try {
            $data = $this->_getCondition($condition);
            return $this->where($data)->count();
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
     */
    public function getListbycondition($condition = '') {
        $where = $this->_getCondition($condition);
        try {
            $field = 'id,effective_date,cur_bn1,cur_bn2,rate,created_by,created_at';

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
                    ->order('id desc')
                    ->select();
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return array();
        }
    }

    /**
     * @desc 获取币种兑换汇率
     *
     * @param string $holdCur 持有币种
     * @param string $exchangeCur 兑换币种
     * @return float
     * @author liujf
     * @time 2018-05-16
     */
    public function getRate($holdCur, $exchangeCur = 'CNY', &$error = null) {
        if (!empty($holdCur)) {
            if ($holdCur == $exchangeCur) {
                return 1;
            }
            $exchangeRateModel = new ExchangeRateModel();
            $exchangeRate = $exchangeRateModel
                            ->where(['cur_bn1' => $holdCur, 'cur_bn2' => $exchangeCur])
                            ->order('created_at DESC')->getfield('rate');

            if (empty($exchangeRate)) {
                $exchangeRate = $exchangeRateModel
                                ->where(['cur_bn2' => $holdCur, 'cur_bn1' => $exchangeCur])
                                ->order('created_at DESC')->getfield('rate');
                if ($exchangeRate) {

                    return round(1 / $exchangeRate, 16);
                } else {
                    $error = '汇率不存在!';
                    return false;
                }
            }
            return $exchangeRate;
        } else {
            $error = '持有币种不能为空!';
            return false;
        }
    }

    /**
     * @desc 获取美元兑换汇率
     *
     * @param string $cur 币种
     * @return float
     * @author liujf
     * @time 2018-05-16
     */
    public function getRateToUSD($cur, &$error = null) {


        if (empty($cur)) {
            return 1;
        } elseif ($this->RateUSD[$cur]) {

            return $this->RateUSD[$cur];
        } else {
            $Rate = $this->getRate($cur, 'USD', $error);
            $this->RateUSD[$cur] = $Rate;
            return $Rate;
        }
    }

}
