<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LoginController
 *
 * @author  jhw
 */
class LoginController extends Yaf_Controller_Abstract {

//    public function __init() {
//        //   parent::__init();
//    }
    /*
     * 用户登录
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */
    public function loginAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['password'])){
            $arr['password'] = $data['password'];
        }else{
            echo json_encode(array("code" => "-101", "message" => "密码不可以都为空"));
            exit();
        }
        if(!empty($data['user_name'])){
            if(isEmail($data['user_name'])){
                $arr['email'] = $data['user_name'];
            }else{
                $arr['mobile'] = $data['user_name'];
            }
        }else{
            echo json_encode(array("code" => "-101", "message" => "帐号不可以都为空"));
            exit();
        }
        $model = new UserModel();
        $info = $model->login($arr);
        if ($info) {
            $jwtclient = new JWTClient();
            $jwt['id'] = $info['id'];
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['name'] = $info['name'];
            $datajson['mobile'] = $info['mobile'];
            $datajson['email'] = $info['email'];
            $datajson['name'] = $info['name'];
            $datajson['token'] = $jwtclient->encode($jwt); //加密
            redisSet('user_info_'.$info['id'],json_encode($info),18000);
            echo json_encode(array("code" => "1", "data" => $datajson, "message" => "登陆成功"));
            exit();
        } else {
            $datajson = [];
            echo json_encode(array("code" => "-104", "data" => $datajson, "message" => "登录失败"));
        }
    }
    /**
     * 用户注册
     * @created_date 2017-06-15
     * @update_date 2017-06-15
     * @author jhw
     */
    public function registerAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['description'])){
            $arr['description'] = $data['description'];
        }
        if(!empty($data['password'])) {
            $arr['password_hash'] = md5($data['password']);
        }else{
            echo json_encode(array("code" => "-101", "message" => "密码不可以都为空"));
            exit();
        }
        if(empty($data['group_id'])){
            echo json_encode(array("code" => "-101", "message" => "部门不能为空"));
            exit();
        }
        if(!empty($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
            if(!isMobile($arr['mobile'])){
                echo json_encode(array("code" => "-101", "message" => "手机格式不正确"));
                exit();
            }
        }else{
            echo json_encode(array("code" => "-101", "message" => "手机不可以都为空"));
            exit();
        }
        if(!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if(!isEmail($arr['email'])){
                echo json_encode(array("code" => "-101", "message" => "邮箱格式不正确"));
                exit();
            }
        }else{
            echo json_encode(array("code" => "-101", "message" => "邮箱不可以都为空"));
            exit();
        }
        if(!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }else{
            echo json_encode(array("code" => "-101", "message" => "用户名不能为空"));
            exit();
        }
        $model = new UserModel();
        $login_arr['email'] = $data['email'];
        $login_arr['mobile'] = $data['mobile'];
        $check = $model->Exist($login_arr);
        if($check){
            echo json_encode(array("code" => "-101", "message" => "手机或账号已存在"));
            exit();
        }
        // 生成用户编码
        $condition['page']=0;
        $condition['countPerPage']=1;
        $data_user = $model->getlist($condition); //($this->put_data);
        if($data_user&&substr($data_user[0]['user_no'],1,6) == date("ymd")){
            $no=substr($data_user[0]['user_no'],-1,3);
            $no++;
        }else{
            $no=1;
        }
        $temp_num = 1000;
        $new_num = $no + $temp_num;
        $real_num = "U".date("ymd").substr($new_num,1,3); //即截取掉最前面的“1”
        $arr['user_no'] = $real_num;
        $id=$model->create_data($arr);
        if($id){
            //添加部门
            $group['group_id'] = $data['group_id'];
            $group['user_id'] = $id;
            $group_user_model = new GroupUserModel();
            $group_user_model -> create_data($group);
            $arr['id'] = $id;
            echo json_encode(array("code" => "1", "data"=>$arr, "message" => "提交成功"));
            exit();
        }else{
            echo json_encode(array("code" => "-101", "message" => "数据添加失败"));
            exit();
        }
    }
    //获取部门信息
    public function groupListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['parent_id'])){
            $where['parent_id'] = $data['parent_id'];
        }
        if(!empty($data['name'])){
            $where['name'] = $data['name'];
        }
        if(!empty($data['page'])){
            $limit['page'] = $data['page'];
        }
        if(!empty($data['countPerPage'])){
            $limit['num'] = $data['countPerPage'];
        }
        $model_group = new GroupModel();
        $data = $model_group ->getlist($where,$limit); //($this->put_data);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -101;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }
        echo json_encode($datajson);
        exit();
    }

}
