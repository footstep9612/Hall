<?php
/**
 * name: BizlineCat
 * desc: 产品线和物料分类关联表
 * User: 张玉良
 * Date: 2017/8/1
 * Time: 13:16
 */
class BizlinecatModel extends PublicModel {

    protected $dbName = 'erui2_operation'; //数据库名称
    protected $tableName = 'bizline_cat'; //数据表表名

    public function __construct()
    {
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
        if (!empty($condition['bizline_id'])) {
            $where['bizline_id'] = $condition['bizline_id'];
        }
        if (!empty($condition['material_cat_no'])) {
            $where['material_cat_no'] = $condition['material_cat_no'];
        }
        $where['status'] = !empty($condition['status'])?$condition['status']:"VALID";

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
    public function getlist($condition = []) {
        if(empty($condition['bizline_id'])){
            $results['code'] = '-103';
            $results['message'] = '缺少产品线id!';
        }

        $where = $this->getcondition($condition);

        try {
            $list = $this->where($where)->order('created_at desc')->select();
            if(isset($list)){
                $results['code'] = '1';
                $results['message'] = '成功！';
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
     * 添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addData($condition = []) {

        if(empty($condition['bizline_id'])){
            $results['code'] = '-103';
            $results['message'] = '缺少产品线id!';
        }
        if(empty($condition['material_cat'])){
            $results['code'] = '-103';
            $results['message'] = '缺少分类编码!';
        }
        $matrialcat = explode(',',$condition['material_cat']);
        $linecat = [];
        foreach($matrialcat as $val){
            $test['bizline_id'] = $condition['bizline_id'];
            $test['material_cat_no'] = $val;
            $test['created_by'] = $condition['userid'];
            $test['created_at'] = $this->getTime();
            $linecat[] = $test;
        }

        try {
            $id = $this->addAll($linecat);
            if(isset($id)){
                $results['code'] = '1';
                $results['message'] = '成功！';
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
            $results['message'] = '缺少id!';
            return $results;
        }
        if(!empty($condition['bizline_id'])){
            $data['bizline_id'] = $condition['bizline_id'];
        }
        if(!empty($condition['material_cat_no'])){
            $data['material_cat_no'] = $condition['material_cat_no'];
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
    public function deleteData($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-103';
            $results['message'] = '缺少id!';
        }

        try {
            $id = $this->where($where)->delete();
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
     * 根据产品线id删除数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function deleteBizlineCat($condition = []) {
        if(!empty($condition['bizline_id'])){
            $where['bizline_id'] = $condition['bizline_id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '缺少产品线id!';
        }

        try {
            $id = $this->where($where)->delete();
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