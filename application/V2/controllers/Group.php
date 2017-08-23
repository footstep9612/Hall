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
class GroupController extends PublicController {

    public function __init() {
        //   parent::__init();
    }
    //递归获取子记录
    function get_group_children($a,$pid =null,$employee=null){
        if(!$pid){
            $pid =$a[0]['parent_id'];
        }
        $tree = array();
        $limit =[];
        if($employee){
            $user_modle =new UserModel();
        }
        foreach($a as $v){
            $model_group = new GroupModel();
            if($v['parent_id'] == $pid){
                $v['children'] = $this->get_group_children($model_group->getlist(['parent_id'=> $v['id']],$limit),$v['id'],$employee); //递归获取子记录
                if($v['children'] == null){
                    unset($v['children']);
                }
                if($employee){
                    $where_user['group_id'] = $v['id'];
                    $v['employee'] =$user_modle->getlist($where_user);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }
    public function listAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['name'])){
            $where['org.name'] = array('like',"%".$data['name']."%");
        }
        if(!empty($data['parent_id'])){
            $where['org.parent_id'] = $data['parent_id'];
        }else{
            if(empty($data['name'])){
                $where['org.parent_id'] = 0;
            }
        }
        $model_group = new GroupModel();
        $data = $model_group->getlist($where,$limit); //($this->put_data);
        $arr  = $this->get_group_children($data);
        if(!empty($arr)){
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $arr;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }

    public function homelistAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['name'])){
            $where['org.name'] = array('like',"%".$data['name']."%");
        }
        if(!empty($data['parent_id'])){
            $where['org.parent_id'] = $data['parent_id'];
            $where_user['group_id'] = $data['parent_id'];
        }else{
            if(empty($data['name'])){
                $where['org.parent_id'] = 0;
            }
        }
        $user_modle =new UserModel();
        $model_group = new GroupModel();
        $data = $model_group->getlist($where,$limit); //($this->put_data);
        if(!isset( $where_user['group_id'])){
            $where_user['group_id']=$data[0]['id'];
        }
        $data['employee'] =$user_modle->getlist($where_user);
        $arr  = $this->get_group_children($data,'',1);
        if(!empty($arr)){
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $arr;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }
    public function listallAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['parent_id'])){
            $where['org.parent_id'] = $data['parent_id'];
        }
        if(!empty($data['name'])){
            $where['org.name'] = array('like',"%".$data['name']."%");
        }
        $model_group = new GroupModel();
        $data = $model_group->getlist($where,$limit); //($this->put_data);
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
    public function adduserAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $where = [];
        if(!empty($data['group_id'])){
            $where['group_id'] = $data['group_id'];
        }else{
            $datajson['code'] = -101;
            $datajson['message'] = '组织id不能为空!';
        }
        if(!empty($data['user_ids'])){
            $where['user_ids'] = $data['user_ids'];
        }
        $model_group = new GroupUserModel();
        $data = $model_group->addusers($where); //($this->put_data);
        if(!empty($data)){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '操作失败!';
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
        $model_group = new GroupModel();
        $data = $model_group->detail($id);
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
        $data['created_by'] = $this->user['id'];
        $model_group = new GroupModel();
        $id = $model_group->create_data($data);
        if(!empty($id)){
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
        if($data[id]){
            $where['id'] =$data[id];
            $model_group = new GroupModel();
            $id = $model_group->update_data($data,$where);
            if(!empty($id)){
                $datajson['code'] = 1;
            }else{
                $datajson['code'] = -104;
                $datajson['data'] = $data;
                $datajson['message'] = '添加失败!';
            }
            $this->jsonReturn($datajson);
        }
    }
    public function deleteAction() {
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
        }
        $arr['deleted_flag'] = 'Y';
        $model_group = new GroupModel();
        $id = $model_group->update_data($arr,$where);
        if($id > 0){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '修改失败!';
        }
        $this->jsonReturn($datajson);
    }

}
