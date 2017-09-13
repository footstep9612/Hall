<?php

/**
 * name: OrderAddress.php
 * desc: 订单地址模型.
 * User: 张玉良
 * Date: 2017/9/13
 * Time: 10:59
 */
class OrderAddressModel extends PublicModel {

    protected $dbName = 'erui2_order'; //数据库名称
    protected $tableName = 'order_address'; //数据表表名

    /**
     * 根据条件获取查询条件
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
        if (!empty($condition['workflow_id'])) {
            $where['workflow_id'] = $condition['workflow_id'];    //工作流ID
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N'; //删除状态

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

        try {
            $list = $this->where($where)
                    ->order('created_at desc')
                    ->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
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
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有附件id!';
            return $results;
        }

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

        $data = $this->create($condition);
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
        $where = $this->getCondition($condition);

        $data = $this->create($condition);

        try {
            $id = $this->where($where)->save($data);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = '成功！';
            } else {
                $results['code'] = '-101';
                $results['message'] = '修改失败!';
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
            $results['message'] = '没有附件ID!';
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

    /* 获取订单详情
     * @param int $order_id // 订单ID
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   订单
     */

    public function info($order_id) {

        return $this->where(['order_id' => $order_id, 'deleted_flag' => 'N'])
                        ->order('created_at desc')->find();
    }

}
