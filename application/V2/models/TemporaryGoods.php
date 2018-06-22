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

    public function sync() {



        $where = ['i.deleted_flag' => 'N', 'ii.deleted_flag' => 'N', 'ISNULL(ii.sku) ',
            'NOT EXISTS(select tg.id from ' . $this->getTableName() . ' as tg WHERE tg.inquiry_item_id = ii.id )'
        ];

        $inquiry_table = (new InquiryModel())->getTableName();
        $inquiry_item_model = new InquiryItemModel();

        $count = $inquiry_item_model->alias('ii')
                ->field('ii.id,ii.inquiry_id,ii.model,ii.name,ii.name_zh,i.serial_no,ii.brand,i.created_at,i.created_by')
                ->join($inquiry_table . ' i on i.id=ii.inquiry_id')
                ->where($where)
                ->order('i.created_at asc')
                ->count();

        for ($i = 0; $i < $count; $i += 100) {
            $skus = $inquiry_item_model->alias('ii')
                    ->field('ii.id,ii.inquiry_id,ii.model,ii.name,ii.name_zh,i.serial_no,ii.brand,i.created_at,i.created_by')
                    ->join($inquiry_table . ' i on i.id=ii.inquiry_id')
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
     * @param string $lang 语言
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
     * @param string $lang 语言
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
            $flag = $this->where(['id' => $id])->save(['sku' => $sku]);


            if ($flag !== false) {
                $inquiry_item_model = new InquiryItemModel();
                $f = $inquiry_item_model->where(['id' => $tmpgoods['inquiry_item_id']])->save(['sku' => $sku]);
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
