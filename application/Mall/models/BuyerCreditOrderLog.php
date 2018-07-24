<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/7/3
 * Time: 16:19
 */
class BuyerCreditOrderLogModel extends PublicModel
{
    protected $tableName = 'buyer_credit_order_log';
    protected $dbName = 'buyer_credit'; //数据库名称

    public function __construct($str = ''){
        parent::__construct($str = '');
    }

    /**
     * 获取详情-最新一条数据
     */
    public function getInfo($condition = []){
        if(empty($condition['buyer_no']) && empty($condition['crm_code']) && empty($condition['contract_no'])){
            return false;
        }
        if(empty($condition['contract_no'])) return false;
        $where = $this->_getCondition($condition);
        return $this->where($where)->order('id desc')->find();
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {
        $where = $this->_getCondition($condition);
        //$condition['current_no'] = $condition['currentPage'];

        //list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,buyer_no,credit_type,credit_cur_bn,use_credit_granted,credit_available,content,order_id,contract_no,type,credit_at,crm_code';
        return $this->field($field)
            ->where($where)
            //->limit($start_no, $pagesize)
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
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getCondition($condition = []) {
        $where = [];
        if (isset($condition['buyer_no']) && !empty($condition['buyer_no'])) {
            $where['buyer_no'] = $condition['buyer_no'];                  //客户编号
        }
        if (isset($condition['crm_code']) && !empty($condition['crm_code'])) {
            $where['crm_code'] = $condition['crm_code'];                  //crm编号
        }
        if (isset($condition['order_id']) && !empty($condition['order_id'])) {
            $where['order_id'] = $condition['order_id'];                  //订单id
        }
        if (isset($condition['contract_no']) && !empty($condition['contract_no'])) {
            $where['contract_no'] = $condition['contract_no'];                  //订单合同号
        }
        if (isset($condition['id']) && !empty($condition['id'])) {
            $where['id'] = $condition['id'];                  //id
        }
        if (isset($condition['type']) && !empty($condition['type'])) {
            $where['type'] = strtoupper($condition['type']);                  //收支类型
        }
        if (isset($condition['deleted_flag']) && !empty($condition['deleted_flag'])) {
            $where['deleted_flag'] = strtoupper($condition['deleted_flag']);                  //是否删除状态
        }else {
            $where['deleted_flag'] = 'N';
        }
        return $where;
    }
}