<?php

/**
 * name: InquiryItem
 * desc: 询单明细表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:54
 */
class InquiryItemModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_item'; //数据表表名
    protected $joinTable = 'erui_rfq.final_quote_item b ON a.id = b.inquiry_item_id AND b.deleted_flag = \'N\'';
    protected $joinTable_ = 'erui_rfq.inquiry_item_attach c ON a.id = c.inquiry_item_id';
    protected $joinField = 'a.qty, a.category, b.quote_unit_price, b.total_quote_price';
    protected $joinField_ = 'a.*, c.attach_name, c.attach_url';
    public $isOil = [
        '石油专用管材',
        '钻修井设备',
        '固井酸化压裂设备',
        '采油集输设备',
        '石油专用工具',
        '石油专用仪器仪表',
        '油田化学材料'
    ]; // 油气
    public $noOil = [
        '通用机械设备',
        '劳动防护用品',
        '消防、医疗产品',
        '电力电工设备',
        '橡塑产品',
        '钢材',
        '包装物',
        '杂品'
    ]; // 非油气

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     *
     */
    protected function getCondition($condition = []) {
        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];    //明细id
        }
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];    //询单id
        }
        if (!empty($condition['sku'])) {
            $where['sku'] = $condition['sku'];  //商品SKU
        }
        if (!empty($condition['brand'])) {
            $where['brand'] = $condition['brand'];  //品牌
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N';
        return $where;
    }

    /**
     * 获取数据条数
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getCondition($condition);
        $count = $this->where($where)->count('id');
        return $count > 0 ? $count : 0;
    }

    /**
     * 获取列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getList($condition = []) {
        $where = $this->getCondition($condition);

        try {
            $list = $this->where($where)->order('id')->select();
            if ($list) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
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
     * 获取详情信息
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
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
     * 添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        if (!empty($condition['inquiry_id'])) {
            $data['inquiry_id'] = $condition['inquiry_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        $data = $this->create($condition);

        if (empty($data['sku'])) {
            $data['sku'] = (new TemporaryGoodsModel)->getSku($condition);
        }
        unset($data['id']);
        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            if ($id) {
                $data['id'] = $id;
                $results['code'] = '1';
                $results['insert_id'] = $id;
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
     * 批量添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addDataBatch($condition = []) {
        if (empty($condition['inquiry_id'])) {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        if (empty($condition['inquiry_rows'])) {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        $inquirydata = [];
        for ($i = 0; $i < $condition['inquiry_rows']; $i++) {
            $test['inquiry_id'] = $condition['inquiry_id'];
            $test['qty'] = null;
            $test['created_by'] = $condition['created_by'];
            $test['created_at'] = $this->getTime();
            $inquirydata[] = $test;
        }

        try {
            $id = $this->addAll($inquirydata);
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

    /**
     * 更新数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function updateData($condition = []) {
        if (isset($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        //如果从报价过来，品牌是inquiry_brand
        if (!empty($condition['inquiry_brand'])) {
            $condition['brand'] = $condition['inquiry_brand'];
        }


        $data = $this->create($condition);
        if (empty($data['sku'])) {
            $data['sku'] = (new TemporaryGoodsModel)->getSku($condition);
        }
        $data['status'] = !empty($condition['status']) ? $condition['status'] : 'VALID';
        $data['updated_at'] = $this->getTime();

        try {
            $id = $this->where($where)->save($data);
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

    /**
     * 删除数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function deleteData($condition = []) {
        if (isset($condition['id'])) {
            $where['id'] = array('in', explode(',', $condition['id']));
        } else {
            return false;
        }

        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if (isset($id)) {
                (new TemporaryGoodsModel)->deleteData($condition);
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

    /**
     * @desc 获取关联询单SKU查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-12-13
     */
    public function getJoinWhere($condition = []) {
        $where['a.deleted_flag'] = 'N';

        if (!empty($condition['inquiry_id'])) {
            $where['a.inquiry_id'] = $condition['inquiry_id'];
        }

        return $where;
    }

    /**
     * @desc 获取关联询单SKU查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2018-01-12
     */
    public function getJoinWhere_($condition = []) {
        $where['a.deleted_flag'] = 'N';

        if (!empty($condition['id'])) {
            $where['a.id'] = $condition['id'];    //明细id
        }
        if (!empty($condition['inquiry_id'])) {
            $where['a.inquiry_id'] = $condition['inquiry_id'];    //询单id
        }
        if (!empty($condition['sku'])) {
            $where['a.sku'] = $condition['sku'];  //商品SKU
        }
        if (!empty($condition['brand'])) {
            $where['a.brand'] = $condition['brand'];  //品牌
        }

        return $where;
    }

    /**
     * @desc 获取关联询单SKU记录总数
     *
     * @param array $condition
     * @return int
     * @author liujf
     * @time 2017-12-13
     */
    public function getJoinCount($condition = []) {
        $where = $this->getJoinWhere($condition);

        $count = $this->alias('a')
                ->join($this->joinTable, 'LEFT')
                ->where($where)
                ->count('a.id');
        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取关联询单SKU记录总数
     *
     * @param array $condition
     * @return int
     * @author liujf
     * @time 2018-04-09
     */
    public function getJoinCount_($condition = []) {
        $where = $this->getJoinWhere_($condition);

        $count = $this->alias('a')
                ->join($this->joinTable_, 'LEFT')
                ->where($where)
                ->count('a.id');
        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取关联询单SKU列表
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-12-07
     */
    public function getJoinList($condition = []) {
        $where = $this->getJoinWhere($condition);

        return $this->alias('a')
                        ->field($this->joinField)
                        ->join($this->joinTable, 'LEFT')
                        ->where($where)
                        ->order('a.id DESC')
                        ->select();
    }

    /**
     * @desc 获取关联询单SKU列表
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2018-01-12
     */
    public function getJoinList_($condition = []) {
        $where = $this->getJoinWhere_($condition);
        try {
            $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
            $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
            $list = $this->alias('a')
                    ->field($this->joinField_)
                    ->join($this->joinTable_, 'LEFT')
                    ->where($where)
                    ->page($currentPage, $pageSize)
                    ->order('a.id ASC')
                    ->select();
            if ($list) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['data'] = $list;
                $results['count'] = $this->getJoinCount_($condition);
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
     * @desc 根据询单ID删除SKU记录
     *
     * @param int $inquiryId
     * @return mixed
     * @author liujf
     * @time 2018-04-09
     */
    public function delByInquiryId($inquiryId) {
        $flag = $this->where(['inquiry_id' => $inquiryId])->setField('deleted_flag', 'Y');

        if ($flag) {
            (new TemporaryGoodsModel)->deleteData(['inquiry_id' => $inquiryId]);
        }
        return $flag;
    }

}
