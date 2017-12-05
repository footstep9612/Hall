<?php
//客户管理---业务信息--王帅
class BuyeragreementController extends PublicController
{
    public function __init()
    {
        parent::__init();
    }
    //框架协议管理index-wangs//
    public function manageAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $agree = new BuyerAgreementModel();
        $res = $agree->manageAgree($data);
        $dataJson['code'] = 1;
        $dataJson['message'] = '返回数据';
        $dataJson['data'] = $res;
        $this -> jsonReturn($dataJson);
    }
    //创建客户---业务信息
    public function createAgreeAction()
    {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $agree = new BuyerAgreementModel();
        $res = $agree->createAgree($data);
        if($res){
            $dataJson['code'] = 1;
            $dataJson['message'] = '创建成功';
        }else{
            $dataJson['code'] = 0;
            $dataJson['message'] = '请输入规范数据';
        }
        $this -> jsonReturn($dataJson);
    }
    //查看框架协议详情
    public function showAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $agree = new BuyerAgreementModel();
        $res = $agree->showAgreeDesc($data);
        if($res == false){
            $dataJson['code'] = 0;
            $dataJson['message'] = '请输入正确执行单号';
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '返回数据';
            $dataJson['data'] = $res;
        }
        $this -> jsonReturn($dataJson);
    }
}