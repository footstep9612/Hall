<?php
/**
 * name: ProductLineCat
 * desc: 产品线和物料分类关联表
 * User: zhangyuliang
 * Date: 2017/7/20
 * Time: 14:16
 */
class ProductLineCatModel extends PublicModel {

    protected $dbName = 'erui_config'; //数据库名称
    protected $tableName = 'product_line_cat'; //数据表表名

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function  getCondition($condition = []) {
        $where = [];
        if (!empty($condition['line_no'])) {
            $where['line_no'] = $condition['line_no'];
        }
        if (!empty($condition['cat_no'])) {
            $where['cat_no'] = $condition['cat_no'];
        }

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
        if(empty($condition['line_no'])){
            $results['code'] = '-101';
            $results['message'] = '缺少产品线编码!';
        }

        $where = $this->getcondition($condition);

        //$page = !empty($condition['currentPage'])?$condition['currentPage']:1;
        //$pagesize = !empty($condition['pageSize'])?$condition['pageSize']:10;

        try {
            $count = $this->getcount($where);
            $list = $this->where($where)
                //->page($page, $pagesize)
                ->order('created_at desc')
                ->select();
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
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function addData($condition = []) {

        if(empty($condition['line_no'])){
            $results['code'] = '-101';
            $results['message'] = '缺少产品线编码!';
        }
        if(empty($condition['material_cat'])){
            $results['code'] = '-101';
            $results['message'] = '缺少分类编码!';
        }
        $matrialcat = explode(',',$condition['material_cat']);
        $linecat = [];
        foreach($matrialcat as $val){
            $test['line_no'] = $condition['line_no'];
            $test['cat_no'] = $val;
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
     * 删除数据
     * @param  int $serial_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function deleteData($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-101';
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
     * 删除全部数据
     * @param  int $serial_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function deleteDataAll($condition = []) {
        if(!empty($condition['line_no'])){
            $where['line_no'] = $condition['line_no'];
        }else{
            $results['code'] = '-101';
            $results['message'] = '缺少产品线编码!';
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
    public function getTime(){
        return date('Y-m-d h:i:s',time());
    }
}