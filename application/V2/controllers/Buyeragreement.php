<?php
//客户管理-框架协议--业务信息--王帅
class BuyeragreementController extends PublicController
{
    public function __init()
    {
        parent::init();
    }
    //获取用户的角色
    public function getUserRole(){
        $config = \Yaf_Application::app()->getConfig();
        $ssoServer=$config['ssoServer'];
        $token=$_COOKIE['eruitoken'];
        $opt = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Content-Type: application/json\r\n" .
                    "Cookie: ".$_COOKIE."\r\n",
                'content' =>json_encode(array('token'=>$token))

            )
        );
        $context = stream_context_create($opt);
        $json = file_get_contents($ssoServer,false,$context);
        $info=json_decode($json,true);

        $arr['role']=$info['role_no'];
        if(!empty($info['country_bn'])){
            $countryArr=[];
            foreach($info['country_bn'] as $k => $v){
                $countryArr[]="'".$v."'";
            }
            $countryStr=implode(',',$countryArr);
        }
        $arr['country']=$countryStr;
        return $arr;
    }
    //统计-excel导出-框架协议数据-wangs
    public function exportStatisAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['admin']=$this->getUserRole();
        $agree = new BuyerAgreementModel();
        $res = $agree->exportAgree($data);
        if($res==false){
            $dataJson = array(
                'code'=>0,
                'message'=>L('data_empty')     //excel导出异常或数据为空
            );
        }else{
            $excel = new BuyerExcelModel();
            $excel->saveExcel($res['name'],$res['url'],$created_by);
            $dataJson = array(
                'code'=>1,
                'message'=>L('success'),
                'data'=>$res
            );
        }
        $this->jsonReturn($dataJson);
    }
    //框架协议管理index-wangs//
    public function manageAgreeAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['admin']=$this->getUserRole();
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
        if(empty($data['agree_attach'][0]['attach_url'])){
            $dataJson['code'] = 0;
            $dataJson['message'] = L('select_protocol');    //请选择协议附件
            $this -> jsonReturn($dataJson);
        }
        if(empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <0){
            $dataJson['code'] = 0;
            $dataJson['message'] = L('digital_amount'); //数字金额
            $this -> jsonReturn($dataJson);
        }
        if(!empty($data['number'])){
            if(!is_numeric($data['number']) || $data['amount'] <0){
                $dataJson['code'] = 0;
                $dataJson['message'] = L('number_format');  //请输入正确数量格式
                $this -> jsonReturn($dataJson);
            }
        }
        $agree = new BuyerAgreementModel();

        $agreement_id = $agree->createAgree($data); //框架协议创建成功
        if(is_numeric($agreement_id)){
            $attach = new AgreementAttachModel();
            $attachRes = $attach->createAgreeAttach($data['agree_attach'],$agreement_id,$created_by);
            if($attachRes){
                $dataJson['code'] = 1;
                $dataJson['message'] = L('success');    //协议，附件，创建成功
            }
            $this -> jsonReturn($dataJson);
        }elseif ($agreement_id !== false && $agreement_id !== true && $agreement_id !== 'exsit'){
            $dataJson['code'] = 0;
            $dataJson['message'] = $agreement_id.L('format_error'); //格式错误
            $this -> jsonReturn($dataJson);
        }elseif ($agreement_id === 'exsit'){
            $dataJson['code'] = 0;
            $dataJson['message'] = L('framework_protocol_existed');  //该框架协议单号已存在,请重新输入
            $this -> jsonReturn($dataJson);
        }
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
            $dataJson['message'] = L('number_error'); //请输入正确执行单号
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
        if(empty($data['agree_attach'][0]['attach_url'])){
            $dataJson['code'] = 0;
            $dataJson['message'] = L('select_protocol');
            $this -> jsonReturn($dataJson);
        }
        $agree = new BuyerAgreementModel();
        $res = $agree->updateAgree($data);
        if($res !==true && $res !== 'no_error' && !is_numeric($res)){
            $dataJson['code'] = 0;
            $dataJson['message'] = $res.L('format_error');  //'请输入规范'
            $this -> jsonReturn($dataJson);
        }
        if($res === 'no_error'){
            $dataJson['code'] = 0;
            $dataJson['message'] = L('number_error');  //框架协议单号错误
            $this -> jsonReturn($dataJson);
        }
        $attach = new AgreementAttachModel();
        $attachRes = $attach->updateAgreeAttach($data['agree_attach'],$res,$created_by);
        if($attachRes){
            $dataJson['code'] = 1;
            $dataJson['message'] = L('success');
        }
        $this -> jsonReturn($dataJson);
    }
}