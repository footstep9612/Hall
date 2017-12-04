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
    const STATUS_APPROVED = 'APPROVED'; //审核；
    const STATUS_REJECTED = 'REJECTED'; //无效；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */

    public function getlist($condition = [], $order = " id desc") {
        $sql = 'SELECT `erui_buyer`.`buyer`.`id`,`buyer_no`,`lang`,`buyer_type`,`erui_buyer`.`buyer`.`name`,`bn`,`profile`,`country_bn`,`province`,`city`,`official_email`,';
        $sql .= '`official_email`,`official_phone`,`official_fax`,`erui_buyer`.`buyer`.`first_name`,`erui_buyer`.`buyer`.`last_name`,`brand`,`official_website`,`logo`,`line_of_credit`,`credit_available`,`buyer_level`,`credit_level`,';
        $sql .= '`recommend_flag`,`erui_buyer`.`buyer`.`status`,`erui_buyer`.`buyer`.`remarks`,`apply_at`,`erui_buyer`.`buyer`.`created_by`,`erui_buyer`.`buyer`.`created_at`,`checked_by`,`checked_at`';
        $sql_count = 'SELECT count(`erui_buyer`.`buyer`.`id`) as num ';
        $str = ' FROM ' . $this->g_table;
        if (!empty($condition['employee_name'])) {
            $str .= " left Join `erui_buyer`.`buyer_agent` on `erui_buyer`.`buyer_agent`.`buyer_id` = `erui_buyer`.`buyer`.`id` ";
            $str .= " left Join `erui_sys`.`employee` on `erui_buyer`.`buyer_agent`.`agent_id` = `erui_sys`.`employee`.`id` ";
            $str .= " left Join `erui_buyer`.`buyer_account` on `erui_buyer`.`buyer_account`.`buyer_id` = `erui_buyer`.`buyer`.`id` ";
        }
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
            $where .= " And `erui_sys`.`employee`.`name`  like '%" . $condition['employee_name'] . "%'";
        }
        if (!empty($condition['official_phone'])) {
            $where .= ' And official_phone  = " ' . $condition['official_phone'] . '"';
        }
        if (!empty($condition['status'])) {
            $where .= ' And `erui_buyer`.`buyer_account`.status  ="' . $condition['status'] . '"';
        }
        if (!empty($condition['user_name'])) {
            $where .= ' And `erui_buyer`.`buyer_account`.`user_name`  ="' . $condition['user_name'] . '"';
        }
        if (!empty($condition['last_name'])) {
            $where .= " And `erui_buyer`.`buyer_account`.last_name like '%" . $condition['last_name'] . "%'";
        }
        if (!empty($condition['first_name'])) {
            $where .= " And `erui_buyer`.`buyer_account`.first_name like '%" . $condition['first_name'] . "%'";
        }
        if (!empty($condition['checked_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer`.checked_at  >="' . $condition['checked_at_start'] . '"';
        }
        if (!empty($condition['checked_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer`.checked_at  <="' . $condition['checked_at_end'] . '"';
        }
        if (!empty($condition['created_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  >="' . $condition['created_at_start'] . '"';
        }
        if (!empty($condition['created_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  <="' . $condition['created_at_end'] . '"';
        }
        if ($where) {
            $sql .= $where;
            $sql_count .= $where;
        }
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
        $sql = 'SELECT `id`,`buyer_no`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
        $sql .= '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
        $sql .= '`status`,`remarks`,`apply_at`,`approved_at`';
        $sql .= ' FROM ' . $this->g_table;
        $where = '';
        if (!empty($data['email'])) {
            $where .= " where email = '" . $data['email'] . "'";
        }
        if (!empty($data['id'])) {
            if ($where) {
                $where .= " and id = '" . $data['id'] . "'";
            } else {
                $where .= " where id = '" . $data['id'] . "'";
            }
        }
        if (!empty($data['buyer_no'])) {
            if ($where) {
                $where .= " and buyer_no = '" . $data['buyer_no'] . "'";
            } else {
                $where .= " where buyer_no = '" . $data['buyer_no'] . "'";
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
        if (isset($create['address'])) {
            $data['address'] = $create['address'];
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
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
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
        if ($data['buyer_id']) {
            $buyerInfo = $this->where(array("buyer.id" => $data['buyer_id']))->field('buyer.*,em.name as checked_name')
                    ->join('erui_sys.employee em on em.id=buyer.checked_by', 'left')
                    ->find();
            $sql = "SELECT  `id`,  `buyer_id`,  `attach_type`,  `attach_name`,  `attach_code`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui_buyer`.`buyer_attach` where deleted_flag ='N' and buyer_id = " . $data['buyer_id'];
            $row = $this->query($sql);
            if ($row) {
                $buyerInfo['attach'] = $row[0];
            } else {
                $buyerInfo['attach'] = new stdClass();
            }
            return $buyerInfo;
        } else {
            return false;
        }
    }

    /**
     * 采购商个人信息更新  -- 门户通用
     * @author klp
     */
    public function upUserInfo($data, $where) {
        $this->startTrans();
        try {

            $resultBuyer = $this->update_data($data, $where);
            if (!$resultBuyer) {
                $this->rollback();
                return false;
            }
            $buyerAccount = new BuyerAccountModel();
            $resultAccount = $buyerAccount->update_data($data, $where);
            if (!$resultAccount) {
                $this->rollback();
                return false;
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($create, $where) {
        $data=[];
        if (isset($create['buyer_no'])) {
            $data['buyer_no'] = $create['buyer_no'];
        }
        if (isset($create['lang'])) {
            $data['lang'] = $create['lang'];
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['address'])) {
            $data['address'] = $create['address'];
        }
        if (isset($create['bn'])) {
            $data['bn'] = $create['bn'];
        }
        if (isset($create['profile'])) {
            $data['profile'] = $create['profile'];
        }
        if (isset($create['area_bn'])) {
            $data['area_bn'] = $create['area_bn'];
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
        if (isset($create['official_website'])) {
            $data['official_website'] = $create['official_website'];
        }
        if (isset($create['buyer_level'])) {
            $data['buyer_level'] = $create['buyer_level'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['status'])) {
            switch (strtoupper($create['status'])) {
                case self::STATUS_APPROVING:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_REJECTED:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_APPROVED:
                    $data['status'] = $create['status'];
                    break;
            }
        }
        if (!empty($where)) {
            $res = $this->where(['id' => $where['buyer_id']])->save($data);
        } else {
            return false;
        }
        if($res!==false){
            return true;
        }
        return false;
    }

    /**
     * 采购商个人信息删除(针对注册--物理删除)
     * @author
     */
    public function delete_data($where){
//        return $this->where($where)->save(['deleted_flag'=>'Y']);
        return $this->where($where)->delete();
    }


    /**
     * 判断采购商是否通过审核
     * @author klp
     */
    public function isBuyerApproved($where){
        $result = $this->field('status,id')->where($where)->find();
        if($result){
            if($result['status'] == self::STATUS_APPROVED){
                $BuyerAgentModel = new BuyerAgentModel();
                $res = $BuyerAgentModel->field('agent_id')->where(['buyer_id'=>$where['id']])->find();
                return $res['agent_id'] ? $res['agent_id'] : false;
            }
            return false;
        }
        return false;
    }

    /**
     * 通过顾客id获取会员等级
     * @author klp
     */
    public function getService($info, $token) {
        $where = array();
        if (!empty($token['id'])) {
            $where['id'] = $token['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        $lang = $info['lang'] ? strtolower($info['lang']) : (browser_lang() ? browser_lang() : 'en');
        //获取会员等级
        $buyerLevel = $this->field('buyer_level')
                ->where($where)
                ->find();
//        //获取服务
//        $MemberBizService = new MemberBizServiceModel();
//        $result = $MemberBizService->getService($buyerLevel, $lang);
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

        $field = 'id,lang,buyer_type,buyer_no,name,bn,country_bn,profile,province,city,official_email,official_phone,official_fax,first_name,last_name,brand,official_website,line_of_credit,credit_available,buyer_level,credit_level,recommend_flag,status,remarks,apply_at,created_by,created_at,checked_by,checked_at';
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
                    'country_bn' => strtoupper($checkout['country_code']),
                    'official_email' => isset($checkout['official_email']) ? $checkout['official_email'] : '',
                    'official_phone' => isset($checkout['official_phone']) ? $checkout['official_phone'] : '',
                    'official_fax' => isset($checkout['official_fax']) ? $checkout['official_fax'] : '',
                    'official_website' => isset($checkout['official_website']) ? $checkout['official_website'] : '',
                    'province' => isset($checkout['province']) ? $checkout['province'] : '', //暂为办公地址
                    'remarks' => isset($checkout['remarks']) ? $checkout['remarks'] : '',
                    'recommend_flag' => isset($checkout['recommend_flag']) ? strtoupper($checkout['recommend_flag']) : 'N'
                ];
                //判断是新增还是编辑,如果有buyer_no就是编辑,反之为新增
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
        $userids = $this->_getUserids($where, $condition, 'approved_by_name', 'cb.approved_by');
        if ($userids) {
            $where['approved_by'] = ['in', $userids];
        }
        //审核人
        //   $this->_getValue($where, $condition, 'approved_by_name', 'array', 'cb.approved_by');
        //公司名称
        $this->_getValue($where, $condition, 'name', 'like', 'b.name');
        //审核状态
        $this->_getValue($where, $condition, 'status', 'string', 'b.status', 'VALID');
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

    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author klp
     */
    public function getInfo($data) {
        $where = array();
        $field = 'buyer_no,lang,name,bn,country_bn,province,city,buyer_level,address';
        try {
            if (!isset($data['buyer_no']) || empty($data['buyer_no'])) {
                if (!empty($data['id'])) {
                    $where['id'] = $data['id'];
                } else {
                    jsonReturn('', '-1001', '用户[id]不可以为空');
                }
                $buyerInfo = $this->where("id='" . $data['id'] . "'")
                        ->field($field)
                        ->find();
            } else {
                $buyerInfo = $this->where("buyer_no='" . $data['buyer_no'] . "'")
                        ->field($field)
                        ->find();
            }

            //通过顾客id查询用户信息
            $buyerAccount = new BuyerAccountModel();
            $userInfo = $buyerAccount->field('email,first_name,last_name')
                    ->where(array('id' => $data['id'], 'status' => 'VALID'))
                    ->find();

            //通过顾客id查询用户邮编
            if ($buyerInfo) {
                if ($userInfo) {
                    $buyerInfo['email'] = $userInfo['email'];
                    $buyerInfo['user_name'] = $userInfo['user_name'];
                    $buyerInfo['first_name'] = $userInfo['first_name'];
                    $buyerInfo['last_name'] = $userInfo['last_name'];
                }
                return $buyerInfo;
            }
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 门户授信 -- 获取采购商信息 NEW
     * @author klp
     * @time 2017-9-8
     */
    public function buyerCerdit($userInfo) {
        $where = array();
        $userInfo['id'] = 1;
        if (!empty($userInfo['id'])) {
            $where['b.id'] = $userInfo['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        if(isset($userInfo['buyer_no'])) {
            $where['b.buyer_no'] = $userInfo['buyer_no'];
        }
        $where['b.deleted_flag'] = 'N';

        $buyercontactModel = new BuyerContactModel();
        $tableAcon = $buyercontactModel->getTableName();
//        $BuyerreginfoModel = new BuyerreginfoModel();
//        $tableReg = $BuyerreginfoModel->getTableName();
        try {
            //基本信息-$this
            $fields = 'b.id as buyer_id, b.lang, b.address,b.buyer_no,  b.area_bn, b.name, b.buyer_type,b.bn,b.country_bn,b.profile,b.province,b.city,b.official_email,b.official_phone,b.official_fax,b.first_name,b.last_name,b.brand,b.official_website,b.line_of_credit,b.credit_available,b.buyer_level,b.credit_level,b.recommend_flag,b.status,b.remarks';
            //联系信息-BuyercontactModel
            $fields .= ',ba.first_name as con_first_name,ba.last_name as con_last_name,ba.gender,ba.title,ba.phone as con_phone,ba.email as con_email,ba.remarks as con_remarks';

            $buyerInfo = $this->alias('b')
                ->field($fields)
                ->join($tableAcon . ' as ba on ba.buyer_id=b.id ', 'left')
                ->join($tableAddr . ' as bd on bd.buyer_id=b.id', 'left')
//                ->join($tableReg . ' as br on br.buyer_id=b.id', 'left')
                ->where($where)
                ->find();
            if ($buyerInfo) {
                return $buyerInfo ? $buyerInfo : array();
            }
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

}
