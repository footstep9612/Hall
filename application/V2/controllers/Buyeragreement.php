<?php
//客户管理-框架协议--业务信息--王帅
class BuyeragreementController extends Yaf_Controller_Abstract
{
    public function __init()
    {
        parent::__init();
    }
    public function exportAgreeAction(){
        //创建对象
        $excel = new PHPExcel();
//Excel表格式,这里简略写了8列
        $letter = range(A,Z);
//表头数组
        $tableheader = array('框架执行单号','事业部','执行分公司','所属地区','客户名称','客户代码（CRM）','品名中文','数量/单位','项目金额（美元）','执行金额（美元）','项目开始执行时间','市场经办人','商务技术经办人');
//填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }
//表格数组
        $data = array(
            'buyer_id'=>123,
            'created_by'=>38698
        );
        $agree = new BuyerAgreementModel();
        $res = $agree->manageAgree($data);
        $arr=$res['info'];
//填充表格信息
        for ($i = 2;$i <= count($arr) + 1;$i++) {
            $j = 0;
            foreach ($arr[$i - 2] as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }

//创建Excel输入对象
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $time = date('Ymd');
        $objWriter->save('tongji.xls');    //文件保存

        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $url = $server . '/V2/Uploadfile/upload';

        $data['tmp_name'] = 'tongji.xls';
        $data['type'] = 'application/excel';
        $data['name'] = pathinfo('tongji.xls', PATHINFO_BASENAME);
        $fileId = postfile($data, $url);
        var_dump($fileId);die;
//        array(3) { ["code"]=> string(1) "1" ["url"]=> string(51) "group1/M00/00/63/rBISxFouUbSAdUHpAAAb6eDEsrA353.xls" ["name"]=> string(11) "testest.xls" }
//
//
//        $write = new PHPExcel_Writer_Excel5($excel);
//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
//        header("Content-Type:application/force-download");
//        header("Content-Type:application/vnd.ms-execl");
//        header("Content-Type:application/octet-stream");
//        header("Content-Type:application/download");;
//        header('Content-Disposition:attachment;filename="testdata.xls"');
//        header("Content-Transfer-Encoding:binary");
//        $write->save('php://output');


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