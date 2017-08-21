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
    const STATUS_DRAFT = 'DRAFT'; //临时未验证；

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
            $str .= " left Join `erui2_buyer`.`buyer_account` on `erui2_buyer`.`buyer_account`.`buyer_id` = `erui2_buyer`.`buyer`.`id` ";
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
            $where .= " And `erui2_sys`.`employee`.`name`  like '%" . $condition['employee_name'] . "%'";
        }
        if (!empty($condition['official_phone'])) {
            $where .= ' And official_phone  = " ' . $condition['official_phone'] . '"';
        }
        if (!empty($condition['status'])) {
            $where .= ' And `erui2_buyer`.`buyer_account`.status  ="' . $condition['status'] . '"';
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
        $sql = 'SELECT `id`,`serial_no`,`buyer_no`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
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
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        $data['status'] = 'DRAFT';
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
                    ->join('erui2_sys.employee em on em.id=buyer.checked_by', 'left')
                    ->find();
            $sql = "SELECT  `id`,  `buyer_id`,  `attach_type`,  `attach_name`,  `attach_code`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui2_buyer`.`buyer_attach` where deleted_flag ='N' and buyer_id = " . $data['id'];
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
        if (!empty($token['id'])) {
            $where['id'] = $token['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        $lang = $info['lang'] ? strtolower($info['lang']) : (browser_lang() ? browser_lang() : 'en');
        //获取会员等级
        $buyerLevel = $this->field('buyer_level')
                ->where("customer_id='" . $where['customer_id'] . "'")
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

}
