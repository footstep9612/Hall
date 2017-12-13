<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class BuyerModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.buyer';

//    protected $autoCheckFields = false;
    public function __construct() {
        parent::__construct();
    }

    //状态

    const STATUS_APPROVING = 'APPROVING'; //待报审；
    const STATUS_FIRST_APPROVED = 'FIRST_APPROVED'; //待报审；
    const STATUS_FIRST_REJECTED = 'FIRST_REJECTED'; //初审驳回
    const STATUS_APPROVED = 'APPROVED'; //审核；
    const STATUS_REJECTED = 'REJECTED'; //无效；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */

    public function getlist($condition = [], $order = " id desc") {
        $sql = 'SELECT `erui_sys`.`employee`.`id` as employee_id,`erui_sys`.`employee`.`name` as employee_name,`erui_buyer`.`buyer`.`id`,`buyer_no`,`lang`,`buyer_type`,`erui_buyer`.`buyer`.`name`,`bn`,`profile`,`buyer`.`country_bn`,`erui_buyer`.`buyer`.`area_bn`,`buyer`.`province`,`buyer`.`city`,`official_email`,';
        $sql .= '`official_email`,`official_phone`,`official_fax`,`brand`,`official_website`,`logo`,`line_of_credit`,`credit_available`,`buyer_level`,`credit_level`,';
        $sql .= '`recommend_flag`,`erui_buyer`.`buyer`.`status`,`erui_buyer`.`buyer`.`remarks`,`apply_at`,`erui_buyer`.`buyer`.`created_by`,`erui_buyer`.`buyer`.`created_at`,`buyer`.`checked_by`,`buyer`.`checked_at`,';
        $sql .= '`erui_buyer`.`buyer`.address,`buyer_credit_log`.checked_by as credit_checked_by,`em`.`name` as credit_checked_name,`buyer_credit_log`.checked_at as credit_checked_at,`credit_apply_date`,`approved_at`,`buyer_credit_log`.in_status as credit_status,`buyer`.buyer_code ';
        $str = ' FROM ' . $this->g_table;
        $str .= " left Join `erui_buyer`.`buyer_agent` on `erui_buyer`.`buyer_agent`.`buyer_id` = `erui_buyer`.`buyer`.`id` ";
        $str .= " left Join `erui_sys`.`employee` on `erui_buyer`.`buyer_agent`.`agent_id` = `erui_sys`.`employee`.`id` AND `erui_sys`.`employee`.deleted_flag='N' ";
        $str .= " left Join `erui_buyer`.`buyer_account` on `erui_buyer`.`buyer_account`.`buyer_id` = `erui_buyer`.`buyer`.`id` AND `erui_buyer`.`buyer_account`.deleted_flag='N' ";
        $str .= " left Join `erui_buyer`.`buyer_credit_log` on `erui_buyer`.`buyer_credit_log`.`buyer_id` = `erui_buyer`.`buyer`.`id` ";
        $str .= " left Join `erui_sys`.`employee` as em on `erui_buyer`.`buyer_credit_log`.`checked_by` = `em`.`id` AND em.deleted_flag='N' ";
        $sql .= $str;
        $where = " WHERE buyer.deleted_flag = 'N'  ";
        if (!empty($condition['country_bn'])) {
            $where .= " And `buyer`.country_bn in (" . $condition['country_bn'] . ")";
        }

        if (!empty($condition['area_bn'])) {
            $where .= ' And `buyer`.area_bn ="' . $condition['area_bn'] . '"';
        }
        if (!empty($condition['name'])) {
            $where .= " And `erui_buyer`.`buyer`.name like '%" . $condition['name'] . "%'";
        }
        if (!empty($condition['buyer_no'])) {
            $where .= ' And buyer_no  ="' . $condition['buyer_no'] . '"';
        }
        if (!empty($condition['employee_name'])) {
            $where .= " And `erui_sys`.`employee`.`name`  like '%" . $condition['employee_name'] . "%'";
        }
        if (!empty($condition['agent_id'])) {
            $where .= " And `erui_buyer`.`buyer_agent`.`agent_id`  in (" . $condition['agent_id'] . ")";
        }
        if (!empty($condition['official_phone'])) {
            $where .= ' And official_phone  = " ' . $condition['official_phone'] . '"';
        }
        if (!empty($condition['status'])) {
            $where .= ' And `erui_buyer`.`buyer`.status  ="' . $condition['status'] . '"';
        }
        if (!empty($condition['user_name'])) {
            $where .= ' And `erui_buyer`.`buyer_account`.`user_name`  ="' . $condition['user_name'] . '"';
        }
        if (!empty($condition['checked_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer`.checked_at  >="' . $condition['checked_at_start'] . '"';
        }
        if (!empty($condition['checked_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer`.checked_at  <="' . $condition['checked_at_end'] . '"';
        }
        if (!empty($condition['created_by'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_by  ="' . $condition['created_by'] . '"';
        }
        if (!empty($condition['source'])) {
            if ($condition['source'] == 1) {
                $where .= ' And `erui_buyer`.`buyer`.created_by  > 0';
            } else if ($condition['source'] == 2) {
                $where .= ' And `erui_buyer`.`buyer`.created_by  is null';
            }
        }
        if (!empty($condition['created_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  >="' . $condition['created_at_start'] . '"';
        }
        if (!empty($condition['created_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  <="' . $condition['created_at_end'] . '"';
        }
        if (!empty($condition['credit_checked_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.checked_at  >="' . $condition['credit_checked_at_start'] . '"';
        }
        if (!empty($condition['credit_checked_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.checked_at  <="' . $condition['credit_checked_at_end'] . '"';
        }
        if (!empty($condition['approved_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.approved_at  >="' . $condition['approved_at_start'] . '"';
        }
        if (!empty($condition['approved_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.approved_at  <="' . $condition['approved_at_end'] . '"';
        }
        if (!empty($condition['credit_checked_name'])) {
            $where .= " And `em`.`name`  like '%" . $condition['credit_checked_name'] . "%'";
        }
        if (!empty($condition['buyer_code'])) {
            $where .= ' And buyer_code  like "%' . $condition['buyer_code'] . '%"';
        }
        if (!empty($condition['line_of_credit_max'])) {
            $where .= ' And `erui_buyer`.`buyer`.line_of_credit  <="' . $condition['line_of_credit_max'] . '"';
        }
        if (!empty($condition['line_of_credit_min'])) {
            $where .= ' And `erui_buyer`.`buyer`.line_of_credit  >="' . $condition['line_of_credit_min'] . '"';
        }
        if (!empty($condition['credit_status'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.in_status  ="' . $condition['credit_status'] . '"';
        }
        if ($condition['is_agent']=='Y') {
            $where .= ' And (`erui_buyer`.`buyer`.created_by  ="' . $condition['agent']['user_id'] . '" OR `erui_buyer`.`buyer_agent`.`agent_id`  in ("' . $condition['agent']['agent_id'] . '"))';
        }
        if ($where) {
            $sql .= $where;
            // $sql_count .= $where;
        }
        $sql .= ' Group By `erui_buyer`.`buyer`.`id`';
        //$sql_count .= ' Group By `erui_buyer`.`buyer`.`id`';
        $sql .= ' Order By ' . $order;
        $res['count'] = count($this->query($sql));
        if ($condition['num']) {
            $sql .= ' LIMIT ' . $condition['page'] . ',' . $condition['num'];
        }

        //$count = $this->query($sql_count);

        $res['data'] = $this->query($sql);
        return $res;
    }

    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function Exist($data) {
        $sql = 'SELECT `id`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
        $sql .= '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
        $sql .= '`status`,`remarks`,`apply_at`,`approved_at`';
        $sql .= ' FROM ' . $this->g_table;
        $where = '';
        if (!empty($data['email'])) {
            $where .= " where email = '" . $data['email'] . "'";
        }
        if (!empty($data['mobile'])) {
            if ($where) {
                $where .= " or mobile = '" . $data['mobile'] . "'";
            } else {
                $where .= " where mobile = '" . $data['mobile'] . "'";
            }
        }
        if (!empty($data['id'])) {
            if ($where) {
                $where .= " and id = '" . $data['id'] . "'";
            } else {
                $where .= " where id = '" . $data['id'] . "'";
            }
        }
        if (!empty($data['customer_id'])) {
            if ($where) {
                $where .= " and customer_id = '" . $data['customer_id'] . "'";
            } else {
                $where .= " where customer_id = '" . $data['customer_id'] . "'";
            }
        }

        if ($where) {
            $where .= " and deleted_flag = 'N'";
        } else {
            $where .= " where deleted_flag = 'N'";
        }
        if ($where) {
            $sql .= $where;
        }
        $row = $this->query($sql);
        return empty($row) ? false : $row;
    }

    public function create_data($create = []) {
        if (isset($create['buyer_no'])) {
            $data['buyer_no'] = $create['buyer_no'];
        }
        if (isset($create['buyer_code'])) {
            $data['buyer_code'] = $create['buyer_code'];    //新增CRM编码，张玉良 2017-9-27
        }
        if (isset($create['lang'])) {
            $data['lang'] = $create['lang'];
        } else {
            $data['lang'] = 'en';
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['bn'])) {
            $data['bn'] = $create['bn'];
        }
        if (isset($create['profile'])) {
            $data['profile'] = $create['profile'];
        }
        if (isset($create['type_remarks'])) {
            $data['type_remarks'] = $create['type_remarks'];
        }
        if (isset($create['employee_count'])) {
            $data['employee_count'] = $create['employee_count'];
        }
        if (isset($create['reg_capital'])) {
            $data['reg_capital'] = $create['reg_capital'];
        }
        if (isset($create['reg_capital_cur'])) {
            $data['reg_capital_cur'] = $create['reg_capital_cur'];
        }
        if (isset($create['expiry_at'])) {
            $data['expiry_at'] = $create['expiry_at'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
        }
        if (isset($create['area_bn'])) {
            $data['area_bn'] = $create['area_bn'];
        }
        if (isset($create['official_email'])) {
            $data['official_email'] = $create['official_email'];
        }
        if (isset($create['official_phone'])) {
            $data['official_phone'] = $create['official_phone'];
        }
        if (isset($create['official_fax'])) {
            $data['official_fax'] = $create['official_fax'];
        }
        if (isset($create['province'])) {
            $data['province'] = $create['province'];
        }
        if (isset($create['logo'])) {
            $data['logo'] = $create['logo'];
        }
        if (isset($create['city'])) {
            $data['city'] = $create['city'];
        }
        if (isset($create['biz_scope'])) {
            $data['biz_scope'] = $create['biz_scope'];
        }
        if (isset($create['intent_product'])) {
            $data['intent_product'] = $create['intent_product'];
        }
        if (isset($create['purchase_amount'])) {
            $data['purchase_amount'] = $create['purchase_amount'];
        }
        if (isset($create['brand'])) {
            $data['brand'] = $create['brand'];
        }
        if (isset($create['bank_name'])) {
            $data['bank_name'] = $create['bank_name'];
        }
        if (isset($create['official_website'])) {
            $data['official_website'] = $create['official_website'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['created_by'])) {
            $data['created_by'] = $create['created_by'];
        }
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'APPROVED';
        if (isset($create['created_by'])) {
            $data['checked_by']  = $create['created_by'];
            $data['checked_at'] = date('Y-m-d H:i:s');

        }
        try {
            $datajson = $this->create($data);
            $res = $this->add($datajson);
            if($res){
                $checked_log_arr['id'] = $res;
                $checked_log_arr['status'] = 'APPROVED';
                $checked_log_arr['checked_by'] = $create['created_by'];
                $checked_log = new BuyerCheckedLogModel();
                $checked_log->create_data($checked_log_arr);
            }
            return $res;
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author jhw
     */
    public function info($data) {

        if ($data['id']) {
            $buyerInfo = $this->where(array("buyer.id" => $data['id']))->field('buyer.*,em.name as checked_name')
                    ->join('erui_sys.employee em on em.id=buyer.checked_by', 'left')
                    ->find();
            $sql = "SELECT  `id`,  `buyer_id`,  `attach_type`,  `attach_name`,  `attach_code`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui_buyer`.`buyer_attach` where deleted_flag ='N' and buyer_id = " . $data['id'];
            $row = $this->query($sql);
            if ($row) {
                $buyerInfo['attach'] = $row[0];
            }
            return $buyerInfo;
        } else {
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($create, $where) {

        if (isset($create['buyer_code'])) {
            $data['buyer_code'] = $create['buyer_code'];    //新增CRM编码，张玉良 2017-9-27
        }
        if (isset($create['lang'])) {
            $data['lang'] = $create['lang'];
        } else {
            $data['lang'] = 'en';
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['bn'])) {
            $data['bn'] = $create['bn'];
        }
        if (isset($create['profile'])) {
            $data['profile'] = $create['profile'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
        }
        if (isset($create['official_email'])) {
            $data['official_email'] = $create['official_email'];
        }
        if (isset($create['level_at'])) {
            $data['level_at'] = $create['level_at'];
        }
        if (isset($create['official_phone'])) {
            $data['official_phone'] = $create['official_phone'];
        }
        if (isset($create['official_fax'])) {
            $data['official_fax'] = $create['official_fax'];
        }
        if (isset($create['province'])) {
            $data['province'] = $create['province'];
        }
        if (isset($create['area_bn'])) {
            $data['area_bn'] = $create['area_bn'];
        }
        if (isset($create['logo'])) {
            $data['logo'] = $create['logo'];
        }

        if (isset($create['type_remarks'])) {
            $data['type_remarks'] = $create['type_remarks'];
        }
        if (isset($create['employee_count'])) {
            $data['employee_count'] = $create['employee_count'];
        }
        if (isset($create['reg_capital'])) {
            $data['reg_capital'] = $create['reg_capital'];
        }
        if (isset($create['reg_capital_cur'])) {
            $data['reg_capital_cur'] = $create['reg_capital_cur'];
        }
        if (isset($create['expiry_at'])) {
            $data['expiry_at'] = $create['expiry_at'];
        }
        if (isset($create['city'])) {
            $data['city'] = $create['city'];
        }
        if (isset($create['line_of_credit'])) {
            $data['line_of_credit'] = $create['line_of_credit'];
        }
        if (isset($create['credit_available'])) {
            $data['credit_available'] = $create['credit_available'];
        }
        if (isset($create['brand'])) {
            $data['brand'] = $create['brand'];
        }
        if (isset($create['bank_name'])) {
            $data['bank_name'] = $create['bank_name'];
        }
        if (isset($create['official_website'])) {
            $data['official_website'] = $create['official_website'];
        }
        if (isset($create['buyer_level'])) {
            $data['buyer_level'] = $create['buyer_level'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        if (isset($create['checked_at'])) {
            $data['checked_at'] = $create['checked_at'];
        }
        if (isset($create['biz_scope'])) {
            $data['biz_scope'] = $create['biz_scope'];
        }
        if (isset($create['intent_product'])) {
            $data['intent_product'] = $create['intent_product'];
        }
        if (isset($create['purchase_amount'])) {
            $data['purchase_amount'] = $create['purchase_amount'];
        }
        if (isset($create['status'])) {
            switch ($create['status']) {
                case self::STATUS_APPROVED:
                    $data['status'] = $create['status'];
                    if($where['id']){
                        $checked_log_arr['id'] = $where['id'];
                        $checked_log_arr['status'] = self::STATUS_APPROVED;
                        $checked_log_arr['checked_by'] = $create['checked_by'];
                        $checked_log = new BuyerCheckedLogModel();
                        $checked_log->create_data($checked_log_arr);
                    }
                    break;
                case self::STATUS_APPROVING:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_FIRST_APPROVED:
                    $data['status'] = $create['status'];
                    if($where['id']){
                        $checked_log_arr['id'] = $where['id'];
                        $checked_log_arr['status'] = self::STATUS_FIRST_APPROVED;
                        $checked_log_arr['checked_by'] = $create['checked_by'];
                        $checked_log_arr['remarks'] = $create['remarks'];
                        $checked_log = new BuyerCheckedLogModel();
                        $checked_log->create_data($checked_log_arr);
                    }
                    break;
                case self::STATUS_FIRST_REJECTED:
                    $data['status'] = $create['status'];
                    if($where['id']){
                        $checked_log_arr['id'] = $where['id'];
                        $checked_log_arr['status'] = self::STATUS_FIRST_REJECTED;
                        $checked_log_arr['checked_by'] = $create['checked_by'];
                        $checked_log_arr['remarks'] = $create['remarks'];
                        $checked_log = new BuyerCheckedLogModel();
                        $checked_log->create_data($checked_log_arr);
                    }
                    break;
                case self::STATUS_REJECTED:
                    $data['status'] = $create['status'];
                    if($where['id']){
                        $checked_log_arr['id'] = $where['id'];
                        $checked_log_arr['status'] = self::STATUS_REJECTED;
                        $checked_log_arr['checked_by'] = $create['checked_by'];
                        $checked_log_arr['remarks'] = $create['remarks'];
                        $checked_log = new BuyerCheckedLogModel();
                        $checked_log->create_data($checked_log_arr);
                    }
                    break;
            }
        }
        return $this->where($where)->save($data);
    }

    /**
     * 通过顾客id获取会员等级
     * @author klp
     */
    public function getService($info, $token) {
        $where = array();
        if (!empty($token['customer_id'])) {
            $where['customer_id'] = $token['customer_id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        $lang = $info['lang'] ? strtolower($info['lang']) : (browser_lang() ? browser_lang() : 'en');
        //获取会员等级
        $buyerLevel = $this->field('buyer_level')
                ->where($where)
                ->find();
        //获取服务
        $MemberBizService = new MemberBizServiceModel();
        $result = $MemberBizService->getService($buyerLevel, $lang);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * 获取采购商信息 NEW
     * @author klp
     */
    public function buyerCerdit($user) {
        $where = array();
        if (!empty($user['id'])) {
            $where['b.id'] = $user['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        if (isset($user['buyer_no'])) {
            $where['b.buyer_no'] = $user['buyer_no'];
        }
        $where['b.deleted_flag'] = 'N';

//        $buyerAccountModel = new BuyerAccountModel();
//        $tableAcco = $buyerAccountModel->getTableName();
        $buyeraddress_model = new BuyerAddressModel();
        $tableAddr = $buyeraddress_model->getTableName();
        $BuyerreginfoModel = new BuyerreginfoModel();
        $tableReg = $BuyerreginfoModel->getTableName();
        $buyerBankInfoModel = new BuyerBankInfoModel();
        $tableBank = $buyerBankInfoModel->getTableName();
        try {
            //必填项
            $fields = 'b.id as buyer_id, b.lang, b.buyer_no, b.area_bn, b.name, bd.address, bb.country_code as bank_country_code, bb.address as bank_address, bb.bank_name';
            //基本信息-$this
            $fields .= ',b.buyer_type,b.bn,b.country_bn,b.profile,b.province,b.city,b.official_email,b.official_phone,b.official_fax,b.brand,b.official_website,b.line_of_credit,b.credit_available,b.buyer_level,b.credit_level,b.recommend_flag,b.status,b.remarks';
            //注册信息-BuyerreginfoModel
            $fields .= ',br.legal_person_name,br.legal_person_gender,br.reg_date,br.expiry_date,br.registered_in,br.reg_capital,br.social_credit_code,br.biz_nature,br.biz_scope,br.biz_type,br.service_type,br.branch_count,br.employee_count,br.equitiy,br.turnover,br.profit,br.total_assets,br.reg_capital_cur_bn,br.equity_ratio,br.equity_capital';
            //注册银行信息-BuyerBankInfoModel
            $fields .= ',bb.swift_code,bb.bank_account,bb.country_bn as bank_country_bn,bb.zipcode as bank_zipcode,bb.phone,fax,bb.turnover,bb.profit,bb.total_assets,bb.reg_capital_cur_bn,bb.equity_ratio,bb.equity_capital,bb.branch_count,bb.employee_count,bb.remarks as bank_remarks';
            $buyerInfo = $this->alias('b')
                    ->field($fields)
                    ->join($tableBank . ' as bb on bb.buyer_id=b.id ', 'left')
                    ->join($tableAddr . ' as bd on bd.buyer_id=b.id', 'left')
                    ->join($tableReg . ' as br on br.buyer_id=b.id', 'left')
                    ->where($where)
                    ->find();
            if ($buyerInfo) {
                return $buyerInfo ? $buyerInfo : array();
            }
            return array();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取采购商信息
     * @author klp
     */
    public function buyerInfo($user) {
        $where = array();
        if (!empty($user['id'])) {
            $where['id'] = $user['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        if (isset($user['buyer_no'])) {
            $where['buyer_no'] = $user['buyer_no'];
        }
        $where['deleted_flag'] = 'N';
        $field = 'id,lang,buyer_type,buyer_no,name,bn,country_bn,profile,province,city,official_email,official_phone,official_fax,brand,official_website,line_of_credit,credit_available,buyer_level,credit_level,recommend_flag,status,remarks,apply_at,created_by,created_at,checked_by,checked_at';
        try {
            $buyerInfo = $this->field($field)->where($where)->find();
            if ($buyerInfo) {
                $BuyerreginfoModel = new BuyerreginfoModel();
                $result = $BuyerreginfoModel->buyerRegInfo($where);
                return $result ? array_merge($buyerInfo, $result) : $buyerInfo;
            }
            return array();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 企业信息新建-门户
     * @author klp
     */
    public function editInfo($token, $input) {
        if (!isset($input)) {
            return false;
        }
        $this->startTrans();
        try {
            if (is_array($input)) {
                $checkout = $this->checkParam($input);
                $data = [
                    'name' => $checkout['name'],
                    'country_code' => strtoupper($checkout['country_code']),
                    'country_bn' => strtoupper($checkout['country_code']),
                    'official_email' => isset($checkout['official_email']) ? $checkout['official_email'] : '',
                    'official_phone' => isset($checkout['official_phone']) ? $checkout['official_phone'] : '',
                    'official_fax' => isset($checkout['official_fax']) ? $checkout['official_fax'] : '',
                    'official_website' => isset($checkout['official_website']) ? $checkout['official_website'] : '',
                    'province' => isset($checkout['province']) ? $checkout['province'] : '', //暂为办公地址
                    'remarks' => isset($checkout['remarks']) ? $checkout['remarks'] : '',
                    'recommend_flag' => isset($checkout['recommend_flag']) ? strtoupper($checkout['recommend_flag']) : 'N'
                ];
                //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                $result = $this->field('id')->where(['id' => $token['id']])->find();
                if ($result) {
                    $result = $this->where(['id' => $token['id']])->save($data);
                    if (!$result) {
                        $this->rollback();
                        return false;
                    }
                } else {
                    // 生成用户编码
                    $condition['page'] = 0;
                    $condition['countPerPage'] = 1;
                    $data_t_buyer = $this->getlist($condition);
                    if ($data_t_buyer && substr($data_t_buyer[0]['buyer_no'], 1, 8) == date("Ymd")) {
                        $no = substr($data_t_buyer[0]['buyer_no'], -1, 6);
                        $no++;
                    } else {
                        $no = 1;
                    }
                    $temp_num = 1000000;
                    $new_num = $no + $temp_num;
                    $real_num = "C" . date("Ymd") . substr($new_num, 1, 6); //即截取掉最前面的“1”即为buyer_no

                    $data['buyer_no'] = $real_num;
                    $data['apply_at'] = date('Y-m-d H:i:s', time());
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $data['status'] = self::STATUS_CHECKING; //待审状态
                    $result = $this->add($data);
                    if (!$result) {
                        $this->rollback();
                        return false;
                    }
                }
                //buyer_reg_info
                $buyerRegInfo = new BuyerreginfoModel();
                $result = $buyerRegInfo->createInfo($token, $input);
                if (!$result) {
                    $this->rollback();
                    return false;
                }
                //buyer_address
                $BuyerBankInfoModel = new BuyerBankInfoModel();
                $res = $BuyerBankInfoModel->editInfo($token, $input);
                if (!$res) {
                    $this->rollback();
                    return false;
                }
            } else {
                return false;
            }
            $this->commit();
            return $token['buyer_id'];
        } catch (Exception $e) {
            $this->rollback();
//            var_dump($e);//测试
            return false;
        }
    }

    /**
     * 参数校验-门户
     * @author klp
     */
    private function checkParam($param = []) {
        if (empty($param)) {
            return false;
        }
        $results = array();
        if (empty($param['name'])) {
            $results['code'] = -101;
            $results['message'] = '[name]不能为空!';
        }
        if (empty($param['bank_name'])) {
            $results['code'] = -101;
            $results['message'] = '[bank_name]不能为空!';
        }
        if (empty($param['bank_address'])) {
            $results['code'] = -101;
            $results['message'] = '[bank_address]不能为空!';
        }
        if (empty($param['province'])) {
            $results['code'] = -101;
            $results['message'] = '[province]不能为空!';
        }
        if ($results) {
            jsonReturn($results);
        }
        return $param;
    }

    /**
     * 获取授信的列表
     * @param  string $code 编码
     * @param  string $lang 语言
     * @return mix
     * @author klp
     */
    public function getListCredit($condition) {
        $BuyerCreditLogModel = new BuyerCreditLogModel(); //取BuyerCreditLog表名
        $creditLogtable = $BuyerCreditLogModel->getTableName();
        $where = array();
        list($from, $pagesize) = $this->_getPage($where);
        //编号
        $this->_getValue($where, $condition, 'id', 'string', 'b.id');
        //审核人
        $this->_getValue($where, $condition, 'approved_by', 'string', 'cb.approved_by');
        $this->_getUserids($where, $condition, 'approved_by_name', 'cb.approved_by');


        $this->_getUserids($where, $condition, 'checked_by_name', 'cb.checked_by');

        //审核人
        //   $this->_getValue($where, $condition, 'approved_by_name', 'array', 'cb.approved_by');
        //公司名称
        $this->_getValue($where, $condition, 'name', 'like', 'b.name');
        //审核状态
        $where['b.status'] = self::STATUS_APPROVED;


        if (isset($condition['status']) && $condition['status']) {
            switch ($condition['status']) {
                case '05'://信保通过

                    $where['cl.out_status'] = 'APPROVED';
                    break;
                case '04'://信保驳回
                    $where['cl.out_status'] = 'REJECTED';
                    break;
                case '03'://易瑞通过
                    $where['cl.in_status'] = 'APPROVED';
                    break;
                case '02'://易瑞驳回
                    $where['cl.in_status'] = 'REJECTED';
                    break;
                case '01'://待易瑞审核
                    $where['cl.in_status'] = 'APPROVING';
                    break;
                default :
                    break;
            }
        }
        //授信额度(暂无字段,待完善)
        $this->_getValue($where, $condition, 'credit', 'between', 'b.line_of_credit');
        //信保审核时间段(暂无,待完善)
        $this->_getValue($where, $condition, 'approved_at', 'between', 'cl.approved_at');
        //易瑞审核时间段
        $this->_getValue($where, $condition, 'checked_at', 'between', 'cl.checked_at');
        //字段待完善
        $field = 'b.id,b.buyer_no,b.name,b.apply_at,b.lang,cl.credit_grantor,cl.credit_granted,'
                . 'cl.in_status,cl.checked_by,cl.checked_at,cl.out_status,cl.approved_by,cl.approved_at';

        $result = $this->alias('b')->field($field)->order("id desc")
                        ->join($creditLogtable . ' as cl ON b.id = cl.id', 'INNER')
                        ->limit($from, $pagesize)->where($where)->select();

        $count = $this->alias('b')->join($creditLogtable . ' as cl ON b.id = cl.id', 'LEFT')
                        ->where($where)->count('b.id');
        $this->_setUserName($result, 'checked_by');
        $this->_setUserName($result, 'approved_by');
        return [$result, $count];
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setUserName(&$arr, $filed) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                $userids[] = $val[$filed];
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                if ($val[$filed] && isset($usernames[$val[$filed]])) {
                    $val[$filed . '_name'] = $usernames[$val[$filed]];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    private function _getUserids(&$where, &$condition, $name, $filed = 'created_by') {
        if (isset($condition[$name]) && $condition[$name]) {
            $employee_model = new EmployeeModel();
            $userids = $employee_model->getUseridsByUserName($condition[$name]);
            if ($userids) {
                $where[$filed] = ['in', $userids];
            }
        }
    }

    /*
     * 根据用户姓 获取用户ID
     * @param string $BuyerName // 客户名称
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getBuyeridsByBuyerName($buyername) {

        try {
            $where = [];
            if ($buyername) {
                $where['name'] = ['like', '%' . trim($buyername) . '%'];
            } else {
                return false;
            }
            $buyers = $this->where($where)->field('id')->select();
            $buyerids = [];
            foreach ($buyers as $buyer) {
                $buyerids[] = $buyer['id'];
            }
            return $buyerids;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据用户ID 获取用户姓名
     * @param array $user_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getBuyerNamesByBuyerids($buyer_ids) {

        try {
            $where = [];

            if (is_string($buyer_ids)) {
                $where['id'] = $buyer_ids;
            } elseif (is_array($buyer_ids) && !empty($buyer_ids)) {
                $where['id'] = ['in', $buyer_ids];
            } else {
                return false;
            }
            $buyers = $this->where($where)->field('id,name,buyer_no')->select();

            $buyer_names = [];
            foreach ($buyers as $buyer) {
                $buyer_arr['buyer_names'][$buyer['id']] = $buyer['name'];
                $buyer_arr['buyer_nos'][$buyer['id']] = $buyer['buyer_no'];
            }
            return $buyer_arr;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据用户ID 获取用户姓名
     * @param array $user_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getBuyerNosByBuyerids($buyer_ids) {

        try {
            $where = [];

            if (is_string($buyer_ids)) {
                $where['id'] = $buyer_ids;
            } elseif (is_array($buyer_ids) && !empty($buyer_ids)) {
                $where['id'] = ['in', $buyer_ids];
            } else {
                return false;
            }
            $buyers = $this->where($where)->field('id,name,buyer_no')->select();

            $buyer_names = [];
            foreach ($buyers as $buyer) {
                $buyer_arr[$buyer['id']] = $buyer['buyer_no'];
            }
            return $buyer_arr;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 更新授信金额
     * @param int $order_id // 订单ID
     * @param string  $type // 授信类型
     * @param floatval $amount // 授信金额
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function updateCredite($order_id, $type, $amount) {
        $order_model = new OrderModel();
        $orderinfo = $order_model->field('buyer_id,id')->where(['id' => $order_id])->find();

        $amount = floatval($amount);
        $buyer = $this->field('credit_available,credit_available,id')
                        ->where(['id' => $orderinfo['buyer_id']])->find();
        if ($buyer) {
            if ($type == 'REFUND') {
                $flag = $this->where(['id' => $buyer['id']])
                        ->save(['credit_available' => $buyer['credit_available'] + $amount]);
            } elseif ($type == 'SPENDING') {
                $flag = $this->where(['id' => $buyer['id']])
                        ->save(['credit_available' => $buyer['credit_available'] - $amount]);
            }
            if ($flag === false) {

                return ['code' => MSG::MSG_FAILED, 'message' => '更新授信额度错误!'];
            } else {
                return ['code' => MSG::MSG_SUCCESS, 'message' => '更新成功!'];
            }
        } else {
            return ['code' => MSG::MSG_FAILED, 'message' => '客户不存在!'];
        }
    }
    /**
     * 获取各状态下会员数量
     * @return data
     * @author jhw
     */
    public function getBuyerCountByStatus($condition){
        $sql = "SELECT  `erui_buyer`.buyer.`status` ,COUNT(*)  as number ";
        $str = ' FROM ' . $this->g_table;
        $str .= " left Join `erui_buyer`.`buyer_agent` on `erui_buyer`.`buyer_agent`.`buyer_id` = `erui_buyer`.`buyer`.`id` ";
        $str .= " left Join `erui_sys`.`employee` on `erui_buyer`.`buyer_agent`.`agent_id` = `erui_sys`.`employee`.`id` AND `erui_sys`.`employee`.deleted_flag='N' ";
        $sql .= $str;
        $where = " WHERE buyer.deleted_flag = 'N'  ";
        if (!empty($condition['country_bn'])) {
            $where .= " And `buyer`.country_bn in (" . $condition['country_bn'] . ")";
        }
        if (!empty($condition['name'])) {
            $where .= " And `erui_buyer`.`buyer`.name like '%" . $condition['name'] . "%'";
        }
        if (!empty($condition['buyer_no'])) {
            $where .= ' And buyer_no  ="' . $condition['buyer_no'] . '"';
        }
        if (!empty($condition['employee_name'])) {
            $where .= " And `erui_sys`.`employee`.`name`  like '%" . $condition['employee_name'] . "%'";
        }
        if (!empty($condition['status'])) {
            $where .= ' And `erui_buyer`.`buyer`.status  ="' . $condition['status'] . '"';
        }
        if (!empty($condition['checked_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer`.checked_at  >="' . $condition['checked_at_start'] . '"';
        }
        if (!empty($condition['checked_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer`.checked_at  <="' . $condition['checked_at_end'] . '"';
        }
        if (!empty($condition['created_by'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_by  ="' . $condition['created_by'] . '"';
        }
        if (!empty($condition['source'])) {
            if ($condition['source'] == 1) {
                $where .= ' And `erui_buyer`.`buyer`.created_by  > 0';
            } else if ($condition['source'] == 2) {
                $where .= ' And `erui_buyer`.`buyer`.created_by  is null';
            }
        }
        if (!empty($condition['created_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  >="' . $condition['created_at_start'] . '"';
        }
        if (!empty($condition['created_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  <="' . $condition['created_at_end'] . '"';
        }
        if (!empty($condition['buyer_code'])) {
            $where .= ' And buyer_code  like "%' . $condition['buyer_code'] . '%"';
        }
        if ($condition['is_agent']=='Y') {
            $where .= ' And (`erui_buyer`.`buyer`.created_by  ="' . $condition['agent']['user_id'] . '" OR `erui_buyer`.`buyer_agent`.`agent_id`  in ("' . $condition['agent']['agent_id'] . '"))';
        }
        if ($where) {
            $sql .= $where;
            // $sql_count .= $where;
        }
        $sql .= ' Group By `buyer`.status';
        //$sql_count .= ' Group By `erui_buyer`.`buyer`.`id`';
        $res['count'] = count($this->query($sql));
        if ($condition['num']) {
            $sql .= ' LIMIT ' . $condition['page'] . ',' . $condition['num'];
        }

        //$count = $this->query($sql_count);

        $res['data'] = $this->query($sql);


        $row = $this->query($sql);
        return $row;
    }
    /**
     * 客户档案管理搜索列表-
     * wangs
     */
    public function buyerList($data)
    {
        //条件
        $cond = "buyer.created_by=$data[created_by]";
        if(!empty($data['area_bn'])){
            $cond .= " and buyer.area_bn=$data[area_bn]";
        }
        if(!empty($data['country_bn'])){
            $cond .= " and buyer.country_bn=$data[country_bn]";
        }
        if(!empty($data['buyer_level'])){
            $cond .= " and buyer.buyer_level=$data[buyer_level]";
        }
        if(!empty($data['buyer_code'])){
            $cond .= " and buyer.buyer_code like '%$data[buyer_code]%'";
        }
        if(!empty($data['name'])){
            $cond .= " and buyer.name like '%$data[name]%'";
        }
        if(!empty($data['reg_capital'])){
            $cond .= " and buyer.reg_capital like '%$data[reg_capital]%'";
        }
        if(!empty($data['line_of_credit'])){
            $cond .= " and buyer.line_of_credit like '%$data[line_of_credit]%'";
        }
        $totalCount = $this->alias('buyer')
            ->join('erui_buyer.buyer_business business on buyer.id=business.buyer_id','left')
            ->where($cond)
            ->count();
        $pageSize = 10;
        $totalPage = ceil($totalCount/$pageSize);
        $page = isset($data['page'])&&!empty($data['page'])&& $data['page']>0 ? ceil($data['page']) : 1;
        if($page > $totalPage && $totalPage > 0){
            $page = $totalPage;
        }
        $offset = ($page-1)*$pageSize;
        $field ='buyer.id';
        $fieldBuyerArr = array(
//            'id',   //客户id
            'area_bn',   //客地区
            'country_bn',   //国家
            'buyer_code',   //客户编码
            'name as buyer_name',   //客户名称
            'created_at',   //创建时间
            'is_oilgas',   //是否油气
            'buyer_level',   //客户等级
            'level_at',   //等级设置时间
            'reg_capital',   //注册资金
            'reg_capital_cur',   //货币
            'credit_level',   //采购商信用等级
            'credit_type',   //授信类型
            'line_of_credit',   //授信额度
        );
        foreach($fieldBuyerArr as $v){
            $field .= ',buyer.'.$v;
        }
        $fieldBusiness = array(
            'is_net', //是否入网
            'net_at', //入网时间
            'net_invalid_at', //失效时间
            'product_type', //产品类型
            'is_local_settlement', //本地结算
            'is_purchasing_relationship', //采购关系
        );
        foreach($fieldBusiness as $v){
            $field .= ',business.'.$v;
        }
        $info = $this->alias('buyer')
            ->join('erui_buyer.buyer_business business on buyer.id=business.buyer_id','left')
            ->field($field)
            ->where($cond)
            ->order('buyer.id desc')
            ->limit($offset,$pageSize)
            ->select();

        $ids = array();
        foreach($info as $k => $v){
            $ids[$v['id']] = $v['id'];
        }
        $res = array(
            'ids' => $ids,
            'info' => $info,
            'page'=>$page,
            'totalCount'=>$totalCount,
            'totalPage'=>$totalPage,
        );
        return $res;
    }
    /**
     * 专用采购商客户基本创建 ----数据验证
     * wangs
     */
    public function validBuyerBaseData($data){
        //验证必填数据非空
        $baseArr = array(
            'buyer_id', //采购商客户id
            'buyer_name', //采购商客户名称
//            'buyer_code', //客户代码
//            'buyer_level', //客户级别
//            'level_at', //定级日期
//            'expiry_at', //有效期
//            'country_bn', //国家
//            'area_bn', //地区
//            'employee_count', //雇员数量
            'official_phone', //公司固话
            'official_email', //公司邮箱
            'official_website', //公司网址
            'company_reg_date', //成立日期
            'reg_capital', //注册资金
            'reg_capital_cur', //注册资金货币
            'profile', //公司介绍txt
        );
        foreach($baseArr as $v){
            if(empty($data['base_info'][$v])){
                return false;
            }
            unset($baseArr['profile']);
            if(strlen($data['base_info'][$v]) > 100 || strlen($data['profile']) > 1000){
                return false;
            }
        }
        //联系人
        $contactArr = array(    //buyer_attach   buyer_contact
//            'role', //购买角色
//            'email',    //邮箱
//            'hobby',    //喜好
            'address', //详细地址
            'experience',    //工作经历
            'social_relations',    //社会关系
        );
        $contactNeed = array(
            'name', //联系人姓名
            'title',    //联系人职位
            'phone',    //联系人电话
        );
        foreach($data['contact'] as $value){
            foreach($contactNeed as $v){
                if(empty($value[$v]) || strlen($value[$v]) > 50){
                    return false;
                }
            }
            foreach($contactArr as $v){
                if(!empty($value[$v]) && strlen($value[$v]) > 100){
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * 采购商客户管理，基本信息的创建
     * wangs
     */
    public function createBuyerBaseInfo($data){
        //验证数据
        $info = $this->validBuyerBaseData($data);
        if($info == false){
            return false;
        }
        //组装基本信息数据
        $arr = $this -> packageBaseData($data['base_info'],$data['created_by']);
        $res = $this->where(array('id'=>$arr['id']))->save($arr);
        if($res){
            return true;
        }
        return false;   //新建客户基本信息失败
    }
    /**
     * 组装客户基本信息创建所需数据
     * wangs
     */
    public function packageBaseData($data,$created_by){
        //会员有效期12个月--------------1年
        if(!empty($data['level_at'])){
            $level_at = $data['level_at'];
        }else{
            $level_at = date('Y-m-d');
        }
        $year_at = substr($level_at,0,4);
        $year_end = substr($level_at,0,4)+1;
        $expiry_at = str_replace($year_at,$year_end,$level_at);
        //必须数据
        $arr = array(
            'created_by'    => $created_by, //客户id
            'created_at'    => date('Y-m-d H:i:s'), //客户id
            'id'    => $data['buyer_id'], //客户id
            'name'  => $data['buyer_name'], //客户名称
            'official_phone'    => $data['official_phone'],    //公司固话
            'official_email'    => $data['official_email'],    //公司邮箱
            'official_website'  => $data['official_website'],  //公司网址
            'company_reg_date'  => $data['company_reg_date'],  //成立日期
            'reg_capital'   => $data['reg_capital'],   //注册资金
            'reg_capital_cur'   => $data['reg_capital_cur'],   //注册资金货币
            'profile'   => $data['profile'],   //公司介绍txt
            'level_at' =>  $level_at,  //定级日期
            'expiry_at' =>  $expiry_at  //有效期
        );
        //非必须数据
        $baseArr = array(
            'buyer_type', //客户类型
            'type_remarks', //客户类型备注
            'is_oilgas', //是否油气
            'employee_count', //雇员数量
        );
        foreach($data as $value){
            foreach($baseArr as $v){
                if(!empty($data[$v])){
                    $arr[$v] = $data[$v];
                }
            }
        }
        return $arr;
    }
    /**
     * 展示客户管理客户基本信息详情
     * wangs
     */
    public function showBuyerBaseInfo($data){
        if(empty($data['buyer_id']) || empty($data['created_by'])){
            return false;
        }
        $buyerArr = array(
            'id as buyer_id', //客户id
            'buyer_type', //客户类型
            'type_remarks', //客户类型备注
            'is_oilgas', //是否油气
            'buyer_code', //客户crm编码
            'name as buyer_name', //客户名称
            'profile', //公司介绍
            'employee_count', //雇员数量
            'company_reg_date', //公司注册日期
            'reg_capital', //注册资金
            'reg_capital_cur', //注册资金货币
            'area_bn', //地区
            'country_bn', //国家
//            'address as company_address', //公司地址
            'official_email', //公司邮箱
            'official_phone', //公司电话
            'official_website', //公司官网
            'buyer_level', //客户等级
            'level_at', //定级日期
            'expiry_at', //有效日期
            'created_by', //客户id
            'created_at', //客户id
            'deleted_flag', //客户id
        );
        $field = '';
        foreach($buyerArr as $v){
            $field .= ','.$v;
        }
        $field = substr($field,1);
        $cond = array(
            'id'=>$data['buyer_id'],
            'created_by'=>$data['created_by']
        );
        $info = $this->field($field)
            ->where($cond)
            ->find();
        return $info;
    }

    /**
     * 客户管理-客户信息的统计数据
     * wangs
     */
    public function showBuyerStatis($data){
        if(empty($data['buyer_id']) || empty($data['created_by'])){
            return false;
        }
//        print_r($data);die;
        $cond = array(
            'id'=>$data['buyer_id'],
            'created_by'=>$data['created_by']
        );

        $info = $this->field('credit_level,credit_type,line_of_credit,credit_available')
            ->where($cond)
            ->find();
        return $info;
    }
}
