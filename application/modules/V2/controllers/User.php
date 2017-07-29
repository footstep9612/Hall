<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author zyg
 */
class UserController extends PublicController {


    public function __init() {
        //   parent::__init();


    }
    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['username'])){
            $where['username'] = $data['username'];
        }
        if(!empty($data['group_id'])){
            $where['group_id'] = $data['group_id'];
        }
        if(!empty($data['pageSize'])){
            $limit['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])){
            $limit['page'] = ($data['currentPage']-1)*$limit['num'];
        }
        $user_modle =new UserModel();
        $data =$user_modle->getlist($where,$limit);
        if(!empty($data)){
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function infoAction() {
        $model = new UserModel();
        $data = $model->info($this->put_data['id']);
        $this->jsonReturn($data);
    }

    public function createAction() {
        $model = new UserModel();
        $data = $model->create_data($this->put_data);
        $this->jsonReturn($data);
    }

    public function updateAction() {
        $model = new UserModel();
        $data = $model->update_data($this->put_data);
        $this->jsonReturn($data);
    }

    public function deleteAction() {
        $model = new UserModel();
        $data = $model->delete_data($this->put_data['id']);
        $this->jsonReturn($data);
    }

    public function getRoleAction(){
        if($this->user['id']){
            $role_user = new RoleUserModel();
            $where['user_id'] = $this->user['id'];
            $data = $role_user->getRoleslist($where);
            $datajson = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $data
            );
            jsonReturn($datajson);
        }else{
            $datajson = array(
                'code' => -104,
                'message' => '用户验证失败',
            );
        }
    }

    public function excelAction() {

        $objPHPExcel = new PHPExcel();

//Set properties 设置文件属性  
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        $objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
        $objPHPExcel->getProperties()->setCategory("Test result file");

//Add some data 添加数据  
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Hello'); //可以指定位置  
        $objPHPExcel->getActiveSheet()->setCellValue('A2', true);
        $objPHPExcel->getActiveSheet()->setCellValue('A3', false);
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'world!');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 2);
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Hello');
        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'world!');

//循环  
        for ($i = 1; $i < 200; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $i);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, 'Test value');
        }

//日期格式化  
        $objPHPExcel->getActiveSheet()->setCellValue('D1', time());
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);

//Add comment 添加注释  
        $objPHPExcel->getActiveSheet()->getComment('E11')->setAuthor('PHPExcel');
        $objCommentRichText = $objPHPExcel->getActiveSheet()->getComment('E11')->getText()->createTextRun('PHPExcel:');
        $objCommentRichText->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getComment('E11')->getText()->createTextRun("\r\n");
        $objPHPExcel->getActiveSheet()->getComment('E11')->getText()->createTextRun('Total amount on the current invoice, excluding VAT.');




//Merge cells 合并分离单元格  
        $objPHPExcel->getActiveSheet()->mergeCells('A18:E22');
        $objPHPExcel->getActiveSheet()->unmergeCells('A18:E22');

//Protect cells 保护单元格  
        $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true); //Needs to be set to true in order to enable any worksheet protection!  
        $objPHPExcel->getActiveSheet()->protectCells('A3:E13', 'PHPExcel');

//Set cell number formats 数字格式化  
        $objPHPExcel->getActiveSheet()->getStyle('E4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('E4'), 'E5:E13');

//Set column widths 设置列宽度  
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);

//Set fonts 设置字体  
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setName('Candara');
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

//Set alignments 设置对齐  
        $objPHPExcel->getActiveSheet()->getStyle('D11')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle('A18')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
        $objPHPExcel->getActiveSheet()->getStyle('A18')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setWrapText(true);

//Set column borders 设置列边框  
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->getActiveSheet()->getStyle('A10')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->getActiveSheet()->getStyle('E10')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

//Set border colors 设置边框颜色  
        $objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getLeft()->getColor()->setARGB('FF993300');
        $objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getTop()->getColor()->setARGB('FF993300');
        $objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getBottom()->getColor()->setARGB('FF993300');
        $objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getRight()->getColor()->setARGB('FF993300');

//Set fills 设置填充  
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF808080');

//Add a hyperlink to the sheet 添加链接  
        $objPHPExcel->getActiveSheet()->setCellValue('E26', 'www.phpexcel.net');
        $objPHPExcel->getActiveSheet()->getCell('E26')->getHyperlink()->setUrl('http://www.phpexcel.net');
        $objPHPExcel->getActiveSheet()->getCell('E26')->getHyperlink()->setTooltip('Navigate to website');
        $objPHPExcel->getActiveSheet()->getStyle('E26')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


//Play around with inserting and removing rows and columns  
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(6, 10);
        $objPHPExcel->getActiveSheet()->removeRow(6, 10);
        $objPHPExcel->getActiveSheet()->insertNewColumnBefore('E', 5);
        $objPHPExcel->getActiveSheet()->removeColumn('E', 5);

//Add conditional formatting  
        $objConditional1 = new PHPExcel_Style_Conditional();
        $objConditional1->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS);
        $objConditional1->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_LESSTHAN);
        $objConditional1->setCondition('0');
        $objConditional1->getStyle()->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
        $objConditional1->getStyle()->getFont()->setBold(true);

//Set autofilter 自动过滤  
        $objPHPExcel->getActiveSheet()->setAutoFilter('A1:C9');

//Hide "Phone" and "fax" column 隐藏列  
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setVisible(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setVisible(false);

//Set document security 设置文档安全  
        $objPHPExcel->getSecurity()->setLockWindows(true);
        $objPHPExcel->getSecurity()->setLockStructure(true);
        $objPHPExcel->getSecurity()->setWorkbookPassword("PHPExcel");

//Set sheet security 设置工作表安全  
        $objPHPExcel->getActiveSheet()->getProtection()->setPassword('PHPExcel');
        $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true); // This should be enabled in order to enable any of the following!  
        $objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);



//Set outline levels  
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setOutlineLevel(1);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setVisible(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setCollapsed(true);

//Freeze panes  
        $objPHPExcel->getActiveSheet()->freezePane('A2');

//Rows to repeat at top  
        $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

//Set data validation 验证输入值  
        $objValidation = $objPHPExcel->getActiveSheet()->getCell('B3')->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_WHOLE);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $objValidation->setAllowBlank(true);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setErrorTitle('Input error');
        $objValidation->setError('Number is not allowed!');
        $objValidation->setPromptTitle('Allowed input');
        $objValidation->setPrompt('Only numbers between 10 and 20 are allowed.');
        $objValidation->setFormula1(10);
        $objValidation->setFormula2(20);
        $objPHPExcel->getActiveSheet()->getCell('B3')->setDataValidation($objValidation);

//Create a new worksheet, after the default sheet 创建新的工作标签  
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);

//Set header and footer. When no different headers for odd/even are used, odd header is assumed. 页眉页脚  
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');

//Set page orientation and size 方向大小  
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Rename sheet 重命名工作表标签  
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

//Set active sheet index to the first sheet, so Excel opens this as the first sheet  
        // $objPHPExcel->setActiveSheetIndex(0);
//Save Excel 2007 file 保存  
//        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//        $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
//
//
        // $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
//        $objWriter->save(str_replace('.php', '.xls', __FILE__));
//1.6.2新版保存  


        try {
            header("Content-Type:application/vnd.ms-excel");
            header("Content-Disposition:attachment;filename=sample.xls");
            header("Pragma:no-cache");
            header("Expires:0");
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save(str_replace('.php', '.xls', __FILE__));
            die();
        } catch (Exception $ex) {
            header("Content-Type:application/html");
            header("Content-Disposition:attachment;filename=sample.txt");
            var_dump($ex);
            echo $ex->getMessage();
            die();
        }
    }



    public function esAction() {
        $es = new ESClient();

        $index = 'erui_db';
        $type = 'product';

        $body = [
            'lang' => 'en',
            'spu' => '0',
            'meterial_cat_code' => 0,
            'qrcode' => 0,
            'name' => '',
            'show_name' => '',
            'supplier_no' => '',
            'brand' => '',
            'source' => '',
            'source_detail' => '',
            'recommend_flag' => 'Y',
            'status' => '',
            'keywords' => '',
            'updated_by' => '',
            'updated_at' => '',
            'checked_by' => '',
            'checked_at' => '',
            'created_by' => '',
            'created_at' => ''
        ];
        $es->add_document($index, $type, $body, 10);
        //  echo '<pre>';
        //  $val = $es->setmust(['testField'=>'加安疗'],ESClient::MATCH)->search($index, $type);
        //  var_dump($val);
        die();
    }

    public function kafkaAction() {
        $kafka = new KafKaServer();
        $kafka->produce();
    }

    //put your code here
}
