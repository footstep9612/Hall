<?php
/*
 * @desc 报价单项物流报价模型
 * 
 * @author liujf 
 * @time 2017-08-02
 */
class QuoteItemLogiModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item_logi';
    protected $joinTable1 = 'erui_rfq.quote_item b ON a.quote_item_id = b.id';
    protected $joinTable2 = 'erui_goods.goods c ON b.sku = c.sku AND c.lang = \'zh\'';
    protected $joinTable3 = 'erui_sys.employee d ON b.bizline_agent_id = d.id';
    protected $joinField = 'a.id, a.tax_no, a.rebate_rate, a.export_tariff_rate, a.supervised_criteria, b.sku, b.quote_qty, b.quote_unit, b.net_weight_kg, b.gross_weight_kg, b.package_size, c.name AS name_zh, c.show_name_loc, d.name AS quoter';
			    
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
        
        if(!empty($condition['id'])) {
            $where['a.id'] = $condition['id'];
        }
         
        if(!empty($condition['inquiry_id'])) {
            $where['a.inquiry_id'] = $condition['inquiry_id'];
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
    
        //$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        //$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
    
        return $this->alias('a')
                            ->join($this->joinTable1, 'LEFT')
                            ->join($this->joinTable2, 'LEFT')
                            ->join($this->joinTable3, 'LEFT')
                            ->field($this->joinField)
                            ->where($where)
                            //->page($currentPage, $pageSize)
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
}
