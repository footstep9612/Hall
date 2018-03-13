<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/3/2
 * Time: 10:19
 */
class BuyerQuotaLogModel extends PublicModel
{
    protected $tableName = 'buyer_quota_log';
    protected $dbName = 'buyer_credit'; //数据库名称

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }

    //空-未申请；APPROVING-待易瑞审核；REJECTED-易瑞驳回；APPROVED-易瑞审核通过；
    const STATUS_APPROVING = 'APPROVING'; //待审核
    const STATUS_REJECTED = 'REJECTED'; //驳回
    const STATUS_APPROVED = 'APPROVED'; //审核通过
    const STATUS_ERUI = 'ERUI'; //ERUI


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
            ->order('id desc')
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


    /**
     * 新建信息
     */
    public function create_data($data)
    {

        if (isset($data['buyer_no']) && !empty($data['buyer_no'])) {
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if (isset($data['type_apply']) && !empty($data['type_apply'])) {
            $dataInfo['type_apply'] = strtoupper($data['type_apply']);
        }
        if (isset($data['credit_cur_bn']) && !empty($data['credit_cur_bn'])) {
            $dataInfo['credit_cur_bn'] = trim($data['credit_cur_bn']);
        }
        if (isset($data['credit_apply_date']) && !empty($data['credit_apply_date'])) {
            $dataInfo['credit_apply_date'] = trim($data['credit_apply_date']);
        }
        if (isset($data['granted']) && !empty($data['granted'])) {
            $dataInfo['granted'] = trim($data['granted']);
        }
        if (isset($data['validity']) && !empty($data['validity'])) {
            $dataInfo['validity'] = trim($data['validity']);
        }
        if (isset($data['data_unit']) && !empty($data['data_unit'])) {
            $dataInfo['data_unit'] = trim($data['data_unit']);
        }
        if (isset($data['credit_at']) && !empty($data['credit_at'])) {
            $dataInfo['credit_at'] = trim($data['credit_at']);
        }
        if (isset($data['credit_invalid_date']) && !empty($data['credit_invalid_date'])) {
            $dataInfo['credit_invalid_date'] = trim($data['credit_invalid_date']);
        }
        $result = $this->add($this->create($dataInfo));
        if ($result) {
            return true;
        }
        return false;
    }


    /**
     * 更新信息
     */
    public function update_data($data)
    {

        if (isset($data['buyer_no']) && !empty($data['buyer_no'])) {
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if (isset($data['type_apply']) && !empty($data['type_apply'])) {
            $dataInfo['type_apply'] = strtoupper($data['type_apply']);
        }
        if (isset($data['credit_cur_bn']) && !empty($data['credit_cur_bn'])) {
            $dataInfo['credit_cur_bn'] = trim($data['credit_cur_bn']);
        }
        if (isset($data['credit_apply_date']) && !empty($data['credit_apply_date'])) {
            $dataInfo['credit_apply_date'] = trim($data['credit_apply_date']);
        }
        if (isset($data['granted']) && !empty($data['granted'])) {
            $dataInfo['granted'] = trim($data['granted']);
        }
        if (isset($data['validity']) && !empty($data['validity'])) {
            $dataInfo['validity'] = trim($data['validity']);
        }
        if (isset($data['data_unit']) && !empty($data['data_unit'])) {
            $dataInfo['data_unit'] = trim($data['data_unit']);
        }
        if (isset($data['credit_at']) && !empty($data['credit_at'])) {
            $dataInfo['credit_at'] = trim($data['credit_at']);
        }
        if (isset($data['credit_invalid_date']) && !empty($data['credit_invalid_date'])) {
            $dataInfo['credit_invalid_date'] = trim($data['credit_invalid_date']);
        }
        $res = $this->where(['buyer_no' => $dataInfo['buyer_no']])->save($this->create($dataInfo));
        if ($res !== false) {
            return true;
        }
        return false;
    }
}