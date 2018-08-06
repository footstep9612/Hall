<?php

/**
 * @desc 最终报价单明细模型
 * @author 张玉良
 */
class FinalQuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote_item';
    protected $joinTable1 = 'erui_rfq.quote_item b ON a.quote_item_id = b.id';
    protected $joinTable2 = 'erui_rfq.inquiry_item c ON a.inquiry_item_id = c.id';
    protected $joinTable3 = 'erui_rfq.inquiry_item_attach d ON a.inquiry_item_id = d.inquiry_item_id';
    protected $joinField = 'a.id,a.inquiry_id,a.quote_id,a.sku,a.supplier_id,a.exw_unit_price as final_exw_unit_price,a.quote_unit_price as final_quote_unit_price,
	                                           c.qty as quote_qty,c.unit as quote_unit,b.brand,b.exw_unit_price,b.quote_unit_price,b.net_weight_kg,b.gross_weight_kg,b.remarks as final_remarks,
	                                           b.package_mode,b.package_size,b.delivery_days,b.period_of_validity,b.goods_source,b.stock_loc,b.reason_for_no_quote,b.pn,
	                                           c.buyer_goods_no,c.name,c.name_zh,c.model,c.remarks,c.remarks_zh,d.attach_name,d.attach_url';
    protected $finalSkuFields = 'a.id,a.sku,
                                                       b.buyer_goods_no,b.name,b.name_zh,b.qty,b.unit,b.brand,b.model,b.remarks,b.category,
                                                       c.exw_unit_price,c.quote_unit_price,
                                                       a.exw_unit_price final_exw_unit_price,a.quote_unit_price final_quote_unit_price,
                                                       c.gross_weight_kg,c.package_mode,c.package_size,c.delivery_days,c.period_of_validity,c.goods_source,c.stock_loc,c.reason_for_no_quote';

    public function __construct() {
        parent::__construct();
    }

    public function getFinalSku($request) {

        $where = ['a.inquiry_id' => $request['inquiry_id'], 'c.deleted_flag' => 'N'];
        return $this->alias('a')
                        ->join('erui_rfq.inquiry_item b ON a.inquiry_item_id=b.id', 'LEFT')
                        ->join('erui_rfq.quote_item c ON a.quote_item_id=c.id', 'LEFT')
                        ->field($this->finalSkuFields)
                        ->where($where)
                        ->select();
        //p($result);
    }

    public function updateFinalSku($data) {
        foreach ($data as $value) {
            $value['updated_at'] = date('Y-m-d H:i:s');
            $this->save($this->create($value));
        }
        return true;
    }

    /**
     * @desc 获取查询条件
     * @author 张玉良
     * @param $condition array
     * @return $where array
     */
    public function getWhere($condition) {
        $where = array();

        if (!empty($condition['id'])) {
            $where['a.id'] = $condition['a.id'];
        }

        if (!empty($condition['inquiry_id'])) {
            $where['a.inquiry_id'] = $condition['inquiry_id'];
        }

        if (!empty($condition['quote_id'])) {
            $where['a.quote_id'] = $condition['a.quote_id'];
        }

        $where['a.deleted_flag'] = !empty($condition['a.deleted_flag']) ? $condition['a.deleted_flag'] : 'N';
        return $where;
    }

    /**
     * @desc 获取记录总数
     * @author 张玉良
     * @param array $condition
     * @return int $count
     */
    public function getCount($condition) {
        $where = $this->getWhere($condition);

        $count = $this->alias('a')
                ->join($this->joinTable1, 'LEFT')
                ->join($this->joinTable2, 'LEFT')
                ->join($this->joinTable3, 'LEFT')
                ->where($where)
                ->count('a.id');

        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取报价单项目列表
     * @author 张玉良
     * @param array $condition
     * @return array
     */
    public function getItemList($condition) {
        $where = $this->getWhere($condition);

        try {
            $count = $this->getCount($condition);
            $list = $this->alias('a')
                    ->join($this->joinTable1, 'LEFT')
                    ->join($this->joinTable2, 'LEFT')
                    ->join($this->joinTable3, 'LEFT')
                    ->field($this->joinField)
                    ->where($where)
                    ->order('a.id DESC')
                    ->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['count'] = $count;
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = L('NO_DATA');
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * @desc 添加报价单SKU详情
     * @author 张玉良
     * @param array $condition
     * @return array
     */
    public function addItem($condition) {
        $data = $this->create($condition);
        $data['status'] = !empty($condition['status']) ? $condition['status'] : 'ONGOING';
        $data['created_at'] = time();

        try {
            $id = $this->add($data);

            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['data'] = $id;
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * @desc 获取报价单SKU详情
     * @author 张玉良
     * @param array $condition
     * @return array
     */
    public function getDetail($condition) {
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        try {
            $info = $this->where($where)->find();

            if ($info) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['data'] = $info;
            } else {
                $results['code'] = '-101';
                $results['message'] = L('NO_DATA');
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * @desc 修改报价单SKU
     * @author 张玉良
     * @param array  $condition
     * @return array
     */
    public function updateItem($condition = []) {
        $data = $this->create($condition);
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        $data['updated_at'] = $this->getTime();

        try {
            $id = $this->where($where)->save($data);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * @desc 删除报价单SKU
     * @author zhangyuliang 2017-06-29
     * @param array $condition
     * @return array
     */
    public function delItem($condition = []) {
        if (!empty($condition['id'])) {
            $where['id'] = array('in', explode(',', $condition['id']));
        } else {

        }

        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d H:i:s', time());
    }

}
