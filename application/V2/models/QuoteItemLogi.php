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
    protected $joinTable2 = 'erui_rfq.inquiry_item c ON b.inquiry_item_id = c.id';
    protected $joinTable3 = 'erui_rfq.quote d ON a.inquiry_id = d.inquiry_id';
    protected $joinTable4 = 'erui_sys.employee e ON d.biz_quote_by = e.id';
    protected $joinField = 'a.id, a.tax_no, a.rebate_rate, a.export_tariff_rate, a.supervised_criteria, b.sku, b.quote_qty, b.quote_unit, b.net_weight_kg, b.gross_weight_kg, b.package_size,c.buyer_goods_no,c.name,c.name_zh,c.remarks,c.brand,c.unit,c.model,d.from_country,e.name AS quoter'; //q去掉了c.name AS name_zh, c.show_name_loc,

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

        if (!empty($condition['id'])) {
            $where['a.id'] = $condition['id'];
        }

        if (!empty($condition['inquiry_id'])) {
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
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->alias('a')
                        ->join($this->joinTable1, 'LEFT')
                        ->join($this->joinTable2, 'LEFT')
                        ->join($this->joinTable3, 'LEFT')
                        ->join($this->joinTable4, 'LEFT')
                        ->field($this->joinField)
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('a.id')
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

    public function getQuoteItemIds($inquiry_id) {
        if (empty($inquiry_id) || !isNum($inquiry_id)) {
            return ['quote_item_ids' => [], 'logiIds' => []];
        }
        $list = $this
                        ->where(['inquiry_id' => $inquiry_id, 'deleted_flag' => 'N'])
                        ->field('quote_item_id,id,status,tax_no,rebate_rate,export_tariff_rate,supervised_criteria,created_by,created_at')->select();
        if (!empty($list)) {
            $quote_item_ids = [];
            $logiIds = [];
            foreach ($list as $val) {
                $quote_item_ids[] = $val['quote_item_id'];
                $logiIds[$val['quote_item_id']] = $val;
            }
            return ['quote_item_ids' => $quote_item_ids, 'logiIds' => $logiIds];
        } else {
            return ['quote_item_ids' => [], 'logiIds' => []];
        }
    }

    public function UpdateItems($quote_id, $inquiry_id, $user) {

        $quoteItemModel = new QuoteItemModel();
        $quoteItemIds = $quoteItemModel->field('id,reason_for_no_quote')
                ->where(['quote_id' => $quote_id, 'deleted_flag' => 'N'])
                ->select();
        $QuoteItemIds = $this->getQuoteItemIds($inquiry_id);

        $this->where(['inquiry_id' => $inquiry_id, 'deleted_flag' => 'N'])->save(['deleted_flag' => 'Y']);
        $quoteItemLogi_Saves = $quoteItemLogi_Adds = [];
        foreach ($quoteItemIds as $quoteItemId) {
            if (empty($quoteItemId['reason_for_no_quote'])) {
                if (!in_array($quoteItemId['id'], $QuoteItemIds['quote_item_ids'])) {
                    $quoteItemLogi_Adds[] = $this->create([
                        'inquiry_id' => $inquiry_id,
                        'quote_id' => $quote_id,
                        'quote_item_id' => $quoteItemId['id'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $user['id']
                    ]);
                } else {
                    $QuoteItemLogi = $QuoteItemIds['logiIds'][$quoteItemId['id']];
                    //'quote_item_id,id,status,tax_no,rebate_rate,export_tariff_rate,supervised_criteria,created_by,created_at'
                    $quoteItemLogi_Saves[] = $this->create([
                        'id' => $QuoteItemLogi['id'],
                        'inquiry_id' => $inquiry_id,
                        'quote_id' => $quote_id,
                        'quote_item_id' => $quoteItemId['id'],
                        'status' => $quoteItemId['status'],
                        'tax_no' => $quoteItemId['tax_no'],
                        'rebate_rate' => $quoteItemId['rebate_rate'],
                        'export_tariff_rate' => $quoteItemId['export_tariff_rate'],
                        'supervised_criteria' => $quoteItemId['supervised_criteria'],
                        'created_at' => $QuoteItemLogi['created_at'],
                        'created_by' => $QuoteItemLogi['created_by'],
                        'updated_at' => date('Y-m-d H:i:s'),
                        'checked_by' => $user['id'],
                        'deleted_flag' => 'N'
                    ]);
                }
            }
        }
        if (!empty($quoteItemLogi_Adds)) {

            $flag = $this->addAll($quoteItemLogi_Adds, [], false);
            if ($flag === false) {
                return false;
            }
        }
        if (!empty($quoteItemLogi_Saves)) {

            $flag = $this->addAll($quoteItemLogi_Saves, [], true);
            if ($flag === false) {
                return false;
            }
        }
        return true;
    }

}
