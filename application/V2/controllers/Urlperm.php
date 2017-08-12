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
class UrlpermController extends PublicController {

    public function __init() {
        //   parent::__init();
    }

    public function listAction() {
        //$data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $model_url_perm = new UrlPermModel();
        $data = $model_url_perm->getlist(['parent_id'=>0],$limit); //($this->put_data);
        $count = count($data);
        $childrencount=0;
        for($i=0;$i<$count;$i++){
            $data[$i]['check'] =false ;
            $data[$i]['children'] = $model_url_perm->getlist(['parent_id'=> $data[$i]['id']],$limit);
            $childrencount = count($data[$i]['children']);
            if($childrencount>0){
                for($j=0;$j<$childrencount;$j++){
                    if(isset($data[$i]['children'][$j]['id'])){
                        $data[$i]['children'][$j]['check'] =false ;
                        $data[$i]['children'][$j]['children'] = $model_url_perm->getlist(['parent_id'=> $data[$i]['children'][$j]['id']],$limit);
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
    public function listallAction() {
        //$data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $model_url_perm = new UrlPermModel();
        $data = $model_url_perm->getlist([],$limit); //($this->put_data);
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
    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_url_perm = new UrlPermModel();
        $data = $model_url_perm->detail($id);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
            $datajson['message'] = '获取成功!';
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
        if(!isset($data['url'])){
            $datajson['code'] = -101;
            $datajson['message'] = '地址不可为空!';
            $this->jsonReturn($datajson);
        }
        if(!isset($data['fn'])){
            $datajson['code'] = -101;
            $datajson['message'] = '方法名不可为空!';
            $this->jsonReturn($datajson);
        }

        $data['created_by']=$this->user['id'];
        $model_url_perm = new UrlPermModel();
        $id = $model_url_perm->create_data($data);
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
        if(empty($data['id'])){
            $datajson['code'] = -101;
            $datajson['message'] = '缺少主键!';
            $this->jsonReturn($datajson);
        }else{
            $where['id'] = $data['id'];
        }
        $model_url_perm = new UrlPermModel();
        $id = $model_url_perm->update_data($data,$where);
        if($id > 0){
            $datajson['code'] = 1;
            $datajson['message'] = '修改成功!';
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '修改失败!';
        }
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
        $model_url_perm = new UrlPermModel();
        $re = $model_url_perm->delete_data($id);
        if($re > 0){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }


}
