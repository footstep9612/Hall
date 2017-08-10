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
    public function listAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        $where['parent_id'] = 0;
        $where['deleted_flag'] = 'N';
        $model_group = new GroupModel();
        $data = $model_group->getlist($where,$limit); //($this->put_data);
        $count = count($data);
        $childrencount=0;
        for($i=0;$i<$count;$i++){
            $data[$i]['children'] = $model_group->getlist(['parent_id'=> $data[$i]['id'],'deleted_flag'=>'N'],$limit);
            $childrencount = count($data[$i]['children']);
            if($childrencount>0){
                for($j=0;$j<$childrencount;$j++){
                    if(isset($data[$i]['children'][$j]['id'])){
                        $data[$i]['children'][$j]['children'] = $model_group->getlist(['parent_id'=> $data[$i]['children'][$j]['id'],'deleted_flag'=>'N'],$limit);
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
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }
    public function listallAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['parent_id'])){
            $where['parent_id'] = $data['parent_id'];
        }
        if(!empty($data['name'])){
            $where['name'] = array('like',"%".$data['name']."%");
        }
        $where['deleted_flag'] = 'N';
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
