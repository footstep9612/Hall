<?php
/**
 * name: Inquiry
 * desc: 询价单表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:11
 */
class InquiryModel extends PublicModel {

    protected $dbName = 'erui2_rfq'; //数据库名称
    protected $tableName = 'inquiry'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    protected function getCondition($condition = []) {
        $where = [];
        if (!empty($condition['status'])) {
            $where['status'] = $condition['status'];    //项目状态
        }
        if (!empty($condition['country_bn'])) {
            $where['country_bn'] = $condition['country_bn'];    //国家
        }
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];  //流程编码
        }
        if (!empty($condition['buyer_name'])) {
            $where['buyer_name'] = $condition['buyer_name'];  //客户名称
        }
        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = $condition['agent_id'];//市场经办人
        }
        if (!empty($condition['pm_id'])) {
            $where['pm_id'] = $condition['pm_id'];  //项目经理
        }
        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = array(
                array('gt',date('Y-m-d H:i:s',$condition['start_time'])),
                array('lt',date('Y-m-d H:i:s',$condition['end_time']))
            );
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
        $where = $this->getCondition($condition);

        $page = !empty($condition['currentPage'])?$condition['currentPage']:1;
        $pagesize = !empty($condition['pageSize'])?$condition['pageSize']:10;

        try {
            $count = $this->getCount($where);
            $list = $this->where($where)->page($page, $pagesize)->order('updated_at desc')->select();
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
            $results['message'] = '没有id!';
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
     * @return mix
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        $data = $this->create($condition);

        if(!empty($condition['serial_no'])) {
            $data['serial_no'] = $condition['serial_no'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有流程编码!';
            return $results;
        }
        $data['status'] = 'DRAFT';
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
        $data = $this->create($condition);
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }
        $data['updated_at'] = $this->getTime();

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

    /*
     * 批量更新状态
     * @param  mix $condition
     * @param  int $serial_no 询单号
     * @return bool
     */
    public function updateStatus($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }
        if(!empty($condition['status'])){
            $data['status'] = $condition['status'];
        }
        $data['updated_at'] = $this->getTime();

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

    /*
     * 检查流程编码是否存在
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function checkSerialNo($condition = []){
        if(!empty($condition['serial_no'])){
            $where['serial_no'] = $condition['serial_no'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有流程编码!';
            return $results;
        }

        try {
            $id = $this->field('id')->where($where)->find();
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
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
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d h:i:s',time());
    }
}
