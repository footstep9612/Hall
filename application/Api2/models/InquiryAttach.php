<?php
/**
 * name: InquiryAttach
 * desc: 询价单附件表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:14
 */
class InquiryAttachModel extends PublicModel {

    protected $dbName = 'erui2_rfq'; //数据库名称
    protected $tableName = 'inquiry_attach'; //数据表表名

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
            $where['id'] = $condition['id'];
        }
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        }
        if (!empty($condition['attach_group'])) {
            $where['attach_group'] = $condition['attach_group'];
        }
        if (!empty($condition['attach_type'])) {
            $where['attach_type'] = $condition['attach_type'];
        }
        if (!empty($condition['attach_name'])) {
            $where['attach_name'] = $condition['attach_name'];
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getcondition($condition);
        return $this->where($where)->count('id');
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
            $list = $this->where($where)->order('created_at desc')->select();
            if($list){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
                $results['data'] = $list;
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
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
        $data = $this->create($condition);
        if(isset($condition['inquiry_id'])){
            $data['inquiry_id'] = $condition['inquiry_id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        if(isset($condition['attach_name'])){
            $data['attach_name'] = $condition['attach_name'];
        }
        if(isset($condition['attach_type'])){
            $data['attach_type'] = $condition['attach_type'];
        }
        if(isset($condition['attach_url'])){
            $data['attach_url'] = $condition['attach_url'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有文件URL!';
            return $results;
        }
        $data['created_at'] = $this->getTime();

        $data = $this->create($data);
        try {
            $id = $this->add($data);
            if($id){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
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
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }

        $data = $this->create($condition);
        try {
            $id = $this->where($where)->save($data);
            if($id){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '修改失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
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
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }

        try {
            $id = $this->where($where)->delete();
            if($id){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '删除失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 根据询单号删除全部数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function deleteAttachAll($condition = []) {
        if(!empty($condition['inquiry_id'])){
            $where['inquiry_id'] = $condition['inquiry_id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }

        try {
            $id = $this->where($where)->delete();
            if($id){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '删除失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
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