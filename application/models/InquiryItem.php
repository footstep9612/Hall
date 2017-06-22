<?php

/**
 * name: InquiryItem
 * desc: 询价单明细表
 * User: zhangyuliang
 * Date: 2017/6/17
 * Time: 10:54
 */
class InquiryItemModel extends PublicModel {

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
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getcount($condition = []) {
        $where = $this->getcondition($condition);
        return $this->where($where)->count('id');
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getlist($condition = []) {
        $where = $this->getcondition($condition);
        $page = $condition['page'] ? $condition['page'] : 1;
        $pagesize = $condition['countPerPage'] ? $condition['countPerPage'] : 10;

        try {
            if (isset($page) && isset($pagesize)) {
                $count = $this->getcount($condition);
                return $this->where($where)
                                ->page($page, $pagesize)
                                ->select();
            } else {
                return $this->where($where)->select();
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function add_data($createcondition = []) {


        $data = $this->create($createcondition);
        $data['status'] = 'INVALID';

        try {
            return $this->add($data);
        } catch (Exception $e) {

            echo $e->getMessage();
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $data 更新数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function update_data($createcondition = []) {
        $where['inquiry_no'] = $createcondition['inquiry_no'];
        $where['id'] = $createcondition['id'];
        switch ($createcondition['status']) {
            case self::STATUS_DELETED:
                $data['status'] = $createcondition['status'];
                break;
            case self::STATUS_INVALID:
                $data['status'] = $createcondition['status'];
                break;
            default : $data['status'] = self::STATUS_INVALID;
                break;
        }

        try {
            return $this->where($where)->save($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author zhangyuliang
     */
    public function delete_data($createcondition = []) {
        $where['id'] = $createcondition['id'];

        try {
            return $this->where($where)->save(['status' => 'DELETED']);
        } catch (Exception $e) {
            return false;
        }
    }

}
