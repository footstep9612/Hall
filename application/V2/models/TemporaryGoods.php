<?php

/**
 * 临时商品库模型
 * Class TemporaryGoodsModel
 * @author 买买提
 */
class TemporaryGoodsModel extends PublicModel {

    /**
     * @var string
     */
    protected $dbName = 'erui_rfq';

    /**
     * @var string
     */
    protected $tableName = 'temporary_goods';

    /**
     * 默认查询条件
     * @var array
     */
    protected $defaultCondition = ['deleted_flag' => 'N'];

    /**
     * 同步数据到临时库
     * @return mix
     * @author zyg
     */
    public function sync() {



        $where = ['i.deleted_flag' => 'N', 'ii.deleted_flag' => 'N', 'ISNULL(ii.sku) ',
            'NOT EXISTS(select tg.id from ' . $this->getTableName() . ' as tg WHERE tg.inquiry_item_id = ii.id )'
        ];

        $inquiry_table = (new InquiryModel())->getTableName();
        $inquiry_item_model = new InquiryItemModel();

        $count = $inquiry_item_model->alias('ii')
                ->join($inquiry_table . ' i on i.id=ii.inquiry_id')
                ->where($where)
                ->order('i.created_at asc')
                ->count();

        for ($i = 0; $i < $count; $i += 100) {
            $skus = $inquiry_item_model->alias('ii')
                    ->field('ii.id,ii.inquiry_id,ii.model,ii.name,ii.name_zh,i.serial_no,ii.brand,i.created_at,i.created_by')
                    ->join($inquiry_table . ' i on i.id=ii.inquiry_id', 'left')
                    ->where($where)
                    ->order('i.created_at asc')
                    ->limit(0, 100)
                    ->select();
            $this->startTrans();
            foreach ($skus as $item) {

                $flag = $this->addData($item);

                if ($flag === false) {

                    continue;
                }
            }
            $this->commit();
        }
        return true;
        //从询报价sku同步到临时商品库
    }

    /**
     * 条件解析
     * @param mix $condition 搜索条件

     * @return mix
     * @author zyg
     */
    private function _getcondition($condition) {

        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'id', 'string');
        $this->_getValue($where, $condition, 'name', 'like');
        $this->_getValue($where, $condition, 'inquiry_at', 'between');
        if (!empty($condition['relation_flag'])) {
            $where['relation_flag'] = $condition['relation_flag'] === 'Y' ? 'Y' : ($condition['relation_flag'] === 'A' ? 'A' : 'N');
        }

        return $where;
    }

    /**
     * 获取数据列表
     * @param mix $condition 搜索条件

     * @return mix
     * @author zyg
     */
    public function getList(array $condition = []) {
        $where = $this->_getcondition($condition);
        list($row_start, $pagesize) = $this->_getPage($condition);
//        $redis_key = md5(json_encode($where) . $row_start . $pagesize);
//        if (redisHashExist(strtoupper($this->tableName), $redis_key)) {
//            return json_decode(redisHashGet(strtoupper($this->tableName), $redis_key), true);
//        }
        try {
            $item = $this->where($where)
                    ->field('id,sku,inquiry_id, serial_no,inquiry_at,name, name_zh,  brand,  model, '
                            . 'relation_flag,updated_by,  updated_at,  checked_by,  checked_at ')
                    ->order('id desc')
                    ->limit($row_start, $pagesize)
                    ->select();

//            redisHashSet(strtoupper($this->tableName), $redis_key, json_encode($item), 3600);
            return $item;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 获取数据条数
     * @param mix $condition 搜索条件

     * @return mix
     * @author zyg
     */
    public function getCount($condition) {
        $where = $this->_getcondition($condition);

        try {
            $count = $this->where($where)
                    ->count('id');

            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
    }

    /**
     * 获取详情
     * @return mix
     * @author zyg
     */
    public function Info($id, &$error) {
        $where = ['tg.id' => $id];

        try {
            $inquiry_table = (new InquiryModel())->getTableName();
            $quote_item_table = (new QuoteItemModel())->getTableName();
            $inquiry_item_table = (new InquiryItemModel())->getTableName();
            $info = $this
                    ->alias('tg')
                    ->field('tg.id,tg.sku,tg.inquiry_item_id,tg.inquiry_id,tg.serial_no,tg.inquiry_at,'
                            . 'tg.name,tg.name_zh,tg.brand,tg.model,tg.deleted_flag,tg.relation_flag,'
                            . 'tg.updated_by,tg.updated_at,tg.checked_by,tg.checked_at,'
                            . 'ii.qty,ii.unit,i.project_basic_info,qi.supplier_id,qi.id as quote_item_id,'
                            . 'qi.purchase_unit_price,qi.purchase_price_cur_bn,'
                            . 'qi.period_of_validity,qi.gross_weight_kg,qi.package_mode,'
                            . 'qi.goods_source,qi.stock_loc,qi.delivery_days,qi.package_size')
                    ->join($inquiry_table . ' i on i.id=tg.inquiry_id and i.deleted_flag=\'N\'')
                    ->join($inquiry_item_table . ' ii on ii.id=tg.inquiry_item_id and ii.deleted_flag=\'N\'')
                    ->join($quote_item_table . ' qi on qi.inquiry_item_id=tg.inquiry_item_id  and qi.deleted_flag=\'N\'', 'left')
                    ->where($where)
                    ->find();

            if (empty($info)) {
                $error = '临时商品不存在!';
                return null;
            }

            if (!empty($info['supplier_id'])) {

                $supplier = (new SupplierModel())->field('name')
                                ->where(['id' => $info['supplier_id'], 'deleted_flag' => 'N'])->find();
                if ($supplier) {
                    $info['supplier_name'] = $supplier['name'];
                } else {
                    $info['supplier_name'] = '';
                }
            } else {
                $info['supplier_name'] = '';
            }


            return $info;
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            Log::write($ex->getMessage(), Log::ERR);
            return [];
        }
    }

    /**
     * 获取数据条数
     * @param string $id ID
     * @param string $sku SKU
     * @return mix
     * @author zyg
     */
    public function Relation($id, $sku, &$error = null) {


        try {
            $this->startTrans();
            $tmpgoods = $this->where(['id' => $id])->find();
            if (!$tmpgoods) {
                $error = '临时询单商品不存在!';
                return false;
            }
            $where = [];
            if (empty($tmpgoods['name']) || empty($tmpgoods['brand']) || empty($tmpgoods['name'])) {
                $where = ['id' => $id];
                $InquiryItem_where = ['id' => $tmpgoods['inquiry_item_id']];
            } else {
                $InquiryItem_where = $where = ['name' => $tmpgoods['name'],
                    'brand' => $tmpgoods['brand'],
                    'model' => $tmpgoods['model']
                ];
                if (!empty($tmpgoods['name_zh'])) {
                    $InquiryItem_where['name_zh'] = $where['name_zh'] = $tmpgoods['name_zh'];
                } else {
                    $InquiryItem_where[] = $where[] = 'isnull(name_zh)';
                }
            }
            $flag = $this->where($where)->save(['sku' => $sku,
                'relation_flag' => 'Y',
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s')]);

            if ($flag !== false) {
                $inquiry_item_model = new InquiryItemModel();
                $f = $inquiry_item_model->where($InquiryItem_where)->save(['sku' => $sku,
                    'updated_by' => defined('UID') ? UID : 0,
                    'updated_at' => date('Y-m-d H:i:s')]);

                if ($f === false) {
                    $this->rollback();
                    $error = '更新询单项SKU失败!';
                    return false;
                }
            } elseif ($flag === false) {
                $this->rollback();
                $error = '关联SKU失败!';
                return false;
            }
            $this->commit();
            return $flag;
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 获取数据条数
     * @param string $id ID
     * @param string $sku SKU
     * @return mix
     * @author zyg
     */
    public function addData($item) {


        try {
            $item['inquiry_at'] = $item['created_at'];
            $item['inquiry_item_id'] = $item['id'];
            unset($item['id']);
            $data = $this->create($item);


            return $this->add($data);
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

}
