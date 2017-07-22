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
        $model_group = new GroupModel();
        $data = $model_group->getlist($where,$limit); //($this->put_data);
        $count = count($data);
        $childrencount=0;
        for($i=0;$i<$count;$i++){
            $data[$i]['children'] = $model_group->getlist(['parent_id'=> $data[$i]['id']],$limit);
            $childrencount = count($data[$i]['children']);
            if($childrencount>0){
                for($j=0;$j<$childrencount;$j++){
                    if(isset($data[$i]['children'][$j]['id'])){
                        $data[$i]['children'][$j]['children'] = $model_group->getlist(['parent_id'=> $data[$i]['children'][$j]['id']],$limit);
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

        jsonReturn($datajson);
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
        jsonReturn($datajson);
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
        $arr['status'] = 'DELETED';
        $model_group = new GroupModel();
        $id = $model_group->update_data($arr,$where);
        if($id > 0){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '修改失败!';
        }
        jsonReturn($datajson);
    }

}
