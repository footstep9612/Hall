<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/5/14
 * Time: 16:46
 */
class SupplierloginController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
    }


    /**
     * 用户注册--瑞商
     * @author
     */
    public function registerAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        $data['lang'] = $lang;
        if (!empty($data['email'])) {
            $supplier_account_data['email'] = trim($data['email']);
            $arr['official_email'] = trim($data['email']);
            if (!isEmail($supplier_account_data['email'])) {
                jsonReturn(null, -112, ShopMsg::getMessage('-112', $lang));
            }
        } else {
            jsonReturn(null, -111, ShopMsg::getMessage('-111', $lang));
        }
        if (!empty($data['password'])) {
            $supplier_account_data['password_hash'] = md5(trim($data['password']));
        } else {
            jsonReturn(null, -110, ShopMsg::getMessage('-110', $lang));
        }
        if (isset($data['source'])&&$data['source']=='mobile') {
            $arr['source']="APP";
        } else {
            $arr['source']="Portal";
        }

        $model = new SupplierModel();
        $supplier_account_model = new SupplierAccountModel();
        $register_arr['email'] = $supplier_account_data['email'];
        $check = $supplier_account_model->Exist($register_arr);
        if ($check) {
            jsonReturn('', -117, ShopMsg::getMessage('-117', $lang));
        }
        // 生成用户编码
        $condition['page'] = 0;
        $condition['countPerPage'] = 1;
        $data_t_supplier = $model->getlist($condition); //($this->put_data);
        if ($data_t_supplier && substr($data_t_supplier['data'][0]['serial_no'], 0, 8) == date("Ymd")) {
            $no = substr($data_t_supplier['data'][0]['serial_no'], -1, 6);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = date("Ymd") . substr($new_num, 1, 6); //即截取掉最前面的“1”
        $arr['serial_no'] = $real_num;
        if (!empty($arr['serial_no'])) {
            $arr['supplier_no'] = $arr['serial_no'];
        }
        if (!empty($data['status'])) {
            $arr['status'] = $data['status'];
        } else {
            $arr['status'] = 'DRAFT';
        }
        $id = $model->create_data($arr);
        if ($id) {
            $supplier_account_data['supplier_id'] = $id;
            $account_id = $supplier_account_model->create_data($supplier_account_data);
            if ($account_id) {
                $datajson['key'] = md5(uniqid());
                redisSet('improve_info_key' . $datajson['key'], $id, 7200);

                $jwtclient = new JWTClient();
                $jwt['id'] = $id;
                $jwt['supplier_id'] = $id;
                $jwt['ext'] = time();
                $jwt['iat'] = time();

                //$datajson['supplier_no'] = $arr['supplier_no'];
                $datajson['supplier_email'] = $supplier_account_data['email'];
                $datajson['supplier_id'] = $id;
                $datajson['supplier_token'] = $jwtclient->encode($jwt); //加密
                $datajson['supplier_time'] = 18000;
                redisSet('supplier_user_info_' . $id, json_encode($datajson), $datajson['supplier_time']);
                jsonReturn($datajson, 1, 'Success!');
            }
            $where['id'] = $id;
            $model->delete_data($where);
            jsonReturn('', -105, ShopMsg::getMessage('-105', $lang));
        }
        jsonReturn('', -105, ShopMsg::getMessage('-105', $lang));
    }

    /*
     * 用户登录
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */

    public function loginAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'zh';

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
        $model = new SupplierAccountModel();
        $info = $model->login($arr, $lang);
        if ($info) {
            $supplier_model = new SupplierModel();
            $supplier_info = $supplier_model->info(['supplier_id' => $info['supplier_id']]);
            $jwtclient = new JWTClient();
            $jwt['id'] = $info['id'];
            $jwt['supplier_id'] = $info['supplier_id'];
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['supplier_user_name'] = $info['user_name'];

            $datajson['supplier_email'] = $info['email'];
            $datajson['supplier_id'] = $info['supplier_id'];
            $datajson['supplier_user_name'] = $info['user_name'];
            $datajson['supplier_country_bn'] = $supplier_info['country_bn'];
            $datajson['supplier_token'] = $jwtclient->encode($jwt); //加密
            $datajson['supplier_time'] = 18000;
            redisSet('supplier_user_info_' . $info['id'], json_encode($info), $datajson['supplier_time']);
            echo json_encode(array("code" => "1", "data" => $datajson, "message" => ShopMsg::getMessage('102', $lang)));
        } else {
            $datajson = [];
            echo json_encode(array("code" => "-124", "data" => $datajson, "message" => ShopMsg::getMessage('-124', $lang)));
        }
        exit();
    }

    // 验证邮件
    public function checkEmailAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (empty($data['key'])) {
            jsonReturn('', -134, ShopMsg::getMessage('-134', $lang));
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
            jsonReturn('', 1, ShopMsg::getMessage('136', $lang));
        } else {
            jsonReturn('', -137, ShopMsg::getMessage('-137', $lang));
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
            $data_key['show_name'] = $check[0]['show_name'];
            $account_id = $check[0]['id'];
            redisHashSet('reset_password_key', $data_key['key'], $account_id, 86400);
            $config_obj = Yaf_Registry::get("config");
            $config_shop = $config_obj->shop->toArray();
            $email_arr['url'] = $config_shop['url'];
            $email_arr['key'] = $data_key['key'];
            $email_arr['show_name'] = $check[0]['show_name'];
            $body = $this->getView()->render('login/retrieve_email_' . $lang . '.html', $email_arr);
            $title = 'Erui.com';
            send_Mail($data_key['email'], $title, $body, $data_key['show_name']);
            jsonReturn($data_key, 1, ShopMsg::getMessage('1', $lang));
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
            $datajson['utime'] = 18000;
            redisSet('supplier_user_info_' . $info['buyer_id'], json_encode($info), $datajson['utime']);

            jsonReturn($datajson, 1, 'success!');
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
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

    /**
     * 用户注册企业信息完善--new
     * @author
     */
    public function improveInfoAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $where = $buyer_data = [];
        if (redisExist('improve_info_key' . $data['key'])) {
            $where['id'] = redisGet('improve_info_key' . $data['key']);
        } else {
            jsonReturn('', -117, ShopMsg::getMessage('-117', $lang)); //'key不存在'
        }
        if (!empty($data['name'])) {
            $buyer_data['name'] = trim($data['name']);
        } else {
            jsonReturn(null, -118, ShopMsg::getMessage('-118', $lang));
        }
        if (!empty($data['biz_scope'])) {
            $buyer_data['biz_scope'] = trim($data['biz_scope']);
        } else {
            jsonReturn('', -123, ShopMsg::getMessage('-123', $lang));
        }
        if (!empty($data['intent_product'])) {
            $buyer_data['intent_product'] = trim($data['intent_product']);
        } else {
            jsonReturn('', -123, ShopMsg::getMessage('-123', $lang));
        }
        if (isset($data['purchase_amount'])) {
            $buyer_data['purchase_amount'] = trim($data['purchase_amount']);
        }

        $buyerModel = new BuyerModel();
        $checkname = $buyerModel->where("name='" . $buyer_data['name'] . "' AND deleted_flag='N' AND id != " . $where['id'])->find();
        if ($checkname) {
            jsonReturn('', -125, ShopMsg::getMessage('-125', $lang));
        }
        $res = $buyerModel->update_data($buyer_data, $where);
        if ($res) {
            redisDel('improve_info_key' . $data['key']);
            jsonReturn('', 1, 'Success!');
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang)); //Failed to update your buyerinfo!
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



}
