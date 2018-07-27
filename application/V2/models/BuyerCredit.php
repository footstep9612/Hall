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
        return $this->where(['buyer_no' => $buyer_no,'deleted_flag'=>'N'])->find();
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
                 `buyer_credit`.`buyer_credit`.`approved_date`,
                 `buyer_credit`.`buyer_credit`.`bank_remarks`,
                 `buyer_credit`.`buyer_credit`.`remarks`,
                 `buyer_credit`.`buyer_credit`.`nolc_deadline`,
                 `buyer_credit`.`buyer_credit`.`lc_deadline`,
                 `buyer_credit`.`buyer_credit`.`account_settle`,
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
        if (isset($condition['name']) && !empty($condition['name'])) {
            $where .= " And `buyer_credit`.`buyer_credit`.`name` like '%" . $condition['name'] . "%'";
        }
        if (isset($condition['sinosure_no']) && !empty($condition['sinosure_no'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`sinosure_no` = "' . $condition['sinosure_no'] .'"';
        }
        if (isset($condition['agent_id']) && !empty($condition['agent_id'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`agent_id` = "'. $condition['agent_id'] .'"';
        }
        if (isset($condition['bank_swift']) && !empty($condition['bank_swift'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`bank_swift`  = " ' . $condition['bank_swift'] . '"';
        }
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`status` ="' . strtoupper($condition['status']) . '"';
        }else{
            $where .= ' And  `buyer_credit`.`buyer_credit`.`status` <> "DRAFT"';
        }
        if (isset($condition['country_code']) && !empty($condition['country_code'])) {
            $where .= ' And `buyer_credit`.`buyer_reg_info`.`country_code` ="' . strtoupper($condition['country_code']) . '"';
        }
        if (isset($condition['account_settle']) && !empty($condition['account_settle'])) {
            $where .= ' And `buyer_credit`.`buyer_credit`.`account_settle` ="' . strtoupper($condition['account_settle']) . '"';
        }
        if (isset($condition['agent_name']) && !empty($condition['agent_name'])) {
            $where .= " And `erui_sys`.`employee`.`agent_name` like '%" . $condition['agent_name'] . "%'";
        }
        if (isset($condition['buyer_code']) && !empty($condition['buyer_code'])) {
            $where .= ' And `erui_buyer`.`buyer`.`buyer_code` = "' . $condition['buyer_code'] .'"';
        }
        if ($where) {
            $sql .= $where;
           // $sql_count .= $where;
        }
        $sql .= ' Order By ' . $order;
        $res['count'] = count($this->query($sql));
        if (!empty($limit['num'])) {
            $sql .= ' LIMIT ' . $limit['page'] . ',' . $limit['num'];
        }
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
        $field = 'id,agent_id,name,buyer_no,sinosure_no,credit_apply_date,approved_date,nolc_deadline,lc_deadline,status,bank_remarks,remarks,account_settle';
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
        if (isset($condition['source']) && !empty($condition['source'])) {
            $where['source'] = $condition['source'];                  //来源
        }
        if (isset($condition['agent_id']) && !empty($condition['agent_id'])) {
            $where['agent_id'] = $condition['agent_id'];                  //经办人
        }
        if (isset($condition['account_settle']) && !empty($condition['account_settle'])) {
            $where['account_settle'] = strtoupper($condition['account_settle']);                  //结算方式
        }
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where['status'] = strtoupper($condition['status']);
        } else{
            $where['status'] = array('neq', 'DRAFT');
        }
        if (isset($condition['status_arr']) && !empty($condition['status_arr'])) {
            $where['status']  = ['in', $condition['status_arr']];
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
        if(isset($data['credit_apply_date']) && !empty($data['credit_apply_date'])){
            $dataInfo['credit_apply_date'] = trim($data['credit_apply_date']);
        } else{
            $dataInfo['credit_apply_date'] = date('Y-m-d H:i:s', time());
        }
        if(isset($data['source']) && !empty($data['source'])){
            $dataInfo['source'] = trim($data['source']);
        } else{
            $dataInfo['source'] = 'BOSS';
        }
        if(isset($data['account_settle']) && !empty($data['account_settle'])){      //结算方式
            $dataInfo['account_settle'] = strtoupper($data['account_settle']);
        }

        if($dataInfo['account_settle'] == 'OA'){                 //可用额度
            $dataInfo['credit_available'] = trim($data['nolc_granted']);
        }elseif($dataInfo['account_settle'] == 'L/C'){
            $dataInfo['credit_available'] = trim($data['lc_granted']);
        }
        if(isset($data['crm_code']) && !empty($data['crm_code'])){      //crm编码
            $dataInfo['crm_code'] = trim($data['crm_code']);
        }
        $buyer_model = new BuyerModel();
        $agent_model = new BuyerAgentModel();
        $buyer_id = $buyer_model->field('id')->where(['buyer_no'=>$data['buyer_no']])->find();
        $agent_id = $agent_model->field('agent_id')->where(['buyer_id'=>$buyer_id['id']])->find();
        if($agent_id){
            $dataInfo['agent_id'] = $agent_id['agent_id'];
            $dataInfo['status'] = 'APPROVING';
        }else{
            $dataInfo['agent_id'] = UID;
            $dataInfo['status'] = 'APPROVING';
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
        if(isset($data['credit_valid_date']) && !empty($data['credit_valid_date'])){
            $dataInfo['credit_valid_date'] = trim($data['credit_valid_date']);
        }
        if(isset($data['credit_apply_date']) && !empty($data['credit_apply_date'])){
            $dataInfo['credit_apply_date'] = trim($data['credit_apply_date']);
        }
        if(isset($data['approved_date']) && !empty($data['approved_date'])){
            $dataInfo['approved_date'] = trim($data['approved_date']);
        }
        if(isset($data['status']) && !empty($data['status'])){
            $dataInfo['status'] = strtoupper($data['status']);
        } else {
            $dataInfo['status'] = 'APPROVING';
        }
        if(isset($data['bank_remarks']) && !empty($data['bank_remarks'])){
            $dataInfo['bank_remarks'] = trim($data['bank_remarks']);
        } else {
            $dataInfo['bank_remarks'] = '';
        }
        if(isset($data['remarks']) && !empty($data['remarks'])){
            $dataInfo['remarks'] = trim($data['remarks']);
        } else {
            $dataInfo['remarks'] = '';
        }
        if(isset($data['account_settle']) && !empty($data['account_settle'])){      //结算方式
            $dataInfo['account_settle'] = strtoupper($data['account_settle']);
        }
        if(isset($data['credit_available']) && !empty($data['credit_available'])){  //可用额度
            $dataInfo['credit_available'] = trim($data['credit_available']);
        }
        if(isset($data['crm_code']) && !empty($data['crm_code'])){      //crm编码
            $dataInfo['crm_code'] = trim($data['crm_code']);
        }
        /*$buyer_model = new BuyerModel();
        $agent_model = new BuyerAgentModel();
        $buyer_id = $buyer_model->field('id')->where(['buyer_no'=>$data['buyer_no']])->find();
        $agent_id = $agent_model->field('agent_id')->where(['buyer_id'=>$buyer_id['id']])->find();
        if($agent_id){
            $dataInfo['agent_id'] = $agent_id['agent_id'];
        } else {*/
            $dataInfo['agent_id'] = UID;
        //}

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

        $valid_date = $this->field('credit_apply_date,credit_valid_date,approved_date,account_settle')->where(['buyer_no'=>$data['buyer_no']])->find();
        $dataArr = $this->_checkParam($data,$valid_date['account_settle']);
        $dataArr['buyer_no'] = $data['buyer_no'];
        $res = $this->update_data($dataArr);
        if($res) {
            $quota_log_model = new BuyerQuotaLogModel();
            $dataLog['buyer_no'] = $data['buyer_no'];
            $dataLog['credit_cur_bn'] = $dataArr['credit_cur_bn'];
            $dataLog['data_unit'] = $dataArr['deadline_cur_unit'];
            if($valid_date['account_settle'] == "OA") {
                $dataLog['credit_at'] = date('Y-m-d H:i:s',time());
                $dataLog['credit_apply_date'] = $valid_date['credit_apply_date'];
                $dataLog['credit_invalid_date'] =  date('Y-m-d H:i:s',strtotime($valid_date['approved_date']." +".$dataArr['nolc_deadline']." day"));
                $dataLog['granted'] = $dataArr['nolc_granted'];
                $dataLog['validity'] = $dataArr['nolc_deadline'];
                $dataLog['type_apply'] = $valid_date['account_settle'];
                $quota_log_model->create_data($dataLog);
            } else {
                $dataLog['credit_invalid_date'] =  date('Y-m-d H:i:s',strtotime($valid_date['approved_date']." +".$dataArr['lc_deadline']." day"));
                $dataLog['granted'] = $dataArr['lc_granted'];
                $dataLog['validity'] = $dataArr['lc_deadline'];
                $dataLog['type_apply'] = 'L/C';
                $quota_log_model->create_data($dataLog);
            }
            return $res;
        }
        return false;
    }
    private function _checkParam($data,$account_settle){
        if($account_settle == "OA") {
            if (!isset($data['nolc_granted']) || empty($data['nolc_granted']) || intval($data['nolc_granted']) > 300000) {
                jsonReturn(null, -110, '请填写信用证额度或额度值过大!');
            } else {
                $dataArr['nolc_granted'] = intval($data['nolc_granted']);
            }
            if (!isset($data['nolc_deadline']) || empty($data['nolc_deadline']) || intval($data['nolc_deadline']) > 90) {
                jsonReturn(null, -110, '请填写信用证有效期限或期限值过大!');
            } else {
                $dataArr['nolc_deadline'] = intval($data['nolc_deadline']);
            }
        } else {
            if (!isset($data['lc_granted']) || empty($data['lc_granted']) || intval($data['lc_granted']) > 1000000) {
                jsonReturn(null, -110, '请填写非信用证额度或额度值过大!');
            } else {
                $dataArr['lc_granted'] = intval($data['lc_granted']);
            }
            if (!isset($data['lc_deadline']) || empty($data['lc_deadline']) || intval($data['lc_deadline']) > 90) {
                jsonReturn(null, -110, '请填写非信用证有效期限或期限值过大!');
            } else {
                $dataArr['lc_deadline'] = intval($data['lc_deadline']);
            }
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
        $dataArr['credit_valid_date'] = date('Y-m-d H:i:s',time());
        $dataArr['status'] = 'APPROVED';   //分配额度为通过   银行和企业通过为信保通过
        return $dataArr;
    }

    //设置市场经办人
    public function setAgentId($data) {
        if(empty($data['id']) || empty($data['user_ids'])){
            return false;                       //id为采购商ID
        }
        $buyer_model = new BuyerModel();
        $buyer = $buyer_model->field('buyer_no')->where(array('id'=>$data['id'],'deleted_flag'=>'N'))->find();
        $agent_model = new BuyerAgentModel();
        $agent = $agent_model->field('agent_id')->where(array('buyer_id'=>$data['id'],'deleted_flag'=>'N'))->find();
        if($agent['agent_id']) {
            $dataInfo['agent_id'] = $agent['agent_id'];
            $dataInfo['status'] = 'APPROVING';
            $this->where(['buyer_no' => $buyer['buyer_no']])->save($this->create($dataInfo));
        }
    }

    /**
     * 通过客户编码获取结算方式
     * @author
     */
    public function getAccountSettleByNo($buyer_no,$name) {
        if(!$buyer_no || !$name) return false;
        $credit_model = new BuyerCreditModel();
        $creditInfo = $credit_model->getInfo($buyer_no);
        if($creditInfo){
            return $creditInfo[$name];
        }
        return false;
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
}