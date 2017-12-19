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
class SpecloginController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
    }

    public function addUcustomAction() {
        $data = $this->getPut();
        if($data['sign'] == 'login') {
            $this->login($data);
        } elseif($data['sign'] == 'register') {
            $this->register($data);
        } elseif($data['sign'] == 'contact'){
            $data['buyer_id'] = $this->user['buyer_id'];
            $res = $this->createUcustom($data);
            if($res) {
                jsonReturn($res,1,'提交成功!');
            } else{
                jsonReturn('',-1,'提交失败!');
            }
        }
    }

    /*
     * 用户登录并提交定制服务
     */

    public function login($data) {
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (!empty($data['email'])) {
            $data['email'] = trim($data['email']);
            if (isEmail($data['email'])) {
                $arr['email'] = $data['email'];
            } else {
                $arr['user_name'] = $data['email'];
            }
        } else {
            jsonReturn(null, -124, ShopMsg::getMessage('-124', $lang));
            exit();
        }
        if (!empty($data['password'])) {
            $arr['password'] = trim($data['password']);
        } else {
            jsonReturn(null, -110, ShopMsg::getMessage('-110', $lang));
        }
        $model = new BuyerAccountModel();
        $info = $model->login($arr, $lang);
        if ($info) {
            $buyer_model = new BuyerModel();

            $buyer_info = $buyer_model->info(['buyer_id' => $info['buyer_id']] );

            $data['buyer_id'] = $info['buyer_id'];
            $data['show_name'] = $info['show_name'];
            $data['phone'] = $buyer_info['official_phone'];
            $data['company'] = $buyer_info['name'];
            $data['country'] = $buyer_info['country_bn'];

            $result= $this->createUcustom($data);
            if($result) {
                $jwtclient = new JWTClient();
                $jwt['id'] = $info['id'];
                $jwt['buyer_id'] = $info['buyer_id'];
                $jwt['ext'] = time();
                $jwt['iat'] = time();
                $jwt['show_name'] = $info['show_name'];
                $datajson['buyer_no'] = $buyer_info['buyer_no'];
                $datajson['email'] = $info['email'];
                $datajson['buyer_id'] = $info['buyer_id'];
                $datajson['show_name'] = $info['show_name'];
                $datajson['user_name'] = $info['user_name'];
                $datajson['token'] = $jwtclient->encode($jwt); //加密
                redisSet('shopmall_user_info_' . $info['id'], json_encode($info), 18000);
                echo json_encode(array("code" => "1", "data" => $datajson, "message" => ShopMsg::getMessage('138',$lang)));
                exit();
            }
            echo json_encode(array("code" => "-124", "data" => [], "message" => ShopMsg::getMessage('-124',$lang)));
        } else {
            echo json_encode(array("code" => "-124", "data" => [], "message" => ShopMsg::getMessage('-124',$lang)));
        }
    }

    /**
     * 用户定制信息新增
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function createUcustom($data) {
        $limit['pagesize'] = 1;
        $limit['current_no'] = 0;
        $buyer_custom_model = new BuyerCustomModel();
        $data_t_custom = $buyer_custom_model->getlist($limit);
        if ($data_t_custom && substr($data_t_custom[0]['service_no'], 1, 8) == date("Ymd")) {
            $no = substr($data_t_custom[0]['service_no'], 9, 6);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = "S" . date("Ymd") . substr($new_num, 1, 6);
        $data['service_no'] = $real_num;

        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (isset($data['phone']) && $data['phone']) {
            $data['tel'] = $data['phone'];
            if (!empty($data['tel_code'])) {
                $data['tel'] = $data['tel_code'].' '.$data['phone'];
            }
        }
        $res = $buyer_custom_model->create_data($data);
        if($res) {
            return $res;
        } else {
            return false;
        }
    }




    function retrievalEmailAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (!empty($data['email'])) {
            $retrieval_arr['email'] = trim($data['email']);
            if (!isEmail($retrieval_arr['email'])) {
                jsonReturn(null, -112, ShopMsg::getMessage('-112', $lang));
            }
        } else {
            jsonReturn(null, -111, ShopMsg::getMessage('-111', $lang));
        }
        $buyer_account_model = new BuyerAccountModel();
        $check_arr['email'] = trim($data['email']);
        $check = $buyer_account_model->Exist($check_arr);
        if ($check) {
            //生成邮件验证码
            $data_key['key'] = md5(uniqid());
            $data_key['email'] = $check_arr['email'];
            $data_key['name'] = $check[0]['name'];
            $account_id = $check[0]['id'];
            redisHashSet('reset_password_key', $data_key['key'], $account_id, 86400);
            $config_obj = Yaf_Registry::get("config");
            $config_shop = $config_obj->shop->toArray();
            $email_arr['url'] = $config_shop['url'];
            $email_arr['key'] = $data_key['key'];
            $email_arr['name'] = $check[0]['name'];
            $body = $this->getView()->render('login/retrieve_email_' . $lang . '.html', $email_arr);
            $title = 'Erui.com';
            send_Mail($data_key['email'], $title, $body, $data_key['name']);
            jsonReturn($data_key, 1, 'success!');
        } else {
            jsonReturn(null, -122, ShopMsg::getMessage('-122', $lang)); //'The company email is not registered yet'
        }
    }

    function checkKeyAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (empty($data['key'])) {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang)); //key不可以为空!'
        }
        if (redisHashExist('reset_password_key', $data['key'])) {
            jsonReturn('', 1, redisHashGet('reset_password_key', $data['key']));
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang)); //'未获取到key!'
        }
    }

    function setPasswordAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (!empty($data['password'])) {
            $user_arr['password_hash'] = trim($data['password']);
        } else {
            jsonReturn(null, -110, ShopMsg::getMessage('-110', $lang));
        }
        if (empty($data['key'])) {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang)); // 'Key is required'
        }
        $account_id = redisHashGet('reset_password_key', $data['key']);
        if ($account_id) {
            $buyer_account_model = new BuyerAccountModel();
            $info = $buyer_account_model->info(['id' => $account_id]);
            if ($info) {
                $user_arr['status'] = 'VALID';
            }
            $check = $buyer_account_model->update_data($user_arr, ['id' => $account_id]);
            redisHashDel('rest_password_key', $data['key']);

            $buyer_model = new BuyerModel();
            $buyer_info = $buyer_model->info(['buyer_id' => $info['buyer_id']]);
            $jwtclient = new JWTClient();
            $jwt['id'] = $info['buyer_id'];
            $jwt['buyer_id'] = $info['buyer_id'];
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['show_name'] = $info['show_name'];
            $datajson['buyer_no'] = $buyer_info['buyer_no'];
            $datajson['email'] = $info['email'];
            $datajson['buyer_id'] = $info['buyer_id'];
            $datajson['show_name'] = $info['show_name'];
            $datajson['user_name'] = $info['user_name'];
            $datajson['country'] = $buyer_info['country_bn'];
            $datajson['phone'] = $buyer_info['official_phone'];
            $datajson['token'] = $jwtclient->encode($jwt); //加密
            redisSet('shopmall_user_info_' . $info['buyer_id'], json_encode($datajson), 18000);

            jsonReturn($datajson, 1, 'success!');
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
        }
    }

    /**
     * 用户注册--new
     * @author
     */
    public function register($data) {
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (!empty($data['email'])) {
            $buyer_account_data['email'] = trim($data['email']);
            $arr['official_email'] = trim($data['email']);
            if (!isEmail($buyer_account_data['email'])) {
                jsonReturn(null, -112, ShopMsg::getMessage('-112', $lang));
            }
        } else {
            jsonReturn(null, -111, ShopMsg::getMessage('-111', $lang));
        }
        if (!empty($data['password'])) {
            $buyer_account_data['password_hash'] = md5(trim($data['password']));
        } else {
            jsonReturn(null, -110, ShopMsg::getMessage('-110', $lang));
        }
        if (!empty($data['phone']) && is_numeric($data['phone'])) {
            $arr['official_phone'] = $data['phone'];
            if (!empty($data['tel_code'])) {
                $arr['official_phone'] = $data['tel_code'] . ' ' . $data['phone'];
            }
        } else {
            jsonReturn(null, -113, ShopMsg::getMessage('-113', $lang));
        }
        if (!empty($data['country'])) {
            $arr['country_bn'] = $data['country'];
        } else {
            jsonReturn(null, -114, ShopMsg::getMessage('-114', $lang));
        }
        if (isset($data['city'])) {
            $arr['city'] = trim($data['city']);
        }
        if (isset($data['show_name'])) {
            $buyer_account_data['show_name'] = trim($data['show_name']);
        } else {
            jsonReturn(null, -115, ShopMsg::getMessage('-115', $lang));
        }

        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();
        $register_arr['email'] = $data['email'];
        //$register_arr['user_name'] = $data['user_name'];
        $check = $buyer_account_model->Exist($register_arr);
        if ($check) {
            jsonReturn('', -117, ShopMsg::getMessage('-117', $lang));
        }
        // 生成用户编码
        $condition['page'] = 0;
        $condition['countPerPage'] = 1;
        $data_t_buyer = $model->getlist($condition);
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

        $id = $model->create_data($arr);
        if ($id) {

            $data['buyer_id'] = $id;
            $result= $this->createUcustom($data);
            if($result) {
                $buyer_account_data['buyer_id'] = $id;
                $account_id = $buyer_account_model->create_data($buyer_account_data);
                if($account_id){

                    $jwtclient = new JWTClient();
                    $jwt['id'] = $id;
                    $jwt['buyer_id'] = $id;
                    $jwt['ext'] = time();
                    $jwt['iat'] = time();
                    $jwt['show_name'] = $buyer_account_data['show_name'];
                    $datajson['buyer_no']   =   $arr['buyer_no'];
                    $datajson['email']      =   $buyer_account_data['email'];
                    $datajson['buyer_id']   =   $id;
                    $datajson['show_name']  =   $buyer_account_data['show_name'];
                    $datajson['user_name']  =   '';
                    $datajson['country']    =   $arr['country_bn'];
                    $datajson['phone']      =   $arr['official_phone'];
                    $datajson['token']      =   $jwtclient->encode($jwt); //加密
                    redisSet('shopmall_user_info_' . $id, json_encode($datajson), 18000);
                    jsonReturn($datajson, 1, ShopMsg::getMessage('139',$lang));
                }
                jsonReturn('', -105, ShopMsg::getMessage('-105',$lang));
            }
            $where['id'] = $id;
            $model->delete_data($where);
            jsonReturn('', -105, ShopMsg::getMessage('-105', $lang));
        }
        jsonReturn('', -105, ShopMsg::getMessage('-105', $lang));
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

    /**
     * 验证邮箱
     * @author
     */
    public function exitEmailAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (!empty($data['email'])) {
            $register_arr['email'] = trim($data['email']);
            if (!isEmail($register_arr['email'])) {
                jsonReturn(null, -112, ShopMsg::getMessage('-112', $lang));
            }
        } else {
            jsonReturn(null, -111, ShopMsg::getMessage('-111', $lang));
        }
        $buyer_account_model = new BuyerAccountModel();
        $exit = $buyer_account_model->Exist($register_arr);
        if ($exit) {
            jsonReturn('', -117, ShopMsg::getMessage('-117', $lang));
        }
    }

    // 激活邮箱
    public function activeEmailAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (!empty($data['buyer_id'])) {
            $where['buyer_id'] = trim($data['buyer_id']);
        }
        if (!empty($data['email'])) {
            $check['email'] = trim($data['email']);
        }
        $data_key['key'] = md5(uniqid());
        $data_key['email'] = $check['email'];

        redisHashSet('set_active_key', $data_key['key'], $where['buyer_id'], 86400);
        $config_obj = Yaf_Registry::get("config");
        $config_shop = $config_obj->shop->toArray();
        $email_arr['url'] = $config_shop['url'];
        $email_arr['key'] = $data_key['key'];
        $body = $this->getView()->render('login/active_email.html', $email_arr);
        $title = 'Erui.com';
        $res = send_Mail($data_key['email'], $title, $body);
        if ($res['code'] == 1) {
            jsonReturn('', 1, '发送成功!');
        } else {
            jsonReturn('', -130, '发送失败!');
        }
    }

    function setActiveAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (empty($data['key'])) {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
        }
        if (redisHashExist('set_active_key', $data['key'])) {
            $buyer_id = redisHashGet('set_active_key', $data['key']);
            $buyer_account_model = new BuyerAccountModel();
            $user_arr['status'] = 'VALID';
            $check = $buyer_account_model->update_data($user_arr, ['buyer_id' => $buyer_id]);
            if ($check) {
                redisHashDel('rest_password_key', $data['key']);
                jsonReturn('', 1, 'success!');
            } else {
                jsonReturn('', -131, 'failed!');
            }
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
        }
    }

}
