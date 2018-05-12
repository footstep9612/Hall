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
          parent::__init();
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
        }else{
            jsonReturn('', -101, '采购商id不可以为空!');
        }
        if (!empty($data['first_name'])) {
            $where['first_name'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $where['last_name'] = $data['last_name'];
        }
        if (!empty($data['name'])) {
            $where['name'] = $data['name'];
        }
        if (!empty($data['country_bn'])) {
            $where['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['area_bn'])) {
            $where['area_bn'] = $data['area_bn'];
        }
        if(!empty($data['pageSize'])){
            $where['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        $model = new BuyercontactModel();
        $data = $model->getlist($where);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['count'] = $model->getcount($where);
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



    public function editContactAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];
        if (empty($data['buyer_id'])) {
            jsonReturn('', -101, '采购商id不可以为空!');
        }
        $model = new BuyercontactModel();
        $res = $model->createContact($data);
        if ($res) {
            $datajson['code'] = 1;
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
        if(isset($data['first_name'])){
            $arr['first_name'] = $data['first_name'];
        }
        if(isset($data['last_name'])){
            $arr['last_name'] = $data['last_name'];
        }
        if(isset($data['name'])){
            if (strlen($data['name']) > 70) jsonReturn('', -101, '您输入的收货人（公司）超出长度!');
            $arr['name'] = $data['name'];
        }
        if(isset($data['gender'])){
            $arr['gender'] = $data['gender'];
        }
        if(isset($data['title'])){
            $arr['title'] = $data['title'];
        }
        if(isset($data['phone'])){
            if (strlen($data['phone']) > 50) jsonReturn('', -101, '您输入的电话超出长度!');
            $arr['phone'] = $data['phone'];
        }
        if(isset($data['email'])){
            if (strlen($data['email']) > 50) jsonReturn('', -101, '您输入的邮箱超出长度!');
            $arr['email'] = $data['email'];
        }
        if(isset($data['remarks'])){
            $arr['remarks'] = $data['remarks'];
        }
        if(isset($data['fax'])){
            if (strlen($data['fax']) > 40) jsonReturn('', -101, '您输入的传真超出长度!');
            $arr['fax'] =$data['fax'];
        }
        if(isset($data['country_code'])){
            $arr['country_code'] =$data['country_code'];
        }
        if(isset($data['country_bn'])){
            $arr['country_bn'] =$data['country_bn'];
        }
        if(isset($data['province'])){
            $arr['province'] =$data['province'];
        }
        if(isset($data['city'])){
            if (strlen($data['city']) > 30) jsonReturn('', -101, '您输入的市超出长度!');
            $arr['city'] =$data['city'];
        }
        if(isset($data['address'])){
            if (strlen($data['address']) > 200) jsonReturn('', -101, '您输入的详细地址超出长度!');
            $arr['address'] =$data['address'];
        }
        if(isset($data['area_bn'])){
            $arr['area_bn'] =$data['area_bn'];
        }
        if(isset($data['zipcode'])){
            if (strlen($data['zipcode']) > 10) jsonReturn('', -101, '您输入的邮编超出长度!');
            $arr['zipcode'] =$data['zipcode'];
        }
        $model = new BuyercontactModel();
        $res = $model->update_data($arr, $where);
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
    /**
     * 客户拜访记录-选择获取客户联系人列表
     * wangs
     */
    public function buyerContactListAction() {
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyercontactModel();
        $res = $model->showBuyerExistContact($data['buyer_id'],$created_by);  //获取客户联系人列表
        $dataJson = array(
            'code'=>1,
            'message'=>'返回客户联系人列表',
            'data'=>$res
        );
        $this -> jsonReturn($dataJson);
    }

}
