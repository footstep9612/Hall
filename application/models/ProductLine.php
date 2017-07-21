<?php
/**
 * name: ProductLine
 * desc: 产品线表
 * User: zhangyuliang
 * Date: 2017/7/20
 * Time: 10:00
 */
class ProductLineModel extends PublicModel {

    protected $dbName = 'erui_config'; //数据库名称
    protected $tableName = 'product_line'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 返回最新产品线编码
     * @author zhangyuliang
     */
    public function getLineNo() {
        $lineno = $this->field('line_no')->order('line_no desc')->find();
        if($lineno){
            $number = intval($lineno)+1;
            $results = 'pl'.str_pad($number,6,'0',STR_PAD_LEFT);
        }else{
            $results = 'pl000001';
        }
        return $results;
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function  getCondition($condition = []) {
        $where = [];
        if (!empty($condition['name'])) {
            $where['name'] = $condition['name'];
        }
        if (!empty($condition['user_no'])) {
            $where['user_no'] = $condition['user_no'];
        }

        $where['status'] = !empty($condition['status'])?$condition['status']:"VALID";

        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getCondition($condition);
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

        $page = !empty($condition['currentPage'])?$condition['currentPage']:1;
        $pagesize = !empty($condition['pageSize'])?$condition['pageSize']:10;

        try {
            $count = $this->getcount($where);
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
     * @param  int $line_no 产品线编码
     * @return mix
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
        if(!empty($condition['line_no'])){
            $where['line_no'] = $condition['line_no'];
        }else{
            $results['code'] = '-101';
            $results['message'] = '没有产品线编码!';
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
     * @return mix
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        $data['line_no'] = $this->getLineNo();

        if(!empty($condition['name'])){
            $data['name'] = $condition['name'];
        }else{
            $results['code'] = '-101';
            $results['message'] = '缺少名称!';
            return $results;
        }
        if(!empty($condition['description'])){
            $data['description'] = $condition['description'];
        }

        $data['status'] = 'VALID';
        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            if(isset($id)){
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
     * @param  mix $createcondition 更新数据
     * @param  int $serial_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function updateData($condition = []) {
        if(!empty($condition['line_no'])){
            $where['line_no'] = $condition['line_no'];
        }else{
            $results['code'] = '-101';
            $results['message'] = '缺少产品线编码!';
            return $results;
        }
        if(!empty($condition['name'])){
            $data['name'] = $condition['name'];
        }
        if(!empty($condition['description'])){
            $data['description'] = $condition['description'];
        }
        if(!empty($condition['user_no'])){
            $data['user_no'] = $condition['user_no'];
        }

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
     * @param  int $serial_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function deleteData($condition = []) {
        if(!empty($condition['line_no'])){
            $where['line_no'] = array('in',explode(',',$condition['line_no']));
        }else{
            $results['code'] = '-101';
            $results['message'] = '缺少产品线编码!';
        }

        try {
            $id = $this->where($where)->save(['inquiry_status' => 'DELETED']);
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
    public function getTime(){
        return date('Y-m-d h:i:s',time());
    }
}