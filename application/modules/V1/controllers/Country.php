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
class CountryController extends PublicController {

    public function __init() {
        //   parent::__init();
    }

    public function listAction() {
//        $reids=new phpredis();
//        $reids->set("name",'eww');
//        var_dump($reids->get("name"));die;
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['lang'])){
            $where['lang'] = $data['lang'];
        }
        if(!empty($data['bn'])){
            $where['bn'] = $data['bn'];
        }
        if(!empty($data['name'])){
            $where['name'] = $data['name'];
        }
        if(!empty($data['time_zone'])){
            $where['time_zone'] = $data['time_zone'];
        }
        if(!empty($data['region'])){
            $where['region'] = $data['region'];
        }
        if(!empty($data['page'])){
            $limit['page'] = $data['page'];
        }
        if(!empty($data['countPerPage'])){
            $limit['num'] = $data['countPerPage'];
        }
        $model_group = new CountryModel();
        $arr = $model_group->getlist($where,$limit); //($this->put_data);
        if(empty($data) && !empty($arr)){
            $reids=new phpredis();
            $redis_arr['list'] = json_encode($arr);
            $count = count($arr);
            $bn_arr = [];
            $name_arr = [];
            for($i = 0; $i < $count; $i++){
                $bn_arr[$arr[$i]['id']] = $arr[$i]['bn'];
                $name_arr[$arr[$i]['id']] = $arr[$i]['name'];
            }
            $redis_arr['bn'] = json_encode($bn_arr);
            $redis_arr['name'] = json_encode($name_arr);
            $reids->hashSet('countryDict',$redis_arr);
            print_r($reids->hashGet("countryDict",'bn'));
            print_r(json_decode($reids->hashGet("countryDict",'bn'),true));die;
        }
        if(!empty($arr)){
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        }else{
            $datajson['code'] = -104;
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
        if(empty($data['id'])){
            $datajson['code'] = -101;
            $datajson['message'] = '缺少主键!';
            $this->jsonReturn($datajson);
        }else{
            $where['id'] = $data['id'];
        }
        $model_group = new GroupModel();
        $id = $model_group->update_data($data,$where);
        if($id > 0){
            $datajson['code'] = 1;
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
        $model_group = new GroupModel();
        $re = $model_group->delete_data($id);
        if($re > 0){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

}
