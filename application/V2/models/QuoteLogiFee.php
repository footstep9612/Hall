<?php
/*
 * @desc 报价单物流费用模型
 * 
 * @author liujf 
 * @time 2017-08-02
 */
class QuoteLogiFeeModel extends PublicModel {

    protected $dbName = 'erui2_rfq';
    protected $tableName = 'quote_logi_fee';
    protected $joinTable1 = 'erui2_rfq.quote b ON a.quote_id = b.id';
    protected $joinTable2 = 'erui2_sys.employee c ON a.updated_by = c.id';
    protected $joinTable3 = 'erui2_rfq.inquiry d ON a.inquiry_id = d.id';
    protected $joinField = 'a.*, d.serial_no, b.trade_terms_bn, b.from_country, b.from_port, b.trans_mode_bn, b.to_country, b.to_port, b.box_type_bn, b.quote_remarks, b.total_insu_fee, b.total_exw_price, b.total_quote_price, c.name';
    protected $joinField_ = 'a.*, d.serial_no, d.country_bn, d.buyer_name, d.agent_id, d.pm_id, d.inquiry_time, b.period_of_validity';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取关联查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-02
     */
    public function getJoinWhere($condition = []) {
         
        $where = [];
         
        if(!empty($condition['quote_id'])) {
            $where['a.quote_id'] = $condition['quote_id'];
        }
        
        if(!empty($condition['status'])) {
            $where['a.status'] = $condition['status'];
        }
        
        if(!empty($condition['country_bn'])) {
            $where['d.country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }
        
        if(!empty($condition['serial_no'])) {
            $where['d.serial_no'] = ['like', '%' . $condition['serial_no'] . '%'];
        }
        
        if(!empty($condition['buyer_name'])) {
            $where['d.buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }
        
        if (!empty($condition['agent_id'])) {
            $where['d.agent_id'] = $condition['agent_id'];
        }
        
        if (!empty($condition['pm_id'])) {
            $where['d.pm_id'] = $condition['pm_id'];
        }   
        
        if(!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])){
            $where['d.inquiry_time'] = [
                ['egt', $condition['start_inquiry_time']],
                ['elt', $condition['end_inquiry_time'] . ' 23:59:59']
            ];
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
     * @time 2017-08-02
     */
    public function getJoinCount($condition = []) {
         
        $where = $this->getJoinWhere($condition);
         
        $count = $this->alias('a')
                                 ->join($this->joinTable1, 'LEFT')
                                 ->join($this->joinTable2, 'LEFT')
                                 ->where($where)
                                 ->count('a.id');
         
        return $count > 0 ? $count : 0;
    }
    
    /**
     * @desc 获取l列表记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-08-07
     */
    public function getListCount($condition = []) {
         
        $where = $this->getJoinWhere($condition);
         
        $count = $this->alias('a')
                                ->join($this->joinTable1, 'LEFT')
                                ->join($this->joinTable2, 'LEFT')
                                ->join($this->joinTable3, 'LEFT')
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
     * @time 2017-08-02
     */
    public function getJoinList($condition = []) {
         
        $where = $this->getJoinWhere($condition);
    
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    
        return $this->alias('a')
                            ->join($this->joinTable1, 'LEFT')
                            ->join($this->joinTable2, 'LEFT')
                            ->join($this->joinTable3, 'LEFT')
                            ->field($this->joinField_)
                            ->where($where)
                            ->page($currentPage, $pageSize)
                            ->order('a.id DESC')
                            ->select();
    }
    
    /**
     * @desc 获取关联详情
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-02
     */
    public function getJoinDetail($condition = []) {
         
        $where = $this->getJoinWhere($condition);
         
        return $this->alias('a')
                            ->join($this->joinTable1, 'LEFT')
                            ->join($this->joinTable2, 'LEFT')
                            ->field($this->joinField)
                            ->where($where)
                            ->find();
    }
    
    /**
     * @desc 添加记录
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-08-02
     */
    public function addRecord($condition = []) {
    
        $data = $this->create($condition);
    
        return $this->add($data);
    }
    
    /**
     * @desc 修改信息
     *
     * @param array $where , $condition
     * @return bool
     * @author liujf
     * @time 2017-08-02
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
     * @time 2017-08-02
     */
    public function delRecord($condition = []) {
    
        if (!empty($condition['r_id'])) {
            $where['id'] = ['in', explode(',', $condition['r_id'])];
        } else {
            return false;
        }
    
        return $this->where($where)->save(['deleted_flag' => 'Y']);
    }
    
    /**
     * @desc 更改状态
     *
     * @param array $condition
     * @param string $status
     * @return bool
     * @author liujf
     * @time 2017-08-08
     */
    public function updateStatus($condition = [], $status) {
    
        if (!empty($condition['quote_id'])) {
            $where['quote_id'] = $condition['quote_id'];
        } else {
            return false;
        }
    
        return $this->where($where)->save(['status' => $status]);
    }
}
