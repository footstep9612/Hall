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
            $where['id'] = $condition['id'];
        }

        if (isset($condition['keyword']) && $condition['keyword']) {
            $keyword = $condition['keyword'];
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
            $field = 'vt.id,vt.country_bn,vt.value_added_tax,vt.tariff'
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
        } catch (Exception $e) {
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
        $flag = $this->where($where)->save($data);
        return $flag;
    }

    public function Exits($where) {

        return $this->where($where)->find();
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
        $flag = $this->add($data);
        return $flag;
    }

}
