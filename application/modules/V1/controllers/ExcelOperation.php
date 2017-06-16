<?php

/**
 * Excel操作类
 * Class ExcelOperationController
 * @author maimaiti
 */
class ExcelOperationController extends Yaf_Controller_Abstract
{

    /**
     * 数据导出为Excel
     * @author maimaiti
     */
    public function data2excelAction()
    {

        //加载PHPExcel类,新建Excel表格
        $objPHPExcel = new PHPExcel();

        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet();//获取当前sheet
        $objSheet->setTitle('商务技术报价单');//设置当前sheet标题

        //数据重组
        $exportData = $this->getData();
        //p($exportData);


        //3.填充数据
        $objSheet->setCellValue("A1","ID")->setCellValue("B1","编号")->setCellValue("C1","姓名");
        $row = 2;
        foreach ($exportData as $user)
        {
            $objSheet->setCellValue("A".$row,$user['id'])->setCellValue("B".$row,$user['user_no'])->setCellValue("C".$row,$user['name']);
            $row++;
        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");

        //保存到服务器指定目录
        //$this->export_to_disc($objWriter,"ExcelFiles","demo.xls");

        //输出到浏览器
        $this->export_to_browser_download("Excel5","demo.xls");
        $objWriter->save("php://output");

    }

    /**
     * 保存到服务器指定目录
     * @param $obj  PHPExcel写入对象
     * @param $path 保存目录
     */
    private function export_to_disc($obj,$path,$filename)
    {
        //保存路径，不存在则创建
        $savePath = APPLICATION_PATH."/".$path."/";
        if (!is_dir($savePath))
        {
            mkdir($savePath,0775,true);
        }
        $obj->save($savePath.$filename);
    }

    /**
     * 获取数据库信息，并重组返回
     * @author maimaiti
     * @return array $data 返回数据
     */
    private function getData()
    {
        $obj = new UserModel();
        $data = $obj->field(['id','user_no','name'],false)->select();
        return $data;
    }

    /**
     * 输出到浏览器下载
     * @param $type 输出类型    可以为"Excel5" 或者 “Excel2007”
     * @param $filename
     */
    private function export_to_browser_download($type,$filename)
    {
        if($type=="Excel5"){
            //输出excel03文件
            header('Content-Type: application/vnd.ms-excel');
        }else{
            //输出excel07文件
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
        //设置文件名
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        //禁止缓存
        header('Cache-Control: max-age=0');
    }



}