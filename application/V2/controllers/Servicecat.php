<?php

/**
 */
//class ServicecatController extends PublicController {
class ServicecatController extends Yaf_Controller_Abstract{

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

    /**
     * 新增
     */
    public function createServiceAction() {
        $data = json_decode(file_get_contents("php://input"), true);
       /* $data=[
            0=>[
                'category'=>'{"lang":"en","name":"Financial Service","remarks":""},{"lang":"zh","name":"金务","remarks":""}',
                'term'=>'{"lang":"en","name":"Financial Service","remarks":""},{"lang":"zh","name":"金务2","remarks":""}',
                'item'=>'{"lang":"en","name":"Financial Service","remarks":""},{"lang":"zh","name":"金务3","remarks":""}',
            ],
        ];*/
        $model = new ServiceCatModel();
        $res = $model->addData($data);
        if($res){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '失败!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 更新
     */

    public function updateServiceAction() {
        $data = json_decode(file_get_contents("php://input"), true);
       /* $data=[
            0=>[
                'id'=>35,
                'category'=>'{"lang":"en","name":"Financial Service","remarks":""},{"lang":"zh","name":"金1务","remarks":""}',
                'term'=>'{"lang":"en","name":"Financial Service","remarks":""},{"lang":"zh","name":"金2务2","remarks":""}',
                'item'=>'{"lang":"en","name":"Financial Service","remarks":""},{"lang":"zh","name":"金3务3","remarks":""}',
            ],
        ];*/
        $model = new ServiceCatModel();
        $res = $model->update_data($data);
        if($res){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '失败!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 删除
     */

    public function deleteServiceAction() {
        $data = json_decode(file_get_contents("php://input"), true);
//        $data['id'] = 35;//测试
        if(empty($data['id'])){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model = new ServiceCatModel();
        $res = $model->delData($data['id']);
        if($res){
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '失败!';
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

    /**
     * 会员等级查询
     * @time  2017-08-05
     * @author klp
     */
    public function levelAction(){
        $MemberServiceModel = new MemberServiceModel();
        $result = $MemberServiceModel->levelInfo();
        if(!empty($result)) {
            jsonReturn($result);
        } else {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
    }

    /**
     * 会员等级新建/编辑
     * @time  2017-08-05
     * @author klp
     */
    public function editLevelAction(){
        /*$this->put_data = [
            0=>[
                'id'=>'',
                'buyer_level'=>'',
                'service_cat_id'=>'',
                'service_term_id'=>'',
                'service_item_id'=>'',
            ],
        ];*/
        //获取用户信息
        $userInfo = getLoinInfo();
        $MemberServiceModel = new MemberServiceModel();
        $result = $MemberServiceModel->editInfo($this->put_data,$userInfo);
        if($result && $result['code'] == 1) {
            jsonReturn($result);
        } else {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
    }


    /**
     * 会员服务信息详情查询 -- 总的接口 一级二级三级
     * @time  2017-08-05
     * @author klp
     */
    public function serviceInfoAction(){
        $ServiceCatModel = new ServiceCatModel();
        $result = $ServiceCatModel->getInfo($this->put_data);
        if(!empty($result)) {
            jsonReturn($result);
        } else {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
    }

}
