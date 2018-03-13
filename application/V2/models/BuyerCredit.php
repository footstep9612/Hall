<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/2/27
 * Time: 9:47
 */
class BuyerCreditModel extends PublicModel
{
    protected $tableName = 'buyer_credit';
    protected $dbName = 'buyer_credit'; //数据库名称
    protected $g_table = 'buyer_credit.buyer_credit';

    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取详情
     */
    public function getInfo($buyer_no){
        return $this->where(['buyer_no' => $buyer_no])->find();
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getCreditlist($condition = [],$limit, $order = " id desc") {

        $sql = 'SELECT `buyer_credit`.`buyer_credit`.`id`,
                 `buyer_credit`.`buyer_credit`.`name`,
                 `buyer_credit`.`buyer_credit`.`buyer_no`,
                 `buyer_credit`.`buyer_credit`.`bank_swift`,
                 `buyer_credit`.`buyer_credit`.`sinosure_no`,
                 `buyer_credit`.`buyer_credit`.`credit_apply_date`,
                 `buyer_credit`.`buyer_credit`.`credit_valid_date`,
                 `buyer_credit`.`buyer_credit`.`source`,
                 `buyer_credit`.`buyer_credit`.`status`,
                 `buyer_credit`.`buyer_credit`.`agent_id`,';
        $sql .= '`buyer_credit`.`buyer_reg_info`.`country_code`,';
        $sql .= '`erui_sys`.`employee`.`name` as `agent_name`,';
        $sql .= '`erui_dict`.`country`.`name` as `country`,';
        $sql .= '`erui_buyer`.`buyer`.`buyer_code` as `crm_code`';
        $str = ' FROM ' . $this->g_table;
        $sql .= $str;

        $sql .= " LEFT JOIN `buyer_credit`.`buyer_reg_info` ON `buyer_credit`.`buyer_credit`.`buyer_no` = `buyer_credit`.`buyer_reg_info`.`buyer_no` ";
        $sql .= " LEFT JOIN `erui_buyer`.`buyer` ON `erui_buyer`.`buyer`.`buyer_no` = `buyer_credit`.`buyer_credit`.`buyer_no` AND  `erui_buyer`.`buyer`.`status` = 'APPROVED'";
        $sql .= " LEFT JOIN `erui_sys`.`employee` ON `buyer_credit`.`buyer_credit`.`agent_id` = `erui_sys`.`employee`.`id` AND `erui_sys`.`employee`.`deleted_flag`='N'";
        $sql .= " LEFT JOIN `erui_dict`.`country` ON `erui_dict`.`country`.`code` = `buyer_credit`.`buyer_reg_info`.`country_code` AND `erui_dict`.`country`.`lang` = `erui_buyer`.`buyer`.`lang`";

        $sql_count = 'SELECT count(`buyer_credit`.`buyer_credit`.`id`) as num ';
        $sql_count .= $str;
        $where = " WHERE 1 = 1";
        if (isset($condition['buyer_no']) && !empty($condition['buyer_no'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`buyer_no` ="' . $condition['buyer_no'] . '"';
        }
        if (isset($condition['country_code']) && !empty($condition['country_code'])) {
            $where .= ' And `buyer_credit`.`buyer_reg_info`.`country_code` ="' . $condition['country_code'] . '"';
        }
        if (isset($condition['name']) && !empty($condition['name'])) {
            $where .= " And `buyer_credit`.`buyer_credit`.`name` like '%" . $condition['name'] . "%'";
        }
        if (isset($condition['sinosure_no']) && !empty($condition['sinosure_no'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`sinosure_no` = "' . $condition['sinosure_no'] .'"';
        }
        if (isset($condition['agent_id']) && !empty($condition['agent_id'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`agent_id` = "'. $condition['agent_id'] .'"';
        }
        if (isset($condition['agent_name']) && !empty($condition['agent_name'])) {
            $where .= " And `erui_sys`.`employee`.`agent_name` like '%" . $condition['agent_name'] . "%'";
        }
        if (isset($condition['buyer_code']) && !empty($condition['buyer_code'])) {
            $where .= ' And `erui_buyer`.`buyer`.`buyer_code` = "' . $condition['buyer_code'] .'"';
        }
        if (isset($condition['bank_swift']) && !empty($condition['bank_swift'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`bank_swift`  = " ' . $condition['bank_swift'] . '"';
        }
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`status` ="' . strtoupper($condition['status']) . '"';
        }else{
            $where .= ' And  `buyer_credit`.`buyer_credit`.`status` <> "DRAFT"';
        }
        if ($where) {
            $sql .= $where;
            $sql_count .= $where;
        }
        $sql .= ' Order By ' . $order;
        if (!empty($limit['num'])) {
            $sql .= ' LIMIT ' . $limit['page'] . ',' . $limit['num'];
        }
        $count = $this->query($sql_count);
        $res['count'] = $count[0]['num'];
        $res['data'] = $this->query($sql);
        return $res;
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
        $field = 'id,agent_id,name,buyer_no,sinosure_no,credit_apply_date,status';
        return $this->field($field)
            //->alias('c')
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
        /*if (isset($condition['status']) && $condition['status']) {
            switch ($condition['status']) {
                case 'Technology':
                    $where['cat_name'] = 'Technology consulting and comprehensive solutions';
                    break;
                case 'Talent':
                    $where['cat_name'] = 'Talent training';
                    break;
                case 'Human':
                    $where['cat_name'] = 'Human resources';
                    break;
                default :
                    break;
            }
        }*/
        if (isset($condition['buyer_no']) && $condition['buyer_no']) {
            $where['buyer_no'] = $condition['buyer_no'];                  //客户编号
        }
        if (isset($condition['name']) && $condition['name']) {
            $where['name'] = $condition['name'];                  //名称
        }
        if (isset($condition['source']) && $condition['source']) {
            $where['source'] = $condition['source'];                  //来源
        }
        if (isset($condition['agent_id']) && $condition['agent_id']) {
            $where['agent_id'] = $condition['agent_id'];                  //经办人
        }
        if (isset($condition['status']) && $condition['status']) {
            $where['status'] = strtoupper($condition['status']);
        } else{
            $where['status'] = array('neq', 'DRAFT');
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

    /**
     * 新建信息
     */
    public function create_data($data) {

        if(isset($data['name']) && !empty($data['name'])){
            $dataInfo['name'] = trim($data['name']);
        }
        if(isset($data['buyer_no']) && !empty($data['buyer_no'])){
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if(isset($data['bank_swift']) && !empty($data['bank_swift'])){
            $dataInfo['bank_swift'] = trim($data['bank_swift']);
        }
        if(isset($data['sinosure_no']) && !empty($data['sinosure_no'])){
            $dataInfo['sinosure_no'] = trim($data['sinosure_no']);
        }
        if(isset($data['nolc_granted']) && !empty($data['nolc_granted'])){
            $dataInfo['nolc_granted'] = trim($data['nolc_granted']);
        }
        if(isset($data['nolc_deadline']) && !empty($data['nolc_deadline'])){
            $dataInfo['nolc_deadline'] = trim($data['nolc_deadline']);
        }
        if(isset($data['lc_granted']) && !empty($data['lc_granted'])){
            $dataInfo['lc_granted'] = trim($data['lc_granted']);
        }
        if(isset($data['lc_deadline']) && !empty($data['lc_deadline'])){
            $dataInfo['lc_deadline'] = trim($data['lc_deadline']);
        }
        if(isset($data['deadline_cur_unit']) && !empty($data['deadline_cur_unit'])){
            $dataInfo['deadline_cur_unit'] = trim($data['deadline_cur_unit']);
        }
        if(isset($data['credit_invalid_date']) && !empty($data['credit_invalid_date'])){
            $dataInfo['credit_invalid_date'] = trim($data['credit_invalid_date']);
        }
        if(isset($data['source']) && !empty($data['source'])){
            $dataInfo['source'] = trim($data['source']);
        } else{
            $data['source'] = 'BOSS';
        }
        $buyer_model = new BuyerModel();
        $agent_model = new BuyerAgentModel();
        $buyer_id = $buyer_model->field('id')->where(['buyer_no'=>$data['buyer_no']])->find();
        $agent_id = $agent_model->field('agent_id')->where(['buyer_id'=>$buyer_id['id']])->find();
        if($agent_id){
            $dataInfo['agent_id'] = $agent_id['agent_id'];
            $dataInfo['status'] = 'APPROVING';
        }else{
            $dataInfo['status'] = 'DRAFT';
        }
        $result = $this->add($this->create($dataInfo));
        if($result){
            return true;
        }
        return false;
    }

    /**
     * 更新信息
     */
    public function update_data($data) {

        if(isset($data['name']) && !empty($data['name'])){
            $dataInfo['name'] = trim($data['name']);
        }
        if(isset($data['buyer_no']) && !empty($data['buyer_no'])){
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if(isset($data['bank_swift']) && !empty($data['bank_swift'])){
            $dataInfo['bank_swift'] = trim($data['bank_swift']);
        }
        if(isset($data['sinosure_no']) && !empty($data['sinosure_no'])){
            $dataInfo['sinosure_no'] = trim($data['sinosure_no']);
        }
        if(isset($data['nolc_granted']) && !empty($data['nolc_granted'])){
            $dataInfo['nolc_granted'] = trim($data['nolc_granted']);
        }
        if(isset($data['nolc_deadline']) && !empty($data['nolc_deadline'])){
            $dataInfo['nolc_deadline'] = trim($data['nolc_deadline']);
        }
        if(isset($data['lc_granted']) && !empty($data['lc_granted'])){
            $dataInfo['lc_granted'] = trim($data['lc_granted']);
        }
        if(isset($data['lc_deadline']) && !empty($data['lc_deadline'])){
            $dataInfo['lc_deadline'] = trim($data['lc_deadline']);
        }
        if(isset($data['deadline_cur_unit']) && !empty($data['deadline_cur_unit'])){
            $dataInfo['deadline_cur_unit'] = trim($data['deadline_cur_unit']);
        } else{
            $dataInfo['deadline_cur_unit'] = 'day';
        }
        if(isset($data['credit_invalid_date']) && !empty($data['credit_invalid_date'])){
            $dataInfo['credit_invalid_date'] = trim($data['credit_invalid_date']);
        }
        if(isset($data['approved_date']) && !empty($data['approved_date'])){
            $dataInfo['approved_date'] = trim($data['approved_date']);
        } else{
            $dataInfo['approved_date'] = date('Y-m-d H:i:s',time());
        }
        if(isset($data['status']) && !empty($data['status'])){
            $dataInfo['status'] = strtoupper($data['status']);
        }
        $result = $this->where(['buyer_no' => $data['buyer_no']])->save($this->create($dataInfo));
        if ($result !== false) {
            return true;
        }
        return false;
    }

    /**
     * 分配额度
     */
    public function grantInfo($data) {

        $dataArr = $this->_checkParam($data);
        $dataArr['buyer_no'] = $data['buyer_no'];
        $res = $this->update_data($dataArr);
        if($res) {
            $quota_log_model = new BuyerQuotaLogModel();
            $dataLog['buyer_no'] = $data['buyer_no'];
            $dataLog['credit_cur_bn'] = $dataArr['credit_cur_bn'];
            $dataLog['data_unit'] = $dataArr['deadline_cur_unit'];

            /*$valid_date = $this->field('credit_valid_date')->where(['buyer_no'=>$data['buyer_no']])->find();
            $dataLog['credit_invalid_date'] =  date('Y-m-d H:i:s',strtotime('+90 d',strtotime($valid_date['credit_valid_date'])));*/
            $dataLog['credit_at'] = $dataArr['credit_valid_date'];

            $dataLog['granted'] = $dataArr['nolc_granted'];
            $dataLog['validity'] = $dataArr['nolc_deadline'];
            $dataLog['type_apply'] = 'NOLC';
            $quota_log_model->create_data($dataLog);
            $dataLog['granted'] = $dataArr['lc_granted'];
            $dataLog['validity'] = $dataArr['lc_deadline'];
            $dataLog['type_apply'] = 'LC';
            $quota_log_model->create_data($dataLog);
            return $res;
        }
        return false;
    }
    private function _checkParam($data){
        if (!isset($data['nolc_granted']) || empty($data['nolc_granted']) || intval($data['nolc_granted']) > 1000000) {
            jsonReturn(null, -110, '请填写信用证额度或额度值过大!');
        } else {
            $dataArr['nolc_granted'] = intval($data['nolc_granted']);
        }
        if (!isset($data['nolc_deadline']) || empty($data['nolc_deadline']) || intval($data['nolc_deadline']) > 90) {
            jsonReturn(null, -110, '请填写信用证有效期限或期限值过大!');
        } else {
            $dataArr['nolc_deadline'] = intval($data['nolc_deadline']);
        }

        if (!isset($data['lc_granted']) || empty($data['lc_granted']) || intval($data['lc_granted']) > 300000) {
            jsonReturn(null, -110, '请填写非信用证额度或额度值过大!');
        } else {
            $dataArr['lc_granted'] = intval($data['lc_granted']);
        }
        if (!isset($data['lc_deadline']) || empty($data['lc_deadline']) || intval($data['lc_deadline']) > 90) {
            jsonReturn(null, -110, '请填写非信用证有效期限或期限值过大!');
        } else {
            $dataArr['lc_deadline'] = intval($data['lc_deadline']);
        }
        if (isset($data['deadline_cur_unit']) && !empty($data['deadline_cur_unit'])) {
            $dataArr['deadline_cur_unit'] = $data['deadline_cur_unit'];
        } else {
            $dataArr['deadline_cur_unit'] = 'day';
        }
        if (isset($data['credit_cur_bn']) && !empty($data['credit_cur_bn'])) {
            $dataArr['credit_cur_bn'] = $data['credit_cur_bn'];
        } else {
            $dataArr['credit_cur_bn'] = '$';
        }
        $data['credit_valid_date'] = date('Y-m-d H:i:s',time());
        $dataArr['status'] = 'APPROVED';   //分配额度为通过   银行和企业通过为信保通过
        return $dataArr;
    }
}