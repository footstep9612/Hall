<?php

/**
 */
class ServicecatController extends PublicController {
//class ServicecatController extends Yaf_Controller_Abstract{

    public function init() {
        parent::init();
        $this->put_data = $this->put_data ? $this->put_data : json_decode(file_get_contents("php://input"), true);

    }

    /*
     * 服务列表
     * */
    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['pageSize'])){
            $limit['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])) {
            $limit['page'] = ($data['currentPage'] - 1) * $limit['num'];
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
        //$res = $model->detail($arr);
        $res = $model->getService($arr);
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
     * {"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ijk4IiwiZXh0IjoxNDk5MjM2NTE2LCJpYXQiOjE0OTkyMzY1MTYsIm5hbWUiOiJcdTUyMThcdTY2NTYifQ.CpeZKj2ar7OradKomSuMzeIYF6M1ZcWLHw8ko81bDJo","services":[{"category":[{"lang":"en","name":"test 1","remarks":""},{"lang":"zh","name":"\u6d4b\u8bd51","remarks":""}],"term":[{"lang":"en","name":"test 2","remarks":""},{"lang":"zh","name":"\u6d4b\u8bd52","remarks":""}],"item":[{"lang":"en","name":"test 3","remarks":""},{"lang":"zh","name":"\u6d4b\u8bd53","remarks":""}]}]}
     */
    public function createServiceAction() {
        $data = json_decode(file_get_contents("php://input"), true);
//        $data= [
//        'service'=>[
//              0=>[
//                  'category'=>[
//                      0=>["lang"=>"en", "name"=>"test 1", "remarks"=>"",],
//                      1=>["lang"=>"zh", "name"=>"测试1", "remarks"=>"",],
//                  ],
//                  'term'=>[
//                      0=>["lang"=>"en", "name"=>"test 2", "remarks"=>"",],
//                      1=>["lang"=>"zh", "name"=>"测试2", "remarks"=>"",],
//                  ],
//                  'item'=>[
//                      0=>["lang"=>"en", "name"=>"test 3", "remarks"=>"",],
//                      1=>["lang"=>"zh", "name"=>"测试3", "remarks"=>"",],
//                  ]
//              ],
//            ]
//               ];
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
     * 服务编辑或修改
     * @author link 2017-08-18
     */
    public function editAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new ServiceCatModel();
        $res = $model->editService($data);
        if($res){
            jsonReturn($res);
        }else{
            jsonReturn('',ErrorMsg::FAILED);
        }
    }

    /**
     * 更新
     * *{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ijk4IiwiZXh0IjoxNDk5MjM2NTE2LCJpYXQiOjE0OTkyMzY1MTYsIm5hbWUiOiJcdTUyMThcdTY2NTYifQ.CpeZKj2ar7OradKomSuMzeIYF6M1ZcWLHw8ko81bDJo","services":[{"id":"39","category":[{"lang":"en","name":"test 391","remarks":""},{"lang":"zh","name":"\u6d4b\u8bd5391","remarks":""}],"term":[{"lang":"en","name":"test 392","remarks":""},{"lang":"zh","name":"\u6d4b\u8bd5392","remarks":""}],"item":[{"lang":"en","name":"test 393","remarks":""},{"lang":"zh","name":"\u6d4b\u8bd5393","remarks":""}]}]}
     */

    public function updateServiceAction() {
        $data = json_decode(file_get_contents("php://input"), true);
//        $data= [
//            'service'=>[
//                0=>[
//                    'id'=> '39',
//                    'category'=>[
//                        0=>["lang"=>"en", "name"=>"test 391", "remarks"=>"",],
//                        1=>["lang"=>"zh", "name"=>"测试391", "remarks"=>"",],
//                    ],
//                    'term'=>[
//                        0=>["lang"=>"en", "name"=>"test 392", "remarks"=>"",],
//                        1=>["lang"=>"zh", "name"=>"测试392", "remarks"=>"",],
//                    ],
//                    'item'=>[
//                        0=>["lang"=>"en", "name"=>"test 393", "remarks"=>"",],
//                        1=>["lang"=>"zh", "name"=>"测试393", "remarks"=>"",],
//                    ]
//                ],
//            ]
//        ];
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
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if(!empty($data['pageSize'])){
            $limit['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])) {
            $limit['page'] = ($data['currentPage'] - 1) * $limit['num'];
        }
        $MemberServiceModel = new MemberServiceModel();
        $result = $MemberServiceModel->levelInfo($limit);
        if(!empty($result)) {
            jsonReturn($result);
        } else {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
    }

    /**
     * 会员等级新建/编辑
     * @time  2017-08-05
     *{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ijk4IiwiZXh0IjoxNDk5MjM2NTE2LCJpYXQiOjE0OTkyMzY1MTYsIm5hbWUiOiJcdTUyMThcdTY2NTYifQ.CpeZKj2ar7OradKomSuMzeIYF6M1ZcWLHw8ko81bDJo","levels":[{"id":"27","buyer_level":"27","service_cat_id":"27","service_term_id":"27","service_item_id":"27"}]}
     * @author klp
     */
    public function editLevelAction(){
        /*  $this->put_data = [
           'levels'=>[
                    0=>[
                           'id'=>'',
                           'buyer_level'=>'',
                           'service_cat_id'=>'',
                           'service_term_id'=>'',
                           'service_item_id'=>'',
                       ],
               ]
           ]; */
        //获取用户信息
        $userInfo = getLoinInfo();
        $MemberServiceModel = new MemberServiceModel();
        $result = $MemberServiceModel->editInfo($this->put_data,$userInfo);
        if($result && $result['code'] == 1) {
            $this->jsonReturn($result);
        } else {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
    }

    /**
     * 删除等级
     */

    public function deleteLevelAction() {
        $data = json_decode(file_get_contents("php://input"), true);
//        $data['id'] = '1';//测试
        if(empty($data['id'])){
            $datajson['code'] = -101;
            $datajson['message'] = '用户等级i[id]不可为空!';
            $this->jsonReturn($datajson);
        }
        $MemberServiceModel = new MemberServiceModel();
        $res = $MemberServiceModel->delData($data['id']);
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
