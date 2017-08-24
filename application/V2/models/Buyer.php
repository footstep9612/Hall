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
    protected $dbName = 'erui2_buyer'; //数据库名称
    protected $g_table = 'erui2_buyer.buyer';

//    protected $autoCheckFields = false;
    public function __construct() {
        parent::__construct();
    }

    //状态

    const STATUS_APPROVING = 'APPROVING'; //待报审；
    const STATUS_APPROVED = 'APPROVED'; //审核；
    const STATUS_REJECTED = 'REJECTED'; //无效；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */

    public function getlist($condition = [], $order = " id desc") {
        $sql = 'SELECT `erui2_buyer`.`buyer`.`id`,`serial_no`,`buyer_no`,`lang`,`buyer_type`,`erui2_buyer`.`buyer`.`name`,`bn`,`profile`,`country_code`,`country_bn`,`province`,`city`,`official_email`,';
        $sql .= '`official_email`,`official_phone`,`official_fax`,`erui2_buyer`.`buyer`.`first_name`,`erui2_buyer`.`buyer`.`last_name`,`brand`,`official_website`,`logo`,`sec_ex_listed_on`,`line_of_credit`,`credit_available`,`credit_cur_bn`,`buyer_level`,`credit_level`,';
        $sql .= '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`recommend_flag`,`erui2_buyer`.`buyer`.`status`,`erui2_buyer`.`buyer`.`remarks`,`apply_at`,`erui2_buyer`.`buyer`.`created_by`,`erui2_buyer`.`buyer`.`created_at`,`checked_by`,`checked_at`';
        $sql_count = 'SELECT count(`erui2_buyer`.`buyer`.`id`) as num ';
        $str = ' FROM ' . $this->g_table;
        if (!empty($condition['employee_name'])) {
            $str .= " left Join `erui2_buyer`.`buyer_agent` on `erui2_buyer`.`buyer_agent`.`buyer_id` = `erui2_buyer`.`buyer`.`id` ";
            $str .= " left Join `erui2_sys`.`employee` on `erui2_buyer`.`buyer_agent`.`agent_id` = `erui2_sys`.`employee`.`id` ";
        }
        $str .= " left Join `erui2_buyer`.`buyer_account` on `erui2_buyer`.`buyer_account`.`buyer_id` = `erui2_buyer`.`buyer`.`id` ";
        $sql .= $str;
        $sql_count .= $str;
        $where = " WHERE 1 = 1";
        if (!empty($condition['country_bn'])) {
            $where .= ' And country_bn ="' . $condition['country_bn'] . '"';
        }
        if (!empty($condition['name'])) {
            $where .= " And name like '%" . $condition['name'] . "%'";
        }
        if (!empty($condition['buyer_no'])) {
            $where .= ' And buyer_no  ="' . $condition['buyer_no'] . '"';
        }
        if (!empty($condition['employee_name'])) {
            $where .= " And `erui2_sys`.`employee`.`name`  like '%" . $condition['employee_name'] . "%'";
        }
        if (!empty($condition['official_phone'])) {
            $where .= ' And official_phone  = " ' . $condition['official_phone'] . '"';
        }
        if (!empty($condition['status'])) {
            $where .= ' And `erui2_buyer`.`buyer`.status  ="' . $condition['status'] . '"';
        }
        if (!empty($condition['user_name'])) {
            $where .= ' And `erui2_buyer`.`buyer_account`.`user_name`  ="' . $condition['user_name'] . '"';
        }
        if (!empty($condition['last_name'])) {
            $where .= " And `erui2_buyer`.`buyer_account`.last_name like '%" . $condition['last_name'] . "%'";
        }
        if (!empty($condition['first_name'])) {
            $where .= " And `erui2_buyer`.`buyer_account`.first_name like '%" . $condition['first_name'] . "%'";
        }
        if (!empty($condition['checked_at_start'])) {
            $where .= ' And `erui2_buyer`.`buyer`.checked_at  >="' . $condition['checked_at_start'] . '"';
        }
        if (!empty($condition['checked_at_end'])) {
            $where .= ' And `erui2_buyer`.`buyer`.checked_at  <="' . $condition['checked_at_end'] . '"';
        }
        if (!empty($condition['created_at_start'])) {
            $where .= ' And `erui2_buyer`.`buyer`.created_at  >="' . $condition['created_at_start'] . '"';
        }
        if (!empty($condition['created_at_end'])) {
            $where .= ' And `erui2_buyer`.`buyer`.created_at  <="' . $condition['created_at_end'] . '"';
        }
        if ($where) {
            $sql .= $where;
            $sql_count .= $where;
        }
        $sql .= ' Group By `erui2_buyer`.`buyer`.`id`';
        $sql .= ' Order By ' . $order;
        if ($condition['num']) {
            $sql .= ' LIMIT ' . $condition['page'] . ',' . $condition['num'];
        }
        $count = $this->query($sql_count);
        $res['count'] = $count[0]['num'];
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
        $sql = 'SELECT `id`,`serial_no`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
        $sql .= '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
        $sql .= '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`status`,`remarks`,`apply_at`,`approved_at`';
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
            $sql .= $where;
        }
        $row = $this->query($sql);
        return empty($row) ? false : $row;
    }

    public function create_data($create = []) {
        if (isset($create['buyer_no'])) {
            $data['buyer_no'] = $create['buyer_no'];
        }
        if (isset($create['serial_no'])) {
            $data['serial_no'] = $create['serial_no'];
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
        if (isset($create['country_code'])) {
            $data['country_code'] = $create['country_code'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
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
        if (isset($create['first_name'])) {
            $data['first_name'] = $create['first_name'];
        }
        if (isset($create['last_name'])) {
            $data['last_name'] = $create['last_name'];
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
        $data['status'] = 'APPROVING';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['checked_at'] = date('Y-m-d H:i:s');
        try {
            $datajson = $this->create($data);
            $res = $this->add($datajson);
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
                    ->join('erui2_sys.employee em on em.id=buyer.checked_by', 'left')
                    ->find();
            $sql = "SELECT  `id`,  `buyer_id`,  `attach_type`,  `attach_name`,  `attach_code`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui2_buyer`.`buyer_attach` where deleted_flag ='N' and buyer_id = " . $data['id'];
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

        if (isset($create['buyer_no'])) {
            $data['buyer_no'] = $create['buyer_no'];
        }
        if (isset($create['serial_no'])) {
            $data['serial_no'] = $create['serial_no'];
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
        if (isset($create['country_code'])) {
            $data['country_code'] = $create['country_code'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
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
        if (isset($create['first_name'])) {
            $data['first_name'] = $create['first_name'];
        }
        if (isset($create['last_name'])) {
            $data['last_name'] = $create['last_name'];
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
        if (isset($create['status'])) {
            switch ($create['status']) {
                case self::STATUS_APPROVED:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_APPROVING:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_REJECTED:
                    $data['status'] = $create['status'];
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
     * 获取采购商信息
     * @author klp
     */
    public function buyerInfo($user) {
//        $userInfo = getLoinInfo();
        $where = array();
        if (!empty($user['id'])) {
            $where['id'] = $user['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }

        $field = 'id,lang,serial_no,buyer_type,buyer_no,name,bn,country_code,country_bn,profile,province,city,official_email,official_phone,official_fax,first_name,last_name,brand,official_website,sec_ex_listed_on,line_of_credit,credit_available,credit_cur_bn,buyer_level,credit_level,recommend_flag,status,remarks,apply_at,created_by,created_at,checked_by,checked_at';
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
                    $data['serial_no'] = $real_num;
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
        $userids = $this->_getUserids($where, $condition, 'approved_by_name', 'cb.approved_by');
        if ($userids) {
            $where['approved_by'] = ['in', $userids];
        }
        //审核人
        //   $this->_getValue($where, $condition, 'approved_by_name', 'array', 'cb.approved_by');
        //公司名称
        $this->_getValue($where, $condition, 'name', 'like', 'b.name');
        //审核状态
        $this->_getValue($where, $condition, 'status', 'string', 'b.status', 'APPROVING');
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
                        ->join($creditLogtable . ' as cl ON b.id = cl.id', 'LEFT')
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

}
