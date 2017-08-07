<?php

/**
 * Created by PhpStorm.
 * User: zyg
 * Date: 2017/8/5
 * Time: 19:46
 */
class VatariffModel extends PublicModel {

    protected $dbName = 'erui2_config'; //数据库名称
    protected $tableName = 'va_tariff'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /*
     * 条件id,bn,country_bn,name,lang,port_type,trans_mode,address,longitude,latitude
     */

    function getCondition($condition) {
        $where = [];
        if (isset($condition['id']) && $condition['id']) {
            $where['vt.id'] = $condition['id'];
        }
        if (isset($condition['status']) && $condition['status']) {
            $where['vt.status'] = $condition['status'];
        } else {
            $where['vt.status'] = 'VALID';
        }
        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = $condition['keyword'];
            $employee_model = new EmployeeModel();
            $userids = $employee_model->getUseridsByUserName($keyword);
            if ($userids) {
                $map['vt.created_by'] = ['in', $userids];
                $map['c.name'] = ['like', '%' . $keyword . '%'];
                $map['_logic'] = 'or';
                $where['_complex'] = $map;
            } else {
                $where['c.name'] = ['like', '%' . $keyword . '%'];
            }
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
    public function getList($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'vt.id,vt.country_bn,vt.value_added_tax,vt.tariff,'
                    . 'vt.created_by,vt.created_at,c.name as country_name';

            $pagesize = 10;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }
            $from = ($current_no - 1) * $pagesize;
            $result = $this->alias('vt')
                    ->join('erui2_dict.country c on vt.country_bn=c.bn and c.lang=\'zh\'', 'left')
                    ->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);

            return array();
        }
    }

    /**
     * 修改数据
     * @param  array $update_data 
     * @param  int $uid 
     * @return bool
     * @author jhw
     */
    public function update_data($update_data, $uid = 0) {
        if (!isset($update_data['id']) || !$update_data['id']) {
            return false;
        }
        $update_data['updated_by'] = $uid;
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        $where['id'] = $update_data['id'];
        $data = $this->create($update_data);
        try {
            $flag = $this->where($where)->save($data);
            return $flag;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    public function Exits($where) {

        return $this->where($where)->find();
    }

    /**
     * Description of 更新目的国 增值税、关税
     * @param  int $id id
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   目的国 增值税、关税
     */
    public function info($id = '') {
        if ($id) {
            $where['id'] = $id;
        } else {
            return [];
        }
        $field = 'id,country_bn,value_added_tax,tariff,created_by,created_at';
        try {
            return $this->field($field)->where($where)
                            ->find();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = [], $uid = 0) {
        $create['created_by'] = $uid;
        $create['created_at'] = date('Y-m-d H:i:s');

        $data = $this->create($create);
        $data['value_added_tax'] = number_format($data['value_added_tax'], 4, '.', '');
        $data['tariff'] = number_format($data['tariff'], 4, '.', '');
        try {
            $flag = $this->add($data);
            return $flag;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
