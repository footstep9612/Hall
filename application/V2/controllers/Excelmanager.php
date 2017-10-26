<?php

/**
 * @desc   系统Excel相关操作处理
 * @Author 买买提
 */
class ExcelmanagerController extends PublicController {

    public function init() {
        parent::init();
    }

    /**
     * 本地Form表单上传测试
     */
    public function uploadAction() {
        $this->getView()->assign("content", "Hello World");
        $this->display('upload');
    }

    /**
     * @desc 获取请求
     * @return mixed
     */
    private function requestParams() {
        return json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params = '') {

        $request = $this->requestParams();
        unset($request['token']);

        //判断筛选字段为空的情况
        if ($params) {
            $params = explode(',', $params);
            foreach ($params as $param) {
                if (empty($request[$param]))
                    $this->jsonReturn(['code' => '-104', 'message' => '缺少参数']);
            }
        }
        return $request;
    }

    /**
     * @desc 下载sku导入模板(询单管理->新增询单)
     */
    public function downloadInquirySkuTemplateAction() {
        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => [
                //'url' => 'http://file01.erui.com/group1/M00/00/03/rBFgyFmegqKAIt1pAAAm7A4b9LA55.xlsx'
                'url' => 'http://file01.erui.com/group1/M00/00/06/rBFgyFnJzoeASARRAAAnAJ_tM4k52.xlsx'
            ]
        ]);
    }

    /**
     * @desc 导入sku(询单管理->新增询单)
     */
    public function importSkuAction() {

        $request = $this->validateRequests('inquiry_id,file_url');

        $remoteFile = $request['file_url'];
        $inquiry_id = $request['inquiry_id'];
        //下载到本地临时文件
        $localFile = ExcelHelperTrait::download2local($remoteFile);
        $data = ExcelHelperTrait::ready2import($localFile);

        $response = $this->importSkuHandler($localFile, $data, $inquiry_id);
        $this->jsonReturn($response);

    }

    /**
     * 执行导入操作
     * @param $data
     * @return array
     */
    private function importSkuHandler($localFile, $data, $inquiry_id) {

        array_shift($data); //去掉第一行数据(excel文件的标题)
        if (empty($data)) {
            return ['code' => '-104', 'message' => '没有可导入的数据', 'data' => ''];
        }

        //遍历重组
        foreach ($data as $k => $v) {
            //$sku[$k]['sku'] = $v[1]; //平台sku
            $sku[$k]['inquiry_id'] = $inquiry_id; //询单id
            $sku[$k]['name'] = $v[1]; //外文品名
            $sku[$k]['name_zh'] = $v[2]; //中文品名
            $sku[$k]['qty'] = $v[3]; //数量
            $sku[$k]['unit'] = $v[4]; //单位
            $sku[$k]['brand'] = $v[5]; //品牌
            $sku[$k]['model'] = $v[6]; //型号
            $sku[$k]['remarks'] = $v[7]; //客户需求描述(外文)
            $sku[$k]['remarks_zh'] = $v[8]; //客户需求描述(中文)
            $sku[$k]['buyer_goods_no'] = $v[9]; //客户询单号
            $sku[$k]['created_at'] = date('Y-m-d H:i:s', time()); //添加时间
        }

        //写入数据库
        $inquiryItem = new InquiryItemModel();
        try {
            foreach ($sku as $item => $value) {
                $inquiryItem->add($inquiryItem->create($value));
            }
            //删除本地临时文件
            if (is_file($localFile) && file_exists($localFile)) {
                unlink($localFile);
            }
            return ['code' => '1', 'message' => '导入成功'];
        } catch (Exception $exception) {
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
    public function downQuotationAction() {

        $request = $this->validateRequests('inquiry_id');
        $inquiryAttach = new InquiryAttachModel();
		$condition = ['inquiry_id'=>intval($request['inquiry_id']),'attach_group'=>'FINAL'];
		$ret = $inquiryAttach->getList($condition);
		if($ret['code']  == 1 && !empty($ret['data']) && !empty($ret['data'][0]['attach_url'])){
			$this->jsonReturn([
				'code' => '1',
				'message' => '导出成功!',
				'data' => [
					'url' => $ret['data'][0]['attach_url']
				]
			]);
		}

        $data = $this->getFinalQuoteData($request['inquiry_id']);

        //创建excel表格并填充数据
        $excelFile = $this->createExcelAndInsertData($data);

        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
		$fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server. '/V2/Uploadfile/upload';
        $data['tmp_name']=$excelFile;
        $data['type']='application/excel';
        $data['name']='excelFile';
        $remoteUrl = $this->postfile($data,$url);    
    
        if (!$remoteUrl) {
            $this->jsonReturn(['code' => '1', 'message' => '失败']);
        }
        //构建打包文件数组
        $fileName = date('YmdHis');
        $files = [
            ['url'=>$excelFile,'name'=>$fileName.'.xls']
        ];
        
        $condition = [
            'inquiry_id'   => $request['inquiry_id'],
            'attach_group' => ['in',['INQUIRY','TECHNICAL','DEMAND']]
        ];
        $inquiryList = $inquiryAttach->getList($condition);
        
        if($inquiryList['code'] == 1){
            foreach($inquiryList['data'] as $item){
                $files[] = ['url'=>$fastDFSServer.$item['attach_url'],'name'=>$item['attach_name']];
            }
        }

        //上传至FastDFS
        $zipFile = $fileName.'.zip';
        $fileId = $this->packAndUpload($url,$zipFile,$files);
        //上传失败
        if(empty($fileId) || empty($fileId['url'])){
            $this->jsonReturn([
                'code' => '-1',
                'message' => '导出失败!',
            ]);
            return;
        }

        //保存数据库
        $data = [
            'inquiry_id'   => intval($request['inquiry_id']),
            'attach_group' => 'FINAL',
            'attach_type'  => 'application/zip',
            'attach_name'  => $zipFile,
            'attach_url'   => $fileId['url'],
            'created_by'   => intval($this->user['id']),
            'created_at'   => date('Y-m-d H:i:s')
        ];        
        $inquiryAttach->addData($data);

        //删除本地的临时文件
        @unlink($excelFile);
        $this->jsonReturn([
            'code' => '1',
            'message' => '导出成功!',
            'data' => [
                'url' => $fileId['url']
            ]
        ]);
    }

    public function finalQuotationAction() {
    
        $request = $this->validateRequests('inquiry_id');

        $data = $this->getFinalQuoteData($request['inquiry_id']);

        $excelFile = $this->createFinalExcelAndInsertData($data);

        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server. '/V2/Uploadfile/upload';
        $data['tmp_name']=$excelFile;
        $data['type']='application/excel';
        $data['name']='excelFile';
        $remoteUrl = $this->postfile($data,$url);

        if (!$remoteUrl) {
            $this->jsonReturn(['code' => '1', 'message' => '失败']);
        }
        //构建打包文件数组
        $fileName = date('YmdHis');
        $files = [
            ['url'=>$excelFile,'name'=>$fileName.'.xls']
        ];


        //上传至FastDFS
        $zipFile = $fileName.'.zip';
        $fileId = $this->packAndUpload($url,$zipFile,$files);
        //上传失败
        if(empty($fileId) || empty($fileId['url'])){
            $this->jsonReturn([
                'code' => '-1',
                'message' => '导出失败!',
            ]);
            return;
        }

        //删除本地的临时文件
        @unlink($excelFile);
        $this->jsonReturn([
            'code' => '1',
            'message' => '导出成功!',
            'data' => [
                'url' => $fileId['url']
            ]
        ]);
    }

    /**
     * 上传文件至FastDFS
     * @param     $data 本地文件信息
     * @param     $url  上传接口地址
     * @param int $timeout  响应时间
     * @return array|mixed
     */
    function postfile($data, $url, $timeout = 30) {             
        $cfile = new \CURLFile($data['tmp_name'], $data['type'], $data['name']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['upFile' => $cfile]);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            print_r(curl_error($ch));
            \think\Log::write('Curl error: ' . curl_error($ch), LOG_ERR);
            return [];
        }
        curl_close($ch);
        $cfile = null;
        unset($cfile);
        return json_decode($response, true);
    }

    
    /**
    * 打包文件并且上传至FastDFS服务器
    * @param string $url 上传地址
    * @param string $filename 压缩包名称
    * @param array $files  需要打包的文件列表
    * @return mixed
    **/
    private function packAndUpload($url,$filename,$files){
        //创建临时目录
        $tmpdir = $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/".uniqid().'/';
        @mkdir($tmpdir,0777,true);        
        if(!is_dir($tmpdir)){       
            return false;
        }

        //复制文件到临时目录
        foreach($files as $key=>$file){
            $name = $file['name'];
            //如果文件存在则重命名
            if(file_exists($tmpdir.$name)){
                //循环100次修改文件名
                for($i=1;$i<100;$i++){
                    $name = preg_replace("/(\.\w+)/i","($i)$1",$name);
                    if(!file_exists($tmpdir.$name)){
                        break;
                    }
                }
            }            
            //目标文件仍然存在，则写入错误文件
            if(file_exists($tmpdir.$name)){				
                $error_files[] = $file;
            }			
			$name = iconv('utf-8','gbk',$name);
			$content = @file_get_contents($file['url']);
            @file_put_contents($tmpdir.$name,$content);  
            
        }
        //如果有文件无法复制到本目录
        if(!empty($error_files)){
            return false;
        }
        //生成压缩文件
        $zip=new ZipArchive();
        $filepath = dirname($tmpdir).'/'.$filename;
        $res = $zip->open($filepath, ZIPARCHIVE::CREATE|ZIPARCHIVE::OVERWRITE);
        if($res !== true){
            return false;
        }
        
        $files = scandir($tmpdir);
        foreach($files as $item){
            if($item != '.' && $item != '..'){
                $zip->addFile($tmpdir.$item,$item);                
            }
        }
        $zip->close();
        //清理临时目录
        foreach($files as $item){
            if($item != '.' && $item != '..'){
                unlink($tmpdir.$item);            
            }
        }
        @rmdir($tmpdir);
        //上传至FastDFS
        $data['tmp_name']=$filepath;
        $data['type']='application/zip';
        $data['name']=$filename;
        $ret = $this->postfile($data,$url);         
        //删除临时压缩文件
        @unlink($filepath);
        return $ret;
    }

    /**
     * 获取报价单信息
     * @param $inquiry_id   询单id
     * @return array    报价信息
     */
    private function getFinalQuoteData($inquiry_id) {

        //询单综合信息 (询价单位 流程编码 项目代码)
        $inquiryModel = new InquiryModel();
        $info = $inquiryModel->where(['id' => $inquiry_id])->field('serial_no,buyer_name,quote_notes')->find();

        //报价综合信息 (报价人，电话，邮箱，报价时间)
        $finalQuoteModel = new FinalQuoteModel();
        $finalQuoteInfo = $finalQuoteModel->where(['inquiry_id' => $inquiry_id])->field('checked_at,checked_by')->find();

        $employee = new EmployeeModel();
        $employeeInfo = $employee->where(['id' => intval($finalQuoteInfo['checked_by'])])->field('email,mobile,name')->find();

        //报价人信息
        $info['quoter_email'] = $employeeInfo['email'];
        $info['quoter_mobile'] = $employeeInfo['mobile'];
        $info['quoter_name'] = $employeeInfo['name'];
		//由于此文件仅生成一次，所以记录日期跟当前日期一致
        $info['quote_time'] = date('Y-m-d');//$finalQuoteInfo['checked_at']; 


        //报价单项(final_quote)
        $finalQuoteItemModel = new FinalQuoteItemModel();
        $fields = 'a.id,a.inquiry_id,b.name_zh,b.name,b.model,b.remarks,c.remarks quote_remarks,b.qty,b.unit,b.brand,a.exw_unit_price,a.quote_unit_price,c.gross_weight_kg,c.package_size,c.package_mode,c.delivery_days,c.period_of_validity';
        $finalQuoteItems = $finalQuoteItemModel->alias('a')
                ->join('erui_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
                ->join('erui_rfq.quote_item c ON a.quote_item_id = c.id')
                ->field($fields)
                ->where(['a.inquiry_id' => $inquiry_id])
                ->order('a.id DESC')
                ->select();

        $quoteModel = new QuoteModel();
        $quoteLogiFeeModel = new QuoteLogiFeeModel();
        $quoteInfo = $quoteModel->where(['inquiry_id' => $inquiry_id])->field('total_weight,package_volumn,payment_mode,delivery_period,trade_terms_bn,trans_mode_bn,dispatch_place,delivery_addr,total_logi_fee,total_bank_fee,total_exw_price,total_insu_fee,total_quote_price,quote_remarks,quote_no,quote_cur_bn')->find();
        $quoteLogiFee = $quoteLogiFeeModel->where(['inquiry_id' => $inquiry_id])->field('est_transport_cycle,logi_remarks')->find();
        $quoteInfo['logi_remarks'] =$quoteLogiFee['logi_remarks'];
        $quoteInfo['est_transport_cycle'] =$quoteLogiFee['est_transport_cycle'];

        //综合报价信息
        return $finalQuoteData = [
            'quoter_info' => $info,
            'quote_items' => $finalQuoteItems,
            'quote_info' => $quoteInfo
        ];

    }
	/**
	* 获取用户所在部门数组
	* @param int $uid 用户ID
	* @return array 返回部门数组，从顶级到最低一级
	**/
	private function getDepartmentByUid($uid){
		if(!is_numeric($uid) || $uid <1){
			return [];
		}
		$orgMember = new OrgMemberModel();
		$orgId = $orgMember->where(['employee_id' => intval($uid)])->getField('org_id');
		if($orgId < 1){
			return [];
		}
		$org = new OrgModel();
		$list = $org->field('id,parent_id,name')->select();
		$orgs = [];
		foreach($list as $key=>$item){
			$orgs[$item['id']] = &$list[$key];
		}
		foreach($orgs as $key=>$item){
			if(isset($orgs[$item['parent_id']])){
				$orgs[$key]['parent'] = &$orgs[$item['parent_id']];
			}else{
				$orgs[$key]['parent'] = null;
			}
		}		
		$depats = [];
		//最大20级，防止死循环
		for($i=0;$i<20;$i++){
			if(isset($orgs[$orgId])){
				array_unshift($depats,$orgs[$orgId]['name']);
				$orgId = (int)$orgs[$orgId]['parent_id'];
			}else{
				return $depats;
			}
		}
	}


    private function createFinalExcelAndInsertData($quote) {

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('commercial offer');

        $styleArray = ['borders' => ['outline' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '333333']]]];

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", 'Erui International Electronic Commerce Co.,Ltd')->mergeCells("A1:H1")->getRowDimension(1)->setRowHeight(45);
        $objSheet->setCellValue("B2", '        Tel:+86-400-820-9199             E-mail: eruixsgl@keruigroup.com')->mergeCells("B2:G2");
        $objSheet->setCellValue("B3", '        Fax: +86-0546-8375185           http://www.erui.com')->mergeCells("B3:G3");
        //$objSheet->getStyle("A4:G5")->applyFromArray($styleArray);


        $objSheet->getStyle("A1:H1")->getFont()->setSize(16)->setBold(true);
        $objSheet->mergeCells("A4:H4");

        /* 设置A1~R1的文字属性 */
        $objSheet->getCell("A1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objSheet->getColumnDimension("A")->setWidth('9');

        $normal_cols = ["B", "C", "D", "E", "F"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('18');
        endforeach;

        //设置最大列宽度


        $objSheet->setCellValue("A5", "Our Offer : " );
        $objSheet->setCellValue("A6", "Date : " );
        $objSheet->setCellValue("A7", "Contact : " );
        $objSheet->setCellValue("A8", "E-mail : " );
        $objSheet->setCellValue("A9", "Tel : " );

        $objSheet->setCellValue("B5", "INQ_20171024_00004" )->mergeCells("B5:C5");
        $objSheet->setCellValue("B6", "2017-10-25 " )->mergeCells("B6:C6");
        $objSheet->setCellValue("B7", "IMAMJAN MAMAT" )->mergeCells("B7:C7");
        $objSheet->setCellValue("B8", "maimt@keruigroup.com" )->mergeCells("B8:C8");
        $objSheet->setCellValue("B9", "17326916890" )->mergeCells("B9:C9");

        $objSheet->setCellValue("D5", "To : " );
        $objSheet->setCellValue("D6", "业务对接人 : ");
        $objSheet->setCellValue("D7", "项目名称 : " );
        $objSheet->setCellValue("D8", "报价要求 : " );

        $objSheet->setCellValue("E5", "OYGHAN" )->mergeCells("E5:H5");
        $objSheet->setCellValue("E6", "IMAM MAMAT ")->mergeCells("E6:H6");
        $objSheet->setCellValue("E7", "NEW YORK" )->mergeCells("E7:H7");
        $objSheet->setCellValue("E8", "BIG BIG PRICE" )->mergeCells("E8:H8");

        $objSheet->getStyle('A5:H9')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objSheet->getStyle("A5:H9")->applyFromArray($styleArray);

        $objSheet->mergeCells("D9:H9");
        $objSheet->mergeCells("A10:H10");

        $objSheet->setCellValue("A11", "Item" );
        $objSheet->setCellValue("B11", "Description" );
        $objSheet->setCellValue("C11", "Reference picture" );
        $objSheet->setCellValue("D11", "Qty." );
        $objSheet->setCellValue("E11", "Unit Price(USD)" );
        $objSheet->setCellValue("F11", "Total Price(USD)" );

        $objSheet->getRowDimension(12)->setRowHeight(35);

        $objSheet->setCellValue("A12", "1" );
        $objSheet->setCellValue("B12", "Description" );
        $objSheet->setCellValue("C12", "Reference picture" );
        $objSheet->setCellValue("D12", "12" );
        $objSheet->setCellValue("E12", "1200" );
        $objSheet->setCellValue("F12", "24000" );

        $R_N = ["A11","B11","C11","D11","E11","F11","A12","B12","C12","D12","E12","F12"];
        foreach ($R_N as $RN):
            $objSheet->getCell($RN)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;


        $objSheet->setCellValue("A14", '1. Validity:')->mergeCells("A14:H14");
        $objSheet->setCellValue("A15", '2. The above offer is based on the Incoterm XXX;')->mergeCells("A15:H15");
        $objSheet->setCellValue("A16", '3. The delivery time: ')->mergeCells("A16:H16");
        $objSheet->setCellValue("A17", '4. Any deviation about the quantity or specification from our offer may affect the price and the delivery time.')->mergeCells("A17:H17");
        $objSheet->setCellValue("A18", '5. Payment Terms: ')->mergeCells("A18:H18");
        $objSheet->setCellValue("A19", '6. The above qutation price does not include the third party inspection cost or other costs.')->mergeCells("A19:H19");


        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "FINAL_" . date('Ymd-His') . '.xls');

    }

    /**
     * 创建excel文件对象
     * @param $quote
     * @return string 文件路径
     */
    private function createExcelAndInsertData($quote) {

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet(); //当前sheet
        $objSheet->setTitle('商务报价单'); //设置报价单标题
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

        $objSheet->setCellValue("A3", "询单编号 : " . $quote['quoter_info']['serial_no'])->mergeCells("A3:R3");
        $objSheet->setCellValue("A4", "报价人 : " . $quote['quoter_info']['quoter_name'])->mergeCells("A4:E4");
        $objSheet->setCellValue("A5", "电话 : " . $quote['quoter_info']['quoter_mobile'])->mergeCells("A5:E5");
        $objSheet->setCellValue("A6", "邮箱 : " . $quote['quoter_info']['quoter_email'])->mergeCells("A6:E6");

        $objSheet->setCellValue("F4", "询价单位 : " . $quote['quoter_info']['buyer_name'])->mergeCells("F4:R4");
        $objSheet->setCellValue("F5", "业务对接人 : ")->mergeCells("F5:R5");
        $objSheet->setCellValue("F6", "报价时间 : " . $quote['quoter_info']['quote_time'])->mergeCells("F6:R6");


        $objSheet->setCellValue("A7", '易瑞国际电子商务有限公司商务技术部')
                //单元格合并
                ->mergeCells("A7:R7")
                //设置高度
                ->getRowDimension("6")
                ->setRowHeight(26);

        $objSheet->getCell("A7")
                ->getStyle()
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objSheet->setCellValue("A8", "序号\nitem")->mergeCells("A8:A9");
        $objSheet->setCellValue("B8", "名称\nitem")->mergeCells("B8:B9");
        $objSheet->setCellValue("C8", "外文名称\nitem")->mergeCells("C8:C9");
        $objSheet->setCellValue("D8", "规格\nmodel")->mergeCells("D8:D9");
        $objSheet->setCellValue("E8", "客户需求描述\nRequirementSpecifications")->mergeCells("E8:E9");
        $objSheet->setCellValue("F8", "报价产品描述\nSupplySpecifications")->mergeCells("F8:F9");
        $objSheet->setCellValue("G8", "数量\nQty")->mergeCells("G8:G9");
        $objSheet->setCellValue("H8", "单位\nUnit")->mergeCells("H8:H9");
        $objSheet->setCellValue("I8", "产品品牌\nBrand")->mergeCells("I8:I9");
        $objSheet->setCellValue("J8", "报出EXW单价\nQuote EXW Unit Price")->mergeCells("J8:J9");
        $objSheet->setCellValue("K8", "贸易单价\nTrade Unit Price")->mergeCells("K8:K9");
        $objSheet->setCellValue("L8", "单重\nUnit\nWeight(kg)")->mergeCells("L8:L9");
        $objSheet->setCellValue("M8", "包装体积\nPacking\nSizeL*W*H(mm)")->mergeCells("M8:M9");
        $objSheet->setCellValue("N8", "包装方式\nPacking")->mergeCells("N8:N9");
        $objSheet->setCellValue("O8", "交货期\nDelivery\n(Working Day)")->mergeCells("O8:O9");
        $objSheet->setCellValue("P8", "有效期\nValidity\n(Working Day)")->mergeCells("P8:P9");
        $objSheet->setCellValue("Q8", "备注\nRemark")->mergeCells("Q8:Q9");

        $cols = ["A8", "B8", "C8", "D8", "E8", "F8", "G8", "H8", "I8", "J8", "K8", "L8", "M8", "N8", "O8", "P8", "Q8"];
        foreach ($cols as $col) {
            $objSheet->getStyle($col)
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        //判断quote_item子数组
        if (is_array($quote['quote_items']) && !empty($quote['quote_items'])) {
            $row_num = 10;
            foreach ($quote['quote_items'] as $item) {
                $objSheet->setCellValue("A" . $row_num, $item['id']);
                $objSheet->setCellValue("B" . $row_num, $item['name_zh']);
                $objSheet->setCellValue("C" . $row_num, $item['name']);
                $objSheet->setCellValue("D" . $row_num, $item['model']);
                $objSheet->setCellValue("E" . $row_num, $item['remarks']);
                $objSheet->setCellValue("F" . $row_num, $item['quote_remarks']);
                $objSheet->setCellValue("G" . $row_num, $item['qty']);
                $objSheet->setCellValue("H" . $row_num, $item['unit']);
                $objSheet->setCellValue("I" . $row_num, $item['brand']);
                $objSheet->setCellValue("J" . $row_num, $item['exw_unit_price']);
                $objSheet->setCellValue("K" . $row_num, $item['quote_unit_price']);
                $objSheet->setCellValue("L" . $row_num, $item['gross_weight_kg']);
                $objSheet->setCellValue("M" . $row_num, $item['package_size']);
                $objSheet->setCellValue("N" . $row_num, $item['package_mode']);
                $objSheet->setCellValue("O" . $row_num, $item['delivery_days']);
                $objSheet->setCellValue("P" . $row_num, $item['period_of_validity']);
                $objSheet->setCellValue("Q" . $row_num, $item['quote_remarks']);

                //设置居中
                $cols = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q"];
                foreach ($cols as $col) {
                    $objSheet->getStyle($col . $row_num)
                            ->getAlignment()
                            ->setWrapText(true)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

                $row_num++;
            }

            $objSheet->getStyle("A7:K" . $row_num)->applyFromArray($styleArray);

            $num10 = $row_num + 1;
            $objSheet->setCellValue("A" . $num10, "")->mergeCells("A" . $num10 . ":R" . $num10);

            $num11 = $row_num + 2;
            $objSheet->setCellValue("B" . $num11, "总重(kg)");
            $objSheet->setCellValue("C" . $num11, $quote['quote_info']['total_weight']);
            $objSheet->setCellValue("D" . $num11, "包装总体积(m³)");
            $objSheet->setCellValue("E" . $num11, $quote['quote_info']['package_volumn']);
            $objSheet->setCellValue("F" . $num11, "付款方式");
            $objSheet->setCellValue("G" . $num11, $quote['quote_info']['payment_mode']);
            $objSheet->setCellValue("H" . $num11, "");
            $objSheet->setCellValue("I" . $num11, "");
            $objSheet->setCellValue("J" . $num11, "EXW交货周期(天)");
            $objSheet->setCellValue("K" . $num11, $quote['quote_info']['delivery_period']);

            $num12 = $row_num + 3;
            $objSheet->setCellValue("B" . $num12, "贸易术语");
            $objSheet->setCellValue("C" . $num12, $quote['quote_info']['trade_terms_bn']);
            $objSheet->setCellValue("D" . $num12, "运输方式");
            $objSheet->setCellValue("E" . $num12, $quote['quote_info']['trans_mode_bn']);
            $objSheet->setCellValue("F" . $num12, "存放地");
            $objSheet->setCellValue("G" . $num12, $quote['quote_info']['dispatch_place']);
            $objSheet->setCellValue("H" . $num12, "目的地");
            $objSheet->setCellValue("I" . $num12, $quote['quote_info']['delivery_addr']);
            $objSheet->setCellValue("J" . $num12, "运输周期(天)");
            $objSheet->setCellValue("K" . $num12, $quote['quote_info']['est_transport_cycle']);

            $num13 = $row_num + 4;
            $objSheet->setCellValue("B" . $num13, "物流合计");
            $objSheet->setCellValue("C" . $num13, $quote['quote_info']['total_logi_fee']);
            $objSheet->setCellValue("D" . $num13, "物流合计币种");
            $objSheet->setCellValue("E" . $num13, "USD");
            $objSheet->setCellValue("F" . $num13, "银行费用");
            $objSheet->setCellValue("G" . $num13, $quote['quote_info']['total_bank_fee']);
            $objSheet->setCellValue("H" . $num13, "银行费用币种");
            $objSheet->setCellValue("I" . $num13, "USD");
            $objSheet->setCellValue("J" . $num13, "");
            $objSheet->setCellValue("K" . $num13, "");

            $num14 = $row_num + 5;
            $objSheet->setCellValue("B" . $num14, "EXW合计");
            $objSheet->setCellValue("C" . $num14, $quote['quote_info']['total_exw_price']);
            $objSheet->setCellValue("D" . $num14, "EXW合计币种");
            $objSheet->setCellValue("E" . $num14, "USD");
            $objSheet->setCellValue("F" . $num14, "出信用保险");
            $objSheet->setCellValue("G" . $num14, $quote['quote_info']['total_insu_fee']);
            $objSheet->setCellValue("H" . $num14, "出信用保险币种");
            $objSheet->setCellValue("I" . $num14, "USD");
            $objSheet->setCellValue("J" . $num14, "");
            $objSheet->setCellValue("K" . $num14, "");

            $num15 = $row_num + 6;
            $objSheet->setCellValue("B" . $num15, "报价合计");
            $objSheet->setCellValue("C" . $num15, $quote['quote_info']['total_quote_price']);
            $objSheet->setCellValue("D" . $num15, "报价合计币种");
            $objSheet->setCellValue("E" . $num15, $quote['quote_info']['quote_cur_bn']);
            $objSheet->setCellValue("F" . $num15, "");
            $objSheet->setCellValue("G" . $num15, "");
            $objSheet->setCellValue("H" . $num15, "");
            $objSheet->setCellValue("I" . $num15, "");
            $objSheet->setCellValue("J" . $num15, "");
            $objSheet->setCellValue("K" . $num15, "");

            $objSheet->getStyle("A" . $num11 . ":K" . $num15)->applyFromArray($styleArray);

            $total_rows = [
                "A" . $num11, "A" . $num12, "A" . $num13, "A" . $num14, "A" . $num15, "B" . $num11, "B" . $num12, "B" . $num13, "B" . $num14, "B" . $num15,
                "C" . $num11, "C" . $num12, "C" . $num13, "C" . $num14, "C" . $num15, "D" . $num11, "D" . $num12, "D" . $num13, "D" . $num14, "D" . $num15,
                "E" . $num11, "E" . $num12, "E" . $num13, "E" . $num14, "E" . $num15, "F" . $num11, "F" . $num12, "F" . $num13, "F" . $num14, "F" . $num15,
                "G" . $num11, "G" . $num12, "G" . $num13, "G" . $num14, "G" . $num15, "H" . $num11, "H" . $num12, "H" . $num13, "H" . $num14, "H" . $num15,
                "I" . $num11, "I" . $num12, "I" . $num13, "I" . $num14, "I" . $num15, "J" . $num11, "J" . $num12, "J" . $num13, "J" . $num14, "J" . $num15,
                "K" . $num11, "K" . $num12, "K" . $num13, "K" . $num14, "K" . $num15,
            ];
            foreach ($total_rows as $total_row) {
                $objSheet->getCell($total_row)->getStyle()
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getStyle($total_row)->applyFromArray($styleArray);
            }

            $num16 = $row_num + 7;
            $num17 = $row_num + 8;
            $objSheet->setCellValue("A" . $num16, '报价备注 : ' . $quote['quoter_info']['quote_notes'])->mergeCells("A" . $num16 . ":K" . $num17);
            $objSheet->getStyle("A" . $num16 . ":K" . $num17)->applyFromArray($styleArray);
            $objSheet->getCell("A" . $num16)
                    ->getStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num18 = $row_num + 9;
            $num19 = $row_num + 10;
            $objSheet->setCellValue("A" . $num18, '物流备注 : ' . $quote['quote_info']['logi_remarks'])->mergeCells("A" . $num18 . ":K" . $num19);
            $objSheet->getStyle("A" . $num18 . ":K" . $num19)->applyFromArray($styleArray);
            $objSheet->getCell("A" . $num18)
                    ->getStyle()
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $num20 = $row_num + 11;
            $num21 = $row_num + 12;
            $objSheet->setCellValue("A" . $num20, "")->mergeCells("A" . $num20 . ":K" . $num21);
            $objSheet->getStyle("A" . $num20 . ":K" . $num21)->applyFromArray($styleArray);
        }

        //TODO 添加logo

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "FQ_" . date('Ymd-His') . '.xls');

    }

}
