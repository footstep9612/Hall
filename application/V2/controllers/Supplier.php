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
        } else {
            $map['name'] = ['neq', ''];
            $map[] = '`name` is not null';
            $map['_logic'] = 'and';
            $where['_complex'] = $map;
        }
        if (!empty($data['supplier_no'])) {
            $where['supplier_no'] = $data['supplier_no'];
        }
        if (!empty($data['status'])) {
            $where['status'] = $data['status'];
        }
        if (!empty($data['supplier_type'])) {
            $where['supplier_type'] = $data['supplier_type'];
        }
        if (!empty($data['checked_by'])) {
            $where['checked_by'] = $data['checked_by'];
        }
        if (!empty($data['checked_at_start'])) {
            $where['checked_at_start'] = $data['checked_at_start'];
        }
        if (!empty($data['checked_at_end'])) {
            $where['checked_at_end'] = $data['checked_at_end'];
        }
        if (!empty($data['created_at_start'])) {
            $where['created_at_start'] = $data['created_at_start'];
        }
        if (!empty($data['created_at_end'])) {
            $where['created_at_end'] = $data['created_at_end'];
        }
        if (!empty($data['supplier_type'])) {
            $where['supplier_type'] = $data['supplier_type'];
        }
        if (!empty($data['pageSize'])) {
            $where['num'] = $data['pageSize'];
        }
        if (!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        $model = new SupplierModel();
        $data = $model->getlist($where);
        for ($i = 0; $i < count($data['data']); $i++) {
            if ($data['data'][$i]['brand']) {
                $data['data'][$i]['brand'] = json_decode($data['data'][$i]['brand'], true);
            }
        }
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new SupplierModel();
        $res = $model->info($data);
        if ($res['brand']) {
            $res['brand'] = json_decode($res['brand'], true);
        }
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

    public function accountinfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new SupplierAccountModel();
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

    public function attachinfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new SupplierAttachModel();
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

    public function bankinfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new SupplierBankInfoModel();
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

    public function addressinfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new SupplierAddressModel();
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

        if (!empty($data['supplier_type'])) {
            $arr['supplier_type'] = $data['supplier_type'];
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -101, '企业名称不能为空!');
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
        if (!empty($data['mobile'])) {
            $supplier_account_data['mobile'] = $data['mobile'];
            $supplier_contact_data['mobile'] = $data['mobile'];
            $arr['official_phone'] = $data['mobile'];
            $bizline_supplier_data['phone'] = $data['phone'];
        }
        if (!empty($data['email'])) {
            $supplier_account_data['email'] = $data['email'];
            if (!isEmail($supplier_account_data['email'])) {
                jsonReturn('', -101, '邮箱格式不正确!');
            }
            $arr['official_email'] = $data['email'];
            $supplier_contact_data['email'] = $data['email'];
            $bizline_supplier_data['email'] = $data['email'];
        } else {
            jsonReturn('', -101, '邮箱不可以都为空!');
        }
        if (!empty($data['country_code'])) {
            $arr['country_code'] = $data['country_code'];
        }
        if (!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        } else {
            jsonReturn('', -101, '地区名不可为空!');
        }
        if (!empty($data['province'])) {
            $arr['province'] = $data['province'];
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
        $id = $model->create_data($arr);
        if ($id) {
            if (isset($data['user_name'])) {
                $supplier_account_data['supplier_id'] = $id;
                $supplier_account_data['user_name'] = $data['user_name'];
                $supplier_account_data['password_hash'] = md5($data['password']);
                $supplier_account = new SupplierAccountModel();
                $supplier_account->create_data($supplier_account_data);
            }
            if (isset($data['bizline_id'])) {
                $bizline_supplier_data['supplier_id'] = $id;
                $bizline_supplier_data['bizline_id'] = $data['bizline_id'];
                $bizline_supplier = new BizlineSupplierModel();
                $bizline_supplier->create_data($bizline_supplier_data);
            }
            $supplier_attach = new SupplierAttachModel();
            if (!empty($data['license_attach_url'])) {
                $supplier_attach_data['supplier_id'] = $id;
                $supplier_attach_data['attach_url'] = $data['license_attach_url'];
                $supplier_attach_data['attach_name'] = $data['attach_name'];
                $supplier_attach_data['attach_group'] = 'LICENSE';
                $supplier_attach_data['created_by'] = $this->user['id'];
                $supplier_attach_data['created_at'] = date("Y-m-d H:i:s");
                $supplier_attach->create_data($supplier_attach_data);
            }
            $supplier_contact = new SupplierContactModel();
            $supplier_contact_data['supplier_id'] = $id;
            $supplier_contact->create_data($supplier_contact_data);
            if ($data['bank_name']) {
                $supplier_bank_info_data['bank_name'] = $data['bank_name'];
            }
            if ($data['bank_address']) {
                $supplier_bank_info_data['address'] = $data['bank_address'];
            }
            if ($data['bank_account']) {
                $supplier_bank_info_data['bank_account'] = $data['bank_account'];
            }

            if (isset($supplier_bank_info_data)) {
                $supplier_bank_info_data['supplier_id'] = $id;
                $supplier_bank_info_data['created_by'] = $this->user['id'];
                $supplier_bank_info_data['created_at'] = date("Y-m-d H:i:s");
                $supplier_bank_info = new SupplierBankInfoModel();
                $supplier_bank_info->create_data($supplier_bank_info_data);
            }
            if ($data['address']) {
                $supplier_address_data['address'] = $data['address'];
                $supplier_address_data['supplier_id'] = $id;
                $supplier_address_data['created_by'] = $this->user['id'];
                $supplier_address_model = new SupplierAddressModel();
                $supplier_address_model->create_data($supplier_address_data);
            }
            if ($data['other_attach_url']) {
                for ($i = 0; $i < count($data['other_attach_url']); $i++) {
                    $supplier_attach_other_data['supplier_id'] = $id;
                    $supplier_attach_other_data['attach_url'] = $data['other_attach_url'][$i];
                    $supplier_attach_other_data['attach_name'] = $data['other_attach_name'][$i];
                    $supplier_attach_other_data['attach_group'] = 'CERT';
                    $supplier_attach_other_data['created_by'] = $this->user['id'];
                    $supplier_attach_other_data['created_at'] = date("Y-m-d H:i:s");
                    $supplier_attach->create_data($supplier_attach_other_data);
                }
            }
            $datajson['code'] = 1;
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
        if (!empty($data['supplier_id'])) {
            $array['supplier_id'] = $data['supplier_id'];
        }
        if (!empty($data['org_id'])) {
            $array['org_id'] = $data['org_id'];
        }
        $model = new SupplierAgentModel();
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
        if (!empty($data['org_ids'])) {
            $array['org_ids'] = $data['org_ids'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "负责人id不能为空"));
        }
        $array['created_by'] = $this->user['id'];
        $model = new SupplierAgentModel();
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

    public function batchupdatestatusAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new SupplierModel();
        if (isset($data['ids']) && isset($data['status'])) {
            $arr_ids = explode(",", $data['ids']);
            $arr['status'] = $data['status'];
            if ($data['status'] == 'APPROVED' || $data['status'] == 'APPLING') {
                $arr['checked_by'] = $this->user['id'];
                $arr['checked_at'] = Date("Y-m-d H:i:s");
            }
            for ($i = 0; $i < count($arr_ids); $i++) {
                $where['id'] = $arr_ids[$i];
                $res = $model->update_data($arr, $where);
            }
            $datajson['code'] = 1;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $where['id'] = $data['id'];
            $where_account['supplier_id'] = $data['id'];
            $where_attach['supplier_id'] = $data['id'];
            $supplier_contact_where['supplier_id'] = $data['id'];
            $where_supplier_bank_info['supplier_id'] = $data['id'];
            $where_supplier_address['supplier_id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "id不能为空"));
        }
        if (!empty($data['supplier_type'])) {
            $arr['supplier_type'] = $data['supplier_type'];
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['lang'])) {
            $arr['lang'] = $data['lang'];
        }
        if (!empty($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
            $supplier_contact_data['first_name'] = $data['first_name'];
            $supplier_contact_data['created_by'] = $data['first_name'];
        }
        if (!empty($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
            $supplier_account_data['last_name'] = $data['last_name'];
            $supplier_contact_data['last_name'] = $data['last_name'];
        }
        if (!empty($data['mobile'])) {
            $supplier_account_data['mobile'] = $data['mobile'];
            $supplier_contact_data['mobile'] = $data['mobile'];
            $arr['official_phone'] = $data['mobile'];
        }
        if (!empty($data['email'])) {
            $supplier_account_data['email'] = $data['email'];
            if (!isEmail($supplier_account_data['email'])) {
                jsonReturn('', -101, '邮箱格式不正确!');
            }
            $arr['official_email'] = $data['email'];
            $supplier_contact_data['email'] = $data['email'];
        }
        if (!empty($data['country_code'])) {
            $arr['country_code'] = $data['country_code'];
        }
        if (!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['province'])) {
            $arr['province'] = $data['province'];
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
        if (isset($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if (!empty($data['status'])) {
            $arr['status'] = $data['status'];
            if ($data['status'] == 'APPROVED' || $data['status'] == 'REJECTED') {
                $arr['checked_by'] = $this->user['id'];
                $arr['checked_at'] = Date("Y-m-d H:i:s");
            }
        }
        if (!isset($data['barnd'])) {
            $brank_arr = explode(",", $data['brand']);
            $brand_json = [];
            for ($i = 0; $i < count($brank_arr); $i++) {
                $brand_modle = new BrandModel();
                if ($brank_arr[$i]) {
                    $brand_json[$i] = $brand_modle->info($brank_arr[$i]);
                    if ($brand_json[$i]) {
                        $brand_json[$i]['brand'] = json_decode($brand_json[$i]['brand'], true);
                        for ($j = 0; $j < count($brand_json[$i]['brand']); $j++) {
                            $brand_json[$i][$brand_json[$i]['brand'][$j]['lang']]['id'] = $brank_arr[$i];
                            $brand_json[$i][$brand_json[$i]['brand'][$j]['lang']]['style'] = $brand_json[$i]['brand'][$j]['style'];
                            $brand_json[$i][$brand_json[$i]['brand'][$j]['lang']]['label'] = $brand_json[$i]['brand'][$j]['label'];
                            $brand_json[$i][$brand_json[$i]['brand'][$j]['lang']]['logo'] = $brand_json[$i]['brand'][$j]['logo'];
                            $brand_json[$i][$brand_json[$i]['brand'][$j]['lang']]['lang'] = $brand_json[$i]['brand'][$j]['lang'];
                            $brand_json[$i][$brand_json[$i]['brand'][$j]['lang']]['name'] = $brand_json[$i]['brand'][$j]['name'];
                        }
                    }
                    unset($brand_json[$i]['brand']);
                }
            }
            $arr['brand'] = json_encode($brand_json, JSON_UNESCAPED_UNICODE);
        }
        // 生成供应商编码
        $model = new SupplierModel();
        $res = $model->update_data($arr, $where);
        if ($res !== false) {
            if (!empty($data['user_name'])) {
                $supplier_account_data['user_name'] = $data['user_name'];
            }
            if (!empty($data['password'])) {
                $supplier_account_data['password_hash'] = md5($data['password']);
            }
            if ($supplier_account_data) {
                $supplier_account = new SupplierAccountModel();
                $supplier_account->update_data($supplier_account_data, $where_account);
            }
            $supplier_attach = new SupplierAttachModel();
            if (!empty($data['license_attach_url'])) {
                $where_attach['attach_group'] = 'LICENSE';
                $supplier_attach_data['attach_url'] = $data['license_attach_url'];
                $supplier_attach_data['attach_name'] = $data['attach_name'];
                $supplier_attach->update_data($supplier_attach_data, $where_attach);
            }
            $supplier_contact = new SupplierContactModel();
            if ($supplier_contact_data) {
                $supplier_contact->update_data($supplier_contact_data, $supplier_contact_where);
            }
            if ($data['bank_name']) {
                $supplier_bank_info_data['bank_name'] = $data['bank_name'];
            }
            if ($data['bank_address']) {
                $supplier_bank_info_data['address'] = $data['bank_address'];
            }
            if ($data['bank_account']) {
                $supplier_bank_info_data['bank_account'] = $data['bank_account'];
            }
            if (isset($supplier_bank_info_data)) {
                $supplier_bank_info = new SupplierBankInfoModel();
                $supplier_bank_info->update_data($supplier_bank_info_data, $where_supplier_bank_info);
            }
            if ($data['address']) {
                $supplier_address_data['address'] = $data['address'];
                $supplier_address_model = new SupplierAddressModel();
                $supplier_address_model->update_data($supplier_address_data, $where_supplier_address);
            }
            if ($data['other_attach_url']) {
                $supplier_attach->deleteall(['supplier_id' => $data['id'], 'attach_group' => 'CERT']);
                for ($i = 0; $i < count($data['other_attach_url']); $i++) {
                    $supplier_attach_other_data['supplier_id'] = $data['id'];
                    $supplier_attach_other_data['attach_url'] = $data['other_attach_url'][$i];
                    $supplier_attach_other_data['attach_name'] = $data['other_attach_name'][$i];
                    $supplier_attach_other_data['attach_group'] = 'CERT';
                    $supplier_attach_other_data['created_by'] = $this->user['id'];
                    $supplier_attach_other_data['created_at'] = date("Y-m-d H:i:s");
                    $supplier_attach->create_data($supplier_attach_other_data);
                }
            }
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

    /*
     * 询报价选择关联SKU供应商列表
     * 张玉良
     * 2017-11-09
     */

    public function getSkuSupplierListAction() {
        $data = $this->put_data;

        /* if (empty($data['sku'])) {
          $datajson['code'] = -104;
          $datajson['message'] = 'SKU为空!';
          $this->jsonReturn($datajson);
          } */

        $model = new SupplierModel();
        $data = $model->getSkuSupplierList($data);

        foreach ($data['data'] as $key => $val) {
            if ($val['brand']) {
                $brand = json_decode($val['brand'], true);
                $data['data'][$key]['brand'] = $brand['name'];
            }
        }

        $this->jsonReturn($data);
    }

    /**
     * Description of 供应商审核
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function CheckedAction() {
        $supplier_id = $this->getPut('supplier_id');
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择供应商!');
            $this->jsonReturn();
        }
        if (!is_numeric($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID必须是数字!');
            $this->jsonReturn();
        }
        $org_model = new OrgModel();

        $condition['org_id'] = $org_model->getOrgIdsById($this->user['group_id']);
        if (!$condition['org_id']) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('您不属于易瑞或事业部,没有供应商审核权限!');
            $this->jsonReturn();
        }

        $supplier_model = new SupplierChainModel();
        $supplier = $supplier_model->field(['supplier_level,erui_status,status,org_id'])->where(['id' => $supplier_id, 'deleted_flag' => 'N'])->find();

        if (!$supplier) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商不存在!');
            $this->jsonReturn();
        }
        if (empty($supplier['org_id'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请先在编辑管理编辑页面选择事业部,再对供应商进行审核!');
            $this->jsonReturn();
        }

        if (!in_array($supplier['org_id'], $condition['org_id']) && !empty($supplier['org_id'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('您所属的事业部和供应商的事业部不匹配,不能对该供应商进行审核!');
            $this->jsonReturn();
        }

        $status = $this->getPut('status');
        if (empty($status)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择审核状态!');
            $this->jsonReturn();
        }


        $note = $this->getPut('note');

        $data = $supplier_model->Checked($supplier_id, $status, $note, $condition['org_id']);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
            $this->jsonReturn();
        }
    }

    /**
     * 已开发供应商数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getSupplierCountAction() {
        $condition = $this->getPut();
        $supplier_model = new SupplierChainModel();
        $total = $supplier_model->getCount($condition); //已开发供应商数量
        $this->setvalue('count', $total); //已开发供应商数量
        $condition['status'] = 'APPROVING';
        $CheckingCount = $supplier_model->getCount($condition); //待审核供应商数量
        $this->setvalue('checking_count', $CheckingCount); //待审核供应商数量
        $condition['status'] = 'APPROVED';
        $ValidCount = $supplier_model->getCount($condition); //已通过供应商数量
        $this->setvalue('valid_count', $ValidCount); //待审核供应商数量
        $condition['status'] = 'INVALID';
        $InvalidCount = $supplier_model->getCount($condition); //已驳回供应商数量
        $this->setvalue('invalid_count', $InvalidCount); //$InvalidCount
        $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('获取成功!');
        $this->jsonReturn();
    }

}
