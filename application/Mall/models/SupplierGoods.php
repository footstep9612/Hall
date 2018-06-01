<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/5/30
 * Time: 16:30
 */
class SupplierGoodsModel extends PublicModel{

    protected $dbName = '';
    protected $tableName = '';

    public function __construct($str = ''){
        parent::__construct($str = '');
    }


    /**
     * 获取列表--代码申请管理
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {
        $where = $this->_getCondition($condition);
        $condition['current_no'] = $condition['currentPage'];

        list($start_no, $pagesize) = $this->_getPage($condition);
        /*$field = 'id,agent_id,name,buyer_no,sinosure_no,credit_apply_date,approved_date,nolc_deadline,lc_deadline,status,bank_remarks,remarks,account_settle';
        return $this->field($field)
            //->alias('c')
            ->where($where)
            ->limit($start_no, $pagesize)
            ->order('id desc')
            ->select();*/
    }

    /**
     *获取定制数量
     * @param array $condition
     * @author  klp
     */
    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
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

    /**
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getCondition($condition = []) {
        $where = [];

        if (!empty($condition['buyer_no_arr'])) {
            $where['buyer_no'] = ['in', $condition['buyer_no_arr']];
            if (isset($condition['buyer_no']) && !empty($condition['buyer_no'])) {
                $where['buyer_no'] = [$where['buyer_no'], ['eq', $condition['buyer_no']]];                  //客户编号
            }
        } else {
            $where['id'] = '-1';
        }
        if (isset($condition['name']) && !empty($condition['name'])) {
            $where['name'] = $condition['name'];                  //名称
        }


        /*if (isset($condition['tel']) && $condition['tel']) {
            $where['tel'] = ['REGEXP','([\+]{0,1}\d*[-| ])*'.$condition['tel'].'$'];
        }*/
        if (!empty($condition['credit_date_start']) && !empty($condition['credit_date_end'])) {   //时间
            $where['credit_apply_date'] = array(
                array('egt', date('Y-m-d 0:0:0',strtotime($condition['credit_date_start']))),
                array('elt', date('Y-m-d 23:59:59',strtotime($condition['credit_date_end'])))
            );
        }
        return $where;
    }

}