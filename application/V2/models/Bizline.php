<?php
/**
 * name: Bizline
 * desc: 产品线表
 * User: 张玉良
 * Date: 2017/8/1
 * Time: 10:02
 */
class BizlineModel extends PublicModel {

    protected $dbName = 'erui2_operation'; //数据库名称
    protected $tableName = 'bizline'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function  getCondition($condition = []) {
        $where = [];
        if (!empty($condition['name'])) {
            $where['name'] = $condition['name'];
        }
        //$where['status'] = !empty($condition['status'])?$condition['status']:"VALID";

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
        return $this->where($where)->count('id');
    }

    /**
     * 获取列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getList($condition = []) {
        $where = $this->getcondition($condition);

        $page = !empty($condition['currentPage'])?$condition['currentPage']:1;
        $pagesize = !empty($condition['pageSize'])?$condition['pageSize']:10;

        try {
            $count = $this->getcount($condition);
            $list = $this->where($where)->page($page, $pagesize)->order('created_at desc')->select();
            if(isset($list)){
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
            $results['message'] = '没有产品线id!';
            return $results;
        }

        try {
            $info = $this->where($where)->find();

            if(isset($info)){
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
        if(!empty($condition['name'])){
            $data['name'] = $condition['name'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '缺少名称!';
            return $results;
        }
        if(!empty($condition['userid'])){
            $data['created_by'] = $condition['userid'];
            $data['updated_by'] = $condition['userid'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '缺少添加人员id!';
            return $results;
        }
        if(!empty($condition['remarks'])){
            $data['remarks'] = $condition['remarks'];
        }
        $data['status'] = 'VALID';
        $data['created_at'] = $this->getTime();
        $data['updated_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            if(isset($id)){
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
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function updateData($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '缺少产品线id!';
            return $results;
        }
        if(!empty($condition['name'])){
            $data['name'] = $condition['name'];
        }
        if(!empty($condition['remarks'])){
            $data['remarks'] = $condition['remarks'];
        }
        $data['updated_by'] = $condition['userid'];
        $data['updated_at'] = $this->getTime();

        try {
            $id = $this->where($where)->save($data);
            if(isset($id)){
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
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function updateStatus($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-103';
            $results['message'] = '缺少产品线id!';
            return $results;
        }
        if(!empty($condition['status'])){
            $data['status'] = $condition['status'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '缺少状态!';
            return $results;
        }

        try {
            $id = $this->where($where)->save($data);
            if(isset($id)){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '删除失败!';
            }
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
        }
        return $results;
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
            $results['message'] = '缺少产品线id!';
        }

        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if(isset($id)){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '删除失败!';
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