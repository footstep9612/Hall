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
        //  $inquiry_at = $this->field('created_at')->order('created_at desc')->find();

        $where = ['i.deleted_flag' => 'N',
            'ii.deleted_flag' => 'N',
            'i.status' => ['neq', 'DRAFT'],
            'NOT EXISTS(select tg.id from ' . $this->getTableName() . ' as tg WHERE tg.inquiry_item_id = ii.id )'
        ];
        $inquiry_table = (new InquiryModel())->getTableName();
        $inquiry_item_model = new InquiryItemModel();
        $count = $inquiry_item_model->alias('ii')
                ->join($inquiry_table . ' i on i.id=ii.inquiry_id')
                ->where($where)
                ->count();

        for ($i = 0; $i < $count; $i += 100) {
            $skus = $inquiry_item_model->alias('ii')
                    ->field('ii.updated_at,ii.id,ii.sku,ii.inquiry_id,ii.model,ii.name,ii.name_zh,i.serial_no,ii.brand,i.created_at,i.created_by')
                    ->join($inquiry_table . ' i on i.id=ii.inquiry_id', 'left')
                    ->where($where)
                    ->order('i.created_at asc')
                    ->limit(0, 100)
                    ->select();

            $this->startTrans();
            foreach ($skus as $item) {
                $flag = $this->addData($item, null);
                if ($flag === false) {

                    continue;
                }
            }
            $this->commit();
        }
        $inquiry_table = $inquiry_item_model = null;
        $this->updatesync();

        return true;
        //从询报价sku同步到临时商品库
    }

    private function updatesync() {

        $inquiry_table = (new InquiryModel())->getTableName();
        $inquiry_item_model = new InquiryItemModel();

        $where = ['i.deleted_flag' => 'N',
            'ii.deleted_flag' => 'N',
            'ii.updated_at is not null',
            'i.status' => ['neq', 'DRAFT'],
            ' EXISTS(select tg.id from ' . $this->getTableName() . ' as tg '
            . 'WHERE tg.inquiry_item_id = ii.id and tg.updated_at<ii.updated_at )'
        ];
        $count = $inquiry_item_model->alias('ii')
                ->join($inquiry_table . ' i on i.id=ii.inquiry_id')
                ->where($where)
                ->count();

        for ($i = 0; $i < $count; $i += 100) {
            $skus = $inquiry_item_model->alias('ii')
                    ->field('ii.updated_at,ii.id,ii.sku,ii.inquiry_id,ii.model,ii.name,ii.name_zh,i.serial_no,ii.brand,i.created_at,i.created_by')
                    ->join($inquiry_table . ' i on i.id=ii.inquiry_id', 'left')
                    ->where($where)
                    ->order('i.created_at asc')
                    ->limit(0, 100)
                    ->select();

            $this->startTrans();
            foreach ($skus as $item) {
                $flag = $this->addData($item, true);
                if ($flag === false) {

                    continue;
                }
            }
            $this->commit();
        }
    }

    /**
     * 条件解析
     * @param mix $condition 搜索条件

     * @return mix
     * @author zyg
     */
    private function _getcondition($condition) {

        $where = ['tg.deleted_flag' => 'N',
            'ii.deleted_flag' => 'N',
        ];
        $this->_getValue($where, $condition, 'id', 'string', 'tg.id');
        if (!empty($condition['name'])) {
            $name = trim($condition['name']);

            $map['tg.name'] = ['like', '%' . $name . '%'];
            $map['tg.name_zh'] = ['like', '%' . $name . '%'];
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $this->_getValue($where, $condition, 'inquiry_at', 'between', 'tg.inquiry_at');
        if (!empty($condition['relation_flag'])) {
            $where['tg.relation_flag'] = $condition['relation_flag'] === 'Y' ? 'Y' : ($condition['relation_flag'] === 'A' ? 'A' : 'N');
        }
        $where[] = 'ii.id is not null';
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

            $inquiry_item_table = (new InquiryItemModel())->getTableName();
            $item = $this
                    ->alias('tg')
                    ->join($inquiry_item_table . ' ii on ii.id=tg.inquiry_item_id', 'left')
                    ->where($where)
                    ->field('tg.id,tg.sku,tg.inquiry_id, tg.serial_no,tg.inquiry_at,tg.name, tg.name_zh,  '
                            . 'tg.brand,  tg.model, '
                            . 'tg.relation_flag,tg.updated_by,  tg.updated_at,  tg.checked_by,  tg.checked_at ')
                    ->order('tg.inquiry_at desc')
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
            $inquiry_item_table = (new InquiryItemModel())->getTableName();
            $count = $this->alias('tg')
                    ->join($inquiry_item_table . ' ii on ii.id=tg.inquiry_item_id', 'left')
                    ->where($where)
                    ->count('tg.id');

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
                            . 'ii.qty,ii.unit,ii.remarks,qi.id as quote_item_id,qi.supplier_id,'
                            . 'qi.purchase_unit_price,qi.purchase_price_cur_bn,'
                            . 'qi.period_of_validity,qi.gross_weight_kg,qi.package_mode,'
                            . 'qi.goods_source,qi.stock_loc,qi.delivery_days,qi.package_size')
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
            if (empty($tmpgoods['name']) || empty($tmpgoods['brand']) || empty($tmpgoods['model']) || empty($tmpgoods['name_zh'])) {
                $where = ['id' => $id];
                $InquiryItem_where = ['id' => $tmpgoods['inquiry_item_id']];
            } else {
                $InquiryItem_where = $where = [
                    'name' => $tmpgoods['name'],
                    'name_zh' => $tmpgoods['name_zh'],
                    'brand' => $tmpgoods['brand'],
                    'model' => $tmpgoods['model']
                ];
                $map['id'] = $tmpgoods['inquiry_item_id'];
                $map[] = 'isnull(sku) and id<>' . $tmpgoods['inquiry_item_id'];
                $map1['id'] = $id;
                $map1[] = 'isnull(sku) and id<>' . $id;
                if (!empty($tmpgoods['sku'])) {
                    $map['sku'] = $tmpgoods['sku'];
                    $map1['sku'] = $tmpgoods['sku'];
                }
                if (!empty($tmpgoods['updated_at'])) {
                    $map['updated_at'] = $tmpgoods['updated_at'];
                    $map1['updated_at'] = $tmpgoods['updated_at'];
                }
                $map['_logic'] = 'or';
                $InquiryItem_where['_complex'] = $map;

                $map1['_logic'] = 'or';
                $where['_complex'] = $map1;
//                if (!empty($tmpgoods['name_zh'])) {
//                    $InquiryItem_where['name_zh'] = $where['name_zh'] = $tmpgoods['name_zh'];
//                } else {
//                    $InquiryItem_where[] = $where[] = 'isnull(name_zh)';
//                }
            }
            $flag = $this->where($where)->save([
                'sku' => $sku,
                'relation_flag' => 'Y',
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s')]);

            if ($flag !== false) {
                $inquiry_item_model = new InquiryItemModel();
                $f = $inquiry_item_model
                        ->where($InquiryItem_where)
                        ->save(['sku' => $sku,
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
     * 同步数据
     * @param array $item ID
     * @param string $inquiry_flag SKU
     * @return mix
     * @author zyg
     */
    public function addData($item, $inquiry_flag = false) {


        try {

            if (empty($item)) {
                return false;
            } elseif (empty($item['name']) && empty($item['brand']) && empty($item['model']) && empty($item['name_zh'])) {
                return false;
            }

            $item['inquiry_at'] = $item['created_at'];
            if ($item['sku']) {
                $sku = trim($item['sku']);
                $goods = (new GoodsModel)->where(['sku' => $sku, 'deleted_flag' => 'N'])->find();
                if ($goods) {
                    $item['relation_flag'] = 'Y';
                } else {
                    $item['relation_flag'] = 'N';
                }
            } else {
                $item['relation_flag'] = 'N';
            }
            $item['inquiry_item_id'] = $item['id'];
            unset($item['id']);
            if ($inquiry_flag) {
                $data = $this->create($item);
                return $this->where(['inquiry_item_id' => $item['inquiry_item_id']])->save($data);
            } else {

                $data = $this->create($item);
                return $this->add($data);
            }
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    public function getSku($tmpgoods) {
        try {
            if (empty($tmpgoods['name']) || empty($tmpgoods['brand']) || empty($tmpgoods['name']) || empty($tmpgoods['name_zh'])) {
                return '';
            } else {

                $where = [
                    'name' => $tmpgoods['name'],
                    'name_zh' => $tmpgoods['name_zh'],
                    'brand' => $tmpgoods['brand'],
                    'model' => $tmpgoods['model']
                ];
                $info = $this->field('sku')
                        ->where($where)
                        ->find();

                return isset($info['sku']) ? $info['sku'] : null;
            }
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return '';
        }
    }

    /**
     * 删除数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function deleteData($condition) {
        if (!empty($condition['id'])) {
            $where['inquiry_item_id'] = array('in', explode(',', $condition['id']));
        } elseif (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        } else {
            return false;
        }
        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if (isset($id)) {
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

}
