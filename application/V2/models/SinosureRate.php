<?php

/*
 * @desc 信保税率模型
 *
 * @author liujf
 * @time 2017-08-01
 */

class SinosurerateModel extends PublicModel {

    protected $dbName = 'erui2_config';
    protected $tableName = 'sinosure_rate';
    protected $joinTable = 'erui2_sys.employee b ON a.created_by = b.id';
    protected $joinField = 'a.*, b.name';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @desc 获取关联查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-01
     */
    public function getJoinWhere($condition = []) {

        $where = [];

        if (!empty($condition['country_bn'])) {
            $where['a.country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }

        if (!empty($condition['name'])) {
            $where['b.name'] = ['like', '%' . $condition['name'] . '%'];
        }
        if (isset($condition['keyword']) && $condition['keyword']) {
            $map = [];
            $keyword = $condition['keyword'];
            $country_model = new CountryModel();
            $bns = $country_model->getBnByName($keyword);

            if ($bns) {
                $bns[] = $keyword;
                $map['a.country_bn'][] = ['in', $bns];


                $this->_getValue($map, $condition, 'keyword', 'like', 'b.name');
                $map['_logic'] = 'or';
                $where['_complex'] = $map;
            } else {
                $map['a.country_bn'] = $keyword;
                $this->_getValue($map, $condition, 'keyword', 'like', 'b.name');
                $map['_logic'] = 'or';
                $where['_complex'] = $map;
            }
        }
        if (!empty($condition['keyword'])) {

        }

        $where['a.deleted_flag'] = 'N';

        return $where;
    }

    /**
     * @desc 获取关联记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-08-01
     */
    public function getJoinCount($condition = []) {

        $where = $this->getJoinWhere($condition);
        try {
            $count = $this->alias('a')
                    ->join($this->joinTable, 'LEFT')
                    ->where($where)
                    ->count('a.id');

            return $count > 0 ? $count : 0;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    public function Exits($where) {

        return $this->where($where)->find();
    }

    /**
     * @desc 获取关联列表
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-01
     */
    public function getJoinList($condition = []) {

        $where = $this->getJoinWhere($condition);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        try {
            return $this->alias('a')
                            ->join($this->joinTable, 'LEFT')
                            ->field($this->joinField)
                            ->where($where)
                            ->page($currentPage, $pageSize)
                            ->order('a.id DESC')
                            ->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * @desc 获取关联详情
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-01
     */
    public function getJoinDetail($condition = []) {
        if (!empty($condition['id'])) {
            $where['a.id'] = $condition['id'];
        } else {
            return [];
        }
        // $where = $this->getJoinWhere($condition);
        try {
            return $this->alias('a')
                            ->join($this->joinTable, 'LEFT')
                            ->field($this->joinField)
                            ->where($where)
                            ->find();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * @desc 添加记录
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-08-01
     */
    public function addRecord($condition = []) {
        if (isset($condition['id'])) {
            unset($condition['id']);
        }
        $condition['deleted_flag'] = isset($condition['deleted_flag']) && $condition['deleted_flag'] === 'Y' ? 'Y' : 'N';
        $condition['settle_period_e_tt'] = empty($condition['settle_period_e_tt']) ? 0 : floatval($condition['settle_period_e_tt']);
        $condition['tax_rate_e_tt'] = empty($condition['tax_rate_e_tt']) ? 0 : floatval($condition['tax_rate_e_tt']);
        $condition['settle_period_e_lc'] = empty($condition['settle_period_e_lc']) ? 0 : floatval($condition['settle_period_e_lc']);
        $condition['tax_rate_e_lc'] = empty($condition['tax_rate_e_lc']) ? 0 : floatval($condition['tax_rate_e_lc']);
        $condition['settle_period_k_tt'] = empty($condition['settle_period_k_tt']) ? 0 : floatval($condition['settle_period_k_tt']);
        $condition['tax_rate_k_tt'] = empty($condition['tax_rate_k_tt']) ? 0 : floatval($condition['tax_rate_k_tt']);
        $condition['settle_period_k_lc'] = empty($condition['settle_period_k_lc']) ? 0 : floatval($condition['settle_period_k_lc']);
        $condition['tax_rate_k_lc'] = empty($condition['tax_rate_k_lc']) ? 0 : floatval($condition['tax_rate_k_lc']);
        $data = $this->create($condition);
        try {
            return $this->add($data);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * @desc 修改信息
     *
     * @param array $where , $condition
     * @return bool
     * @author liujf
     * @time 2017-08-01
     */
    public function updateInfo($where = [], $condition = []) {
        try {
            if (isset($condition['id'])) {
                unset($condition['id']);
            }
            $condition['deleted_flag'] = isset($condition['deleted_flag']) && $condition['deleted_flag'] === 'Y' ? 'Y' : 'N';
            $condition['settle_period_e_tt'] = empty($condition['settle_period_e_tt']) ? 0 : floatval($condition['settle_period_e_tt']);
            $condition['tax_rate_e_tt'] = empty($condition['tax_rate_e_tt']) ? 0 : floatval($condition['tax_rate_e_tt']);
            $condition['settle_period_e_lc'] = empty($condition['settle_period_e_lc']) ? 0 : floatval($condition['settle_period_e_lc']);
            $condition['tax_rate_e_lc'] = empty($condition['tax_rate_e_lc']) ? 0 : floatval($condition['tax_rate_e_lc']);
            $condition['settle_period_k_tt'] = empty($condition['settle_period_k_tt']) ? 0 : floatval($condition['settle_period_k_tt']);
            $condition['tax_rate_k_tt'] = empty($condition['tax_rate_k_tt']) ? 0 : floatval($condition['tax_rate_k_tt']);
            $condition['settle_period_k_lc'] = empty($condition['settle_period_k_lc']) ? 0 : floatval($condition['settle_period_k_lc']);
            $condition['tax_rate_k_lc'] = empty($condition['tax_rate_k_lc']) ? 0 : floatval($condition['tax_rate_k_lc']);
            $condition['updated_by'] = defined('UID') ? UID : 0;
            $condition['updated_at'] = date('Y-m-d H:i:s');
            $data = $this->create($condition);

            return $this->where($where)->save($data);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * @desc 删除记录
     *
     * @param array $condition
     * @return bool
     * @author liujf
     * @time 2017-08-01
     */
    public function delRecord($condition = []) {

        if (!empty($condition['r_id'])) {
            $where['id'] = ['in', explode(',', $condition['r_id'])];
        } elseif (!empty($condition['id'])) {

            $where['id'] = $condition['id'];
        } else {
            return false;
        }
        try {
            return $this->where($where)->save(['deleted_flag' => 'Y', 'status' => 'DELETED']);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
