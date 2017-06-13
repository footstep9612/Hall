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
class UserController extends PublicController {

    public function __init() {
        parent::__init();
    }

    public function getlistAction() {
        $model = new UserModel();
        $data = $model->getlist($this->put_data); //($this->put_data);
        $this->jsonReturn($data);
    }

    public function infoAction() {
        $model = new UserModel();
        $data = $model->info($this->put_data['id']);
        $this->jsonReturn($data);
    }

    public function createAction() {
        $model = new UserModel();
        $data = $model->create_data($this->put_data);
        $this->jsonReturn($data);
    }

    public function updateAction() {
        $model = new UserModel();
        $data = $model->update_data($this->put_data);
        $this->jsonReturn($data);
    }

    public function deleteAction() {
        $model = new UserModel();
        $data = $model->delete_data($this->put_data['id']);
        $this->jsonReturn($data);
    }

    public function loginAction() {
        $model = new UserModel();
        $this->put_data = ['name' => 'azhong', 'email' => '87725826@qq.com', 'enc_password' => '1234567890'];
        if (!isset($this->put_data['name'])) {
            $this->jsonReturn($model->getMessage(UserModel::MSG_USERNAME_CANNOTEMPTY));
        }

        if (!isset($this->put_data['enc_password'])) {
            $this->jsonReturn($model->getMessage(UserModel::MSG_PASSWORD_CANNOTEMPTY));
        }


        $userinfo = $model->login($this->put_data['name'], $this->put_data['enc_password']);

        if ($userinfo['id']) {
            $data['success'] = 1;
            $data['msg'] = '登录成功!';
            $jwtclient = new JWTClient();
            $jwt['uid'] = md5($userinfo['id']);
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['account'] = $userinfo['name'];
            $data['obj'] = ['token' => $jwtclient->encode($jwt)]; //加密
            $data['jsonStr'] = json_encode($data);
            $this->jsonReturn($data);
        } else {
            $data['success'] = 0;
            $data['msg'] = '登录失败!';
            $data['obj'] = [];
            $data['jsonStr'] = json_encode($data);
            $this->jsonReturn($data);
        }
    }

    public function registerAction() {
        $model = new UserModel();
        $this->put_data = ['name' => 'azhong', 'email' => '87725826@qq.com', 'enc_password' => '1234567890'];
        if (!isset($this->put_data['name'])) {
            $this->jsonReturn($model->getMessage(UserModel::MSG_USERNAME_CANNOTEMPTY));
        }
        if (!isset($this->put_data['email'])) {
            $this->jsonReturn($model->getMessage(UserModel::MSG_EMAIL_CANNOTEMPTY));
        }
        if (!isset($this->put_data['enc_password'])) {
            $this->jsonReturn($model->getMessage(UserModel::MSG_PASSWORD_CANNOTEMPTY));
        }
        if ($model->Exist($this->put_data['name'])) {
            $this->jsonReturn($model->getMessage(UserModel::MSG_NAME_ERR));
        }
        if ($model->Exist($this->put_data['email'], 'email')) {
            $this->jsonReturn($model->getMessage(UserModel::MSG_EMAIL_CANNOTEMPTY));
        }
        $flag = $model->create_data($this->put_data);
        if ($flag) {
            $data['success'] = 1;
            $data['msg'] = '注册成功!';
            $jwtclient = new JWTClient();
            $jwt['uid'] = md5($userinfo['id']);
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['account'] = $userinfo['name'];
            $data['obj'] = ['token' => $jwtclient->encode($jwt)]; //加密
            $data['jsonStr'] = json_encode($data);
            $this->jsonReturn($data);
            // $this->jsonReturn($model->getMessage(UserModel::MSG_SUCCESS));
        } else {
            $data['success'] = 0;
            $data['msg'] = '注册失败!';
            $data['obj'] = [];
            $data['jsonStr'] = json_encode($data);
            $this->jsonReturn($data);
            // $this->jsonReturn($model->getMessage(UserModel::MSG_PARAMETER_ERR));
        }
    }

    public function esAction() {
        $es = new ESClient();

        $index = 'erui_db';
        $type = 'product';
        
        $body = [
            'lang' => 'en',
            'spu' => '0',
            'meterial_cat_code' => 0,
            'qrcode' => 0,
            'name' => '',
            'show_name' => '',
            'supplier_no'=>'',
            'brand'=>'',
            'source'=>'',
            'source_detail'=>'',
            'recommend_flag'=>'Y',
            'status'=>'',
            'keywords'=>'',
            'updated_by' => '',
            'updated_at' => '',
            'checked_by' => '',
            'checked_at' => '',
            'created_by' => '',
            'created_at' => ''
        ];
        $es->add_document($index, $type, $body);
        //  echo '<pre>';
        //  $val = $es->setmust(['testField'=>'加安疗'],ESClient::MATCH)->search($index, $type);
        //  var_dump($val);
        die();
    }
    public function kafkaAction(){
        $kafka=new KafKaServer();
        $kafka->produce();
    }

    //put your code here
}
