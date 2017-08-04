<?php

/**
 */
class ServicecatController extends PublicController {


    public function init() {
//parent::init();
    }

    /*
     * 服务列表
     * */
    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['lang'])){
            $where['lang'] = $data['lang'];
        }
        if(!empty($data['pageSize'])){
            $limit['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])) {
            $limit['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        $model = new ServiceCatModel();
        $data =$model->getlist($where,$limit);
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




    /**
     * 详情
     */
    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $arr['id'] = $data['id'];
        if(empty($arr['id'])){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model = new ServiceCatModel();
        $res = $model->detail($arr);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }


    /*
     * 更新
     */

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model = new ServiceCatModel();
        $res = $model->update_data($data,['id' => $id]);
        if(!empty($res)){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function indexAction() {
        $body['mappings'] = [];
        foreach ($this->langs as $lang) {
            $body['mappings']['country_' . $lang]['properties'] = $this->country($lang);
            $body['mappings']['country_' . $lang]['_all'] = ['enabled' => false];
        }
        $this->es->create_index($this->index, $body, 5);
        $this->setCode(1);
        $this->setMessage('成功!');
        $this->jsonReturn();
    }


}
