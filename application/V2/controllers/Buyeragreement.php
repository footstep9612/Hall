<?php
//客户管理-框架协议--业务信息--王帅
class BuyeragreementController extends PublicController
{
    public function __init()
    {
        parent::__init();
    }
    public function exportAgreeAction(){
        //创建对象
        $excel = new PHPExcel();
//Excel表格式,这里简略写了8列
        $letter = array('A','B','C','D','E','F','F','G');
//表头数组
        $tableheader = array('学号','姓名','性别','年龄','班级');
//填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }

//表格数组
        $data = array(
            array('1','小王','男','20','100'),
            array('2','小李','男','20','101'),
            array('3','小张','女','20','102'),
            array('4','小赵','女','20','103')
        );
//填充表格信息
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }

//创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="testdata.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');


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
        if($agreement_id == false){
            $dataJson['code'] = 0;
            $dataJson['message'] = '创建协议失败,请输入规范数据';
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