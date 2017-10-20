<?php
/**
 * name: CheckLog.php
 * desc: 审核日志表
 * User: 张玉良
 * Date: 2017/8/21
 * Time: 9:31
 */
class CheckLogModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry_check_log';

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

        if(!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        }
        if(!empty($condition['quote_id'])) {
            $where['quote_id'] = $condition['quote_id'];
        }
        if(!empty($condition['category'])) {
            $where['op_result'] = $condition['op_result'];
        }
        if(!empty($condition['action'])) {
            $where['action'] = $condition['action'];
        }
        if(!empty($condition['op_result'])) {
            $where['op_result'] = $condition['op_result'];
        }


        if(!empty($condition['status'])) {
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
     * @desc 获取审核日志列表
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function getList($condition) {

        $where = $this->getWhere($condition);

        try {
            $count = 0;
            $list = $this->where($where)->order('id asc')->select();

            if($list){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = $count;
                $results['data'] = $list;
            }else{
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
     * 添加审核日志
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function addData($condition = []) {
        $data = $this->create($condition);

        if(empty($condition['inquiry_id'])) {
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        if(empty($condition['action'])){
            $results['code'] = '-103';
            $results['message'] = '没有操作类型!';
            return $results;
        }

        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            $data['id'] = $id;
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $data;
            }else{
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d h:i:s',time());
    }
}