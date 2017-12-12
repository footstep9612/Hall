<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author zyg
 */
class BuyerController extends PublicController {

    public function __init() {
        parent::__init();
    }

    /*
     * 用户列表
     * */

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['name'])) {
            $where['name'] = $data['name'];
        }
        if (!empty($data['country_bn'])) {
            $pieces = explode(",", $data['country_bn']);
            for ($i = 0; $i < count($pieces); $i++) {
                $where['country_bn'] = $where['country_bn'] . "'" . $pieces[$i] . "',";
            }
            $where['country_bn'] = rtrim($where['country_bn'], ",");
        }
        if (!empty($data['country_name'])) {

            $country_name = trim($data['country_name']);
            $country_model = new CountryModel();
            $country_bns = $country_model->getBnByName($country_name);
            if ($country_bns) {
                foreach ($country_bns as $country_bn) {
                    $where['country_bn'] = $where['country_bn'] . '\'' . $country_bn . '\',';
                }
                $where['country_bn'] = rtrim($where['country_bn'], ',');
            } else {
                $datajson['code'] = -104;
                $datajson['data'] = "";
                $datajson['message'] = '数据为空!';
            }
        }
        if (!empty($data['area_bn'])) {
            $where['area_bn'] = $data['area_bn'];
        }
        if (!empty($data['created_by'])) {
            $where['created_by'] = $data['created_by'];
        }
        if (!empty($data['agent_id'])) {
            $where['agent_id'] = $data['agent_id'];
        }
        if ($data['is_agent']=="Y") {
            $where['is_agent'] = $data['is_agent'];
            $where['agent']['user_id'] = $this->user['id'];
            $where['agent']['agent_id'] = $this->user['id'];
        }
        if (!empty($data['buyer_no'])) {
            $where['buyer_no'] = $data['buyer_no'];
        }
        if (!empty($data['buyer_code'])) {
            $where['buyer_code'] = $data['buyer_code'];
        }
        if (!empty($data['official_phone'])) {
            $where['official_phone'] = $data['official_phone'];
        }
        if (!empty($data['status'])) {
            $where['status'] = $data['status'];
        }
        if (!empty($data['employee_name'])) {
            $where['employee_name'] = $data['employee_name'];
        }
        if (!empty($data['user_name'])) {
            $where['user_name'] = $data['user_name'];
        }
        if (!empty($data['source'])) {
            $where['source'] = $data['source'];
        }
        if (!empty($data['checked_at_start'])) {
            $where['checked_at_start'] = $data['checked_at_start'];
        }
        if (!empty($data['checked_at_end'])) {
            $where['checked_at_end'] = $data['checked_at_end'];
        }
        if (!empty($data['created_at_end'])) {
            $where['created_at_end'] = $data['created_at_end'];
        }
        if (!empty($data['created_at_start'])) {
            $where['created_at_start'] = $data['created_at_start'];
        }
        if (!empty($data['credit_checked_at_start'])) {
            $where['credit_checked_at_start'] = $data['credit_checked_at_start'];
        }
        if (!empty($data['credit_checked_at_end'])) {
            $where['credit_checked_at_end'] = $data['credit_checked_at_end'];
        }
        if (!empty($data['approved_at_start'])) {
            $where['approved_at_start'] = $data['approved_at_start'];
        }
        if (!empty($data['approved_at_end'])) {
            $where['approved_at_end'] = $data['approved_at_end'];
        }
        if (!empty($data['pageSize'])) {
            $where['num'] = $data['pageSize'];
        }
        if (!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        if (!empty($data['credit_checked_name'])) {
            $where['credit_checked_name'] = $data['credit_checked_name'];
        }
        if (!empty($data['line_of_credit_min'])) {
            $where['line_of_credit_min'] = $data['line_of_credit_min'];
        }
        if (!empty($data['line_of_credit_max'])) {
            $where['line_of_credit_max'] = $data['line_of_credit_max'];
        }
        if (!empty($data['credit_status'])) {
            $where['credit_status'] = $data['credit_status'];
        }
        if (!empty($data['credit_status'])) {
            $where['credit_status'] = $data['credit_status'];
        }
        $model = new BuyerModel();
        $data = $model->getlist($where);
        $this->_setArea($data['data'], 'area');
        $this->_setCountry($data['data'], 'country');
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['count'] = $data['count'];
            $datajson['data'] = $data['data'];
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 统计各状态数量 jhw
     * */
    public function buyercountAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerModel();
        if (!empty($data['name'])) {
            $where['name'] = $data['name'];
        }
        if (!empty($data['country_bn'])) {
            $pieces = explode(",", $data['country_bn']);
            for ($i = 0; $i < count($pieces); $i++) {
                $where['country_bn'] = $where['country_bn'] . "'" . $pieces[$i] . "',";
            }
            $where['country_bn'] = rtrim($where['country_bn'], ",");
        }
        if (!empty($data['buyer_no'])) {
            $where['buyer_no'] = $data['buyer_no'];
        }
        if (!empty($data['buyer_code'])) {
            $where['buyer_code'] = $data['buyer_code'];
        }
        if (!empty($data['status'])) {
            $where['status'] = $data['status'];
        }
        if (!empty($data['employee_name'])) {
            $where['employee_name'] = $data['employee_name'];
        }
        if (!empty($data['source'])) {
            $where['source'] = $data['source'];
        }
        if (!empty($data['checked_at_start'])) {
            $where['checked_at_start'] = $data['checked_at_start'];
        }
        if (!empty($data['checked_at_end'])) {
            $where['checked_at_end'] = $data['checked_at_end'];
        }
        if (!empty($data['created_at_end'])) {
            $where['created_at_end'] = $data['created_at_end'];
        }
        if (!empty($data['created_at_start'])) {
            $where['created_at_start'] = $data['created_at_start'];
        }
        if (!empty($data['pageSize'])) {
            $where['num'] = $data['pageSize'];
        }
        if (!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        $data = $model->getBuyerCountByStatus($where);
        $arr = [];
        for ($i = 0; $i<count($data); $i++){
            $arr[$data[$i]['status']] = $data[$i]['number'];
        }
        if ($arr) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }


    /*
     * 统计各状态数量 jhw
     * */
    public function buyercheckedlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerCheckedLogModel();

        if (!empty($data['buyer_id'])) {
            $where['buyer_id'] = $data['buyer_id'];
        }else {
            $datajson['code'] = -103;
            $datajson['data'] = "";
            $datajson['message'] = '会员id缺失!';
        }
        $data = $model->getlist($where);
        if ($data) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
     * 用户详情
     * */

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerModel();
        $res = $model->info($data);
        $countryModel = new CountryModel();
        $marketAreaModel = new MarketAreaModel();
        $res_arr = [$res];
        $this->_setArea($res_arr, 'area');
        $this->_setCountry($res_arr, 'country');
        if (!empty($res_arr[0])) {
            $datajson['code'] = 1;
            $datajson['data'] = $res_arr[0];
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * Description of 获取营销区域
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setArea(&$arr, $filed) {
        if ($arr) {
            $marketarea_model = new MarketAreaModel();
            $bns = [];
            foreach ($arr as $key => $val) {
                $bns[] = trim($val[$filed . '_bn']);
            }
            $area_names = $marketarea_model->getNamesBybns($bns);
            foreach ($arr as $key => $val) {
                if (trim($val[$filed . '_bn']) && isset($area_names[trim($val[$filed . '_bn'])])) {
                    $val[$filed . '_name'] = $area_names[trim($val[$filed . '_bn'])];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setCountry(&$arr, $filed) {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val[$filed . '_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, 'zh');
            foreach ($arr as $key => $val) {
                if (trim($val[$filed . '_bn']) && isset($countrynames[trim($val[$filed . '_bn'])])) {
                    $val[$filed . '_name'] = $countrynames[trim($val[$filed . '_bn'])];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * 用户详情
     * */

    public function accountinfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerAccountModel();
        $res = $model->info($data);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['user_name'])) {
            $buyer_account_data['user_name'] = $data['user_name'];
        } else {
            jsonReturn('', -101, '用户名不可以为空!');
        }
        if (!empty($data['password'])) {
            $buyer_account_data['password_hash'] = md5(trim($data['password']));
        } else {
            jsonReturn('', -101, '密码不可以都为空!');
        }
        if (!empty($data['email'])) {
            $buyer_account_data['email'] = $data['email'];
            if (!isEmail($buyer_account_data['email'])) {
                jsonReturn('', -101, '邮箱格式不正确!');
            }
            $arr['official_email'] = $data['email'];
        } else {
            jsonReturn('', -101, '邮箱不可以都为空!');
        }
        if (!empty($data['area_bn'])) {
            $arr['area_bn'] = $data['area_bn'];
        }
        if (!empty($data['mobile'])) {
            $arr['official_phone'] = $data['mobile'];
        }
        if (!empty($data['type_remarks'])) {
            $arr['type_remarks'] = $data['type_remarks'];
        }
        if (!empty($data['employee_count'])) {
            $arr['employee_count'] = $data['employee_count'];
        }
        if (!empty($data['reg_capital'])) {
            $arr['reg_capital'] = $data['reg_capital'];
        }
        if (!empty($data['reg_capital_cur'])) {
            $arr['reg_capital_cur'] = $data['reg_capital_cur'];
        }
        if (!empty($data['expiry_at'])) {
            $arr['expiry_at'] = $data['expiry_at'];
        }
        if (!empty($data['show_name'])) {
            $buyer_account_data['show_name'] = $data['show_name'];
        }
        $buyer_account_data['created_at'] = $this->user['id'];
        //附件
        if (!empty($data['attach_url'])) {
            $buyer_attach_data['attach_url'] = $data['attach_url'];
        }
        if (isset($buyer_attach_data)) {
            $buyer_attach_data['created_by'] = $this->user['id'];
            $buyer_attach_data['created_at'] = date("Y-m-d H:i:s");
            $buyer_attach_data['attach_name'] = $data['name'] . '营业执照';
        }
        $buyer_contact_data['mobile'] = $data['mobile'];
        $buyer_contact_data['email'] = $data['email'];
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -101, '名称不能为空!');
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['province'])) {
            $arr['province'] = $data['province'];
        }
        if (!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        } else {
            jsonReturn('', -101, '国家名不可为空!');
        }
        if (!empty($data['buyer_no'])) {
            $arr['buyer_no'] = $data['buyer_no'];
        }
        if (!empty($data['buyer_code'])) {
            $arr['buyer_code'] = $data['buyer_code'];    //新增CRM编码，张玉良 2017-9-27
        } else {
            jsonReturn('', -101, 'crm编码为必填项!');
        }
        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();
        $checkcrm = $model->where("buyer_code='" . $arr['buyer_code'] . "' AND deleted_flag='N'")->find();
        if ($checkcrm) {
            jsonReturn('', -103, 'crm编码已经存在');
        }
        if (!empty($data['address'])) {
            $arr['address'] = $data['address'];
        }
        if (!empty($data['biz_scope'])) {
            $arr['biz_scope'] = $data['biz_scope'];
        }
        if (!empty($data['intent_product'])) {
            $arr['intent_product'] = $data['intent_product'];
        }
        if (!empty($data['purchase_amount'])) {
            $arr['purchase_amount'] = $data['purchase_amount'];
        }
        $arr['created_by'] = $this->user['id'];
        $login_email['email'] = $data['email'];
        $login_email['user_name'] = $data['email'];
        $check_email = $buyer_account_model->Exist($login_email, 'or');

        if ($check_email) {
            jsonReturn('', -101, '邮箱已经存在!');
        }
        $login_uname['email'] = $data['user_name'];
        $login_uname['user_name'] = $data['user_name'];
        $check_uname = $buyer_account_model->Exist($login_uname, 'or');
        if ($check_uname) {
            jsonReturn('', -102, '用户名已经存在!');
        }
        //验证公司名称是否存在
        $checkcompany = $model->where("name='" . $data['name'] . "' AND deleted_flag='N'")->find();
        if ($checkcompany) {
            jsonReturn('', -103, '公司名称已经存在');
        }
        // 生成用户编码
        $condition['page'] = 0;
        $condition['countPerPage'] = 1;

        $data_t_buyer = $model->getlist($condition); //($this->put_data);
        //var_dump($data_t_buyer);die;
        if ($data_t_buyer && substr($data_t_buyer['data'][0]['buyer_no'], 1, 8) == date("Ymd")) {
            $no = substr($data_t_buyer['data'][0]['buyer_no'], 9, 6);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = "C" . date("Ymd") . substr($new_num, 1, 6); //即截取掉最前面的“1”
        $arr['buyer_no'] = $real_num;
        $arr['created_by'] = $this->user['id'];
        $id = $model->create_data($arr);
        if ($id) {
            $buyer_account_data['buyer_id'] = $id;
            if (!empty($buyer_attach_data)) {
                $buyer_attach_data['buyer_id'] = $id;
            }
            $buyer_contact_data['buyer_id'] = $id;
            //添加联系人
            $buyer_contact_model = new BuyercontactModel();
            $buyer_contact_model->create_data($buyer_contact_data);
            //添加附件
            $buyer_attach_model = new BuyerattachModel();
            $buyer_attach_model->create_data($buyer_attach_data);
            //采购商帐号表
            $buyer_account_model->create_data($buyer_account_data);
            //获取营销区域信息 -- link 2017-10-31
            //$mareaModel = new MarketAreaModel();
            //$areaInfo = $mareaModel->getInfoByBn($arr['area_bn']);
            //获取营销国家信息 -- link 2017-10-31
            //$countryModel = new CountryModel();
            //$countryInfo = $countryModel->getInforByBn($arr['country_bn']);
            //获取市场经办人信息 -- link 2017-10-31
            $userInfo = new UserModel();
            $countryModel = new CountryModel();
            $marketAreaModel = new MarketAreaModel();
            $agentInfo = $userInfo->info($data['agent_id'], ['deleted_flag' => 'N', 'status' => 'NORMAL'], 'name');

            //询单所在国家
            $rs1 = $countryModel->field('name')->where(['bn' => $arr['country_bn'], 'lang' => 'zh', 'deleted_flag' => 'N'])->find();
            $datajson['country_name'] = $rs1['name'];

            //询单所在区域
            $rs2 = $marketAreaModel->field('name')->where(['bn' => $arr['area_bn'], 'lang' => 'zh', 'deleted_flag' => 'N'])->find();
            $datajson['area_name'] = $rs2['name'];

            $datajson['code'] = 1;
            $datajson['id'] = $id;
            $datajson['buyer_code'] = $data['buyer_code'];
            $datajson['buyer_no'] = $arr['buyer_no'];
            $datajson['name'] = $arr['name'];    //-- link 2017-10-31
            $datajson['area'] = $arr['area_bn']; //$areaInfo;    //-- link 2017-10-31
            $datajson['agent_id'] = $data['agent_id'];
            $datajson['agent'] = $agentInfo ? $agentInfo['name'] : '';    //-- link 2017-10-31
            $datajson['country'] = $arr['country_bn']; //$countryInfo;    //-- link 2017-10-31
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function agentlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['buyer_id'])) {
            $array['buyer_id'] = $data['buyer_id'];
        }
        if (!empty($data['agent_id'])) {
            $array['agent_id'] = $data['agent_id'];
        }
        $model = new BuyerAgentModel();
        $res = $model->getlist($array);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateagentAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $array['id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "会员id不能为空"));
        }
        if (!empty($data['user_ids'])) {
            $array['user_ids'] = $data['user_ids'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "负责人id不能为空"));
        }
        $array['created_by'] = $this->user['id'];
        $model = new BuyerAgentModel();
        $inquiry_model = new InquiryModel();
        $user_arr = explode(',', $array['user_ids']);
        if ($user_arr[0]) {
            $condition['buyer_id'] = $array['id'];
            $condition['agent_id'] = $user_arr[0];
            $inquiry_model->setBuyerAgentInfo($condition);
        }
        $res = $model->create_data($array);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $where['id'] = $data['id'];
            $where_account['buyer_id'] = $data['id'];
            $where_attach['buyer_id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['province'])) {
            $arr['province'] = $data['province'];
        }
        if (!empty($data['buyer_code'])) {
            $arr['buyer_code'] = $data['buyer_code'];   //新增CRM编码，张玉良 2017-9-27
        }
        if (!empty($data['show_name'])) {
            $arr['show_name'] = $data['show_name'];   //新增CRM编码，张玉良 2017-9-27
        }
        if (!empty($data['country_bn'])) {
            $account['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['biz_scope'])) {
            $arr['biz_scope'] = $data['biz_scope'];
        }
        if (!empty($data['intent_product'])) {
            $arr['intent_product'] = $data['intent_product'];
        }
        if (!empty($data['purchase_amount'])) {
            $arr['purchase_amount'] = $data['purchase_amount'];
        }
        $buyer_account_model = new BuyerAccountModel();
        if (!empty($data['email'])) {
            $arr['official_email'] = $data['email'];
            $account['email'] = $data['email'];
            $buyer_id = $buyer_account_model->where(['email' => $data['email']])->getField('buyer_id');
            if ($buyer_id > 0 && $buyer_id != $data['id']) {
                $this->jsonReturn(array("code" => "-101", "message" => "该邮箱已经被其他账号使用"));
            }
        }
        if (!empty($data['mobile'])) {
            $arr['official_phone'] = $data['mobile'];
        }
        if (!empty($data['buyer_level'])) {
            $arr['buyer_level'] = $data['buyer_level'];
            $arr['level_at'] =date("Y-m-d H:i:s");
        }
        if (!empty($data['type_remarks'])) {
            $arr['type_remarks'] = $data['type_remarks'];
        }
        if (!empty($data['employee_count'])) {
            $arr['employee_count'] = $data['employee_count'];
        }
        if (!empty($data['reg_capital'])) {
            $arr['reg_capital'] = $data['reg_capital'];
        }
        if (!empty($data['reg_capital_cur'])) {
            $arr['reg_capital_cur'] = $data['reg_capital_cur'];
        }
        if (!empty($data['expiry_at'])) {
            $arr['expiry_at'] = $data['expiry_at'];
        }
        if (!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if (!empty($data['area_bn'])) {
            $arr['area_bn'] = $data['area_bn'];
        }
        if (!empty($data['status'])) {
            $arr['status'] = $data['status'];
            if ($data['status'] == 'APPROVED' || $data['status'] == 'REJECTED') {
                $arr['checked_by'] = $this->user['id'];
                $arr['checked_at'] = Date("Y-m-d H:i:s");
            }
        }
        if (!empty($data['address'])) {
            $arr['address'] = $data['address'];
        }
        $model = new BuyerModel();
        $res = $model->update_data($arr, $where);


        if (!empty($data['password'])) {
            $account['password_hash'] = $data['password'];
            // $buyer_account_model->update_data($arr_account, $where_account);
        }
        $buyer_attach_model = new BuyerattachModel();
        if (!empty($data['attach_url'])) {
            $where_attach['attach_url'] = $data['attach_url'];
            $buyer_attach_model->update_data($where_attach);
        }
        //$model = new UserModel();
        if (!empty($account)) {
            $buyer_account_model->update_data($account, $where_account);
        }
        if (!empty($data['status'])&&$res!==false) {
            if ($data['status'] == 'APPROVED' || $data['status'] == 'REJECTED') {
                $info =  $buyer_account_model->info($where_account);
                $info_buyer =  $model->info($where);

                if($info['email']){
                    if ($data['status'] == 'APPROVED') {
                        //审核通过邮件
                        if($info_buyer['lang']){
                            $body = $this->getView()->render('buyer/approved_'.$info_buyer['lang'].'.html');
                            send_Mail($info['email'], 'Erui.com', $body, $arr['name']);
                        }
                    }
                    if ($data['status'] == 'REJECTED') {
                        //驳回邮件
                        if($info_buyer['lang']){
                            $body = $this->getView()->render('buyer/rejected_'.$info_buyer['lang'].'.html');
                            send_Mail($info['email'], 'Erui.com', $body, $arr['name']);
                        }
                    }
                }
            }
        }
        if ($res !== false) {
            $datajson['code'] = 1;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function getRoleAction() {
        if ($this->user['id']) {
            $role_user = new RoleUserModel();
            $where['user_id'] = $this->user['id'];
            $data = $role_user->getRoleslist($where);
            $datajson = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $data
            );
            jsonReturn($datajson);
        } else {
            $datajson = array(
                'code' => -104,
                'message' => '用户验证失败',
            );
        }
    }
    /**
     * 客户档案信息管理，创建客户档案-->基本信息
     * wangs
     */
    public function createBuyerInfoAction() {
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $res = $model->createBuyerBaseInfo($data);  //创建基本信息
        if($res == false){
            $valid = array(
                'code'=>0,
                'message'=>'请输入规范数据',
            );
            $this -> jsonReturn($valid);
        }
        //创建联系人信息
        $model = new BuyercontactModel();
        $contactRes = $model->createBuyerContact($data['contact'],$data['base_info']['buyer_id'],$created_by);
        if($contactRes == false){
            $valid = array(
                'code'=>1,
                'message'=>'基本信息，公司介绍，创建成功,联系人创建失败',
            );
            $this -> jsonReturn($valid);
        }
        //创建财务报表
        if(!empty($data['base_info']['attach_name']) && !empty($data['base_info']['attach_url'])){
            //创建采购商客户证书-财务表附件
            $financeRes = $this->_createBuyerFinanceTable($data);
            if($financeRes == false){
                $valid = array(
                    'code'=>1,
                    'message'=>'基本信息，公司介绍，联系人,创建成功，财务报表创建失败',
                );
            }else{
                $valid = array(
                    'code'=>1,
                    'message'=>'基本信息，公司介绍，联系人，财务报表,创建成功',
                );
            }
            $this -> jsonReturn($valid);
        }
        $valid = array(
            'code'=>1,
            'message'=>'基本信息，公司介绍，联系人,创建成功,财务报表为空',
        );
        $this -> jsonReturn($valid);
    }
    //添加财务报表
    private function _createBuyerFinanceTable($data){
        //创建采购商客户证书-财务表附件
        $attach_name = $data['base_info']['attach_name'];
        $attach_url = $data['base_info']['attach_url'];
        $buyer_id = $data['base_info']['buyer_id'];
        $created_by = $data['created_by'];
        $model = new BuyerattachModel();
        $financeRes = $model->createBuyerFinanceTable($attach_name,$attach_url,$buyer_id,$created_by);
        return $financeRes;
    }
    /**
     * 客户管理：客户基本信息展示详情
     * wangs
     */
    public function showBuyerInfoAction(){
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $buerInfo = $model->showBuyerBaseInfo($data);
        if(empty($buerInfo) || $buerInfo == false){
            $dataJson = array(
                'code'=>0,
                'message'=>'该客户暂无数据请添加',
            );
            $this->jsonReturn($dataJson);
        }
        //获取客户账号
        $account = new BuyerAccountModel();
        $accountInfo = $account->getBuyerAccount($data['buyer_id']);
        $buerInfo['buyer_account'] = $accountInfo['email'];
        //获取服务经理经办人，调用市场经办人方法
        $agent = new BuyerAgentModel();
        $agentInfo = $agent->buyerMarketAgent($data);
        $buerInfo['market_agent_name'] = $agentInfo['info'][0]['name']; //没有数据则为空
        $buerInfo['market_agent_mobile'] = $agentInfo['info'][0]['mobile'];
        //获取财务报表
        $attach = new BuyerattachModel();
        $finance = $attach->showBuyerExistAttach($data['buyer_id'],$data['created_by']);
        if(!empty($finance)){
            $buerInfo['attach_name'] = $finance['attach_name'];
            $buerInfo['attach_url'] = $finance['attach_url'];
        }
        $arr['base_info'] = $buerInfo;
        //获取客户联系人
        $contact = new BuyercontactModel();
        $contactInfo = $contact->showBuyerExistContact($data['buyer_id'],$data['created_by']);
        if(!empty($contactInfo)){
            $arr['contact'] = $contactInfo;
        }
        $dataJson = array(
            'code'=>1,
            'message'=>'返回数据',
            'data'=>$arr
        );
        $this->jsonReturn($dataJson);
    }
    /**
     * 客户管理-附件下载
     * wangs
     */
    public function attachDownloadAction(){
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerattachModel();
        $attach = $model->attachDownload($data);
        if($attach == false){
            $dataJson = array(
                'code'=>0,
                'message'=>'请输入正确信息'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'数据下载',
                'data'=>$attach
            );
        }
        $this->jsonReturn($dataJson);
    }
    /**
     * 客户管理模块
     * 统计--客户拜访记录统计列表
     * wangs
     */
    public function buyerVisitStatisListAction(){
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerVisitModel();
        $ststisList = $model->buyerVisitStatisList($data);
        if($ststisList === false){
            $dataJson = array(
                'code'=>0,
                'message'=>'请求参数错误'
            );
            $this->jsonReturn($dataJson);
        }
        $dataJson = array(
            'code'=>1,
            'message'=>'统计模块，访问记录统计数据',
            'data'=>$ststisList
        );
        $this->jsonReturn($dataJson);
    }
    /**
     * 客户管理-客户档案--统计
     * wangs
     */
    public function showBuyerStatisAction(){
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        //客户信用评价
        $model = new BuyerModel();
        $ststisInfo = $model->showBuyerStatis($data);
        if($ststisInfo === false){
            $dataJson = array(
                'code'=>0,
                'message'=>'请求缺少规定参数'
            );
            $this->jsonReturn($dataJson);
        }
        //客户信用评价
        $visit = new BuyerVisitModel();
        $visitInfo = $visit->singleVisitInfo($data['buyer_id']);
        //客户需求反馈
        $reply = new BuyerVisitReplyModel();
        $replyInfo = $reply->singleVisitReplyInfo($data['buyer_id'],$data['created_by']);
        //整合数据
        $arr['credit'] = $ststisInfo;
        $arr['visit'] = $visitInfo;
        $arr['reply'] = $replyInfo;
        $dataJson = array(
            'code'=>1,
            'message'=>'返回数据',
            'data'=>$arr
        );
        $this->jsonReturn($dataJson);
    }
}
