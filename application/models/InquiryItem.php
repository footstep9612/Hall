<?php

/**
 * name: InquiryItem
 * desc: 询价单明细表
 * User: zhangyuliang
 * Date: 2017/6/17
 * Time: 10:54
 */
class InquiryItemModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
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
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];
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
        $where['status'] = isset($condition['status'])?$condition['status']:self::STATUS_INVALID;
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

        $page = isset($condition['page']) ? $condition['page'] : 1;
        $pagesize = isset($condition['countPerPage']) ? $condition['countPerPage'] : 10;


        try {
            if (isset($page) && isset($pagesize)) {
                //$count = $this->getcount($condition);

                return $this->where($where)->select();
                                //->page($page, $pagesize)
                                //->select();
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
        if (isset($createcondition['serial_no'])) {
            $data['serial_no'] = $createcondition['serial_no'];
        } else {
            return false;
        }
        if (isset($createcondition['quantity'])) {
            $data['quantity'] = $createcondition['quantity'];
        } else {
            return false;
        }

        $data['status'] = self::STATUS_INVALID;
        $data['created_at'] = $this->getTime();

        try {
            return $this->add($data);
        } catch (Exception $e) {
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
        if (isset($createcondition['serial_no'])) {
            $where['serial_no'] = $createcondition['serial_no'];
        } else {
            return false;
        }
        if (isset($createcondition['id'])) {
            $where['id'] = $createcondition['id'];
        } else {
            return false;
        }

        $data = $this->create($createcondition);
        $data['status'] = isset($createcondition['status']) ? $createcondition['status'] : self::STATUS_INVALID;

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
        if (isset($createcondition['serial_no'])) {
            $where['serial_no'] = $createcondition['serial_no'];
        } else {
            return false;
        }
        if (isset($createcondition['id'])) {
            $where['id'] = $createcondition['id'];
        } else {
            return false;
        }

        try {
            return $this->where($where)->save(['status' => 'DELETED']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d h:i:s', time());
    }

}
