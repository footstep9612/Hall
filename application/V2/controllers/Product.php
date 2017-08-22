<?php

/**
 * 产品管理
 * Author: linkai
 * DateTime: 2017/7/21 15:40
 * Copyright  Erui
 */
class ProductController extends PublicController {

    protected $method = '';

    public function init() {
        parent::init();

        $this->method = $this->getMethod();
        Log::write(json_encode($this->put_data), Log::INFO);
    }

    /**
     * 基本详情信息
     */
    public function infoAction() {
        $spu = isset($this->put_data['spu']) ? $this->put_data['spu'] : '';
        $lang = isset($this->put_data['lang']) ? $this->put_data['lang'] : '';
        $status = isset($this->put_data['status']) ? $this->put_data['status'] : '';
        if (empty($spu)) {
            jsonReturn('', '1000', '参数[spu]有误');
        }

        if ($lang != '' && !in_array($lang, array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', '1000', '参数[语言]有误');
        }

        if ($status != '' && !in_array($status, array('NORMAL', 'CLOSED', 'VALID', 'TEST', 'CHECKING', 'INVALID', 'DELETED'))) {
            jsonReturn('', '1000', '参数[状态]有误');
        }

        $productModel = new ProductModel();
        $result = $productModel->getInfo($spu, $lang, $status);
        if ($result !== false) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
        exit;
    }

    /**
     * 产品添加/编辑
     */
    public function editAction() {
        $productModel = new ProductModel();
        $result = $productModel->editInfo($this->put_data);
        if ($result) {
            $this->updateEsproduct($this->put_data, $result);
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
        exit;
    }

    public function updateEsproduct($input, $spu) {
        $es_product_model = new EsProductModel();
        $langs = ['en', 'zh', 'es', 'ru'];

        foreach ($langs as $lang) {

            if (isset($input[$lang]) && $input[$lang]) {
                $es_product_model->create_data($spu, $lang);
            } elseif (empty($input)) {
                $es_product_model->create_data($spu, $lang);
            }
        }
    }

    /**
     * SPU删除
     * @param array $spu
     * @param string $lang
     */
    public function deleteAction() {
        if (!isset($this->put_data['spu'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        $lang = '';
        if (isset($this->put_data['lang']) && !in_array(strtolower($this->put_data['lang']), array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        } else {
            $lang = isset($this->put_data['lang']) ? strtolower($this->put_data['lang']) : null;
        }

        /**
         * 查看是否存在上架
         */
        $showCatProductModel = new ShowCatProductModel();
        $scp_info = $showCatProductModel->where(array('spu' => is_array($this->put_data['spu']) ? array('in', $this->put_data['spu']) : $this->put_data['spu'], 'lang' => $lang))->find();
        if ($scp_info) {
            jsonReturn('', ErrorMsg::NOTDELETE_EXIST_ONSHELF);
        }

        $productModel = new ProductModel();
        $result = $productModel->deleteInfo($this->put_data['spu'], $lang);
        if ($result) {
            if ($lang) {
                $this->updateEsproduct($lang, $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
            }
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 修改
     * @param string $update_type 操作 必填
     * @param string $spu 必填
     * @param string $lang 语言  选填 不填将处理全部语言
     */
    public function updateAction() {
        if (!isset($this->put_data['update_type'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        if (!isset($this->put_data['spu'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        $lang = '';
        if (isset($this->put_data['lang']) && !in_array(strtolower($this->put_data['lang']), array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        } else {
            $lang = isset($this->put_data['lang']) ? strtolower($this->put_data['lang']) : '';
        }

        $remark = isset($this->put_data['remark']) ? htmlspecialchars($this->put_data['remark']) : '';

        $result = '';
        switch ($this->put_data['update_type']) {
            case 'declare':    //SPU报审
                $productModel = new ProductModel();
                $result = $productModel->updateStatus($this->put_data['spu'], $lang, $productModel::STATUS_CHECKING);
                break;
            case 'verifyok':    //SPU审核通过
                $productModel = new ProductModel();
                $result = $productModel->updateStatus($this->put_data['spu'], $lang, $productModel::STATUS_VALID, $remark);
                break;
            case 'verifyno':    //SPU审核驳回
                $productModel = new ProductModel();
                $result = $productModel->updateStatus($this->put_data['spu'], $lang, $productModel::STATUS_INVALID, $remark);
                break;
        }
        if ($result) {
            if ($lang) {
                $this->updateEsproduct([$lang => $this->put_data['spu']], $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
            }
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 产品附件
     */
    public function attachAction() {
        $spu = isset($this->put_data['spu']) ? $this->put_data['spu'] : '';

        if (empty($spu)) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }
        $status = isset($this->put_data['status']) ? $this->put_data['status'] : '';

        $pattach = new ProductAttachModel();
        $result = $pattach->getAttachBySpu($spu, $status);
        if ($result !== false) {

            $this->updateEsproduct(null, $spu);

            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
        exit;
    }

    /**
     * 上架
     */
    public function onshelfAction() {
        if (!isset($this->put_data['spu'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if (!isset($this->put_data['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $lang = isset($this->put_data['lang']) ? $this->put_data['lang'] : '';
        $spu = isset($this->put_data['spu']) ? $this->put_data['spu'] : '';
        $cat_no = isset($this->put_data['cat_no']) ? $this->put_data['cat_no'] : '';

        $showCatProduct = new ShowCatProductModel();
        $result = $showCatProduct->onShelf($spu, $lang, $cat_no);
        if ($result) {
            if ($lang) {
                $this->updateEsproduct($lang, $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
            }
            jsonReturn(true);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 下架
     */
    public function downshelfAction() {
        if (!isset($this->put_data['spu'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        $lang = '';
        if (isset($this->put_data['lang']) && !in_array(strtolower($this->put_data['lang']), array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', ErrorMsg::WRONG_LANG);
        } else {
            $lang = isset($this->put_data['lang']) ? strtolower($this->put_data['lang']) : '';
        }

        $cat_no = isset($this->put_data['cat_no']) ? $this->put_data['cat_no'] : '';

        $showCatProduct = new ShowCatProductModel();
        $result = $showCatProduct->downShelf($this->put_data['spu'], $lang, $cat_no);
        if ($result) {
            if ($lang) {
                $this->updateEsproduct($lang, $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
            }
            jsonReturn(true);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 审核记录
     */
    public function checklogAction() {
        $spu = ($this->method == 'GET') ? $this->getQuery('spu', '') : (isset($this->put_data['spu']) ? $this->put_data['spu'] : '');
        $lang = ($this->method == 'GET') ? $this->getQuery('lang', '') : (isset($this->put_data['lang']) ? $this->put_data['lang'] : '');

        if (empty($spu)) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if (empty($lang)) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $pchecklog = new ProductCheckLogModel();
        $logs = $pchecklog->getRecord(array('spu' => $spu, 'lang' => $lang), 'spu,lang,status,remarks,approved_by,approved_at');
        if ($logs !== false) {
            jsonReturn($logs);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 产品导入
     */
    public function importAction() {
        //$remoteFile = $this->put_data['url'];
        //下载到本地临时文件
       // $localFile = ExcelHelperTrait::download2local($remoteFile);

        $localFile = MYPATH.'/public/tmp/1501903034.xls';
        $data = ExcelHelperTrait::ready2import($localFile);
        var_dump($data);die;
        $this->jsonReturn($this->importSkuHandler($data));
    }

    /**
     * 产品导出
     */
    public function exportAction() {
        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();    //当前sheet
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);
        $objSheet->getStyle("A1:I1")
            ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objSheet->getStyle("A1:I1")->getFont()->setSize(14)->setBold(true);    //粗体
        $objSheet->getStyle("A1:I1")->getFill()->getStartColor()->setARGB('FF808080');
        $objSheet->getRowDimension("1")->setRowHeight(25);    //设置行高
        $column_width_20 = ["B","C","D"];
        foreach($column_width_20 as $column){
            $objSheet->getColumnDimension($column)->setWidth(20);
        }
        $column_width_30 = ["E","F","G","H","I"];
        foreach($column_width_30 as $column){
            $objSheet->getColumnDimension($column)->setWidth(30);
        }
        $objSheet->setTitle('产品SPU');//设置报价单标题
        $objSheet->setCellValue("A1","序号");
        $objSheet->setCellValue("B1","物料分类编码");
        $objSheet->setCellValue("C1","产品名称");
        $objSheet->setCellValue("D1","品牌");
        $objSheet->setCellValue("E1","产品优势");
        $objSheet->setCellValue("F1","技术参数");
        $objSheet->setCellValue("G1","执行标准");
        $objSheet->setCellValue("H1","关键字");
        $objSheet->setCellValue("I1","产品描述");

        //保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");

        return ExcelHelperTrait::createExcelToLocalDir($objWriter,time().'.xls');
    }

}
