<?php
/**
 * name: OrderAttach.php
 * desc: 订单附件模型.
 * User: 张玉良
 * Date: 2017/9/13
 * Time: 9:44
 */
class OrderAttachModel extends PublicModel {

    protected $dbName = 'erui2_order'; //数据库名称
    protected $tableName = 'order_attach'; //数据表表名

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    protected function getCondition($condition = []) {

        $where = [];
        if (!empty($condition['order_id'])) {
            $where['order_id'] = $condition['order_id'];    //订单ID
        }
        if (!empty($condition['attach_group'])) {
            $where['attach_group'] = $condition['attach_group'];    //附件分组
        }
        if (!empty($condition['log_id'])) {
            $where['log_id'] = $condition['log_id'];  //关联ID
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag'])?$condition['deleted_flag']:'N'; //删除状态

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
        if(empty($condition['order_id'])){
            $results['code'] = '-103';
            $results['message'] = '没有询单id!';
            return $results;
        }

        $where = $this->getCondition($condition);

        try {
            $count = $this->getCount($condition);
            $list = $this->where($where)->order('created_at desc')->select();

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
     * 获取详情信息
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有附件id!';
            return $results;
        }

        try {
            $info = $this->where($where)->find();

            if($info){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $info;
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
     * 添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        if(empty($condition['order_id'])) {
            $results['code'] = '-103';
            $results['message'] = '没有订单ID!';
            return $results;
        }

        $data = $this->create($condition);
        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $id;
            }else{
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
     * 添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addAllData($condition = []) {
        if(empty($condition['order_id'])) {
            $results['code'] = '-103';
            $results['message'] = '没有订单ID!';
            return $results;
        }
        if(empty($condition['attach_array'])){
            $results['code'] = '-103';
            $results['message'] = '没有附件信息!';
            return $results;
        }
        $data = [];

        foreach($condition['attach_array'] as $key=>$val){
            $data[$key]['order_id'] = $condition['order_id'];
            $data[$key]['attach_group'] = $condition['workflow_group'];
            $data[$key]['log_id'] = $condition['log_id'];
            $data[$key]['attach_url'] = $val['attach_url'];
            $data[$key]['attach_name'] = $val['attach_name'];
            $data[$key]['created_by'] = $condition['created_by'];
            $data[$key]['created_at'] = $this->getTime();
        }

        try {
            $id = $this->addAll($data);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $id;
            }else{
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
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有附件ID!';
            return $results;
        }
        $data = $this->create($condition);

        try {
            $id = $this->where($where)->save($data);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
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
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有附件ID!';
            return $results;
        }

        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
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
        return date('Y-m-d H:i:s',time());
    }
}