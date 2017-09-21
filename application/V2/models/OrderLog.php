<?php

/**
 * name: OrderLog.php
 * desc: 订单流程模型.
 * User: 张玉良
 * Date: 2017/9/12
 * Time: 17:14
 */
class OrderLogModel extends PublicModel {

    protected $dbName = 'erui2_order'; //数据库名称
    protected $tableName = 'order_log'; //数据表表名

    /**
     * 组合数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */

    protected function createData($condition = []) {
        if (!empty($condition['order_id'])) {
            $data['order_id'] = $condition['order_id'];    //订单ID
        }
        if (!empty($condition['log_group'])) {
            $data['log_group'] = $condition['log_group'];    //工作分组
        }
        if (!empty($condition['log_id'])) {
            $data['log_id'] = $condition['log_id'];  //上级工作流ID
        }
        if (!empty($condition['content'])) {
            $data['content'] = $condition['content'];    //内容
        } elseif (isset($data['content']) && !$data['content']) {
            $data['content'] = null;
        }
        if (!empty($condition['log_at'])) {
            $data['log_at'] = $condition['log_at'];    //时间
        }
        if (!empty($condition['out_no'])) {
            $data['out_no'] = $condition['out_no'];  //出库编号
        }
        if (!empty($condition['waybill_no'])) {
            $data['waybill_no'] = $condition['waybill_no'];    //运单号
        } elseif (isset($data['waybill_no']) && !$data['waybill_no']) {
            $data['waybill_no'] = null;
        }
        if (!empty($condition['amount'])) {
            $data['amount'] = $condition['amount'];    //金额
        }
        if (!empty($condition['type'])) {
            $data['type'] = $condition['type'];  //类型
        }
        if (!empty($condition['order_address_id'])) {
            $data['order_address_id'] = $condition['order_address_id'];  //订单地址id
        }
        if (!empty($condition['created_by'])) {
            $data['created_by'] = $condition['created_by'];  //创建人
        }
        if (!empty($condition['deleted_flag'])) {
            $data['deleted_flag'] = $condition['deleted_flag'];  //是否删除
        }
        return $data;
    }

    /**
     * 获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    protected function getCondition($condition = []) {

        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];    //ID
        }
        if (!empty($condition['order_id'])) {
            $where['order_id'] = $condition['order_id'];    //订单ID
        }
        if (!empty($condition['log_group'])) {
            $where['log_group'] = $condition['log_group'];    //工作分组
        }
        if (!empty($condition['log_id'])) {
            $where['log_id'] = $condition['log_id'];  //上级工作流ID
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N'; //删除状态

        return $where;
    }

    /**
     * 获取关联查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    protected function getJionCondition($condition = []) {

        $where = [];
        if (!empty($condition['log_group'])) {
            $where['a.log_group'] = $condition['log_group'];    //工作分组
        }
        if (!empty($condition['execute_no'])) {
            $where['b.execute_no'] = $condition['execute_no'];  //执行单号
        }
        if (!empty($condition['out_no'])) {
            $where['a.out_no'] = $condition['out_no'];    //出库单号
        }
        if (!empty($condition['waybill_no'])) {
            $where['a.waybill_no'] = $condition['waybill_no'];    //运单号
        }
        if (!empty($condition['show_status'])) {
            $where['b.show_status'] = $condition['show_status'];    //订单状态
        }
        $where['a.deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N'; //删除状态

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
        if (empty($condition['order_id'])) {
            $results['code'] = '-103';
            $results['message'] = '没有询单id!';
            return $results;
        }

        $where = $this->getCondition($condition);

        //$page = !empty($condition['currentPage'])?$condition['currentPage']:1;
        //$pagesize = !empty($condition['pageSize'])?$condition['pageSize']:10;

        try {
            $count = $this->getCount($condition);
            $list = $this->where($where)
                    //->page($page, $pagesize)
                    ->order('created_at asc')
                    ->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = $count;
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 获取列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getBuyerLogList($condition = []) {
        if (!empty($condition['buyer_id'])) {
            $where['b.buyer_id'] = $condition['buyer_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有客户id!';
            return $results;
        }
        if (!empty($condition['order_id'])) {
            $where['a.order_id'] = $condition['order_id'];
        }
        if (!empty($condition['log_group'])) {
            $where['a.log_group'] = $condition['log_group'];
        }

        $field = 'a.id,a.order_id,a.log_group,a.content,a.out_no,a.waybill_no,a.log_at,a.amount,a.type,a.log_id,b.order_no,b.po_no,b.execute_no,b.buyer_id';

        $page = !empty($condition['currentPage']) ? $condition['currentPage'] : 1;
        $pagesize = !empty($condition['pageSize']) ? $condition['pageSize'] : 10;

        try {
            $count = $this->alias('a')
                    ->join('erui2_order.order b ON a.order_id = b.id', 'LEFT')
                    ->where($where)
                    ->count('a.id');

            $list = $this->alias('a')
                    ->join('erui2_order.order b ON a.order_id = b.id', 'LEFT')
                    ->field($field)
                    ->where($where)
                    ->page($page, $pagesize)
                    ->order('a.created_at asc')
                    ->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = $count;
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 获取物流列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getLogiList($condition = []) {
        if (empty($condition['log_group'])) {
            $results['code'] = '-103';
            $results['message'] = '没有日志分组!';
            return $results;
        }

        $where = $this->getJionCondition($condition);

        $page = !empty($condition['currentPage']) ? $condition['currentPage'] : 1;
        $pagesize = !empty($condition['pageSize']) ? $condition['pageSize'] : 10;

        $field = 'a.id,a.order_id,a.log_group,a.out_no,a.waybill_no,a.log_at,b.execute_no,b.buyer_id';

        try {
            $count = $this->getCount($condition);
            $list = $this->alias('a')
                    ->join('erui2_order.order b ON a.order_id = b.id', 'LEFT')
                    ->field($field)
                    ->where($where)
                    ->page($page, $pagesize)
                    ->order('a.order_id desc,a.created_at asc')
                    ->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = $count;
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
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
        $where = $this->getCondition($condition);

        try {
            $info = $this->where($where)->find();

            if ($info) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $info;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
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
        if (empty($condition['order_id'])) {
            $results['code'] = '-103';
            $results['message'] = '没有订单ID!';
            return $results;
        }
        if (empty($condition['log_group'])) {
            $results['code'] = '-103';
            $results['message'] = '没有流程分组!';
            return $results;
        }

        $data = $this->createData($condition);
        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $id;
            } else {
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
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
     * @param  Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function updateData($condition = []) {
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有流程ID!';
            return $results;
        }
        $data = $this->createData($condition);

        try {
            $id = $this->where($where)->save($data);
            if ($id === false) {
                $results['code'] = '-101';
                $results['message'] = '修改失败!';
            } else {
                $results['code'] = '1';
                $results['message'] = '成功！';
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
     * @param  Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function deleteData($condition = []) {
        if (!empty($condition['id'])) {
            $where['id'] = array('in', explode(',', $condition['id']));
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有流程ID!';
            return $results;
        }

        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = '成功！';
            } else {
                $results['code'] = '-101';
                $results['message'] = '删除失败!';
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
