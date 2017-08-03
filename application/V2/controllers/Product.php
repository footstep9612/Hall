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
        //parent::init();
        $this->method = $this->getMethod();
    }

    /**
     * 基本详情信息
     */
    public function infoAction() {
        if ($this->method == 'GET') {
            $spu = $this->getQuery('spu','');
            $lang = $this->getQuery('lang','');
            $status = $this->getQuery('lang','');
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
        } else {
            jsonReturn('', ErrorMsg::ERROR_REQUEST_MATHOD);
        }
        exit;
    }

    /**
     * 产品添加/编辑
     */
    public function editAction() {
        $this->put_data = array(
            'zh' => array(
                'material_cat_no' => '111',
                'name' => '113',
                'show_name' => '111',
                'keywords' => '',
            ),
            'spu' =>'252862',
        );

        $productModel = new ProductModel();
        $result = $productModel->editInfo($this->put_data);
        if ($result) {
            Log::write('[成功]'.$result,Log::INFO);
            //$this->updateEsproduct($this->put_data, $result);
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
        exit;
    }

    public function updateEsproduct($input, $spu) {
        $es_product_model = new EsproductModel();
        $productModel = new ProductModel();
        if (isset($input['en']) && $input['en']) {
            $data = $productModel->getInfo($spu, 'en');
            $es_product_model->create_data($data['en'], 'en');
        }
        if (isset($input['es']) && $input['es']) {
            $data = $productModel->getInfo($spu, 'es');
            $es_product_model->create_data($data['es'], 'es');
        }
        if (isset($input['ru']) && $input['ru']) {
            $data = $productModel->getInfo($spu, 'ru');
            $es_product_model->create_data($data['ru'], 'ru');
        }
        if (isset($input['zh']) && $input['zh']) {
            $data = $productModel->getInfo($spu, 'zh');
            $es_product_model->create_data($data['zh'], 'zh');
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
        if(isset($this->put_data['lang']) && !in_array(strtolower($this->put_data['lang']),array('zh','en','es','ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        } else {
            $lang = isset($this->put_data['lang']) ? strtolower($this->put_data['lang']) : '';
        }

        $productModel = new ProductModel();
        $result = $productModel->deleteInfo($this->put_data['spu'], $lang);
        if ($result) {
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
        $this->put_data = array(
            'update_type'=>'verifyok',
            'spu'=>'111'
        );
        if (!isset($this->put_data['update_type'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        if (!isset($this->put_data['spu'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

        $lang = '';
        if(isset($this->put_data['lang']) && !in_array(strtolower($this->put_data['lang']),array('zh','en','es','ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        } else {
            $lang = isset($this->put_data['lang']) ? strtolower($this->put_data['lang']) : '';
        }

        $result = '';
        switch ($this->put_data['update_type']) {
            case 'declare':    //SPU报审
                $productModel = new ProductModel();
                $result = $productModel->updateStatus($this->put_data['spu'], $lang, $productModel::STATUS_CHECKING);
                break;
            case 'verifyok':    //SPU审核通过
                $productModel = new ProductModel();
                $result = $productModel->updateStatus($this->put_data['spu'],$lang,$productModel::STATUS_VALID);
                break;
            case 'verifyno':    //SPU审核驳回
                $remark = isset($this->put_data['remark']) ? htmlspecialchars($this->put_data['remark']) : '';
                $productModel = new ProductModel();
                $result = $productModel->updateStatus($this->put_data['spu'], $lang, $productModel::STATUS_INVALID);
                break;
        }
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
