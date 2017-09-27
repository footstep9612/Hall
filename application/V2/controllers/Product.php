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
            $this->jsonReturn($result);
        } else {
            $this->setCode(ErrorMsg::FAILED);
            $this->jsonReturn(false);
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

    /*
     * 更新ESgoods
     */

    public function updateEsgoods($input, $spu) {
        $es_goods_model = new EsGoodsModel();
        $goods_model = new GoodsModel();
        $langs = ['en', 'zh', 'es', 'ru'];

        foreach ($langs as $lang) {
            if (isset($input[$lang]) && $input[$lang]) {
                $list = $goods_model->getskubyspu($spu, $lang);
                $skus = [];
                foreach ($list as $item) {
                    $skus[] = $item['sku'];
                }
                $es_goods_model->create_data($skus, $lang);
            } elseif (empty($input)) {
                $list = $goods_model->getskubyspu($spu, $lang);
                $skus = [];
                foreach ($list as $item) {
                    $skus[] = $item['sku'];
                }
                $es_goods_model->create_data($skus, $lang);
            }
        }
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
                $this->updateEsproduct([$lang => $lang], $this->put_data['spu']);
                $this->updateEsgoods([$lang => $lang], $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
                $this->updateEsgoods(null, $this->put_data['spu']);
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

        $this->checkproduct();
        $this->checkimg();
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
                $this->updateEsproduct([$lang => $lang], $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
            }
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    public function checkproduct() {

        if ($this->put_data['update_type'] === 'verifyno') {
            return true;
        }
        $lang = isset($this->put_data['lang']) ? strtolower($this->put_data['lang']) : '';
        if (is_array($this->put_data['spu'])) {
            $productModel = new ProductModel();
            $checkinfo = ['spu' => ['in', $this->put_data['spu']]];

            if (!empty($lang)) {
                $checkinfo['lang'] = $lang;
            }
            $checkinfo[] = 'isnull(material_cat_no) or material_cat_no=\'\''
                    . ' or isnull(name) or `name`=\'\' or isnull(brand) or brand=\'\'';
            $pinfo = $productModel->field('spu')->where($checkinfo)->select();
            $spus = [];
            foreach ($pinfo as $item) {
                $spus[] = $item['spu'];
            }
            $spus = implode(',', $spus);
            if ($pinfo && $this->put_data['update_type'] == 'declare') {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('批量审核产品中SPU为[' . $spus . ']必填参数不全');
                $this->jsonReturn(false);
            } elseif ($pinfo && $this->put_data['update_type'] == 'verifyok') {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('批量报审产品中SPU为[' . $spus . ']必填参数不全');
                $this->jsonReturn(false);
            }
        } else {
            $productModel = new ProductModel();
            $checkinfo = ['spu' => $this->put_data['spu']];

            if (!empty($lang)) {
                $checkinfo['lang'] = $lang;
            }
            $checkinfo[] = 'isnull(material_cat_no) or material_cat_no=\'\''
                    . ' or isnull(name) or `name`=\'\' or isnull(brand) or brand=\'\'';


            $pinfo = $productModel->where($checkinfo)->find();

            if ($pinfo && $this->put_data['update_type'] == 'declare') {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('审核产品必填参数不全');
                $this->jsonReturn(false);
            } elseif ($pinfo && $this->put_data['update_type'] == 'verifyok') {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('报审产品必填参数不全');
                $this->jsonReturn(false);
            }
        }
    }

    public function checkimg() {
        if ($this->put_data['update_type'] === 'verifyno') {
            return true;
        }
        if (is_array($this->put_data['spu'])) {
            $productattachModel = new ProductAttachModel();
            if (is_array($this->put_data['spu'])) {

                $checkinfo = ['spu' => ['in', $this->put_data['spu']], 'attach_type' => 'BIG_IMAGE', 'deleted_flag' => 'N'];
                $pinfo = $productattachModel->field('spu')->where($checkinfo)->group('spu')->select();
                $spus = [];
                foreach ($pinfo as $item) {
                    $spus[] = $item['spu'];
                }
                $spus = implode(',', $spus);
                if (!$pinfo && $this->put_data['update_type'] == 'declare') {
                    $this->setCode(MSG::ERROR_PARAM);
                    $this->setMessage('批量审核产品SPU为[' . $spus . ']没有图片');
                    $this->jsonReturn(false);
                } elseif (!$pinfo && $this->put_data['update_type'] == 'verifyok') {
                    $this->setCode(MSG::ERROR_PARAM);
                    $this->setMessage('批量报审产品SPU为[' . $spus . ']没有图片');
                    $this->jsonReturn(false);
                }
            } else {
                $checkinfo = ['spu' => $this->put_data['spu'], 'attach_type' => 'BIG_IMAGE', 'deleted_flag' => 'N'];
                $pinfo = $productattachModel->where($checkinfo)->find();
                if (!$pinfo && $this->put_data['update_type'] == 'declare') {
                    $this->setCode(MSG::ERROR_PARAM);
                    $this->setMessage('审核产品没有图片');
                    $this->jsonReturn(false);
                } elseif (!$pinfo && $this->put_data['update_type'] == 'verifyok') {
                    $this->setCode(MSG::ERROR_PARAM);
                    $this->setMessage('报审产品没有图片');
                    $this->jsonReturn(false);
                }
            }
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
                $this->updateEsproduct([$lang => $lang], $this->put_data['spu']);
                $this->updateEsgoods([$lang => $lang], $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
                $this->updateEsgoods(null, $this->put_data['spu']);
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
                $this->updateEsproduct([$lang => $lang], $this->put_data['spu']);
                $this->updateEsgoods([$lang => $lang], $this->put_data['spu']);
            } else {
                $this->updateEsproduct(null, $this->put_data['spu']);
                $this->updateEsgoods(null, $this->put_data['spu']);
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
     * Usage:
     *
     */
    public function importAction() {
        if (empty($this->put_data) || empty($this->put_data['xls']) || !in_array($this->put_data['lang'],array('zh','en','es','ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        $productModel = new ProductModel();
        $result = $productModel->import($this->put_data['xls'],$this->put_data['lang']);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 压缩包导入
     */
    public function zipImportAction() {
        if (empty($this->put_data['xls'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        $productModel = new ProductModel();
        $result = $productModel->zipImport2($this->put_data['xls']);
        if ($result !== false && $result['sucess']>0) {
            $error = '';
            if (!empty($result['failds'])) {
                foreach ($result['failds'] as $e) {
                    $error .= '[' . $e['item'] . ']失败：' . $e['hint'] . ';';
                }
            }
            $result['failds'] = $error;
            //$str = '成功导入'.$result['succes_lang'].'条，spu'.$result['sucess'].'个；'.$error;
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 导出模板
     */
    public function exportTempAction() {
        $productModel = new ProductModel();
        $localDir = $productModel->exportTemp();
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 产品导出
     */
    public function exportAction() {
        $productModel = new ProductModel();
        $localDir = $productModel->export();
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 导出上下架
     */
    public function exportShelfAction() {
        $productModel = new ProductModel();
        $localDir = $productModel->exportShelf();
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
