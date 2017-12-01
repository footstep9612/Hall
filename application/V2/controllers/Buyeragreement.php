<?php
//客户管理---业务信息--王帅
class BuyeragreementController extends PublicController
{
    public function __init()
    {
        parent::__init();
    }
    //框架协议管理index-wangs
    public function manageAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $agree = new BuyerAgreementModel();
        $res = $agree->manageAgree($data);
        print_r($res);die;
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
            echo json_encode(array("code" => "1","message" => "创建成功"));
        }
        echo json_encode(array("code" => "0","message" => "请输入规范数据"));
    }
    //查看框架协议详情
    public function showAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $agree = new BuyerAgreementModel();
        $res = $agree->showAgreeDesc($data);
        if($res == false){
            echo json_encode(array("code" => "0","message" => "请输入正确执行单号"));
            exit();
        }
        echo json_encode(array("code" => "1","message" => "返回数据",""=>$res));
    }
}