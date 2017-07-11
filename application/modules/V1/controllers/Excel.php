<?php


/**
 * Excel操作类
 * Class ExcelController
 * @author maimaiti
 */
//class ExcelController extends PublicController
class ExcelController extends Yaf_Controller_Abstract
{
    /**
     * 报价单Excel导出api接口
     * @author maimaiti
     */
    public function quoteAction()
    {
        //获取post
        $raw = json_decode(file_get_contents("php://input"), true);
        //判断请求参数 这个就扣可以接受serial_no(流水号)或者quote_no(报价单号)
        if (empty($raw))
        {
            jsonReturn(null, -2103, ErrorMsg::getMessage('-2103'));
        }

        if (isset($raw['quote_no'])) {
            $condition = ['quote_no'=>$raw['quote_no']];
        }
        if (isset($raw['serial_no'])) {
            $condition = ['serial_no'=>$raw['serial_no']];
        }
        $result = $this->data2excelAction($condition);
        //$result = $this->testAction($condition);
        //响应数据
        exit(json_encode($result));
    }

    /**
     * 数据导出为Excel
     * @author maimaiti
     */
    private function data2excelAction($condition)
    {
        //按条件查询数据库记录
        $quote = $this->getQuoteByCondition($condition);

        //加载PHPExcel类,新建Excel表格
        $objPHPExcel = new PHPExcel();
        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet(); //获取当前sheet
        //设置报价单标题
        switch (key($condition))
        {
            case "quote_no" :
                $objSheet->setTitle('市场报价单');
                $prefix = $quote['inquiry_no']."_Q_O_";//导出文件前缀
                break;
            case "serial_no" :
                $objSheet->setTitle('商务技术报价单');
                $prefix = $quote['inquiry_no']."_Q_I_";//导出文件前缀
                break;
        }
        //设置边框
        $styleArray = [
            'borders' => [
                'outline' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '333333'],
                ],
            ],
        ];

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", '易瑞国际电子商务有限公司商务技术部')->mergeCells("A1:R2");
        $objSheet->getStyle("A3:R5")->applyFromArray($styleArray);


        $objSheet->getStyle("A1:R2")
            ->getFont()
            ->setSize(18)
            ->setBold(true);

        /* 设置A1~R1的文字属性 */
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

        //设置最小列宽度
        $small_cols = ["A", "G"];
        foreach ($small_cols as $small_col):
            $objSheet->getColumnDimension($small_col)->setWidth('9');
        endforeach;

        //设置中等列宽度
        $normal_cols = ["I", "K", "L", "N", "O", "P", "Q"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('12');
        endforeach;

        //设置最大列宽度
        $big_cols = ["B", "C", "D", "E", "F", "H", "J", "M", "R"];
        foreach ($big_cols as $big_col):
            $objSheet->getColumnDimension($big_col)->setWidth('18');
        endforeach;

        $objSheet->setCellValue("A3", "报价人 : " .$quote['quoter'])->mergeCells("A3:E3");
        $objSheet->setCellValue("A4", "电话 : ")->mergeCells("A4:E4");
        $objSheet->setCellValue("A5", "邮箱 : " .$quote['quoter_email'])->mergeCells("A5:E5");

        $objSheet->setCellValue("F3", "询价单位 : ")->mergeCells("F3:R3");
        $objSheet->setCellValue("F4", "业务对接人 : ")->mergeCells("F4:R4");
        $objSheet->setCellValue("F5", "报价时间 : " .$quote['quote_at'])->mergeCells("F5:R5");


        $objSheet->setCellValue("A6", '易瑞国际电子商务有限公司商务技术部')
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

        $objSheet->setCellValue("A7", "序号\nitem")->mergeCells("A7:A8");
        $objSheet->setCellValue("B7", "名称\nitem")->mergeCells("B7:B8");
        $objSheet->setCellValue("C7", "外文名称\nitem")->mergeCells("C7:C8");
        $objSheet->setCellValue("D7", "规格\nmodel")->mergeCells("D7:D8");
        $objSheet->setCellValue("E7", "客户需求描述\nRequirementSpecifications")->mergeCells("E7:E8");
        $objSheet->setCellValue("F7", "报价产品描述\nSupplySpecifications")->mergeCells("F7:F8");
        $objSheet->setCellValue("G7", "数量\nQty")->mergeCells("G7:G8");
        $objSheet->setCellValue("H7", "单位\nUnit")->mergeCells("H7:H8");
        $objSheet->setCellValue("I7", "产品品牌\nBrand")->mergeCells("I7:I8");
        $objSheet->setCellValue("J7", "报出EXW单价\nQuote EXW Unit Price")->mergeCells("J7:J8");
        $objSheet->setCellValue("K7", "贸易单价\nTrade Unit Price")->mergeCells("K7:K8");
        $objSheet->setCellValue("L7", "单重\nUnit\nWeight(kg)")->mergeCells("L7:L8");
        $objSheet->setCellValue("M7", "包装体积\nPacking\nSizeL*W*H(mm)")->mergeCells("M7:M8");
        $objSheet->setCellValue("N7", "包装方式\nPacking")->mergeCells("N7:N8");
        $objSheet->setCellValue("O7", "交货期\nValidity\n(Working Day)")->mergeCells("O7:O8");
        $objSheet->setCellValue("P7", "退税率\nTax RefundRate")->mergeCells("P7:P8");
        $objSheet->setCellValue("Q7", "备注\nRemark")->mergeCells("Q7:Q8");

        $cols = ["A7", "B7", "C7", "D7", "E7", "F7", "G7", "H7", "I7", "J7", "K7", "L7", "M7", "N7", "O7", "P7", "Q7"];
        foreach ($cols as $col) {
            $objSheet->getStyle($col)
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        //判断quote_item子数组
        if (is_array($quote['quote_items']) && !empty($quote['quote_items']))
        {
            $row_num = 9;
            foreach ($quote['quote_items'] as $item)
            {
                $objSheet->setCellValue("A".$row_num, $item['id']);
                $objSheet->setCellValue("B".$row_num, $item['name_cn']);
                $objSheet->setCellValue("C".$row_num, $item['name_en']);
                $objSheet->setCellValue("D".$row_num, $item['quote_spec']);
                $objSheet->setCellValue("E".$row_num, $item['inquiry_desc']);
                $objSheet->setCellValue("F".$row_num, $item['quote_desc']);
                $objSheet->setCellValue("G".$row_num, $item['quote_quantity']);
                $objSheet->setCellValue("H".$row_num, $item['quote_unit']);
                $objSheet->setCellValue("I".$row_num, $item['quote_brand']);
                $objSheet->setCellValue("J".$row_num, $item['exw_unit_price']);
                $objSheet->setCellValue("K".$row_num, $item['quote_unit_price']);
                $objSheet->setCellValue("L".$row_num, $item['unit_weight']);
                $objSheet->setCellValue("M".$row_num, $item['package_size']);
                $objSheet->setCellValue("N".$row_num, $quote['package_mode']);
                $objSheet->setCellValue("O".$row_num, $item['delivery_period']);
                $objSheet->setCellValue("P".$row_num, $item['rebate_rate']);
                $objSheet->setCellValue("Q".$row_num, $item['quote_notes']);

                //设置居中
                $cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q"];
                foreach ($cols as $col) {
                    $objSheet->getStyle($col.$row_num)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

                $row_num++;
            }
            $objSheet->getStyle("A7:K".$row_num)->applyFromArray($styleArray);

            $num10 = $row_num + 1;
            $objSheet->setCellValue("A".$num10, "")->mergeCells("A".$num10.":R".$num10);

            $num11 = $row_num + 2;
            $objSheet->setCellValue("B".$num11, "总重(kg)");
            $objSheet->setCellValue("C".$num11, $quote['total_weight']);
            $objSheet->setCellValue("D".$num11, "包装总体积(m³)");
            $objSheet->setCellValue("E".$num11, $quote['package_volumn']);
            $objSheet->setCellValue("F".$num11, "付款方式");
            $objSheet->setCellValue("G".$num11, $quote['payment_mode']);
            $objSheet->setCellValue("H".$num11, "");
            $objSheet->setCellValue("I".$num11, "");
            $objSheet->setCellValue("J".$num11, "EXW交货周期(天)");
            $objSheet->setCellValue("K".$num11, $quote['exw_delivery_period']);

            $num12 = $row_num + 3;
            $objSheet->setCellValue("B".$num12, "贸易术语");
            $objSheet->setCellValue("C".$num12, $quote['trade_terms']);
            $objSheet->setCellValue("D".$num12, "运输方式");
            $objSheet->setCellValue("E".$num12, "Ocean");
            $objSheet->setCellValue("F".$num12, "存放地");
            $objSheet->setCellValue("G".$num12, $quote['origin_place']);
            $objSheet->setCellValue("H".$num12, "目的地");
            $objSheet->setCellValue("I".$num12, $quote['destination']);
            $objSheet->setCellValue("J".$num12, "运输周期(天)");
            $objSheet->setCellValue("K".$num12, $quote['est_transport_cycle']);

            $num13 = $row_num + 4;
            $objSheet->setCellValue("B".$num13, "物流合计");
            $objSheet->setCellValue("C".$num13, $quote['total_logi_fee']);
            $objSheet->setCellValue("D".$num13, "物流合计币种");
            $objSheet->setCellValue("E".$num13, $quote['total_logi_fee_cur']);
            $objSheet->setCellValue("F".$num13, "银行费用");
            $objSheet->setCellValue("G".$num13, $quote['total_bank_fee']);
            $objSheet->setCellValue("H".$num13, "银行费用币种");
            $objSheet->setCellValue("I".$num13, $quote['total_bank_fee_cur']);
            $objSheet->setCellValue("J".$num13, "");
            $objSheet->setCellValue("K".$num13, "");

            $num14 = $row_num + 5;
            $objSheet->setCellValue("B".$num14, "EXW合计");
            $objSheet->setCellValue("C".$num14, $quote['total_exw_price']);
            $objSheet->setCellValue("D".$num14, "EXW合计币种");
            $objSheet->setCellValue("E".$num14, $quote['total_exw_cur']);
            $objSheet->setCellValue("F".$num14, "出信用保险");
            $objSheet->setCellValue("G".$num14, $quote['total_insu_fee']);
            $objSheet->setCellValue("H".$num14, "出信用保险币种");
            $objSheet->setCellValue("I".$num14, $quote['total_insu_fee_cur']);
            $objSheet->setCellValue("J".$num14, "");
            $objSheet->setCellValue("K".$num14, "");

            $num15 = $row_num + 6;
            $objSheet->setCellValue("B".$num15, "报价合计");
            $objSheet->setCellValue("C".$num15, $quote['total_quote_price']);
            $objSheet->setCellValue("D".$num15, "1报价合计币种");
            $objSheet->setCellValue("E".$num15, $quote['total_quote_cur']);
            $objSheet->setCellValue("F".$num15, "");
            $objSheet->setCellValue("G".$num15, "");
            $objSheet->setCellValue("H".$num15, "");
            $objSheet->setCellValue("I".$num15, "");
            $objSheet->setCellValue("J".$num15, "");
            $objSheet->setCellValue("K".$num15, "");

            $objSheet->getStyle("A".$num11.":K".$num15)->applyFromArray($styleArray);

            $total_rows = [
                "A".$num11, "A".$num12, "A".$num13, "A".$num14, "A".$num15, "B".$num11, "B".$num12, "B".$num13, "B".$num14, "B".$num15,
                "C".$num11, "C".$num12, "C".$num13, "C".$num14, "C".$num15, "D".$num11, "D".$num12, "D".$num13, "D".$num14, "D".$num15,
                "E".$num11, "E".$num12, "E".$num13, "E".$num14, "E".$num15, "F".$num11, "F".$num12, "F".$num13, "F".$num14, "F".$num15,
                "G".$num11, "G".$num12, "G".$num13, "G".$num14, "G".$num15, "H".$num11, "H".$num12, "H".$num13, "H".$num14, "H".$num15,
                "I".$num11, "I".$num12, "I".$num13, "I".$num14, "I".$num15, "J".$num11, "J".$num12, "J".$num13, "J".$num14, "J".$num15,
                "K".$num11, "K".$num12, "K".$num13, "K".$num14, "K".$num15,
            ];
            foreach ($total_rows as $total_row) {
                $objSheet->getCell($total_row)->getStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getStyle($total_row)->applyFromArray($styleArray);
            }

            $num16 = $row_num + 7; $num17 = $row_num + 8;
            $objSheet->setCellValue("A".$num16, '报价备注 : ' .$quote['quote_notes'])->mergeCells("A".$num16.":K".$num17);
            $objSheet->getStyle("A".$num16.":K".$num17)->applyFromArray($styleArray);
            $objSheet->getCell("A".$num16)
                ->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num18 = $row_num + 9; $num19 = $row_num + 10;
            $objSheet->setCellValue("A".$num18, '物流备注 : ' .$quote['logi_notes'])->mergeCells("A".$num18.":K".$num19);
            $objSheet->getStyle("A".$num18.":K".$num19)->applyFromArray($styleArray);
            $objSheet->getCell("A".$num18)
                ->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num20 = $row_num + 11; $num21 = $row_num + 12;
            $objSheet->setCellValue("A".$num20, "")->mergeCells("A".$num20.":K".$num21);
            $objSheet->getStyle("A".$num20.":K".$num21)->applyFromArray($styleArray);

        }else{
            //p('no quote_item data');
        }

        //添加logo

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");

        //保存到服务器指定目录
        $file =  $this->export_to_disc($objWriter, "ExcelFiles", $prefix.date('Ymd_His').".xls");
        if (!is_file($file) && !file_exists($file))
        {
            $data = [
                'code'=>-2105,
                'message'=>ErrorMsg::getMessage('-2105')
            ];
        }else{
            $data = [
                'code'=>1,
                'message'=>ErrorMsg::getMessage('1'),
                'data'=>[
                    'file'=>$file,
                    'exported_at'=>time('YmdHis')
                ]
            ];
        }
        return $data;

    }
    /**
     * 获取数据库信息，并重组返回
     * @author maimaiti
     * @return array $data 返回数据
     */
    private function getQuoteByCondition($condition)
    {
        //第一次查询判断
        $quoteObj = new QuoteModel();
        $firstCondition = $quoteObj->where($condition)->find();
        if (!$firstCondition)
        {
            jsonReturn(null, -2102, ErrorMsg::getMessage('-2102'));
        }

        //第二次真正获取数据并且重组数组结构
        $fields = [
            'id',//编号
            'inquiry_no',
            'quote_no',
            'quoter', //商务报价人
            'quoter_email', //商务报价人邮箱
            'quote_at', //商务报价时间
            'serial_no',//项目代码(流水号)
            'notes',//备注
            'quote_notes',//商务报价备注
            'logi_notes',//物流备注
            'total_weight',//总重
            'package_volumn',//包装总体积
            'trade_terms',//贸易术语
            'total_logi_fee',//物流合计
            'total_logi_fee_cur',//物流合计币种
            'total_exw_price',//EXW合计
            'total_exw_cur',//EXW合计币种
            'total_quote_price',//报价合计
            'total_quote_cur',//报价合计币种
            'payment_mode',//付款方式
            'origin_place',//存放地
            'destination',//目的地
            'total_bank_fee',//银行费用
            'total_bank_fee_cur',//银行费用币种
            'total_insu_fee',//出口信用保险费用
            'total_insu_fee_cur',//出口信用保险费用币种
            'exw_delivery_period',//EXW交货周期
            'est_transport_cycle',//预计运输周期
            'package_mode'//包装方式
        ];
        //询单本身信息
        $data = $quoteObj->where($condition)->field($fields, false)->find();
        //追加询单明细信息
        $quote_item_model = new QuoteItemModel();
        $quote_item_fields = [
            'id',//编号
            'name_cn',//中文名称
            'name_en',//外文名称
            'quote_spec',//规格
            'inquiry_desc',//客户需求描述
            'quote_desc',//报价产品描述
            'quote_quantity',//数量
            'quote_unit',//单位
            'quote_brand',//品牌
            'exw_unit_price',//EXW单价
            'quote_unit_price',//贸易单价
            'unit_weight',//单重
            'package_size',//包装尺寸
            'delivery_period',//交货期
            'rebate_rate',//退税率
            'quote_notes',//备注(商务技术备注)
        ];
        $where = ['quote_no'=>$data['quote_no']];
        $data['quote_items'] = $quote_item_model->where($where)->field($quote_item_fields)->select();
        return $data;
    }

    /**
     * 我的测试
     * @param $condition
     * @return array
     */
    public function testAction($condition)
    {
        //按条件查询数据库记录
        $quote = $this->getQuoteByCondition($condition);

        //加载PHPExcel类,新建Excel表格
        $objPHPExcel = new PHPExcel();
        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet(); //获取当前sheet
        //设置报价单标题
        switch (key($condition))
        {
            case "quote_no" :
                $objSheet->setTitle('市场报价单');
                $prefix = $quote['inquiry_no']."_Q_O_";//导出文件前缀
                break;
            case "serial_no" :
                $objSheet->setTitle('商务技术报价单');
                $prefix = $quote['inquiry_no']."_Q_I_";//导出文件前缀
                break;
        }
        //设置边框
        $styleArray = [
            'borders' => [
                'outline' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '333333'],
                ],
            ],
        ];

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", '易瑞国际电子商务有限公司商务技术部')->mergeCells("A1:R2");
        $objSheet->getStyle("A3:R5")->applyFromArray($styleArray);


        $objSheet->getStyle("A1:R2")
            ->getFont()
            ->setSize(18)
            ->setBold(true);

        /* 设置A1~R1的文字属性 */
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

        //设置最小列宽度
        $small_cols = ["A", "G"];
        foreach ($small_cols as $small_col):
            $objSheet->getColumnDimension($small_col)->setWidth('9');
        endforeach;

        //设置中等列宽度
        $normal_cols = ["I", "K", "L", "N", "O", "P", "Q"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('12');
        endforeach;

        //设置最大列宽度
        $big_cols = ["B", "C", "D", "E", "F", "H", "J", "M", "R"];
        foreach ($big_cols as $big_col):
            $objSheet->getColumnDimension($big_col)->setWidth('18');
        endforeach;

        $objSheet->setCellValue("A3", "报价人 : " .$quote['quoter'])->mergeCells("A3:E3");
        $objSheet->setCellValue("A4", "电话 : ")->mergeCells("A4:E4");
        $objSheet->setCellValue("A5", "邮箱 : " .$quote['quoter_email'])->mergeCells("A5:E5");

        $objSheet->setCellValue("F3", "询价单位 : ")->mergeCells("F3:R3");
        $objSheet->setCellValue("F4", "业务对接人 : ")->mergeCells("F4:R4");
        $objSheet->setCellValue("F5", "报价时间 : " .$quote['quote_at'])->mergeCells("F5:R5");


        $objSheet->setCellValue("A6", '易瑞国际电子商务有限公司商务技术部')
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

        $objSheet->setCellValue("A7", "序号\nitem")->mergeCells("A7:A8");
        $objSheet->setCellValue("B7", "名称\nitem")->mergeCells("B7:B8");
        $objSheet->setCellValue("C7", "外文名称\nitem")->mergeCells("C7:C8");
        $objSheet->setCellValue("D7", "规格\nmodel")->mergeCells("D7:D8");
        $objSheet->setCellValue("E7", "客户需求描述\nRequirementSpecifications")->mergeCells("E7:E8");
        $objSheet->setCellValue("F7", "报价产品描述\nSupplySpecifications")->mergeCells("F7:F8");
        $objSheet->setCellValue("G7", "数量\nQty")->mergeCells("G7:G8");
        $objSheet->setCellValue("H7", "单位\nUnit")->mergeCells("H7:H8");
        $objSheet->setCellValue("I7", "产品品牌\nBrand")->mergeCells("I7:I8");
        $objSheet->setCellValue("J7", "报出EXW单价\nQuote EXW Unit Price")->mergeCells("J7:J8");
        $objSheet->setCellValue("K7", "贸易单价\nTrade Unit Price")->mergeCells("K7:K8");
        $objSheet->setCellValue("L7", "单重\nUnit\nWeight(kg)")->mergeCells("L7:L8");
        $objSheet->setCellValue("M7", "包装体积\nPacking\nSizeL*W*H(mm)")->mergeCells("M7:M8");
        $objSheet->setCellValue("N7", "包装方式\nPacking")->mergeCells("N7:N8");
        $objSheet->setCellValue("O7", "交货期\nValidity\n(Working Day)")->mergeCells("O7:O8");
        $objSheet->setCellValue("P7", "退税率\nTax RefundRate")->mergeCells("P7:P8");
        $objSheet->setCellValue("Q7", "备注\nRemark")->mergeCells("Q7:Q8");

        $cols = ["A7", "B7", "C7", "D7", "E7", "F7", "G7", "H7", "I7", "J7", "K7", "L7", "M7", "N7", "O7", "P7", "Q7"];
        foreach ($cols as $col) {
            $objSheet->getStyle($col)
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }


        //判断quote_item子数组
        if (is_array($quote['quote_items']) && !empty($quote['quote_items']))
        {
            $row_num = 9;
            foreach ($quote['quote_items'] as $item)
            {
                $objSheet->setCellValue("A".$row_num, $item['id']);
                $objSheet->setCellValue("B".$row_num, $item['name_cn']);
                $objSheet->setCellValue("C".$row_num, $item['name_en']);
                $objSheet->setCellValue("D".$row_num, $item['quote_spec']);
                $objSheet->setCellValue("E".$row_num, $item['inquiry_desc']);
                $objSheet->setCellValue("F".$row_num, $item['quote_desc']);
                $objSheet->setCellValue("G".$row_num, $item['quote_quantity']);
                $objSheet->setCellValue("H".$row_num, $item['quote_unit']);
                $objSheet->setCellValue("I".$row_num, $item['quote_brand']);
                $objSheet->setCellValue("J".$row_num, $item['exw_unit_price']);
                $objSheet->setCellValue("K".$row_num, $item['quote_unit_price']);
                $objSheet->setCellValue("L".$row_num, $item['unit_weight']);
                $objSheet->setCellValue("M".$row_num, $item['package_size']);
                $objSheet->setCellValue("N".$row_num, $quote['package_mode']);
                $objSheet->setCellValue("O".$row_num, $item['delivery_period']);
                $objSheet->setCellValue("P".$row_num, $item['rebate_rate']);
                $objSheet->setCellValue("Q".$row_num, $item['quote_notes']);

                //设置居中
                $cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q"];
                foreach ($cols as $col) {
                    $objSheet->getStyle($col.$row_num)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

                $row_num++;
            }
            $objSheet->getStyle("A7:K".$row_num)->applyFromArray($styleArray);

            $num10 = $row_num + 1;
            $objSheet->setCellValue("A".$num10, "")->mergeCells("A".$num10.":R".$num10);

            $num11 = $row_num + 2;
            $objSheet->setCellValue("B".$num11, "总重(kg)");
            $objSheet->setCellValue("C".$num11, $quote['total_weight']);
            $objSheet->setCellValue("D".$num11, "包装总体积(m³)");
            $objSheet->setCellValue("E".$num11, $quote['package_volumn']);
            $objSheet->setCellValue("F".$num11, "付款方式");
            $objSheet->setCellValue("G".$num11, $quote['payment_mode']);
            $objSheet->setCellValue("H".$num11, "");
            $objSheet->setCellValue("I".$num11, "");
            $objSheet->setCellValue("J".$num11, "EXW交货周期(天)");
            $objSheet->setCellValue("K".$num11, $quote['exw_delivery_period']);

            $num12 = $row_num + 3;
            $objSheet->setCellValue("B".$num12, "贸易术语");
            $objSheet->setCellValue("C".$num12, $quote['trade_terms']);
            $objSheet->setCellValue("D".$num12, "运输方式");
            $objSheet->setCellValue("E".$num12, "Ocean");
            $objSheet->setCellValue("F".$num12, "存放地");
            $objSheet->setCellValue("G".$num12, $quote['origin_place']);
            $objSheet->setCellValue("H".$num12, "目的地");
            $objSheet->setCellValue("I".$num12, $quote['destination']);
            $objSheet->setCellValue("J".$num12, "运输周期(天)");
            $objSheet->setCellValue("K".$num12, $quote['est_transport_cycle']);

            $num13 = $row_num + 4;
            $objSheet->setCellValue("B".$num13, "物流合计");
            $objSheet->setCellValue("C".$num13, $quote['total_logi_fee']);
            $objSheet->setCellValue("D".$num13, "物流合计币种");
            $objSheet->setCellValue("E".$num13, $quote['total_logi_fee_cur']);
            $objSheet->setCellValue("F".$num13, "银行费用");
            $objSheet->setCellValue("G".$num13, $quote['total_bank_fee']);
            $objSheet->setCellValue("H".$num13, "银行费用币种");
            $objSheet->setCellValue("I".$num13, $quote['total_bank_fee_cur']);
            $objSheet->setCellValue("J".$num13, "");
            $objSheet->setCellValue("K".$num13, "");

            $num14 = $row_num + 5;
            $objSheet->setCellValue("B".$num14, "EXW合计");
            $objSheet->setCellValue("C".$num14, $quote['total_exw_price']);
            $objSheet->setCellValue("D".$num14, "EXW合计币种");
            $objSheet->setCellValue("E".$num14, $quote['total_exw_cur']);
            $objSheet->setCellValue("F".$num14, "出信用保险");
            $objSheet->setCellValue("G".$num14, $quote['total_insu_fee']);
            $objSheet->setCellValue("H".$num14, "出信用保险币种");
            $objSheet->setCellValue("I".$num14, $quote['total_insu_fee_cur']);
            $objSheet->setCellValue("J".$num14, "");
            $objSheet->setCellValue("K".$num14, "");

            $num15 = $row_num + 6;
            $objSheet->setCellValue("B".$num15, "报价合计");
            $objSheet->setCellValue("C".$num15, $quote['total_quote_price']);
            $objSheet->setCellValue("D".$num15, "1报价合计币种");
            $objSheet->setCellValue("E".$num15, $quote['total_quote_cur']);
            $objSheet->setCellValue("F".$num15, "");
            $objSheet->setCellValue("G".$num15, "");
            $objSheet->setCellValue("H".$num15, "");
            $objSheet->setCellValue("I".$num15, "");
            $objSheet->setCellValue("J".$num15, "");
            $objSheet->setCellValue("K".$num15, "");

            $objSheet->getStyle("A".$num11.":K".$num15)->applyFromArray($styleArray);

            $total_rows = [
                "A".$num11, "A".$num12, "A".$num13, "A".$num14, "A".$num15, "B".$num11, "B".$num12, "B".$num13, "B".$num14, "B".$num15,
                "C".$num11, "C".$num12, "C".$num13, "C".$num14, "C".$num15, "D".$num11, "D".$num12, "D".$num13, "D".$num14, "D".$num15,
                "E".$num11, "E".$num12, "E".$num13, "E".$num14, "E".$num15, "F".$num11, "F".$num12, "F".$num13, "F".$num14, "F".$num15,
                "G".$num11, "G".$num12, "G".$num13, "G".$num14, "G".$num15, "H".$num11, "H".$num12, "H".$num13, "H".$num14, "H".$num15,
                "I".$num11, "I".$num12, "I".$num13, "I".$num14, "I".$num15, "J".$num11, "J".$num12, "J".$num13, "J".$num14, "J".$num15,
                "K".$num11, "K".$num12, "K".$num13, "K".$num14, "K".$num15,
            ];
            foreach ($total_rows as $total_row) {
                $objSheet->getCell($total_row)->getStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getStyle($total_row)->applyFromArray($styleArray);
            }

            $num16 = $row_num + 7; $num17 = $row_num + 8;
            $objSheet->setCellValue("A".$num16, '报价备注 : ' .$quote['quote_notes'])->mergeCells("A".$num16.":K".$num17);
            $objSheet->getStyle("A".$num16.":K".$num17)->applyFromArray($styleArray);
            $objSheet->getCell("A".$num16)
                ->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num18 = $row_num + 9; $num19 = $row_num + 10;
            $objSheet->setCellValue("A".$num18, '物流备注 : ' .$quote['logi_notes'])->mergeCells("A".$num18.":K".$num19);
            $objSheet->getStyle("A".$num18.":K".$num19)->applyFromArray($styleArray);
            $objSheet->getCell("A".$num18)
                ->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num20 = $row_num + 11; $num21 = $row_num + 12;
            $objSheet->setCellValue("A".$num20, "")->mergeCells("A".$num20.":K".$num21);
            $objSheet->getStyle("A".$num20.":K".$num21)->applyFromArray($styleArray);

        }else{
            //p('no quote_item data');
        }

        //添加logo

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");

        //保存到服务器指定目录
        $file =  $this->export_to_disc($objWriter, "ExcelFiles", $prefix.date('Ymd_His').".xls");
        if (!is_file($file) && !file_exists($file))
        {
            $data = [
                'code'=>-2105,
                'message'=>ErrorMsg::getMessage('-2105')
            ];
        }else{
            $data = [
                'code'=>1,
                'message'=>ErrorMsg::getMessage('1'),
                'data'=>[
                    'file'=>$file,
                    'exported_at'=>time('YmdHis')
                ]
            ];
        }
        return $data;

    }
    /**
     * 保存到服务器指定目录
     * @param $obj  PHPExcel写入对象
     * @param $path 保存目录
     */
    private function export_to_disc($obj, $path, $filename) {
        //保存路径，不存在则创建
        $appPath = $_SERVER['DOCUMENT_ROOT'] . "/application/";
        $savePath = $appPath . $path . "/";
        //echo $_SERVER['DOCUMENT_ROOT'];die;
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }
        $saveName = $savePath . $filename;
        $obj->save($saveName);
        return $saveName;
    }

    /**
     * SKU信息导出接口
     * 操作表quote_item 只导出商品信息
     * @author maimaiti
     */
    public function quoteItemAction()
    {
        $file = $this->export_sku_handler();
        $data = [
            'code'=>1,
            'message'=>'成功',
            'data'=>[
                'file'=>$file,
                'exported_at'=>time()
            ]
        ];
        exit(json_encode($data));
    }

    /**
     * 导出询价单列表
     * @return string 导出文件
     */
    protected function export_sku_handler() {
        //创建表格
        $objPHPExcel = new PHPExcel();
        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet(); //获取当前sheet
        $objSheet->setTitle('询价单'); //设置当前sheet标题
        //设置列宽度
        $normal_cols = ["A", "B", "C", "D", "E", "F", "G", "H"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('16');
        endforeach;

        //填充数据
        $objSheet->setCellValue("A1", "客户单号");
        $objSheet->setCellValue("B1", "中文品名");
        $objSheet->setCellValue("C1", "外文品名");
        $objSheet->setCellValue("D1", "规格");
        $objSheet->setCellValue("E1", "客户需求描述");
        $objSheet->setCellValue("F1", "数量");
        $objSheet->setCellValue("G1", "单位");
        $objSheet->setCellValue("H1", "品牌");

        //追加数据库数据
        $sku = new QuoteItemModel();
        $fields = [
            'id', //序号
            'quote_no', //询单号
            'name_cn', //中文名
            'name_en', //外文名
            'quote_spec', //规格
            'inquiry_desc', //客户需求描述
            'quote_quantity', //数量
            'quote_unit', //单位
            'quote_brand'//品牌
        ];
        $sku_items = $sku->field($fields)->select();
        if (empty($sku_items)) {
            $data = [
                'code' => -2102,
                'message' => ErrorMsg::getMessage('-2102')
            ];
            exit(json_encode($data));
        }
        //P($sku_items);die;
        $item = 2;
        foreach ($sku_items as $key => $value) {
            $objSheet->setCellValue("A" . $item, $value['quote_no'])
                ->setCellValue("B" . $item, $value['name_cn'])
                ->setCellValue("C" . $item, $value['name_en'])
                ->setCellValue("D" . $item, $value['quote_spec'])
                ->setCellValue("E" . $item, $value['inquiry_desc'])
                ->setCellValue("F" . $item, $value['quote_quantity'])
                ->setCellValue("G" . $item, $value['quote_unit'])
                ->setCellValue("H" . $item, $value['quote_brand']);
            $item++;
        }


        //居中设置
        $objSheet->getDefaultStyle()
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //保存到服务器指定目录
        return $this->export_to_disc($objWriter, "ExcelFiles", date('Ymd_His')."_QI.xls");
    }

    /**
     * 导入报价单明细数据接口
     * @author maimaiti
     */
    public function importQuoteItemAction()
    {
        //1.接受Excel文件并核验
        $post = json_decode(file_get_contents("php://input"),true);
        echo $post['file'];
        $excel_file = strstr($post['file'],'/');
        p($excel_file);
        if (is_file($excel_file))
        {
            p($excel_file);
        }else{
            p('die here');
        }
        if(!$this->check_remote_file_exists($excel_file))
        {
            jsonReturn(null,-2104,ErrorMsg::getMessage('-2104'));
        }

        //2.分析数据并进行导入
        //$excel_file = APPLICATION_PATH."/ExcelFiles/sku_null.xls";
        $this->importQuoteItemHandler($excel_file);

        //3.响应(Response)
        $response = ['code'=>-2105,'message'=>'导入成功','data'=>[]];
        exit(json_encode($response));
    }
    /**
     * 导入报价单明细数据处理
     * @param $excel_file string  导入文件完整路径
     * @author maimaiti
     */
    private function importQuoteItemHandler($excel_file)
    {
        //获取文件类型
        $fileType = PHPExcel_IOFactory::identify($excel_file);
        //创建PHPExcel读取对象
        $objReader = PHPExcel_IOFactory::createReader($fileType);
        //加载文件并读取
        $excelData = $objReader->load($excel_file)->getSheet(0)->toArray();
        //弹出数组第一个item
        array_shift($excelData);

        //判读有没有可导入的数据
        if (empty($data))
        {
            $response = ['code'=>-2105,'message'=>ErrorMsg::getMessage('-2105'),'data'=>[]];
            exit(json_encode($response));
        }

        //遍历重组
        //TODO 这里后期优化为PHPExcel自动映射到数据库字段的类
        foreach ($excelData as $k=>$v)
        {
            $data[$k]['quote_no'] = $v[0];//询单号
            $data[$k]['name_cn'] = $v[1];//中文名
            $data[$k]['name_en'] = $v[2];//外文名
            $data[$k]['quote_spec'] = $v[3];//规格
            $data[$k]['inquiry_desc'] = $v[4];//客户需求描述
            $data[$k]['quote_quantity'] = $v[5];//数量
            $data[$k]['quote_unit'] = $v[6];//单位
            $data[$k]['quote_brand'] = $v[7];//品牌
            $data[$k]['created_at'] = date('YmdHis');
        }

        //批量添加到数据库中
        $this->data2base($data);

    }

    /**
     * 数据添加到数据库当中
     * @param $data 要添加的数据
     */
    private function data2base($data)
    {
        $model = new QuoteItemModel();
        //TODO 这里后期改善为批量插入，代替当前的循环插入
        foreach ($data as $k=>$v)
        {
            $model->add($v);
        }
        return true;
    }

    /**
     * 导出全部商品
     * @author maimaiti
     */
    public function goodsListAction()
    {
        //判断语言参数
        $request = json_decode(file_get_contents("php://input"),true);
        $goodsModel = new GoodsModel();
        //条件
        $condition = [];
        //筛选字段
        $field = [
            //序号
            'id',
            //询价单号
            'quote_no',
            //询价单位
            //所属地区
            //客户名称
            //中文名
            'name_cn',
            //外文名
            'name_en',
            //规格
            'quote_spec',
            //图号，数量
            'quote_quantity',
            //单位
            'quote_unit',
            //产品品牌
            'quote_brand',
            //报价单位

            //供应商报价人
            'supplier_contact',
            //报价人联系方式
            'supplier_contact_phone',
            //厂家单价
            //厂家总价
            //利润率
            //报价单价
            'quote_unit_price',
            //报价总价
            'total_quote_price',
            //报价总金额(美金)
            //单重
            //总重
            //包装体积
            //包装方式
            //交货期
            //有效期
            //备注
            //产品分类
            //是否科瑞设备用配件
            //是否投标
            //转入日期
            //需用日期
            //报出日期
            //报价用时
            //市场负责人
            //商务技术部报价人
            //贸易术语
        ];
        if (!empty($request['lang']))
        {
            $condition = ['lang'=>strtolower($request['lang'])];
        }
        /*$goods = $goodsModel->where($condition)->select();
        echo "<pre>";
        print_r($goods);*/
        $file = $this->goodsListHandler();

        $response = [
            'code'=>1,
            'message'=>ErrorMsg::getMessage('1'),
            'data'=>[
                'file'=>$file,
                //文件创建时间(文件创建时是指xls文件被导出的时间)
                'exported_at'=>date('Y-m-d H:i:s')
            ]
        ];

        exit(json_encode($response));
    }

    /**
     * 导出全部商品处理
     * @return string
     */
    private function goodsListHandler()
    {
        //创建表格
        $objPHPExcel = new PHPExcel();
        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet(); //获取当前sheet
        $objSheet->setTitle('商品列表'); //设置当前sheet标题
        //设置列宽度
        $normal_cols = [
            "A", "B", "C", "D", "E", "F", "G", "H","I","J","K","L",
            "M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
            "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ"
        ];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('16');
        endforeach;

        //填充数据
        $objSheet->setCellValue("A1", "序号");
        $objSheet->setCellValue("B1", "询价单号");
        $objSheet->setCellValue("C1", "询价单位");
        $objSheet->setCellValue("D1", "所属地区");
        $objSheet->setCellValue("E1", "客户名称");
        $objSheet->setCellValue("F1", "品名中文");
        $objSheet->setCellValue("G1", "品名外文");
        $objSheet->setCellValue("H1", "图号 数量");
        $objSheet->setCellValue("I1", "单位");
        $objSheet->setCellValue("J1", "产品品牌");
        $objSheet->setCellValue("K1", "报价单位");
        $objSheet->setCellValue("L1", "供应商报价人");
        $objSheet->setCellValue("M1", "供应商联系方式");
        $objSheet->setCellValue("N1", "厂家单价");
        $objSheet->setCellValue("O1", "厂家总价");
        $objSheet->setCellValue("P1", "利润率");
        $objSheet->setCellValue("Q1", "报价单价");
        $objSheet->setCellValue("R1", "报价总价");
        $objSheet->setCellValue("S1", "报价总金额(美金)");
        $objSheet->setCellValue("T1", "单重");
        $objSheet->setCellValue("U1", "总重");
        $objSheet->setCellValue("V1", "包装体积");
        $objSheet->setCellValue("W1", "包装方式");
        $objSheet->setCellValue("X1", "交货期");
        $objSheet->setCellValue("Y1", "有效期");
        $objSheet->setCellValue("Z1", "备注");
        $objSheet->setCellValue("AA1", "产品分类");
        $objSheet->setCellValue("AB1", "是否科瑞设备用配件");
        $objSheet->setCellValue("AC1", "是否投标");
        $objSheet->setCellValue("AD1", "转入日期");
        $objSheet->setCellValue("AE1", "需用日期");
        $objSheet->setCellValue("AF1", "报出日期");
        $objSheet->setCellValue("AG1", "报价用时");
        $objSheet->setCellValue("AH1", "市场负责人");
        $objSheet->setCellValue("AI1", "商务技术部报价人");
        $objSheet->setCellValue("AJ1", "贸易术语");

        //追加数据库数据

        //P($sku_items);die;
        /*        $item = 2;
                foreach ($sku_items as $key => $value) {
                    $objSheet->setCellValue("A" . $item, $value['quote_no'])
                        ->setCellValue("B" . $item, $value['name_cn'])
                        ->setCellValue("C" . $item, $value['name_en'])
                        ->setCellValue("D" . $item, $value['quote_spec'])
                        ->setCellValue("E" . $item, $value['inquiry_desc'])
                        ->setCellValue("F" . $item, $value['quote_quantity'])
                        ->setCellValue("G" . $item, $value['quote_unit'])
                        ->setCellValue("H" . $item, $value['quote_brand']);
                    $item++;
                }*/


        //居中设置
        $objSheet->getDefaultStyle()
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //$objWriter->save('demo.xlsx');
        //$objWriter->save(dirname(__FILE__)."/dir1"."/".date('YmdHis')."_goods".".xlsx");
        //保存到服务器指定目录
        return $this->export_to_disc($objWriter, "ExcelFiles", date('YmdHis',time())."_goodsList.xls");
    }


    /**
     * 导出询价单模板接口(市场询报价)
     */
    public function getMarketInquiryTemplateAction()
    {
        //导出询价单模板
        $inquiryTemplateFile = $_SERVER['DOCUMENT_ROOT']."/application/ExcelFiles/marketInquiryTemplate.xls";

        //判断文件的真实性和是否存在
        $response = ['code'=>1,'message'=>ErrorMsg::getMessage('1'),'data'=>['file'=>$inquiryTemplateFile]] ;

        exit(json_encode($response));
    }
    /**
     * 导出询价单模板接口(市场询报价)
     */
    public function getBusinessInquiryTemplateAction()
    {

        //导出询价单模板
        $inquiryTemplateFile = $_SERVER['DOCUMENT_ROOT']."/application/ExcelFiles/businessInquiryTemplate.xls";
        //判断文件的真实性和是否存在
        $response = ['code'=>1,'message'=>ErrorMsg::getMessage('1'),'data'=>['file'=>$inquiryTemplateFile]] ;
        exit(json_encode($response));
    }

    /**
     * 导出询单明细接口
     * url：http://xx.com/V1/Excel/inquiryDetail
     * @author maimaiti
     */
    public function inquiryDetailAction()
    {
        //获取参数
        $request = json_decode(file_get_contents("php://input"),true);
        //流水号
        $serial_no = $request['serial_no'];

        //查找数据
        $inquiryModel = new InquiryModel();
        $where = [ 'serial_no' =>  $serial_no ];
        $field = [
            'id',//序号
            //商品ID
            'inquiry_no',//客户询单号
            //商品数据来源
            //商品名称
            //外文品名
            //规格
            //客户需求描述
            //报价产品描述
            //数量
            //单位
            //品牌
            //产品分类
            //供应商单位
            //供应商联系方式
            //采购单价
            //EXW单价
            //报出单价
            //贸易单价
            //单重
            //包装尺寸
            //交货期(天)
            //未报价分析
            //产品来源
            //退税率
            //商务技术备注
            //报价有效期
        ];
        $inquiryDetail = $inquiryModel->where($where)->field($field)->find();
        if (!$inquiryDetail)
        {
            $response = ['code'=>-2102,'message'=>ErrorMsg::getMessage('-2102'),'data'=>[]];
        }else{
            //创建表格并填充数据
            $file = $this->createInquiryDetailExcel($inquiryDetail);
            $response = [
                'code'=>1,
                'message'=>ErrorMsg::getMessage('1'),
                'data'=>[
                    'file'=>$file,
                    'exported_at'=>time()
                ]
            ];

        }
        exit(json_encode($response));
    }

    /**
     * 创建询单明细表格并填充数据
     * @param $item array 当前询单明细数据
     */
    private function createInquiryDetailExcel($item)
    {
        //创建表格
        $objPHPExcel = new PHPExcel();
        //2.创建sheet(内置表)
        $objSheet = $objPHPExcel->getActiveSheet(); //获取当前sheet
        $objSheet->setTitle('询单明细'); //设置当前sheet标题
        //设置列宽度
        $normal_cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L",
            "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X",
            "Y", "Z","AA"
        ];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('16');
        endforeach;

        //填充数据
        $objSheet->setCellValue("A1", "序号");
        $objSheet->setCellValue("B1", "商品ID");
        $objSheet->setCellValue("C1", "客户询单号");
        $objSheet->setCellValue("D1", "商品数据来源");
        $objSheet->setCellValue("E1", "商品名称");
        $objSheet->setCellValue("F1", "外文品名");
        $objSheet->setCellValue("G1", "规格");
        $objSheet->setCellValue("H1", "客户需求描述");
        $objSheet->setCellValue("I1", "报价产品描述");
        $objSheet->setCellValue("J1", "数量");
        $objSheet->setCellValue("K1", "单位");
        $objSheet->setCellValue("L1", "品牌");
        $objSheet->setCellValue("M1", "产品分类");
        $objSheet->setCellValue("N1", "供应商单位");
        $objSheet->setCellValue("O1", "供应商联系方式");
        $objSheet->setCellValue("P1", "采购单价");
        $objSheet->setCellValue("Q1", "EXW单价");
        $objSheet->setCellValue("R1", "报出单价");
        $objSheet->setCellValue("S1", "贸易单价");
        $objSheet->setCellValue("T1", "单重");
        $objSheet->setCellValue("U1", "包装尺寸");
        $objSheet->setCellValue("V1", "交货期(天)");
        $objSheet->setCellValue("W1", "未报价分析");
        $objSheet->setCellValue("X1", "产品来源");
        $objSheet->setCellValue("Y1", "退税率");
        $objSheet->setCellValue("Z1", "商务技术备注");
        $objSheet->setCellValue("AA1", "报价有效期");

        //TODO 待完善数据库字段对应

        //居中设置
        $objSheet->getDefaultStyle()
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //保存到服务器指定目录
        return $this->export_to_disc($objWriter, "ExcelFiles", date('YmdHis')."_IQD.xls");
    }
}
