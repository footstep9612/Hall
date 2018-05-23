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
class EmployeeController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
    }

    /*
     * 用户列表
     * */

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if(!empty($data['deleted_flag'])){
            $where['deleted_flag'] = $data['deleted_flag'];
        }else{
            $where['deleted_flag'] = 'N';
        }
        $where['lang'] = $this->lang;
        if (!empty($data['username'])) {
            $username = trim($data['username']);
            $where['username'] = $username;
        }
        if (!empty($data['role_id'])) {
            $where['role_id'] = trim($data['role_id']);
        }
        if (!empty($data['role_no'])) {
           //$where['role_no'] = trim($data['role_no']);
            $role_no = explode(",", $data['role_no']);
            for ($i = 0; $i < count($role_no); $i++) {
                $where['role_no'] = $where['role_no'] . "'" . $role_no[$i] . "',";
            }
            $where['role_no'] = rtrim($where['role_no'], ",");
        }
        if (!empty($data['status'])) {
            $where['status'] = trim($data['status']);
        }
        if (!empty($data['gender'])) {
            $where['gender'] = trim($data['gender']);
        }
        if (!empty($data['employee_flag'])) {
            $where['employee_flag'] = trim($data['employee_flag']);
        }
        if (!empty($data['pageSize'])) {
            $where['num'] = trim($data['pageSize']);
        }
        if (!empty($data['user_no'])) {

            $user_no = trim($data['user_no']);
            $where['user_no'] = $user_no;
        }
        if (!empty($data['bn'])) {
            $pieces = explode(",", $data['bn']);
            for ($i = 0; $i < count($pieces); $i++) {
                $where['bn'] = $where['bn'] . "'" . $pieces[$i] . "',";
            }
            $where['bn'] = rtrim($where['bn'], ",");
        }
        if (!empty($data['role_name'])) {
            $where['role_name'] = trim($data['role_name']);
        }
        if (!empty($data['currentPage'])) {
            $where['page'] = intval($data['currentPage']) > 1 ? (intval($data['currentPage']) - 1) * $where['num'] : 0;
        }
        if (!empty($where)) {
            $user_modle = new EmployeeModel();
            $data = $user_modle->getlist($where);
            $count = $user_modle->getcount($where);
            if (!empty($data)) {
                $datajson['code'] = 1;
                if ($count) {
                    $datajson['count'] = $count[0]['num'];
                } else {
                    $datajson['count'] = 0;
                }
                $datajson['data'] = $data;
            } else {
                $datajson['code'] = -104;
                $datajson['data'] = "";
                $datajson['message'] = '数据为空!';
            }
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '搜索条件为空';
        }

        $this->jsonReturn($datajson);
    }

    /*
     * 用户详情
     * */

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new UserModel();
        $res = $model->info($data['id']);
        if (!empty($res)) {
            unset($res['password_hash']);
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }



}
