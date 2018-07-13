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
        // parent::init();
    }

    /*
     * 用户登录
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */

    public function loginAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        /* if (!empty($data['email'])) {
          if (!isEmail($data['email'])) {
          jsonReturn(null, -112, ShopMsg::getMessage('-112',$lang));
          }
          $arr['email'] = trim($data['email']);
          } else {
          jsonReturn(null, -111, ShopMsg::getMessage('-111',$lang));
          } */
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
            if ($info['deleted_flag'] !== 'N' || ($info['status'] !== 'VALID' && $info['status'] !== 'DRAFT')) {
                jsonReturn(null, -1, ShopMsg::getMessage('-145', $lang));
            }
            $buyer_model = new BuyerModel();
            $buyer_info = $buyer_model->info(['buyer_id' => $info['buyer_id']]);
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
            $datajson['country_bn'] = $buyer_info['country_bn'];
            $datajson['token'] = $jwtclient->encode($jwt); //加密
            $datajson['utime'] = 18000;
            redisSet('shopmall_user_info_' . $info['id'], json_encode($info), $datajson['utime']);
            echo json_encode(array("code" => "1", "data" => $datajson, "message" => ShopMsg::getMessage('102', $lang)));
            exit();
        } else {
            $datajson = [];
            echo json_encode(array("code" => "-124", "data" => $datajson, "message" => ShopMsg::getMessage('-124', $lang)));
        }
    }

    // 发送邮件
    /* public function sendEmailAction() {

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
      } */

    // 验证邮件
    /* public function checkEmailAction() {
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
      } */

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
            //验证账号状态
            $accountInfo = (is_array($check) && isset($check[0])) ? $check[0] : [];
            if (!$accountInfo || $accountInfo['deleted_flag'] !== 'N' || ($accountInfo['status'] !== 'VALID' && $accountInfo['status'] !== 'DRAFT')) {
                jsonReturn(null, -145, ShopMsg::getMessage('-145', $lang)); //'The company email is not registered yet'
            }
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
            $datajson['utime'] = 18000;
            redisSet('shopmall_user_info_' . $info['buyer_id'], json_encode($datajson), $datajson['utime']);

            jsonReturn($datajson, 1, 'success!');
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
        }
    }

    /**
     * 用户注册--new
     * @author
     */
    public function registerAction() {
        $data = $this->getPut();
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
                $arr['official_phone'] = $data['tel_code'] . '-' . $data['phone'];
            }
        } else {
            jsonReturn(null, -113, ShopMsg::getMessage('-113', $lang));
        }
        if (!empty($data['country'])) {
            $arr['country_bn'] = $data['country'];
        } else {
            jsonReturn(null, -114, ShopMsg::getMessage('-114', $lang));
        }
        if (isset($data['show_name'])) {
            $buyer_account_data['show_name'] = trim($data['show_name']);
        } else {
            jsonReturn(null, -115, ShopMsg::getMessage('-115', $lang));
        }
        if (isset($data['source']) && $data['source'] == 'mobile') {
            $arr['source'] = 3;
        } else {
            $arr['source'] = 2;
        }
        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();
        $register_arr['email'] = $data['email'];
        //$register_arr['user_name'] = $data['user_name'];
        $check = $buyer_account_model->Exist($register_arr);
        if ($check) {
            jsonReturn('', -117, ShopMsg::getMessage('-117', $lang));
        }
        if (isset($data['company_name']) && !empty($data['company_name'])) {
            $arr['name'] = trim($data['company_name']);
            $checkname = $model->where("name='" . $arr['name'] . "' AND deleted_flag='N'")->find();
            if ($checkname) {
                jsonReturn('', -125, ShopMsg::getMessage('-125', $lang));
            }
        } else {
            jsonReturn(null, -118, ShopMsg::getMessage('-118', $lang));
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
            $buyer_account_data['buyer_id'] = $id;
            $account_id = $buyer_account_model->create_data($buyer_account_data);
            /*
             * 判断introduction_source 字段是否存在 存在 就是 推介会来的 写入数据库
             */
            $jump_url = null;
            if (!empty($data['introduction_source'])) {
                $introduction_source = $data['introduction_source'];
                $source_country_model = new SourceCountryModel();
                $jump_url = $source_country_model->exist(trim($data['introduction_source']));
                if ($jump_url) {
                    $buyersource = new BuyerSourceModel();
                    $ret = $buyersource->create_data($id, $introduction_source);
                    if ($ret[0]) {
                        $this->sendmail($data['email'], $ret[1], $id, trim($data['show_name']));
                    }
                }
            }


            if ($account_id) {
                $datajson['key'] = md5(uniqid());
                redisSet('improve_info_key' . $datajson['key'], $id, 7200);

                $jwtclient = new JWTClient();
                $jwt['id'] = $id;
                $jwt['buyer_id'] = $id;
                $jwt['ext'] = time();
                $jwt['iat'] = time();
                $jwt['show_name'] = $buyer_account_data['show_name'];
                $datajson['buyer_no'] = $arr['buyer_no'];
                $datajson['email'] = $buyer_account_data['email'];
                //$datajson['company_name'] = $arr['name'];
                $datajson['buyer_id'] = $id;
                $datajson['jump_url'] = $jump_url;
                $datajson['show_name'] = $buyer_account_data['show_name'];
                $datajson['user_name'] = '';
                $datajson['country'] = $arr['country_bn'];
                $datajson['phone'] = $arr['official_phone'];
                $datajson['token'] = $jwtclient->encode($jwt); //加密
                $datajson['utime'] = 18000;
                redisSet('shopmall_user_info_' . $id, json_encode($datajson), $datajson['utime']);
                jsonReturn($datajson, 1, 'Success!');
            }
            $where['id'] = $id;
            $model->delete_data($where);
            jsonReturn('', -105, ShopMsg::getMessage('-105', $lang));
        }
        jsonReturn('', -105, ShopMsg::getMessage('-105', $lang));
    }

    /*
     * 发送邮件
     */

    private function sendmail($email, $token, $buyer_id, $show_name) {

        $shop_url = Yaf_Application::app()->getConfig()->get('shop.url');
        $body = $this->getView()->render('login/receipt_email.html', [
            'email' => $email,
            'token' => $token,
            'buyer_id' => $buyer_id,
            'show_name' => $show_name,
            'shop_url' => $shop_url,
                ]
        );
        $res = send_Mail($email, 'REGISTRATION confirmation', $body, $show_name);

        if ($res['code'] == 1) {
            $buyersource = new BuyerSourceModel();
            $ret = $buyersource->update_sendmail($buyer_id);
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
        /* if (!empty($data['name'])) {
          $buyer_data['name'] = trim($data['name']);
          } else {
          jsonReturn(null, -118, ShopMsg::getMessage('-118',$lang));
          } */
        /* if (isset($data['company_name']) && !empty($data['company_name'])) {
          $buyer_data['name'] = trim($data['company_name']);
          } else {
          jsonReturn(null, -118, ShopMsg::getMessage('-118',$lang));
          } */
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
        /*  $checkname = $buyerModel->where("name='" . $buyer_data['name'] . "' AND deleted_flag='N' AND id != ".$where['id'])->find();
          if ($checkname) {
          jsonReturn('', -125,  ShopMsg::getMessage('-125',$lang));
          } */
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
