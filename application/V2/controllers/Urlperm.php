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
    //递归获取子记录
    function get_urlperm_children($a,$pid =null,$employee=null){
        if(!$pid){
            $pid =$a[0]['parent_id'];
        }
        $tree = array();
        $limit =[];
        $model_group = new UrlPermModel();
        foreach($a as $v){
            $v['check']= false;
            if($v['parent_id'] == $pid){
                $v['children'] = $this->get_urlperm_children($model_group->getlist(['parent_id'=> $v['id']],$limit),$v['id'],$employee); //递归获取子记录
                if($v['children'] == null ){
                    unset($v['children']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public function listAction() {
        //$data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $model_url_perm = new UrlPermModel();
        $data = $model_url_perm->getlist(['parent_id'=>0,'source'=>'BOSS'],$limit); //($this->put_data);
        $count = count($data);
        $res = $this -> get_urlperm_children($data);

        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['count'] = $count;
            $datajson['data'] = $res;
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

    /**
     * 创建菜单
     * @author 买买提 <maimt@keruigroup.com>
     * @return void
     */
    public function createAction() {

        $request = json_decode(file_get_contents("php://input"), true);

        if (empty($request['fn'])) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '名称不能为空'
            ]);
        }

        if (empty($request['url'])) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '链接URL不能为空'
            ]);
        }

        $request['created_by'] = $this->user['id'];

        $response = (new UrlPermModel)->create_data($request);

        if($response){
            $datajson['code'] = 1;
            $datajson['message'] = '添加成功';
            $datajson['data']['id'] = $response;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '添加失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['fn'])) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '名称不能为空'
            ]);
        }

        if (empty($data['url'])) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '链接URL不能为空'
            ]);
        }

        if (empty($data['id'])) {
            $this->jsonReturn([
                'code' => -101,
                'message' => '缺少主键'
            ]);
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
