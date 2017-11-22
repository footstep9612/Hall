<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LoginController
 *
 * @author  jhw
 */
class LoginController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
    }

    /*
     * 用户登录
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */

    public function loginAction() {
        $data = $this->getPut();
        if (!empty($data['password'])) {
            $arr['password'] = $data['password'];
        } else {
            echo json_encode(array("code" => "-101", "message" => "密码不可以都为空"));
            exit();
        }
        if (!empty($data['user_name'])) {
            $data['user_name']=trim($data['user_name']);
            if (isEmail($data['user_name'])) {
                $arr['email'] = $data['user_name'];
            } else {
                $arr['user_name'] = $data['user_name'];
            }
        } else {
            echo json_encode(array("code" => "-101", "message" => "帐号不可以都为空"));
            exit();
        }
        $model = new BuyerAccountModel();
        $info = $model->login($arr);
        if ($info) {
            $buyer_model = new BuyerModel();
            $buyer_info = $buyer_model->info(['id' => $info['buyer_id']] );
            $jwtclient = new JWTClient();
            $jwt['id'] = $info['id'];
            $jwt['buyer_no'] = $buyer_info['buyer_no'];
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['user_name'] = $info['user_name'];
            $datajson['buyer_no'] = $buyer_info['buyer_no'];
            $datajson['email'] = $info['email'];
            $datajson['user_name'] = $info['user_name'];
            $datajson['token'] = $jwtclient->encode($jwt); //加密
            redisSet('shopmall_user_info_' . $info['id'], json_encode($info), 18000);
            echo json_encode(array("code" => "1", "data" => $datajson, "message" => "登陆成功"));
            exit();
        } else {
            $datajson = [];
            echo json_encode(array("code" => "-104", "data" => $datajson, "message" => "Logon failure"));
        }
    }

    /**
     * 用户注册
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */
    public function registerAction() {
        $data = $this->getPut();
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
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -101, '公司名称不能为空!');
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['phone'])) {
            $buyer_account_data['phone'] = $data['phone'];
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
        if (!empty($data['first_name'])) {
            $buyer_account_data['first_name'] = $data['first_name'];
            $arr['first_name'] = $data['first_name'];
        } else {
            jsonReturn('', -101, '名字不能为空!');
        }
        if (!empty($data['last_name'])) {
            $buyer_account_data['last_name'] = $data['last_name'];
            $arr['last_name'] = $data['last_name'];

        }
        if (!empty($data['mobile'])) {
            $buyer_account_data['mobile'] = $data['mobile'];
            $arr['official_phone'] = $data['mobile'];
        }
        if (!empty($data['country'])) {
            $arr['country_bn'] = $data['country'];
        } else {
            jsonReturn('', -101, '国家不能为空!');
        }
        if (!empty($data['zipcode'])) {
            $buyer_address_data['zipcode'] = $data['zipcode'];
        }
        if (!empty($data['address'])) {
            $buyer_address_data['address'] = $data['address'];
        }
        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();
        $login_arr['email'] = $data['email'];
        $login_arr['user_name'] = $data['user_name'];
        $check = $buyer_account_model->Exist($login_arr);
        if ($check) {
            jsonReturn('', -101, 'The company email or user name already exists.');
        }
        //验证公司名称是否存在
        $checkcompany = $model->where("name='" . $data['name'] . "'")->find();
        if ($checkcompany) {
            jsonReturn('', -101, 'The company name already exists.');
        }

        // 生成用户编码
        $condition['page'] = 0;
        $condition['countPerPage'] = 1;
        $data_t_buyer = $model->getlist($condition); //($this->put_data);
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
        if (empty($arr['serial_no'])) {
            $arr['serial_no'] = $arr['buyer_no'];
        }
        $id = $model->create_data($arr);
        if ($id) {
            if (!empty($buyer_address_data)) {
                $buyer_address_data['buyer_id'] = $id;
            }
            $buyer_account_data['buyer_id'] = $id;
            $buyer_account_data['status'] = 'DRAFT';
            $account_id = $buyer_account_model->create_data($buyer_account_data);
            if (!empty($buyer_address_data)) {
                $buyer_address_model = new BuyerAddressModel();
                $buyer_address_model->create_data($buyer_address_data);
            }
            //生成邮件验证码
            $data_key['key'] = md5(uniqid());
            $data_key['email'] = $data['email'];
            $data_key['name'] = $data['first_name'].$data['last_name'];
            redisHashSet('login_reg_key', $data_key['key'], $account_id);
            $config_obj = Yaf_Registry::get("config");
            $config_shop = $config_obj->shop->toArray();
            $email_arr['url'] = $config_shop['url'];
            $email_arr['key'] = $data_key['key'];
            $body = $this->getView()->render('login/email.html', $email_arr);
            send_Mail($data_key['email'], 'Activation email for your registration on ERUI platform', $body, $data['first_name'].$data['last_name']);
            jsonReturn($data_key, 1, '提交成功');
        } else {
            jsonReturn('', -105, 'Failed to register your account.');
        }
    }

    // 发送邮件
    public function sendEmailAction() {

        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['email'])) {
            $arr['email'] = $data['email'];
        } else {
            jsonReturn('', -101, '邮箱不可以为空!');
        }
        if (!empty($data['key'])) {
            $arr['key'] = $data['key'];
        } else {
            jsonReturn('', -101, '邮箱不可以为空!');
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -101, '收件人姓名不可以为空!');
        }
        $config_obj = Yaf_Registry::get("config");
        $config_shop = $config_obj->shop->toArray();
        $email_arr['url'] = $config_shop['url'];
        $email_arr['key'] = $arr['key'];
        $email_arr['name'] = $arr['name'];
        $body = $this->getView()->render('login/email.html', $email_arr);
        $res = send_Mail($arr['email'], 'Activation email for your registration on ERUI platform', $body, $arr['name']);
        if ($res['code'] == 1) {
            jsonReturn('', 1, '发送成功');
        } else {
            jsonReturn('', -104, $res['msg']);
        }
    }

    // 验证邮件
    public function checkEmailAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['key'])) {
            jsonReturn('', -101, '邮箱不可以为空!');
        }
        if (redisHashExist('login_reg_key', $data['key'])) {
            $arr['id'] = redisHashGet('login_reg_key', $data['key']);
        } else {
            jsonReturn('', -104, 'key不存在');
        }
        $buyer_account_model = new BuyerAccountModel();
        $list = $buyer_account_model->Exist($arr);
        $buyer_data['status'] = 'VALID';
        $res = $buyer_account_model->update_data($buyer_data, $arr);
        if ($res) {
            redisHashDel('login_reg_key', $data['key']);
            jsonReturn('', 1, '验证成功');
        } else {
            jsonReturn('', -104, '验证失败');
        }
    }

    //获取部门信息
    public function groupListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['parent_id'])) {
            $where['parent_id'] = $data['parent_id'];
        }
        if (!empty($data['name'])) {
            $where['name'] = $data['name'];
        }
        if (!empty($data['page'])) {
            $limit['page'] = $data['page'];
        }
        if (!empty($data['countPerPage'])) {
            $limit['num'] = $data['countPerPage'];
        }
        $model_group = new GroupModel();
        $data = $model_group->getlist($where, $limit); //($this->put_data);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -101;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }
        echo json_encode($datajson);
        exit();
    }

    function retrievalEmailAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['email'])) {
            $buyer_account_data['email'] = $data['email'];
            if (!isEmail($buyer_account_data['email'])) {
                jsonReturn('', -101, 'Incorrect email format');
            }
        } else {
            jsonReturn('', -101, 'Email is required');
        }
        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();
        $login_arr['email'] = $data['email'];
        $check = $buyer_account_model->Exist($login_arr, 'and');
        if ($check) {
            //生成邮件验证码
            $data_key['key'] = md5(uniqid());
            $data_key['email'] = $login_arr['email'];
            $data_key['name'] = $check[0]['first_name'].$check[0]['last_name'];
            redisHashSet('rest_password_key', $data_key['key'], $check[0]['id']);
            $config_obj = Yaf_Registry::get("config");
            $config_shop = $config_obj->shop->toArray();
            $email_arr['url'] = $config_shop['url'];
            $email_arr['key'] = $data_key['key'];
            $email_arr['first_name'] = $check[0]['first_name'].$check[0]['last_name'];
            $body = $this->getView()->render('login/forgetemail.html', $email_arr);
            send_Mail($data_key['email'], 'Password retrieval on ERUI platform', $body, $check[0]['first_name'].$check[0]['last_name']);
            jsonReturn($data_key, 1, '发送成功');
        } else {
            jsonReturn('', -103, ' The email does not exist.');
        }
    }

    function checkKeyAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['key'])) {
            jsonReturn('', -101, 'key不可以为空!');
        }
        if (redisHashExist('rest_password_key', $data['key'])) {
            jsonReturn('', 1, redisHashGet('rest_password_key', $data['key']));
        } else {
            jsonReturn('', -101, '未获取到key!');
        }
    }

    function setPasswordAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['password'])) {
            jsonReturn('', -101, 'Password is required!');
        } else {
            $user_arr['password_hash'] = $data['password'];
        }
        if (empty($data['key'])) {
            jsonReturn('', -101, 'Key is required');
        }
        $id = redisHashGet('rest_password_key', $data['key']);
        if ($id) {
            $buyer_account_model = new BuyerAccountModel();
            $info = $buyer_account_model ->info(['id' => $id]);
            if($info&&$info['status'] == 'DRAFT'){
                $user_arr['status'] = "VALID";
            }
            $check = $buyer_account_model->update_data($user_arr, ['id' => $id]);
            redisHashDel('rest_password_key', $data['key']);
            jsonReturn('', 1, '操作成功');
        } else {
            jsonReturn('', -101, 'Key is required');
        }
    }

    /**
     * 用户注册--2.2
     * @author
     */
    public function registerdoAction() {
        $data = $this->getPut();
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
        } else {
            jsonReturn('', -101, '邮箱不可以都为空!');
        }
        if (!empty($data['mobile'])) {
            $buyer_account_data['mobile'] = $data['mobile'];
        }
        if (!empty($data['country'])) {
            $arr['country_bn'] = $data['country'];
            $buyer_address_data['country_bn'] = $data['country'];
        } else {
            jsonReturn('', -101, '国家不能为空!');
        }
        if (!empty($data['first_name'])) {
            $buyer_account_data['first_name'] = $data['first_name'];
            $arr['first_name'] = $data['first_name'];
        } else {
            jsonReturn('', -101, '名字不能为空!');
        }
        if (!empty($data['last_name'])) {
            $buyer_account_data['last_name'] = $data['last_name'];
            $arr['last_name'] = $data['last_name'];
        }

        $model = new BuyerModel();
        //对于修改个人认为逻辑不符
        if(isset($data['buyer_id']) && !empty($data['buyer_id'])) {
            $where['buyer_id'] = $data['buyer_id'];
            $result = $model->upUserInfo($data, $where);
            if ($result) {
                jsonReturn('', 1, '保存成功!');
            } else {
                jsonReturn('', '-1002', '保存失败!');
            }
        }

        $buyer_account_model = new BuyerAccountModel();
        $login_arr['email'] = $data['email'];
        $login_arr['user_name'] = $data['user_name'];
        $check = $buyer_account_model->Exist($login_arr);
        if ($check) {
            jsonReturn('', -101, 'The company email or user name already exists.');
        }
        // 生成用户编码
        $condition['page'] = 0;
        $condition['countPerPage'] = 1;
        $data_t_buyer = $model->getlist($condition);
        if ($data_t_buyer && substr($data_t_buyer['data'][0]['buyer_no'], 1, 8) == date("Ymd")) {
            $no = substr($data_t_buyer['data'][0]['buyer_no'], -1, 6);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = "C" . date("Ymd") . substr($new_num, 1, 6);
        $arr['buyer_no'] = $real_num;
        if (empty($arr['serial_no'])) {
            $arr['serial_no'] = $arr['buyer_no'];
        }
        $id = $model->create_data($arr);
        if ($id) {
            if (!empty($buyer_address_data)) {
                $buyer_address_data['buyer_id'] = $id;
            }
            $buyer_account_data['buyer_id'] = $id;
            $buyer_account_data['status'] = 'DRAFT';
            $account_id = $buyer_account_model->create_data($buyer_account_data);
            if (!empty($buyer_address_data)) {
                $buyer_address_model = new BuyerAddressModel();
                $buyer_address_model->create_data($buyer_address_data);
            }
            if($account_id){
                jsonReturn('', 1, 'Success!');
            }
            $where['id'] = $id;
            $model->delete_data($where);
            jsonReturn('', -105, 'Failed to register your buyeraddress!');
        }
        jsonReturn('', -105, 'Failed to register your account!');
    }

    /**
     * 用户注册信息回填修改--2.2
     * @author
     */
    public function registerInfoAction() {
        $data = $this->getPut();
        if (empty($data['buyer_id'])) {
            jsonReturn('', -101, '用户id不能为空!');
        }
        $buyerModel = new BuyerAccountModel();
        $result = $buyerModel->getinfo($data);
        if (!empty($result)) {
            jsonReturn($data);
        } else {
            jsonReturn('', '-1002', '获取失败!');
        }
    }

    /**
     * 用户注册完善--2.2
     * @author
     */
    public function improveInfoAction() {
        $data = $this->getPut();
        $where = $buyer_reg_data = [];
        if (!empty($data['buyer_id'])) {
            $where['buyer_id'] = $data['buyer_id'];
            $buyer_reg_data['buyer_id'] = $data['buyer_id'];
        } else {
            jsonReturn('', -101, '用户id不能为空!');
        }
        if (!empty($data['name'])) {
            $buyer_data['name'] = $data['name'];
        } else {
            jsonReturn('', -101, '公司名称不可以为空!');
        }
        if (!empty($data['biz_scope'])) {
            $buyer_reg_data['biz_scope'] = $data['biz_scope'];
        } else {
            jsonReturn('', -101, '经营范围不可以为空!');
        }
        if (!empty($data['intent_product'])) {
            $buyer_reg_data['intent_product'] = $data['intent_product'];
        } else {
            jsonReturn('', -101, '意向产品不可以为空!');
        }
        if (isset($data['purchase_quota'])) {
            $buyer_reg_data['purchase_quota'] = $data['purchase_quota'];
        }
        if (isset($data['address'])) {
            $buyer_address_data['address'] = $data['address'];
        }
        $buyerModel = new BuyerModel();
        $res = $buyerModel->update_data($buyer_data,$where);
        if($res) {
            if (!empty($buyer_address_data)) {
                $buyer_address_model = new BuyerAddressModel();
                $buyer_address_model->update_data($buyer_address_data,$where);
            }
            $buyer_reg_model = new BuyerreginfoModel();
            $regId = $buyer_reg_model->create_data($buyer_reg_data);
            if($regId){
                jsonReturn('', 1, 'Success!');
            }
            jsonReturn('',-1002,'Failed to register your buyerreginfo!');
        } else{
            jsonReturn('',-1002,'Failed to update your buyerinfo!');
        }
    }

    /**
     * 发送邮件--2.2    //待完善
     * @author
     */
    public function sendEmailCodeAction() {

        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if (!isEmail($data['email'])) {
                jsonReturn('', -101, '邮箱格式不正确!');
            }
        } else {
            jsonReturn('', -101, '邮箱不可以为空!');
        }
        $buyer_account_model = new BuyerAccountModel();
        $login_arr['email'] = $data['email'];
        $check = $buyer_account_model->Exist($login_arr);
        if (!$check) {
            jsonReturn('', -101, 'The company email is not registered yet');
        }

        //生成邮件验证码
        $data_key['key'] = md5(uniqid());
        $verify['code'] = rand(100000,999999);
        $verify['email'] = $data['email'];
        redisHashSet('rest_password_key', $data_key['key'], $verify, 1800);

        $body = $this->getView()->render('login/email.html', $verify['code']);
        //待完善--
        $res = send_Mail($arr['email'], 'Activation email for your registration on ERUI platform', $body);
        if ($res['code'] == 1) {
            jsonReturn('', 1, '发送成功');
        } else {
            jsonReturn('', -104, $res['msg']);
        }
    }

}
