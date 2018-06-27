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
class BizlinesupplierController extends PublicController {


    public function __init() {
        //   parent::__init();
    }
    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['bizline_supplier'])) {
            if(empty($data['supplier_id'])) {
                jsonReturn('',-101,'采购商id不能为空!');
            }
            $bizline_supplier_model = new BizlineSupplierModel();
            $bizline_supplier_model->deletes(['supplier_id'=>$data['supplier_id']]);
           for($i=0;$i<count($data['bizline_supplier']);$i++){
                if($data['bizline_supplier'][$i]['id']){
                    $arr['bizline_id'] = $data['bizline_supplier'][$i]['id'];
                    $arr['bizline_id'] = $data['bizline_supplier'][$i]['id'];
                    $arr['supplier_id'] = $data['supplier_id'];
                    $arr['first_name'] = $data['bizline_supplier'][$i]['first_name'];
                    $arr['last_name'] = $data['bizline_supplier'][$i]['last_name'];
                    $arr['gender'] = $data['bizline_supplier'][$i]['gender'];
                    $arr['title'] = $data['bizline_supplier'][$i]['title'];
                    $arr['phone'] = $data['bizline_supplier'][$i]['phone'];
                    $arr['email'] = $data['bizline_supplier'][$i]['email'];
                    $arr['supply_level'] = $data['bizline_supplier'][$i]['supply_level'];
                    $arr['quote_group_id'] = $data['bizline_supplier'][$i]['quote_group_id'];
                    $bizline_supplier_model->create_data($arr);
                }
           }
            $datajson['code'] = 1;
            $datajson['message'] ='成功';
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }


    /*
     * 列表
     * */
    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['supplier_id'])){
            $where['supplier_id'] = $data['supplier_id'];
        }
        if(!empty($data['bizline_id'])){
            $where['bizline_id'] = $data['bizline_id'];
        }
        $model = new BizlineSupplierModel();
        $data =$model->getSupplierList($where);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
 * 列表
 * */
    public function supplierlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['supplier_id'])){
            $where['bizline_supplier.supplier_id'] = $data['supplier_id'];
        }
        if(!empty($data['supplier_name'])){
            $where[] = "sp.name like '%".$data['supplier_name']."%'";
        }
        if(!empty($data['bizline_id'])){
            $where['bizline_id'] = $data['bizline_id'];
        }
        if(!empty($data['sku'])){
            $where['sku'] = $data['sku'];
        }
        $model = new BizlineSupplierModel();
        $data =$model->getSupplierGoodsCostList($where);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
//
//    public function infoAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        $model = new SupplierModel();
//        $res = $model->info($data);
//        if($res['brand']){
//            $res['brand'] = json_decode($res['brand'],true);
//        }
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['data'] = $res;
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据为空!';
//        }
//        $this->jsonReturn($datajson);
//    }
//
//    public function accountinfoAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        $model = new SupplierAccountModel();
//        $res = $model->info($data);
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['data'] = $res;
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据为空!';
//        }
//        $this->jsonReturn($datajson);
//    }
//
//    public function attachinfoAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        $model = new SupplierAttachModel();
//        $res = $model->info($data);
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['data'] = $res;
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据为空!';
//        }
//        $this->jsonReturn($datajson);
//    }
//    public function bankinfoAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        $model = new SupplierBankInfoModel();
//        $res = $model->info($data);
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['data'] = $res;
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据为空!';
//        }
//        $this->jsonReturn($datajson);
//    }
//    public function addressinfoAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        $model = new SupplierAddressModel();
//        $res = $model->info($data);
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['data'] = $res;
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据为空!';
//        }
//        $this->jsonReturn($datajson);
//    }

//    public function agentlistAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        if(!empty($data['supplier_id'])) {
//            $array['supplier_id'] = $data['supplier_id'];
//        }
//        if(!empty($data['org_id'])) {
//            $array['org_id'] = $data['org_id'];
//        }
//        $model = new SupplierAgentModel();
//        $res = $model->getlist($array);
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['data'] = $res;
//            $datajson['message'] ='成功';
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据操作失败!';
//        }
//        $this->jsonReturn($datajson);
//    }
//    public function updateagentAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        if(!empty($data['id'])) {
//            $array['id'] = $data['id'];
//        }else{
//            $this->jsonReturn(array("code" => "-101", "message" => "会员id不能为空"));
//        }
//        if(!empty($data['org_ids'])) {
//            $array['org_ids'] = $data['org_ids'];
//        }else{
//            $this->jsonReturn(array("code" => "-101", "message" => "负责人id不能为空"));
//        }
//        $array['created_by'] = $this->user['id'];
//        $model = new SupplierAgentModel();
//        $res = $model->create_data($array);
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['message'] ='成功';
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据操作失败!';
//        }
//        $this->jsonReturn($datajson);
//    }
//
//    public function updateAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
//        if(!empty($data['id'])) {
//            $where['id'] = $data['id'];
//            $where_account['supplier_id'] = $data['id'];
//            $where_attach['supplier_id'] = $data['id'];
//            $supplier_contact_where['supplier_id'] = $data['id'];
//            $where_supplier_bank_info = $data['id'];
//            $where_supplier_address = $data['id'];
//        }else{
//            $this->jsonReturn(array("code" => "-101", "message" => "id不能为空"));
//        }
//        if(!empty($data['supplier_type'])) {
//            $arr['supplier_type'] = $data['supplier_type'];
//        }
//        if(!empty($data['name'])) {
//            $arr['name'] = $data['name'];
//        }
//        if(!empty($data['bn'])) {
//            $arr['bn'] = $data['bn'];
//        }
//        if(!empty($data['lang'])) {
//            $arr['lang'] = $data['lang'];
//        }
//        if(!empty($data['lang'])) {
//            $arr['lang'] = $data['lang'];
//        }
//        if(!empty($data['first_name'])) {
//            $arr['first_name'] = $data['first_name'];
//            $supplier_contact_data['first_name']= $data['first_name'];
//            $supplier_contact_data['created_by']=  $data['first_name'];
//        }
//        if(!empty($data['last_name'])) {
//            $arr['last_name'] = $data['last_name'];
//            $supplier_account_data['last_name']= $data['last_name'];
//            $supplier_contact_data['last_name']=  $data['last_name'];
//        }
//        if(!empty($data['mobile'])) {
//            $supplier_account_data['mobile'] = $data['mobile'];
//            $supplier_contact_data['mobile']=$data['mobile'];
//            $arr['official_phone'] = $data['mobile'];
//        }
//        if(!empty($data['email'])) {
//            $supplier_account_data['email'] = $data['email'];
//            if(!isEmail($supplier_account_data['email'])){
//                jsonReturn('',-101,'邮箱格式不正确!');
//            }
//            $arr['official_email'] = $data['email'];
//            $supplier_contact_data['email']=$data['email'];
//        }
//        if(!empty($data['country_code'])) {
//            $arr['country_code'] = $data['country_code'];
//        }
//        if(!empty($data['country_bn'])) {
//            $arr['country_bn'] = $data['country_bn'];
//        }
//        if(!empty($data['province'])) {
//            $arr['province'] = $data['province'];
//        }
//        if(!empty($data['logo'])) {
//            $arr['logo'] = $data['logo'];
//        }
//        if(!empty($data['social_credit_code'])) {
//            $arr['social_credit_code'] = $data['social_credit_code'];
//        }
//        if(!empty($data['profile'])) {
//            $arr['profile'] = $data['profile'];
//        }
//        if(!empty($data['reg_capital'])) {
//            $arr['reg_capital'] = $data['reg_capital'];
//        }
//        if(!empty($data['employee_count'])) {
//            $arr['employee_count'] = $data['employee_count'];
//
//        }
//        if(!empty($data['status'])) {
//            $arr['status'] = $data['status'];
//
//        }
//        if(!isset($data['brand'])) {
//           $brank_arr =  explode(",",$data['brand']) ;
//            for($i=0;$i<count($brank_arr);$i++){
//                $brand_modle = new BrandModel();
//                $brand_json[$i] = $brand_modle->info($brank_arr[$i]);
//                if($brand_json[$i]){
//                    $brand_json[$i]['brand']=json_decode($brand_json[$i]['brand'],true);
//                    for($j=0;$j<count($brand_json[$i]['brand']);$j++){
//                        $brand_json[$i][ $brand_json[$i]['brand'][$j]['lang']]['id'] =$brank_arr[$i];
//                        $brand_json[$i][ $brand_json[$i]['brand'][$j]['lang']]['style'] =$brand_json[$i]['brand'][$j]['style'];
//                        $brand_json[$i][ $brand_json[$i]['brand'][$j]['lang']]['label'] =$brand_json[$i]['brand'][$j]['label'];
//                        $brand_json[$i][ $brand_json[$i]['brand'][$j]['lang']]['logo'] =$brand_json[$i]['brand'][$j]['logo'];
//                        $brand_json[$i][ $brand_json[$i]['brand'][$j]['lang']]['lang'] =$brand_json[$i]['brand'][$j]['lang'];
//                        $brand_json[$i][ $brand_json[$i]['brand'][$j]['lang']]['name'] =$brand_json[$i]['brand'][$j]['name'];
//                    }
//                }
//                unset($brand_json[$i]['brand']);
//            }
//            $arr['brand'] = json_encode($brand_json,JSON_UNESCAPED_UNICODE);
//        }
//        // 生成供应商编码
//        $model  =  new SupplierModel();
//        $res=$model->update_data($arr,$where);
//        if($res!==false){
//            if(!empty($data['user_name'])) {
//                $supplier_account_data['user_name']=$data['user_name'];
//            }
//            if(!empty($data['password'])) {
//                $supplier_account_data['password_hash']=md5($data['password']);
//            }
//            if($supplier_account_data) {
//                $supplier_account = new SupplierAccountModel();
//                $supplier_account ->update_data($supplier_account_data,$where_account);
//            }
//
//            $supplier_attach = new SupplierAttachModel();
//            if(!empty($data['license_attach_url'])){
//                $where_attach['attach_group'] ='LICENSE';
//                $supplier_attach_data['license_attach_url'] = $data['license_attach_url'];
//                $supplier_attach_data['attach_name'] = $data['attach_name'];
//                $supplier_attach ->update_data($supplier_attach_data,$where_attach);
//            }
//            //
//            $supplier_contact = new SupplierContactModel();
//            if($supplier_contact_data){
//                $supplier_contact ->update_data($supplier_contact_data,$supplier_contact_where);
//            }
//            if($data['bank_name']){
//                $supplier_bank_info_data['bank_name'] = $data['bank_name'];
//            }
//            if($data['address']){
//                $supplier_bank_info_data['address'] = $data['brand_address'];
//            }
//            if($data['bank_account']){
//                $supplier_bank_info_data['bank_account'] = $data['bank_account'];
//            }
//            if(isset($supplier_bank_info_data)){
//                $supplier_bank_info = new SupplierBankInfoModel();
//                $supplier_bank_info ->update_data($supplier_bank_info_data,$where_supplier_bank_info);
//
//            }
//            if($data['address']){
//                $supplier_address_data['address'] = $data['address'];
//                $supplier_address_model = new SupplierAddressModel();
//                $supplier_address_model ->update_data($supplier_address_data, $where_supplier_address);
//            }
//            if($data['other_attach_url']){
//                $supplier_attach ->deleteall(['supplier_id'=>$data['id'],'attach_group'=>'CERT']);
//                for($i=0;$i<count($data['other_attach_url']);$i++){
//                    $supplier_attach_other_data['supplier_id'] = $data['id'];
//                    $supplier_attach_other_data['license_attach_url'] = $data['other_attach_url'][$i];
//                    $supplier_attach_other_data['attach_name'] = $data['other_attach_name'][$i];
//                    $supplier_attach_other_data['attach_group'] = 'CERT';
//                    $supplier_attach_other_data['created_by']= $this->user['id'];
//                    $supplier_attach_other_data['created_at']= date("Y-m-d H:i:s");
//                    $supplier_attach ->create_data($supplier_attach_other_data);
//                }
//            }
//            $datajson['code'] = 1;
//            $datajson['message'] ='成功';
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据操作失败!';
//        }
//        $this->jsonReturn($datajson);
//        $model = new BuyerModel();
//        $model -> update_data($arr,$where);
//        $buyer_account_model = new BuyerAccountModel();
//        if(!empty($data['password'])) {
//            $arr_account['password_hash'] = md5($data['password']);
//            $buyer_account_model -> update_data($arr_account,$where_account);
//        }
//        $buyer_attach_model =  new BuyerAttachModel();
//        if(!empty($data['attach_url'])) {
//            $where_attach['attach_url'] = $data['attach_url'];
//            $buyer_attach_model -> update_data($where_attach);
//        }
//        $model = new UserModel();
//        $res = $model->update_data($arr,$where);
//        if(!empty($res)){
//            $datajson['code'] = 1;
//            $datajson['message'] ='成功';
//        }else{
//            $datajson['code'] = -104;
//            $datajson['data'] = "";
//            $datajson['message'] = '数据操作失败!';
//        }
//        $this->jsonReturn($datajson);
//    }
//
//
//    public function getRoleAction(){
//        if($this->user['id']){
//            $role_user = new RoleUserModel();
//            $where['user_id'] = $this->user['id'];
//            $data = $role_user->getRoleslist($where);
//            $datajson = array(
//                'code' => 1,
//                'message' => '数据获取成功',
//                'data' => $data
//            );
//            jsonReturn($datajson);
//        }else{
//            $datajson = array(
//                'code' => -104,
//                'message' => '用户验证失败',
//            );
//        }
//    }


}
