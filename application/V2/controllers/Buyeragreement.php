<?php
//客户管理-框架协议--业务信息--王帅
class BuyeragreementController extends PublicController
{
    public function __init()
    {
        parent::__init();
    }
    //统计-excel导出-框架协议数据
    public function exportStatisAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $agree = new BuyerAgreementModel();
        $res = $agree->exportAgree($data);
        if($res['code'] == 1){
            $excel = new BuyerExcelModel();
            $excel->saveExcel($res['name'],$res['url'],$created_by);
            $this->jsonReturn($res);
        }else{
            $dataJson = array(
                'code'=>0,
                'message'=>'excel导出异常或数据为空'
            );
            $this->jsonReturn($dataJson);
        }
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
    //创建客户---框架协议
    public function createAgreeAction()
    {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        if(empty($data['attach_name']) || empty($data['attach_url'])){
            $dataJson['code'] = 0;
            $dataJson['message'] = '请选择协议附件';
            $this -> jsonReturn($dataJson);
        }
        $agree = new BuyerAgreementModel();
        $agreement_id = $agree->createAgree($data);
        if($agreement_id === false){
            $dataJson['code'] = 0;
            $dataJson['message'] = '创建协议失败,请输入规范数据';
            $this -> jsonReturn($dataJson);
        }
        if($agreement_id === 'exsit'){
            $dataJson['code'] = 0;
            $dataJson['message'] = '该框架协议单号已存在,请重新输入';
            $this -> jsonReturn($dataJson);
        }
        $data['agreement_id'] = $agreement_id;
        $attach = new AgreementAttachModel();
        $attachRes = $attach->createAgreeAttach($data);
        if($attachRes){
            $dataJson['code'] = 1;
            $dataJson['message'] = '协议，附件，创建成功';
        }else{
            $dataJson['code'] = 0;
            $dataJson['message'] = '创建协议成功，附件创建失败';
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
    //查看框架协议编辑
    public function updateAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $agree = new BuyerAgreementModel();
        $res = $agree->updateAgree($data);
        if($res == false || empty($data['attach_name']) || empty($data['attach_url'])){
            $dataJson['code'] = 0;
            $dataJson['message'] = '保存协议失败,请输入规范数据';
            $this -> jsonReturn($dataJson);
        }
        $attach = new AgreementAttachModel();
        $attachRes = $attach->updateAgreeAttach($res,$data);
        if($attachRes){
            $dataJson['code'] = 1;
            $dataJson['message'] = '保存协议成功';
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '保存协议成功,附件失败';
        }
        $this -> jsonReturn($dataJson);
    }
}