<?php

/**
 * name: FinalQuote.php
 * desc: 市场报价单控制器
 * User: 张玉良
 * Date: 2017/8/4
 * Time: 10:45
 */
class FinalQuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @desc 获取查询条件
     * @author zhangyulianag
     * @param array $condition
     * @return array
     */
    public function getWhere($condition) {
        $where = array();

        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        }

        if (!empty($condition['status'])) {
            $where['status'] = $condition['status'];
        }

        return $where;
    }

    /**
     * @desc 获取记录总数
     * @author zhangyuliang
     * @param array $condition
     * @return int $count
     */
    public function getCount($condition) {
        $where = $this->getWhere($condition);

        $count = $this->where($where)->count('id');

        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取市场报价单列表
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function getList($condition) {

        $where = $this->getWhere($condition);

        try {
            if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
                $page = !empty($condition['currentPage']) ? $condition['currentPage'] : 1;
                $pagesize = !empty($condition['pageSize']) ? $condition['pageSize'] : 10;

                $count = $this->getCount($condition);
                $list = $this->where($where)->page($page, $pagesize)->order('updated_at desc')->select();
            } else {
                $count = 0;
                $list = $this->where($where)->order('updated_at desc')->select();
            }

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
     * @desc 获取市场报价单详情
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function getInfo($condition) {
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
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
     * 添加市场报价单
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function addData($condition = []) {
        $data = $this->create($condition);

        if (!empty($condition['inquiry_id'])) {
            $data['inquiry_id'] = $condition['inquiry_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        if (!empty($condition['buyer_id'])) {
            $data['buyer_id'] = $condition['buyer_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        if (!empty($condition['quote_id'])) {
            $data['quote_id'] = $condition['quote_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['data'] = $data;
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
     * @desc 修改市场报价单
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function updateFinal($condition = []) {
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        $data = $this->create($condition);
        $data['updated_at'] = $this->getTime();

        try {
            $id = $this->where($where)->save($data);
            if ($id !== false) {
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
     * @desc 批量修改状态
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function updateFinalStatus($condition = []) {

        if (isset($condition['inquiry_id'])) {
            $where['inquiry_id'] = array('in', explode(',', $condition['inquiry_id']));
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        if (isset($condition['status'])) {
            $data['status'] = $condition['status'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        $data['updated_by'] = $condition['updated_by'];
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
     * @desc 删除报价单
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function deleteFinal($condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            return false;
        }

        return $this->where($where)->save(['deleted_flag' => 'Y']);
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d H:i:s', time());
    }

}
