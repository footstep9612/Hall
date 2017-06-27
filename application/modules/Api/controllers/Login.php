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
class LoginController extends Yaf_Controller_Abstract {

//    public function __init() {
//        //   parent::__init();
//    }
    /*
     * 用户登录
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */
    public function loginAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['password'])) {
            $arr['password'] = $data['password'];
        } else {
            echo json_encode(array("code" => "-101", "message" => "密码不可以都为空"));
            exit();
        }
        if (!empty($data['user_name'])) {
            if (isEmail($data['user_name'])) {
                $arr['email'] = $data['user_name'];
            } else {
                $arr['mobile'] = $data['user_name'];
            }
        } else {
            echo json_encode(array("code" => "-101", "message" => "帐号不可以都为空"));
            exit();
        }
        $model = new BuyerAccountModel();
        $info = $model->login($arr);
        if ($info) {
            $jwtclient = new JWTClient();
            $jwt['account_id'] = $info['id'];
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['user_name'] = $info['user_name'];
            $jwt['email'] = $info['email'];
            $datajson['email'] = $info['email'];
            $datajson['name'] = $info['name'];
            $datajson['token'] = $jwtclient->encode($jwt); //加密
            redisSet('shopmall_user_info_' . $info['id'], json_encode($info), 18000);
            echo json_encode(array("code" => "1", "data" => $datajson, "message" => "登陆成功"));
            exit();
        } else {
            $datajson = [];
            echo json_encode(array("code" => "-104", "data" => $datajson, "message" => "登录失败"));
        }
    }

    /**
     * 用户注册
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */
    public function registerAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['user_name'])) {
            $buyer_account_data['user_name'] = $data['user_name'];
        } else {
            jsonReturn('', -101, '用户名不可以为空!');
        }
        if (!empty($data['password'])) {
            $buyer_account_data['password_hash'] = md5($data['password']);
        } else {
            jsonReturn('', -101, '密码不可以都为空!');
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -101, '用户名不能为空!');
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['phone'])) {
            $buyer_account_data['phone'] = $data['phone'];
        } else {
            jsonReturn('', -101, '固话不可以都为空!');
        }

        if (!empty($data['email'])) {
            $buyer_account_data['email'] = $data['email'];
            if (!isEmail($buyer_account_data['email'])) {
                jsonReturn('', -101, '邮箱格式不正确!');
            }
        } else {
            jsonReturn('', -101, '邮箱不可以都为空!');
        }
        if (!empty($data['first_name'])) {
            $buyer_account_data['first_name'] = $data['first_name'];
        } else {
            jsonReturn('', -101, '名字不能为空!');
        }

        if (!empty($data['last_name'])) {
            $buyer_account_data['last_name'] = $data['last_name'];
        } else {
            jsonReturn('', -101, '姓不能为空!');
        }
        if (!empty($data['mobile'])) {
            $buyer_account_data['mobile'] = $data['mobile'];
        }
        if (!empty($data['country'])) {
            $data['country'] = $data['country'];
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
            jsonReturn('', -101, '手机或账号已存在!');
        }

        // 生成用户编码
        $condition['page'] = 0;
        $condition['countPerPage'] = 1;
        $data_t_buyer = $model->getlist($condition); //($this->put_data);
        if ($data_t_buyer && substr($data_t_buyer[0]['customer_id'], 1, 6) == date("ymd")) {
            $no = substr($data_t_buyer[0]['customer_id'], -1, 3);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000;
        $new_num = $no + $temp_num;
        $real_num = "C" . date("Ymd") . substr($new_num, 1, 3); //即截取掉最前面的“1”
        $arr['customer_id'] = $real_num;
        $buyer_address_data['customer_id'] = $arr['customer_id'];
        if (empty($arr['serial_no'])) {
            $arr['serial_no'] = $arr['customer_id'];
        }
        $id = $model->create_data($arr);
        var_dump($id);
        die;
        if ($id) {

            $group_user_model->create_data($buyer_address_data);
            jsonReturn('', 1, '提交成功');
        } else {
            jsonReturn('', -105, '数据添加失败');
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

}
