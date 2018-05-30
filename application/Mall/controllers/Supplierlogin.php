<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/5/14
 * Time: 16:46
 */
class SupplierloginController extends SupplierpublicController {

    public function init() {
        $this->supplier_token = false;
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
        if(isset($data['source']) && !empty($data['source'])){
            $arr['source']="Association";
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
            $arr['status'] = 'REVIEW';
        }
        $arr['supplier_type'] = '';
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
                $jwt['supplier_email'] = $supplier_account_data['email'];
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
            //$jwt['supplier_user_name'] = $info['user_name'];
            $jwt['supplier_email'] = $info['email'];

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
        $lang = $data['lang'] ? $data['lang'] : 'zh';
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
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        if (!empty($data['email'])) {
            $retrieval_arr['email'] = trim($data['email']);
            if (!isEmail($retrieval_arr['email'])) {
                jsonReturn(null, -112, ShopMsg::getMessage('-112', $lang));
            }
        } else {
            jsonReturn(null, -111, ShopMsg::getMessage('-111', $lang));
        }
        $supplier_account_model = new SupplierAccountModel();
        $check_arr['email'] = trim($data['email']);
        $check = $supplier_account_model->Exist($check_arr);
        if ($check) {
            //验证账号状态
            $accountInfo = (is_array($check) && isset($check[0])) ? $check[0] : [];
            if (!$accountInfo || $accountInfo['deleted_flag'] !== 'N' || ($accountInfo['status'] !== 'VALID' && $accountInfo['status'] !== 'DRAFT')) {
                jsonReturn(null, -145, ShopMsg::getMessage('-145', $lang));
            }
            //生成邮件验证码
            $data_key['key'] = md5(uniqid());
            $data_key['email'] = $check_arr['email'];
            $data_key['user_name'] = $check[0]['user_name'];
            $account_id = $check[0]['id'];
            redisHashSet('reset_supplier_password_key', $data_key['key'], $account_id, 86400);
            $config_obj = Yaf_Registry::get("config");

            $config_alliance = $config_obj->alliance->toArray();  //添加供应商域名

            $email_arr['url'] = $config_alliance['url'];
            $email_arr['key'] = $data_key['key'];
            $email_arr['user_name'] = $check[0]['user_name'];
            $title = 'Erui.com';
            $type = 'supplier_verification';
            $html = 'login/retrieve_supplier_email_' . $lang . '.html';
            //jsonReturn($email_arr);
            $body = $this->getView()->render($html, $email_arr);
            $title = 'Erui.com';
            send_Mail($data_key['email'], $title, $body, $data_key['user_name']);
            //$this->creditEmail($data_key['email'],$email_arr,(array)$config_email['url'],$title,$html,$type);
            jsonReturn($data_key, 1, '成功!');
        } else {
            jsonReturn(null, -122, ShopMsg::getMessage('-122', $lang));
        }
    }

    function checkKeyAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        if (empty($data['key'])) {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang)); //key不可以为空!'
        }
        if (redisHashExist('reset_supplier_password_key', $data['key'])) {
            jsonReturn('', 1, redisHashGet('reset_supplier_password_key', $data['key']));
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang)); //'未获取到key!'
        }
    }

    function setPasswordAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        if (!empty($data['password'])) {
            $user_arr['password_hash'] = md5(trim($data['password']));
        } else {
            jsonReturn(null, -110, ShopMsg::getMessage('-110', $lang));
        }
        if (empty($data['key'])) {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang)); // 'Key is required'
        }
        $account_id = redisHashGet('reset_supplier_password_key', $data['key']);
        if ($account_id) {
            $supplier_account_model = new SupplierAccountModel();
            $info = $supplier_account_model->info(['id' => $account_id]);
            $check = $supplier_account_model->update_data($user_arr, ['id' => $account_id]);
            redisHashDel('reset_supplier_password_key', $data['key']);
            if($check){
//                $model = new SupplierModel();
//                $supplier_info = $model->info(['supplier_id' => $info['supplier_id']]);
//                $jwtclient = new JWTClient();
//                $jwt['id'] = $info['id'];
//                $jwt['supplier_id'] = $info['supplier_id'];
//                $jwt['ext'] = time();
//                $jwt['iat'] = time();
//                $jwt['user_name'] = $info['user_name'];
//                $datajson['supplier_no'] = $supplier_info['supplier_no'];
//                $datajson['supplier_email'] = $info['email'];
//                $datajson['supplier_id'] = $info['supplier_id'];
//                $datajson['user_name'] = $info['user_name'];
//                $datajson['country'] = $supplier_info['country_bn'];
//                $datajson['phone'] = $info['official_phone'];
//                $datajson['supplier_token'] = $jwtclient->encode($jwt); //加密
//                $datajson['supplier_time'] = 18000;
//                redisSet('supplier_user_info_' . $info['supplier_id'], json_encode($datajson), $datajson['supplier_time']);

                jsonReturn('', 1, '成功!');
            } else{
                jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
            }
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
        }
    }


    /**
     * 验证邮箱
     * @author
     */
    public function exitEmailAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'zh';
        if (!empty($data['email'])) {
            $register_arr['email'] = trim($data['email']);
            if (!isEmail($register_arr['email'])) {
                jsonReturn(null, -112, ShopMsg::getMessage('-112', $lang));
            }
        } else {
            jsonReturn(null, -111, ShopMsg::getMessage('-111', $lang));
        }
        $supplier_account_model = new SupplierAccountModel();
        $exit = $supplier_account_model->Exist($register_arr);
        if ($exit) {
            jsonReturn('', -117, ShopMsg::getMessage('-117', $lang));
        }
    }

    //发送邮件
    function creditEmail($email,$arr, $emailUrl, $title= 'Erui.com',$html,$type) {
        if(!$html) return false;
        if(!$email) return false;
        if(!$emailUrl) return false;
        $body = $this->getView()->render($html, $arr);
        $data = [
            "title"        => $title,
            "content"      => $body,
            "groupSending" => 0,
            "useType"      => $type
        ];
        if(is_array($email)) {
            $arr_email = implode(',',$email);
            $data["to"] = "[$arr_email]";
        }elseif(is_string($email)){
            $data["to"] = "[$email]";
        }
        PostData($emailUrl, $data, true);
    }

}
