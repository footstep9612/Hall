<?php
/*
 * @desc 报价单物流费用模型
 * 
 * @author liujf 
 * @time 2017-08-02
 */
class QuoteLogiFeeModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_logi_fee';
    protected $joinTable1 = 'erui_rfq.quote b ON a.quote_id = b.id';
    protected $joinTable2 = 'erui_rfq.inquiry c ON a.inquiry_id = c.id';
    protected $joinTable3 = 'erui_sys.employee d ON c.logi_agent_id = d.id';
    protected $joinTable4 = 'erui_dict.country e ON c.country_bn = e.bn AND e.lang = \'zh\'';
    protected $joinField = 'a.*, b.trade_terms_bn, b.from_country, b.from_port, b.trans_mode_bn, b.to_country, b.to_port, b.package_mode, b.box_type_bn, b.delivery_addr, b.dispatch_place, b.quote_remarks, b.total_logi_fee, b.total_insu_fee, b.total_exw_price, b.total_quote_price, c.serial_no, d.name';
    protected $joinField_ = 'a.*, b.period_of_validity, c.serial_no, c.buyer_name, c.agent_id, e.name AS country_name';
			    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-18
     */
    public function getWhere($condition = []) {
         
        $where = [];
         
        if(!empty($condition['quote_id'])) {
            $where['quote_id'] = $condition['quote_id'];
        }
        
        if(!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        }
    
        $where['deleted_flag'] = 'N';
         
        return $where;
    
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
        
        if(!empty($condition['inquiry_id'])) {
            $where['a.inquiry_id'] = $condition['inquiry_id'];
        }
        
        if(!empty($condition['status'])) {
            $where['a.status'] = $condition['status'];
        }
        
        if(!empty($condition['country_bn'])) {
            $where['c.country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }
        
        if(!empty($condition['serial_no'])) {
            $where['c.serial_no'] = ['like', '%' . $condition['serial_no'] . '%'];
        }
        
        if(!empty($condition['buyer_name'])) {
            $where['c.buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }
        
        if (!empty($condition['agent_id'])) {
            $where['c.agent_id'] = $condition['agent_id'];
        }
        
        if(!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])){
            $where['c.created_at'] = [
                ['egt', $condition['start_inquiry_time']],
                ['elt', $condition['end_inquiry_time'] . ' 23:59:59']
            ];
        }
        
        if (!empty($condition['market_agent_id'])) {
            if (empty($condition['agent_id'])) {
                $quoter['c.agent_id'] = ['in', $condition['market_agent_id']];
            } else {
                $quoter['c.agent_id'] = [['eq', $condition['agent_id']], ['in', $condition['market_agent_id']], 'and'];
            }
            $quoter['a.status'] = ['neq', 'QUOTED'];
            
            $checker['a.checked_by'] = $condition['user_id'];
            $checker['a.status'] = 'QUOTED';
            
            $map[] = $quoter;
            $map[] = $checker;
            $map['_logic'] = 'or';
            $where[] = $map;
         }
         
         /*if (!empty($condition['logi_agent_id'])) {
          $where['a.logi_agent_id'] = [['eq', $condition['logi_agent_id']], ['exp', 'is null'], 'or'];
         }*/
        
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
                                 ->join($this->joinTable4, 'LEFT')
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
                            ->join($this->joinTable4, 'LEFT')
                            ->field($this->joinField_)
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
     * @time 2017-08-18
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
     * @time 2017-08-02
     */
    public function getJoinDetail($condition = []) {
         
        $where = $this->getJoinWhere($condition);
         
        return $this->alias('a')
                            ->join($this->joinTable1, 'LEFT')
                            ->join($this->joinTable2, 'LEFT')
                            ->join($this->joinTable3, 'LEFT')
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
    
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        } else {
            return false;
        }
    
        return $this->where($where)->save(['status' => $status]);
    }
}
