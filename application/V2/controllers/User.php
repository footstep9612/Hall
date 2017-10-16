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
        //   parent::__init();


    }
    /*
     * 用户列表
     * */
    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['username'])){
            $where['username'] = $data['username'];
        }
        if(!empty($data['group_id'])){
            $where['group_id'] = $data['group_id'];
        }
        if(!empty($data['role_id'])){
            $where['role_id'] = $data['role_id'];
        }
        if(!empty($data['role_name'])){
            $where['role_name'] = $data['role_name'];
        }
        if(!empty($data['status'])){
            $where['status'] = $data['status'];
        }
        if(!empty($data['gender'])){
            $where['gender'] = $data['gender'];
        }
        if(!empty($data['employee_flag'])){
            $where['employee_flag'] = $data['employee_flag'];
        }
        if(!empty($data['pageSize'])){
            $where['num'] = $data['pageSize'];
        }
        if(!empty($data['mobile'])){
            $where['mobile'] = $data['mobile'];
        }
        if(!empty($data['user_no'])){
            $where['user_no'] = $data['user_no'];
        }
        if(!empty($data['bn'])){
            $where['bn'] = $data['bn'];
        }
        if(!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        $user_modle =new UserModel();
        $data =$user_modle->getlist($where);
        $count =$user_modle->getcount($where);
        if(!empty($data)){
            $datajson['code'] = 1;
            if($count){
                $datajson['count'] =$count[0]['num'];
            }else{
                $datajson['count'] =0;
            }
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
     * 用户角色列表
     *
     * */
    public function userroleAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if($data['user_id']){
            $user_id = $data['user_id'];
        }else{
            $user_id = $this->user['id'];
        }
        $role_user_modle =new RoleUserModel();
        $data =$role_user_modle->userRole($user_id);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
     * 用户国家列表
     *
     * */
    public function usercountryAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if($data['user_id']){
            $user_id = $data['user_id'];
        }else{
            $user_id = $this->user['id'];
        }
        $role_cuntry_modle = new CountryUserModel();
        $data =$role_cuntry_modle->userCountry($user_id);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
     * 用户部门列表
     *
     * */
    public function usergroupAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if($data['user_id']){
            $user_id = $data['user_id'];
        }else{
            $user_id = $this->user['id'];
        }
        $role_group_modle = new GroupUserModel();
        $data =$role_group_modle->getlist(['user_id'=>$user_id]);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
     * 用户权限列表
     *
     * */
    public function userrolelistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $role_user_modle =new RoleUserModel();
        $data =$role_user_modle->userRoleList($this->user['id']);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
     * 用户列表
     *
     * */
    public function userrolelisttreeAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $role_user_modle =new RoleUserModel();
        if(isset($data['user_id'])){
            $user_id = $data['user_id'];
        }else{
            $user_id=$this->user['id'];
        }
        $data =$role_user_modle->userRoleList($user_id,0);
        $count = count($data);
        $childrencount=0;
        for($i=0;$i<$count;$i++){
            $data[$i]['check'] =false ;
            $data[$i]['children'] = $role_user_modle->userRoleList($user_id,$data[$i]['func_perm_id']);
            $childrencount = count($data[$i]['children']);
            if($childrencount>0){
                for($j=0;$j<$childrencount;$j++){
                    if(isset($data[$i]['children'][$j]['id'])){
                        $data[$i]['children'][$j]['check'] =false ;
                        $data[$i]['children'][$j]['children'] = $role_user_modle->userRoleList($data['user_id'],$data[$i]['children'][$j]['func_perm_id']);
                        if(!$data[$i]['children'][$j]['children']){
                            unset($data[$i]['children'][$j]['children']);
                        }
                    }
                }
            }else{
                unset($data[$i]['children']);
            }
        }
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /*
    * 用户列表
    *
    * */
    public function updatepasswordAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $arr['id'] = $this->user['id'];
        $arr['password_hash'] = md5($data['old_password']);
        $new_passwoer['password_hash'] = md5($data['password']);
        $user_modle =new UserModel();
        $data =$user_modle->infoList($arr);
        if($data){
            $res =$user_modle->update_data($new_passwoer,$arr);
            if($res!==false){
                $datajson['code'] = 1;
            }else{
                $datajson['code'] = -104;
                $datajson['message'] = '修改失败!';
            }
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '原密码错误!';
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
        if(!empty($res)){
            unset($res['password_hash']);
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
        if(!empty($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
            if(!isMobile($arr['mobile'])){
                $this->jsonReturn(array("code" => "-101", "message" => "手机格式不正确"));
            }
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "手机不可以都为空"));
        }
        if(!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if(!isEmail($arr['email'])){
                $this->jsonReturn(array("code" => "-101", "message" => "邮箱格式不正确"));
            }
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "邮箱不可以都为空"));
        }
        if(!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "用户名不能为空"));
        }
        if(!empty($data['name_en'])) {
            $arr['name_en'] = $data['name_en'];
        }
        if(!empty($data['gender'])) {
            $arr['gender'] = $data['gender'];
        }
        if(!empty($data['mobile2'])) {
            $arr['mobile2'] = $data['mobile2'];
        }
        if(!empty($data['show_name'])) {
            $arr['show_name'] = $data['show_name'];
        }
        if(!empty($data['phone'])) {
            $arr['phone'] = $data['phone'];
        }
        if(!empty($data['ext'])) {
            $arr['ext'] = $data['ext'];
        }
        if(!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if(!empty($data['employee_flag'])) {
            $arr['employee_flag'] = $data['employee_flag'];
        }else{
            $arr['employee_flag'] = "I";
        }
        $password = randStr(6);
        $arr['password_hash'] = md5($password);
        $model = new UserModel();
        if($arr['employee_flag']=="O"){
            $condition['page']=0;
            $condition['countPerPage']=1;
            $condition['employee_flag']='O';
            $data_t = $model->getlist($condition); //($this->put_data);
            if($data_t){
                $no=substr($data_t[0]['user_no'],-1,9);
                $no++;
            }else{
                $no=1;
            }
            $temp_num = 1000000000;
            $new_num = $no + $temp_num;
            $real_num = date("Ymd").substr($new_num,1,9); //即截取掉最前面的“1”
            $arr['user_no'] = $real_num;
        }else{
            if(!empty($data['user_no'])) {
                $arr['user_no'] = $data['user_no'];
            }else{
                $this->jsonReturn(array("code" => "-101", "message" => "用户编号不能为空"));
            }
        }
        $arr['created_by'] = $this->user['id'];
        $arr['user_no'] = $data['user_no'];
        $check = $model->Exist($arr);
        if($check){
            $this->jsonReturn(array("code" => "-101", "message" => "用户已存在"));
        }
        $res=$model->create_data($arr);
        if($res){
            if( $data['role_ids']){
                $model_role_user = new RoleUserModel();
                $role_user_arr['user_id'] = $res;
                $role_user_arr['role_ids'] = $data['role_ids'];
                $model_role_user->update_role_datas($role_user_arr);
            }
            if( $data['group_ids']){
                $model_group_user = new GroupUserModel();
                $group_user_arr['user_id'] = $res;
                $group_user_arr['group_ids'] = $data['group_ids'];
                $model_group_user->addGroup($group_user_arr);
            }
            if( $data['country_bns']){
                $model_country_user = new CountryUserModel();
                $country_user_arr['user_id'] = $res;
                $country_user_arr['country_bns'] = $data['country_bns'];
                $model_country_user->addCountry($country_user_arr);
            }
           // $body = $this->getView()->render('login/email.html', $email_arr);
            send_Mail($arr['email'], '帐号创建成功', "密码：".$password, $arr['name']);
        }
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = [ 'id'=>$res ];
            $datajson['message'] ='成功';
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    public function resetpasswordAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['id'])) {
            $where['id'] = $data['id'];
            $user_modle = new UserModel();
            $info  = $user_modle->info($data['id']);
            if(!$info){
                $this->jsonReturn(array("code" => "-101", "message" => "用户id不正确"));
            }
            $password = randStr(6);
            $arr['password_hash'] = md5($password);
            $res = $user_modle->update_data($arr,$where);
            if(!empty($res)){
                send_Mail($info['email'], '密码重置成功', "新密码：".$password, $info['name']);
                $datajson['code'] = 1;
                $datajson['message'] ='成功';
            }else{
                $datajson['code'] = -104;
                $datajson['data'] = "";
                $datajson['message'] = '修改失败!';
            }
            $this->jsonReturn($datajson);
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
        }
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['password'])) {
            $arr['password_hash'] = md5($data['password']);
        }
        if(!empty($data['id'])) {
            $where['id'] = $data['id'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
        }
        if(!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if(!isEmail($arr['email'])){
                $this->jsonReturn(array("code" => "-101", "message" => "邮箱格式不正确"));
            }
        }
        if(!empty($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
            if(!isMobile($arr['mobile'])){
                $this->jsonReturn(array("code" => "-101", "message" => "手机格式不正确"));
            }
        }
        if(!empty($data['show_name'])) {
            $arr['show_name'] = $data['show_name'];
        }
        if(!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if(!empty($data['name_en'])) {
            $arr['name_en'] = $data['name_en'];
        }
        if(!empty($data['gender'])) {
            $arr['gender'] = $data['gender'];
        }
        if(!empty($data['mobile2'])) {
            $arr['mobile2'] = $data['mobile2'];
        }
        if(!empty($data['phone'])) {
            $arr['phone'] = $data['phone'];
        }
        if(!empty($data['ext'])) {
            $arr['ext'] = $data['ext'];
        }
        if(!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if(!empty($data['user_no'])) {
            $arr['user_no'] = $data['user_no'];
        }
        if(!empty($data['status'])) {
            $arr['status'] = $data['status'];
        }
        $model = new UserModel();
        $res = $model->update_data($arr,$where);
        if($res!==false){
            if( $data['role_ids']){
                $model_role_user = new RoleUserModel();
                $role_user_arr['user_id'] = $where['id'];
                $role_user_arr['role_ids'] = $data['role_ids'];
                $model_role_user->update_role_datas($role_user_arr);
            }
            if( $data['group_ids']){
                $model_group_user = new GroupUserModel();
                $group_user_arr['user_id'] = $where['id'];
                $group_user_arr['group_ids'] = $data['group_ids'];
                $model_group_user->addGroup($group_user_arr);
            }
            if( $data['country_bns']){
                $model_country_user = new CountryUserModel();
                $country_user_arr['user_id'] = $where['id'];
                $country_user_arr['country_bns'] = $data['country_bns'];
                $model_country_user->addCountry($country_user_arr);
            }
            // $body = $this->getView()->render('login/email.html', $email_arr);
        }
        if($res!==false){
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
