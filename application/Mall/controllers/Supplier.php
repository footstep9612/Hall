<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author jhw
 */
class SupplierController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
    }

    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (empty($data['buyer_id'])) {
            $this->user['id'] = null;
        } else {
            $this->user['id'] = $data['buyer_id'];
        }
        if (isset($data['supplier_type'])) {
            $arr['supplier_type'] = $data['supplier_type'];
        }
        if (isset($data['source'])) {
            $arr['source'] = $data['source'];
        } else {
            $arr['source'] = 'Portal';
        }
        if (!empty($data['contact_name'])) {
            $arr['contact_name'] = $data['contact_name'];
        } else {
            jsonReturn('', -118, ShopMsg::getMessage('-115', $lang));
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -118, ShopMsg::getMessage('-118', $lang));
        }
        if (!empty($data['name'])) {
            $arr['name_en'] = $data['name'];
        } else {
            jsonReturn('', -118, ShopMsg::getMessage('-118', $lang));
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['lang'])) {
            $arr['lang'] = $data['lang'];
        }
        if (!empty($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
            $supplier_account_data['first_name'] = $data['first_name'];
            $supplier_contact_data['first_name'] = $data['first_name'];
            $bizline_supplier_data['first_name'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
            $supplier_account_data['last_name'] = $data['last_name'];
            $supplier_contact_data['last_name'] = $data['last_name'];
            $bizline_supplier_data['last_name'] = $data['last_name'];
        }
        if (!empty($data['phone'])) {
            $supplier_account_data['phone'] = $data['phone'];
            $supplier_contact_data['phone'] = $data['phone'];
            $arr['official_phone'] = $data['phone'];
            $bizline_supplier_data['phone'] = $data['phone'];
        }
        if (!empty($data['email'])) {
            $supplier_account_data['email'] = $data['email'];
            if (!isEmail($supplier_account_data['email'])) {
                jsonReturn('', -112, ShopMsg::getMessage('-112', $lang));
            }
            $arr['official_email'] = $data['email'];
            $supplier_contact_data['email'] = $data['email'];
            $bizline_supplier_data['email'] = $data['email'];
        } else {
            jsonReturn('', -111, ShopMsg::getMessage('-111', $lang));
        }
        if (!empty($data['country_code'])) {
            $arr['country_code'] = $data['country_code'];
        }
        if (!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        } else {
            jsonReturn('', -114, ShopMsg::getMessage('-114', $lang));
        }
        if (!empty($data['province'])) {
            $arr['province'] = $data['province'];
        }
        if (!empty($data['city'])) {
            $arr['city'] = $data['city'];
        }
        if (!empty($data['logo'])) {
            $arr['logo'] = $data['logo'];
        }
        if (!empty($data['social_credit_code'])) {
            $arr['social_credit_code'] = $data['social_credit_code'];
        }
        if (!empty($data['profile'])) {
            $arr['profile'] = $data['profile'];
        }
        if (!empty($data['reg_capital'])) {
            $arr['reg_capital'] = $data['reg_capital'];
        }
        if (!empty($data['employee_count'])) {
            $arr['employee_count'] = $data['employee_count'];
        }
        if (!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
            $supplier_contact_data['remarks'] = $data['remarks'];
        }
        $arr['created_by'] = $this->user['id'];
        $supplier_account_data['created_by'] = $this->user['id'];
        $supplier_account_data['created_at'] = date("Y-m-d H:i:s");
        $supplier_contact_data['created_by'] = $this->user['id'];
        $bizline_supplier_data['created_by'] = $this->user['id'];
        $bizline_supplier_data['created_at'] = date("Y-m-d H:i:s");
        $arr['created_at'] = date("Y-m-d H:i:s");

        // 生成供应商编码
        $model = new SupplierModel();
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
            $arr['status'] = 'DRAFT';
        }
        $org_model = new OrgModel();
        $org_id = $org_model->getOrgIdByName();
        if(!empty($org_id)) {
            $arr['org_id'] = $org_id;
        }
        $id = $model->create_data($arr);
        if ($id) {
            if (isset($data['user_name'])) {
                $supplier_account_data['supplier_id'] = $id;
                $supplier_account_data['user_name'] = $data['user_name'];
                $supplier_account_data['password_hash'] = md5($data['password']);
                $supplier_account = new SupplierAccountModel();
                $supplier_account->create_data($supplier_account_data);
            }
            $supplier_contact = new SupplierContactModel();
            $supplier_contact_data['supplier_id'] = $id;
            $supplier_contact_data['contact_name'] = $data['contact_name'];
            $supplier_contact->create_data($supplier_contact_data);

            $datajson['code'] = 1;
            $datajson['message'] = ShopMsg::getMessage('1', $lang);
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = ShopMsg::getMessage('-107', $lang);
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 瑞商信息完善
     * */
    public function editSupplierInfoAction(){

    }

}
