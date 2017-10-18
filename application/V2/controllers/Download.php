<?php

/**
 * 导出相关接口
 * @desc   DownloadController
 * @Author 买买提
 */
class DownloadController extends PublicController{


    public function init()
    {
        parent::init();
    }

    /**
     * 通用边框
     * @var array
     */
    private $borderStyle = [
            'borders' => [
                'outline' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '333333'],
                ],
            ],
    ];

    /**
     * 会员列表
     */
    public function buyerListAction()
    {
        $buyerList = $this->getBuyerList();
        $localFile = $this->createExcelObjWithData($buyerList);
        $compressedFile = $this->compresFile($localFile);
        $remoteFile = $this->upload2FileServer($compressedFile);

        if (!$remoteFile['code']=='1'){
            $this->jsonReturn([
                'code' => '-104',
                'message' => '导出失败!'
            ]);
        }

        $this->jsonReturn([
            'code' => '1',
            'message' => '导出成功!',
            'data' => [
                'url' => $remoteFile
            ]
        ]);

    }

    /**
     * 压缩
     * @param $localFile
     * @return string
     */
    private function compresFile($localFile)
    {
        $zipFileName = "BY_".date('Ymd-His').'.zip';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/".$zipFileName;

        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZIPARCHIVE::CREATE|ZIPARCHIVE::OVERWRITE);
        if ($result){
            $zip->addFile($localFile,basename($localFile));
            $zip->close();
            @unlink($localFile);
            return $filePath;
        }
    }

    /**
     * 导出的文件上传到文件服务器
     * @param $localFile 本地临时文件
     * @return array|mixed 文件服务器上的文件信息
     */
    private function upload2FileServer($localFile)
    {

        $server = Yaf_Application::app()->getConfig()->myhost;
        $url = $server. '/V2/Uploadfile/upload';
        $data['tmp_name']=$localFile;
        $data['type']='application/zip';
        $data['name']=basename($localFile);
        $excel = new ExcelmanagerController();
        $result = $excel->postfile($data,$url);
        if ($result['code']=='1'){
            @unlink($localFile);
        }
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        return $fastDFSServer.$result['url'];
    }


    /**
     * 获取会员列表
     * @return mixed
     */
    private function getBuyerList()
    {
        $buyerModel = new BuyerModel();
        //会员编号,会员名称,注册地,会员等级,用户来源,注册时间,审核状态
        $data = $buyerModel->field('buyer_no,name,province,buyer_level,created_by,created_at,status')->order('id DESC')->select();
        foreach ($data as $k=>$v){
            //会员等级
            if (empty($v['buyer_level'])){
                $data[$k]['buyer_level'] = '注册会员';
            }
            //用户来源
            if (!empty($v['created_by'])){
                $data[$k]['created_by'] = '后台注册';
            }else{
                $data[$k]['created_by'] = '门户注册';
            }
            //审核状态
            switch ($v['status']){
                case 'APPROVING' : $data[$k]['status'] = '待审核'; break;
                case 'APPROVED' : $data[$k]['status'] = '已通过'; break;
                case 'REJECTED' : $data[$k]['status'] = '已驳回'; break;
            }
        }

        return $data;

    }

    /**
     * 创建Excel文件并填充数据
     * @param array $data
     *
     * @return string
     */
    private function createExcelObjWithData(array $data)
    {
        //创建Excel对象
        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('会员列表');

        //列表头
        $objSheet->setCellValue("A1","会员编号")->getColumnDimension("A")->setWidth('24');
        $objSheet->setCellValue("B1","会员名称")->getColumnDimension("B")->setWidth('24');
        $objSheet->setCellValue("C1","注册地")->getColumnDimension("C")->setWidth('24');
        $objSheet->setCellValue("D1","会员等级")->getColumnDimension("D")->setWidth('24');
        $objSheet->setCellValue("E1","用户来源")->getColumnDimension("E")->setWidth('24');
        $objSheet->setCellValue("F1","注册时间")->getColumnDimension("F")->setWidth('24');
        $objSheet->setCellValue("G1","审核状态")->getColumnDimension("G")->setWidth('24');

        //设置边框
        $objSheet->getStyle("A1:G1")->applyFromArray($this->borderStyle);

        //写入数据
        $rowNum = 2;
        foreach ($data as $v){

            $objSheet->setCellValue("A" . $rowNum, $v['buyer_no']);
            $objSheet->setCellValue("B" . $rowNum, $v['name']);
            $objSheet->setCellValue("C" . $rowNum, $v['province']);
            $objSheet->setCellValue("D" . $rowNum, $v['buyer_level']);
            $objSheet->setCellValue("E" . $rowNum, $v['created_by']);
            $objSheet->setCellValue("F" . $rowNum, $v['created_at']);
            $objSheet->setCellValue("G" . $rowNum, $v['status']);

            $objSheet->getStyle("A2:G" . $rowNum)->applyFromArray($this->borderStyle);

            $rowNum++;

        }

        //保存文件
        ob_end_clean();
        ob_start();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $fileName = "BL_".date('Ymd-His'). '.xls';
        $fullFileName = $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/".$fileName;
        $objWriter->save($fullFileName);
        return $fullFileName;

    }

}
