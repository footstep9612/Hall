<?php

/**
 * @desc   系统Excel相关操作处理
 * @Author 买买提
 */
class ExcelmanagerController extends PublicController
{

    public function init(){
        //parent::init();
    }


    public function uploadAction(){
        $this->getView()->assign("content", "Hello World");
        $this->display('upload');
    }
    /**
     * @desc 获取请求
     * @return mixed
     */
    private function requestParams(){
        return json_decode(file_get_contents("php://input"),true);
    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params=''){

        $request = $this->requestParams();
        unset($request['token']);

        //判断筛选字段为空的情况
        if ($params){
            $params = explode(',',$params);
            foreach ($params as $param){
                if (empty($request[$param])) $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
            }
        }
        return $request;
    }

    /**
     * @desc 下载sku导入模板(询单管理->新增询单)
     */
    public function downloadInquirySkuTemplateAction(){
        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => [
                'url' => 'http://file01.erui.com/group1/M00/00/03/rBFgyFmegqKAIt1pAAAm7A4b9LA55.xlsx'
            ]
        ]);
    }

    /**
     * @desc 导入sku(询单管理->新增询单)
     */
    public function importSkuAction(){

        //测试地址 有数据 http://file01.erui.com/group1/M00/00/03/rBFgyFmegTCATPDzAAAniCErWEI19.xlsx
        //测试地址 没有数据 http://file01.erui.com/group1/M00/00/03/rBFgyFmegqKAIt1pAAAm7A4b9LA55.xlsx
        $request = $this->validateRequests('inquiry_id,file_url');

        $remoteFile = $request['file_url'];
        $inquiry_id = $request['inquiry_id'];
        //下载到本地临时文件
        $localFile = ExcelHelperTrait::download2local($remoteFile);
        $data = ExcelHelperTrait::ready2import($localFile);

        $response = $this->importSkuHandler($localFile,$data,$inquiry_id);
        $this->jsonReturn($response);

    }

    /**
     * 执行导入操作
     * @param $data
     *
     * @return array
     */
    private function importSkuHandler($localFile,$data,$inquiry_id){

        array_shift($data);//去掉第一行数据(excel文件的标题)
        //p($data);
        if (empty($data)){
            return ['code' => '-104','message' => '没有可导入的数据','data' => ''];
        }

        //遍历重组
        foreach ($data as $k=>$v){
            $sku[$k]['sku'] = $v[1];//平台sku
            $sku[$k]['inquiry_id'] = $inquiry_id;//询单id
            $sku[$k]['buyer_goods_no'] = $v[2];//客户询单号
            $sku[$k]['name'] = $v[3];//外文品名
            $sku[$k]['name_zh'] = $v[4];//中文品名
            $sku[$k]['model'] = $v[5];//型号
            $sku[$k]['remarks'] = $v[6];//客户需求描述
            $sku[$k]['remarks_zh'] = $v[7];//客户需求描述(澄清)
            $sku[$k]['qty'] = $v[8];//数量
            $sku[$k]['unit'] = $v[9];//单位
            $sku[$k]['brand'] = $v[10];//品牌
            $sku[$k]['created_at'] = date('Y-m-d H:i:s',time());//添加时间
        }
        //p($sku);
        //写入数据库
        $inquiryItem = new InquiryItemModel();
        try{
            foreach ($sku as $item=>$value){
                $inquiryItem->add($inquiryItem->create($value));
            }
            //删除本地临时文件
            if (is_file($localFile) && file_exists($localFile)){
                unlink($localFile);
            }
            return ['code' => '1','message' => '导入成功'];

        }catch (Exception $exception){
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'data' => ''
            ];
        }
    }

    /**
     * 下载报价单(询单管理->报价信息)
     */
    public function downQuotationAction(){

        $request = $this->validateRequests('inquiry_id');

        $data = $this->getFinalQuoteData($request['inquiry_id']);

        p($data);
        //获取数据并重组格式
        //$data = $this->getResortData($this->_requestParams['serial_no']);
        //$data = $this->simulateData();

        //创建excel表格并填充数据
        $excelFile = $this->createExcelAndInsertData($data);

        //把导出的文件上传到文件服务器上
        $remoteUrl = ExcelHelperTrait::uploadToFileServer($excelFile);

        if (!$remoteUrl){
            $this->jsonReturn(['code'=>'1','message'=>'失败']);
        }

        $this->jsonReturn([
            'code' => '1',
            'message' => '导出成功!',
            'data' => [
                'url' => $remoteUrl
            ]
        ]);
    }

    private function getFinalQuoteData($inquiry_id){

        //询单综合信息 (询价单位 流程编码 项目代码)
        $inquiryModel  = new InquiryModel();
        $info = $inquiryModel->where(['id'=>$inquiry_id])->field('buyer_name,serial_no')->find();

        //报价综合信息 (报价人，电话，邮箱，报价时间)
        $finalQuoteModel = new FinalQuoteModel();
        $finalQuoteInfo = $finalQuoteModel->where(['inquiry_id'=>$inquiry_id])->field('created_by,checked_at')->find();

        $employee = new EmployeeModel();
        $employeeInfo = $employee->where(['id'=>$finalQuoteInfo['created_by']])->field('email,mobile,name')->find();

        $info['email'] = $employeeInfo['email'];
        $info['mobile'] = $employeeInfo['mobile'];
        $info['name'] = $employeeInfo['name'];

        //p($info);
        return $info;
    }

    /**
     * 模拟报价数据
     * @return array
     */
    private function simulateData(){
        $quote = [
            'id'=>23456787654,//编号
            'inquiry_id'=>'INQ23456543',
            'biz_quote_by'=>'买买提', //商务报价人
            'quoter_email'=>'742163033@qq.com', //商务报价人邮箱
            'biz_quote_at'=>date('Y-m-d H:i:s'), //商务报价时间
            'serial_no'=>'SO123543',//项目代码(流水号)
            'notes'=>'notes',//备注
            'quote_remarks'=>'',//商务报价备注
            'logi_notes'=>'',//物流备注
            'total_weight'=>242,//总重
            'package_volumn'=>'',//包装总体积
            'trade_terms_bn'=>'',//贸易术语
            'total_logi_fee'=>'',//物流合计
            'total_logi_fee_cur'=>'',//物流合计币种
            'total_exw_price'=>'',//EXW合计
            'total_exw_cur'=>'',//EXW合计币种
            'total_quote_price'=>'',//报价合计
            'quote_cur_bn'=>'',//报价合计币种
            'payment_mode'=>'alipay',//付款方式
            'origin_place'=>'Beijing',//存放地
            'destination'=>'NewYork',//目的地
            'total_bank_fee'=>'353',//银行费用
            'total_bank_fee_cur'=>'1313',//银行费用币种
            'total_insu_fee'=>'43',//出口信用保险费用
            'total_insu_fee_cur'=>'USD',//出口信用保险费用币种
            'delivery_period'=>'15',//EXW交货周期
            'est_transport_cycle'=>'30',//预计运输周期
            'package_mode'=>'AIR'//包装方式
        ];

        $quote_item = [
            [
                'id'=>1,//编号
                'name_cn'=>'小米手机',//中文名称
                'name_en'=>'XIAOMI',//外文名称
                'quote_spec'=>'30*80',//规格
                'inquiry_desc'=>'全新',//客户需求描述
                'quote_desc'=>'缺货',//报价产品描述
                'quote_quantity'=>12,//数量
                'quote_unit'=>242,//单位
                'quote_brand'=>'小米MUI',//品牌
                'exw_unit_price'=>878,//EXW单价
                'quote_unit_price'=>876,//贸易单价
                'unit_weight'=>546,//单重
                'package_size'=>1,//包装尺寸
                'delivery_period'=>3,//交货期
                'rebate_rate'=>'',//退税率
                'quote_notes'=>'',//备注(商务技术备注)
            ],
            [
                'id'=>2,//编号
                'name_cn'=>'苹果',//中文名称
                'name_en'=>'APPLE',//外文名称
                'quote_spec'=>'30*80',//规格
                'inquiry_desc'=>'全新',//客户需求描述
                'quote_desc'=>'缺货',//报价产品描述
                'quote_quantity'=>12,//数量
                'quote_unit'=>242,//单位
                'quote_brand'=>'APPLE iPhone7',//品牌
                'exw_unit_price'=>4500,//EXW单价
                'quote_unit_price'=>4499,//贸易单价
                'unit_weight'=>546,//单重
                'package_size'=>1,//包装尺寸
                'delivery_period'=>3,//交货期
                'rebate_rate'=>'',//退税率
                'quote_notes'=>'',//备注(商务技术备注)
            ],
            [
                'id'=>3,//编号
                'name_cn'=>'三星',//中文名称
                'name_en'=>'APPLE',//外文名称
                'quote_spec'=>'30*80',//规格
                'inquiry_desc'=>'全新',//客户需求描述
                'quote_desc'=>'缺货',//报价产品描述
                'quote_quantity'=>12,//数量
                'quote_unit'=>242,//单位
                'quote_brand'=>'APPLE iPhone7',//品牌
                'exw_unit_price'=>4500,//EXW单价
                'quote_unit_price'=>4499,//贸易单价
                'unit_weight'=>546,//单重
                'package_size'=>1,//包装尺寸
                'delivery_period'=>3,//交货期
                'rebate_rate'=>'',//退税率
                'quote_notes'=>'',//备注(商务技术备注)
            ]
        ];

        $quote['quote_items'] = $quote_item;
        return $quote;
    }

    /**
     * 重组报价信息
     * @param $param
     */
    private function getResortData($param){

        $quote = new QuoteModel();

        $where = ['serial_no'=>$param];
        $fields = [
            'id',//编号
            'inquiry_id',
            'biz_quote_by', //商务报价人
            //'quoter_email', //商务报价人邮箱
            'biz_quote_at', //商务报价时间
            'serial_no',//项目代码(流水号)
            //'notes',//备注
            'quote_remarks',//商务报价备注
            //'logi_notes',//物流备注
            'total_weight',//总重
            'package_volumn',//包装总体积
            'trade_terms_bn',//贸易术语
            'total_logi_fee',//物流合计
            //'total_logi_fee_cur',//物流合计币种
            'total_exw_price',//EXW合计
            //'total_exw_cur',//EXW合计币种
            //'total_quote_price',//报价合计
            'quote_cur_bn',//报价合计币种
            'payment_mode',//付款方式
            'origin_place',//存放地
            'destination',//目的地
            'total_bank_fee',//银行费用
            //'total_bank_fee_cur',//银行费用币种
            'total_insu_fee',//出口信用保险费用
            //'total_insu_fee_cur',//出口信用保险费用币种
            'delivery_period',//EXW交货周期
            //'est_transport_cycle',//预计运输周期
            'package_mode'//包装方式
        ];

        //获取报价信息(quote表)
        $quoteInfo = $quote->where($where)->field($fields)->find();
        p($quoteInfo);
//        //获取报价明细
//        $quoteItem = new QuoteItemModel();
//        $condition = ['quote_id'=>$quoteInfo['quote_id']];
//        $quoteItemFields = [
//            'id',//编号
//            'name_cn',//中文名称
//            'name_en',//外文名称
//            'quote_spec',//规格
//            'inquiry_desc',//客户需求描述
//            'quote_desc',//报价产品描述
//            'quote_quantity',//数量
//            'quote_unit',//单位
//            'quote_brand',//品牌
//            'exw_unit_price',//EXW单价
//            'quote_unit_price',//贸易单价
//            'unit_weight',//单重
//            'package_size',//包装尺寸
//            'delivery_period',//交货期
//            'rebate_rate',//退税率
//            'quote_notes',//备注(商务技术备注)
//        ];
//
//        $quoteInfo['quote_items']  = $quoteItem->where($condition)->field($quoteItemFields)->select();
//
//        return $quoteInfo;
//
    }

    /**
     * 创建excel文件对象
     * @param $quote
     *
     * @return string 文件路径
     */
    private function createExcelAndInsertData($quote){

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();//当前sheet
        $objSheet->setTitle('市场报价单');//设置报价单标题

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

        $objSheet->setCellValue("A3", "报价人 : " .$quote['biz_quote_by'])->mergeCells("A3:E3");
        $objSheet->setCellValue("A4", "电话 : ")->mergeCells("A4:E4");
        $objSheet->setCellValue("A5", "邮箱 : " .$quote['quoter_email'])->mergeCells("A5:E5");

        $objSheet->setCellValue("F3", "询价单位 : ")->mergeCells("F3:R3");
        $objSheet->setCellValue("F4", "业务对接人 : ")->mergeCells("F4:R4");
        $objSheet->setCellValue("F5", "报价时间 : " .$quote['biz_quote_at'])->mergeCells("F5:R5");


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
            $objSheet->setCellValue("K".$num11, $quote['delivery_period']);

            $num12 = $row_num + 3;
            $objSheet->setCellValue("B".$num12, "贸易术语");
            $objSheet->setCellValue("C".$num12, $quote['trade_terms_bn']);
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
            $objSheet->setCellValue("D".$num15, "报价合计币种");
            $objSheet->setCellValue("E".$num15, '');
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
            $objSheet->setCellValue("A".$num16, '报价备注 : ' )->mergeCells("A".$num16.":K".$num17);
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

        }

        //添加logo

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");

        return ExcelHelperTrait::createExcelToLocalDir($objWriter,time().'.xls');

    }
}

