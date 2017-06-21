<?php
/**
 * name: InquiryItem
 * desc: 询价单明细表
 * User: zhangyuliang
 * Date: 2017/6/17
 * Time: 10:54
 */
class InquiryItemodel extends PublicModel {

    protected $dbName = 'erui_db_ddl_rfq'; //数据库名称
    protected $tableName = 'inquiry_item'; //数据表表名

    const STATUS_INVALID = 'INVALID'; //NORMAL-有效；
    const STATUS_DELETE = 'DELETED'; //DISABLED-删除；

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     *
     */
    protected function getcondition($condition = []) {
        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        }
        if (!empty($condition['inquiry_no'])) {
            $where['inquiry_no'] = $condition['inquiry_no'];
        }
        if (!empty($condition['sku'])) {
            $where['sku'] = $condition['sku'];
        }
        if (!empty($condition['model'])) {
            $where['model'] = $condition['model'];
        }
        if (!empty($condition['spec'])) {
            $where['spec'] = $condition['spec'];
        }
        if (!empty($condition['brand'])) {
            $where['brand'] = $condition['brand'];
        }
        return $where;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getlist($condition = []) {
        $where = $this->getcondition($condition);

        if (isset($condition['page']) && isset($condition['countPerPage'])) {
            $count = $this->getcount($condition);
            return $this->where($where)
                ->limit($condition['page'] . ',' . $condition['countPerPage'])
                ->select();
        } else {
            return $this->where($where)->select();
        }
    }

    /**
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function add_data($createcondition = []) {

        //$data = $this->create($createcondition);
        $createcondition['status'] = 'INVALID';

        return $this->add($createcondition);

    }

    /**
     * 更新数据
     * @param  mix $data 更新数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function update_data($createcondition =  []) {

        $where['inquiry_no'] = $createcondition['inquiry_no'];
        $where['id'] = $createcondition['id'];
        switch ($createcondition['status']) {

            case self::STATUS_DELETED:
                $data['status'] = $createcondition['status'];
                break;
            case self::STATUS_DISABLED:
                $data['status'] = $createcondition['status'];
                break;
            case self::STATUS_NORMAL:
                $data['status'] = $createcondition['status'];
                break;
            default : $data['status'] = self::STATUS_NORMAL;
                break;
        }
        return $this->where($where)->save($data);

    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author zhangyuliang
     */
    public function delete_data($inquiry_no = '') {

        $where['inquiry_no'] = $inquiry_no;
        return $this->where($where)->save(['status' => 'DELETED']);

    }
}