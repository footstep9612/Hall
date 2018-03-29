<?php
trait SupplierHelper
{

    public static function createSupplierExcelWithData($data)
    {
        set_time_limit(0);

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('供应商数据');

        $normal_cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P",];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $objSheet->setCellValue("A1", '供应商ID');
        $objSheet->setCellValue("B1", '公司名称');
        $objSheet->setCellValue("C1", '营业执照编号');
        $objSheet->setCellValue("D1", '注册时间');
        $objSheet->setCellValue("E1", '创建人');
        $objSheet->setCellValue("F1", '开发人');
        $objSheet->setCellValue("G1", '归口管理部门(开发人)');
        $objSheet->setCellValue("H1", '审核状态');
        $objSheet->setCellValue("I1", '审核人');
        $objSheet->setCellValue("J1", '所属事业部');
        $objSheet->setCellValue("K1", '英文SPU数量');
        $objSheet->setCellValue("L1", '中文SPU数量');
        $objSheet->setCellValue("M1", '英文SKU数量');
        $objSheet->setCellValue("N1", '中文SKU数量');
        $objSheet->setCellValue("O1", '供货范围');
        $objSheet->setCellValue("P1", '资质到期提醒');


        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $normal_cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P",];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $startRow = 2;
        if (!empty($data)) {
            foreach ($data as $k => $v) {

                $objSheet->getRowDimension($startRow)->setRowHeight(30);

                $objSheet->setCellValue("A" . $startRow, $v['id']);
                $objSheet->setCellValue("B" . $startRow, $v['name']);
                $objSheet->setCellValue("C" . $startRow, $v['social_credit_code']);
                $objSheet->setCellValue("D" . $startRow, $v['created_at']);
                $objSheet->setCellValue("E" . $startRow, $v['created_by']);
                $objSheet->setCellValue("F" . $startRow, $v['developer']);
                $objSheet->setCellValue("G" . $startRow, '');
                $objSheet->setCellValue("H" . $startRow, $v['status']);
                $objSheet->setCellValue("I" . $startRow, $v['checked_by']);
                $objSheet->setCellValue("J" . $startRow, $v['org_name']);
                $objSheet->setCellValue("K" . $startRow, $v['en_spu_count']);
                $objSheet->setCellValue("L" . $startRow, $v['zh_spu_count']);
                $objSheet->setCellValue("M" . $startRow, $v['en_sku_count']);
                $objSheet->setCellValue("N" . $startRow, $v['zh_sku_count']);
                $objSheet->setCellValue("O" . $startRow, $v['material_cat']);
                $objSheet->setCellValue("P" . $startRow, $v['expiry_date']);

                $objSheet->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("B" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("C" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("E" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("F" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("G" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("H" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("I" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("J" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("K" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("L" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("M" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("N" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("O" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("P" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $startRow++;
            }

        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "Supplier_" . date('Ymd-His') . '.xls');
    }

    /**
     * 上传到文件服务器
     * @param $file
     * @return array|mixed
     */
    public static function upload2FastDFS($file)
    {
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $data['tmp_name'] = $file;
        $data['type'] = filetype($file);
        $data['name'] = $file;
        $fileId = postfile($data, $url);

        if (is_file($file) && file_exists($file)) {
            unlink($file);
        }

        return $fileId;
    }

    public static function SupplierOnShelfSku($data, $supplierName)
    {
        set_time_limit(0);

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle($supplierName);

        $normal_cols = ["A", "B", "C", "D", "E"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $objSheet->setCellValue("A1", 'SKU编码');
        $objSheet->setCellValue("B1", '价格');
        $objSheet->setCellValue("C1", '有效期');
        $objSheet->setCellValue("D1", 'PN');
        $objSheet->setCellValue("E1", '供应商名称');


        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $normal_cols = ["A", "B", "C", "D", "E"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $startRow = 2;
        if (!empty($data)) {
            foreach ($data as $k => $v) {

                $objSheet->getRowDimension($startRow)->setRowHeight(30);

                $objSheet->setCellValue("A" . $startRow, $v['sku']);
                $objSheet->setCellValue("B" . $startRow, $v['price']);
                $objSheet->setCellValue("C" . $startRow, $v['price_validity']);
                $objSheet->setCellValue("D" . $startRow, $v['pn']);
                $objSheet->setCellValue("E" . $startRow, $supplierName);

                $objSheet->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("B" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("C" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("E" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $startRow++;
            }

        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "SupplierOnShelfSku_" . date('Ymd-His') . '.xls');
    }
}