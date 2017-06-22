<?php

/**
 * Excel操作类
 * Class ExcelOperationController
 * @author maimaiti
 */
class ExcelOperationController extends PublicController
{

    /**
     * 测试接口
     */
    public function testAction()
    {
        if (!$this->getRequest()->isPost())
        {
            $response = ['code'=>400,'message'=>'BadRequest...','data'=>[]];
            exit(json_encode($response));
        }
        $response = ['code'=>200,'message'=>'Response successfuly...','data'=>[]];
        exit(json_encode($response));
    }

    /**
     * 报价单Excel导出api接口
     * @author maimaiti
     */
    public function exportAction()
    {
        //请求验证
        if (!$this->getRequest()->isPost())
        {
            jsonReturn(null,-2101,ErrorMsg::getMessage('-2101'));
        }
        //获取post
        $raw = json_decode(file_get_contents("php://input"),true);
        if (!isset($raw['quote_no']))
        {
            jsonReturn(null,-2103,ErrorMsg::getMessage('-2103'));
        }

        //后期补上api身份验证相关的逻辑
        $file = $this->data2excelAction($raw['quote_no']);
        if (file_exists($file)){
            $returnData = [
                'code'=>1,
                'message'=>ErrorMsg::getMessage('1'),
                'data'=>[
                    'file'=>$file,
                    'exported_at'=>date('YmdHis')
                ]
            ];
        }
        exit(json_encode($returnData));
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
        return $savePath.$filename;
    }

    /**
     * 获取数据库信息，并重组返回
     * @author maimaiti
     * @return array $data 返回数据
     */
    private function getData($quote_no)
    {
        $obj = new QouteModel();
        $fields = [
            'quoter',//商务报价人
            'quoter_email',//商务报价人邮箱
            'quote_at',//商务报价时间
            'id',//编号
            // ...
        ];
        $where = ['quote_no'=>$quote_no];
        $data = $obj->where($where)->field($fields,false)->find();
        return $data;
    }

    /**
     *
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

    /**
     * 数据导出为Excel
     * @author maimaiti
     */
    public function data2excelAction($quote_no)
    {

        //加载PHPExcel类,新建Excel表格
        $objPHPExcel = new PHPExcel();

        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet();//获取当前sheet
        $objSheet->setTitle('商务技术报价单');//设置当前sheet标题

        //数据重组
        $quote = $this->getData($quote_no);
        //var_dump($quote);die;


        //3.填充数据
        //设置边框
        $styleArray = [
            'borders'=>[
                'outline'=>[
                    'style'=>PHPExcel_Style_Border::BORDER_THIN,
                    'color'=>['rgb'=>'333333'],
                ],
            ],
        ];
        /*设置A1~R1标题并合并单元格(水平整行，垂直2列)*/
        $objSheet->setCellValue("A1",'易瑞国际电子商务有限公司商务技术部')->mergeCells("A1:R2");
        $objSheet->getStyle("A3:R5")->applyFromArray($styleArray);


        $objSheet->getStyle("A1:R2")
            ->getFont()
            ->setSize(18)
            ->setBold(true);

        /*设置A1~R1的文字属性*/
        $objSheet->getCell("A1")
            ->getStyle()
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置全局文字居中
        $objSheet->getDefaultStyle()
            ->getFont()
            ->setName("微软雅黑")
            ->setSize(10);

        $objSheet->getStyle()
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置全局列宽度
        $small_cols = ["A","G"];
        foreach ($small_cols as $small_col):
            $objSheet->getColumnDimension($small_col)->setWidth('6');
        endforeach;

        $normal_cols = ["I","K","L","N","O","P","Q"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('12');
        endforeach;

        $big_cols = ["B","C","D","E","F","H","J","M","R"];
        foreach ($big_cols as $big_col):
            $objSheet->getColumnDimension($big_col)->setWidth('18');
        endforeach;

//        $objSheet->getColumnDimension("A")->setWidth('6');
//        $objSheet->getColumnDimension("B")->setWidth('16');

        $objSheet->setCellValue("A3","报价人 : ".$quote['quoter'])->mergeCells("A3:E3");
        $objSheet->setCellValue("A4","电话 : ")->mergeCells("A4:E4");
        $objSheet->setCellValue("A5","邮箱 : ".$quote['quoter_email'])->mergeCells("A5:E5");

        $objSheet->setCellValue("F3","询价单位 : (加拿大)孙继飞")->mergeCells("F3:R3");
        $objSheet->setCellValue("F4","业务对接人 : 孙继飞")->mergeCells("F4:R4");
        $objSheet->setCellValue("F5","报价时间 : ".date('Y-m-d',$quote['quote_at']))->mergeCells("F5:R5");

        $objSheet->setCellValue("A6",'易瑞国际电子商务有限公司商务技术部')
            //单元格合并
            ->mergeCells("A6:R6")
            //设置高度
            ->getRowDimension("6")
            ->setRowHeight(26);

        $objSheet->getCell("A6")
            ->getStyle()
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objSheet->setCellValue("A7","序号\nitem")->mergeCells("A7:A8");
        $objSheet->setCellValue("B7","名称\nitem")->mergeCells("B7:B8");
        $objSheet->setCellValue("C7","外文名称\nitem")->mergeCells("C7:C8");
        $objSheet->setCellValue("D7","规格\nmodel")->mergeCells("D7:D8");
        $objSheet->setCellValue("E7","客户需求描述\nRequirementSpecifications")->mergeCells("E7:E8");
        $objSheet->setCellValue("F7","报价产品描述\nSupplySpecifications")->mergeCells("F7:F8");
        $objSheet->setCellValue("G7","数量\nQty")->mergeCells("G7:G8");
        $objSheet->setCellValue("H7","单位\nUnit")->mergeCells("H7:H8");
        $objSheet->setCellValue("I7","产品品牌\nBrand")->mergeCells("I7:I8");
        $objSheet->setCellValue("J7","报出EXW单价\nQuote EXW Unit Price")->mergeCells("J7:J8");
        $objSheet->setCellValue("K7","贸易单价\nTrade Unit Price")->mergeCells("K7:K8");
        $objSheet->setCellValue("L7","单重\nUnit\nWeight(kg)")->mergeCells("L7:L8");
        $objSheet->setCellValue("M7","包装体积\nPacking\nSizeL*W*H(mm)")->mergeCells("M7:M8");
        $objSheet->setCellValue("N7","包装方式\nPacking")->mergeCells("N7:N8");
        $objSheet->setCellValue("O7","交货期\nValidity\n(Working Day)")->mergeCells("O7:O8");
        $objSheet->setCellValue("P7","退税率\nTax RefundRate")->mergeCells("P7:P8");
        $objSheet->setCellValue("Q7","备注\nRemark")->mergeCells("Q7:Q8");

        $cols = ["A7","B7","C7","D7","E7","F7","G7","H7","I7","J7","K7","L7","M7","N7","O7","P7","Q7"];
        foreach ($cols as $col)
        {
            $objSheet->getStyle($col)
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        $objSheet->setCellValue("A9",$quote['id']);
        $objSheet->setCellValue("B9","科瑞");
        $objSheet->setCellValue("C9","kerui");
        $objSheet->setCellValue("D9","1|22|35");
        $objSheet->setCellValue("E9","描述");
        $objSheet->setCellValue("F9","");
        $objSheet->setCellValue("G9","100");
        $objSheet->setCellValue("H9","热");
        $objSheet->setCellValue("I9","科瑞");
        $objSheet->setCellValue("J9","130");
        $objSheet->setCellValue("K9","131.16");
        $objSheet->setCellValue("L9","12");
        $objSheet->setCellValue("M9","");
        $objSheet->setCellValue("N9","4656");
        $objSheet->setCellValue("O9","12");
        $objSheet->setCellValue("P9","");
        $objSheet->setCellValue("Q9","");

        $cols = ["A9","B9","C9","D9","E9","F9","G9","H9","I9","J9","K9","L9","M9","N9","O9","P9","Q9"];
        foreach ($cols as $col)
        {
            $objSheet->getStyle($col)
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        $objSheet->setCellValue("A10","")->mergeCells("A10:R10");

        $objSheet->setCellValue("B11","重量");
        $objSheet->setCellValue("C11","12000");
        $objSheet->setCellValue("D11","包装总体积(m³)");
        $objSheet->setCellValue("E11","23");
        $objSheet->setCellValue("F11","付款方式");
        $objSheet->setCellValue("G11","电汇");
        $objSheet->setCellValue("H11","");
        $objSheet->setCellValue("I11","");
        $objSheet->setCellValue("J11","EXW交货周期(天)");
        $objSheet->setCellValue("K11","60");

        $objSheet->setCellValue("B12","贸易术语");
        $objSheet->setCellValue("C12","FOB");
        $objSheet->setCellValue("D12","运输方式");
        $objSheet->setCellValue("E12","Ocean");
        $objSheet->setCellValue("F12","存放地");
        $objSheet->setCellValue("G12","东营");
        $objSheet->setCellValue("H12","目的地");
        $objSheet->setCellValue("I12","目的地");
        $objSheet->setCellValue("J12","运输周期(天)");
        $objSheet->setCellValue("K12","15");

        $objSheet->setCellValue("B13","物流合计");
        $objSheet->setCellValue("C13","214.3");
        $objSheet->setCellValue("D13","物流合计");
        $objSheet->setCellValue("E13","USD");
        $objSheet->setCellValue("F13","银行费用");
        $objSheet->setCellValue("G13","1254.35");
        $objSheet->setCellValue("H13","银行费用币种");
        $objSheet->setCellValue("I13","USD");
        $objSheet->setCellValue("J13","");
        $objSheet->setCellValue("K13","");

        $objSheet->setCellValue("B14","EXW合计");
        $objSheet->setCellValue("C14","0");
        $objSheet->setCellValue("D14","EXW合计币种");
        $objSheet->setCellValue("E14","USD");
        $objSheet->setCellValue("F14","出信用保险");
        $objSheet->setCellValue("G14","0");
        $objSheet->setCellValue("H14","出信用保险币种");
        $objSheet->setCellValue("I14","USD");
        $objSheet->setCellValue("J14","");
        $objSheet->setCellValue("K14","");

        $objSheet->setCellValue("B15","报价合计");
        $objSheet->setCellValue("C15","131158.64");
        $objSheet->setCellValue("D15","1报价合计币种");
        $objSheet->setCellValue("E15","USD");
        $objSheet->setCellValue("F15","");
        $objSheet->setCellValue("G15","");
        $objSheet->setCellValue("H15","");
        $objSheet->setCellValue("I15","");
        $objSheet->setCellValue("J15","");
        $objSheet->setCellValue("K15","");


        $objSheet->getStyle("A11:K15")->applyFromArray($styleArray);

        $total_rows = [
                        "A11","A12","A13","A14","A15","B11","B12","B13","B14","B15",
                        "C11","C12","C13","C14","C15","D11","D12","D13","D14","D15",
                        "E11","E12","E13","E14","E15","F11","F12","F13","F14","F15",
                        "G11","G12","G13","G14","G15","H11","H12","H13","H14","H15",
                        "I11","I12","I13","I14","I15","J11","J12","J13","J14","J15",
                        "K11","K12","K13","K14","K15",
        ];
        foreach ($total_rows as $total_row)
        {
            $objSheet->getCell($total_row)->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objSheet->getStyle($total_row)->applyFromArray($styleArray);
        }

        $objSheet->setCellValue("A16",'报价备注 : ')->mergeCells("A16:K17");
        $objSheet->getStyle("A16:K17")->applyFromArray($styleArray);
        $objSheet->getCell("A16")
            ->getStyle()
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objSheet->setCellValue("A18",'物流备注 : ')->mergeCells("A18:K19");
        $objSheet->getStyle("A18:K19")->applyFromArray($styleArray);
        $objSheet->getCell("A18")
            ->getStyle()
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objSheet->setCellValue("A20","")->mergeCells("A20:K21");
        $objSheet->getStyle("A20:K21")->applyFromArray($styleArray);

        //添加logo


        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel2007");

        //保存到服务器指定目录
        return $this->export_to_disc($objWriter,"ExcelFiles","demo.xls");

        //输出到浏览器
        //$this->export_to_browser_download("Excel5","demo.xls");
        //$objWriter->save("php://output");

    }

    /**
     * SKU信息导出接口
     * 操作表quote_item 只导出商品信息
     * @author maimaiti
     */
    public function export_skuAction()
    {
        //后期添加api身份验证
        //请求验证
        if (!$this->getRequest()->isPost())
        {
            jsonReturn(null,-2101,ErrorMsg::getMessage('-2101'));
        }
        //成功导出
        $data = [
            'code'=>1,
            'message'=>ErrorMsg::getMessage('1'),
            'data'=>[
                'file'=>$this->export_sku_handler(),
                'exported_at'=>date('YmdHis')
            ]
        ];

        exit(json_encode($data));

    }


    protected function export_sku_handler()
    {
        //创建表格
        $objPHPExcel = new PHPExcel();
        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet();//获取当前sheet
        $objSheet->setTitle('询价单');//设置当前sheet标题

        //设置列宽度
        $normal_cols = ["A","B","C","D","E","F","G","H"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('16');
        endforeach;

        //填充数据
        $objSheet->setCellValue("A1","客户单号");
        $objSheet->setCellValue("B1","中文品名");
        $objSheet->setCellValue("C1","外文品名");
        $objSheet->setCellValue("D1","规格");
        $objSheet->setCellValue("E1","客户需求描述");
        $objSheet->setCellValue("F1","数量");
        $objSheet->setCellValue("G1","单位");
        $objSheet->setCellValue("H1","品牌");

        //追加数据库数据
        $sku = new QouteItemModel();
        $fields = [
            'id',//序号
            'quote_no',//询单号
            'name_cn',//中文名
            'name_en',//外文名
            'quote_spec',//规格
            'inquiry_desc',//客户需求描述
            'quote_quantity',//数量
            'quote_unit',//单位
            'quote_brand'//品牌
        ];
        $sku_items = $sku->get_quote_item_list($fields);
        if (empty($sku_items))
        {
            $data = [
                'code'=>-2102,
                'message'=>ErrorMsg::getMessage('-2102')
            ];
            exit(json_encode($data));
        }
        //P($sku_items);die;
        $item = 2;
        foreach ($sku_items as $key=>$value)
        {
            $objSheet->setCellValue("A".$item,$value['quote_no'])
                ->setCellValue("B".$item,$value['name_cn'])
                ->setCellValue("C".$item,$value['name_en'])
                ->setCellValue("D".$item,$value['quote_spec'])
                ->setCellValue("E".$item,$value['inquiry_desc'])
                ->setCellValue("F".$item,$value['quote_quantity'])
                ->setCellValue("G".$item,$value['quote_unit'])
                ->setCellValue("H".$item,$value['quote_brand']);
            $item++;
        }


        //居中设置
        $objSheet->getDefaultStyle()
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
        //保存到服务器指定目录
        return $this->export_to_disc($objWriter,"ExcelFiles","sku.xls");

    }
}
