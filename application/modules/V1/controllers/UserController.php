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

    public function getlist() {
        $model = new UserModel();
        $data = $model->getlist($this->put_data); //($this->put_data);
        $this->jsonReturn($data);
    }

    public function info() {
        $model = new UserModel();
        $data = $model->info($this->put_data['id']);
        $this->jsonReturn($data);
    }

    public function create() {
        $model = new UserModel();
        $data = $model->create_data($this->put_data);
        $this->jsonReturn($data);
    }

    public function update() {
        $model = new UserModel();
        $data = $model->update_data($this->put_data);
        $this->jsonReturn($data);
    }

    public function delete() {
        $model = new UserModel();
        $data = $model->delete_data($this->put_data['id']);
        $this->jsonReturn($data);
    }

    public function login() {
        $model = new UserModel();
        $data['name'] = $this->put_data['name'];
        $data['enc_password'] = $this->put_data['enc_password'];
        $data = $model->getlist($data);
        $this->jsonReturn($data);
    }

    public function register() {
        $model = new UserModel();
        $data = $model->update($this->put_data['id']);
        $this->jsonReturn($data);
    }

    //put your code here
}
