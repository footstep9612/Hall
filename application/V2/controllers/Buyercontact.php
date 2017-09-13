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
class BuyercontactController extends PublicController {

    public function __init() {
        //   parent::__init();
    }

    /*
     * 用户列表
     * */

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['buyer_id'])) {
            $where['buyer_id'] = $data['buyer_id'];
        }
        $model = new BuyercontactModel();
        $data = $model->getlist($where);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['count'] = $data['count'];
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户详情
     * */

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyercontactModel();
        $res = $model->info($data);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }



    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['buyer_id'])) {
            jsonReturn('', -101, '采购商id不可以为空!');
        }
        $model = new BuyercontactModel();
        $id = $model->create_data($data);
        if ($id) {
            $datajson['code'] = 1;
            $datajson['id'] = $id;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }


    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['id'])) {
            $where['id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "id不能为空"));
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['province'])) {
            $arr['province'] = $data['province'];
        }
        if (!empty($data['country_code'])) {
            $arr['country_code'] = $data['country_code'];
        }
        if (!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
        }
        if (!empty($data['email'])) {
            $arr['official_email'] = $data['email'];
        }
        if (!empty($data['mobile'])) {
            $arr['official_phone'] = $data['mobile'];
        }
        if (!empty($data['buyer_level'])) {
            $arr['buyer_level'] = $data['buyer_level'];
        }
        if (!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if (!empty($data['area_bn'])) {
            $arr['area_bn'] = $data['area_bn'];
        }
        if (!empty($data['status'])) {
            $arr['status'] = $data['status'];
            if ($data['status'] == 'APPROVED' || $data['status'] == 'REJECTED') {
                $arr['checked_by'] = $this->user['id'];
                $arr['checked_at'] = Date("Y-m-d H:i:s");
            }
        }
        $model = new BuyerModel();
        $res = $model->update_data($arr, $where);
        $buyer_account_model = new BuyerAccountModel();
        if (!empty($data['password'])) {
            $arr_account['password_hash'] = $data['password'];
            $buyer_account_model->update_data($arr_account, $where_account);
        }
        $buyer_attach_model = new BuyerattachModel();
        if (!empty($data['attach_url'])) {
            $where_attach['attach_url'] = $data['attach_url'];
            $buyer_attach_model->update_data($where_attach);
        }
        $model = new UserModel();
        $model->update_data($arr, $where);
        if ($res !== false) {
            $datajson['code'] = 1;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function getRoleAction() {
        if ($this->user['id']) {
            $role_user = new RoleUserModel();
            $where['user_id'] = $this->user['id'];
            $data = $role_user->getRoleslist($where);
            $datajson = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $data
            );
            jsonReturn($datajson);
        } else {
            $datajson = array(
                'code' => -104,
                'message' => '用户验证失败',
            );
        }
    }

}
