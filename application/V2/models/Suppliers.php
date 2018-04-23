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
    protected $joinTable5 = 'erui_supplier.supplier_agent f ON a.id = f.supplier_id AND f.agent_type = \'DEVELOPER\'';
    protected $joinField = 'a.*, b.name AS org_name, f.agent_id, e.sign_agreement_end_time';
    protected $joinField_ = 'a.*, b.name AS org_name, c.name AS country_name, d.bank_name, d.bank_account, d.address AS bank_address, e.sign_agreement_flag, e.sign_agreement_time, e.sign_agreement_end_time, e.providing_sample_flag, e.distribution_products, e.est_time_arrival, e.distribution_amount, e.stocking_place, e.info_upload_flag, e.photo_upload_flag';

    protected $exportFields = 'a.id,a.name,a.social_credit_code,a.created_at,a.created_by,a.checked_at,a.checked_by,a.org_id,a.erui_status, b.name AS org_name';

    protected $listFields = '';

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
        $where['a.status'] = ['neq', 'OVERDUE'];

        if (!empty($condition['id'])) {
            $where['a.id'] = $condition['id'];
        }

        if (!empty($condition['supplier_no'])) {
            $where['a.supplier_no'] = ['like', '%' . $condition['supplier_no'] . '%'];
        }

        if (!empty($condition['name'])) {
            $where['a.name'] = ['like', '%' . $condition['name'] . '%'];
        }
        
        if (!empty($condition['supplier_level'])) {
            $where['a.supplier_level'] = $condition['supplier_level'];
        }

        if (!empty($condition['status'])) {
            $where['a.status'] = [['eq', $condition['status']], $where['a.status']];
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

        if (isset($condition['org_id'])) {
            $where['a.org_id'] = ['in', $condition['org_id'] ? : ['-1']];
        }
        
        if (isset($condition['agent_ids'])) {
            $where['f.agent_id'] = ['in', $condition['agent_ids'] ? : ['-1']];
        }
        
        if (isset($condition['created_ids'])) {
            $where['a.created_by'] = ['in', $condition['created_ids'] ? : ['-1']];
        }

        if (isset($condition['checked_ids'])) {
            $where['a.checked_by'] = ['in', $condition['checked_ids'] ? : ['-1']];
        }
        
        if (isset($condition['supplier_ids'])) {
            $where['a.id'] = ['in', $condition['supplier_ids'] ? : ['-1']];
        }
        
        if (isset($condition['qualification_status'])) {
            $where['a.status'] = $condition['qualification_status'];
        }

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

        if (isset($condition['sign_agreement_end']) && $condition['sign_agreement_end']=='Y') {
            $this->joinTable4 = 'erui_supplier.supplier_extra_info e ON a.id = e.supplier_id where to_days(sign_agreement_end_time)-to_days(now()) <=30';
            $count = $this->alias('a')->join($this->joinTable1, 'LEFT')->join($this->joinTable5, 'LEFT')->join($this->joinTable4, 'LEFT')->where($where)->where('')->count('a.id');
            return $count > 0 ? $count : 0;
        }elseif (isset($condition['sign_agreement_end']) && $condition['sign_agreement_end']=='N') {
            $count = $this->alias('a')->join($this->joinTable1, 'LEFT')->join($this->joinTable5, 'LEFT')->join($this->joinTable4, 'LEFT')->where($where)->where('')->count('a.id');
            return $count > 0 ? $count : 0;
        }

        if (isset($condition['expiry_of_qualification']) && $condition['expiry_of_qualification']=='Y') {
            list($data, $count) = (new SupplierQualificationModel)->getExpiryQualificationsListWithPaginationBy($condition);
            return $count > 0 ? $count : 0;

        }elseif (isset($condition['expiry_of_qualification']) && $condition['expiry_of_qualification']=='N') {
            list($data, $count) = (new SupplierQualificationModel)->getExpiryQualificationsListWithPaginationBy($condition, 'RIGHT');
            return $count > 0 ? $count : 0;
        }

        $count = $this->alias('a')
                                 ->join($this->joinTable1, 'LEFT')
                                 ->join($this->joinTable5, 'LEFT')
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

        if (isset($condition['sign_agreement_end']) && $condition['sign_agreement_end']=='Y') {
            $this->joinTable4 = 'erui_supplier.supplier_extra_info e ON a.id = e.supplier_id where to_days(sign_agreement_end_time)-to_days(now()) <= 30';
            return $this->alias('a')
                ->join($this->joinTable1, 'LEFT')
                ->join($this->joinTable5, 'LEFT')
                ->join($this->joinTable4, 'LEFT')
                ->field($this->joinField)
                ->where($where)
                ->where('')
                ->page($currentPage, $pageSize)
                ->order('a.id DESC')
                ->select();
        }elseif (isset($condition['sign_agreement_end']) && $condition['sign_agreement_end']=='N') {
            $this->joinTable4 = 'erui_supplier.supplier_extra_info e ON a.id = e.supplier_id where to_days(sign_agreement_end_time)-to_days(now()) >= 30';
            return $this->alias('a')
                ->join($this->joinTable1, 'LEFT')
                ->join($this->joinTable5, 'LEFT')
                ->join($this->joinTable4, 'LEFT')
                ->field($this->joinField)
                ->where($where)
                ->where('')
                ->page($currentPage, $pageSize)
                ->order('a.id DESC')
                ->select();
        }

        if (isset($condition['expiry_of_qualification']) && $condition['expiry_of_qualification']=='Y') {

            list($data, $count) = (new SupplierQualificationModel)->getExpiryQualificationsListWithPaginationBy($condition);

            $list = [];
            foreach ($data as $item) {
                $list[] = (new SupplierQualificationModel)->alias('a')
                                ->join('erui_supplier.supplier s ON a.supplier_id=s.id', 'LEFT')
                                ->join('erui_sys.org b ON s.org_id = b.id', 'LEFT')
                                ->join('erui_supplier.supplier_extra_info e ON a.id = e.supplier_id ', 'LEFT')
                                ->join('erui_supplier.supplier_agent f ON a.id = f.supplier_id AND f.agent_type = \'DEVELOPER\'', 'LEFT')
                                ->field('s.*, b.name AS org_name, f.agent_id, e.sign_agreement_end_time,a.expiry_date')
                                ->where(['a.supplier_id' => $item['supplier_id']])
                                ->find();
            }
            return $list;

        }elseif (isset($condition['expiry_of_qualification']) && $condition['expiry_of_qualification']=='N') {
            list($data, $count) = (new SupplierQualificationModel)->getExpiryQualificationsListWithPaginationBy($condition, 'RIGHT');

            $list = [];
            foreach ($data as $item) {
                $list[] = (new SupplierQualificationModel)->alias('a')
                    ->join('erui_supplier.supplier s ON a.supplier_id=s.id', 'LEFT')
                    ->join('erui_sys.org b ON s.org_id = b.id', 'LEFT')
                    ->join('erui_supplier.supplier_extra_info e ON a.id = e.supplier_id ', 'LEFT')
                    ->join('erui_supplier.supplier_agent f ON a.id = f.supplier_id AND f.agent_type = \'DEVELOPER\'', 'LEFT')
                    ->field('s.*, b.name AS org_name, f.agent_id, e.sign_agreement_end_time,a.expiry_date')
                    ->where(['a.supplier_id' => $item['supplier_id']])
                    ->find();
            }
            return $list;
        }

        return $this->alias('a')
                        ->join($this->joinTable1, 'LEFT')
                        ->join($this->joinTable5, 'LEFT')
                        ->join($this->joinTable4, 'LEFT')
                        ->field($this->joinField)
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('a.id DESC')
                        ->select();
    }

    /**
     * 获取导出数据
     * @param array $condition
     * @return mixed
     * @author 买买提
     * @time 2018-03-19
     */
    public function getJoinListForExport($condition = []) {

        $where = $this->getJoinWhere($condition);

        unset($where['a.status']);

        if (!empty($condition['erui_status'])) {
            $where['a.erui_status'] =  $condition['erui_status'];
        }else{
            $where['a.erui_status'] = ['in', ['CHECKING', 'VALID'] ];
        }
        //$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        //$pageSize = empty($condition['pageSize']) ? 500 : $condition['pageSize'];

        return $this->alias('a')
            ->join($this->joinTable1, 'LEFT')
            ->join($this->joinTable5, 'LEFT')
            ->field($this->exportFields)
            ->where($where)
           // ->page($currentPage, $pageSize)
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

        // 去掉删除条件
        unset($where['a.deleted_flag']);

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
        // 供应商编码
        $condition['supplier_no'] = $condition['serial_no'] = $this->getSupplierNo();

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

    /**
     * @desc 根据供应商ID获取供应商名称
     *
     * @param array $supplier_ids
     * @return bool
     * @author zyg
     * @time 2017-12-09
     */
    public function getSupplierNameByIds($supplier_ids = []) {


        $where['id'] = ['deleted_flag' => 'Y'];
        if (!empty($supplier_ids)) {
            $where['id'] = ['in', $supplier_ids];
        } else {
            return [];
        }
        $map['name'] = ['neq', ''];
        $map[] = '`name` is not null';
        $map['_logic'] = 'and';
        $where['_complex'] = $map;
        $data = $this->field('id,name')->where($where)->select();
        $ret = [];
        if ($data) {
            foreach ($data as $val) {
                $ret[$val['id']] = $val;
            }
        }
        return $ret;
    }
    
    /**
     * @desc 获取生成的供应商编码
     *
     * @return string
     * @author liujf
     * @time 2018-02-07
     */
    public function getSupplierNo() {
        $today = date('Ymd');
        $serialNo = $this->where(['serial_no' => ['like', $today . '%']])->order('id DESC')->getField('serial_no');
        $no = $serialNo ? intval(substr($serialNo, 8)) + 1 : 1;
        return $today . str_pad($no, 6, '0', STR_PAD_LEFT);
    }

}
