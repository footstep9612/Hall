<?php

/*
 * @desc 供应商模型
 *
 * @author liujf
 * @time 2017-11-10
 */

class SuppliersModel extends PublicModel {

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier';
    protected $joinTable1 = 'erui_sys.org b ON a.org_id = b.id';
    protected $joinTable2 = 'erui_dict.country c ON a.country_bn = c.bn ';
    protected $joinTable3 = 'erui_supplier.supplier_bank_info d ON a.id = d.supplier_id ';
    protected $joinTable4 = 'erui_supplier.supplier_extra_info e ON a.id = e.supplier_id ';
    protected $joinField = 'a.*, b.name AS org_name';
    protected $joinField_ = 'a.*, b.name AS org_name, c.name AS country_name, d.bank_name, d.bank_account, d.address AS bank_address, e.sign_agreement_flag, e.sign_agreement_time, e.providing_sample_flag, e.distribution_products, e.est_time_arrival, e.distribution_amount, e.stocking_place, e.info_upload_flag, e.photo_upload_flag';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-10
     */
    public function getWhere($condition = []) {

        $where['deleted_flag'] = 'N';

        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        }

        return $where;
    }

    /**
     * @desc 获取关联查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-10
     */
    public function getJoinWhere($condition = []) {

        $where['a.deleted_flag'] = 'N';

        if (!empty($condition['id'])) {
            $where['a.id'] = $condition['id'];
        }

        if (!empty($condition['supplier_no'])) {
            $where['a.supplier_no'] = ['like', '%' . $condition['supplier_no'] . '%'];
        }

        if (!empty($condition['name'])) {
            $where['a.name'] = ['like', '%' . $condition['name'] . '%'];
        }

        if (!empty($condition['status'])) {
            $where['a.status'] = $condition['status'];
        }

        if (!empty($condition['check_start_time']) && !empty($condition['check_end_time'])) {
            $where['a.checked_at'] = [
                ['egt', $condition['check_start_time']],
                ['elt', $condition['check_end_time'] . ' 23:59:59']
            ];
        }

        if (!empty($condition['create_start_time']) && !empty($condition['create_end_time'])) {
            $where['a.created_at'] = [
                ['egt', $condition['create_start_time']],
                ['elt', $condition['create_end_time'] . ' 23:59:59']
            ];
        }
//
        if (isset($condition['org_id'])) {
            $where['a.org_id'] = ['in', $condition['org_id'] ?: ['-1']];
        }
//        if (isset($condition['org_id'])) {
//            $map1['a.org_id'] = ['in', $condition['org_id'] ?: ['-1']];
//            $map1[] = 'a.org_id is null';
//            $map1['_logic'] = 'or';
//            $where['_complex'] = $map1;
//        }

        return $where;
    }

    /**
     * @desc 获取关联记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-11-10
     */
    public function getJoinCount($condition = []) {

        $where = $this->getJoinWhere($condition);

        $count = $this->alias('a')
                ->join($this->joinTable1, 'LEFT')
                ->where($where)
                ->count('a.id');

        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取关联列表
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-10
     */
    public function getJoinList($condition = []) {

        $where = $this->getJoinWhere($condition);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->alias('a')
                        ->join($this->joinTable1, 'LEFT')
                        ->field($this->joinField)
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('a.id DESC')
                        ->select();
    }

    /**
     * @desc 获取详情
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-10
     */
    public function getDetail($condition = []) {

        $where = $this->getWhere($condition);

        return $this->where($where)->find();
    }

    /**
     * @desc 获取关联详情
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-10
     */
    public function getJoinDetail($condition = []) {

        $where = $this->getJoinWhere($condition);

        return $this->alias('a')
                        ->join($this->joinTable1, 'LEFT')
                        ->join($this->joinTable2, 'LEFT')
                        ->join($this->joinTable3, 'LEFT')
                        ->join($this->joinTable4, 'LEFT')
                        ->field($this->joinField_)
                        ->where($where)
                        ->find();
    }

    /**
     * @desc 添加记录
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-11-10
     */
    public function addRecord($condition = []) {

        $data_t_supplier = $this->field('max(serial_no) as serial_no')->find(); //($this->put_data);
        if ($data_t_supplier && substr($data_t_supplier['serial_no'], 0, 8) == date("Ymd")) {
            $no = substr($data_t_supplier['serial_no'], -1, 6);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = date("Ymd") . substr($new_num, 1, 6); //即截取掉最前面的“1”
        $condition['serial_no'] = $real_num;
        if (!empty($condition['serial_no'])) {
            $condition['supplier_no'] = $condition['serial_no'];
        }
        $data = $this->create($condition);
        return $this->add($data);
    }

    /**
     * @desc 修改信息
     *
     * @param array $where , $condition
     * @return bool
     * @author liujf
     * @time 2017-11-10
     */
    public function updateInfo($where = [], $condition = []) {

        $data = $this->create($condition);

        return $this->where($where)->save($data);
    }

    /**
     * @desc 删除记录
     *
     * @param array $condition
     * @return bool
     * @author liujf
     * @time 2017-11-10
     */
    public function delRecord($condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        } else {
            return false;
        }

        return $this->where($where)->save(['deleted_flag' => 'Y']);
    }

}
