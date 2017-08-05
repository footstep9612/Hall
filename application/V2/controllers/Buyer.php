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
        //   parent::__init();


    }
    /*
     * 用户列表
     * */
    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['name'])){
            $where['name'] = $data['name'];
        }
        if(!empty($data['country_bn'])){
            $where['country_bn'] = $data['country_bn'];
        }
        if(!empty($data['pageSize'])){
            $where['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        $model = new BuyerModel();
        $data =$model->getlist($where);
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
         * 用户详情
         * */
    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerModel();
        $res = $model->info($data);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
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
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['user_name'])) {
            $buyer_account_data['user_name'] = $data['user_name'];
        }else{
            jsonReturn('',-101,'用户名不可以为空!');
        }
        if(!empty($data['password'])) {
            $buyer_account_data['password_hash'] = md5(trim($data['password']));
        }else{
            jsonReturn('',-101,'密码不可以都为空!');
        }
        if(!empty($data['phone'])) {
            $buyer_account_data['mobile'] = $data['mobile'];
        }
        if(!empty($data['email'])) {
            $buyer_account_data['email'] = $data['email'];
            if(!isEmail($buyer_account_data['email'])){
                jsonReturn('',-101,'邮箱格式不正确!');
            }
            $arr['official_email'] = $data['email'];
        }else{
            jsonReturn('',-101,'邮箱不可以都为空!');
        }

        if(!empty($data['first_name'])) {
            $buyer_account_data['first_name'] = $data['first_name'];
        }else{
            jsonReturn('',-101,'名字不能为空!');
        }
        if(!empty($data['last_name'])) {
            $buyer_account_data['last_name'] = $data['last_name'];
        }
        if(!empty($data['mobile'])) {
            $buyer_account_data['mobile'] = $data['mobile'];
            $arr['official_phone'] = $data['mobile'];

        }

        $buyer_account_data['created_at'] = $this->user['id'];
        //附件
        if(!empty($data['attach_url'])) {
            $buyer_attach_data['attach_url'] = $data['attach_url'];
        }
        if(isset($buyer_attach_data)){
            $buyer_attach_data['created_by'] = $this->user['id'];
            $buyer_attach_data['created_at'] =date("Y-m-d H:i:s");
            $buyer_attach_data['attach_name'] =$data['name'].'营业执照';
        }
        $buyer_contact_data['mobile'] = $data['mobile'];
        $buyer_contact_data['email'] = $data['email'];
        $buyer_contact_data['last_name'] = $data['last_name'];
        $buyer_contact_data['first_name'] = $data['first_name'];
        if(!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }else{
            jsonReturn('',-101,'名称不能为空!');
        }
        if(!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if(!empty($data['province'])) {
            $arr['province'] = $data['province'];
        }
        if(!empty($data['country_code'])) {
            $arr['country_code'] = $data['country_code'];
        }
        if(!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        }else{
            jsonReturn('',-101,'国家名不可为空!');
        }
        if(!empty($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
        }else{
            jsonReturn('',-101,'名字不能为空!');
        }
        if(!empty($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
        }
        if(!empty($data['zipcode'])) {
            $buyer_address_data['zipcode'] = $data['zipcode'];
        }
        if(!empty($data['address'])) {
            $buyer_address_data['address'] = $data['address'];
        }
        $arr['created_at'] = $this->user['id'];
        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();
        $login_arr['email'] = $data['email'];
        $login_arr['user_name'] = $data['user_name'];
        $check = $buyer_account_model->Exist($login_arr);
        if($check){
            jsonReturn('',-101,'The company email or user name already exists.');
        }
        // 生成用户编码
        $condition['page']=0;
        $condition['countPerPage']=1;
        $data_t_buyer = $model->getlist($condition); //($this->put_data);
        //var_dump($data_t_buyer);die;
        if($data_t_buyer&&substr($data_t_buyer[0]['buyer_no'],1,8) == date("Ymd")){
            $no=substr($data_t_buyer[0]['buyer_no'],-1,6);
            $no++;
        }else{
            $no=1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = "C".date("Ymd").substr($new_num,1,6); //即截取掉最前面的“1”
        $arr['buyer_no'] = $real_num;
        if(empty($arr['serial_no'])){
            $arr['serial_no'] = $arr['buyer_no'];
        }
        $arr['created_by'] = $this->user['id'];

        $id=$model->create_data($arr);
        if($id){
            $buyer_account_data['buyer_id'] = $id;
            if(!empty($buyer_address_data)){
                $buyer_address_data['buyer_id'] = $id;
            }
            if(!empty($buyer_attach_data)){
                $buyer_attach_data['buyer_id'] = $id;
            }
            $buyer_contact_data['buyer_id'] = $id;
            //添加联系人
            $buyer_contact_model =  new BuyerContactModel();
            $buyer_contact_model ->create_data($buyer_contact_data);
            //添加附件
            $buyer_attach_model =  new BuyerAttachModel();
            $buyer_attach_model ->create_data($buyer_attach_data);
            //采购商帐号表
            $buyer_account_model ->create_data($buyer_account_data);
            if(!empty($buyer_address_data)){
                $buyer_address_model = new BuyerAddressModel();
                $buyer_address_model -> create_data($buyer_address_data);
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
    public function agentlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['buyer_id'])) {
            $array['buyer_id'] = $data['buyer_id'];
        }
        if(!empty($data['agent_id'])) {
            $array['agent_id'] = $data['agent_id'];
        }
        $model = new BuyerAgentModel();
        $res = $model->getlist($array);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
            $datajson['message'] ='成功';
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }
    public function updateagentAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['id'])) {
            $array['id'] = $data['id'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "会员id不能为空"));
        }
        if(!empty($data['user_ids'])) {
            $array['user_ids'] = $data['user_ids'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "负责人id不能为空"));
        }
        $array['created_by'] = $this->user['id'];
        $model = new BuyerAgentModel();
        $res = $model->create_data($array);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['message'] ='成功';
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['id'])) {
            $where['id'] = $data['id'];
            $where_account['buyer_id'] = $data['buyer_id'];
            $where_attach['buyer_id'] = $data['buyer_id'];

        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
        }
        if(!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if(!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if(!empty($data['province'])) {
            $arr['province'] = $data['province'];
        }
        if(!empty($data['country_code'])) {
            $arr['country_code'] = $data['country_code'];
        }
        if(!empty($data['country_bn'])) {
            $arr['country_bn'] = $data['country_bn'];
        }
        if(!empty($data['first_name'])) {
            $arr['first_name'] = $data['first_name'];
        }
        if(!empty($data['last_name'])) {
            $arr['last_name'] = $data['last_name'];
        }
        if(!empty($data['email'])) {
            $arr['official_email'] = $data['email'];
        }
        if(!empty($data['mobile'])) {
            $arr['official_phone'] = $data['mobile'];
        }
        $model = new BuyerModel();
        $model -> update_data($arr,$where);
        $buyer_account_model = new BuyerAccountModel();
        if(!empty($data['password'])) {
            $arr_account['password_hash'] = md5($data['password']);
            $buyer_account_model -> update_data($arr_account,$where_account);
        }
        $buyer_attach_model =  new BuyerAttachModel();
        if(!empty($data['attach_url'])) {
            $where_attach['attach_url'] = $data['attach_url'];
            $buyer_attach_model -> update_data($where_attach);
        }
        $model = new UserModel();
        $res = $model->update_data($arr,$where);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['message'] ='成功';
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }


    public function getRoleAction(){
        if($this->user['id']){
            $role_user = new RoleUserModel();
            $where['user_id'] = $this->user['id'];
            $data = $role_user->getRoleslist($where);
            $datajson = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $data
            );
            jsonReturn($datajson);
        }else{
            $datajson = array(
                'code' => -104,
                'message' => '用户验证失败',
            );
        }
    }



}
