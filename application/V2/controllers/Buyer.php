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
class BuyerController extends PublicController {

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
        if (!empty($data['name'])) {
            $where['name'] = $data['name'];
        }
        if (!empty($data['country_bn'])) {
            $where['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['area_bn'])) {
            $where['area_bn'] = $data['area_bn'];
        }
        if(!empty($data['agent_id'])){
            $where['agent_id'] = $data['agent_id'];
        }
        if (!empty($data['buyer_no'])) {
            $where['buyer_no'] = $data['buyer_no'];
        }
        if (!empty($data['serial_no'])) {
            $where['serial_no'] = $data['serial_no'];
        }
        if (!empty($data['official_phone'])) {
            $where['official_phone'] = $data['official_phone'];
        }
        if (!empty($data['status'])) {
            $where['status'] = $data['status'];
        }
        if (!empty($data['employee_name'])) {
            $where['employee_name'] = $data['employee_name'];
        }
        if (!empty($data['user_name'])) {
            $where['user_name'] = $data['user_name'];
        }
        if (!empty($data['first_name'])) {
            $where['first_name'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $where['last_name'] = $data['last_name'];
        }
        if (!empty($data['checked_at_start'])) {
            $where['checked_at_start'] = $data['checked_at_start'];
        }
        if (!empty($data['checked_at_end'])) {
            $where['checked_at_end'] = $data['checked_at_end'];
        }
        if (!empty($data['created_at_end'])) {
            $where['created_at_end'] = $data['created_at_end'];
        }
        if (!empty($data['created_at_start'])) {
            $where['created_at_start'] = $data['created_at_start'];
        }
        if (!empty($data['credit_checked_at_start'])) {
            $where['credit_checked_at_start'] = $data['credit_checked_at_start'];
        }
        if (!empty($data['credit_checked_at_end'])) {
            $where['credit_checked_at_end'] = $data['credit_checked_at_end'];
        }
        if (!empty($data['approved_at_start'])) {
            $where['approved_at_start'] = $data['approved_at_start'];
        }
        if (!empty($data['approved_at_end'])) {
            $where['approved_at_end'] = $data['approved_at_end'];
        }
        if (!empty($data['pageSize'])) {
            $where['num'] = $data['pageSize'];
        }
        if (!empty($data['currentPage'])){
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        if (!empty($data['credit_checked_name'])) {
            $where['credit_checked_name'] = $data['credit_checked_name'];
        }
        if (!empty($data['line_of_credit_min'])) {
            $where['line_of_credit_min'] = $data['line_of_credit_min'];
        }
        if (!empty($data['line_of_credit_max'])) {
            $where['line_of_credit_max'] = $data['line_of_credit_max'];
        }
        if (!empty($data['credit_status'])) {
            $where['credit_status'] = $data['credit_status'];
        }
        $model = new BuyerModel();
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
        $model = new BuyerModel();
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

    /*
     * 用户详情
     * */

    public function accountinfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerAccountModel();
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
        if (!empty($data['user_name'])) {
            $buyer_account_data['user_name'] = $data['user_name'];
        } else {
            jsonReturn('', -101, '用户名不可以为空!');
        }
        if (!empty($data['password'])) {
            $buyer_account_data['password_hash'] = md5(trim($data['password']));
        } else {
            jsonReturn('', -101, '密码不可以都为空!');
        }
        if (!empty($data['phone'])) {
            $buyer_account_data['mobile'] = $data['mobile'];
        }
        if (!empty($data['email'])) {
            $buyer_account_data['email'] = $data['email'];
            if (!isEmail($buyer_account_data['email'])) {
                jsonReturn('', -101, '邮箱格式不正确!');
            }
            $arr['official_email'] = $data['email'];
        } else {
            jsonReturn('', -101, '邮箱不可以都为空!');
        }

        if (!empty($data['first_name'])) {
            $buyer_account_data['first_name'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $buyer_account_data['last_name'] = $data['last_name'];
        }
        if (!empty($data['mobile'])) {
            $buyer_account_data['mobile'] = $data['mobile'];
            $arr['official_phone'] = $data['mobile'];
        }

        $buyer_account_data['created_at'] = $this->user['id'];
        //附件
        if (!empty($data['attach_url'])) {
            $buyer_attach_data['attach_url'] = $data['attach_url'];
        }
        if (isset($buyer_attach_data)) {
            $buyer_attach_data['created_by'] = $this->user['id'];
            $buyer_attach_data['created_at'] = date("Y-m-d H:i:s");
            $buyer_attach_data['attach_name'] = $data['name'] . '营业执照';
        }
        $buyer_contact_data['mobile'] = $data['mobile'];
        $buyer_contact_data['email'] = $data['email'];
        $buyer_contact_data['last_name'] = $data['last_name'];
        $buyer_contact_data['first_name'] = $data['first_name'];
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -101, '名称不能为空!');
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
        } else {
            jsonReturn('', -101, '国家名不可为空!');
        }
        if (!empty($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
        }
        if (!empty($data['area_bn'])) {
            $arr['area_bn'] = $data['area_bn'];
        }
        if (!empty($data['zipcode'])) {
            $buyer_address_data['zipcode'] = $data['zipcode'];
        }
        if (!empty($data['address'])) {
            $buyer_address_data['address'] = $data['address'];
        }


        $arr['created_by'] = $this->user['id'];
        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();

        $login_email['email'] = $data['email'];
        $check_email = $buyer_account_model->Exist($login_email);
        if ($check_email) {
            jsonReturn('', -101, '公司邮箱已经存在!');
        }
        $login_uname['user_name'] = $data['user_name'];
        $check_uname = $buyer_account_model->Exist($login_uname);
        if ($check_uname) {
            jsonReturn('', -102, '用户名已经存在!');
        }
        
        // 生成用户编码
        $condition['page'] = 0;
        $condition['countPerPage'] = 1;

        $data_t_buyer = $model->getlist($condition); //($this->put_data);

        //var_dump($data_t_buyer);die;
        if ($data_t_buyer && substr($data_t_buyer['data'][0]['buyer_no'], 1, 8) == date("Ymd")) {
            $no = substr($data_t_buyer['data'][0]['buyer_no'], -1, 6);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = "C" . date("Ymd") . substr($new_num, 1, 6); //即截取掉最前面的“1”
        $arr['buyer_no'] = $real_num;
        if (!empty($data['serial_no'])) {
            $arr['serial_no'] = $data['serial_no'];
        }else{
            $arr['serial_no'] = $arr['buyer_no'];
        }
        $arr['created_by'] = $this->user['id'];

        $id = $model->create_data($arr);
        if ($id) {
            $buyer_account_data['buyer_id'] = $id;
            if (!empty($buyer_address_data)) {
                $buyer_address_data['buyer_id'] = $id;
            }
            if (!empty($buyer_attach_data)) {
                $buyer_attach_data['buyer_id'] = $id;
            }
            $buyer_contact_data['buyer_id'] = $id;
            //添加联系人
            $buyer_contact_model = new BuyercontactModel();
            $buyer_contact_model->create_data($buyer_contact_data);
            //添加附件
            $buyer_attach_model = new BuyerattachModel();
            $buyer_attach_model->create_data($buyer_attach_data);
            //采购商帐号表
            $buyer_account_model->create_data($buyer_account_data);
            if (!empty($buyer_address_data)) {
                $buyer_address_model = new BuyerAddressModel();
                $buyer_address_model->create_data($buyer_address_data);
            }
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

    public function agentlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['buyer_id'])) {
            $array['buyer_id'] = $data['buyer_id'];
        }
        if (!empty($data['agent_id'])) {
            $array['agent_id'] = $data['agent_id'];
        }
        $model = new BuyerAgentModel();
        $res = $model->getlist($array);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateagentAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $array['id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "会员id不能为空"));
        }
        if (!empty($data['user_ids'])) {
            $array['user_ids'] = $data['user_ids'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "负责人id不能为空"));
        }
        $array['created_by'] = $this->user['id'];
        $model = new BuyerAgentModel();
        $res = $model->create_data($array);
        if (!empty($res)) {
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
            $where_account['buyer_id'] = $data['id'];
            $where_attach['buyer_id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
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
        if (!empty($data['serial_no'])) {
            $arr['serial_no'] = $data['serial_no'];
        }
        if (!empty($data['country_code'])) {
            $arr['country_code'] = $data['country_code'];
        }
        if (!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
            $account['first_name'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
            $account['last_name'] = $data['last_name'];
        }
        $buyer_account_model = new BuyerAccountModel();
        if (!empty($data['email'])) {
            $arr['official_email'] = $data['email'];
            $account['email'] = $data['email'];
            $buyer_id = $buyer_account_model->where(['email'=>$data['email']])->getField('buyer_id');
            if($buyer_id >0 && $buyer_id != $data['id']){
                $this->jsonReturn(array("code" => "-101", "message" => "该邮箱已经被其他账号使用"));
            }
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "办公邮箱不能为空"));
        }
        if (!empty($data['mobile'])) {
            $arr['official_phone'] = $data['mobile'];
            $account['mobile'] = $data['mobile'];
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
        
        if (!empty($data['password'])) {
            $arr_account['password_hash'] = $data['password'];
            $buyer_account_model->update_data($arr_account, $where_account);
        }
        $buyer_attach_model = new BuyerattachModel();
        if (!empty($data['attach_url'])) {
            $where_attach['attach_url'] = $data['attach_url'];
            $buyer_attach_model->update_data($where_attach);
        }
        //$model = new UserModel();
        if(!empty($account)){
            $buyer_account_model->update_data($account, $where_account);
        }
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
//    public function creditAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        $role_user = new RoleUserModel();
//            $where['user_id'] = $this->user['id'];
//            $data = $role_user->getRoleslist($where);
//            $datajson = array(
//                'code' => 1,
//                'message' => '数据获取成功',
//                'data' => $data
//            );
//            jsonReturn($datajson);
//        } else {
//            $datajson = array(
//                'code' => -104,
//                'message' => '用户验证失败',
//            );
//        }
//    }
}
