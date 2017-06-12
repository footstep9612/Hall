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
        $data['name'] = $this->put_data['name'];
        $data['enc_password'] = $this->put_data['enc_password'];
        $data = $model->getlist($data);
        $this->jsonReturn($data);
    }

    public function registerAction() {
        $model = new UserModel();
        if (!isset($this->put_data['name'])) {
            $this->jsonReturn($model::getMessage(UserModel::MSG_USERNAME_CANNOTEMPTY));
        }
        if (!isset($this->put_data['email'])) {
            $this->jsonReturn($model::getMessage(UserModel::MSG_EMAIL_CANNOTEMPTY));
        }
        if (!isset($this->put_data['enc_password'])) {
            $this->jsonReturn($model::getMessage(UserModel::MSG_PASSWORD_CANNOTEMPTY));
        }
        $data = $model->create_data($this->put_data);
        $this->jsonReturn($data);
    }

    //put your code here
}
