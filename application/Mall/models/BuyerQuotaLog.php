<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/3/6
 * Time: 10:42
 */
class BuyerQuotaLogModel extends PublicModel
{
    protected $tableName = 'buyer_quota_log';
    protected $dbName = 'buyer_credit'; //数据库名称

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }

    /**
     * 获取明细日志列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {

        $where = $this->_getCondition($condition);
        $condition['current_no'] = $condition['currentPage'];

        list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,buyer_no,type_apply,credit_cur_bn,credit_apply_date,granted,validity,data_unit,credit_at,credit_invalid_date';
        return $this->field($field)
            ->where($where)
            ->limit($start_no, $pagesize)
            ->order('id,credit_at desc')
            ->select();
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
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getCondition($condition = []) {
        $where = [];
        if (isset($condition['buyer_no']) && $condition['buyer_no']) {
            $where['buyer_no'] = $condition['buyer_no'];                  //客户编号
        }
        if (isset($condition['id']) && $condition['id']) {
            $where['id'] = intval($condition['id']);
        }
        return $where;
    }

}