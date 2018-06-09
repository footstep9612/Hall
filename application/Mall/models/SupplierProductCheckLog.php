<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/6/5
 * Time: 10:13
 */
class SupplierProductCheckLogModel extends PublicModel{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_product_check_log';

    public function __construct($str = ''){
        parent::__construct($str = '');
    }


    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {
        $condition['current_no'] = $condition['currentPage'];

        list($start_no, $pagesize) = $this->_getPage($condition);

        return $this->field('c.*, e.name')
            ->alias('c')
            ->join('erui_sys.employee as e ON e.id = c.approved_by AND e.deleted_flag = \'N\'', 'left')
            ->limit($start_no, $pagesize)
            ->where(['c.spu'=>$condition['spu']])
            ->select();
    }

    /**
     *获取定制数量
     * @param array $condition
     * @author  klp
     */
    public function getCount($condition) {

        return $this->where(['spu'=>$condition['spu']])->count();
    }

    /**
     * @desc 添加记录
     * @param array $condition
     */
    public function addRecord($condition = []) {


        $data = $this->create($condition);

        return $this->add($data);
    }

    /**
     * @desc 修改信息
     * @param array $where , $condition
     * @return bool
     */
    public function updateInfo($where = [], $condition = []) {

        $data = $this->create($condition);

        $res = $this->where($where)->save($data);
        if ($res !== false) {
            return true;
        }
        return false;
    }

    /**
     * @desc 软删除
     * @param array $where , $condition
     * @return bool
     */
    public function deleteInfo($where = [], $condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        } else {
            return false;
        }
        $res = $this->where($where)->save(['deleted_flag'=>'Y']);
        if ($res !== false) {
            return true;
        }
        return false;
    }

    /**
     * @desc 删除记录
     * @param array $condition
     * @return bool
     */
    public function delRecord($condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        } else {
            return false;
        }

        return $this->where($where)->delete();
    }







}