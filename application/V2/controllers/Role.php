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
class RoleController extends PublicController {

    public function __init() {
        //   parent::__init();

    }

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['name'])){
            $where['name'] = array('like','%'.$data['name'].'%');
        }
        if(!empty($data['pageSize'])){
            $limit['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])){
            $limit['page'] = ($data['currentPage']-1)* $limit['num'];
        }
        $model_rolo = new RoleModel();
        $data = $model_rolo->getlist($where,$limit);
        if($limit){
            $count = $model_rolo->getcount($where);
            $datajson['count'] = $count;
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
    public function roleuserAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_role_user = new RoleUserModel();
        $data = $model_role_user->getRolesUserlist($id);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);

    }
    public function roleuserdeleteAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_role_user = new RoleUserModel();
        $res = $model_role_user->delete_data($id);
        if($res){
            $datajson['code'] = 1;
            $datajson['message'] = '删除成功!';
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '删除失败!';
        }

        $this->jsonReturn($datajson);

    }

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_rolo = new RoleModel();
        $data = $model_rolo->detail($id);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    public function permlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_rolo = new RoleModel();
        $data = $model_rolo->getRoleslist($id);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data)){
            $datajson['code'] = -101;
            $datajson['message'] = '数据不可为空!';
            $this->jsonReturn($datajson);
        }
        if(empty($data['name'])){
            $datajson['code'] = -101;
            $datajson['message'] = '权限名称不可为空!';
            $this->jsonReturn($datajson);
        }
        $model_rolo = new RoleModel();
        $id = $model_rolo->create_data($data);

        if(!empty($id)){
            if($data['url_perm_ids']){
                $model_role_access_perm = new RoleAccessPermModel();
                $role_arr['url_perm_ids'] = $data['url_perm_ids'];
                $role_arr['role_id'] = $id;
                $model_role_access_perm->update_datas($role_arr);
            }
            if( $data['role_user_ids']){
                $model_role_user = new RoleUserModel();
                $role_user_arr['role_user_ids'] = $data['role_user_ids'];
                $role_user_arr['role_id'] = $id;
                $model_role_user->update_datas($role_arr);
            }
            $datajson['code'] = 1;
            $datajson['data']['id'] = $id;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '添加失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data)){
            $datajson['code'] = -101;
            $datajson['message'] = '数据不可为空!';
            $this->jsonReturn($datajson);
        }
        if(empty($data['id'])){
            $datajson['code'] = -101;
            $datajson['message'] = '缺少主键!';
            $this->jsonReturn($datajson);
        }else{
            $where['id'] = $data['id'];
            $role_arr['role_id'] = $data['id'];
            $role_user_arr['role_id'] = $data['id'];

        }
        if(isset($data['name'])){
            $arr['name'] = $data['name'];
        }
        if(isset($data['remarks'])){
            $arr['remarks'] = $data['remarks'];
        }
        if(isset($data['status'])){
            $arr['status'] = $data['status'];
        }
        $model_rolo = new RoleModel();
        $model_rolo->update_data($arr,$where);
        $model_role_access_perm = new RoleAccessPermModel();
        $role_arr['url_perm_ids'] = $data['url_perm_ids'];
        $model_role_access_perm->update_datas($role_arr);
        $model_role_user = new RoleUserModel();
        $role_user_arr['role_user_ids'] = $data['role_user_ids'];
        $model_role_user->update_datas($role_user_arr);
        $datajson['code'] = 1;
        $datajson['message'] = '操作完成!';
        $this->jsonReturn($datajson);
    }

    public function deleteAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_rolo = new RoleModel();
        $re = $model_rolo->delete_data($id);
        if($re > 0){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

}
